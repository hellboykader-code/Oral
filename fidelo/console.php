<?php
/* ==================================================================
   Fidelo — Console propriétaire (Part 5). Accès : ?k=<ADMIN_KEY>.
   La clé n'est JAMAIS renvoyée au navigateur ni stockée en clair côté
   client : elle ouvre une session admin, puis on travaille en session.
   ================================================================== */
require __DIR__ . '/lib.php'; require __DIR__ . '/gwallet.php'; require __DIR__ . '/stripe.php'; fidelo_session();
$LOCK = db_lock();   // sérialise les écritures concurrentes (lecture-modification-écriture)
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

/* ==================================================================
   Accès propriétaire — deux portes :
     1. mot de passe (la porte normale, une fois configuré)
     2. clé de secours ?k=… (dépannage, si le mot de passe est perdu)
   Les deux sont limitées en tentatives, et la session expire.
   ================================================================== */
const ADMIN_TTL = 8 * 3600;              // 8 h d'inactivité → reconnexion

function admin_open(string $via = 'key'): void {
  session_regenerate_id(true);
  $_SESSION['admin'] = true;
  $_SESSION['admin_at'] = now();
  $_SESSION['admin_via'] = $via;              // 'key' (clé de secours) ou 'pass'
  $_SESSION['acsrf'] = tok(24);
}
function admin_close(): void {
  unset($_SESSION['admin'], $_SESSION['admin_at'], $_SESSION['acsrf']);
}

$dbA = db_load();
$adminCfg = $dbA['settings']['admin'] ?? [];
$hasPass = !empty($adminCfg['passHash']);

/* clé de secours (redirigée aussitôt : elle ne reste pas dans la barre d'adresse) */
if (isset($_GET['k'])) {
  if (rate_hit($dbA, 'adminkey:' . client_ip(), 10, 900)
      && strlen(ADMIN_KEY) >= 16 && hash_equals(ADMIN_KEY, (string)$_GET['k'])) admin_open('key');
  db_save($dbA);
  header('Location: ' . ($base ?: '') . '/console.php'); exit;
}

/* connexion par mot de passe */
if (($_POST['a'] ?? '') === 'admin_login') {
  $db = $dbA;
  if (!rate_hit($db, 'adminlog:' . client_ip(), 10, 900)) { db_save($db); json_out(['ok'=>false,'error'=>'ratelimit'],429); }
  $u = mb_strtolower(trim($_POST['user'] ?? ''));
  $pw = (string)($_POST['pass'] ?? '');
  db_save($db);
  if ($hasPass && hash_equals(mb_strtolower((string)($adminCfg['user'] ?? '')), $u)
      && password_verify($pw, $adminCfg['passHash'])) { admin_open('pass'); json_out(['ok'=>true]); }
  json_out(['ok'=>false,'error'=>'bad'],401);
}

/* déconnexion */
if (($_POST['a'] ?? '') === 'admin_logout') { admin_close(); json_out(['ok'=>true]); }

/* expiration de session */
if (!empty($_SESSION['admin']) && (now() - (int)($_SESSION['admin_at'] ?? 0)) > ADMIN_TTL) admin_close();
if (!empty($_SESSION['admin'])) $_SESSION['admin_at'] = now();   // prolonge tant qu'on travaille

$isAdmin = !empty($_SESSION['admin']);

/* ---- API admin (POST) ---- */
$a = $_POST['a'] ?? '';
if ($a !== '') {
  if (!$isAdmin) json_out(['ok'=>false,'error'=>'auth'],401);
  if (!hash_equals($_SESSION['acsrf'] ?? '', $_POST['_csrf'] ?? '')) json_out(['ok'=>false,'error'=>'csrf'],403);
  $db = db_load();
  if ($a === 'add') {
    $tmpPass = bin2hex(random_bytes(4)); $tmpPin = (string)random_int(1000, 9999);
    $r = shop_create($db, ['name'=>$_POST['name']??'','type'=>$_POST['type']??'Commerce',
      'email'=>$_POST['email']??'','pass'=>$tmpPass,'pin'=>$tmpPin]);
    if ($r['ok']) json_out(['ok'=>true,'email'=>mb_strtolower(trim($_POST['email']??'')),'pass'=>$tmpPass,'pin'=>$tmpPin]);
    json_out(['ok'=>false,'error'=>$r['error']]);
  }
  if ($a === 'reset') {
    $s = &shop_ref($db, $_POST['id'] ?? ''); if (!$s) json_out(['ok'=>false,'error'=>'notfound'],404);
    $np = bin2hex(random_bytes(4)); $npin = (string)random_int(1000, 9999);
    $s['passHash'] = password_hash($np, PASSWORD_DEFAULT);
    $s['pinHash']  = password_hash($npin, PASSWORD_DEFAULT);
    $s['devices']  = []; // révoque les appareils de confiance
    db_save($db);
    json_out(['ok'=>true,'email'=>$s['email'],'pass'=>$np,'pin'=>$npin]);
  }
  if ($a === 'status') {
    $id=$_POST['id']??''; $st=$_POST['st']??'';
    if(!in_array($st,['active','trial','impaye','annule'],true)) json_out(['ok'=>false],400);
    $s=&shop_ref($db,$id); if(!$s) json_out(['ok'=>false,'error'=>'notfound'],404);
    /* on date le passage en impayé : c'est le point de départ du délai de grâce */
    if ($st === 'impaye' && ($s['status'] ?? '') !== 'impaye') $s['impayeDepuis'] = now();
    if ($st === 'active') unset($s['impayeDepuis']);
    $s['status']=$st; db_save($db); json_out(['ok'=>true]);
  }
  /* Formule (quota) : Découverte reste plafonnée à PLAN_FREE_MAX ; les
     3 autres formules débloquent le quota illimité (voir plan_of() dans lib.php).
     Séparé du statut de paiement ci-dessus : un commerce peut être "actif"
     sur n'importe quelle formule. */
  if ($a === 'plan') {
    $id=$_POST['id']??''; $pl=$_POST['pl']??'';
    if(!in_array($pl,['decouverte','mensuel','annuel','avie'],true)) json_out(['ok'=>false],400);
    $s=&shop_ref($db,$id); if(!$s) json_out(['ok'=>false,'error'=>'notfound'],404);
    $s['plan']=$pl; db_save($db); json_out(['ok'=>true]);
  }
  /* Codes promo : lister, créer, modifier, supprimer. */
  if ($a === 'promos') {
    $out = [];
    foreach (($db['settings']['promos'] ?? []) as $code => $p) $out[] = [
      'code'=>$code,'type'=>$p['type'],'valeur'=>(int)($p['valeur'] ?? 0),
      'label'=>$p['label'] ?? '','max'=>(int)($p['max'] ?? 0),
      'utilise'=>(int)($p['utilise'] ?? 0),'actif'=>!empty($p['actif']),
      'jusqu'=>(int)($p['jusqu'] ?? 0)];
    usort($out, fn($x,$y)=>$y['utilise'] <=> $x['utilise']);
    json_out(['ok'=>true,'promos'=>$out]);
  }
  if ($a === 'promo_set') {
    $code = promo_norm($_POST['code'] ?? '');
    if (strlen($code) < 3) json_out(['ok'=>false,'error'=>'code'],400);
    $type = in_array($_POST['type'] ?? '', ['jours','remise','avie'], true) ? $_POST['type'] : 'jours';
    $anc = $db['settings']['promos'][$code] ?? [];
    $db['settings']['promos'][$code] = [
      'type'=>$type,
      'valeur'=>max(0, (int)($_POST['valeur'] ?? 0)),
      'label'=>mb_substr(trim($_POST['label'] ?? ''), 0, 90),
      'max'=>max(0, (int)($_POST['max'] ?? 0)),                 // 0 = illimité
      'utilise'=>(int)($anc['utilise'] ?? 0),
      'actif'=>!empty($_POST['actif']),
      'jusqu'=>!empty($_POST['jusqu']) ? strtotime($_POST['jusqu'] . ' 23:59:59') : 0,
    ];
    db_save($db);
    json_out(['ok'=>true,'code'=>$code]);
  }
  if ($a === 'promo_del') {
    $code = promo_norm($_POST['code'] ?? '');
    unset($db['settings']['promos'][$code]);
    db_save($db);
    json_out(['ok'=>true]);
  }

  /* Vue du parrainage : qui a amené qui. */
  if ($a === 'parrainages') {
    $noms = []; foreach ($db['shops'] as $s) $noms[$s['id']] = $s['name'];
    $out = [];
    foreach ($db['shops'] as $s) {
      if (empty($s['parrain']) && empty($s['filleuls'])) continue;
      $out[] = ['nom'=>$s['name'],'code'=>parrain_code($s),
        'filleuls'=>(int)($s['filleuls'] ?? 0),'credit'=>(int)($s['creditMois'] ?? 0),
        'parrain'=>$noms[$s['parrain'] ?? ''] ?? ''];
    }
    usort($out, fn($x,$y)=>$y['filleuls'] <=> $x['filleuls']);
    json_out(['ok'=>true,'lignes'=>$out]);
  }

  /* Sauvegardes : lister et restaurer. Sans cela, une sauvegarde ne sert à rien. */
  if ($a === 'backups') {
    $out = [];
    foreach (glob(DATA_DIR . '/backups/db-*.json.gz') ?: [] as $f) $out[] = [
      'f' => basename($f), 'ko' => (int)round(filesize($f)/1024), 'at' => filemtime($f)];
    usort($out, fn($x,$y)=>$y['at'] <=> $x['at']);
    json_out(['ok'=>true,'backups'=>$out]);
  }
  /* Sauvegarde immédiate, avant une manipulation risquée par exemple. */
  if ($a === 'backup_now') {
    $f = DATA_DIR . '/backups/db-' . date('Y-m-d') . '.json.gz';
    if (!backup_write($f)) json_out(['ok'=>false,'error'=>'ecriture'],500);
    json_out(['ok'=>true,'ko'=>(int)round((filesize($f) ?: 0)/1024)]);
  }

  if ($a === 'restore') {
    $f = basename((string)($_POST['f'] ?? ''));
    if (!preg_match('/^db-\d{4}-\d{2}-\d{2}\.json\.gz$/', $f)) json_out(['ok'=>false,'error'=>'nom'],400);
    $path = DATA_DIR . '/backups/' . $f;
    if (!is_file($path)) json_out(['ok'=>false,'error'=>'introuvable'],404);
    $raw = gzdecode((string)file_get_contents($path));
    $arc = json_decode((string)$raw, true);
    if (!is_array($arc) || empty($arc['index']['shops'])) json_out(['ok'=>false,'error'=>'illisible'],400);
    /* on met l'état actuel de côté avant d'écraser : une restauration par
       erreur ne doit pas être définitive non plus */
    $avant = DATA_DIR . '/backups/avant-restauration-' . date('Ymd-His') . '.json.gz';
    $etat = ['index'=>jread(DB_FILE),'shops'=>[],'cards'=>cards_load()];
    foreach (glob(SHOPS_DIR . '/*.json') ?: [] as $sf) $etat['shops'][basename($sf,'.json')] = jread($sf);
    @file_put_contents($avant, gzencode(json_encode($etat) ?: '', 6));
    jwrite(DB_FILE, $arc['index']);
    foreach (glob(SHOPS_DIR . '/*.json') ?: [] as $sf) @unlink($sf);
    foreach (($arc['shops'] ?? []) as $id => $d) jwrite(shard_file((string)$id), $d ?: ['clients'=>[],'events'=>[]]);
    cards_save($arc['cards'] ?? []);
    json_out(['ok'=>true,'commerces'=>count($arc['index']['shops']),
      'clients'=>array_sum(array_map(fn($d)=>count($d['clients'] ?? []), $arc['shops'] ?? []))]);
  }

  /* Diagnostic de sécurité : ce que le propriétaire doit vérifier. */
  if ($a === 'audit') {
    $out = [];
    $scheme = is_https() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $root = $scheme . '://' . $host . ($base ?: '');

    // 1. le dossier des données est-il atteignable depuis le web ?
    $exposed = null;
    if (function_exists('curl_init')) {
      $ch = curl_init($root . '/data/db.json');
      /* on ne lit que les premiers octets : le test doit rester léger et rapide */
      curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>4,
        CURLOPT_CONNECTTIMEOUT=>2, CURLOPT_SSL_VERIFYPEER=>false,
        CURLOPT_RANGE=>'0-300', CURLOPT_USERAGENT=>'Fidelo-selftest']);
      $body = (string)curl_exec($ch);
      $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      $exposed = ($code === 200 && str_contains($body, '"shops"'));
    }
    $out[] = ['k'=>'data','ok'=>$exposed === false,'na'=>$exposed === null,
      'label'=>"Base de données inaccessible depuis le web",
      'fix'=>"Le fichier data/db.json répond en HTTP : votre hébergeur n'applique pas .htaccess. Contactez-le (AllowOverride) ou déplacez data/ hors de public_html."];

    // 2. HTTPS
    $out[] = ['k'=>'https','ok'=>is_https(),'label'=>"Site servi en HTTPS",
      'fix'=>"Activez le certificat SSL (AutoSSL dans cPanel) : sans HTTPS, ni la caméra ni les notifications ne fonctionnent."];

    // 3. mot de passe propriétaire
    $out[] = ['k'=>'pass','ok'=>!empty($db['settings']['admin']['passHash']),
      'label'=>"Console protégée par mot de passe",
      'fix'=>"Cliquez sur « 🔐 Sécurité » et définissez un mot de passe : la clé dans l'URL ne suffit pas."];

    // 4. clé de secours changée
    $out[] = ['k'=>'key','ok'=>ADMIN_KEY !== 'fidelo-admin-CHANGEZ-MOI' && strlen(ADMIN_KEY) >= 20,
      'label'=>"Clé de secours personnalisée et longue",
      'fix'=>"Modifiez ADMIN_KEY dans lib.php (au moins 20 caractères aléatoires)."];

    // 5. comptes de démonstration livrés avec un mot de passe connu
    /* Tout compte d'exemple est à supprimer : soit il garde un mot de passe
       public, soit il pollue simplement la base en production. */
    $demo = []; $faible = 0;
    foreach ($db['shops'] as $sh) {
      if (!preg_match('/@(exemple|example|demo|test)\\.(fr|com|org)$/i', $sh['email'] ?? '')) continue;
      $demo[] = $sh['name'];
      foreach (['demo1234','test1234','password','12345678','motdepasse'] as $mdp)
        if (password_verify($mdp, $sh['passHash'] ?? '')) { $faible++; break; }
    }
    $out[] = ['k'=>'demo','ok'=>!$demo,'label'=>"Aucun commerce de démonstration en base",
      'n'=>count($demo), 'noms'=>$demo, 'faible'=>$faible,
      'fix'=>($faible ? $faible . " de ces comptes ont un mot de passe deviné en une seconde : n'importe qui entre dans l'espace et voit les clients. "
                      : "Ces comptes d'exemple n'ont rien à faire en production. ")
             . "Supprimez-les d'un clic ci-dessous."];

    // 6. sauvegardes
    $bk = glob(DATA_DIR . '/backups/db-*.json') ?: [];
    $out[] = ['k'=>'backup','ok'=>count($bk) > 0,'n'=>count($bk),
      'label'=>"Sauvegardes quotidiennes en place",
      'fix'=>"Aucune sauvegarde trouvée : vérifiez que data/backups est accessible en écriture."];

    // 7. notifications
    $out[] = ['k'=>'push','ok'=>!empty($db['settings']['vapidPem']),
      'label'=>"Notifications push prêtes",'fix'=>"Elles se créent automatiquement au premier envoi."];

    // 8. volume des données — alerte bien avant tout problème
    $idx = @filesize(DB_FILE) ?: 0;
    $shards = glob(SHOPS_DIR . '/*.json') ?: [];
    $total = $idx + array_sum(array_map(fn($f)=>filesize($f) ?: 0, $shards)) + (@filesize(CARDS_FILE) ?: 0);
    $plusGros = 0; foreach ($shards as $f) $plusGros = max($plusGros, filesize($f) ?: 0);
    $out[] = ['k'=>'volume','ok'=>$plusGros < 6*1024*1024,
      'label'=>"Volume des données",
      'n'=>(int)round($total/1024), 'gros'=>(int)round($plusGros/1024), 'shops'=>count($shards),
      'fix'=>"Un commerce dépasse 6 Mo de clients. Tout fonctionne encore, mais prévenez-moi pour passer au stockage par tranches."];

    // 9. espace disque
    $free = @disk_free_space(DATA_DIR);
    $out[] = ['k'=>'disk','ok'=>$free === false || $free > 20*1024*1024,
      'label'=>"Espace disque suffisant",
      'n'=>$free ? round($free/1048576) : 0,
      'fix'=>"Moins de 20 Mo libres : libérez de la place, sinon les écritures échoueront."];

    json_out(['ok'=>true,'checks'=>$out]);
  }

  /* Suppression en un clic des comptes de démonstration. */
  if ($a === 'demo_clean') {
    $del = [];
    foreach ($db['shops'] as $sh)
      if (preg_match('/@(exemple|example|demo|test)\.(fr|com|org)$/i', $sh['email'] ?? '')) $del[] = $sh['id'];
    if (!$del) json_out(['ok'=>true,'n'=>0]);
    $db['shops'] = array_values(array_filter($db['shops'], fn($o)=>!in_array($o['id'], $del, true)));
    db_save($db);
    json_out(['ok'=>true,'n'=>count($del)]);
  }

  /* Inscription publique ouverte ou fermée. */
  if ($a === 'signup_set') {
    $db['settings']['signupOpen'] = !empty($_POST['open']);
    if (isset($_POST['grace'])) $db['settings']['grace'] = max(0, min(365, (int)$_POST['grace']));
    db_save($db);
    json_out(['ok'=>true,'open'=>$db['settings']['signupOpen'],'grace'=>$db['settings']['grace'] ?? 14]);
  }

  /* Identifiants du propriétaire. Si un mot de passe existe déjà,
     l'ancien est exigé — même connecté. */
  if ($a === 'admin_set') {
    $cur = $db['settings']['admin'] ?? [];
    $u = mb_strtolower(trim($_POST['user'] ?? ''));
    $new = (string)($_POST['new'] ?? '');
    if (!filter_var($u, FILTER_VALIDATE_EMAIL)) json_out(['ok'=>false,'error'=>'user'],400);
    if (strlen($new) < 10) json_out(['ok'=>false,'error'=>'short'],400);
    $viaKey = (($_SESSION['admin_via'] ?? '') === 'key');   // entré par clé de secours = réinitialisation autorisée
    if (!$viaKey && !empty($cur['passHash']) && !password_verify((string)($_POST['old'] ?? ''), $cur['passHash']))
      json_out(['ok'=>false,'error'=>'old'],403);
    $db['settings']['admin'] = ['user'=>$u, 'passHash'=>password_hash($new, PASSWORD_DEFAULT), 'at'=>now()];
    db_save($db);
    json_out(['ok'=>true]);
  }

  /* Fiche commerce : le propriétaire corrige nom, type, e-mail. */
  if ($a === 'shop_edit') {
    $sh = &shop_ref($db, $_POST['id'] ?? ''); if (!$sh) json_out(['ok'=>false,'error'=>'notfound'],404);
    $name = mb_substr(trim($_POST['name'] ?? ''), 0, 60);
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    if ($name === '') json_out(['ok'=>false,'error'=>'name'],400);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_out(['ok'=>false,'error'=>'email'],400);
    foreach ($db['shops'] as $o)                       // e-mail unique
      if ($o['id'] !== $sh['id'] && mb_strtolower($o['email']) === $email)
        json_out(['ok'=>false,'error'=>'exists'],400);
    $sh['name'] = $name; $sh['email'] = $email;
    $t = trim($_POST['type'] ?? '');
    if (in_array($t, ['Café','Restaurant','Boulangerie','Salon','Commerce'], true)) $sh['type'] = $t;
    $sh['brandAt'] = now();                            // logo/bandeau à régénérer
    foreach (glob(__DIR__ . '/data/brand/' . $sh['id'] . '-*-*.png') ?: [] as $f) @unlink($f);
    db_save($db);
    json_out(['ok'=>true]);
  }

  /* Suppression d'un commerce — le nom exact est exigé en confirmation. */
  if ($a === 'shop_del') {
    $id = (string)($_POST['id'] ?? '');
    $sh = null;
    foreach ($db['shops'] as $o) if ($o['id'] === $id) { $sh = $o; break; }
    if (!$sh) json_out(['ok'=>false,'error'=>'notfound'],404);
    if (trim((string)($_POST['confirm'] ?? '')) !== $sh['name']) json_out(['ok'=>false,'error'=>'confirm'],400);
    $nCli = (int)($sh['nClients'] ?? 0);
    $db['shops'] = array_values(array_filter($db['shops'], fn($o) => $o['id'] !== $id));
    foreach (glob(__DIR__ . '/data/brand/' . $id . '-*.png') ?: [] as $f) @unlink($f);
    db_save($db);
    json_out(['ok'=>true,'clients'=>$nCli]);
  }

  /* Les clients d'un commerce, vus depuis la console. */
  if ($a === 'shop_clients') {
    $wanted = (string)($_POST['id'] ?? '');
    shop_hydrate($db, $wanted);
    $sh = null;
    foreach ($db['shops'] as $o) if ($o['id'] === $wanted) { $sh = $o; break; }
    if (!$sh) json_out(['ok'=>false,'error'=>'notfound'],404);
    $list = [];
    foreach ($sh['clients'] ?? [] as $c) $list[] = [
      'id'=>$c['id'],'name'=>$c['name'],'tel'=>$c['tel'] ?? '','points'=>(int)$c['points'],
      'visits'=>(int)$c['visits'],'card'=>$c['card'] ?? '','last'=>(int)($c['last'] ?? 0)];
    usort($list, fn($a2,$b2) => $b2['last'] <=> $a2['last']);
    json_out(['ok'=>true,'shop'=>['id'=>$sh['id'],'name'=>$sh['name']],'clients'=>$list]);
  }

  /* Ouvrir l'espace d'un commerçant pour l'aider (dépannage). */
  if ($a === 'impersonate') {
    $sh = null;
    foreach ($db['shops'] as $o) if ($o['id'] === ($_POST['id'] ?? '')) { $sh = $o; break; }
    if (!$sh) json_out(['ok'=>false,'error'=>'notfound'],404);
    $_SESSION['shop_id'] = $sh['id'];
    $_SESSION['csrf'] = tok(24);
    $_SESSION['as_admin'] = true;                      // trace : session ouverte par le propriétaire
    json_out(['ok'=>true,'url'=>($base ?: '') . '/index.php']);
  }

  if ($a === 'gw_set') {
    /* Google Wallet : Issuer ID + JSON du compte de service.
       Le JSON n'est jamais renvoyé au navigateur (comme un mot de passe). */
    $issuer = preg_replace('/[^0-9]/', '', $_POST['issuer'] ?? '');
    $sa = trim((string)($_POST['sa'] ?? ''));
    $db['settings']['gw'] = $db['settings']['gw'] ?? [];
    if ($issuer !== '') $db['settings']['gw']['issuer'] = $issuer;
    $db['settings']['gw']['live'] = !empty($_POST['live']);
    if ($sa !== '' && $sa !== '••••••••') {
      $j = json_decode($sa, true);
      if (!$j || empty($j['client_email']) || empty($j['private_key']))
        json_out(['ok'=>false,'error'=>'json'],400);
      $db['settings']['gw']['sa'] = $sa;
      unset($db['settings']['gw']['tok']);           // nouveau compte → nouveau jeton
    }
    db_save($db);
    $c = gw_conf($db);
    json_out(['ok'=>true,'ready'=>$c['ok'],'email'=>$c['email']]);
  }

  if ($a === 'gw_test') {
    /* Vérifie la chaîne complète : jeton OAuth + accès à l'émetteur. */
    $c = gw_conf($db);
    if (!$c['ok']) json_out(['ok'=>false,'error'=>'config']);
    if (!gw_token($db)) json_out(['ok'=>false,'error'=>'token',
      'hint'=>"Clé du compte de service refusée par Google."]);
    $r = gw_api($db, 'GET', '/issuer/' . rawurlencode($c['issuer']));
    if ($r['code'] === 200) json_out(['ok'=>true,'name'=>$r['body']['name'] ?? $c['issuer']]);
    if ($r['code'] === 403) json_out(['ok'=>false,'error'=>'access',
      'hint'=>"Ajoutez " . $c['email'] . " comme utilisateur (rôle Developer) dans pay.google.com/business/console."]);
    if ($r['code'] === 404) json_out(['ok'=>false,'error'=>'issuer','hint'=>"Issuer ID introuvable."]);
    json_out(['ok'=>false,'error'=>'http'.$r['code'],'hint'=>substr(json_encode($r['body']),0,300)]);
  }

  if ($a === 'stripe_set') {
    /* Clé secrète + secret de webhook : jamais renvoyés au navigateur
       (comme le JSON Google Wallet ci-dessus). '••••••••' = inchangé. */
    $sec = trim((string)($_POST['secret'] ?? ''));
    $wh  = trim((string)($_POST['whsec'] ?? ''));
    $db['settings']['stripe'] = $db['settings']['stripe'] ?? [];
    if ($sec !== '' && $sec !== '••••••••') {
      if (!preg_match('/^sk_(live|test)_/', $sec)) json_out(['ok'=>false,'error'=>'secret'],400);
      $db['settings']['stripe']['secret'] = $sec;
    }
    if ($wh !== '' && $wh !== '••••••••') {
      if (!preg_match('/^whsec_/', $wh)) json_out(['ok'=>false,'error'=>'whsec'],400);
      $db['settings']['stripe']['whsec'] = $wh;
    }
    db_save($db);
    json_out(['ok'=>true,'ready'=>st_on($db)]);
  }

  if ($a === 'stripe_prices') {
    /* Crée (si besoin) les 3 tarifs Fidelo sur Stripe. Idempotent. */
    $r = st_ensure_prices($db);
    json_out($r, $r['ok']?200:500);
  }

  json_out(['ok'=>false,'error'=>'unknown'],400);
}

/* ---- données pour l'affichage ---- */
$acsrf = $_SESSION['acsrf'] ?? '';
$db = $isAdmin ? db_load() : ['shops'=>[]];
$PRICE = $db['settings']['price'] ?? 29;
$shops = $db['shops'] ?? [];
$actifs = array_filter($shops, fn($s)=>($s['status']??'')==='active');
$mrr = count($actifs)*$PRICE;
$essais = count(array_filter($shops, fn($s)=>($s['status']??'')==='trial'));
$impayes = count(array_filter($shops, fn($s)=>($s['status']??'')==='impaye'));
$te = ['Café'=>'☕','Restaurant'=>'🍽️','Boulangerie'=>'🥐','Salon'=>'💈','Commerce'=>'🛍️'];
$stl = ['active'=>'Actif','trial'=>'Essai','impaye'=>'Impayé','annule'=>'Annulé'];
$pll = ['decouverte'=>'Découverte (30 max)','mensuel'=>'Mensuel','annuel'=>'Annuel','avie'=>'À vie'];
function ini2($n){return strtoupper(mb_substr(preg_replace('/\s+/','',$n),0,2));}
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title>Console Fidelo</title><meta name="theme-color" content="#0A2E38">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Instrument+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap">
<style>
:root{--em:#06B6D4;--em-d:#0891B2;--or:#D9A94E;--or-l:#f0d488;--iv:#F3FCFD;--me:#E7F8FA;--me-d:#D6EEF1;--card:#fff;--line:#D6EEF1;--text:#1c2a25;--muted:#5a675f;--faint:#8a958e;--deep:#0A2E38;--deep2:#0F3D49;
--good:#06B6D4;--good-b:#e2f1ea;--warn:#c98a1e;--warn-b:#f7ecd6;--bad:#c0492f;--bad-b:#f7e2dc;--off:#8a958e;--off-b:#E2F2F4;
--disp:"Bricolage Grotesque",system-ui,sans-serif;--body:"Instrument Sans",system-ui,sans-serif;--mono:"IBM Plex Mono",monospace;}
@media(prefers-color-scheme:dark){:root{--em:#22D3EE;--em-d:#0891B2;--or:#e3ba63;--iv:#07222A;--me:#0B2C35;--me-d:#183B45;--card:#0E323C;--line:#1C4650;--text:#e6efea;--muted:#9fc4cc;--faint:#728178;
--good:#22D3EE;--good-b:#12312a;--warn:#e0b25f;--warn-b:#33280f;--bad:#e07a63;--bad-b:#3a201a;--off:#728178;--off-b:#1c2c25;}}
*{box-sizing:border-box}body{margin:0;background:var(--iv);color:var(--text);font-family:var(--body);-webkit-font-smoothing:antialiased}
h1,h2,h3{font-family:var(--disp);font-weight:600;letter-spacing:-.02em;margin:0}button{font-family:inherit;cursor:pointer;border:0;background:none;color:inherit}input,select{font-family:inherit}
.mono{font-family:var(--mono);font-variant-numeric:tabular-nums}
.wrap{max-width:1080px;margin:0 auto;padding:20px 20px 60px}
.head{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:22px}
.logo{display:flex;align-items:center;gap:10px}.logo .m{width:42px;height:42px;border-radius:12px;background:linear-gradient(150deg,var(--deep),var(--em-d));color:#fff;display:grid;place-items:center;font-family:var(--disp);font-weight:700;font-size:20px}
.logo .t{font-family:var(--disp);font-weight:700;font-size:20px}.logo .t i{color:var(--or);font-style:normal}.logo .s{font-size:11px;color:var(--faint);font-family:var(--mono)}
.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 18px;border-radius:11px;font-weight:600;font-size:14px}.btn-p{background:var(--em);color:#fff}.btn-p svg{width:16px;height:16px}
.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px}@media(max-width:820px){.kpis{grid-template-columns:1fr 1fr}}
.kpi{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:16px}.kpi.hero{background:linear-gradient(160deg,var(--deep),var(--deep2));color:#eef7f2;border-color:transparent}
.kpi .k{font-size:12px;color:var(--muted)}.kpi.hero .k{color:var(--or-l)}.kpi .v{font-family:var(--disp);font-weight:700;font-size:29px;margin-top:8px}.kpi.hero .v{color:#fff}.kpi .sub{font-size:12px;color:var(--faint);margin-top:6px}.kpi.hero .sub{color:rgba(238,247,242,.6)}
.panel{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:18px}
.ph{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}.ph h3{font-size:16px}.ph .n{font-size:12px;color:var(--faint)}
.tbl{width:100%;border-collapse:collapse}.tbl th{text-align:left;font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--faint);font-weight:600;padding:0 10px 10px;font-family:var(--mono)}
.tbl td{padding:12px 10px;border-top:1px solid var(--line);font-size:14px}.tbl td.r{text-align:right}
.mrow{display:flex;align-items:center;gap:11px}.mrow .av{width:38px;height:38px;border-radius:11px;background:var(--me);color:var(--em-d);display:grid;place-items:center;font-weight:600;font-size:13px}
.mrow .nm{font-weight:600}.mrow .tp{font-size:11.5px;color:var(--faint)}
.pill{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:99px;font-size:12px;font-weight:600}.pill .dot{width:7px;height:7px;border-radius:50%}
.pill.active{background:var(--good-b);color:var(--good)}.pill.active .dot{background:var(--good)}
.pill.trial{background:var(--warn-b);color:var(--warn)}.pill.trial .dot{background:var(--warn)}
.pill.impaye{background:var(--bad-b);color:var(--bad)}.pill.impaye .dot{background:var(--bad)}
.pill.annule{background:var(--off-b);color:var(--off)}.pill.annule .dot{background:var(--off)}
select.st{padding:7px 10px;border-radius:9px;border:1.5px solid var(--line);background:var(--card);color:var(--text);font-size:12.5px;font-weight:600}
.mrr{font-family:var(--mono);font-weight:600}.mrr.z{color:var(--faint)}
.gate{max-width:380px;margin:16vh auto;text-align:center;padding:0 20px}.gate .m{width:56px;height:56px;border-radius:16px;background:var(--deep);color:var(--or);display:grid;place-items:center;font-family:var(--disp);font-weight:700;font-size:26px;margin:0 auto 18px}
.gate input{width:100%;padding:13px;border-radius:12px;border:1.5px solid var(--line);background:var(--card);color:var(--text);font-size:15px;margin:14px 0 10px;text-align:center;font-family:var(--mono)}
.gate .btn{width:100%;justify-content:center}
.mask{position:fixed;inset:0;z-index:100;background:rgba(6,14,11,.5);display:none;place-items:center;padding:20px}.mask.on{display:grid}
@media(max-width:600px){.mask{padding:10px;align-items:start}.mask.on{display:grid}.dlg{max-height:94vh;padding:18px}}
.dlg{background:var(--card);border-radius:20px;padding:22px;max-width:400px;width:100%;max-height:88vh;overflow-y:auto;-webkit-overflow-scrolling:touch}.dlg h3{font-size:19px;margin-bottom:4px}.dlg .sub{font-size:13px;color:var(--muted);margin-bottom:12px}
.dlg label{display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:12px 0 6px}.dlg .inp{width:100%;padding:12px;border-radius:11px;border:1.5px solid var(--line);background:var(--iv);color:var(--text);font-size:14px}
.chips{display:flex;flex-wrap:wrap;gap:8px}.chip{padding:8px 12px;border-radius:10px;border:1.5px solid var(--line);font-size:13.5px;color:var(--muted)}.chip.on{border-color:var(--em);color:var(--em-d);font-weight:600}
.drow{display:flex;gap:9px;margin-top:16px}.drow .btn{flex:1;justify-content:center}.btn-g{background:var(--card);border:1.5px solid var(--line);color:var(--text)}
.toast{position:fixed;left:50%;bottom:24px;transform:translateX(-50%);background:var(--deep);color:#eef7f2;padding:12px 18px;border-radius:12px;font-size:14px;z-index:200;opacity:0;transition:.3s;pointer-events:none}.toast.on{opacity:1}
@media(max-width:680px){.tbl thead{display:none}.tbl,.tbl tbody,.tbl tr,.tbl td{display:block}.tbl tr{border:1px solid var(--line);border-radius:14px;margin-bottom:10px;padding:6px}.tbl td{border:0;padding:6px 12px}.tbl td.r{text-align:left}}
.flogo{width:1.35em;height:1.35em;display:inline-block;vertical-align:-.32em;margin-right:.32em}
</style></head><body>
<?php if(!$isAdmin): ?>
<div class="gate">
  <div class="m">F</div><h2>Console Fidelo</h2>
  <p style="color:var(--muted);font-size:14px;margin-top:8px">Accès réservé au propriétaire.</p>
  <?php if (strlen(ADMIN_KEY) < 16 && !$hasPass): ?>
  <p style="color:#c0492f;font-size:13px;margin-top:10px;font-weight:600">⚠️ Aucun accès valide n'est configuré — la console reste fermée.</p>
  <?php endif; ?>
  <?php if ($hasPass): ?>
  <div style="margin-top:16px;text-align:left">
    <input class="inp" id="alU" type="email" placeholder="Votre e-mail" autocomplete="username" autofocus style="width:100%">
    <input class="inp" id="alP" type="password" placeholder="Mot de passe" autocomplete="current-password" style="width:100%;margin-top:8px">
    <button class="btn btn-p" id="alOk" style="width:100%;margin-top:12px">Entrer</button>
    <div id="alMsg" style="color:#c0492f;font-size:13px;margin-top:10px;min-height:18px"></div>
    <details style="margin-top:12px"><summary style="font-size:12.5px;color:var(--muted);cursor:pointer">Mot de passe perdu ?</summary>
      <p style="font-size:12.5px;color:var(--muted);margin-top:8px">Ouvrez <code>console.php?k=VOTRE_CLÉ</code> (la clé se trouve dans <code>lib.php</code> sur votre serveur), puis redéfinissez votre mot de passe.</p>
    </details>
  </div>
  <script>
  document.getElementById('alOk').onclick=async()=>{
    const b=new URLSearchParams({a:'admin_login',user:document.getElementById('alU').value.trim(),pass:document.getElementById('alP').value});
    const r=await fetch(location.pathname,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:b}).then(x=>x.json()).catch(()=>({ok:false}));
    if(r.ok)location.reload();
    else document.getElementById('alMsg').textContent=r.error==='ratelimit'
      ? 'Trop de tentatives. Réessayez dans 15 minutes.' : 'E-mail ou mot de passe incorrect.';};
  document.getElementById('alP').addEventListener('keydown',e=>{if(e.key==='Enter')document.getElementById('alOk').click();});
  </script>
  <?php else: ?>
  <form method="get" style="margin-top:14px"><input name="k" type="password" placeholder="Clé d'accès" autofocus><button class="btn btn-p" type="submit">Entrer</button></form>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="wrap">
  <?php if (!$hasPass): ?>
  <div style="background:#c0492f;color:#fff;padding:14px 16px;border-radius:12px;margin-bottom:16px;font-size:13.5px;font-weight:600;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <span style="flex:1">⚠️ Votre console n'est protégée que par la clé dans l'URL. Définissez un mot de passe.</span>
    <button class="btn" id="setPwBtn" style="background:#fff;color:#c0492f">Définir un mot de passe</button>
  </div>
  <?php endif; ?>
  <?php if (ADMIN_KEY === 'fidelo-admin-CHANGEZ-MOI' || strlen(ADMIN_KEY) < 16): ?>
  <div style="background:#c0492f;color:#fff;padding:12px 16px;border-radius:12px;margin-bottom:16px;font-size:13.5px;font-weight:600">⚠️ Sécurité : la clé admin par défaut est encore active. Modifiez ADMIN_KEY dans lib.php AVANT la mise en ligne.</div>
  <?php endif; ?>
  <div class="head">
    <div class="logo"><div class="m">F</div><div><div class="t">Console <svg class="flogo" viewBox="0 0 64 64" aria-hidden="true"><defs><linearGradient id="coa" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#06B6D4"/><stop offset=".55" stop-color="#22D3EE"/><stop offset="1" stop-color="#FF6B6B"/></linearGradient><linearGradient id="cob" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".3"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#coa)"/><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#cob)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>Fidelo<i>.</i></div><div class="s">PLATEFORME · PROPRIÉTAIRE</div></div></div>
    <button class="btn btn-g" id="pushBtn" style="background:var(--card);border:1.5px solid var(--line);color:var(--text)"><?= !empty($db['settings']['vapidPub'])?'🔔 Push activé':'🔔 Activer le push' ?></button>
    <button class="btn btn-g" id="secBtn" style="margin-left:auto;background:var(--card);border:1.5px solid var(--line);color:var(--text)">🛡️ Sécurité</button>
    <button class="btn btn-g" id="outBtn" style="background:var(--card);border:1.5px solid var(--line);color:var(--text)" title="Fermer la session">Quitter</button>
    <button class="btn btn-g" id="gwBtn" style="background:var(--card);border:1.5px solid var(--line);color:var(--text)"><?= gw_live($db)?'💳 Wallet actif':(gw_on($db)?'💳 Wallet en attente':'💳 Google Wallet') ?></button>
    <button class="btn btn-g" id="stBtn" style="background:var(--card);border:1.5px solid var(--line);color:var(--text)"><?= st_on($db)?(empty(st_conf($db)['prices'])?'💰 Stripe (tarifs à créer)':'💰 Stripe actif'):'💰 Stripe' ?></button>
    <button class="btn btn-p" id="addBtn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M12 5v14M5 12h14"/></svg> Ajouter un commerce</button>
  </div>
  <div class="kpis">
    <div class="kpi hero"><div class="k">MRR · revenu mensuel</div><div class="v"><?= number_format($mrr,0,',',' ') ?> €</div><div class="sub"><?= count($actifs) ?> abonnés actifs</div></div>
    <div class="kpi"><div class="k">Commerces</div><div class="v"><?= count($shops) ?></div><div class="sub"><?= count($actifs) ?> actifs</div></div>
    <div class="kpi"><div class="k">En essai</div><div class="v"><?= $essais ?></div><div class="sub"><?= $impayes ?> impayé<?= $impayes>1?'s':'' ?></div></div>
    <div class="kpi"><div class="k">Revenu annualisé</div><div class="v"><?= number_format($mrr*12,0,',',' ') ?> €</div><div class="sub">ARR estimé</div></div>
  </div>
  <div class="panel">
    <div class="ph"><h3>Commerces</h3><span class="n"><?= count($shops) ?> au total</span></div>
    <table class="tbl"><thead><tr><th>Commerce</th><th>Statut</th><th>Formule</th><th>Clients</th><th class="r">MRR</th></tr></thead><tbody>
    <?php foreach($shops as $s): $st=$s['status']??'trial'; $pay=$st==='active'; $pl=$s['plan']??'decouverte'; ?>
      <tr>
        <td><div class="mrow"><span class="av"><?= e(ini2($s['name'])) ?></span><div><div class="nm"><button class="opn" data-id="<?= e($s['id']) ?>" style="background:none;border:0;padding:0;font:inherit;color:inherit;cursor:pointer;text-align:left"><?= e($s['name']) ?></button></div><div class="tp"><?= ($te[$s['type']??'Commerce']??'🛍️') ?> <?= e($s['type']??'Commerce') ?> · <?= e($s['email']) ?></div></div></div></td>
        <td><select class="st" data-id="<?= e($s['id']) ?>">
          <?php foreach($stl as $k=>$v): ?><option value="<?= $k ?>" <?= $k===$st?'selected':'' ?>><?= $v ?></option><?php endforeach; ?>
        </select> <button class="rst" data-id="<?= e($s['id']) ?>" data-nm="<?= e($s['name']) ?>" title="Réinitialiser l'accès" style="padding:6px 9px;border-radius:8px;border:1.5px solid var(--line);background:var(--card);cursor:pointer">🔑</button></td>
        <td><select class="pl" data-id="<?= e($s['id']) ?>">
          <?php foreach($pll as $k=>$v): ?><option value="<?= $k ?>" <?= $k===$pl?'selected':'' ?>><?= $v ?></option><?php endforeach; ?>
        </select></td>
        <td class="mono"><?= (int)($s['nClients'] ?? count($s['clients'] ?? [])) ?></td>
        <td class="r"><span class="mrr <?= $pay?'':'z' ?>"><?= $pay?$PRICE.' €':'—' ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>
</div>
<div class="mask" id="mask"><div class="dlg" id="dlg"></div></div>
<div class="toast" id="toast"></div>
<script>
const BASE=<?= json_encode($base) ?>, ACSRF=<?= json_encode($acsrf) ?>;
const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)];
let tT;function toast(m){const t=$('#toast');t.textContent=m;t.classList.add('on');clearTimeout(tT);tT=setTimeout(()=>t.classList.remove('on'),2400);}
async function api(a,d={}){const b=new URLSearchParams({a,_csrf:ACSRF,...d});const r=await fetch(BASE+'/console.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:b});return r.json();}
$$('.st').forEach(sel=>sel.onchange=async()=>{const r=await api('status',{id:sel.dataset.id,st:sel.value});toast(r.ok?'✓ Statut mis à jour':'Erreur');if(r.ok)setTimeout(()=>location.reload(),700);});
$$('.pl').forEach(sel=>sel.onchange=async()=>{const r=await api('plan',{id:sel.dataset.id,pl:sel.value});toast(r.ok?'✓ Formule mise à jour':'Erreur');if(r.ok)setTimeout(()=>location.reload(),700);});
$('#pushBtn').onclick=async()=>{const r=await fetch(BASE+'/push.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'a=keygen'}).then(x=>x.json());
  toast(r.ok?'🔔 Notifications push activées (clés VAPID générées)':'Erreur : '+(r.error||''));if(r.ok)setTimeout(()=>location.reload(),900);};
/* ---- Google Wallet : réglages propriétaire ---- */
const GRACE=<?= (int)($db['settings']['grace'] ?? 14) ?>;
const SIGNUP_OPEN=<?= (($db['settings']['signupOpen'] ?? true) !== false)?'true':'false' ?>;
const GW_LIVE=<?= gw_live($db)?'true':'false' ?>;
const GW_READY=<?= gw_on($db)?'true':'false' ?>, GW_ISSUER=<?= json_encode($db['settings']['gw']['issuer'] ?? '') ?>, GW_MAIL=<?= json_encode(gw_conf($db)['email']) ?>;
$('#gwBtn').onclick=()=>{
  $('#dlg').innerHTML=`<h3>💳 Google Wallet</h3>
    <div class="sub">Les clients Android ajoutent leur carte de fidélité dans Google Wallet en un clic. Les points s'y mettent à jour tout seuls. Gratuit.</div>
    <label>Issuer ID <span style="font-weight:400;color:var(--muted)">(pay.google.com/business/console)</span></label>
    <input class="inp" id="gwI" inputmode="numeric" placeholder="3388000000022…" value="${GW_ISSUER}">
    <label>Clé du compte de service <span style="font-weight:400;color:var(--muted)">(contenu du fichier JSON)</span></label>
    <textarea class="inp" id="gwS" style="min-height:110px;font-family:monospace;font-size:12px" placeholder='{"type":"service_account", …}'>${GW_READY?'••••••••':''}</textarea>
    ${GW_MAIL?`<div class="sub" style="margin-top:8px">Compte de service : <b>${GW_MAIL}</b><br>Il doit être invité en rôle <b>Developer</b> dans la console Google Pay Business.</div>`:''}
    <label style="display:flex;align-items:flex-start;gap:10px;margin-top:14px;cursor:pointer;background:var(--iv);border:1px solid var(--line);border-radius:12px;padding:12px">
      <input type="checkbox" id="gwL" ${GW_LIVE?'checked':''} style="margin-top:3px;width:18px;height:18px">
      <span style="font-size:13px;line-height:1.5"><b>Google a accordé l'accès en publication</b><br>
      <span style="color:var(--muted)">Tant que cette case est décochée, le bouton « Ajouter à Google Wallet » reste masqué chez les clients — ils ne verront jamais le message « carte de test ». Cochez-la le jour où Google valide votre demande.</span></span>
    </label>
    <div id="gwMsg" class="sub" style="margin-top:10px"></div>
    <div class="drow"><button class="btn btn-g" id="gwX">Fermer</button><button class="btn btn-g" id="gwT">Tester</button><button class="btn btn-p" id="gwO">Enregistrer</button></div>`;
  $('#mask').classList.add('on');
  $('#gwX').onclick=()=>$('#mask').classList.remove('on');
  $('#gwO').onclick=async()=>{
    const r=await api('gw_set',{issuer:$('#gwI').value.trim(),sa:$('#gwS').value.trim(),
      live:$('#gwL').checked?'1':''});
    if(r.ok){toast(r.ready?($('#gwL').checked?'✓ Wallet actif chez les clients':'✓ Enregistré — bouton encore masqué'):'Enregistré — il manque une information');setTimeout(()=>location.reload(),900);}
    else toast(r.error==='json'?'Ce JSON n\'est pas une clé de compte de service valide':'Erreur');};
  $('#gwT').onclick=async()=>{
    $('#gwMsg').textContent='Test en cours…';
    const r=await api('gw_test');
    $('#gwMsg').innerHTML=r.ok?'✅ Connexion à Google réussie.':'❌ '+(r.hint||r.error||'échec');};
};

/* ---- Paiement Stripe ---- */
const ST_READY=<?= st_on($db)?'true':'false' ?>, ST_NPRICES=<?= count(st_conf($db)['prices']) ?>, ST_WEBHOOK=<?= json_encode($base.'/stripe-webhook.php') ?>;
$('#stBtn').onclick=()=>{
  $('#dlg').innerHTML=`<h3>💰 Paiement Stripe</h3>
    <div class="sub">Le commerçant passe par Stripe Checkout pour payer sa formule (Mensuel/Annuel/À vie). Stripe confirme le paiement par webhook, qui débloque le quota tout seul — rien à faire ici ensuite.</div>
    <label>Clé secrète <span style="font-weight:400;color:var(--muted)">(Stripe → Developers → API keys, "sk_live_…" ou "sk_test_…")</span></label>
    <input class="inp" id="stS" type="password" placeholder="sk_live_… ou sk_test_…" value="${ST_READY?'••••••••':''}">
    <label>Secret de webhook <span style="font-weight:400;color:var(--muted)">(donné par Stripe à la création du webhook ci-dessous, "whsec_…")</span></label>
    <input class="inp" id="stW" type="password" placeholder="whsec_…">
    <div class="sub" style="margin-top:8px">URL à coller dans Stripe → Developers → Webhooks → « Add endpoint », évènements <b>checkout.session.completed</b>, <b>customer.subscription.updated</b>, <b>customer.subscription.deleted</b> :<br><b style="user-select:all">${ST_WEBHOOK}</b></div>
    <div class="sub" style="margin-top:8px">Tarifs créés sur Stripe : <b>${ST_NPRICES} / 3</b>${ST_NPRICES<3?' — à faire après avoir enregistré la clé ci-dessus.':''}</div>
    <div id="stMsg" class="sub" style="margin-top:6px"></div>
    <div class="drow"><button class="btn btn-g" id="stX">Fermer</button><button class="btn btn-g" id="stP">Créer les tarifs</button><button class="btn btn-p" id="stO">Enregistrer</button></div>`;
  $('#mask').classList.add('on');
  $('#stX').onclick=()=>$('#mask').classList.remove('on');
  $('#stO').onclick=async()=>{
    const r=await api('stripe_set',{secret:$('#stS').value.trim(),whsec:$('#stW').value.trim()});
    if(r.ok){toast(r.ready?'✓ Clé enregistrée':'Enregistré — il manque la clé secrète');setTimeout(()=>location.reload(),900);}
    else toast('Clé/secret invalide — vérifiez le préfixe (sk_… / whsec_…)');};
  $('#stP').onclick=async()=>{
    $('#stMsg').textContent='Création des tarifs sur Stripe…';
    const r=await api('stripe_prices');
    $('#stMsg').innerHTML=r.ok?'✅ 3 tarifs prêts sur Stripe.':'❌ '+(r.error||'échec');
    if(r.ok)setTimeout(()=>location.reload(),900);};
};

/* ---- Sécurité du compte propriétaire ---- */
const HAS_PASS=<?= $hasPass?'true':'false' ?>, VIA_KEY=<?= (($_SESSION['admin_via'] ?? '')==='key')?'true':'false' ?>, ADMIN_USER=<?= json_encode($adminCfg['user'] ?? '') ?>;
/* Diagnostic : ce qui protège (ou non) l'installation, avec le correctif. */
async function openAudit(){
  $('#dlg').innerHTML='<h3>🛡️ Diagnostic de sécurité</h3><div class="sub">Analyse en cours…</div>';
  $('#mask').classList.add('on');
  const r=await api('audit');
  if(!r.ok){$('#dlg').innerHTML='<h3>Erreur</h3><div class="drow"><button class="btn btn-g" onclick="document.getElementById(\'mask\').classList.remove(\'on\')">Fermer</button></div>';return;}
  const ko=r.checks.filter(c=>!c.ok&&!c.na).length;
  const rows=r.checks.map(c=>{
    const icon=c.na?'⚪':(c.ok?'✅':'❌');
    const det=c.k==='demo'&&c.n?` <span style="color:#c0492f">(${c.n} : ${c.noms.map(escH).join(', ')}${c.faible?` — dont ${c.faible} à mot de passe deviné`:''})</span>`
      :c.k==='backup'&&c.ok?` <span style="color:var(--muted)">(${c.n} sauvegarde${c.n>1?'s':''})</span>`
      :c.k==='volume'?` <span style="color:var(--muted)">(${c.n} Ko · ${c.shops} commerce(s) · plus gros : ${c.gros} Ko)</span>`
      :c.k==='disk'&&c.n?` <span style="color:var(--muted)">(${c.n} Mo libres)</span>`:'';
    return `<div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--line)">
      <span style="font-size:16px;line-height:1.3">${icon}</span>
      <div style="flex:1"><div style="font-weight:600;font-size:14px">${escH(c.label)}${det}</div>
      ${c.ok||c.na?'':`<div style="font-size:12.5px;color:var(--muted);margin-top:4px">${escH(c.fix)}</div>`}</div></div>`;}).join('');
  const demo=r.checks.find(c=>c.k==='demo');
  $('#dlg').innerHTML=`<h3>🛡️ Diagnostic de sécurité</h3>
    <div class="sub">${ko?`<b style="color:#c0492f">${ko} point${ko>1?'s':''} à corriger</b>`:'<b style="color:#2f7d5f">Tout est en ordre.</b>'}</div>
    <div style="margin-top:10px;max-height:52vh;overflow:auto">${rows}</div>
    ${demo&&demo.n?`<button class="btn btn-p" id="dcl" style="width:100%;margin-top:14px;background:#c0492f">🗑 Supprimer les ${demo.n} comptes de démonstration</button>`:''}
    <div style="margin-top:14px;background:var(--iv);border:1px solid var(--line);border-radius:12px;padding:12px">
      <div style="font-size:13px;font-weight:600">Délai de grâce après la fin d'abonnement</div>
      <div style="font-size:12.5px;color:var(--muted);margin:4px 0 8px">Passé ce délai, le commerçant consulte encore ses clients mais ne peut plus ajouter de points. 0 = jamais bloquer.</div>
      <div style="display:flex;gap:8px;align-items:center">
        <input class="inp" id="auGrace" type="number" min="0" max="365" value="${GRACE}" style="width:90px;margin:0">
        <span style="font-size:13px;color:var(--muted)">jours</span>
        <button class="btn btn-g" id="auGraceOk" style="margin-left:auto">Enregistrer</button>
      </div>
    </div>
    <label style="display:flex;align-items:flex-start;gap:10px;margin-top:14px;cursor:pointer;background:var(--iv);border:1px solid var(--line);border-radius:12px;padding:12px">
      <input type="checkbox" id="auSignup" ${SIGNUP_OPEN?'checked':''} style="margin-top:3px;width:18px;height:18px">
      <span style="font-size:13px;line-height:1.5"><b>Inscription libre depuis le site</b><br>
      <span style="color:var(--muted)">Décochez pour que personne ne puisse créer un compte commerçant sans passer par vous.</span></span>
    </label>
    <div class="drow"><button class="btn btn-g" id="auX">Fermer</button><button class="btn btn-g" id="auB">💾 Sauvegardes</button><button class="btn btn-g" id="auS">🔐 Mot de passe</button></div>`;
  $('#auGraceOk').onclick=async()=>{
    const x=await api('signup_set',{open:$('#auSignup').checked?'1':'',grace:$('#auGrace').value});
    toast(x.ok?'✓ Délai enregistré':'Erreur');};
  $('#auSignup').onchange=async e=>{
    const x=await api('signup_set',{open:e.target.checked?'1':'',grace:$('#auGrace').value});
    toast(x.ok?(e.target.checked?'Inscription ouverte':'Inscription fermée'):'Erreur');};
  $('#auX').onclick=()=>$('#mask').classList.remove('on');
  $('#auS').onclick=openSecurity;
  $('#auB').onclick=openBackups;
  const d=document.getElementById('dcl');
  if(d)d.onclick=async()=>{
    if(!confirm('Supprimer définitivement les comptes de démonstration et leurs clients ?'))return;
    const x=await api('demo_clean');
    if(x.ok){toast(x.n+' compte(s) supprimé(s)');setTimeout(()=>location.reload(),800);}};
}
async function openBackups(){
  const r=await api('backups');
  const fmt=t=>new Date(t*1000).toLocaleString('fr-FR',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit'});
  const rows=(r.backups||[]).length?r.backups.map(x=>`
    <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--line)">
      <div style="flex:1"><div style="font-weight:600;font-size:13.5px">${escH(x.f.replace('db-','').replace('.json.gz',''))}</div>
        <div style="font-size:11.5px;color:var(--muted)">${fmt(x.at)} · ${x.ko} Ko</div></div>
      <button class="btn btn-g rst" data-f="${escH(x.f)}" style="padding:7px 11px;font-size:12px">Restaurer</button>
    </div>`).join('') : '<div class="sub" style="padding:12px 0">Aucune sauvegarde pour le moment — la première est créée demain.</div>';
  $('#dlg').innerHTML=`<h3>💾 Sauvegardes</h3>
    <div class="sub">Une archive complète par jour : 14 quotidiennes, puis une par mois sur 12 mois. Restaurer remplace les données actuelles — l'état d'avant est archivé lui aussi.</div>
    <div style="max-height:46vh;overflow:auto;margin-top:10px">${rows}</div>
    <button class="btn btn-p" id="bkNow" style="width:100%;margin-top:12px">💾 Sauvegarder maintenant</button>
    <div class="drow"><button class="btn btn-g" id="bkX">Retour</button></div>`;
  $('#bkNow').onclick=async()=>{
    const x=await api('backup_now');
    toast(x.ok?('✓ Sauvegarde créée ('+x.ko+' Ko)'):'Erreur');
    if(x.ok)setTimeout(openBackups,700);};
  $('#bkX').onclick=openAudit;
  $$('.rst').forEach(b=>b.onclick=async()=>{
    if(!confirm('Restaurer la sauvegarde du '+b.dataset.f.replace('db-','').replace('.json.gz','')+' ?\n\nToutes les données actuelles seront remplacées.'))return;
    const x=await api('restore',{f:b.dataset.f});
    if(x.ok){toast('✓ Restauré : '+x.commerces+' commerce(s), '+x.clients+' client(s)');setTimeout(()=>location.reload(),1200);}
    else toast('Erreur : '+(x.error||''));});
}
function openSecurity(){
  $('#dlg').innerHTML=`<h3>🔐 Sécurité de la console</h3>
    <div class="sub">${VIA_KEY?'Vous êtes entré par la clé de secours : définissez un nouveau mot de passe (aucun ancien requis).':(HAS_PASS?'Changez votre e-mail ou votre mot de passe.':'Définissez un accès par mot de passe : la clé dans l\'URL ne servira plus que de secours.')}</div>
    <label>Votre e-mail</label><input class="inp" id="asU" type="email" value="${ADMIN_USER}" placeholder="vous@exemple.fr">
    ${(HAS_PASS&&!VIA_KEY)?'<label>Mot de passe actuel</label><input class="inp" id="asO" type="password" autocomplete="current-password">':''}
    <label>Nouveau mot de passe <span style="font-weight:400;color:var(--muted)">(10 caractères minimum)</span></label>
    <input class="inp" id="asN" type="password" autocomplete="new-password">
    <label>Confirmer</label><input class="inp" id="asN2" type="password" autocomplete="new-password">
    <div id="asMsg" class="sub" style="color:#c0492f;margin-top:10px;min-height:18px"></div>
    <div class="drow"><button class="btn btn-g" id="asX">Annuler</button><button class="btn btn-p" id="asOk">Enregistrer</button></div>`;
  $('#mask').classList.add('on');
  $('#asX').onclick=()=>$('#mask').classList.remove('on');
  $('#asOk').onclick=async()=>{
    const n=$('#asN').value;
    if(n.length<10){$('#asMsg').textContent='10 caractères minimum.';return;}
    if(n!==$('#asN2').value){$('#asMsg').textContent='Les deux mots de passe ne correspondent pas.';return;}
    const oldEl=document.getElementById('asO');
    const r=await api('admin_set',{user:$('#asU').value.trim(),new:n,old:oldEl?oldEl.value:''});
    if(r.ok){toast('✓ Accès sécurisé');setTimeout(()=>location.reload(),900);}
    else $('#asMsg').textContent=r.error==='old'?'Mot de passe actuel incorrect.'
      :r.error==='user'?'E-mail invalide.':r.error==='short'?'10 caractères minimum.':'Erreur.';};
}
$('#secBtn').onclick=openAudit;
{const b=document.getElementById('setPwBtn'); if(b)b.onclick=openSecurity;}
$('#outBtn').onclick=async()=>{
  await fetch(location.pathname,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'a=admin_logout'});
  location.href=BASE+'/console.php';};

/* ---- Fiche commerce : tout ce que le propriétaire peut faire ---- */
const SHOPS=<?= json_encode(array_map(fn($s)=>['id'=>$s['id'],'name'=>$s['name'],'type'=>$s['type']??'Commerce',
  'email'=>$s['email'],'status'=>$s['status']??'trial','clients'=>(int)($s['nClients'] ?? 0)], $shops), JSON_UNESCAPED_UNICODE) ?>;
const TYPES=['Café','Restaurant','Boulangerie','Salon','Commerce'];
function openShop(id){
  const sh=SHOPS.find(x=>x.id===id); if(!sh)return;
  $('#dlg').innerHTML=`<h3>${escH(sh.name)}</h3><div class="sub">${sh.clients} client(s) · ${escH(sh.email)}</div>
    <label>Nom du commerce</label><input class="inp" id="seN" maxlength="60" value="${escH(sh.name)}">
    <label>Type</label><div class="chips" id="seT">${TYPES.map(t=>`<button class="chip ${t===sh.type?'on':''}" data-t="${t}">${t}</button>`).join('')}</div>
    <label>E-mail du gérant</label><input class="inp" id="seE" type="email" value="${escH(sh.email)}">
    <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap">
      <button class="btn btn-p" id="seOk">Enregistrer</button>
      <button class="btn btn-g" id="seCli">👥 Ses clients</button>
      <button class="btn btn-g" id="seImp">🔓 Ouvrir son espace</button>
      <button class="btn btn-g" id="seKey">🔑 Réinitialiser l'accès</button>
    </div>
    <div style="border-top:1px solid var(--line);margin-top:16px;padding-top:14px">
      <button class="btn btn-g" id="seDel" style="color:#c0492f;border-color:#e4bcb2">🗑 Supprimer ce commerce</button>
    </div>
    <div id="seMsg" class="sub" style="margin-top:10px"></div>
    <div class="drow"><button class="btn btn-g" id="seX">Fermer</button></div>`;
  $('#mask').classList.add('on');
  let ty=sh.type;
  $('#seT').onclick=e=>{const b=e.target.closest('.chip');if(!b)return;
    $$('#seT .chip').forEach(c=>c.classList.remove('on'));b.classList.add('on');ty=b.dataset.t;};
  $('#seX').onclick=()=>$('#mask').classList.remove('on');
  $('#seOk').onclick=async()=>{
    const r=await api('shop_edit',{id:sh.id,name:$('#seN').value.trim(),email:$('#seE').value.trim(),type:ty});
    if(r.ok){toast('✓ Fiche enregistrée');setTimeout(()=>location.reload(),700);}
    else $('#seMsg').textContent=r.error==='exists'?'Cet e-mail est déjà utilisé par un autre commerce.'
      :r.error==='email'?'E-mail invalide.':r.error==='name'?'Le nom est obligatoire.':'Erreur.';};
  $('#seKey').onclick=async()=>{
    if(!confirm('Réinitialiser l\'accès de '+sh.name+' ?\n\nSon mot de passe, son code et ses appareils de confiance seront remplacés.'))return;
    const r=await api('reset',{id:sh.id});
    if(r.ok)showCreds('Nouvel accès — '+sh.name,r);else toast('Erreur');};
  $('#seImp').onclick=async()=>{
    const r=await api('impersonate',{id:sh.id});
    if(r.ok)window.open(r.url,'_blank');else toast('Erreur');};
  $('#seCli').onclick=async()=>{
    const r=await api('shop_clients',{id:sh.id});
    if(!r.ok){toast('Erreur');return;}
    const rows=r.clients.length?r.clients.map(c=>`<tr>
        <td><div class="nm">${escH(c.name)}</div><div class="tp">${escH(c.tel||'—')}</div></td>
        <td class="mono">${c.points} pts</td><td class="mono">${c.visits} v.</td>
        <td class="r"><a href="${BASE}/carte.php?c=${c.card}" target="_blank" style="color:var(--em)">carte ↗</a></td>
      </tr>`).join('') : '<tr><td colspan="4" class="sub">Aucun client pour le moment.</td></tr>';
    $('#dlg').innerHTML=`<h3>Clients de ${escH(sh.name)}</h3><div class="sub">${r.clients.length} fiche(s)</div>
      <div style="max-height:52vh;overflow:auto;margin-top:12px">
        <table class="tbl"><thead><tr><th>Client</th><th>Points</th><th>Visites</th><th class="r">Carte</th></tr></thead>
        <tbody>${rows}</tbody></table></div>
      <div class="drow"><button class="btn btn-g" id="bk">← Retour</button></div>`;
    $('#bk').onclick=()=>openShop(sh.id);};
  $('#seDel').onclick=()=>{
    const t=prompt('Suppression définitive.\n\nTapez le nom exact du commerce pour confirmer :\n'+sh.name);
    if(t===null)return;
    api('shop_del',{id:sh.id,confirm:t}).then(r=>{
      if(r.ok){toast('Commerce supprimé ('+r.clients+' client(s))');setTimeout(()=>location.reload(),800);}
      else toast(r.error==='confirm'?'Le nom ne correspond pas — rien n\'a été supprimé':'Erreur');});};
}
function escH(x){return String(x).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));}
$$('.opn').forEach(b=>b.onclick=()=>openShop(b.dataset.id));

let nt='Café';const TE={'Café':'☕','Restaurant':'🍽️','Boulangerie':'🥐','Salon':'💈','Commerce':'🛍️'};
$('#addBtn').onclick=()=>{nt='Café';
  $('#dlg').innerHTML=`<h3>Nouveau commerce</h3><div class="sub">Le gérant définira son mot de passe via le lien d'installation. Essai 10 jours.</div>
   <label>Nom</label><input class="inp" id="nn" placeholder="Ex. Le Bon Café">
   <label>Type</label><div class="chips" id="nc">${Object.keys(TE).map(t=>`<button class="chip ${t==='Café'?'on':''}" data-t="${t}">${TE[t]} ${t}</button>`).join('')}</div>
   <label>E-mail du gérant</label><input class="inp" id="ne" type="email" placeholder="gerant@exemple.fr">
   <div class="drow"><button class="btn btn-g" id="nx">Annuler</button><button class="btn btn-p" id="no">Créer</button></div>`;
  $('#mask').classList.add('on');
  $('#nc').onclick=e=>{const b=e.target.closest('.chip');if(!b)return;$$('#nc .chip').forEach(c=>c.classList.remove('on'));b.classList.add('on');nt=b.dataset.t;};
  $('#nx').onclick=()=>$('#mask').classList.remove('on');
  $('#no').onclick=async()=>{const name=$('#nn').value.trim(),email=$('#ne').value.trim();if(!name||!email){toast('Nom et e-mail requis');return;}
    const r=await api('add',{name,type:nt,email});if(r.ok){showCreds('Compte créé — '+name,r);}else toast(r.error==='exists'?'E-mail déjà utilisé':'Erreur');};};
/* affiche les identifiants temporaires à transmettre au gérant */
function showCreds(title,r){
  $('#dlg').innerHTML=`<h3>${title}</h3><div class="sub">Transmettez ces identifiants au gérant. Il pourra les changer ensuite.</div>
    <div style="background:var(--iv);border:1px solid var(--line);border-radius:12px;padding:14px;font-family:monospace;font-size:14px;line-height:1.9">
      E-mail : <b>${r.email}</b><br>Mot de passe : <b>${r.pass}</b><br>Code : <b>${r.pin}</b></div>
    <div class="drow"><button class="btn btn-g" id="cc">Copier</button><button class="btn btn-p" id="cok">Terminé</button></div>`;
  $('#mask').classList.add('on');
  $('#cc').onclick=()=>{navigator.clipboard?.writeText(`Fidelo\nE-mail: ${r.email}\nMot de passe: ${r.pass}\nCode: ${r.pin}`).then(()=>toast('🔗 Copié'));};
  $('#cok').onclick=()=>location.reload();
}
$$('.rst').forEach(b=>b.onclick=async()=>{
  if(!confirm('Réinitialiser l\'accès de '+b.dataset.nm+' ? Un nouveau mot de passe sera généré (l\'ancien cessera de marcher).'))return;
  const r=await api('reset',{id:b.dataset.id});if(r.ok)showCreds('Nouvel accès — '+b.dataset.nm,r);else toast('Erreur');});
$('#mask')&&($('#mask').onclick=e=>{if(e.target.id==='mask')$('#mask').classList.remove('on');});
</script>
<?php endif; ?>
</body></html>
