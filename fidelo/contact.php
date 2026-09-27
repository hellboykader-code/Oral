<?php
/* Fidelo — page Contact (formulaire auto-hébergé, envoi par mail()). */
require __DIR__ . '/lib.php';
require __DIR__ . '/nav.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$E = EDITEUR;

$sent = false; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom = mb_substr(trim($_POST['name'] ?? ''), 0, 80);
  $eml = mb_substr(trim($_POST['email'] ?? ''), 0, 120);
  $msg = mb_substr(trim($_POST['message'] ?? ''), 0, 2000);
  $hp  = trim($_POST['website'] ?? '');            // piège à robots
  if ($hp !== '') { $err = 'Erreur, réessayez.'; }
  elseif ($nom === '' || $msg === '' || !filter_var($eml, FILTER_VALIDATE_EMAIL)) {
    $err = 'Merci de remplir votre nom, un e-mail valide et votre message.';
  } else {
    try { $db = db_load(); } catch (Throwable $e) { $db = null; }
    if ($db !== null && !rate_hit($db, 'contact:' . client_ip(), 5, 3600)) {
      db_save($db); $err = 'Trop de messages envoyés. Réessayez plus tard.';
    } else {
      if ($db !== null) db_save($db);
      $to = $E['inbox'] ?? $E['email'];
      $subject = 'Contact Fidelo — ' . $nom;
      $body = "Nom : $nom\r\nE-mail : $eml\r\n\r\nMessage :\r\n$msg\r\n\r\n—\r\nEnvoyé depuis fidelo.site le " . date('d/m/Y H:i');
      $headers = "From: Fidelo <" . $E['email'] . ">\r\n"
               . "Reply-To: " . $eml . "\r\n"
               . "Content-Type: text/plain; charset=UTF-8\r\n";
      @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
      $sent = true;
    }
  }
}
$old = fn($k) => e($_POST[$k] ?? '');
$CK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg>';
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title>Contact — Fidelo</title>
<meta name="description" content="Une question sur Fidelo ? Écrivez-nous : nous répondons vite, en français, sans engagement.">
<meta name="theme-color" content="#06B6D4">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap">
<style>
:root{
  --tq:#06B6D4;--tq-d:#0891B2;--co:#FF6B6B;--co-d:#F14E4E;--am-d:#F7B500;
  --ink-fixed:#0A2E38;--accent-txt:#0B7C97;
  --bg:#F3FCFD;--bg-2:#E7F8FA;--card:#ffffff;--line:#D6EEF1;
  --text:#0F343D;--muted:#4e767f;--faint:#7d9ea7;
  --disp:"Bricolage Grotesque",-apple-system,system-ui,sans-serif;
  --body:"Plus Jakarta Sans",-apple-system,system-ui,sans-serif;--mono:"IBM Plex Mono",monospace;
  --grad:linear-gradient(118deg,#06B6D4 0%,#22D3EE 38%,#FF6B6B 100%);
}
@media(prefers-color-scheme:dark){:root{
  --tq:#22D3EE;--tq-d:#06B6D4;--co:#FF8080;--co-d:#FF6B6B;--accent-txt:#7DE9FB;--ink-fixed:#04161c;
  --bg:#07222a;--bg-2:#0b2c35;--card:#0e323c;--line:#1c4650;
  --text:#EAFBFD;--muted:#9fc4cc;--faint:#6f939b;}}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:var(--body);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
.wrap{max-width:1000px;margin:0 auto;padding:0 22px}
.hero{text-align:center;padding:46px 0 6px}
.eyebrow{font-family:var(--mono);font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:var(--accent-txt);font-weight:600}
.hero h1{font-family:var(--disp);font-weight:800;font-size:clamp(32px,5vw,50px);line-height:1.05;letter-spacing:-.03em;margin:12px 0 0}
.hero p{color:var(--muted);font-size:17.5px;max-width:600px;margin:14px auto 0;line-height:1.6}
.cols{display:grid;grid-template-columns:1.1fr .9fr;gap:26px;margin:40px 0 70px;align-items:start}
@media(max-width:820px){.cols{grid-template-columns:1fr;gap:20px}}
.card{background:var(--card);border:2px solid var(--line);border-radius:22px;padding:28px 26px}
.card h2{font-family:var(--disp);font-weight:700;font-size:21px;margin:0 0 4px}
.card .sub{color:var(--muted);font-size:14.5px;margin-bottom:18px;line-height:1.5}
label{display:block;font-size:13px;font-weight:600;color:var(--muted);margin:14px 0 6px}
.inp{width:100%;padding:13px 14px;border-radius:12px;border:1.6px solid var(--line);background:var(--bg);color:var(--text);font-size:15px;font-family:inherit}
.inp:focus{outline:0;border-color:var(--tq);box-shadow:0 0 0 3px rgba(6,182,212,.16)}
textarea.inp{min-height:130px;resize:vertical}
.hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;width:100%;padding:15px;border-radius:13px;font-weight:700;font-size:15px;border:0;cursor:pointer;font-family:inherit;background:var(--grad);color:#fff;box-shadow:0 14px 30px -12px rgba(6,182,212,.7);margin-top:18px;transition:transform .15s}
.btn:hover{transform:translateY(-2px)}
.err{background:rgba(241,78,78,.12);color:var(--co-d);border:1px solid rgba(241,78,78,.3);border-radius:12px;padding:12px 14px;font-size:14px;font-weight:600;margin-bottom:6px}
.ok{text-align:center;padding:10px 0}
.ok .big{width:66px;height:66px;border-radius:50%;background:var(--grad);display:grid;place-items:center;margin:0 auto 14px}
.ok .big svg{width:30px;height:30px;color:#fff}
.ok h2{font-family:var(--disp);font-weight:800;font-size:24px;margin:0}
.ok p{color:var(--muted);margin:10px 0 0;line-height:1.6}
.side .row{display:flex;gap:13px;align-items:flex-start;padding:16px 0;border-bottom:1px solid var(--line)}
.side .row:last-child{border-bottom:0}
.side .ic{width:42px;height:42px;border-radius:12px;background:var(--bg-2);display:grid;place-items:center;flex-shrink:0;font-size:19px}
.side .row b{display:block;font-size:15px;margin-bottom:2px}
.side .row span,.side .row a{color:var(--muted);font-size:14px;line-height:1.5}
.side .row a{color:var(--accent-txt);font-weight:600}
.mapsec{margin:6px 0 66px}
.mapsec h2{font-family:var(--disp);font-weight:700;font-size:21px;margin:0 0 6px}
.mapsec .sub{color:var(--muted);font-size:14.5px;margin-bottom:16px}
.mapwrap{border-radius:22px;overflow:hidden;border:2px solid var(--line);height:360px;box-shadow:0 24px 48px -30px rgba(6,182,212,.4)}
.mapwrap iframe{width:100%;height:100%;border:0;display:block;filter:saturate(1.05)}
.foot{padding:38px 0 60px;border-top:2px solid var(--line);color:var(--muted);font-size:13.5px;text-align:center}
.foot .ll{display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-bottom:10px}
.foot .ll a{color:var(--accent-txt);font-weight:700}
</style></head><body>

<?php nav_bar($base, 'contact'); ?>

<div class="wrap">
  <section class="hero">
    <span class="eyebrow">Contact</span>
    <h1>Une question ? Écrivez-nous.</h1>
    <p>Nous répondons vite, en français, sans engagement. Que vous hésitiez encore ou que vous soyez déjà prêt à démarrer, nous sommes là pour vous aider.</p>
  </section>

  <div class="cols">
    <!-- Formulaire -->
    <div class="card">
      <?php if ($sent): ?>
        <div class="ok">
          <div class="big"><?= $CK ?></div>
          <h2>Message envoyé !</h2>
          <p>Merci, nous avons bien reçu votre message et nous vous répondrons rapidement à l'adresse indiquée.</p>
          <a class="btn" href="<?= e($base) ?>/accueil.php" style="max-width:260px;margin:22px auto 0">Retour à l'accueil</a>
        </div>
      <?php else: ?>
        <h2>Envoyez-nous un message</h2>
        <div class="sub">Décrivez votre besoin en quelques mots — nous revenons vers vous sous 24 h ouvrées.</div>
        <?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
        <form method="post" action="contact.php" novalidate>
          <div class="hp"><label>Ne pas remplir<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
          <label for="name">Votre nom</label>
          <input class="inp" id="name" name="name" maxlength="80" value="<?= $old('name') ?>" required>
          <label for="email">Votre e-mail</label>
          <input class="inp" id="email" name="email" type="email" maxlength="120" value="<?= $old('email') ?>" placeholder="vous@exemple.fr" required>
          <label for="message">Votre message</label>
          <textarea class="inp" id="message" name="message" maxlength="2000" placeholder="Bonjour, je gère un salon et j'aimerais savoir…" required><?= $old('message') ?></textarea>
          <button class="btn" type="submit">Envoyer le message</button>
        </form>
      <?php endif; ?>
    </div>

    <!-- Coordonnées -->
    <div class="card side">
      <h2>Nos coordonnées</h2>
      <div class="sub">Vous préférez le contact direct ? Voici comment nous joindre.</div>
      <div class="row">
        <div class="ic">💬</div>
        <div><b>WhatsApp</b><a href="https://wa.me/<?= e($E['tel_raw']) ?>" target="_blank" rel="noopener">Discuter sur WhatsApp — réponse rapide</a></div>
      </div>
      <div class="row">
        <div class="ic">📞</div>
        <div><b>Téléphone</b><a href="tel:+<?= e($E['tel_raw']) ?>"><?= e($E['tel']) ?></a></div>
      </div>
      <div class="row">
        <div class="ic">✉️</div>
        <div><b>E-mail</b><a href="mailto:<?= e($E['email']) ?>"><?= e($E['email']) ?></a></div>
      </div>
      <div class="row">
        <div class="ic">⏱️</div>
        <div><b>Délai de réponse</b><span>Sous 24 h ouvrées, du lundi au vendredi.</span></div>
      </div>
      <div class="row">
        <div class="ic">🚀</div>
        <div><b>Envie d'essayer d'abord ?</b><a href="<?= e($base) ?>/inscription.php">Créer un compte gratuit →</a></div>
      </div>
      <div class="row">
        <div class="ic">🏢</div>
        <div><b><?= e($E['societe']) ?></b><span><?= e($E['cp_ville']) ?><br>SIRET <?= e($E['siret']) ?><br><a href="<?= e($E['maps']) ?>" target="_blank" rel="noopener">📍 Voir sur Google Maps</a></span></div>
      </div>
    </div>
  </div>

  <section class="mapsec">
    <h2>Nous trouver</h2>
    <div class="sub"><?= e($E['cp_ville']) ?> · <a href="<?= e($E['maps']) ?>" target="_blank" rel="noopener" style="color:var(--accent-txt);font-weight:600">Ouvrir dans Google Maps ↗</a></div>
    <div class="mapwrap">
      <iframe src="https://maps.google.com/maps?q=4%20Av.%20du%20Mar%C3%A9chal%20de%20Lattre%20de%20Tassigny%2C%2094000%20Cr%C3%A9teil&z=15&output=embed"
        loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Localisation Fidelo — Créteil"></iframe>
    </div>
</div>

<footer class="foot"><div class="wrap">
  <div class="ll">
    <a href="<?= e($base) ?>/accueil.php">Accueil</a>
    <a href="<?= e($base) ?>/tarifs.php">Nos offres</a>
    <a href="<?= e($base) ?>/confidentialite.php">Confidentialité</a>
    <a href="<?= e($base) ?>/mentions-legales.php">Mentions légales</a>
  </div>
  <p>© <?= date('Y') ?> <?= e($E['marque']) ?> — <?= e($E['societe']) ?>. <?= e($E['tva']) ?>.</p>
</div></footer>
</body></html>
