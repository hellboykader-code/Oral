<?php
/* ==================================================================
   Fidelo — API du commerçant (JSON, session, CSRF, multi-tenant).
   Toute action portant sur des clients est cloisonnée au commerce
   en SESSION : jamais un id de commerce venu de la requête.
   ================================================================== */
require __DIR__ . '/lib.php'; require __DIR__ . '/gwallet.php'; require __DIR__ . '/stripe.php';
fidelo_session();
/* Verrou et chargement limités AU commerce connecté : deux commerces
   différents ne s'attendent plus, et on ne lit pas les clients des autres. */
$SID_SESSION = current_shop_id();
$LOCK = db_lock($SID_SESSION);

$a = $_POST['a'] ?? ($_GET['a'] ?? '');

/* Actions publiques (pas encore connecté) : login / pin / me / WebAuthn. */
$public = ['login', 'pin', 'me', 'logout', 'wa_aopts', 'wa_auth'];
if (!in_array($a, $public, true)) csrf_check();
elseif (in_array($a, ['login', 'pin', 'logout', 'wa_aopts', 'wa_auth'], true)) csrf_check();

$db = db_load($SID_SESSION);

/* ---- helpers de réponse ---- */
function client_view(array $c, array $rewards): array {
  $nx = next_reward($rewards, (int)$c['points']);
  $cl = claimable($rewards, (int)$c['points']);
  return [
    'id'=>$c['id'],'name'=>$c['name'],'tel'=>$c['tel'] ?? '','card'=>$c['card'] ?? '',
    'points'=>(int)$c['points'],'visits'=>(int)$c['visits'],'last'=>(int)$c['last'],
    'bday'=>$c['bday'] ?? '',
    'next'=>$nx,'claim'=>$cl,
  ];
}
function today_reset(array &$shop): void {
  $d = date('Y-m-d');
  if (($shop['today']['day'] ?? '') !== $d) $shop['today'] = ['pts'=>0,'rw'=>0,'day'=>$d];
}

switch ($a) {

case 'me': {
  $id = current_shop_id();
  $s = $id ? shop_ref($db, $id) : null;
  $dev = $_COOKIE['fidelo_dev'] ?? ''; $waReady = false;
  foreach ($db['shops'] as $sh) if (!empty($sh['devices'][$dev]['creds'])) { $waReady = true; break; }
  json_out([
    'ok'=>true,
    'auth'=> (bool)$s,
    'deviceKnown'=> device_known($db),
    'waReady'=> $waReady,
    'csrf'=> csrf_token(),
    'shop'=> $s ? shop_public($s) : null,
  ]);
}

/* WebAuthn — options d'authentification (visage/empreinte) sur un appareil connu. */
case 'wa_aopts': {
  $dev = $_COOKIE['fidelo_dev'] ?? '';
  $shop = null; foreach ($db['shops'] as $sh) if (!empty($sh['devices'][$dev]['creds'])) { $shop = $sh; break; }
  if (!$shop) json_out(['ok'=>false,'error'=>'nocred'],400);
  $chal = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
  $_SESSION['wa_chal'] = $chal;
  $allow = array_map(fn($cid)=>['id'=>$cid,'type'=>'public-key'], array_keys($shop['devices'][$dev]['creds']));
  json_out(['ok'=>true,'challenge'=>$chal,'rpId'=>wa_rpid(),'allow'=>$allow]);
}

/* WebAuthn — vérification de l'assertion → ouvre la session. */
case 'wa_auth': {
  $dev = $_COOKIE['fidelo_dev'] ?? '';
  $shop = null; foreach ($db['shops'] as $sh) if (!empty($sh['devices'][$dev]['creds'])) { $shop = $sh; break; }
  if (!$shop) json_out(['ok'=>false,'error'=>'nocred'],400);
  if (!rate_hit($db, 'wa:' . $dev, 8, 900)) { db_save($db); json_out(['ok'=>false,'error'=>'locked'],429); }
  $cid  = (string)($_POST['id'] ?? '');
  $creds = $shop['devices'][$dev]['creds'];
  if (!isset($creds[$cid])) { db_save($db); json_out(['ok'=>false,'error'=>'badcred'],400); }
  $ok = wa_verify_assertion(
    (string)($_POST['authData'] ?? ''), (string)($_POST['clientData'] ?? ''),
    (string)($_POST['sig'] ?? ''), $creds[$cid]['pub'], (string)($_SESSION['wa_chal'] ?? '')
  );
  unset($_SESSION['wa_chal']);
  if (!$ok) { db_save($db); json_out(['ok'=>false,'error'=>'verify'],401); }
  rate_reset($db, 'wa:' . $dev);
  fidelo_session(); session_regenerate_id(true);
  $_SESSION['shop_id'] = $shop['id']; $_SESSION['csrf'] = tok(24);
  db_save($db);
  json_out(['ok'=>true,'shop'=>shop_public($shop),'csrf'=>csrf_token()]);
}

case 'login': {
  $r = auth_login($db, (string)($_POST['email'] ?? ''), (string)($_POST['pass'] ?? ''));
  if (!$r['ok']) json_out($r, $r['error']==='ratelimit'?429:401);
  $r['csrf'] = csrf_token();
  json_out($r);
}

case 'pin': {
  $r = auth_pin($db, (string)($_POST['pin'] ?? ''));
  if (!$r['ok']) json_out($r, $r['error']==='locked'?429:401);
  $r['csrf'] = csrf_token();
  json_out($r);
}

case 'logout': { auth_logout(); json_out(['ok'=>true]); }

/* ------------- à partir d'ici : connexion obligatoire ------------- */
}

$shop = require_shop($db);      // 401 si non connecté / 403 si suspendu
$sid  = $shop['id'];
$ref  = &shop_ref($db, $sid);   // référence modifiable, cloisonnée

/* Abonnement terminé depuis plus que le délai de grâce : l'espace reste
   consultable (le commerçant voit ses clients) mais plus rien ne s'ajoute.
   Il ne perd RIEN, il ne peut simplement plus travailler avec l'outil. */
if (shop_readonly($db, $ref)
    && in_array($a, ['add_point','adjust','redeem','client_add','client_edit','client_del',
                     'dedup','rewards_set','trash_restore','relance'], true)) {
  json_out(['ok'=>false,'error'=>'expired',
    'message'=>"Votre abonnement Fidelo est arrêté. Vos clients et leurs points sont conservés — réactivez pour reprendre."], 402);
}

switch ($a) {

/* WebAuthn — options d'ENREGISTREMENT (activer le visage après connexion). */
case 'wa_ropts': {
  $chal = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
  $_SESSION['wa_chal'] = $chal;
  json_out(['ok'=>true,'challenge'=>$chal,'rpId'=>wa_rpid(),
    'userId'=>rtrim(strtr(base64_encode($sid), '+/', '-_'), '='),
    'userName'=>$ref['email']]);
}
/* WebAuthn — enregistre la clé (visage/empreinte) sur l'appareil de confiance courant. */
case 'wa_reg': {
  $dev = $_COOKIE['fidelo_dev'] ?? '';
  if (empty($ref['devices'][$dev])) json_out(['ok'=>false,'error'=>'nodevice'],400);
  // vérifier le clientDataJSON de création (type, challenge, origin)
  $cd = json_decode(base64_decode((string)($_POST['clientData'] ?? '')), true);
  if (!is_array($cd) || ($cd['type'] ?? '') !== 'webauthn.create'
      || !hash_equals((string)($_SESSION['wa_chal'] ?? ''), (string)($cd['challenge'] ?? ''))
      || ($cd['origin'] ?? '') !== wa_origin()) json_out(['ok'=>false,'error'=>'clientdata'],400);
  unset($_SESSION['wa_chal']);
  $cid = (string)($_POST['id'] ?? '');
  $pub = (string)($_POST['pub'] ?? '');
  if ($cid === '' || $pub === '' || !base64_decode($pub, true)) json_out(['ok'=>false,'error'=>'badkey'],400);
  $ref['devices'][$dev]['creds'] = $ref['devices'][$dev]['creds'] ?? [];
  $ref['devices'][$dev]['creds'][$cid] = ['pub'=>$pub, 'at'=>now()];
  db_save($db);
  json_out(['ok'=>true]);
}

case 'home': {
  $before = $ref['today']['day'] ?? '';
  today_reset($ref);
  $dayChanged = $before !== ($ref['today']['day'] ?? '');
  if ($dayChanged) bday_gifts_process($ref, now());   // cadeaux anniversaire, au plus une fois/jour
  $clients = real_clients($ref['clients']);
  usort($clients, fn($x,$y)=>$y['last']-$x['last']);
  $feed = array_map(fn($c)=>client_view($c,$ref['rewards']), array_slice($clients,0,5));
  if ($dayChanged) db_save($db);   // écrit seulement au changement de jour
  $inboxUnread = count(array_filter($ref['inbox'] ?? [], fn($m)=>empty($m['read'])));
  $annUnread = count(array_filter($db['settings']['announcements'] ?? [],
    fn($a)=>($a['at'] ?? 0) > ($ref['annReadAt'] ?? 0)));
  json_out(['ok'=>true,'today'=>$ref['today'],'goal'=>$ref['goal'],
    'nClients'=>count($clients),'feed'=>$feed,'inboxUnread'=>$inboxUnread,'annUnread'=>$annUnread,
    'quota'=>quota_restant($ref, count($clients)), 'freeMax'=>PLAN_FREE_MAX]);
}

case 'clients': {
  $q = mb_strtolower(trim($_POST['q'] ?? ''));
  $qd = preg_replace('/\D/', '', $q); // recherche par numéro de téléphone
  $list = array_filter(real_clients($ref['clients']), fn($c)=>
    $q==='' || mb_strpos(mb_strtolower($c['name']),$q)!==false || mb_strpos(mb_strtolower($c['id']),$q)!==false
    || ($qd!=='' && strlen($qd)>=3 && mb_strpos(preg_replace('/\D/','',$c['tel']??''),$qd)!==false));
  usort($list, fn($x,$y)=>$y['last']-$x['last']);
  json_out(['ok'=>true,'clients'=>array_map(fn($c)=>client_view($c,$ref['rewards']),array_values($list))]);
}

case 'client_get': {
  $c = &client_ref($ref, (string)($_POST['cid'] ?? ''));
  if (!$c) json_out(['ok'=>false,'error'=>'notfound'],404);
  json_out(['ok'=>true,'client'=>client_view($c,$ref['rewards'])]);
}

case 'client_add': {
  $q = quota_restant($ref, count(real_clients($ref['clients'])));
  if ($q !== null && $q <= 0) json_out(['ok'=>false,'error'=>'quota','max'=>PLAN_FREE_MAX,
    'message'=>"Votre formule Découverte est complète (".PLAN_FREE_MAX." clients). Passez en illimité pour continuer — vos clients actuels restent intacts."], 402);
  $name = mb_substr(trim($_POST['name'] ?? ''), 0, 50);
  if ($name==='') json_out(['ok'=>false,'error'=>'name'],400);
  $tel = mb_substr(trim($_POST['tel'] ?? ''), 0, 30);
  $bday = bday_norm((string)($_POST['bday'] ?? ''));
  if ($bday === null) json_out(['ok'=>false,'error'=>'bday'],400);
  // anti-doublon : si un client identique existe déjà, on le renvoie (pas de 2e carte)
  $dup = client_find_existing($ref, $name, $tel);
  if ($dup) json_out(['ok'=>true,'client'=>client_view($dup,$ref['rewards']),'existing'=>true]);
  $c = ['id'=>'F'.rid(6),'name'=>$name,'tel'=>$tel!==''?$tel:'—','points'=>0,'visits'=>0,
        'last'=>now(),'card'=>unique_card($db),'push'=>[],'created'=>now()];
  if ($bday !== '') $c['bday'] = $bday;
  $ref['clients'][] = $c;
  db_save($db);
  json_out(['ok'=>true,'client'=>client_view($c,$ref['rewards'])]);
}

/* Passer à une formule payante : renvoie l'URL Stripe Checkout du commerce
   en session. Le plan n'est activé qu'à la confirmation du paiement,
   par stripe-webhook.php — jamais côté client. */
case 'stripe_checkout': {
  $plan = $_POST['plan'] ?? '';
  if (!in_array($plan, ['mensuel','annuel','avie'], true)) json_out(['ok'=>false,'error'=>'plan'],400);
  if (!st_on($db)) json_out(['ok'=>false,'error'=>'stripe_off']);
  $r = st_checkout_url($db, $ref, $plan);
  json_out($r, $r['ok']?200:500);
}

/* Cartes vierges pré-imprimées : un lot de N cartes QR sans client, à coller
   sur des supports physiques et distribuer au comptoir. Cloisonnées au quota
   comme n'importe quel client (une carte imprimée = une place réservée). */
case 'blank_batch': {
  $n = max(1, min(200, (int)($_POST['n'] ?? 0)));
  $q = quota_restant($ref, count(real_clients($ref['clients'])));
  if ($q !== null) $n = min($n, $q);
  if ($n <= 0) json_out(['ok'=>false,'error'=>'quota','max'=>PLAN_FREE_MAX,
    'message'=>"Votre formule Découverte est complète (".PLAN_FREE_MAX." clients). Passez en illimité pour imprimer plus de cartes."], 402);
  $made = []; $seen = [];
  for ($i = 0; $i < $n; $i++) {
    do { $token = tok(8); } while (card_taken($token) || isset($seen[$token]));
    $seen[$token] = true;
    $c = ['id'=>'F'.rid(6),'name'=>'','tel'=>'','points'=>0,'visits'=>0,'last'=>0,
          'card'=>$token,'push'=>[],'created'=>0,'blank'=>true];
    $ref['clients'][] = $c;
    $made[] = ['id'=>$c['id'],'card'=>$token];
  }
  db_save($db);
  json_out(['ok'=>true,'cards'=>$made]);
}

/* Active une carte vierge : le commerçant scanne le QR d'une carte déjà
   imprimée et remise à un client, puis saisit son nom. Compte comme sa
   première visite (le geste physique de la remise = premier point). */
case 'blank_activate': {
  $card = trim($_POST['card'] ?? '');
  $c = null;
  foreach ($ref['clients'] as $i=>$cc) if (($cc['card']??'')===$card && !empty($cc['blank'])) { $c=&$ref['clients'][$i]; break; }
  if (!$c) json_out(['ok'=>false,'error'=>'notfound'],404);
  $q = quota_restant($ref, count(real_clients($ref['clients'])));
  if ($q !== null && $q <= 0) json_out(['ok'=>false,'error'=>'quota','max'=>PLAN_FREE_MAX,
    'message'=>"Votre formule Découverte est complète (".PLAN_FREE_MAX." clients). Passez en illimité pour continuer — vos clients actuels restent intacts."], 402);
  $name = mb_substr(trim($_POST['name'] ?? ''), 0, 50);
  if ($name==='') json_out(['ok'=>false,'error'=>'name'],400);
  $tel = mb_substr(trim($_POST['tel'] ?? ''), 0, 30);
  unset($c['blank']);
  $c['name'] = $name; $c['tel'] = $tel !== '' ? $tel : '—';
  $c['points'] = 1; $c['visits'] = 1; $c['last'] = now(); $c['created'] = now();
  today_reset($ref); $ref['today']['pts']++;
  $ref['events'][] = ['at'=>now(),'type'=>'point','cid'=>$c['id']];
  if (count($ref['events'])>400) $ref['events']=array_slice($ref['events'],-400);
  db_save($db);
  gw_sync_later($sid, (string)($c['card'] ?? ''));
  json_out(['ok'=>true,'client'=>client_view($c,$ref['rewards'])]);
}

/* Fusion des doublons : regroupe par téléphone normalisé, garde la fiche la
   plus ancienne, additionne points/visites, fusionne push. Les cartes vierges
   n'ont pas de téléphone : elles restent à part, jamais fusionnées. */
case 'dedup': {
  $blanks = array_filter($ref['clients'], fn($c)=>!empty($c['blank']));
  $groups = []; $solo = [];
  foreach (real_clients($ref['clients']) as $c) {
    $tn = norm_tel($c['tel'] ?? '');
    if (strlen($tn) >= 9) $groups['T:' . $tn][] = $c;   // fusion SEULEMENT par téléphone fiable
    else $solo[] = $c;                                   // sans téléphone → jamais fusionné (sécurité)
  }
  $merged = 0; $out = $solo;
  foreach ($groups as $g) {
    if (count($g) === 1) { $out[] = $g[0]; continue; }
    usort($g, fn($a,$b)=>($a['created']??0)-($b['created']??0));
    $base = $g[0];
    for ($i=1;$i<count($g);$i++) {
      $base['points'] += (int)$g[$i]['points'];
      $base['visits'] += (int)$g[$i]['visits'];
      $base['last']    = max($base['last'], $g[$i]['last']);
      if (!empty($g[$i]['push'])) $base['push'] = array_merge($base['push'] ?? [], $g[$i]['push']);
      // ⭐ on GARDE l'ancienne carte comme alias : le QR déjà imprimé, ajouté
      //    au Wallet ou à l'écran d'accueil du client continue de fonctionner.
      if (!empty($g[$i]['card'])) {
        $base['alias'] = $base['alias'] ?? [];
        if (!in_array($g[$i]['card'], $base['alias'], true)) $base['alias'][] = $g[$i]['card'];
      }
      $merged++;
    }
    $out[] = $base;
  }
  $ref['clients'] = array_merge($out, array_values($blanks));
  db_save($db);
  json_out(['ok'=>true,'merged'=>$merged,'nClients'=>count($out)]);
}

/* ⭐ ANTI-FRAUDE : le point est ajouté côté serveur, par le commerçant
   connecté, uniquement pour un client de SON commerce. */
case 'add_point': {
  if (!rate_hit($db,'scan:'.$sid,120,60)) json_out(['ok'=>false,'error'=>'ratelimit'],429);
  $card = trim($_POST['card'] ?? '');
  $cid  = trim($_POST['cid'] ?? '');
  $c = null;
  foreach ($ref['clients'] as $i=>$cc) {
    if (($cid!=='' && $cc['id']===$cid) || ($card!=='' && ($cc['card']??'')===$card)) { $c=&$ref['clients'][$i]; break; }
  }
  if (!$c) json_out(['ok'=>false,'error'=>'notfound'],404);
  /* Carte vierge pas encore remise à un client : on ne crédite rien tant
     qu'elle n'a pas de nom (voir blank_activate). */
  if (!empty($c['blank'])) json_out(['ok'=>false,'error'=>'blank','card'=>$c['card']],409);

  /* Anti double-scan : le même client crédité deux fois en quelques secondes,
     c'est presque toujours une erreur (deux employés, ou un double appui).
     On refuse en silence, sauf si le commerçant confirme (force=1). */
  $depuis = now() - (int)($c['last'] ?? 0);
  if ($depuis < 90 && (int)($c['visits'] ?? 0) > 0 && empty($_POST['force'])) {
    json_out(['ok'=>false,'error'=>'recent','seconds'=>$depuis,
      'client'=>client_view($c,$ref['rewards'])], 409);
  }

  $before = claimable($ref['rewards'], (int)$c['points']);
  $c['points']++; $c['visits']++; $c['last']=now();
  today_reset($ref); $ref['today']['pts']++;
  $ref['events'][] = ['at'=>now(),'type'=>'point','cid'=>$c['id']];
  if (count($ref['events'])>400) $ref['events']=array_slice($ref['events'],-400);
  $after = claimable($ref['rewards'], (int)$c['points']);
  $unlocked = ($after && (!$before || $after['pts']>$before['pts'])) ? $after : null;
  db_save($db);
  gw_sync_later($sid, (string)($c['card'] ?? ''));   // points à jour dans Google Wallet
  json_out(['ok'=>true,'client'=>client_view($c,$ref['rewards']),'unlocked'=>$unlocked]);
}

/* Correction manuelle des points (annuler un scan, corriger une erreur). */
case 'adjust': {
  $c = &client_ref($ref, (string)($_POST['cid'] ?? ''));
  if (!$c) json_out(['ok'=>false,'error'=>'notfound'],404);
  $delta = (int)($_POST['delta'] ?? 0);
  if ($delta < -50 || $delta > 50) json_out(['ok'=>false,'error'=>'range'],400);
  $c['points'] = max(0, (int)$c['points'] + $delta);
  today_reset($ref);
  if ($delta < 0 && $ref['today']['pts'] > 0) $ref['today']['pts'] = max(0, $ref['today']['pts'] + $delta);
  $ref['events'][] = ['at'=>now(),'type'=>'adjust','cid'=>$c['id'],'d'=>$delta];
  db_save($db);
  gw_sync_later($sid, (string)($c['card'] ?? ''));
  json_out(['ok'=>true,'client'=>client_view($c,$ref['rewards'])]);
}

case 'redeem': {
  $c = &client_ref($ref, (string)($_POST['cid'] ?? ''));
  if (!$c) json_out(['ok'=>false,'error'=>'notfound'],404);
  // le client CHOISIT la récompense (par son coût en points) ; sinon, la plus haute atteinte.
  $pts = isset($_POST['rw']) ? (int)$_POST['rw'] : -1;
  $chosen = null;
  if ($pts >= 0) { foreach ($ref['rewards'] as $r) if ((int)$r['pts'] === $pts) { $chosen = $r; break; } }
  if (!$chosen) $chosen = claimable($ref['rewards'], (int)$c['points']);
  if (!$chosen) json_out(['ok'=>false,'error'=>'none'],400);
  if ((int)$c['points'] < (int)$chosen['pts']) json_out(['ok'=>false,'error'=>'insufficient'],400);
  // on DÉDUIT le coût de la récompense choisie (jamais de remise à zéro) → le surplus est conservé.
  $c['points'] = (int)$c['points'] - (int)$chosen['pts'];
  today_reset($ref); $ref['today']['rw']++;
  $ref['events'][] = ['at'=>now(),'type'=>'redeem','cid'=>$c['id'],'r'=>$chosen['t'],'pts'=>(int)$chosen['pts']];
  db_save($db);
  gw_sync_later($sid, (string)($c['card'] ?? ''));
  json_out(['ok'=>true,'client'=>client_view($c,$ref['rewards']),'reward'=>$chosen]);
}

/* Logo du commerce : remplace le monogramme automatique partout
   (carte du client, affichette, Google Wallet). PNG/JPEG, 2 Mo max. */
case 'logo_set': {
  $raw = (string)($_POST['img'] ?? '');
  if ($raw === 'none') {                                  // retour au monogramme
    @unlink(__DIR__ . '/data/brand/' . $sid . '-logo.png');
    $ref['brandAt'] = now(); db_save($db);
    json_out(['ok'=>true,'logo'=>false]);
  }
  if (!preg_match('#^data:image/(png|jpe?g|webp);base64,#i', $raw, $m)) json_out(['ok'=>false,'error'=>'format'],400);
  $bin = base64_decode(substr($raw, strpos($raw, ',') + 1) ?: '', true);
  if ($bin === false || strlen($bin) > 2 * 1024 * 1024) json_out(['ok'=>false,'error'=>'size'],400);
  if (!function_exists('imagecreatefromstring')) json_out(['ok'=>false,'error'=>'gd'],500);
  $src = @imagecreatefromstring($bin);
  if (!$src) json_out(['ok'=>false,'error'=>'image'],400);
  // redimensionne dans 660×660 en gardant la transparence
  $sw = imagesx($src); $sh = imagesy($src);
  $k = min(1, 660 / max($sw, $sh));
  $nw = max(1, (int)($sw * $k)); $nh = max(1, (int)($sh * $k));
  $out = imagecreatetruecolor($nw, $nh);
  imagealphablending($out, false); imagesavealpha($out, true);
  imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
  imagecopyresampled($out, $src, 0, 0, 0, 0, $nw, $nh, $sw, $sh);
  $dir = __DIR__ . '/data/brand';
  if (!is_dir($dir)) @mkdir($dir, 0775, true);
  imagepng($out, $dir . '/' . $sid . '-logo.png', 9);
  imagedestroy($src); imagedestroy($out);
  foreach (glob($dir . '/' . $sid . '-*-*.png') ?: [] as $old) @unlink($old);   // vide le cache
  $ref['brandAt'] = now(); db_save($db);
  json_out(['ok'=>true,'logo'=>true]);
}

/* Le commerçant modifie la fiche d'un de SES clients (nom, téléphone). */
case 'client_edit': {
  $c = &client_ref($ref, (string)($_POST['cid'] ?? ''));
  if (!$c) json_out(['ok'=>false,'error'=>'notfound'],404);
  $name = mb_substr(trim($_POST['name'] ?? ''), 0, 50);
  if ($name === '') json_out(['ok'=>false,'error'=>'name'],400);
  $tel = mb_substr(trim($_POST['tel'] ?? ''), 0, 30);
  $bday = bday_norm((string)($_POST['bday'] ?? ''));
  if ($bday === null) json_out(['ok'=>false,'error'=>'bday'],400);
  $c['name'] = $name;
  $c['tel'] = $tel !== '' ? $tel : '—';
  if ($bday === '') unset($c['bday']); else $c['bday'] = $bday;
  db_save($db);
  gw_sync_later($sid, (string)($c['card'] ?? ''));
  json_out(['ok'=>true,'client'=>client_view($c,$ref['rewards'])]);
}

/* Suppression d'un client (sa carte cesse de fonctionner). */
case 'client_del': {
  $cid = (string)($_POST['cid'] ?? '');
  $sup = null;
  foreach ($ref['clients'] as $i => $c) if ($c['id'] === $cid) { $sup = $c; unset($ref['clients'][$i]); break; }
  if (!$sup) json_out(['ok'=>false,'error'=>'notfound'],404);
  $ref['clients'] = array_values($ref['clients']);
  /* Corbeille 30 jours : une suppression par erreur se rattrape. */
  $ref['corbeille'] = $ref['corbeille'] ?? [];
  $sup['_supprime'] = now();
  $ref['corbeille'][] = $sup;
  $ref['corbeille'] = array_values(array_filter($ref['corbeille'],
    fn($x) => (now() - (int)($x['_supprime'] ?? 0)) < 30 * 86400));
  if (count($ref['corbeille']) > 200) $ref['corbeille'] = array_slice($ref['corbeille'], -200);
  $ref['events'][] = ['at'=>now(),'type'=>'client_del','cid'=>$cid];
  db_save($db);
  json_out(['ok'=>true,'restaurable'=>true]);
}

/* Corbeille : consulter et restaurer un client supprimé par erreur. */
case 'trash': {
  $t = array_map(fn($c)=>['id'=>$c['id'],'name'=>$c['name'],'tel'=>$c['tel'] ?? '',
    'points'=>(int)$c['points'],'at'=>(int)($c['_supprime'] ?? 0)], $ref['corbeille'] ?? []);
  usort($t, fn($x,$y)=>$y['at'] <=> $x['at']);
  json_out(['ok'=>true,'trash'=>$t]);
}
case 'trash_restore': {
  $cid = (string)($_POST['cid'] ?? '');
  $rest = null;
  foreach (($ref['corbeille'] ?? []) as $i => $c) if ($c['id'] === $cid) { $rest = $c; unset($ref['corbeille'][$i]); break; }
  if (!$rest) json_out(['ok'=>false,'error'=>'notfound'],404);
  $ref['corbeille'] = array_values($ref['corbeille']);
  unset($rest['_supprime']);
  /* si la carte a été réattribuée entre-temps, on en donne une neuve */
  if (card_taken($rest['card'] ?? '')) $rest['card'] = unique_card($db);
  $ref['clients'][] = $rest;
  db_save($db);
  json_out(['ok'=>true,'client'=>client_view($rest,$ref['rewards'])]);
}

/* Le commerçant change SON mot de passe (l'ancien est exigé). */
case 'pass_set': {
  if (!rate_hit($db, 'passset:' . $sid, 10, 900)) { db_save($db); json_out(['ok'=>false,'error'=>'ratelimit'],429); }
  $old = (string)($_POST['old'] ?? '');
  $new = (string)($_POST['new'] ?? '');
  if (!password_verify($old, $ref['passHash'] ?? '')) { db_save($db); json_out(['ok'=>false,'error'=>'old'],403); }
  if (mb_strlen($new) < 8) json_out(['ok'=>false,'error'=>'short'],400);
  if ($new === $old) json_out(['ok'=>false,'error'=>'same'],400);
  $ref['passHash'] = password_hash($new, PASSWORD_DEFAULT);
  rate_reset($db, 'passset:' . $sid);
  /* Si le commerçant pense avoir été piraté, il coupe tous les autres accès :
     appareils de confiance révoqués → seul le nouveau mot de passe ouvre. */
  $revoked = 0;
  if (!empty($_POST['revoke'])) { $revoked = count($ref['devices'] ?? []); $ref['devices'] = []; }
  db_save($db);
  session_regenerate_id(true);                      // l'ancien identifiant de session ne vaut plus rien
  json_out(['ok'=>true,'revoked'=>$revoked]);
}

/* Liste et révocation des appareils de confiance (téléphones/tablettes
   qui ouvrent l'espace avec le code à 4 chiffres). */
case 'devices': {
  $out = [];
  $mine = $_COOKIE['fidelo_dev'] ?? '';
  foreach (($ref['devices'] ?? []) as $k => $d) $out[] = [
    'k' => substr(sha1((string)$k), 0, 10),
    'at' => (int)($d['at'] ?? 0),
    'face' => !empty($d['creds']),
    'me' => hash_equals((string)$k, (string)$mine),
    'expire' => (int)($d['at'] ?? 0) + DEVICE_TTL,
  ];
  usort($out, fn($x,$y)=>$y['at'] <=> $x['at']);
  json_out(['ok'=>true,'devices'=>$out]);
}
case 'device_revoke': {
  $k = (string)($_POST['k'] ?? '');
  $mine = $_COOKIE['fidelo_dev'] ?? '';
  $n = 0;
  foreach (array_keys($ref['devices'] ?? []) as $key) {
    // « all » = tous les AUTRES : on ne se déconnecte pas soi-même par surprise
    if ($k === 'all' && hash_equals((string)$key, (string)$mine)) continue;
    if ($k === 'all' || substr(sha1((string)$key), 0, 10) === $k) { unset($ref['devices'][$key]); $n++; }
  }
  db_save($db);
  json_out(['ok'=>true,'revoked'=>$n]);
}

/* Le commerçant change SON code à 4 chiffres. */
case 'pin_set': {
  if (!rate_hit($db, 'pinset:' . $sid, 10, 900)) { db_save($db); json_out(['ok'=>false,'error'=>'ratelimit'],429); }
  $pass = (string)($_POST['pass'] ?? '');
  $pin  = preg_replace('/\D/', '', (string)($_POST['pin'] ?? ''));
  if (!password_verify($pass, $ref['passHash'] ?? '')) { db_save($db); json_out(['ok'=>false,'error'=>'pass'],403); }
  if (strlen($pin) !== 4) json_out(['ok'=>false,'error'=>'pin'],400);
  if (in_array($pin, ['0000','1111','1234','1212','2222','9999','4321'], true)) json_out(['ok'=>false,'error'=>'weak'],400);
  $ref['pinHash'] = password_hash($pin, PASSWORD_DEFAULT);
  db_save($db);
  json_out(['ok'=>true]);
}

case 'rewards_set': {
  $rw = json_decode($_POST['rewards'] ?? '[]', true);
  if (!is_array($rw) || !$rw) json_out(['ok'=>false,'error'=>'empty'],400);
  $clean=[];
  foreach ($rw as $r) {
    $pts=max(1,(int)($r['pts']??1)); $t=trim((string)($r['t']??''));
    if ($t!=='') $clean[]=['pts'=>$pts,'t'=>mb_substr($t,0,60),'d'=>mb_substr(trim((string)($r['d']??'')),0,80)];
  }
  if (!$clean) json_out(['ok'=>false,'error'=>'empty'],400);
  $ref['rewards']=$clean; db_save($db);
  json_out(['ok'=>true,'rewards'=>$clean]);
}

case 'settings_set': {
  if (isset($_POST['name'])) { $n=trim($_POST['name']); if($n!=='') $ref['name']=mb_substr($n,0,60); }
  if (isset($_POST['goal'])) $ref['goal']=max(1,(int)$_POST['goal']);
  if (isset($_POST['city'])) $ref['city']=mb_substr(trim($_POST['city']),0,60);
  if (isset($_POST['googleReview'])) {
    $gr = trim($_POST['googleReview']);
    if ($gr !== '' && !filter_var($gr, FILTER_VALIDATE_URL)) json_out(['ok'=>false,'error'=>'googleReview'],400);
    $ref['googleReview'] = mb_substr($gr, 0, 300);
  }
  if (isset($_POST['birthdayGift'])) $ref['birthdayGift'] = $_POST['birthdayGift'] === '1';
  db_save($db);
  json_out(['ok'=>true,'shop'=>shop_public($ref)]);
}

/* Messages envoyés par les clients depuis leur carte (carte.php, action
   publique client_msg). Les plus récents d'abord ; marqués lus à l'ouverture. */
case 'inbox': {
  $inbox = $ref['inbox'] ?? [];
  usort($inbox, fn($a,$b)=>$b['at']-$a['at']);
  foreach ($ref['inbox'] as &$m) $m['read'] = true; unset($m);
  db_save($db);
  json_out(['ok'=>true,'messages'=>array_slice($inbox,0,100)]);
}

/* Historique des messages groupés envoyés par CE commerce (push.php?a=broadcast). */
case 'broadcast_history': {
  json_out(['ok'=>true,'items'=>array_slice($ref['broadcasts'] ?? [],0,30)]);
}

/* Annonces du propriétaire de la plateforme (globales, console.php?a=announce).
   Marquées lues à l'ouverture (annReadAt sert au badge non-lu de home). */
case 'announcements': {
  $items = array_slice($db['settings']['announcements'] ?? [], 0, 20);
  $ref['annReadAt'] = now();
  db_save($db);
  json_out(['ok'=>true,'items'=>$items]);
}

case 'stats': {
  $cl = real_clients($ref['clients']);
  $seg = ['champions'=>0,'fideles'=>0,'nouveaux'=>0,'endormis'=>0];
  $sleepers = []; $t = now();
  foreach ($cl as $c) {
    $s = client_segment($c, $t);
    $seg[$s]++;
    if ($s === 'endormis') $sleepers[] = $c;
  }
  usort($sleepers, fn($x,$y)=>$x['last']-$y['last']);
  $sleepers = array_map(fn($c)=>[
    'id'=>$c['id'],'name'=>$c['name'],'tel'=>$c['tel']??'','points'=>(int)$c['points'],
    'days'=>intdiv($t-$c['last'],86400)
  ], array_slice($sleepers,0,6));
  $top = $cl; usort($top, fn($x,$y)=>$y['points']-$x['points']);
  $top = array_map(fn($c)=>['name'=>$c['name'],'visits'=>(int)$c['visits'],'points'=>(int)$c['points']], array_slice($top,0,5));
  // série : points par jour sur 8 jours (depuis events)
  $days=[]; for($i=7;$i>=0;$i--){ $days[date('Y-m-d',$t-$i*86400)]=0; }
  foreach ($ref['events'] as $ev) if (($ev['type']??'')==='point') { $d=date('Y-m-d',$ev['at']); if(isset($days[$d]))$days[$d]++; }
  $series = ['pts'=>array_values($days),'xl'=>array_map(fn($d)=>date('d/m',strtotime($d)),array_keys($days))];
  $active = count(array_filter($cl, fn($c)=>($t-$c['last'])<=30*86400));
  json_out(['ok'=>true,'seg'=>$seg,'sleepers'=>$sleepers,'top'=>$top,'series'=>$series,
    'nClients'=>count($cl),'active'=>$active]);
}

/* Export CSV des clients du commerce (portabilité des données, RGPD). */
/* Parrainage : le commerçant partage son lien, les deux gagnent 2 mois. */
case 'parrain': {
  $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
  $scheme = is_https() ? 'https' : 'http';
  $lien = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'fidelo.site') . ($base ?: '') .
          '/inscription.php?p=' . rawurlencode(parrain_code($ref));
  json_out(['ok'=>true,'code'=>parrain_code($ref),'lien'=>$lien,
    'filleuls'=>(int)($ref['filleuls'] ?? 0),
    'creditMois'=>(int)($ref['creditMois'] ?? 0),
    'histo'=>array_slice($ref['creditHisto'] ?? [], -10)]);
}

case 'export': {
  $rows = [['Nom','Telephone','Points','Visites','Derniere visite','Carte','Inscrit le']];
  $cl = real_clients($ref['clients']);
  usort($cl, fn($x,$y)=>$y['last']-$x['last']);
  foreach ($cl as $c) $rows[] = [
    $c['name'], ($c['tel'] ?? '') === '—' ? '' : ($c['tel'] ?? ''),
    (int)$c['points'], (int)$c['visits'],
    $c['last'] ? date('d/m/Y', (int)$c['last']) : '',
    $c['card'] ?? '', !empty($c['created']) ? date('d/m/Y', (int)$c['created']) : '',
  ];
  $out = "\xEF\xBB\xBF";                       // BOM : Excel lit les accents correctement
  foreach ($rows as $r) $out .= implode(';', array_map(
    fn($v) => '"' . str_replace('"', '""', (string)$v) . '"', $r)) . "\r\n";
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="clients-' . preg_replace('/[^A-Za-z0-9]+/','-', $ref['name']) . '-' . date('Y-m-d') . '.csv"');
  echo $out; exit;
}

case 'relance': {
  $c = &client_ref($ref, (string)($_POST['cid'] ?? ''));
  if (!$c) json_out(['ok'=>false,'error'=>'notfound'],404);
  // démo : on note la relance (canal gratuit push/WhatsApp géré ailleurs)
  $ref['events'][] = ['at'=>now(),'type'=>'relance','cid'=>$c['id']];
  db_save($db);
  json_out(['ok'=>true]);
}

default:
  json_out(['ok'=>false,'error'=>'unknown_action'],400);
}
