<?php
/* ==================================================================
   Fidelo — Push Web (VAPID, PHP pur, sans payload chiffré).
   La notification part VIDE ; le service worker lit le texte via
   ?a=peek (identifié par le cookie fidelo_card du client). Gratuit.
   Actions : keygen (admin), send (admin), peek (SW du client).
   ================================================================== */
require __DIR__ . '/lib.php'; fidelo_session();

function b64u_enc(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }

/* Génère la paire VAPID (une fois), stockée dans settings. */
function vapid_keygen(array &$db): array {
  $res = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
  if (!$res) return ['ok' => false, 'error' => 'openssl'];
  openssl_pkey_export($res, $pem);
  $d = openssl_pkey_get_details($res);
  $pub = "\x04" . $d['ec']['x'] . $d['ec']['y'];
  $db['settings']['vapidPub']  = b64u_enc($pub);
  $db['settings']['vapidPriv'] = b64u_enc($d['ec']['d']);
  $db['settings']['vapidPem']  = $pem;
  db_save($db);
  return ['ok' => true, 'pub' => $db['settings']['vapidPub']];
}

/* Signature ES256 (DER -> r||s de 64 octets). */
function es256(string $data, string $pem): ?string {
  $key = openssl_pkey_get_private($pem);
  if (!$key) return null;
  if (!openssl_sign($data, $der, $key, OPENSSL_ALGO_SHA256)) return null;
  // DER SEQUENCE { INTEGER r, INTEGER s } -> r||s
  $off = 0;
  if (ord($der[$off++]) !== 0x30) return null;
  if (ord($der[$off]) & 0x80) $off += (ord($der[$off]) & 0x7f); $off++;
  $rs = '';
  for ($i = 0; $i < 2; $i++) {
    if (ord($der[$off++]) !== 0x02) return null;
    $len = ord($der[$off++]);
    $val = substr($der, $off, $len); $off += $len;
    $val = ltrim($val, "\x00");
    $val = str_pad($val, 32, "\x00", STR_PAD_LEFT);
    $rs .= $val;
  }
  return $rs;
}

/* Construit le JWT VAPID pour un endpoint donné. */
function vapid_jwt(array $db, string $endpoint): ?string {
  $p = parse_url($endpoint);
  $aud = $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '');
  $sub = 'mailto:' . ($db['settings']['adminEmail'] ?: 'contact@fidelo.fr');
  $head = b64u_enc(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
  $body = b64u_enc(json_encode(['aud' => $aud, 'exp' => now() + 12 * 3600, 'sub' => $sub]));
  $sig = es256($head . '.' . $body, $db['settings']['vapidPem'] ?? '');
  if ($sig === null) return null;
  return $head . '.' . $body . '.' . b64u_enc($sig);
}

/* Envoie une notification VIDE à un abonnement (réveille le SW). */
function push_one(array $db, array $sub): int {
  $jwt = vapid_jwt($db, $sub['endpoint']);
  if (!$jwt) return 0;
  $ch = curl_init($sub['endpoint']);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
      'Authorization: vapid t=' . $jwt . ', k=' . $db['settings']['vapidPub'],
      'TTL: 3600',
      'Content-Length: 0',
    ],
    CURLOPT_TIMEOUT => 10,
  ]);
  curl_exec($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return $code;
}

/* Envoie à un client (par carte) : stocke le message + push toutes ses souscriptions. */
function push_to_card(array &$db, string $card, string $title, string $body, string $url): array {
  foreach ($db['shops'] as $si => $s) foreach ($s['clients'] as $ci => $c) {
    if (($c['card'] ?? '') === $card) {
      $db['shops'][$si]['clients'][$ci]['notif'] = ['title' => $title, 'body' => $body, 'url' => $url, 'at' => now()];
      $sent = 0; $bad = [];
      foreach (($c['push'] ?? []) as $k => $sub) {
        $code = push_one($db, $sub);
        if ($code === 404 || $code === 410) $bad[] = $k; elseif ($code >= 200 && $code < 300) $sent++;
      }
      foreach (array_reverse($bad) as $k) array_splice($db['shops'][$si]['clients'][$ci]['push'], $k, 1);
      db_save($db);
      return ['ok' => true, 'sent' => $sent];
    }
  }
  return ['ok' => false, 'error' => 'notfound'];
}

/* ---------------- routage ---------------- */
$a = $_GET['a'] ?? ($_POST['a'] ?? '');
/* on ne charge que ce dont l'action a besoin */
$cible = null;
if ($a === 'peek') { $m = cards_load(); $cible = $m[preg_replace('/[^A-Za-z0-9_-]/','',$_COOKIE['fidelo_card'] ?? '')] ?? null; }
elseif ($a === 'send') { $m = cards_load(); $cible = $m[preg_replace('/[^A-Za-z0-9_-]/','',$_POST['card'] ?? '')] ?? null; }
else { $cible = current_shop_id(); }
$LOCK = db_lock($cible);
$db = db_load($cible);

if ($a === 'peek') {
  // appelé par le SW du client — identifié par le cookie fidelo_card
  $card = preg_replace('/[^A-Za-z0-9_-]/', '', $_COOKIE['fidelo_card'] ?? '');
  foreach ($db['shops'] as $si => $s) foreach ($s['clients'] as $ci => $c) {
    if (($c['card'] ?? '') === $card && !empty($c['notif'])) {
      $n = $c['notif'];
      unset($db['shops'][$si]['clients'][$ci]['notif']);
      db_save($db);
      json_out($n);
    }
  }
  json_out(['title' => 'Fidelo', 'body' => 'Vous avez une nouveauté.']);
}

if ($a === 'keygen') {
  if (empty($_SESSION['admin'])) json_out(['ok' => false, 'error' => 'auth'], 401);
  $pub = vapid_ensure($db);
  json_out($pub ? ['ok' => true, 'pub' => $pub] : ['ok' => false, 'error' => 'openssl']);
}

if ($a === 'send') {
  // réservé : admin OU commerçant connecté relançant SON client
  $card = preg_replace('/[^A-Za-z0-9_-]/', '', $_POST['card'] ?? '');
  $title = mb_substr(trim($_POST['title'] ?? 'Fidelo'), 0, 60);
  $body  = mb_substr(trim($_POST['body'] ?? ''), 0, 160);
  $ok = !empty($_SESSION['admin']);
  if (!$ok && ($sid = current_shop_id())) {
    // le commerçant ne peut viser qu'un client de SON commerce
    $s = shop_ref($db, $sid);
    foreach (($s['clients'] ?? []) as $c) if (($c['card'] ?? '') === $card) { $ok = true; break; }
  }
  if (!$ok) json_out(['ok' => false, 'error' => 'auth'], 401);
  if (empty($db['settings']['vapidPem'])) json_out(['ok' => false, 'error' => 'novapid'], 400);
  json_out(push_to_card($db, $card, $title, $body ?: 'Un cadeau vous attend, revenez !', $_POST['url'] ?? './carte.php?c=' . $card));
}

/* Message groupé : le commerçant envoie une notification à TOUS ses clients
   qui ont activé les notifications (canal gratuit). Session marchand + CSRF. */
if ($a === 'broadcast') {
  fidelo_session();
  $sid = current_shop_id();
  if (!$sid) json_out(['ok' => false, 'error' => 'auth'], 401);
  $sent = $_SERVER['HTTP_X_CSRF'] ?? ($_POST['_csrf'] ?? '');
  if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$sent)) json_out(['ok' => false, 'error' => 'csrf'], 403);
  vapid_ensure($db);                         // clés créées automatiquement si absentes
  $title = mb_substr(trim($_POST['title'] ?? ''), 0, 60) ?: 'Fidelo';
  $body  = mb_substr(trim($_POST['body'] ?? ''), 0, 160);
  if ($body === '') json_out(['ok' => false, 'error' => 'empty'], 400);
  $segment = $_POST['segment'] ?? 'all';
  if (!in_array($segment, ['all', 'champions', 'fideles', 'nouveaux', 'endormis'], true)) $segment = 'all';
  $ref = &shop_ref($db, $sid);
  if (!$ref) json_out(['ok' => false, 'error' => 'auth'], 401);
  $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
  $t = now();
  /* Ciblage optionnel par segment (mêmes règles que l'onglet Statistiques). */
  $targetIds = null;
  if ($segment !== 'all') {
    $targetIds = [];
    foreach (real_clients($ref['clients']) as $c) if (client_segment($c, $t) === $segment) $targetIds[] = $c['id'];
  }
  /* 1) le message est déposé sur la carte des clients ciblés : même sans
        notification activée, il s'affiche dès qu'ils ouvrent leur carte. */
  $clients = shop_broadcast_set($ref, $title, $body, $targetIds);
  /* 2) notification push immédiate pour ceux qui l'ont activée. */
  $targets = 0; $sentN = 0;
  foreach ($ref['clients'] as $i => $c) {
    if ($targetIds !== null && !in_array($c['id'], $targetIds, true)) continue;
    if (empty($c['push'])) continue;
    $targets++;
    $ref['clients'][$i]['notif'] = ['title' => $title, 'body' => $body,
      'url' => ($base ?: '') . '/carte.php?c=' . $c['card'], 'at' => now()];
    $bad = [];
    foreach ($ref['clients'][$i]['push'] as $pk => $sub) {
      $code = push_one($db, $sub);
      if ($code === 404 || $code === 410) $bad[] = $pk; elseif ($code >= 200 && $code < 300) $sentN++;
    }
    foreach (array_reverse($bad) as $pk) array_splice($ref['clients'][$i]['push'], $pk, 1);
  }
  shop_broadcast_log($ref, $title, $body, $segment, $clients, $sentN);
  db_save($db);
  json_out(['ok' => true, 'clients' => $clients, 'targets' => $targets, 'sent' => $sentN, 'segment' => $segment]);
}

json_out(['ok' => false, 'error' => 'unknown'], 400);
