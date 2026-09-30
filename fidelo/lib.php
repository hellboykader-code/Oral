<?php
/* ==================================================================
   Fidelo — noyau backend (données + auth + sécurité).
   Aucune sortie ici ; inclus par index/carte/inscription/console/api.
   Stockage : data/db.json (protégé par .htaccess). Multi-commerçant.
   ================================================================== */
declare(strict_types=1);
mb_internal_encoding('UTF-8');

define('FIDELO_ROOT', __DIR__);
define('DATA_DIR', __DIR__ . '/data');
define('DB_FILE',  DATA_DIR . '/db.json');

/* Un appareil de confiance (déverrouillage par code/visage) n'est pas
   éternel : au-delà, il faut repasser par le mot de passe. */
define('DEVICE_TTL', 120 * 86400);      // 120 jours

/* Clé de secours de la console propriétaire (?k=…), pour le jour où le mot
   de passe est perdu — la porte normale reste le mot de passe.
   ⚠️ Ne JAMAIS écrire la vraie clé ici : ce fichier est suivi par un dépôt
   Git PUBLIC. Réglez FIDELO_ADMIN_KEY comme variable d'environnement sur
   le serveur, ou modifiez cette ligne UNIQUEMENT sur la copie EN LIGNE
   (jamais dans Git) avec une valeur générée par vous
   (ex. bin2hex(random_bytes(24))). Tant qu'aucune vraie clé n'est réglée,
   la clé de secours reste désactivée (strlen < 16 ci-dessous) — seul le
   mot de passe permet de se connecter. */
if (!defined('ADMIN_KEY')) {
  define('ADMIN_KEY', getenv('FIDELO_ADMIN_KEY') ?: '');
}

/* ---------- session durcie ---------- */
function fidelo_session(): void {
  if (session_status() === PHP_SESSION_ACTIVE) return;
  $secure = is_https();
  session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 30,
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  session_name('fidelo_sid');
  session_start();
}

/* ==================================================================
   STOCKAGE — un fichier par commerce.

   data/db.json          index léger : réglages + fiche de chaque commerce
                         (nom, récompenses, appareils, compteurs). PAS les clients.
   data/shops/<id>.json  les clients et l'historique de CE commerce.
   data/cards.json       carte → commerce (pour ouvrir une carte sans tout lire).
   data/.lock-<id>       verrou par commerce : deux commerçants ne s'attendent plus.

   Pourquoi : avec tout dans un seul fichier, à 150 commerces la lecture
   dépassait la mémoire autorisée (page blanche) et un scan bloquait tout
   le monde. Ici chaque requête ne lit que ce qu'elle utilise.
   ================================================================== */
define('SHOPS_DIR', DATA_DIR . '/shops');
define('CARDS_FILE', DATA_DIR . '/cards.json');

function jread(string $file) {
  if (!is_file($file)) return null;
  $d = json_decode((string)file_get_contents($file), true);
  return is_array($d) ? $d : null;
}
function jwrite(string $file, array $data): bool {
  $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  if ($json === false) { error_log('Fidelo : encodage impossible ' . $file); return false; }
  $dir = dirname($file);
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
  $tmp = $file . '.' . getmypid() . '.tmp';
  if (@file_put_contents($tmp, $json) !== strlen($json)) { @unlink($tmp);
    error_log('Fidelo : écriture incomplète ' . $file . ' (disque plein ?)'); return false; }
  if (!@rename($tmp, $file)) { @unlink($tmp); error_log('Fidelo : rename impossible ' . $file); return false; }
  return true;
}

/* ---------- index carte → commerce ---------- */
function cards_load(): array { return jread(CARDS_FILE) ?? []; }
function cards_save(array $m): bool { return jwrite(CARDS_FILE, $m); }
/* Met à jour l'index pour UN commerce (ses cartes et ses alias). */
function cards_sync(string $shopId, array $clients): void {
  $m = cards_load();
  foreach ($m as $card => $sid) if ($sid === $shopId) unset($m[$card]);
  foreach ($clients as $c) {
    if (!empty($c['card'])) $m[$c['card']] = $shopId;
    foreach (($c['alias'] ?? []) as $al) if ($al !== '') $m[$al] = $shopId;
  }
  cards_save($m);
}

/* ---------- données d'un commerce ---------- */
function shard_file(string $id): string { return SHOPS_DIR . '/' . preg_replace('/[^A-Za-z0-9_-]/', '', $id) . '.json'; }
function shard_read(string $id): array {
  $d = jread(shard_file($id));
  return ['clients' => $d['clients'] ?? [], 'events' => $d['events'] ?? []];
}
function shard_write(string $id, array $clients, array $events): bool {
  if (count($events) > 300) $events = array_slice($events, -300);
  $ok = jwrite(shard_file($id), ['clients' => array_values($clients), 'events' => array_values($events)]);
  if ($ok) cards_sync($id, $clients);
  return $ok;
}

/* ---------- verrous ---------- */
function db_lock(?string $shopId = null) {
  if (!is_dir(DATA_DIR)) @mkdir(DATA_DIR, 0775, true);
  $f = DATA_DIR . ($shopId ? '/.lock-' . preg_replace('/[^A-Za-z0-9_-]/', '', $shopId) : '/.dblock');
  $fh = @fopen($f, 'c');
  if ($fh) @flock($fh, LOCK_EX);
  return $fh;   // gardé vivant jusqu'à la fin de la requête
}

/* ---------- chargement ----------
   $hydrate = identifiant du commerce dont on a besoin des clients.
   Les autres commerces reviennent sans leurs clients (juste le compteur). */
function db_load(?string $hydrate = null): array {
  if (!is_dir(DATA_DIR)) @mkdir(DATA_DIR, 0775, true);
  if (!is_file(DB_FILE)) { $db = db_seed(); db_save($db); return $db; }
  db_backup();
  $raw = file_get_contents(DB_FILE);
  $db  = json_decode($raw ?: '', true);

  if (!is_array($db) || !isset($db['shops'])) {
    /* Fichier illisible : on ne repart JAMAIS de zéro. */
    @copy(DB_FILE, DATA_DIR . '/db.corrompu-' . date('Ymd-His') . '.json');
    $backups = glob(DATA_DIR . '/backups/db-*.json*') ?: [];
    rsort($backups);
    foreach ($backups as $b) {
      $c = str_ends_with($b, '.gz') ? gzdecode((string)file_get_contents($b)) : file_get_contents($b);
      $try = json_decode((string)$c, true);
      if (is_array($try) && isset($try['shops'])) {
        @file_put_contents(DB_FILE, json_encode($try, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        error_log('Fidelo : index corrompu, restauré depuis ' . basename($b));
        $db = $try; break;
      }
    }
    if (!is_array($db) || !isset($db['shops'])) {
      error_log('Fidelo : index corrompu et aucune sauvegarde exploitable.');
      http_response_code(503);
      header('Content-Type: text/plain; charset=utf-8');
      exit("Service momentanément indisponible. Vos données sont conservées dans data/.\n");
    }
  }

  /* Migration automatique depuis l'ancien format (tout dans db.json). */
  $migre = false;
  foreach ($db['shops'] as $i => $sh) {
    if (isset($sh['clients']) || isset($sh['events'])) {
      shard_write($sh['id'], $sh['clients'] ?? [], $sh['events'] ?? []);
      $db['shops'][$i]['nClients'] = count(real_clients($sh['clients'] ?? []));
      unset($db['shops'][$i]['clients'], $db['shops'][$i]['events']);
      $migre = true;
    }
  }
  if ($migre) { jwrite(DB_FILE, $db); error_log('Fidelo : migration vers un fichier par commerce effectuée.'); }

  if (!isset($db['settings']['promos'])) {          // base créée avant les codes promo
    $db['settings']['promos'] = promos_defaut();
    jwrite(DB_FILE, $db);
  }
  if ($hydrate !== null && $hydrate !== '') shop_hydrate($db, $hydrate);
  return $db;
}

/* Charge les clients d'un commerce dans $db (et le marque comme « à écrire »). */
function shop_hydrate(array &$db, string $id): bool {
  foreach ($db['shops'] as $i => $sh) {
    if ($sh['id'] !== $id) continue;
    if (!empty($sh['_h'])) return true;
    $d = shard_read($id);
    $db['shops'][$i]['clients'] = $d['clients'];
    $db['shops'][$i]['events']  = $d['events'];
    $db['shops'][$i]['_h'] = true;
    return true;
  }
  return false;
}

/* Écrit l'index + le fichier de chaque commerce chargé. */
function db_save(array $db): bool {
  if (!isset($db['shops'])) { error_log('Fidelo : db_save refusé (structure invalide)'); return false; }
  $index = $db;
  foreach ($index['shops'] as $i => $sh) {
    if (!empty($sh['_h'])) {
      if (!shard_write($sh['id'], $sh['clients'] ?? [], $sh['events'] ?? [])) return false;
      $index['shops'][$i]['nClients'] = count(real_clients($sh['clients'] ?? []));
    }
    unset($index['shops'][$i]['clients'], $index['shops'][$i]['events'], $index['shops'][$i]['_h']);
  }
  $ok = jwrite(DB_FILE, $index);
  if ($ok) @chmod(DB_FILE, 0640);
  return $ok;
}

/* Archive complète (index + tous les commerces + index des cartes). */
function backup_write(string $chemin): bool {
  if (!is_file(DB_FILE)) return false;
  $dir = dirname($chemin);
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
  $all = ['index' => jread(DB_FILE), 'shops' => [], 'cards' => cards_load(), 'at' => now()];
  foreach (glob(SHOPS_DIR . '/*.json') ?: [] as $f)
    $all['shops'][basename($f, '.json')] = jread($f);
  $gz = gzencode(json_encode($all, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '', 6);
  if ($gz === false) return false;
  return @file_put_contents($chemin, $gz) !== false;
}

/* Sauvegarde automatique : une par jour, RAFRAÎCHIE si la journée avance
   (sinon une panne à 18 h ne laisserait que l'état de 8 h du matin). */
function db_backup(): void {
  $dir = DATA_DIR . '/backups';
  $today = $dir . '/db-' . date('Y-m-d') . '.json.gz';
  if (!is_file(DB_FILE)) return;
  $age = is_file($today) ? (now() - filemtime($today)) : PHP_INT_MAX;
  if ($age < 4 * 3600) return;                 // au plus une archive toutes les 4 h
  backup_write($today);
  /* rotation : 14 quotidiennes, puis une seule par mois */
  $files = glob($dir . '/db-*.json.gz') ?: [];
  rsort($files);
  $moisVus = [];
  foreach ($files as $i => $f) {
    if ($i < 14) continue;
    $mois = substr(basename($f), 3, 7);
    if (!isset($moisVus[$mois])) { $moisVus[$mois] = true; if (count($moisVus) <= 12) continue; }
    @unlink($f);
  }
  foreach (glob($dir . '/db-*.json') ?: [] as $vieux) @unlink($vieux);   // anciennes non compressées
}

/* ---------- helpers ---------- */
function e($s): string { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
function rid(int $n = 6): string { return substr(strtoupper(bin2hex(random_bytes($n))), 0, $n); }
function tok(int $n = 20): string { return rtrim(strtr(base64_encode(random_bytes($n)), '+/', '-_'), '='); }
/* Code court lisible pour saisie manuelle (sans 0/O/1/I/L). */
function shortcode(int $n = 4): string {
  $al = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789'; $s = '';
  for ($i = 0; $i < $n; $i++) $s .= $al[random_int(0, strlen($al) - 1)];
  return $s;
}
function now(): int { return time(); }
function norm_tel(string $t): string {
  $d = preg_replace('/\D/', '', $t);
  if (strlen($d) === 11 && str_starts_with($d, '33')) $d = '0' . substr($d, 2);
  return $d;
}
function json_out($x, int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($x, JSON_UNESCAPED_UNICODE);
  exit;
}
/* ⚠️ SÉCURITÉ : ne JAMAIS faire confiance à X-Forwarded-For envoyé par le
   client — n'importe qui peut l'inventer et contourner toutes les limites
   de tentatives. On prend l'adresse réelle de la connexion. Les en-têtes de
   proxy ne sont lus que si l'hébergeur est explicitement déclaré de confiance
   (constante FIDELO_TRUSTED_PROXY dans ce fichier). */
function client_ip(): string {
  $real = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
  if (defined('FIDELO_TRUSTED_PROXY') && FIDELO_TRUSTED_PROXY) {
    // Cloudflare / proxy déclaré : on prend la PREMIÈRE adresse de la chaîne
    $h = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($h !== '') {
      $first = trim(explode(',', $h)[0]);
      if (filter_var($first, FILTER_VALIDATE_IP)) return $first;
    }
  }
  return $real;
}

/* HTTPS réel, y compris derrière un proxy qui termine le TLS (cPanel, CDN). */
function is_https(): bool {
  return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
      || (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on')
      || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443);
}

/* ---------- WebAuthn (déverrouillage par le visage / l'empreinte) ---------- */
function b64u_dec(string $s): string { return base64_decode(strtr($s, '-_', '+/') . str_repeat('=', (4 - strlen($s) % 4) % 4)); }
function spki_pem(string $der): string { return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n"; }
function wa_rpid(): string { return preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost'); }
function wa_origin(): string {
  return (is_https() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}
/* Vérifie une assertion WebAuthn (signature de l'authentificateur). Strict :
   type, challenge, origin, rpIdHash, présence utilisateur, puis signature. */
function wa_verify_assertion(string $authDataB64, string $clientDataB64, string $sigB64, string $pubSpkiB64, string $challengeB64u): bool {
  $clientData = base64_decode($clientDataB64);
  $cd = json_decode($clientData, true);
  if (!is_array($cd)) return false;
  if (($cd['type'] ?? '') !== 'webauthn.get') return false;
  if (!hash_equals($challengeB64u, (string)($cd['challenge'] ?? ''))) return false;
  if (($cd['origin'] ?? '') !== wa_origin()) return false;
  $authData = base64_decode($authDataB64);
  if (strlen($authData) < 37) return false;
  if (!hash_equals(hash('sha256', wa_rpid(), true), substr($authData, 0, 32))) return false;
  if ((ord($authData[32]) & 0x01) === 0) return false; // User Present requis
  $signed = $authData . hash('sha256', $clientData, true);
  $pem = spki_pem(base64_decode($pubSpkiB64));
  return openssl_verify($signed, base64_decode($sigB64), $pem, OPENSSL_ALGO_SHA256) === 1;
}

/* ---------- CSRF ---------- */
function csrf_token(): string {
  fidelo_session();
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = tok(24);
  return $_SESSION['csrf'];
}
function csrf_check(): void {
  fidelo_session();
  $sent = $_SERVER['HTTP_X_CSRF'] ?? ($_POST['_csrf'] ?? '');
  if (empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$sent)) {
    json_out(['ok' => false, 'error' => 'csrf'], 403);
  }
}

/* ---------- limitation de débit (anti-brute-force) ---------- */
function rate_hit(array &$db, string $key, int $max, int $window): bool {
  // retourne true si AUTORISÉ, false si bloqué
  $db['_rl'] = $db['_rl'] ?? [];
  $k = sha1($key);
  $t = now();
  $arr = array_values(array_filter($db['_rl'][$k] ?? [], fn($ts) => $ts > $t - $window));
  if (count($arr) >= $max) { $db['_rl'][$k] = $arr; return false; }
  $arr[] = $t; $db['_rl'][$k] = $arr;
  if (count($db['_rl']) > 500) $db['_rl'] = array_slice($db['_rl'], -500, null, true);
  return true;
}
function rate_reset(array &$db, string $key): void {
  $db['_rl'][sha1($key)] = [];
}

/* ================================================================
   COMMERÇANTS (multi-tenant). Toute opération est cloisonnée par
   l'id du commerce en SESSION — jamais par un id venu de la requête.
   ================================================================ */
function &shop_ref(array &$db, string $id) {
  foreach ($db['shops'] as $i => $s) if ($s['id'] === $id) return $db['shops'][$i];
  $null = null; return $null;
}
function shop_by_email(array $db, string $email): ?array {
  $email = mb_strtolower(trim($email));
  foreach ($db['shops'] as $s) if (mb_strtolower($s['email']) === $email) return $s;
  return null;
}
function shop_by_join(array $db, string $token): ?array {
  if ($token === '') return null;
  foreach ($db['shops'] as $s) if (hash_equals((string)($s['joinToken'] ?? ''), $token)) return $s;
  return null;
}
function shop_by_short(array $db, string $code): ?array {
  $code = strtoupper($code);
  if ($code === '') return null;
  foreach ($db['shops'] as $s) if (strtoupper($s['shortCode'] ?? '') === $code) return $s;
  return null;
}
/* Génération GARANTIE unique (anti-collision) d'un code court / d'une carte. */
function unique_short(array $db): string { do { $c = shortcode(4); } while (shop_by_short($db, $c)); return $c; }
function unique_card(array $db): string { do { $t = tok(8); } while (card_taken($t)); return $t; }
/* Cartes pré-imprimées à l'avance (stock physique), pas encore remises à un
   client précis : présentes dans clients[] (même stockage, même carte QR)
   mais marquées 'blank'=>true, exclues des listes/quota/stats tant qu'elles
   ne sont pas activées (blank_activate). */
function real_clients(array $clients): array {
  return array_values(array_filter($clients, fn($c) => empty($c['blank'])));
}
/* Un client identique existe-t-il déjà dans CE commerce (anti-doublon) ?
   Match UNIQUEMENT par téléphone normalisé (identifiant fiable) : deux personnes
   du même nom ne seront JAMAIS fusionnées par erreur. Sans téléphone → pas de match. */
function client_find_existing(array $shop, string $name, string $tel): ?array {
  $tn = norm_tel($tel);
  if (strlen($tn) < 9) return null;
  foreach (($shop['clients'] ?? []) as $c) if (norm_tel($c['tel'] ?? '') === $tn) return $c;
  return null;
}
/* Alerte d'abonnement (essai fini / impayé) — n'empêche pas d'utiliser,
   affiche juste un bandeau. Seul 'annule' bloque réellement l'accès. */
function shop_alert(array $s): string {
  $st = $s['status'] ?? 'trial';
  if ($st === 'impaye') return 'impaye';
  if ($st === 'trial' && ($s['trialEnds'] ?? 0) > 0 && $s['trialEnds'] < now()) return 'trial_over';
  if ($st === 'trial' && ($s['trialEnds'] ?? 0) > 0) {
    $j = (int)ceil(($s['trialEnds'] - now()) / 86400);
    if ($j <= 3) return 'trial_' . max(0, $j);          // J-3, J-2, J-1, dernier jour
  }
  return '';
}
/* Jours restants d'essai (négatif = terminé depuis N jours). */
function trial_days(array $s): ?int {
  if (($s['status'] ?? '') !== 'trial' || empty($s['trialEnds'])) return null;
  return (int)ceil(($s['trialEnds'] - now()) / 86400);
}
/* Lecture seule : l'espace reste consultable mais on ne crédite plus de points.
   Déclenché après un délai de grâce configurable (0 = jamais, défaut 14 jours). */
function shop_readonly(array $db, array $s): bool {
  $grace = (int)($db['settings']['grace'] ?? 14);
  if ($grace <= 0) return false;
  $st = $s['status'] ?? 'trial';
  if ($st === 'active') return false;
  if ($st === 'annule') return true;
  $fin = $st === 'trial' ? (int)($s['trialEnds'] ?? 0) : (int)($s['impayeDepuis'] ?? 0);
  if ($fin <= 0) return false;
  return now() > $fin + $grace * 86400;
}
function current_shop_id(): ?string {
  fidelo_session();
  return $_SESSION['shop_id'] ?? null;
}
function require_shop(array $db): array {
  $id = current_shop_id();
  $s = $id ? shop_ref($db, $id) : null;
  if (!$s) json_out(['ok' => false, 'error' => 'auth'], 401);
  if (($s['status'] ?? '') === 'annule') json_out(['ok' => false, 'error' => 'suspended'], 403);
  return $s;
}

/* Connexion complète (email + mot de passe) → ouvre la session. */
function auth_login(array &$db, string $email, string $pass): array {
  // limite large (plusieurs employés derrière une même connexion) : 30 essais / 15 min / IP
  if (!rate_hit($db, 'login:' . client_ip(), 30, 900)) return ['ok' => false, 'error' => 'ratelimit'];
  $s = shop_by_email($db, $email);
  if (!$s || !password_verify($pass, $s['passHash'] ?? '')) {
    db_save($db);
    return ['ok' => false, 'error' => 'bad'];
  }
  fidelo_session();
  session_regenerate_id(true);
  $_SESSION['shop_id'] = $s['id'];
  $_SESSION['csrf'] = tok(24);
  // appareil de confiance (permet le déverrouillage par code ensuite)
  $dev = tok(24);
  $ref = &shop_ref($db, $s['id']);
  $ref['devices'] = $ref['devices'] ?? [];
  $ref['devices'][$dev] = ['at' => now(), 'ip' => client_ip()];
  if (count($ref['devices']) > 12) $ref['devices'] = array_slice($ref['devices'], -12, null, true);
  $ref['lastLogin'] = now();
  db_save($db);
  setcookie('fidelo_dev', $dev, ['expires' => now() + DEVICE_TTL, 'path' => '/',
    'secure' => is_https(), 'httponly' => true, 'samesite' => 'Lax']);
  return ['ok' => true, 'shop' => shop_public($ref)];
}

/* Déverrouillage rapide par code à 4 chiffres — EXIGE un appareil de confiance. */
function device_valid(array $shop, string $dev): bool {
  if ($dev === '' || empty($shop['devices'][$dev])) return false;
  $at = (int)($shop['devices'][$dev]['at'] ?? 0);
  return $at > 0 && (now() - $at) < DEVICE_TTL;
}
function auth_pin(array &$db, string $pin): array {
  $dev = $_COOKIE['fidelo_dev'] ?? '';
  // le code ne fonctionne QUE sur un appareil déjà connecté par mot de passe,
  // et pas indéfiniment (un vieux téléphone perdu ne doit plus ouvrir)
  $shop = null;
  foreach ($db['shops'] as $s) if (device_valid($s, $dev)) { $shop = $s; break; }
  if (!$shop) return ['ok' => false, 'error' => 'nodevice'];
  if (!rate_hit($db, 'pin:' . $dev, 5, 900)) { db_save($db); return ['ok' => false, 'error' => 'locked']; }
  if (!password_verify($pin, $shop['pinHash'] ?? '')) { db_save($db); return ['ok' => false, 'error' => 'bad']; }
  rate_reset($db, 'pin:' . $dev);
  $ref = &shop_ref($db, $shop['id']);
  if ($ref) $ref['devices'][$dev]['at'] = now();      // appareil encore utilisé → prolongé
  fidelo_session();
  session_regenerate_id(true);
  $_SESSION['shop_id'] = $shop['id'];
  $_SESSION['csrf'] = tok(24);
  db_save($db);
  return ['ok' => true, 'shop' => shop_public($shop)];
}
function auth_logout(): void {
  fidelo_session();
  $_SESSION = [];
  session_destroy();
}
/* Un appareil de confiance existe-t-il (→ proposer visage/code au lieu du mot de passe) ? */
function device_known(array $db): bool {
  $dev = $_COOKIE['fidelo_dev'] ?? '';
  if (!$dev) return false;
  foreach ($db['shops'] as $s) if (device_valid($s, $dev)) return true;
  return false;
}

/* Vue publique d'un commerce (jamais les hash/tokens). */
function shop_public(array $s): array {
  return [
    'id'      => $s['id'],
    'name'    => $s['name'],
    'type'    => $s['type'] ?? 'Commerce',
    'city'    => $s['city'] ?? '',
    'email'   => $s['email'],
    'goal'    => $s['goal'] ?? 25,
    'rewards' => array_values($s['rewards'] ?? []),
    'status'  => $s['status'] ?? 'trial',
    'join'    => $s['joinToken'] ?? '',
    'short'   => $s['shortCode'] ?? '',
    'alert'   => shop_alert($s),
    'trialDays' => trial_days($s),
    'plan'    => plan_of($s),
    'essai'   => essai_actif($s),
    'freeMax' => PLAN_FREE_MAX,
    'parrainCode' => parrain_code($s),
    'filleuls'    => (int)($s['filleuls'] ?? 0),
    'creditMois'  => (int)($s['creditMois'] ?? 0),
    'googleReview' => $s['googleReview'] ?? '',
  ];
}

/* Création d'un commerce (inscription). */
function shop_create(array &$db, array $in): array {
  $name = mb_substr(trim($in['name'] ?? ''), 0, 60);
  $email = mb_strtolower(trim($in['email'] ?? ''));
  $pass = (string)($in['pass'] ?? '');
  $pin  = (string)($in['pin'] ?? '');
  if ($name === '') return ['ok' => false, 'error' => 'name'];
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return ['ok' => false, 'error' => 'email'];
  if (mb_strlen($pass) < 6) return ['ok' => false, 'error' => 'pass'];
  if (!preg_match('/^\d{4}$/', $pin)) return ['ok' => false, 'error' => 'pin'];
  if (shop_by_email($db, $email)) return ['ok' => false, 'error' => 'exists'];
  $id = 'S' . rid(8);
  $db['shops'][] = [
    'id'       => $id,
    'name'     => $name,
    'type'     => $in['type'] ?? 'Commerce',
    'city'     => trim($in['city'] ?? ''),
    'email'    => $email,
    'passHash' => password_hash($pass, PASSWORD_DEFAULT),
    'pinHash'  => password_hash($pin, PASSWORD_DEFAULT),
    'devices'  => [],
    'joinToken'=> tok(10),
    'shortCode'=> unique_short($db),
    'goal'     => 25,
    'rewards'  => $in['rewards'] ?? [
      ['pts' => 3,  't' => 'Boisson offerte',      'd' => ''],
      ['pts' => 6,  't' => 'Dessert maison',       'd' => ''],
      ['pts' => 10, 't' => '−15 % sur l\'addition','d' => ''],
      ['pts' => 20, 't' => 'Menu offert · VIP',    'd' => ''],
    ],
    'nClients' => 0,
    'status'   => 'trial',
    'plan'     => 'decouverte',
    'trialEnds'=> now() + 10 * 86400,
    'createdAt'=> now(),
  ];
  shard_write($id, [], []);
  db_save($db);
  return ['ok' => true, 'id' => $id];
}

/* ---------- clients (cloisonnés au commerce en session) ---------- */
function &client_ref(array &$shop, string $cid) {
  foreach (($shop['clients'] ?? []) as $i => $c) if ($c['id'] === $cid) return $shop['clients'][$i];
  $null = null; return $null;
}
/* Retrouve une carte SANS parcourir toute la base : l'index dit à quel
   commerce elle appartient, on ne charge que celui-là. */
function client_by_card(array &$db, string $token): array {
  if ($token === '') return [];
  $m = cards_load();
  $sid = $m[$token] ?? null;
  if ($sid === null) return [];
  if (!shop_hydrate($db, $sid)) return [];
  foreach ($db['shops'] as $s) {
    if ($s['id'] !== $sid) continue;
    foreach ($s['clients'] as $c)
      if (($c['card'] ?? '') === $token || in_array($token, $c['alias'] ?? [], true))
        return ['shop' => $s, 'client' => $c];
  }
  return [];
}
/* Une carte est-elle déjà utilisée ? (index seul, aucun fichier commerce lu) */
function card_taken(string $token): bool { $m = cards_load(); return isset($m[$token]); }
function next_reward(array $rewards, int $p): ?array {
  $sorted = $rewards; usort($sorted, fn($a,$b)=>$a['pts']-$b['pts']);
  foreach ($sorted as $r) if ($r['pts'] > $p) return $r;
  return null;
}
function claimable(array $rewards, int $p): ?array {
  $sorted = $rewards; usort($sorted, fn($a,$b)=>$b['pts']-$a['pts']);
  foreach ($sorted as $r) if ($r['pts'] <= $p) return $r;
  return null;
}

/* ---------- base neuve ----------
   ⚠️ AUCUN commerce de démonstration : des comptes livrés avec un mot de
   passe connu (« demo1234 ») seraient une porte ouverte en production.
   Le propriétaire crée ses commerces depuis la console. */
function db_seed(): array {
  return [
    'shops' => [],
    'settings' => ['vapidPub'=>'','vapidPriv'=>'','adminEmail'=>'',
                   'price'=>TARIFS['mensuel'],'promos'=>promos_defaut()],
    '_rl' => [],
  ];
}

/* ------------------------------------------------------------------
   Clés VAPID (notifications push gratuites).
   Générées AUTOMATIQUEMENT au premier besoin : le commerçant n'a rien
   à activer, et l'envoi groupé ne peut plus échouer faute de clés.
   ------------------------------------------------------------------ */
function vapid_ensure(array &$db): string {
  if (!empty($db['settings']['vapidPem']) && !empty($db['settings']['vapidPub'])) return $db['settings']['vapidPub'];
  if (!function_exists('openssl_pkey_new')) return '';
  $res = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
  if (!$res) return '';
  openssl_pkey_export($res, $pem);
  $d = openssl_pkey_get_details($res);
  if (empty($d['ec']['x'])) return '';
  $b = fn(string $x): string => rtrim(strtr(base64_encode($x), '+/', '-_'), '=');
  $db['settings']['vapidPub']  = $b("\x04" . $d['ec']['x'] . $d['ec']['y']);
  $db['settings']['vapidPriv'] = $b($d['ec']['d']);
  $db['settings']['vapidPem']  = $pem;
  db_save($db);
  return $db['settings']['vapidPub'];
}

/* Segment RFM simplifié d'un client (aligné sur l'action stats de api.php). */
function client_segment(array $c, int $t): string {
  if (($t - (int)($c['last'] ?? 0)) > 14 * 86400) return 'endormis';
  $p = (int)($c['points'] ?? 0);
  if ($p >= 20) return 'champions';
  if ($p >= 10) return 'fideles';
  return 'nouveaux';
}

/* Dernier message du commerce affiché sur la carte du client.
   $targetIds = null → tous les clients ; sinon uniquement les id listés. */
function shop_broadcast_set(array &$ref, string $title, string $body, ?array $targetIds = null): int {
  $n = 0;
  foreach ($ref['clients'] as $i => $c) {
    if ($targetIds !== null && !in_array($c['id'], $targetIds, true)) continue;
    $ref['clients'][$i]['msg'] = ['title' => $title, 'body' => $body, 'at' => now()];
    $n++;
  }
  return $n;
}

/* Historique des messages groupés envoyés par le commerce (30 derniers). */
function shop_broadcast_log(array &$ref, string $title, string $body, string $segment, int $clients, int $sent): void {
  $ref['broadcasts'] = $ref['broadcasts'] ?? [];
  array_unshift($ref['broadcasts'], [
    'title' => $title, 'body' => $body, 'segment' => $segment,
    'clients' => $clients, 'sent' => $sent, 'at' => now(),
  ]);
  $ref['broadcasts'] = array_slice($ref['broadcasts'], 0, 30);
}

/* ---------- palette du commerce ---------- */
function brand_colors(array $shop): array {
  $byType = [
    'Café'        => ['#5B3A1E', '#A8763F'],
    'Restaurant'  => ['#7A2E2E', '#C06A4A'],
    'Boulangerie' => ['#8A5A12', '#D9A441'],
    'Salon'       => ['#3B2A55', '#8A6FB0'],
    'Commerce'    => ['#123B4A', '#3E8FA3'],
  ];
  $t = $shop['type'] ?? '';
  if (isset($byType[$t])) return $byType[$t];
  $pal = [['#0A2E38', '#2B7A8C'], ['#1E3A5F', '#4E7FB8'], ['#2F4F3E', '#5E9B76'],
          ['#4A2D45', '#9A6A90'], ['#5A3A22', '#B07A4A']];
  return $pal[hexdec(substr(md5($shop['id']), 0, 2)) % count($pal)];
}
function rgb(string $hex): array {
  return [hexdec(substr($hex,1,2)), hexdec(substr($hex,3,2)), hexdec(substr($hex,5,2))];
}
/* initiales : « Pâtisserie Signature » → « PS » */
function monogram(string $name): string {
  $w = preg_split('/[\s\-\']+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);
  $out = '';
  foreach ($w as $x) {
    if (mb_strlen($x) < 2) continue;
    $out .= mb_strtoupper(mb_substr($x, 0, 1));
    if (mb_strlen($out) >= 2) break;
  }
  return $out !== '' ? $out : mb_strtoupper(mb_substr($name, 0, 2));
}

/* ==================================================================
   FORMULES, CODES PROMO ET PARRAINAGE
   ------------------------------------------------------------------
   decouverte : gratuit à vie, 30 clients maximum
   mensuel / annuel / avie : tout illimité
   Un nouveau commerce reçoit 10 jours de Fidelo complet, puis bascule
   automatiquement en Découverte — il ne perd JAMAIS ses clients.
   ================================================================== */
/* ---------------- éditeur du service (pages légales) ----------------
   Source unique : mentions légales, politique de confidentialité, factures. */
define('EDITEUR', [
  'marque'    => 'Fidelo',
  'societe'   => 'AK DEV',
  'dirigeant' => 'Hammou-Boutrig Abdelkader',
  'forme'     => 'Entreprise individuelle',
  'adresse'   => '4 avenue du Maréchal de Lattre de Tassigny',
  'cp_ville'  => '94000 Créteil, France',
  'siret'     => '991 470 212 00010',
  'tva'       => 'TVA non applicable, art. 293 B du CGI',
  'email'     => 'contact@fidelo.site',
  'inbox'     => 'kaderhb33@gmail.com',                 // où arrivent réellement les messages du formulaire
  'tel'       => '+33 7 45 92 95 20',
  'tel_raw'   => '33745929520',
  'maps'      => 'https://maps.app.goo.gl/e4fkWmbeKHQstK9a8',
  'site'      => 'https://fidelo.site',
  'hebergeur' => 'Namecheap, Inc. — 4600 East Washington Street, Suite 305, Phoenix, AZ 85034, États-Unis — namecheap.com',
  'maj'       => '17 septembre 2026',
]);

define('PLAN_FREE_MAX', 30);          // clients inclus dans la formule gratuite
define('TARIFS', ['mensuel' => 29, 'annuel' => 250, 'avie' => 525, 'domaine' => 50]);

function plan_of(array $s): string {
  $p = $s['plan'] ?? '';
  if (in_array($p, ['mensuel','annuel','avie'], true)) return $p;
  return 'decouverte';
}
function plan_illimite(array $s): bool { return plan_of($s) !== 'decouverte'; }
/* Essai complet encore en cours ? (10 jours offerts à l'inscription) */
function essai_actif(array $s): bool {
  return plan_of($s) === 'decouverte' && (int)($s['trialEnds'] ?? 0) > now();
}
/* Combien de clients ce commerce peut-il encore inscrire ? null = illimité */
function quota_restant(array $s, int $nClients): ?int {
  if (plan_illimite($s) || essai_actif($s)) return null;
  return max(0, PLAN_FREE_MAX - $nClients);
}

/* ---------------- codes promo ---------------- */
function promo_norm(string $c): string { return strtoupper(preg_replace('/[^A-Za-z0-9-]/', '', $c)); }

/* Retourne le code s'il est utilisable, sinon un motif de refus. */
function promo_check(array $db, string $code): array {
  $code = promo_norm($code);
  if ($code === '') return ['ok' => false, 'error' => 'vide'];
  $p = $db['settings']['promos'][$code] ?? null;
  if (!$p) return ['ok' => false, 'error' => 'inconnu'];
  if (empty($p['actif'])) return ['ok' => false, 'error' => 'inactif'];
  if (!empty($p['jusqu']) && (int)$p['jusqu'] < now()) return ['ok' => false, 'error' => 'expire'];
  if (!empty($p['max']) && (int)($p['utilise'] ?? 0) >= (int)$p['max'])
    return ['ok' => false, 'error' => 'epuise'];
  return ['ok' => true, 'code' => $code, 'promo' => $p,
          'restant' => !empty($p['max']) ? (int)$p['max'] - (int)($p['utilise'] ?? 0) : null];
}

/* Applique un code à un commerce (à l'inscription). */
function promo_apply(array &$db, string $shopId, string $code): array {
  $v = promo_check($db, $code);
  if (!$v['ok']) return $v;
  $ref = &shop_ref($db, $shopId);
  if (!$ref) return ['ok' => false, 'error' => 'commerce'];
  $p = $v['promo'];
  switch ($p['type']) {
    case 'jours':                                  // essai complet rallongé
      $base = max(now(), (int)($ref['trialEnds'] ?? 0));
      $ref['trialEnds'] = $base + (int)$p['valeur'] * 86400;
      break;
    case 'remise':                                 // réduction sur le 1er paiement
    case 'avie':                                   // tarif de lancement à vie
      break;                                       // appliqué au paiement
  }
  $ref['promo'] = ['code' => $v['code'], 'at' => now(), 'type' => $p['type'], 'valeur' => $p['valeur'] ?? 0];
  $db['settings']['promos'][$v['code']]['utilise'] = (int)($p['utilise'] ?? 0) + 1;
  return ['ok' => true, 'code' => $v['code'], 'label' => $p['label'] ?? ''];
}

/* ---------------- parrainage ---------------- */
function parrain_code(array $s): string { return 'FID-' . strtoupper($s['shortCode'] ?? substr($s['id'], 1, 4)); }
function shop_by_parrain(array $db, string $code): ?array {
  $code = promo_norm($code);
  foreach ($db['shops'] as $s) if (parrain_code($s) === $code) return $s;
  return null;
}
/* Enregistre le lien parrain → filleul et crédite les deux de 2 mois. */
function parrain_link(array &$db, string $filleulId, string $code): bool {
  $par = shop_by_parrain($db, $code);
  if (!$par || $par['id'] === $filleulId) return false;
  $f = &shop_ref($db, $filleulId);
  if (!$f || !empty($f['parrain'])) return false;
  $f['parrain'] = $par['id'];
  $f['trialEnds'] = max(now(), (int)($f['trialEnds'] ?? 0)) + 60 * 86400;   // 2 mois au filleul
  $p = &shop_ref($db, $par['id']);
  if ($p) {
    $p['filleuls'] = (int)($p['filleuls'] ?? 0) + 1;
    $p['creditMois'] = (int)($p['creditMois'] ?? 0) + 2;                    // 2 mois au parrain
    $p['creditHisto'] = $p['creditHisto'] ?? [];
    $p['creditHisto'][] = ['at' => now(), 'shop' => $f['name'], 'mois' => 2];
    if (count($p['creditHisto']) > 50) $p['creditHisto'] = array_slice($p['creditHisto'], -50);
  }
  return true;
}

/* Codes fournis à l'installation (modifiables depuis la console). */
function promos_defaut(): array {
  return [
    'LANCEMENT15' => ['type'=>'avie','valeur'=>525,'label'=>"À vie + site web à 525 € au lieu de 890 €",
                      'max'=>15,'utilise'=>0,'actif'=>true,'jusqu'=>0],
    'PARRAIN'     => ['type'=>'jours','valeur'=>60,'label'=>"2 mois de Fidelo complet offerts",
                      'max'=>0,'utilise'=>0,'actif'=>true,'jusqu'=>0],
    'QUARTIER'    => ['type'=>'jours','valeur'=>30,'label'=>"1 mois complet offert en plus",
                      'max'=>0,'utilise'=>0,'actif'=>true,'jusqu'=>0],
  ];
}
