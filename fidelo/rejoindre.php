<?php
/* ==================================================================
   Fidelo — page « Rejoindre » (self-service client).
   Le client scanne le QR du commerce (?s=<joinToken>) avec l'appareil
   photo, saisit prénom + nom, obtient sa carte de fidélité.
   ================================================================== */
require __DIR__ . '/lib.php'; fidelo_session();
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$csrf = csrf_token();
$s = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['s'] ?? ($_POST['s'] ?? ''));
$j = preg_replace('/[^A-Za-z0-9]/', '', $_GET['j'] ?? ($_POST['j'] ?? ''));
$db = db_load();
$shop = shop_by_join($db, $s);
if (!$shop && $j !== '') { $shop = shop_by_short($db, $j); if ($shop) $s = $shop['joinToken']; }
if ($shop) { $LOCK = db_lock($shop['id']); shop_hydrate($db, $shop['id']); $shop = shop_ref($db, $shop['id']); }
$err = '';

if ($shop && $_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['_csrf'] ?? '')) { $err = 'Session expirée, réessayez.'; }
  elseif (!rate_hit($db, 'join:' . client_ip(), 12, 3600)) { $err = 'Trop de demandes, réessayez plus tard.'; db_save($db); }
  else {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom    = trim($_POST['nom'] ?? '');
    if ($prenom === '') { $err = 'Indiquez votre prénom.'; }
    else {
      $name = mb_substr(trim($prenom . ' ' . $nom), 0, 50);
      $tel  = mb_substr(trim($_POST['tel'] ?? ''), 0, 30);
      // ANTI-DOUBLON : si cette personne a déjà une carte ici, on la lui rend (pas de 2e carte)
      $dup = client_find_existing($shop, $name, $tel);
      if ($dup) { header('Location: ' . ($base ?: '') . '/carte.php?c=' . $dup['card'] . '&deja=1'); exit; }
      $q = quota_restant($shop, count($shop['clients'] ?? []));
      if ($q !== null && $q <= 0) {
        $err = "Ce commerce a atteint le nombre de cartes de son offre. Demandez-lui de vous inscrire directement.";
        goto fin_post;
      }
      $card = unique_card($db);
      foreach ($db['shops'] as $i => $sh) if ($sh['id'] === $shop['id']) {
        $db['shops'][$i]['clients'][] = [
          'id' => 'F' . rid(6), 'name' => $name, 'tel' => $tel ?: '—',
          'points' => 0, 'visits' => 0, 'last' => now(), 'card' => $card, 'push' => [], 'created' => now(),
        ];
        db_save($db);
        header('Location: ' . ($base ?: '') . '/carte.php?c=' . $card . '&bienvenue=1');
        exit;
      }
    }
  }
  fin_post:;
}
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title><?= $shop ? 'Rejoindre '.e($shop['name']) : 'Fidelo' ?></title><meta name="theme-color" content="#241A12">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Calistoga&family=Inter:wght@400;500;600&display=swap">
<style>
:root{--em:#C1552F;--em-d:#9C4024;--or:#D9A94E;--or-l:#f0d488;--iv:#FBF3E7;--card:#fff;--line:#E9DAC3;--text:#241A12;--muted:#6E5B47;--faint:#9C8B74;--deep:#241A12;--bad:#B0241A;
--disp:"Calistoga",system-ui,sans-serif;--body:"Inter",system-ui,sans-serif;}
@media(prefers-color-scheme:dark){:root{--em:#E08A5D;--em-d:#9C4024;--or:#e3ba63;--iv:#1E140D;--card:#2A1E16;--line:#4A382A;--text:#F3E8D8;--muted:#C9B79E;--faint:#8F7C64;}}
*{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:var(--body);color:#F3ECE1;-webkit-font-smoothing:antialiased;
background:radial-gradient(70% 40% at 80% 5%,rgba(193,85,47,.2),transparent 60%),radial-gradient(60% 40% at 15% 100%,rgba(227,186,99,.16),transparent 62%),linear-gradient(168deg,#241A12,#2A1E16 55%,#241A12)}
h1,h2{font-family:var(--disp);font-weight:600;letter-spacing:-.02em;margin:0}
.wrap{max-width:420px;margin:0 auto;padding:34px 22px;min-height:100vh;display:flex;flex-direction:column}
.brand{font-family:var(--disp);font-weight:700;font-size:19px;color:#fff}.brand i{color:var(--or);font-style:normal}
.shopcard{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:18px;margin-top:28px;display:flex;align-items:center;gap:13px}
.shopcard .ic{width:48px;height:48px;border-radius:13px;background:var(--em);color:#2E1608;display:grid;place-items:center;font-family:var(--disp);font-weight:700;font-size:20px}
.shopcard .nm{font-family:var(--disp);font-weight:600;font-size:19px;color:#fff}.shopcard .sb{font-size:12px;color:rgba(243,236,225,.6)}
.hero{margin-top:30px}.hero h1{font-size:30px;color:#fff}.hero p{color:rgba(243,236,225,.7);font-size:15px;margin-top:10px}
.rew{display:flex;gap:8px;margin-top:18px;flex-wrap:wrap}.rew span{background:rgba(217,169,78,.14);border:1px solid rgba(217,169,78,.4);color:var(--or-l);padding:6px 11px;border-radius:99px;font-size:12.5px;font-weight:600}
form{margin-top:26px}label{display:block;font-size:13px;font-weight:600;color:rgba(243,236,225,.75);margin:14px 0 6px}
.inp{width:100%;padding:14px;border-radius:13px;border:1.5px solid rgba(255,255,255,.14);background:rgba(255,255,255,.06);color:#fff;font-size:16px}
.inp:focus{outline:none;border-color:var(--em)}.inp::placeholder{color:rgba(243,236,225,.4)}
.btn{width:100%;margin-top:22px;padding:16px;border-radius:14px;background:var(--em);color:#2E1608;font-weight:700;font-size:16px;border:0;cursor:pointer}
.btn:hover{background:#E08A5D}
.err{background:rgba(224,122,99,.16);border:1px solid var(--bad);color:#ffb9a6;padding:12px 14px;border-radius:12px;font-size:14px;margin-top:16px}
.foot{margin-top:auto;padding-top:26px;text-align:center;font-size:12px;color:rgba(243,236,225,.45)}
.empty{text-align:center;margin-top:20vh}
.flogo{width:1.35em;height:1.35em;display:inline-block;vertical-align:-.32em;margin-right:.32em}
</style></head><body>
<div class="wrap">
  <div class="brand"><svg class="flogo" viewBox="0 0 64 64" aria-hidden="true"><defs><linearGradient id="rja" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#C1552F"/><stop offset=".55" stop-color="#E08A5D"/><stop offset="1" stop-color="#FF6B6B"/></linearGradient><linearGradient id="rjb" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".3"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#rja)"/><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#rjb)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>Fidelo<i>.</i></div>
  <?php if(!$shop): ?>
    <div class="empty"><h2 style="color:#fff">Lien invalide</h2><p style="color:rgba(243,236,225,.7);margin-top:10px">Ce QR ne correspond à aucun commerce. Demandez au personnel de vous en présenter un valide.</p></div>
  <?php else:
    $rw = $shop['rewards'] ?? []; usort($rw, fn($a,$b)=>$a['pts']-$b['pts']);
  ?>
    <div class="shopcard"><div class="ic"><?= e(strtoupper(mb_substr($shop['name'],0,1))) ?></div>
      <div><div class="nm"><?= e($shop['name']) ?></div><div class="sb">Programme de fidélité</div></div></div>
    <div class="hero"><h1>Votre carte de fidélité, offerte 🎁</h1>
      <p>Créez-la en 10 secondes. Rien à installer : elle se range dans votre téléphone. Chaque passage compte.</p>
      <?php if($rw): ?><div class="rew"><?php foreach(array_slice($rw,0,3) as $r): ?><span><?= (int)$r['pts'] ?> pts · <?= e($r['t']) ?></span><?php endforeach; ?></div><?php endif; ?>
    </div>
    <?php if($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="s" value="<?= e($s) ?>">
      <label>Prénom *</label><input class="inp" name="prenom" placeholder="Votre prénom" required autofocus value="<?= e($_POST['prenom'] ?? '') ?>">
      <label>Nom</label><input class="inp" name="nom" placeholder="Votre nom" value="<?= e($_POST['nom'] ?? '') ?>">
      <label>Téléphone <span style="color:rgba(243,236,225,.4);font-weight:400">(recommandé — retrouve votre carte si vous changez de téléphone)</span></label>
      <input class="inp" name="tel" inputmode="tel" placeholder="06 12 34 56 78" value="<?= e($_POST['tel'] ?? '') ?>">
      <button class="btn" type="submit">Créer ma carte →</button>
      <p style="font-size:11.5px;color:rgba(243,236,225,.55);line-height:1.6;margin-top:14px">
        En créant votre carte, vous acceptez que <b><?= e($shop['name']) ?></b> conserve votre prénom,
        votre nom et votre téléphone pour gérer votre fidélité. Aucune publicité, aucune revente
        de vos données. Vous pouvez demander leur suppression à tout moment auprès du commerce. <a href="confidentialite.php" style="color:inherit;text-decoration:underline">Confidentialité</a>.
      </p>
    </form>
    <div class="foot">Propulsé par Fidelo · Vos points sont crédités par le commerçant.</div>
  <?php endif; ?>
</div>
</body></html>
