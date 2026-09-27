<?php
/* Fidelo — page des offres (4 formules : Découverte, Mensuel, Annuel, À vie). */
require __DIR__ . '/lib.php';
require __DIR__ . '/nav.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

/* Places restantes de l'offre de lancement (LANCEMENT15), lues en direct. */
$lancRestant = null;
try {
  $dbT = db_load();
  $lp = $dbT['settings']['promos']['LANCEMENT15'] ?? null;
  if ($lp && !empty($lp['actif']) && !empty($lp['max'])) {
    $lancRestant = max(0, (int)$lp['max'] - (int)($lp['utilise'] ?? 0));
  }
} catch (Throwable $e) { $lancRestant = null; }
$FREE = defined('PLAN_FREE_MAX') ? PLAN_FREE_MAX : 30;
$CK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg>';
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title>Nos offres — Fidelo</title>
<meta name="description" content="Commencez gratuitement jusqu'à 30 clients, sans limite de durée. Puis 29 €/mois, 250 €/an, ou 525 € à vie avec un site internet professionnel offert.">
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
body{margin:0;background:var(--paper);color:var(--text);font-family:var(--body);-webkit-font-smoothing:antialiased;overflow-x:hidden}
h1,h2,h3,h4{font-family:var(--disp);font-weight:700;line-height:1.04;letter-spacing:-.015em;margin:0}
p{margin:0}a{color:inherit;text-decoration:none}button{font-family:inherit;cursor:pointer;border:0;background:none;color:inherit}
img{max-width:100%}[hidden]{display:none!important}.mono{font-family:var(--mono);font-variant-numeric:tabular-nums}
.wrap{max-width:1320px;margin:0 auto;padding-inline:var(--s3)}
.eyebrow{font-family:var(--mono);font-size:.78rem;letter-spacing:.16em;text-transform:uppercase;color:var(--tq-d);font-weight:600}
.rule{height:1px;background:var(--line);border:0}
.flogo{width:1.35em;height:1.35em;display:inline-block;vertical-align:-.32em;margin-right:.34em}

.btn{display:inline-flex;align-items:center;gap:.55rem;padding:1.05rem 1.9rem;border-radius:999px;font-weight:700;font-size:.98rem;line-height:1;transition:transform .2s var(--ease),background .2s,color .2s}
.btn-p{background:var(--ink);color:var(--paper)}
.btn-p:hover{transform:translateY(-3px)}
.btn-line{padding:0;border-radius:0;font-weight:700;color:var(--text);border-bottom:2px solid var(--ink)}
.btn-line:hover{color:var(--tq-d);border-color:var(--tq-d)}
.btn-out{border:1.5px solid rgba(245,242,234,.5);color:var(--paper)}
.btn-out:hover{background:rgba(245,242,234,.1);transform:translateY(-3px)}
.btn-ghost{border:1.5px solid var(--line);color:var(--text)}
.btn-ghost:hover{transform:translateY(-3px);border-color:var(--ink)}

#fnav{animation:navFade .6s var(--ease) both;transition:box-shadow .3s ease}
@keyframes navFade{from{opacity:0}to{opacity:1}}
#fnav.scrolled{box-shadow:0 1px 0 var(--line)}
@media(prefers-reduced-motion:reduce){#fnav{animation:none}}

/* HERO — centré, plus sobre que l'accueil, même grammaire typographique */
.hero{padding:var(--s6) 0 var(--s5);text-align:center}
.hero .eyebrow{display:block}
.hero h1{font-size:clamp(2.4rem,5.6vw,4.6rem);margin-top:var(--s3);max-width:18ch;margin-inline:auto}
.hero .lead{color:var(--muted);font-size:1.2rem;max-width:60ch;margin:var(--s3) auto 0;line-height:1.55}

/* bandeau cinématique — identique système que l'accueil */
.cine{position:relative;height:64vh;min-height:400px;max-height:620px;overflow:hidden;background:var(--ink);margin-top:var(--s5)}
.cine video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;background:var(--ink)}
.cine .scrim{position:absolute;inset:0;background:linear-gradient(0deg,rgba(8,15,13,.92) 0%,rgba(8,15,13,.35) 42%,rgba(8,15,13,.05) 68%,transparent 100%)}
.cine .cap{position:absolute;left:0;right:0;bottom:0;padding:var(--s5) 0 var(--s5)}
.cine .cap .wrap{display:flex;align-items:flex-end;justify-content:space-between;gap:var(--s4);flex-wrap:wrap}
.cine .cap .eyebrow{color:var(--tq-l)}
.cine .cap h3{font-size:clamp(1.6rem,3.2vw,2.5rem);color:var(--paper);max-width:18ch;margin-top:var(--s1)}
.cine .cap .mono{color:rgba(245,242,234,.55);font-family:var(--mono);font-size:12.5px;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap;padding-bottom:.4rem}
@media(max-width:640px){.cine{height:52vh;min-height:340px}.cine .cap .wrap{align-items:flex-start}}

section.band{padding:var(--s7) 0}
.head{max-width:680px}
.head.center{max-width:680px;margin-inline:auto;text-align:center}
.head h2{font-size:clamp(2.2rem,5vw,3.8rem);margin-top:var(--s2)}
.head p{color:var(--muted);font-size:1.15rem;margin-top:var(--s3);line-height:1.5}

/* ── Découverte : panneau gratuit plein-large ── */
.free{margin-top:var(--s6);border:2px solid var(--tq);border-radius:28px;background:var(--card);
  padding:var(--s5);display:grid;grid-template-columns:1.3fr 1fr;gap:var(--s5);align-items:center;position:relative;overflow:hidden}
.free::before{content:"";position:absolute;inset:0;background:radial-gradient(120% 140% at 100% 0,rgba(14,165,183,.1),transparent 55%);pointer-events:none}
.free>*{position:relative;z-index:1}
.free .tag{display:inline-flex;align-items:center;gap:8px;background:var(--bg-2);border:1.5px solid var(--line);border-radius:99px;padding:6px 14px;font-size:12.5px;font-weight:800;font-family:var(--mono);letter-spacing:.06em;text-transform:uppercase;color:var(--accent-txt)}
.free h2{font-size:clamp(1.7rem,3.2vw,2.4rem);margin-top:var(--s3)}
.free .price{font-family:var(--disp);font-weight:800;font-size:3rem;line-height:1;color:var(--tq-d);margin-top:var(--s2)}
.free .price small{font-size:1rem;color:var(--muted);font-weight:600;font-family:var(--body)}
.free p.d{color:var(--muted);font-size:1.02rem;line-height:1.6;margin-top:var(--s3);max-width:54ch}
.free .cta{margin-top:var(--s4)}
.free ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:var(--s2)}
.free li{display:flex;gap:10px;font-size:1rem;align-items:flex-start;line-height:1.5}
.free li svg{width:19px;height:19px;flex-shrink:0;color:var(--tq);margin-top:3px}
@media(max-width:900px){.free{grid-template-columns:1fr}}

/* ── 3 formules payantes ── */
.packs{display:grid;grid-template-columns:repeat(3,1fr);gap:var(--s4);margin-top:var(--s5);align-items:start}
@media(max-width:980px){.packs{grid-template-columns:1fr;max-width:560px;margin-inline:auto}}
.pack{background:var(--card);border:1.5px solid var(--line);border-radius:24px;padding:var(--s4);position:relative;display:flex;flex-direction:column;height:100%}
.pack.best{border-color:var(--tq);box-shadow:0 30px 60px -32px rgba(14,165,183,.4)}
.pack.life{border-color:var(--gold);box-shadow:0 30px 60px -32px rgba(201,154,62,.35)}
.pack .flag{position:absolute;top:-13px;left:50%;transform:translateX(-50%);white-space:nowrap;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:6px 15px;border-radius:99px;font-family:var(--mono)}
.pack.best .flag{background:var(--grad);color:#fff}
.pack.life .flag{background:linear-gradient(118deg,var(--gold),var(--gold-l));color:#3a2a05}
.pack h3{font-size:1.5rem;margin-top:var(--s2)}
.pack .sub{color:var(--muted);font-size:.92rem;min-height:42px;margin-top:.3rem;line-height:1.5}
.pack .tag{display:flex;align-items:baseline;gap:8px;margin-top:var(--s3);flex-wrap:wrap}
.pack .amt{font-family:var(--disp);font-weight:800;font-size:2.6rem;line-height:1;color:var(--ink)}
.pack .per{color:var(--muted);font-size:.9rem;font-weight:600}
.pack .equiv{font-size:.8rem;color:var(--muted);font-family:var(--mono);margin-top:.3rem;min-height:18px}
.pack .save{display:inline-block;margin-top:.6rem;background:rgba(14,165,183,.1);color:var(--accent-txt);font-size:.78rem;font-weight:700;padding:5px 11px;border-radius:8px}
.pack.life .save{background:rgba(201,154,62,.16);color:var(--gold)}
.pack .places{display:inline-flex;align-items:center;gap:7px;margin-top:.6rem;background:rgba(226,78,63,.1);color:var(--coral-d);font-size:.78rem;font-weight:800;padding:6px 12px;border-radius:8px;font-family:var(--mono)}
.pack .places .dot{width:8px;height:8px;border-radius:50%;background:var(--coral-d);box-shadow:0 0 0 0 rgba(226,78,63,.5);animation:pulse 1.8s infinite}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(226,78,63,.5)}70%{box-shadow:0 0 0 7px rgba(226,78,63,0)}100%{box-shadow:0 0 0 0 rgba(226,78,63,0)}}
.pack ul{list-style:none;padding:0;margin:var(--s3) 0;display:flex;flex-direction:column;gap:.65rem}
.pack li{display:flex;gap:9px;font-size:.92rem;align-items:flex-start;line-height:1.5}
.pack li svg{width:17px;height:17px;flex-shrink:0;color:var(--tq);margin-top:3px}
.pack.life li svg{color:var(--gold)}
.pack li b{font-weight:700}
.pack .detail{background:var(--bg-2);border:1px solid var(--line);border-radius:14px;padding:.9rem 1rem;margin-top:auto;font-size:.86rem;color:var(--muted);line-height:1.6}
.pack .detail b{color:var(--text)}
.pack .cta{margin-top:var(--s3)}
.pack .cta .btn{width:100%;justify-content:center}
@media(prefers-reduced-motion:reduce){.pack .places .dot{animation:none}}

/* ── comparatif ── */
.cmptable{width:100%;border-collapse:collapse;background:var(--card);border-radius:20px;overflow:hidden;box-shadow:0 20px 44px -30px rgba(14,165,183,.4);font-size:.92rem}
.cmptable th,.cmptable td{padding:.9rem .85rem;text-align:left;border-bottom:1px solid var(--line)}
.cmptable thead th{background:var(--ink);color:var(--paper);font-family:var(--disp);font-weight:600;font-size:.95rem}
.cmptable thead th small{font-weight:400;opacity:.7;font-family:var(--body)}
.cmptable tbody tr:last-child td{border-bottom:0}
.cmptable td.c{text-align:center;font-weight:700}
.cmptable .oui{color:var(--accent-txt)}.cmptable .non{color:var(--muted)}
@media(max-width:820px){.cmptable{font-size:.78rem}.cmptable th,.cmptable td{padding:.55rem .45rem}}

.faq{max-width:820px;margin-inline:auto}
.faq details{border-top:1px solid var(--line);padding:var(--s3) 0}
.faq details:last-child{border-bottom:1px solid var(--line)}
.faq summary{list-style:none;cursor:pointer;font-family:var(--disp);font-weight:700;font-size:1.2rem;color:var(--text);display:flex;justify-content:space-between;align-items:center;gap:var(--s2)}
.faq summary::-webkit-details-marker{display:none}.faq summary .pl{color:var(--tq-d);font-size:1.6rem;transition:transform .2s;flex-shrink:0}
.faq details[open] summary .pl{transform:rotate(45deg)}.faq p{color:var(--muted);margin-top:.7rem;font-size:1.02rem;max-width:66ch;line-height:1.6}

.footcta{background:var(--ink);color:var(--paper);padding:var(--s7) 0}
.footcta h2{font-size:clamp(2.6rem,7vw,5.4rem);color:var(--paper);max-width:15ch}
.footcta p{color:rgba(245,242,234,.7);font-size:1.2rem;margin:var(--s3) 0 var(--s4);max-width:46ch}

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
<?php nav_bar($base, 'tarifs'); ?>

<main>
<section class="hero rv"><div class="wrap">
  <span class="eyebrow">Nos offres</span>
  <h1>Commencez gratuitement. Payez seulement si ça marche.</h1>
  <p class="lead">Toutes les formules donnent accès à <b>l'intégralité de Fidelo</b>. Vous démarrez sans rien payer ; vous choisissez une formule le jour où vos clients reviennent.</p>
</div></section>

<!-- MOMENT — la récompense, en vrai -->
<section class="cine rv" id="cine">
  <video id="cineVid" muted loop playsinline preload="none" poster="<?= e($base) ?>/media/tarifs-cinematic-poster.jpg" data-src="<?= e($base) ?>/media/tarifs-cinematic.mp4" aria-label="Un commerçant tend un café offert à une cliente fidèle, comptoir chaleureux, lumière du matin."></video>
  <div class="scrim"></div>
  <div class="cap"><div class="wrap">
    <div><span class="eyebrow">La récompense, en vrai</span><h3>Le geste qui fait revenir : offrir.</h3></div>
    <span class="mono">huit secondes · zéro commission</span>
  </div></div>
</section>

<div class="wrap">
  <!-- ══════════ DÉCOUVERTE — GRATUIT ══════════ -->
  <section class="free rv">
    <div>
      <span class="tag">🎁 Offre Découverte</span>
      <h2>Gratuit, pour de vrai. Jusqu'à <?= (int)$FREE ?> clients.</h2>
      <div class="price">0 € <small>— sans carte bancaire, sans limite de durée</small></div>
      <p class="d">Créez votre programme de fidélité, inscrivez vos <?= (int)$FREE ?> premiers clients et faites-les revenir — <b>sans jamais payer</b>. Pas d'essai qui expire au bout de quinze jours : tant que vous restez sous <?= (int)$FREE ?> clients, Fidelo est gratuit. Quand vous grandissez, vous passez à une formule — et <b>vos clients restent intacts</b>.</p>
      <div class="cta"><a class="btn btn-p" href="<?= e($base) ?>/inscription.php">Créer mon compte gratuit</a></div>
    </div>
    <ul>
      <li><?= $CK ?><span><b>Tout Fidelo</b> : scanner, points, récompenses, statistiques</span></li>
      <li><?= $CK ?><span>Cartes à <b>votre logo</b> · Google Wallet</span></li>
      <li><?= $CK ?><span>Messages groupés à vos clients</span></li>
      <li><?= $CK ?><span>Aucune commission, aucune pub</span></li>
      <li><?= $CK ?><span><b>10 jours en illimité</b> à l'inscription pour tout essayer en grand</span></li>
    </ul>
  </section>

  <!-- ══════════ 3 FORMULES ══════════ -->
  <div class="packs">
    <div class="pack rv">
      <h3>Mensuel</h3>
      <div class="sub">Pour passer au-delà de <?= (int)$FREE ?> clients sans vous engager. Vous arrêtez quand vous voulez.</div>
      <div class="tag"><span class="amt">29 €</span><span class="per">/ mois</span></div>
      <div class="equiv">clients et points illimités</div>
      <ul>
        <li><?= $CK ?><span><b>Clients illimités</b> — au-delà des <?= (int)$FREE ?> gratuits</span></li>
        <li><?= $CK ?><span>Votre espace : scanner, fiches, cadeaux, statistiques</span></li>
        <li><?= $CK ?><span>Cartes à <b>votre logo et vos couleurs</b> · Google Wallet</span></li>
        <li><?= $CK ?><span>Messages groupés — <b>gratuits et illimités</b></span></li>
        <li><?= $CK ?><span>Affichette QR + cartes physiques</span></li>
        <li><?= $CK ?><span>Sauvegardes, mises à jour, assistance</span></li>
      </ul>
      <div class="detail">
        <b>Pour qui ?</b> Le commerce qui dépasse les <?= (int)$FREE ?> clients gratuits et veut essayer sérieusement sans s'engager.<br><br>
        <b>Aucune commission</b> sur votre chiffre d'affaires — un client qui revient grâce à Fidelo vous rapporte 100 % de ce qu'il dépense.<br><br>
        <b>Le calcul :</b> 29 € par mois, c'est <b>moins d'1 € par jour</b>, pour faire revenir ceux qui achètent chez vous.
      </div>
      <div class="cta"><a class="btn btn-ghost" href="<?= e($base) ?>/inscription.php">Commencer gratuitement</a></div>
    </div>

    <div class="pack best rv">
      <span class="flag">Le plus choisi</span>
      <h3>Annuel</h3>
      <div class="sub">Le même service, réglé une fois par an. La formule la plus économique à l'usage.</div>
      <div class="tag"><span class="amt">250 €</span><span class="per">/ an</span></div>
      <div class="equiv">soit 20,83 € par mois</div>
      <div class="save">Vous économisez 98 € — près de 3,5 mois offerts</div>
      <ul>
        <li><?= $CK ?><span><b>Tout ce que contient la formule mensuelle</b></span></li>
        <li><?= $CK ?><span><b>28 % moins cher</b> que le paiement au mois</span></li>
        <li><?= $CK ?><span>Une seule facture par an — plus simple pour la compta</span></li>
        <li><?= $CK ?><span><b>Prix bloqué 12 mois</b>, même si le tarif public augmente</span></li>
        <li><?= $CK ?><span>Configuration offerte (récompenses, logo, affichette)</span></li>
        <li><?= $CK ?><span>Assistance prioritaire</span></li>
      </ul>
      <div class="detail">
        <b>Pour qui ?</b> Le commerce qui sait déjà que la fidélité fait partie de son métier, et préfère régler une bonne fois pour l'année.<br><br>
        <b>Le calcul :</b> 250 € l'année, c'est environ <b>21 € par mois</b>. Si Fidelo ramène seulement <b>deux clients par mois</b> qui dépensent 15 €, la formule est déjà remboursée. Le reste est du bénéfice.
      </div>
      <div class="cta"><a class="btn btn-p" href="<?= e($base) ?>/inscription.php">Choisir l'annuel</a></div>
    </div>

    <div class="pack life rv">
      <span class="flag">★ Places limitées</span>
      <h3>À vie + site web</h3>
      <div class="sub">Vous payez une seule fois. Fidelo est à vous pour toujours — et votre site internet est offert.</div>
      <div class="tag"><span class="amt">525 €</span><span class="per">une seule fois</span></div>
      <div class="equiv">puis 50 € / an (nom de domaine + hébergement)</div>
      <div class="save">Rentabilisé en 18 mois face au mensuel</div>
      <?php if ($lancRestant !== null): ?>
        <?php if ($lancRestant > 0): ?>
        <div class="places"><span class="dot"></span>Offre de lancement — il reste <?= (int)$lancRestant ?> place<?= $lancRestant>1?'s':'' ?> à 525 €</div>
        <?php else: ?>
        <div class="places" style="background:var(--bg-2);color:var(--muted)">Offre de lancement complète — tarif normal</div>
        <?php endif; ?>
      <?php endif; ?>
      <ul>
        <li><?= $CK ?><span><b>Fidelo à vie</b> — plus jamais d'abonnement</span></li>
        <li><?= $CK ?><span>🎁 <b>Un site internet professionnel offert</b> — design sur mesure, pensé pour le mobile, prêt pour Google</span></li>
        <li><?= $CK ?><span>Votre carte, votre menu ou vos prestations sur votre propre site</span></li>
        <li><?= $CK ?><span><b>Mises à jour à vie</b>, sans surcoût</span></li>
        <li><?= $CK ?><span>Un seul règlement : plus rien à surveiller</span></li>
        <li><?= $CK ?><span>Assistance prioritaire à vie</span></li>
      </ul>
      <div class="detail">
        <b>Pour qui ?</b> Le commerce installé, qui ne compte pas fermer et en a assez des abonnements qui s'accumulent.<br><br>
        <b>Les 50 € par an, c'est quoi ?</b> Uniquement les frais réels tant que votre site est en ligne : <b>nom de domaine</b>, <b>hébergement</b> et <b>maintenance</b>. Ce n'est pas un abonnement à Fidelo — Fidelo est déjà payé, une fois pour toutes.<br><br>
        <b>Le calcul :</b> 525 € une fois, contre 348 € <i>chaque</i> année en mensuel. Dès la deuxième année vous êtes gagnant — et le site professionnel, lui, se facture seul plusieurs centaines d'euros.
      </div>
      <div class="cta"><a class="btn btn-p" style="background:var(--gold);color:#3a2a05" href="<?= e($base) ?>/inscription.php">Réserver ma place</a></div>
    </div>
  </div>
</div>

<!-- COMPARATIF -->
<section class="band"><div class="wrap">
  <div class="head center rv"><span class="eyebrow">Comparatif</span><h2>Le produit ne change pas.</h2>
    <p>Aucune fonctionnalité n'est réservée à une formule. Vous ne payez pas des options, vous choisissez une durée.</p></div>
  <div class="rv" style="overflow-x:auto;margin-top:var(--s5)">
  <table class="cmptable">
    <thead><tr>
      <th>&nbsp;</th>
      <th style="text-align:center">Découverte<br><small>0 €</small></th>
      <th style="text-align:center">Mensuel<br><small>29 € / mois</small></th>
      <th style="text-align:center">Annuel<br><small>250 € / an</small></th>
      <th style="text-align:center">À vie<br><small>525 € une fois</small></th>
    </tr></thead>
    <tbody>
      <tr><td>Nombre de clients</td><td class="c"><?= (int)$FREE ?> max</td><td class="c oui">illimité</td><td class="c oui">illimité</td><td class="c oui">illimité</td></tr>
      <tr><td>Points et récompenses illimités</td><td class="c oui">✓</td><td class="c oui">✓</td><td class="c oui">✓</td><td class="c oui">✓</td></tr>
      <tr><td>Scanner anti-fraude + espace commerçant</td><td class="c oui">✓</td><td class="c oui">✓</td><td class="c oui">✓</td><td class="c oui">✓</td></tr>
      <tr><td>Cartes à votre logo · Google Wallet</td><td class="c oui">✓</td><td class="c oui">✓</td><td class="c oui">✓</td><td class="c oui">✓</td></tr>
      <tr><td>Messages groupés illimités</td><td class="c oui">✓</td><td class="c oui">✓</td><td class="c oui">✓</td><td class="c oui">✓</td></tr>
      <tr><td>Sauvegardes et mises à jour</td><td class="c oui">✓</td><td class="c oui">✓</td><td class="c oui">✓</td><td class="c oui">✓</td></tr>
      <tr><td>Commission sur votre chiffre d'affaires</td><td class="c non">aucune</td><td class="c non">aucune</td><td class="c non">aucune</td><td class="c non">aucune</td></tr>
      <tr><td><b>Site internet professionnel</b></td><td class="c non">—</td><td class="c non">—</td><td class="c non">—</td><td class="c oui">offert</td></tr>
      <tr><td><b>Coût sur 3 ans</b></td><td class="c oui">0 €</td><td class="c">1 044 €</td><td class="c">750 €</td><td class="c oui">625 €</td></tr>
    </tbody>
  </table>
  </div>
</div></section>

<!-- FAQ -->
<section class="band" style="background:var(--card)"><div class="wrap">
  <div class="head center rv"><span class="eyebrow">Questions</span><h2>Ce que l'on nous demande</h2></div>
  <div class="faq" style="margin-top:var(--s5)">
    <details class="rv"><summary>La formule Découverte est-elle vraiment gratuite ? <span class="pl">+</span></summary><p>Oui, entièrement, et sans limite de durée. Tant que vous restez sous <?= (int)$FREE ?> clients, vous ne payez rien et vous n'entrez aucune carte bancaire. Ce n'est pas un essai qui se coupe au bout de quinze jours : c'est gratuit aussi longtemps que vous le souhaitez. À l'inscription, vous profitez même de 10 jours en illimité pour tout tester en grand.</p></details>
    <details class="rv"><summary>Que se passe-t-il quand j'atteins <?= (int)$FREE ?> clients ? <span class="pl">+</span></summary><p>Vos <?= (int)$FREE ?> clients continuent de fonctionner normalement — scan, points, récompenses, messages. Pour en inscrire davantage, vous passez à une formule payante (mensuelle, annuelle ou à vie). Rien n'est perdu : vos clients et vos points restent intacts, et l'accès se débloque immédiatement.</p></details>
    <details class="rv"><summary>Puis-je changer de formule plus tard ? <span class="pl">+</span></summary><p>Oui, dans les deux sens. En passant du mensuel à l'annuel, le mois déjà réglé est déduit. En passant à la formule à vie, ce que vous avez payé sur l'année en cours est déduit également.</p></details>
    <details class="rv"><summary>Que veut dire « à vie » exactement ? <span class="pl">+</span></summary><p>Votre accès à Fidelo n'expire pas et ne fait l'objet d'aucun abonnement. Vous continuez à recevoir les mises à jour. Les 50 € annuels couvrent uniquement les frais qui existent tant que votre site est en ligne : nom de domaine, hébergement et maintenance.</p></details>
    <details class="rv"><summary>Le site internet offert, c'est quel genre de site ? <span class="pl">+</span></summary><p>Un vrai site professionnel à votre nom : accueil, présentation, carte ou prestations, galerie, contact et horaires. Dessiné sur mesure à vos couleurs, rapide sur téléphone, et préparé pour apparaître sur Google. Ce n'est pas un modèle générique rempli à la va-vite.</p></details>
    <details class="rv"><summary>Y a-t-il une commission sur mes ventes ? <span class="pl">+</span></summary><p>Jamais. Ni sur vos ventes, ni sur vos clients, ni sur les récompenses distribuées. Le prix affiché est le prix payé.</p></details>
    <details class="rv"><summary>Et si j'arrête ? Mes clients sont-ils perdus ? <span class="pl">+</span></summary><p>Non. Vous exportez la liste complète de vos clients au format Excel quand vous le souhaitez, y compris après l'arrêt. Vos données vous appartiennent.</p></details>
    <details class="rv"><summary>Faut-il acheter du matériel ? <span class="pl">+</span></summary><p>Non. Un téléphone ou une tablette suffit — celui que vous avez déjà. Vos clients n'installent aucune application : leur carte s'ouvre dans leur navigateur et s'ajoute à l'écran d'accueil ou à Google Wallet.</p></details>
  </div>
</div></section>

<!-- CTA FINAL -->
<section class="footcta"><div class="wrap rv">
  <h2>Commencez gratuitement, aujourd'hui.</h2>
  <p>Jusqu'à <?= (int)$FREE ?> clients sans rien payer, sans limite de durée. Passez à une formule seulement le jour où ça marche.</p>
  <a href="<?= e($base) ?>/inscription.php" class="btn btn-out">Créer mon compte gratuitement →</a>
</div></section>
</main>

<button id="up" class="up" aria-label="Remonter en haut"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg></button>

<footer class="site"><div class="wrap foot-grid">
  <a class="brand" href="<?= e($base) ?>/accueil.php">Fidelo<span class="d">.</span></a>
  <span>Le programme de fidélité des commerces de proximité.</span>
  <span style="display:flex;gap:15px;flex-wrap:wrap">
    <a href="<?= e($base) ?>/accueil.php" style="color:var(--muted);font-weight:600">Accueil</a>
    <a href="<?= e($base) ?>/confidentialite.php" style="color:var(--muted);font-weight:600">Confidentialité</a>
    <a href="<?= e($base) ?>/mentions-legales.php" style="color:var(--muted);font-weight:600">Mentions légales</a>
  </span>
  <span class="mono" style="font-size:12px;color:var(--faint)">© 2026 Fidelo</span>
</div></footer>

<script id="pageScript">
window.__fideloPageInit=function(){
const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)];
const reduce=matchMedia('(prefers-reduced-motion:reduce)').matches;
const teardown=[];
window.__fideloPageTeardown=function(){teardown.forEach(fn=>{try{fn();}catch(e){}});teardown.length=0;};

document.body.classList.add('reveal-ready');
[['.packs .pack'],['.faq details']].forEach(([sel])=>$$(sel).forEach((el,i)=>el.style.setProperty('--d',(i*0.09)+'s')));

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
