<?php
/* ==================================================================
   Fidelo — Google Wallet (cartes de fidélité Android).
   PHP pur, aucun SDK : signature RS256 avec openssl, jeton OAuth2 par
   assertion JWT, appels REST à walletobjects.googleapis.com.

   Réglages (console propriétaire) : Issuer ID + JSON du compte de
   service. Les clés vivent dans data/db.json (dossier protégé),
   JAMAIS dans le code.
   ================================================================== */

const GW_API   = 'https://walletobjects.googleapis.com/walletobjects/v1';
const GW_TOKEN = 'https://oauth2.googleapis.com/token';
const GW_SCOPE = 'https://www.googleapis.com/auth/wallet_object.issuer';
const GW_SAVE  = 'https://pay.google.com/gp/v/save/';

function gw_b64u(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }

/* Configuration lisible ? (Issuer ID + compte de service valides) */
function gw_conf(array $db): array {
  $g = $db['settings']['gw'] ?? [];
  $sa = json_decode($g['sa'] ?? '', true);
  $ok = !empty($g['issuer']) && !empty($sa['client_email']) && !empty($sa['private_key']);
  return ['ok' => $ok, 'issuer' => (string)($g['issuer'] ?? ''), 'sa' => $sa ?: [],
          'email' => (string)($sa['client_email'] ?? '')];
}
function gw_on(array $db): bool { return gw_conf($db)['ok']; }

/* Google n'ouvre les pass au grand public qu'après avoir accordé
   l'« accès en publication ». Tant que ce n'est pas le cas, un client
   qui cliquerait verrait un message d'erreur : on masque le bouton. */
function gw_live(array $db): bool {
  return gw_on($db) && !empty($db['settings']['gw']['live']);
}

/* Signature JWT RS256 avec la clé privée du compte de service. */
function gw_jwt(array $claims, string $privateKey): ?string {
  $head = gw_b64u(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
  $body = gw_b64u(json_encode($claims, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
  $key = openssl_pkey_get_private($privateKey);
  if (!$key) return null;
  if (!openssl_sign($head . '.' . $body, $sig, $key, OPENSSL_ALGO_SHA256)) return null;
  return $head . '.' . $body . '.' . gw_b64u($sig);
}

/* Jeton OAuth2 (mis en cache ~55 min dans les réglages). */
function gw_token(array &$db): ?string {
  $c = gw_conf($db);
  if (!$c['ok']) return null;
  $cache = $db['settings']['gw']['tok'] ?? null;
  if ($cache && ($cache['exp'] ?? 0) > now() + 60) return $cache['v'];
  $assert = gw_jwt([
    'iss' => $c['email'], 'scope' => GW_SCOPE, 'aud' => GW_TOKEN,
    'iat' => now(), 'exp' => now() + 3600,
  ], $c['sa']['private_key']);
  if (!$assert) return null;
  $ch = curl_init(GW_TOKEN);
  curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15,
    CURLOPT_POSTFIELDS => http_build_query([
      'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $assert])]);
  $r = json_decode((string)curl_exec($ch), true); curl_close($ch);
  if (empty($r['access_token'])) return null;
  $db['settings']['gw']['tok'] = ['v' => $r['access_token'], 'exp' => now() + (int)($r['expires_in'] ?? 3600)];
  db_save($db);
  return $r['access_token'];
}

/* Appel REST générique. Retourne ['code'=>int,'body'=>array]. */
function gw_api(array &$db, string $method, string $path, ?array $payload = null): array {
  $tok = gw_token($db);
  if (!$tok) return ['code' => 0, 'body' => ['error' => 'token']];
  $ch = curl_init(GW_API . $path);
  $h = ['Authorization: Bearer ' . $tok];
  $opt = [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_CUSTOMREQUEST => $method];
  if ($payload !== null) {
    $h[] = 'Content-Type: application/json';
    $opt[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  }
  $opt[CURLOPT_HTTPHEADER] = $h;
  curl_setopt_array($ch, $opt);
  $raw = (string)curl_exec($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return ['code' => $code, 'body' => json_decode($raw, true) ?: ['raw' => $raw]];
}

/* Identifiants : une classe par commerce, un objet par carte client. */
function gw_class_id(array $db, string $shopId): string { return gw_conf($db)['issuer'] . '.fidelo_' . $shopId; }
function gw_obj_id(array $db, string $card): string { return gw_conf($db)['issuer'] . '.c_' . $card; }

function gw_origin(): string {
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}
function gw_base(): string { return gw_origin() . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/'); }

/* Images de marque du commerce (logo rond + bandeau), servies par brand.php.
   Le numéro de version force Google à recharger quand le logo change. */
function gw_brand(array $shop, string $kind): string {
  return gw_base() . '/brand.php?s=' . rawurlencode($shop['id']) . '&t=' . $kind
       . '&v=' . (int)($shop['brandAt'] ?? 1);
}

/* Couleur de la carte : chaque commerce a la sienne (par type, sinon
   dérivée de son identifiant) — deux commerces ne se ressemblent pas. */
function gw_color(array $shop): string {
  $byType = ['Café' => '#5B3A1E', 'Restaurant' => '#7A2E2E', 'Boulangerie' => '#8A5A12',
             'Salon' => '#3B2A55', 'Commerce' => '#123B4A'];
  $t = $shop['type'] ?? '';
  if (isset($byType[$t])) return $byType[$t];
  $pal = ['#241A12', '#1E3A5F', '#2F4F3E', '#4A2D45', '#5A3A22', '#20404A'];
  return $pal[hexdec(substr(md5($shop['id']), 0, 2)) % count($pal)];
}

/* Modèle de carte du commerce (créé une fois, mis à jour si le nom change). */
function gw_class_payload(array $db, array $shop): array {
  return [
    'id' => gw_class_id($db, $shop['id']),
    'issuerName' => mb_substr($shop['name'], 0, 40),
    'programName' => mb_substr($shop['name'], 0, 40),
    'reviewStatus' => 'UNDER_REVIEW',
    'hexBackgroundColor' => gw_color($shop),
    'countryCode' => 'FR',
    'programLogo' => ['sourceUri' => ['uri' => gw_brand($shop, 'logo')],
      'contentDescription' => ['defaultValue' => ['language' => 'fr', 'value' => 'Logo ' . $shop['name']]]],
    'heroImage' => ['sourceUri' => ['uri' => gw_brand($shop, 'hero')],
      'contentDescription' => ['defaultValue' => ['language' => 'fr', 'value' => $shop['name']]]],
    'localizedIssuerName' => ['defaultValue' => ['language' => 'fr', 'value' => mb_substr($shop['name'], 0, 40)]],
    'localizedProgramName' => ['defaultValue' => ['language' => 'fr', 'value' => mb_substr($shop['name'], 0, 40)]],
  ];
}

/* Crée la classe si absente. Retourne true si elle existe au final. */
function gw_ensure_class(array &$db, array $shop): bool {
  $id = gw_class_id($db, $shop['id']);
  $r = gw_api($db, 'GET', '/loyaltyClass/' . rawurlencode($id));
  if ($r['code'] === 200) {
    // le commerce a changé de nom ? on met le modèle à jour chez Google
    // (Google renvoie le nom dans localizedProgramName, pas dans programName)
    $vu = (string)($r['body']['localizedProgramName']['defaultValue']['value']
                   ?? $r['body']['programName'] ?? '');
    $logoVu = (string)($r['body']['programLogo']['sourceUri']['uri'] ?? '');
    if ($vu !== mb_substr($shop['name'], 0, 40) || $logoVu !== gw_brand($shop, 'logo'))
      gw_api($db, 'PATCH', '/loyaltyClass/' . rawurlencode($id), gw_class_payload($db, $shop));
    return true;
  }
  if ($r['code'] !== 404) return false;
  $c = gw_api($db, 'POST', '/loyaltyClass', gw_class_payload($db, $shop));
  if ($c['code'] >= 200 && $c['code'] < 300) return true;
  /* Google refuse quand il n'arrive pas à charger nos images (GD absent,
     site inaccessible…). Plutôt que de priver le commerce de Wallet, on
     réessaie avec le logo Fidelo : la carte existe, le logo suivra. */
  $msg = (string)($c['body']['error']['message'] ?? '');
  if (stripos($msg, 'image') !== false) {
    $p = gw_class_payload($db, $shop);
    unset($p['heroImage']);
    $p['programLogo']['sourceUri']['uri'] = gw_base() . '/icon-512.png';
    $c = gw_api($db, 'POST', '/loyaltyClass', $p);
    return $c['code'] >= 200 && $c['code'] < 300;
  }
  return false;
}

/* Carte du client : points, QR (lisible par notre scanner), prochain palier. */
function gw_obj_payload(array $db, array $shop, array $client): array {
  $rw = $shop['rewards'] ?? [];
  usort($rw, fn($a, $b) => $a['pts'] - $b['pts']);
  $pts = (int)$client['points'];
  $next = null;
  foreach ($rw as $r) if ((int)$r['pts'] > $pts) { $next = $r; break; }
  $txt = $next
    ? ('Plus que ' . ((int)$next['pts'] - $pts) . ' point(s) pour « ' . $next['t'] . ' »')
    : 'Vous avez atteint le palier maximum. Merci !';
  return [
    'id' => gw_obj_id($db, $client['card']),
    'classId' => gw_class_id($db, $shop['id']),
    'state' => 'ACTIVE',
    'accountId' => $client['card'],
    'accountName' => $client['name'],
    'loyaltyPoints' => ['label' => 'Points', 'balance' => ['int' => $pts]],
    'barcode' => ['type' => 'QR_CODE', 'value' => 'FIDELO:' . $client['card'],
      'alternateText' => $client['card']],
    'textModulesData' => [[
      'id' => 'next', 'header' => 'Prochaine récompense', 'body' => $txt,
    ]],
    'linksModuleData' => ['uris' => [[
      'uri' => gw_base() . '/carte.php?c=' . $client['card'],
      'description' => 'Ouvrir ma carte Fidelo', 'id' => 'carte',
    ]]],
  ];
}

/* Lien « Ajouter à Google Wallet » (JWT signé : l'objet est créé au clic). */
function gw_save_url(array &$db, array $shop, array $client): ?string {
  $c = gw_conf($db);
  if (!$c['ok']) return null;
  if (!gw_ensure_class($db, $shop)) return null;
  $jwt = gw_jwt([
    'iss' => $c['email'], 'aud' => 'google', 'typ' => 'savetowallet',
    'iat' => now(), 'origins' => [gw_origin()],
    'payload' => ['loyaltyObjects' => [gw_obj_payload($db, $shop, $client)]],
  ], $c['sa']['private_key']);
  return $jwt ? GW_SAVE . $jwt : null;
}

/* Mise à jour des points dans le Wallet du client (silencieuse : si la
   carte n'a jamais été ajoutée, l'objet n'existe pas → on n'insiste pas). */
function gw_sync(array &$db, array $shop, array $client): bool {
  if (!gw_on($db)) return false;
  $id = gw_obj_id($db, $client['card']);
  $p = gw_obj_payload($db, $shop, $client);
  unset($p['id'], $p['classId']);
  $r = gw_api($db, 'PATCH', '/loyaltyObject/' . rawurlencode($id), $p);
  return $r['code'] >= 200 && $r['code'] < 300;
}

/* ------------------------------------------------------------------
   Mise à jour APRÈS la réponse HTTP : le commerçant ne doit jamais
   attendre Google. On ne synchronise que les clients qui ont vraiment
   ajouté leur carte au Wallet (drapeau posé au moment du « Save »).
   ------------------------------------------------------------------ */
function gw_sync_later(string $shopId, string $card): void {
  static $queued = [];
  $k = $shopId . '|' . $card;
  if (isset($queued[$k])) return;
  $queued[$k] = true;
  register_shutdown_function(function () use ($shopId, $card) {
    if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
    elseif (function_exists('litespeed_finish_request')) litespeed_finish_request();
    try {
      $db = db_load($shopId);          // un seul commerce chargé
      if (!gw_on($db)) return;
      foreach ($db['shops'] as $s) {
        if ($s['id'] !== $shopId) continue;
        foreach (($s['clients'] ?? []) as $c) {
          if (($c['card'] ?? '') !== $card) continue;
          if (empty($c['gw'])) return;   // client sans carte Wallet : rien à faire
          gw_sync($db, $s, $c);
          return;
        }
      }
    } catch (Throwable $e) { /* jamais bloquant */ }
  });
}
