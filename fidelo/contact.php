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
<meta name="theme-color" content="#080F0D">
<link rel="icon" href="<?= e($base) ?>/icon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600&family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap">
<style>
:root{
  --ink:#080F0D;--ink-2:#122019;
  --paper:#F5F2EA;--card:#FFFFFF;--line:#E4DFD0;
  --tq:#0EA5B7;--tq-d:#0A7A88;--tq-l:#3FC6D6;
  --gold:#C99A3E;--gold-l:#E4C583;
  --coral:#E24E3F;--coral-d:#B93B2F;
  --text:#141A16;--muted:#5C6259;--faint:#8C9188;
  --disp:"Playfair Display",Georgia,"Times New Roman",serif;
  --body:"Inter",-apple-system,system-ui,sans-serif;
  --mono:"IBM Plex Mono",monospace;
  --s1:.5rem;--s2:1rem;--s3:1.5rem;--s4:2rem;--s5:3rem;--s6:4rem;--s7:6rem;--s8:9rem;
  --ease:cubic-bezier(.16,1,.3,1);
  --co:var(--coral);--accent-txt:var(--tq-d);--bg-2:#ECE7D8;
  --grad:linear-gradient(118deg,var(--tq) 0%,var(--tq-l) 45%,var(--coral) 100%);
}
@media(prefers-color-scheme:dark){:root{
  --ink:#F5F2EA;--ink-2:#e8e3d3;--paper:#0B100D;--card:#141A16;--line:#262C25;
  --tq:#3FC6D6;--tq-d:#7BDCE7;--tq-l:#9EE8F0;--gold:#E4C583;--gold-l:#F3DFA6;
  --coral:#FF8577;--coral-d:#FFA89E;
  --text:#F1EFE6;--muted:#A7ACA0;--faint:#6E7368;
  --co:var(--coral);--accent-txt:var(--tq-l);--bg-2:#1A211A;}}
*{box-sizing:border-box}
html{scroll-behavior:auto;overflow-x:hidden}
body{margin:0;background:var(--paper);color:var(--text);font-family:var(--body);-webkit-font-smoothing:antialiased;overflow-x:hidden}
h1,h2,h3,h4{font-family:var(--disp);font-weight:700;line-height:1.04;letter-spacing:-.015em;margin:0}
p{margin:0}a{color:inherit;text-decoration:none}button{font-family:inherit;cursor:pointer;border:0;background:none;color:inherit}
img{max-width:100%}[hidden]{display:none!important}.mono{font-family:var(--mono);font-variant-numeric:tabular-nums}
.wrap{max-width:1320px;margin:0 auto;padding-inline:var(--s3)}
.eyebrow{font-family:var(--mono);font-size:.78rem;letter-spacing:.16em;text-transform:uppercase;color:var(--tq-d);font-weight:600}
.flogo{width:1.35em;height:1.35em;display:inline-block;vertical-align:-.32em;margin-right:.34em}

.btn{display:inline-flex;align-items:center;gap:.55rem;padding:1.05rem 1.9rem;border-radius:999px;font-weight:700;font-size:.98rem;line-height:1;transition:transform .2s var(--ease),background .2s,color .2s}
.btn-p{background:var(--ink);color:var(--paper)}
.btn-p:hover{transform:translateY(-3px)}

#fnav{animation:navFade .6s var(--ease) both;transition:box-shadow .3s ease}
@keyframes navFade{from{opacity:0}to{opacity:1}}
#fnav.scrolled{box-shadow:0 1px 0 var(--line)}
@media(prefers-reduced-motion:reduce){#fnav{animation:none}}

.hero{padding:var(--s6) 0 var(--s5);text-align:center}
.hero h1{font-size:clamp(2.4rem,5.6vw,4.6rem);margin-top:var(--s3);max-width:16ch;margin-inline:auto}
.hero .lead{color:var(--muted);font-size:1.2rem;max-width:60ch;margin:var(--s3) auto 0;line-height:1.55}

.cine{position:relative;height:56vh;min-height:360px;max-height:560px;overflow:hidden;background:var(--ink);margin-top:var(--s5)}
.cine video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;background:var(--ink)}
.cine .scrim{position:absolute;inset:0;background:linear-gradient(0deg,rgba(8,15,13,.92) 0%,rgba(8,15,13,.35) 42%,rgba(8,15,13,.05) 68%,transparent 100%)}
.cine .cap{position:absolute;left:0;right:0;bottom:0;padding:var(--s5) 0 var(--s5)}
.cine .cap .wrap{display:flex;align-items:flex-end;justify-content:space-between;gap:var(--s4);flex-wrap:wrap}
.cine .cap .eyebrow{color:var(--tq-l)}
.cine .cap h3{font-size:clamp(1.5rem,3vw,2.3rem);color:var(--paper);max-width:18ch;margin-top:var(--s1)}
.cine .cap .mono{color:rgba(245,242,234,.55);font-family:var(--mono);font-size:12.5px;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap;padding-bottom:.4rem}
@media(max-width:640px){.cine{height:46vh;min-height:300px}.cine .cap .wrap{align-items:flex-start}}

.cols{display:grid;grid-template-columns:1.1fr .9fr;gap:var(--s4);margin:var(--s6) 0;align-items:start}
@media(max-width:820px){.cols{grid-template-columns:1fr;gap:var(--s3)}}
.card{background:var(--card);border:1.5px solid var(--line);border-radius:24px;padding:var(--s4)}
.card h2{font-size:1.5rem}
.card .sub{color:var(--muted);font-size:.95rem;margin-top:.3rem;margin-bottom:var(--s3);line-height:1.5}
label{display:block;font-size:.85rem;font-weight:600;color:var(--muted);margin:var(--s3) 0 .4rem}
.inp{width:100%;padding:.85rem .9rem;border-radius:12px;border:1.6px solid var(--line);background:var(--paper);color:var(--text);font-size:1rem;font-family:inherit}
.inp:focus{outline:0;border-color:var(--tq);box-shadow:0 0 0 3px rgba(14,165,183,.16)}
textarea.inp{min-height:130px;resize:vertical}
.hp{position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden}
.card .btn{width:100%;justify-content:center;margin-top:var(--s3)}
.err{background:rgba(226,78,63,.1);color:var(--coral-d);border:1px solid rgba(226,78,63,.3);border-radius:12px;padding:.8rem .9rem;font-size:.9rem;font-weight:600;margin-bottom:.4rem}
.ok{text-align:center;padding:.6rem 0}
.ok .big{width:66px;height:66px;border-radius:50%;background:var(--tq);display:grid;place-items:center;margin:0 auto var(--s3)}
.ok .big svg{width:30px;height:30px;color:#fff}
.ok h2{font-size:1.5rem}
.ok p{color:var(--muted);margin-top:.6rem;line-height:1.6}
.ok .btn{max-width:260px;margin:var(--s4) auto 0}
.side .row{display:flex;gap:.8rem;align-items:flex-start;padding:var(--s3) 0;border-bottom:1px solid var(--line)}
.side .row:last-child{border-bottom:0}
.side .ic{width:42px;height:42px;border-radius:12px;background:var(--bg-2);display:grid;place-items:center;flex-shrink:0;font-size:1.15rem}
.side .row b{display:block;font-size:.95rem;margin-bottom:.15rem}
.side .row span,.side .row a{color:var(--muted);font-size:.88rem;line-height:1.5}
.side .row a{color:var(--accent-txt);font-weight:600}
.mapsec{margin:0 0 var(--s6)}
.mapsec h2{font-size:1.5rem}
.mapsec .sub{color:var(--muted);font-size:.95rem;margin:.3rem 0 var(--s3)}
.mapwrap{border-radius:24px;overflow:hidden;border:1.5px solid var(--line);height:360px;box-shadow:0 24px 48px -30px rgba(14,165,183,.35)}
.mapwrap iframe{width:100%;height:100%;border:0;display:block;filter:saturate(1.05)}

.footcta{background:var(--ink);color:var(--paper);padding:var(--s7) 0}
.footcta h2{font-size:clamp(2.6rem,7vw,5.4rem);color:var(--paper);max-width:15ch}
.footcta p{color:rgba(245,242,234,.7);font-size:1.2rem;margin:var(--s3) 0 var(--s4);max-width:46ch}
.btn-out{border:1.5px solid rgba(245,242,234,.5);color:var(--paper)}
.btn-out:hover{background:rgba(245,242,234,.1);transform:translateY(-3px)}

footer.site{padding:var(--s5) 0 var(--s6);border-top:1px solid var(--line)}
.foot-grid{display:flex;justify-content:space-between;align-items:center;gap:var(--s3);flex-wrap:wrap;color:var(--muted);font-size:.9rem}
.foot-grid .brand{color:var(--text);font-family:var(--disp);font-weight:800;font-size:1.2rem}.foot-grid .brand .d{color:var(--tq-d)}

#scrollbar{position:fixed;top:0;left:0;height:2px;width:0;z-index:200;background:var(--tq-d);transition:width .12s linear}
.reveal-ready .rv{opacity:0;transform:translateY(50px);transition:opacity .9s var(--ease),transform .9s var(--ease);transition-delay:var(--d,0s)}
.reveal-ready .rv.in{opacity:1;transform:none}
.up{position:fixed;right:20px;bottom:20px;z-index:150;width:46px;height:46px;border-radius:14px;background:var(--ink);color:var(--paper);display:grid;place-items:center;opacity:0;transform:translateY(18px);pointer-events:none;transition:opacity .25s,transform .25s,background .2s}
.up.on{opacity:1;transform:none;pointer-events:auto}
.up:hover{background:var(--tq-d)}.up svg{width:20px;height:20px}
@media(prefers-reduced-motion:reduce){.reveal-ready .rv{opacity:1!important;transform:none!important}#scrollbar{display:none}.up{transition:none}}
</style></head><body>
<div id="scrollbar"></div>
<?php nav_bar($base, 'contact'); ?>

<main>
<section class="hero rv"><div class="wrap">
  <span class="eyebrow">Contact</span>
  <h1>Une question ? Écrivez-nous.</h1>
  <p class="lead">Nous répondons vite, en français, sans engagement. Que vous hésitiez encore ou que vous soyez déjà prêt à démarrer, nous sommes là pour vous aider.</p>
</div></section>

<!-- MOMENT — l'accueil, en vrai -->
<section class="cine rv" id="cine">
  <video id="cineVid" muted loop playsinline preload="none" poster="<?= e($base) ?>/media/contact-cinematic-poster.jpg" data-src="<?= e($base) ?>/media/contact-cinematic.mp4" aria-label="Un commerçant accueille chaleureusement un client à l'entrée de sa boutique, lumière douce."></video>
  <div class="scrim"></div>
  <div class="cap"><div class="wrap">
    <div><span class="eyebrow">L'accueil, en vrai</span><h3>On répond comme on accueille : vite, avec le sourire.</h3></div>
    <span class="mono">huit secondes · sans engagement</span>
  </div></div>
</section>

<div class="wrap">
  <div class="cols">
    <!-- Formulaire -->
    <div class="card rv">
      <?php if ($sent): ?>
        <div class="ok">
          <div class="big"><?= $CK ?></div>
          <h2>Message envoyé !</h2>
          <p>Merci, nous avons bien reçu votre message et nous vous répondrons rapidement à l'adresse indiquée.</p>
          <a class="btn btn-p" href="<?= e($base) ?>/accueil.php">Retour à l'accueil</a>
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
          <button class="btn btn-p" type="submit">Envoyer le message</button>
        </form>
      <?php endif; ?>
    </div>

    <!-- Coordonnées -->
    <div class="card side rv">
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

  <section class="mapsec rv">
    <h2>Nous trouver</h2>
    <div class="sub"><?= e($E['cp_ville']) ?> · <a href="<?= e($E['maps']) ?>" target="_blank" rel="noopener" style="color:var(--accent-txt);font-weight:600">Ouvrir dans Google Maps ↗</a></div>
    <div class="mapwrap">
      <iframe src="https://maps.google.com/maps?q=4%20Av.%20du%20Mar%C3%A9chal%20de%20Lattre%20de%20Tassigny%2C%2094000%20Cr%C3%A9teil&z=15&output=embed"
        loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Localisation Fidelo — Créteil"></iframe>
    </div>
  </section>
</div>

<!-- CTA FINAL -->
<section class="footcta"><div class="wrap rv">
  <h2>Prêt à faire revenir vos clients ?</h2>
  <p>Inscription gratuite, jusqu'à 30 clients sans limite de durée. Pas de carte bancaire demandée.</p>
  <a href="<?= e($base) ?>/inscription.php" class="btn btn-out">Créer mon compte gratuitement →</a>
</div></section>
</main>

<button id="up" class="up" aria-label="Remonter en haut"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg></button>

<footer class="site"><div class="wrap foot-grid">
  <a class="brand" href="<?= e($base) ?>/accueil.php">Fidelo<span class="d">.</span></a>
  <span>Le programme de fidélité des commerces de proximité.</span>
  <span style="display:flex;gap:15px;flex-wrap:wrap">
    <a href="<?= e($base) ?>/accueil.php" style="color:var(--muted);font-weight:600">Accueil</a>
    <a href="<?= e($base) ?>/tarifs.php" style="color:var(--muted);font-weight:600">Nos offres</a>
    <a href="<?= e($base) ?>/confidentialite.php" style="color:var(--muted);font-weight:600">Confidentialité</a>
    <a href="<?= e($base) ?>/mentions-legales.php" style="color:var(--muted);font-weight:600">Mentions légales</a>
  </span>
  <span class="mono" style="font-size:12px;color:var(--faint)">© <?= date('Y') ?> <?= e($E['marque']) ?></span>
</div></footer>

<script id="pageScript">
window.__fideloPageInit=function(){
const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)];
const reduce=matchMedia('(prefers-reduced-motion:reduce)').matches;
const teardown=[];
window.__fideloPageTeardown=function(){teardown.forEach(fn=>{try{fn();}catch(e){}});teardown.length=0;};

document.body.classList.add('reveal-ready');

if(reduce){$$('.rv').forEach(el=>el.classList.add('in'));}
else{
  const rio=new IntersectionObserver(es=>es.forEach(e=>{
    if(e.isIntersecting){e.target.classList.add('in');rio.unobserve(e.target);}
  }),{threshold:.12,rootMargin:'0px 0px -6% 0px'});
  $$('.rv').forEach(el=>rio.observe(el));
  teardown.push(()=>rio.disconnect());
}

const cineVid=$('#cineVid');
if(cineVid&&!reduce){
  let loaded=false;
  const cio=new IntersectionObserver(es=>es.forEach(e=>{
    if(!loaded&&e.isIntersecting){
      loaded=true;
      const s=document.createElement('source');s.src=cineVid.getAttribute('data-src');s.type='video/mp4';
      cineVid.appendChild(s);cineVid.load();
    }
    if(loaded){if(e.isIntersecting)cineVid.play().catch(()=>{});else cineVid.pause();}
  }),{threshold:.25});
  cio.observe($('#cine'));
  teardown.push(()=>{cio.disconnect();try{cineVid.pause();}catch(e){}});
}
};
if(document.readyState!=='loading')window.__fideloPageInit();else document.addEventListener('DOMContentLoaded',window.__fideloPageInit);
</script>
</body></html>
