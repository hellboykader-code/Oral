<?php
/* ==================================================================
   Fidelo — Paiement Stripe (Checkout + webhook), PHP pur, aucun SDK.
   Réglages (console propriétaire) : clé secrète + secret de webhook.
   Les clés vivent dans data/db.json (dossier protégé), JAMAIS dans le
   code — même schéma que gwallet.php pour Google Wallet.
   ================================================================== */

const ST_API = 'https://api.stripe.com/v1';

/* Tarifs Fidelo (centimes, EUR) — doit rester cohérent avec TARIFS (lib.php). */
const ST_PLANS = [
  'mensuel' => ['name' => 'Fidelo — Mensuel', 'amount' => 2900,  'interval' => 'month'],
  'annuel'  => ['name' => 'Fidelo — Annuel',  'amount' => 25000, 'interval' => 'year'],
  'avie'    => ['name' => 'Fidelo — À vie',   'amount' => 52500, 'interval' => null],
];

function st_conf(array $db): array {
  $s = $db['settings']['stripe'] ?? [];
  return [
    'ok'     => !empty($s['secret']),
    'secret' => (string)($s['secret'] ?? ''),
    'whsec'  => (string)($s['whsec'] ?? ''),
    'prices' => $s['prices'] ?? [],
  ];
}
function st_on(array $db): bool { return st_conf($db)['ok']; }

/* Appel générique à l'API Stripe (form-encodé, comme l'exige leur REST API). */
function st_api(array $db, string $method, string $path, ?array $params = null): array {
  $secret = st_conf($db)['secret'];
  if ($secret === '') return ['code' => 0, 'body' => ['error' => ['message' => 'no_key']]];
  $url = ST_API . $path;
  $opt = [
    CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $secret],
  ];
  if ($method === 'GET' && $params) { $url .= '?' . http_build_query($params); }
  elseif ($params !== null) { $opt[CURLOPT_POSTFIELDS] = http_build_query($params); }
  $ch = curl_init($url);
  curl_setopt_array($ch, $opt);
  $raw = (string)curl_exec($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return ['code' => $code, 'body' => json_decode($raw, true) ?: ['raw' => $raw]];
}

/* Crée (une seule fois) les 3 produits/tarifs Fidelo sur Stripe. Idempotent :
   ne recrée jamais un tarif déjà enregistré dans les réglages. */
function st_ensure_prices(array &$db): array {
  if (!st_on($db)) return ['ok' => false, 'error' => 'no_key'];
  $prices = st_conf($db)['prices'];
  foreach (ST_PLANS as $key => $p) {
    if (!empty($prices[$key])) continue;
    $prod = st_api($db, 'POST', '/products', ['name' => $p['name']]);
    if ($prod['code'] < 200 || $prod['code'] >= 300) return ['ok' => false, 'error' => $prod['body']['error']['message'] ?? 'product'];
    $pp = ['product' => $prod['body']['id'], 'unit_amount' => $p['amount'], 'currency' => 'eur'];
    if ($p['interval']) $pp['recurring'] = ['interval' => $p['interval']];
    $price = st_api($db, 'POST', '/prices', $pp);
    if ($price['code'] < 200 || $price['code'] >= 300) return ['ok' => false, 'error' => $price['body']['error']['message'] ?? 'price'];
    $prices[$key] = $price['body']['id'];
  }
  $db['settings']['stripe']['prices'] = $prices;
  db_save($db);
  return ['ok' => true, 'prices' => $prices];
}

function st_plan_for_price(array $db, string $priceId): ?string {
  foreach (st_conf($db)['prices'] as $plan => $pid) if ($pid === $priceId) return $plan;
  return null;
}

/* Session de paiement pour UN commerce, UNE formule. */
function st_checkout_url(array $db, array $shop, string $plan): array {
  if (!in_array($plan, ['mensuel', 'annuel', 'avie'], true)) return ['ok' => false, 'error' => 'plan'];
  $priceId = st_conf($db)['prices'][$plan] ?? '';
  if ($priceId === '') return ['ok' => false, 'error' => 'no_price'];
  $origin = (is_https() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
  $base = $origin . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
  $mode = $plan === 'avie' ? 'payment' : 'subscription';
  $params = [
    'mode' => $mode,
    'line_items' => [['price' => $priceId, 'quantity' => 1]],
    'success_url' => $base . '/index.php?paid=1',
    'cancel_url'  => $base . '/index.php?paid=0',
    'client_reference_id' => $shop['id'],
    'customer_email' => $shop['email'],
    'metadata' => ['shop_id' => $shop['id'], 'plan' => $plan],
    'allow_promotion_codes' => 'true',
  ];
  if ($mode === 'subscription') $params['subscription_data'] = ['metadata' => ['shop_id' => $shop['id'], 'plan' => $plan]];
  $r = st_api($db, 'POST', '/checkout/sessions', $params);
  if ($r['code'] < 200 || $r['code'] >= 300) return ['ok' => false, 'error' => $r['body']['error']['message'] ?? 'checkout'];
  return ['ok' => true, 'url' => $r['body']['url']];
}

/* Vérifie la signature d'un webhook Stripe (HMAC-SHA256, anti-rejeu 5 min). */
function st_verify_sig(string $payload, string $sigHeader, string $secret): bool {
  $parts = [];
  foreach (explode(',', $sigHeader) as $kv) { $p = explode('=', $kv, 2); if (count($p) === 2) $parts[$p[0]] = $p[1]; }
  $t = $parts['t'] ?? ''; $v1 = $parts['v1'] ?? '';
  if ($t === '' || $v1 === '' || abs(time() - (int)$t) > 300) return false;
  $expected = hash_hmac('sha256', $t . '.' . $payload, $secret);
  return hash_equals($expected, $v1);
}

/* Anti-rejeu applicatif : un évènement Stripe déjà traité une fois ne
   modifie plus rien (Stripe retente les webhooks qui n'ont pas répondu 200). */
function st_event_seen(array &$db, string $eventId): bool {
  $seen = $db['settings']['stripe']['seen'] ?? [];
  if (in_array($eventId, $seen, true)) return true;
  $seen[] = $eventId;
  if (count($seen) > 200) $seen = array_slice($seen, -200);
  $db['settings']['stripe']['seen'] = $seen;
  return false;
}
