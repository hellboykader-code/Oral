<?php
/* Fidelo — habillage commun des pages légales (mentions, confidentialité).
   N'affiche rien par lui-même : fournit legal_open() / legal_close(). */
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/nav.php';

function legal_icon(): string {
  return '<svg class="flogo" viewBox="0 0 64 64" aria-hidden="true"><defs><linearGradient id="lga" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#4F7A6C"/><stop offset=".55" stop-color="#7CA396"/><stop offset="1" stop-color="#A85630"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#lga)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>';
}

function legal_open(string $titre, string $desc, string $chapeau): void {
  $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
  $E = EDITEUR;
  ?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title><?= e($titre) ?> — Fidelo</title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="theme-color" content="#080F0D">
<link rel="icon" href="<?= e($base) ?>/icon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600&family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap">
<style>
:root{
  --ink:#17140F;--ink-2:#241E16;
  --paper:#FAF4E9;--card:#FFFCF5;--line:#E4D8C3;
  --tq:#4F7A6C;--tq-d:#3A5B50;--tq-l:#7CA396;
  --gold:#C79A5C;--gold-l:#E3C393;
  --coral:#A85630;--coral-d:#874323;
  --text:#241D14;--muted:#6E6353;--faint:#A0947F;
  --disp:"Playfair Display",Georgia,"Times New Roman",serif;
  --body:"Inter",-apple-system,system-ui,sans-serif;
  --mono:"IBM Plex Mono",monospace;
  --s1:.5rem;--s2:1rem;--s3:1.5rem;--s4:2rem;--s5:3rem;--s6:4rem;--s7:6rem;--s8:9rem;
  --ease:cubic-bezier(.16,1,.3,1);
  --co:var(--coral);--accent-txt:var(--tq-d);--bg-2:#F0E6D4;
  --grad:linear-gradient(118deg,var(--tq) 0%,var(--tq-l) 45%,var(--coral) 100%);
}
@media(prefers-color-scheme:dark){:root{
  --ink:#F5EEDF;--ink-2:#e8dfc9;--paper:#14100B;--card:#1E1811;--line:#33291C;
  --tq:#7CA396;--tq-d:#4F7A6C;--tq-l:#A9C7BC;--gold:#E3C393;--gold-l:#F0DDB2;
  --coral:#C87A50;--coral-d:#D89670;
  --text:#F1E9D8;--muted:#B8AC94;--faint:#7A6F5C;
  --co:var(--coral);--accent-txt:var(--tq-l);--bg-2:#241C12;}}
*{box-sizing:border-box}
html{scroll-behavior:auto;overflow-x:hidden}
body{margin:0;background:var(--paper);color:var(--text);font-family:var(--body);-webkit-font-smoothing:antialiased;line-height:1.7;overflow-x:hidden}
a{color:var(--accent-txt);text-decoration:none;font-weight:600}
a:hover{text-decoration:underline}
.wrap{max-width:820px;margin:0 auto;padding:0 var(--s3)}
.flogo{width:1.25em;height:1.25em;display:inline-block;vertical-align:-.28em;margin-right:.3em}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:11px 19px;border-radius:999px;font-weight:700;font-size:14.5px;border:0;text-decoration:none}
.btn-g{background:var(--card);border:1.6px solid var(--line);color:var(--text)}

#fnav{animation:navFade .6s var(--ease) both;transition:box-shadow .3s ease}
@keyframes navFade{from{opacity:0}to{opacity:1}}
#fnav.scrolled{box-shadow:0 1px 0 var(--line)}
@media(prefers-reduced-motion:reduce){#fnav{animation:none}}

.head{padding:var(--s5) 0 var(--s2)}
.eyebrow{font-family:var(--mono);font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:var(--accent-txt);font-weight:600}
h1{font-family:var(--disp);font-weight:700;font-size:clamp(1.9rem,5vw,2.9rem);line-height:1.08;letter-spacing:-.015em;margin:var(--s2) 0 0}
.chapeau{color:var(--muted);font-size:1.05rem;margin-top:var(--s2)}
.maj{font-family:var(--mono);font-size:12.5px;color:var(--faint);margin-top:var(--s3)}
h2{font-family:var(--disp);font-weight:700;font-size:1.4rem;letter-spacing:-.01em;margin:var(--s5) 0 0;padding-top:var(--s3);border-top:1px solid var(--line)}
h3{font-family:var(--disp);font-weight:600;font-size:1.1rem;margin:var(--s3) 0 0}
p{color:var(--text);font-size:.98rem;margin:.8rem 0 0}
ul{margin:.8rem 0 0;padding-left:20px}
li{font-size:.98rem;margin-top:.45rem}
.box{background:var(--card);border:1.5px solid var(--line);border-radius:16px;padding:var(--s3);margin-top:var(--s3)}
.box.key{border-color:var(--tq);background:var(--bg-2)}
.box p:first-child,.box ul:first-child{margin-top:0}
table{width:100%;border-collapse:collapse;margin-top:var(--s3);background:var(--card);border-radius:14px;overflow:hidden;font-size:.92rem;border:1px solid var(--line)}
th,td{padding:.75rem .85rem;text-align:left;border-bottom:1px solid var(--line);vertical-align:top}
thead th{background:var(--ink);color:var(--paper);font-family:var(--disp);font-weight:600;font-size:.92rem}
tbody tr:last-child td{border-bottom:0}
@media(max-width:700px){table{font-size:.82rem}th,td{padding:.55rem .6rem}}

.footcta{background:var(--ink);color:var(--paper);padding:var(--s6) 0;margin-top:var(--s6)}
.footcta h2{border-top:0;color:var(--paper);font-size:clamp(1.8rem,4vw,2.6rem);margin:0}
.footcta p{color:rgba(245,242,234,.7);margin-top:var(--s2)}
.footcta .btn{background:var(--tq-l);color:var(--ink);margin-top:var(--s3);font-weight:700}

footer.site{padding:var(--s5) 0 var(--s6);border-top:1px solid var(--line);margin-top:var(--s5)}
.foot-grid{display:flex;justify-content:space-between;align-items:center;gap:var(--s3);flex-wrap:wrap;color:var(--muted);font-size:.85rem}
.foot-grid a{color:var(--muted);font-weight:600}
.foot-grid .brand{color:var(--text);font-family:var(--disp);font-weight:800;font-size:1.1rem;text-decoration:none}.foot-grid .brand .d{color:var(--tq-d)}

#scrollbar{position:fixed;top:0;left:0;height:2px;width:0;z-index:200;background:var(--tq-d);transition:width .12s linear}
.up{position:fixed;right:20px;bottom:20px;z-index:150;width:46px;height:46px;border-radius:14px;background:var(--ink);color:var(--paper);display:grid;place-items:center;opacity:0;transform:translateY(18px);pointer-events:none;transition:opacity .25s,transform .25s,background .2s}
.up.on{opacity:1;transform:none;pointer-events:auto}
.up:hover{background:var(--tq-d)}.up svg{width:20px;height:20px}
@media(prefers-reduced-motion:reduce){#scrollbar{display:none}.up{transition:none}}
</style></head><body>
<div id="scrollbar"></div>
<?php nav_bar($base, ''); ?>

<main>
<div class="wrap">
  <div class="head">
    <span class="eyebrow">Informations légales</span>
    <h1><?= e($titre) ?></h1>
    <p class="chapeau"><?= $chapeau ?></p>
    <p class="maj">Dernière mise à jour : <?= e($E['maj']) ?></p>
  </div>
<?php
}

function legal_close(): void {
  $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
  $E = EDITEUR;
  ?>
</div>

<section class="footcta"><div class="wrap">
  <h2>Une question sur vos données ou votre compte ?</h2>
  <p>Écrivez-nous, nous répondons vite et en français.</p>
  <a href="<?= e($base) ?>/contact.php" class="btn">Nous contacter →</a>
</div></section>
</main>

<button id="up" class="up" aria-label="Remonter en haut"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg></button>

<footer class="site"><div class="wrap foot-grid">
  <a class="brand" href="<?= e($base) ?>/accueil.php">Fidelo<span class="d">.</span></a>
  <span style="display:flex;gap:15px;flex-wrap:wrap">
    <a href="<?= e($base) ?>/accueil.php">Accueil</a>
    <a href="<?= e($base) ?>/tarifs.php">Nos offres</a>
    <a href="<?= e($base) ?>/confidentialite.php">Confidentialité</a>
    <a href="<?= e($base) ?>/mentions-legales.php">Mentions légales</a>
    <a href="mailto:<?= e($E['email']) ?>"><?= e($E['email']) ?></a>
  </span>
  <span class="mono" style="font-size:12px;color:var(--faint)">© <?= date('Y') ?> <?= e($E['marque']) ?> — <?= e($E['societe']) ?>. <?= e($E['tva']) ?>.</span>
</div></footer>

<script id="pageScript">
window.__fideloPageInit=function(){
const reduce=matchMedia('(prefers-reduced-motion:reduce)').matches;
window.__fideloPageTeardown=function(){};
};
if(document.readyState!=='loading')window.__fideloPageInit();else document.addEventListener('DOMContentLoaded',window.__fideloPageInit);
</script>
</body></html>
<?php
}
