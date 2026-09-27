<?php
/* Fidelo — inscription commerçant (Part 3). Crée le compte + ouvre la session. */
require __DIR__ . '/lib.php'; fidelo_session();
$LOCK = db_lock();   // sérialise les écritures concurrentes (lecture-modification-écriture)
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$csrf = csrf_token();
$err = '';

$dbCheck = db_load();
$signupOpen = ($dbCheck['settings']['signupOpen'] ?? true) !== false;

/* Code promo (saisi) et code parrain (dans le lien ?p=…) */
$codePromo = promo_norm($_POST['promo'] ?? ($_GET['promo'] ?? ''));
$codeParr  = promo_norm($_POST['p'] ?? ($_GET['p'] ?? ''));
$parrainNom = '';
if ($codeParr !== '') { $pp = shop_by_parrain($dbCheck, $codeParr); $parrainNom = $pp['name'] ?? ''; }
$promoInfo = $codePromo !== '' ? promo_check($dbCheck, $codePromo) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$signupOpen) { $err = 'Les inscriptions sont momentanément fermées.'; }
  elseif (!hash_equals($_SESSION['csrf'] ?? '', $_POST['_csrf'] ?? '')) { $err = 'Session expirée, réessayez.'; }
  elseif (trim($_POST['website'] ?? '') !== '') { $err = 'Erreur, réessayez.'; }   // piège à robots
  else {
    $db = db_load();
    if (!rate_hit($db, 'signup:' . client_ip(), 8, 3600)) { $err = 'Trop de tentatives, réessayez plus tard.'; db_save($db); }
    else {
      $r = shop_create($db, [
        'name' => $_POST['name'] ?? '', 'type' => $_POST['type'] ?? 'Commerce',
        'city' => $_POST['city'] ?? '', 'email' => $_POST['email'] ?? '',
        'pass' => $_POST['pass'] ?? '', 'pin' => $_POST['pin'] ?? '',
      ]);
      if ($r['ok']) {
        // code promo puis parrainage : les deux créditent le nouveau commerce
        if ($codePromo !== '') promo_apply($db, $r['id'], $codePromo);
        if ($codeParr !== '')  parrain_link($db, $r['id'], $codeParr);
        db_save($db);
        // connexion immédiate
        auth_login($db, mb_strtolower(trim($_POST['email'])), $_POST['pass']);
        header('Location: ' . ($base ?: '') . '/index.php'); exit;
      }
      $map = ['name'=>'Indiquez le nom du commerce.','email'=>'E-mail invalide.','pass'=>'Mot de passe : 6 caractères min.',
        'pin'=>'Le code doit contenir 4 chiffres.','exists'=>'Un compte existe déjà avec cet e-mail.'];
      $err = $map[$r['error']] ?? 'Erreur, vérifiez vos informations.';
    }
  }
}
$old = fn($k) => e($_POST[$k] ?? '');
$types = ['Café','Restaurant','Boulangerie','Salon','Commerce'];
$te = ['Café'=>'☕','Restaurant'=>'🍽️','Boulangerie'=>'🥐','Salon'=>'💈','Commerce'=>'🛍️'];
$curType = $_POST['type'] ?? 'Café';
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title>Créer mon compte — Fidelo</title><meta name="theme-color" content="#0A2E38">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Instrument+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap">
<style>
:root{--em:#06B6D4;--em-d:#0891B2;--em-btn:#0B7C97;--or:#D9A94E;--lien:#0B7C97;--iv:#F3FCFD;--me:#E7F8FA;--me-d:#D6EEF1;--card:#fff;--line:#D6EEF1;--text:#1c2a25;--muted:#5a675f;--faint:#6f7c76;--deep:#0A2E38;--bad:#c0492f;
--disp:"Bricolage Grotesque",system-ui,sans-serif;--body:"Instrument Sans",system-ui,sans-serif;--mono:"IBM Plex Mono",monospace;}
@media(prefers-color-scheme:dark){:root{--em:#22D3EE;--em-d:#0891B2;--em-btn:#0E7F9B;--or:#e3ba63;--lien:#3FD3EE;--iv:#07222A;--me:#0B2C35;--me-d:#183B45;--card:#0E323C;--line:#1C4650;--text:#e6efea;--muted:#9fc4cc;--faint:#8fa39a;}}
*{box-sizing:border-box}body{margin:0;background:var(--iv);color:var(--text);font-family:var(--body);-webkit-font-smoothing:antialiased}
h1,h2{font-family:var(--disp);font-weight:600;letter-spacing:-.02em;margin:0}button{font-family:inherit;cursor:pointer;border:0}
.top{padding:18px 22px;max-width:520px;margin:0 auto}.brand{font-family:var(--disp);font-weight:700;font-size:20px}.brand i{color:var(--or);font-style:normal}
.wrap{max-width:520px;margin:0 auto;padding:6px 22px 60px}
.kick{font-family:var(--mono);font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:var(--lien);font-weight:600}
h1{font-size:clamp(26px,5vw,34px);margin:8px 0 6px}.lead{color:var(--muted);font-size:15.5px;margin-bottom:22px}
.err{background:color-mix(in srgb,var(--bad) 12%,transparent);border:1px solid var(--bad);color:var(--bad);padding:12px 14px;border-radius:12px;font-size:14px;margin-bottom:16px}
.f{margin-bottom:14px}.f label{display:block;font-size:13px;font-weight:600;margin-bottom:6px}.req{color:var(--or)}
.inp{width:100%;padding:13px 14px;border-radius:12px;border:1.5px solid var(--line);background:var(--card);color:var(--text);font-size:15px}
.inp:focus{outline:none;border-color:var(--em);box-shadow:0 0 0 4px color-mix(in srgb,var(--em) 14%,transparent)}
.hint{font-size:12px;color:var(--faint);margin-top:5px}.two{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.chips{display:flex;flex-wrap:wrap;gap:9px}.chip{padding:10px 14px;border-radius:11px;border:1.5px solid var(--line);background:var(--card);font-size:14px;font-weight:500;color:var(--muted)}
.chip.on{border-color:var(--em);background:color-mix(in srgb,var(--em) 10%,var(--card));color:var(--em-d);font-weight:600}
.sep{font-family:var(--mono);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--faint);margin:22px 0 12px;padding-top:16px;border-top:1px solid var(--line)}
.code{max-width:150px;text-align:center;letter-spacing:.5em;font-family:var(--mono);font-size:20px;font-weight:600}
.btn{width:100%;padding:15px;border-radius:13px;background:var(--em-btn);color:#fff;font-weight:700;font-size:15px;margin-top:8px}.btn:hover{background:#08637a}
.alt{text-align:center;margin-top:16px;font-size:14px;color:var(--muted)}.alt a{color:var(--lien);font-weight:600;text-decoration:none}
.trial{display:inline-flex;align-items:center;gap:7px;background:var(--me);border:1px solid var(--me-d);color:var(--em-d);padding:6px 12px;border-radius:99px;font-size:13px;font-weight:600;margin-bottom:16px}
.flogo{width:1.35em;height:1.35em;display:inline-block;vertical-align:-.32em;margin-right:.32em}
</style></head><body>
<div class="top"><span class="brand"><svg class="flogo" viewBox="0 0 64 64" aria-hidden="true"><defs><linearGradient id="ina" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#06B6D4"/><stop offset=".55" stop-color="#22D3EE"/><stop offset="1" stop-color="#FF6B6B"/></linearGradient><linearGradient id="inb" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".3"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#ina)"/><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#inb)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>Fidelo<i>.</i></span></div>
<div class="wrap">
  <span class="kick">Inscription commerçant</span>
  <h1>Créez votre compte</h1>
  <p class="lead">3 minutes, et vos clients cumulent des points dès aujourd'hui.</p>
  <span class="trial">✨ 10 jours d'essai gratuit — sans carte bancaire</span>
  <?php if($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
  <?php if (!$signupOpen): ?>
  <div style="background:#fdece8;border:1px solid #e4bcb2;color:#8c3a25;padding:14px 16px;border-radius:12px;margin-bottom:16px;font-size:14px">
    Les inscriptions en ligne sont momentanément fermées. Contactez-nous pour ouvrir votre compte.
  </div>
  <?php endif; ?>
  <form method="post" autocomplete="on">
    <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"
           style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="f"><label>Nom du commerce <span class="req">*</span></label><input class="inp" name="name" value="<?= $old('name') ?>" placeholder="Ex. Café des Amis" required></div>
    <div class="f"><label>Type d'établissement</label>
      <div class="chips" id="chips">
        <?php foreach($types as $t): ?><button type="button" class="chip <?= $t===$curType?'on':'' ?>" data-t="<?= e($t) ?>"><?= $te[$t] ?> <?= e($t) ?></button><?php endforeach; ?>
      </div><input type="hidden" name="type" id="typeInp" value="<?= e($curType) ?>"></div>
    <div class="two">
      <div class="f"><label>Ville</label><input class="inp" name="city" value="<?= $old('city') ?>" placeholder="Votre ville"><div class="hint">Optionnel.</div></div>
      <div class="f"><label>E-mail <span class="req">*</span></label><input class="inp" name="email" type="email" value="<?= $old('email') ?>" placeholder="vous@exemple.fr" required></div>
    </div>
    <div class="sep">🔒 Identifiants de connexion</div>
    <div class="two">
      <div class="f"><label>Mot de passe <span class="req">*</span></label><input class="inp" name="pass" type="password" placeholder="••••••••" minlength="6" required><div class="hint">6 caractères min.</div></div>
      <div class="f"><label>Confirmer <span class="req">*</span></label><input class="inp" id="pass2" type="password" placeholder="••••••••" required></div>
    </div>
    <div class="f"><label>Code d'accès rapide — 4 chiffres <span class="req">*</span></label>
      <input class="inp code" name="pin" id="pin" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="••••" required>
      <div class="hint">Pour déverrouiller vite ensuite (visage ou ce code).</div></div>
    <div class="f">
      <label for="promo">Code promo <span style="font-weight:400;color:var(--muted)">(facultatif)</span></label>
      <input class="inp" name="promo" id="promo" maxlength="24" placeholder="Ex. LANCEMENT15"
             value="<?= e($codePromo) ?>" style="text-transform:uppercase;letter-spacing:.06em">
      <?php if ($promoInfo && $promoInfo['ok']): ?>
        <div class="hint" style="color:#0B7C97;font-weight:600">✓ <?= e($promoInfo['promo']['label'] ?? 'Code valide') ?><?php
          if ($promoInfo['restant'] !== null) echo ' — il reste ' . (int)$promoInfo['restant'] . ' place' . ($promoInfo['restant']>1?'s':'');
        ?></div>
      <?php elseif ($promoInfo): ?>
        <div class="hint" style="color:var(--bad);font-weight:600"><?php
          echo ['inconnu'=>"Ce code n'existe pas.",'epuise'=>"Ce code est épuisé.",
                'expire'=>"Ce code a expiré.",'inactif'=>"Ce code n'est plus actif."][$promoInfo['error']] ?? "Code invalide.";
        ?></div>
      <?php endif; ?>
    </div>
    <?php if ($parrainNom !== ''): ?>
      <input type="hidden" name="p" value="<?= e($codeParr) ?>">
      <div style="background:color-mix(in srgb,var(--em) 10%,var(--card));border:1px solid var(--line);border-radius:12px;padding:12px 14px;margin-top:14px;font-size:13.5px">
        🤝 Vous êtes invité par <b><?= e($parrainNom) ?></b> — <b>2 mois de Fidelo complet offerts</b> pour vous deux.
      </div>
    <?php endif; ?>
    <button class="btn" type="submit">Créer mon compte &amp; démarrer</button>
    <p style="font-size:11.5px;color:var(--faint);line-height:1.6;margin-top:12px;text-align:center">
      En créant votre compte, vous acceptez nos
      <a href="mentions-legales.php" style="color:var(--lien)">mentions légales</a> et notre
      <a href="confidentialite.php" style="color:var(--lien)">politique de confidentialité</a>.
    </p>
  </form>
  <div class="alt">Déjà un compte ? <a href="<?= e($base) ?>/index.php">Se connecter</a></div>
</div>
<script>
const $=s=>document.querySelector(s);
$('#chips').onclick=e=>{const b=e.target.closest('.chip');if(!b)return;document.querySelectorAll('#chips .chip').forEach(c=>c.classList.remove('on'));b.classList.add('on');$('#typeInp').value=b.dataset.t;};
$('#pin').oninput=e=>{e.target.value=e.target.value.replace(/\D/g,'').slice(0,4);};
document.querySelector('form').addEventListener('submit',e=>{
  if(document.querySelector('[name=pass]').value!==$('#pass2').value){e.preventDefault();alert('Les mots de passe ne correspondent pas.');}
});
</script>
</body></html>
