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

$ICON = '<svg class="flogo" viewBox="0 0 64 64" aria-hidden="true"><defs><linearGradient id="tla" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#06B6D4"/><stop offset=".55" stop-color="#22D3EE"/><stop offset="1" stop-color="#FF6B6B"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#tla)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>';
$CK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg>';
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title>Nos offres — Fidelo</title>
<meta name="description" content="Commencez gratuitement jusqu'à 30 clients, sans limite de durée. Puis 29 €/mois, 250 €/an, ou 525 € à vie avec un site internet professionnel offert.">
<meta name="theme-color" content="#06B6D4">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap">
<style>
:root{
  --tq:#06B6D4;--tq-d:#0891B2;--tq-l:#22D3EE;--co:#FF6B6B;--co-d:#F14E4E;
  --am:#FFD166;--am-d:#F7B500;--ink:#0A2E38;--ink-fixed:#0A2E38;--accent-txt:#0B7C97;--gold-txt:#8A6200;
  --bg:#F3FCFD;--bg-2:#E7F8FA;--card:#ffffff;--line:#D6EEF1;
  --text:#0F343D;--muted:#4e767f;--faint:#7d9ea7;
  --disp:"Bricolage Grotesque",-apple-system,system-ui,sans-serif;
  --body:"Plus Jakarta Sans",-apple-system,system-ui,sans-serif;--mono:"IBM Plex Mono",monospace;
  --grad:linear-gradient(118deg,#06B6D4 0%,#22D3EE 38%,#FF6B6B 100%);
}
@media(prefers-color-scheme:dark){:root{
  --tq:#22D3EE;--tq-d:#06B6D4;--co:#FF8080;--co-d:#FF6B6B;--am:#FFD97A;
  --ink:#EAFBFD;--ink-fixed:#04161c;--accent-txt:#7DE9FB;--gold-txt:#FFD97A;--bg:#07222a;--bg-2:#0b2c35;--card:#0e323c;--line:#1c4650;
  --text:#EAFBFD;--muted:#9fc4cc;--faint:#6f939b;}}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:var(--body);-webkit-font-smoothing:antialiased;overflow-x:hidden}
a{color:inherit;text-decoration:none}
.wrap{max-width:1180px;margin:0 auto;padding:0 22px}
.flogo{width:1.25em;height:1.25em;display:inline-block;vertical-align:-.28em;margin-right:.3em}
.top{display:flex;align-items:center;gap:20px;padding:20px 0}
.brand{font-family:var(--disp);font-weight:800;font-size:23px;letter-spacing:-.02em}
.brand .d{color:var(--co)}
.top .sp{margin-left:auto}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:13px 22px;border-radius:13px;font-weight:700;font-size:15px;border:0;cursor:pointer;font-family:var(--body);transition:transform .15s,box-shadow .2s}
.btn:hover{transform:translateY(-2px)}
.btn-p{background:var(--grad);color:#fff;box-shadow:0 14px 30px -12px rgba(6,182,212,.7)}
.btn-g{background:var(--card);border:1.6px solid var(--line);color:var(--text)}
.btn-ink{background:var(--ink-fixed);color:#EAFBFD}
.hero{text-align:center;padding:46px 0 10px}
.eyebrow{font-family:var(--mono);font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:var(--accent-txt);font-weight:600}
.hero h1{font-family:var(--disp);font-weight:800;font-size:clamp(34px,5.6vw,58px);line-height:1.04;letter-spacing:-.03em;margin:14px 0 0}
.hero p{color:var(--muted);font-size:18px;max-width:640px;margin:16px auto 0;line-height:1.6}

/* ── Offre Découverte : bandeau gratuit, pleine largeur ── */
.free-hero{margin:40px 0 6px;background:var(--card);border:2px solid var(--tq);border-radius:24px;
  padding:30px 32px;display:grid;grid-template-columns:1.5fr 1fr;gap:26px;align-items:center;
  box-shadow:0 30px 60px -32px rgba(6,182,212,.5);position:relative;overflow:hidden}
.free-hero::before{content:"";position:absolute;inset:0;background:radial-gradient(120% 140% at 100% 0,rgba(6,182,212,.10),transparent 55%);pointer-events:none}
.free-hero .fl-tag{display:inline-flex;align-items:center;gap:8px;background:var(--bg-2);border:1.5px solid var(--line);border-radius:99px;padding:6px 14px;font-size:12.5px;font-weight:800;font-family:var(--mono);letter-spacing:.06em;text-transform:uppercase;color:var(--accent-txt)}
.free-hero h2{font-family:var(--disp);font-weight:800;font-size:clamp(24px,3.6vw,32px);letter-spacing:-.02em;margin:14px 0 0}
.free-hero .price0{font-family:var(--disp);font-weight:800;font-size:44px;line-height:1;color:var(--tq-d);margin:10px 0 0}
.free-hero .price0 small{font-size:16px;color:var(--muted);font-weight:600;font-family:var(--body)}
.free-hero p{color:var(--muted);font-size:15px;line-height:1.6;margin:12px 0 0;max-width:52ch}
.free-hero ul{list-style:none;margin:4px 0 0;padding:0;display:flex;flex-direction:column;gap:10px}
.free-hero li{display:flex;gap:9px;font-size:14.5px;align-items:flex-start;line-height:1.5}
.free-hero li svg{width:17px;height:17px;flex-shrink:0;color:var(--tq);margin-top:3px}
.free-hero .cta{margin-top:18px}
@media(max-width:820px){.free-hero{grid-template-columns:1fr;padding:26px 22px}}

.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;margin:26px 0 10px;align-items:start}
@media(max-width:980px){.grid{grid-template-columns:1fr;max-width:520px;margin-inline:auto}}
.pack{background:var(--card);border:2px solid var(--line);border-radius:24px;padding:30px 26px;position:relative;display:flex;flex-direction:column;height:100%}
.pack.best{border-color:var(--tq);box-shadow:0 30px 60px -28px rgba(6,182,212,.55)}
.pack.life{border-color:var(--am-d);box-shadow:0 30px 60px -28px rgba(247,181,0,.4)}
.pack .flag{position:absolute;top:-13px;left:50%;transform:translateX(-50%);white-space:nowrap;font-size:11.5px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:6px 15px;border-radius:99px;font-family:var(--mono)}
.pack.best .flag{background:var(--grad);color:#fff}
.pack.life .flag{background:linear-gradient(118deg,#F7B500,#FFD166);color:#3a2a05}
.pack h2{font-family:var(--disp);font-weight:700;font-size:22px;margin:6px 0 4px}
.pack .sub{color:var(--muted);font-size:14px;min-height:40px;line-height:1.5}
.tag{display:flex;align-items:baseline;gap:7px;margin:18px 0 2px;flex-wrap:wrap}
.tag .amt{font-family:var(--disp);font-weight:800;font-size:46px;line-height:1;background:linear-gradient(115deg,#06B6D4 0%,#0E9FC0 55%,#FF6B6B 165%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.pack.life .tag .amt{background:linear-gradient(118deg,#F7B500,#FFD166);-webkit-background-clip:text;background-clip:text}
.tag .per{color:var(--muted);font-size:14px;font-weight:600}
.equiv{font-size:12.5px;color:var(--muted);font-family:var(--mono);min-height:18px}
.save{display:inline-block;margin-top:10px;background:rgba(6,182,212,.12);color:var(--accent-txt);font-size:12.5px;font-weight:700;padding:5px 11px;border-radius:8px}
.pack.life .save{background:rgba(247,181,0,.18);color:var(--gold-txt)}
.pack .places{display:inline-flex;align-items:center;gap:7px;margin-top:10px;background:rgba(241,78,78,.12);color:var(--co-d);font-size:12.5px;font-weight:800;padding:6px 12px;border-radius:8px;font-family:var(--mono)}
.pack .places .dot{width:8px;height:8px;border-radius:50%;background:var(--co-d);box-shadow:0 0 0 0 rgba(241,78,78,.55);animation:pulse 1.8s infinite}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(241,78,78,.5)}70%{box-shadow:0 0 0 7px rgba(241,78,78,0)}100%{box-shadow:0 0 0 0 rgba(241,78,78,0)}}
.pack ul{list-style:none;padding:0;margin:20px 0 22px;display:flex;flex-direction:column;gap:11px}
.pack li{display:flex;gap:9px;font-size:14.2px;align-items:flex-start;line-height:1.5}
.pack li svg{width:17px;height:17px;flex-shrink:0;color:var(--tq);margin-top:3px}
.pack.life li svg{color:var(--am-d)}
.pack li b{font-weight:700}
.pack .cta{margin-top:18px}
.detail{background:var(--bg-2);border:1px solid var(--line);border-radius:14px;padding:15px 16px;margin-top:auto;font-size:13.2px;color:var(--muted);line-height:1.65}
.detail b{color:var(--text)}
.band{padding:64px 0;background:var(--bg-2);margin-top:60px;border-top:1px solid var(--line)}
.sec-head{text-align:center;max-width:680px;margin:0 auto 34px}
.sec-head h2{font-family:var(--disp);font-weight:800;font-size:clamp(26px,4vw,38px);letter-spacing:-.02em;margin:10px 0 0}
.sec-head p{color:var(--muted);font-size:16.5px;margin-top:12px}
.cmp{width:100%;border-collapse:collapse;background:var(--card);border-radius:18px;overflow:hidden;box-shadow:0 20px 44px -30px rgba(6,182,212,.5);font-size:14px}
.cmp th,.cmp td{padding:14px 13px;text-align:left;border-bottom:1px solid var(--line)}
.cmp thead th{background:var(--ink-fixed);color:#EAFBFD;font-family:var(--disp);font-weight:600;font-size:14px}
.cmp tbody tr:last-child td{border-bottom:0}
.cmp td.c{text-align:center;font-weight:700}
.cmp .oui{color:var(--accent-txt)}.cmp .non{color:var(--muted)}
.cmp thead th small{font-weight:400;opacity:.72}
@media(max-width:820px){.cmp{font-size:12px}.cmp th,.cmp td{padding:9px 7px}}
.faq{max-width:760px;margin:0 auto}
details{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:17px 19px;margin-bottom:11px}
details summary{cursor:pointer;font-weight:700;font-size:15.5px;list-style:none;display:flex;justify-content:space-between;gap:14px;align-items:center}
details summary::-webkit-details-marker{display:none}
details .pl{color:var(--tq);font-size:20px;font-weight:400;flex-shrink:0}
details[open] .pl{transform:rotate(45deg)}
details p{color:var(--muted);font-size:14.5px;line-height:1.7;margin:12px 0 0}
.foot{padding:40px 0;text-align:center;color:var(--muted);font-size:13.5px}
.foot a{color:var(--accent-txt);font-weight:700}
.foot .ll{display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-bottom:10px}
@media(max-width:600px){
  .top{flex-wrap:wrap;gap:10px 12px;padding:16px 0}
  .top .sp{display:none}
  .top .brand{font-size:20px}
  .top .btn{padding:10px 14px;font-size:13.5px;flex:1;min-width:0}
  .hero{padding:30px 0 6px}
  .free-hero .price0{font-size:36px}
  .free-hero .price0 small{display:block;margin-top:6px}
}
</style></head><body>

<div class="wrap">
  <?php nav_bar($base, 'tarifs'); ?>

  <section class="hero">
    <span class="eyebrow">Nos offres</span>
    <h1>Commencez gratuitement.<br>Payez seulement si ça marche.</h1>
    <p>Toutes les formules donnent accès à <b>l'intégralité de Fidelo</b>. Vous démarrez sans rien payer&nbsp;; vous choisissez une formule le jour où vos clients reviennent.</p>
  </section>

  <!-- ══════════ DÉCOUVERTE — GRATUIT ══════════ -->
  <section class="free-hero">
    <div style="position:relative;z-index:1">
      <span class="fl-tag">🎁 Offre Découverte</span>
      <h2>Gratuit, pour de vrai. Jusqu'à <?= (int)$FREE ?> clients.</h2>
      <div class="price0">0 €<small> — sans carte bancaire, sans limite de durée</small></div>
      <p>Créez votre programme de fidélité, inscrivez vos <?= (int)$FREE ?> premiers clients et
      faites-les revenir — <b>sans jamais payer</b>. Pas d'essai qui expire au bout de quinze jours :
      tant que vous restez sous <?= (int)$FREE ?> clients, Fidelo est gratuit.
      Quand vous grandissez, vous passez à une formule — et <b>vos clients restent intacts</b>.</p>
      <div class="cta"><a class="btn btn-p" href="<?= e($base) ?>/inscription.php">Créer mon compte gratuit</a></div>
    </div>
    <ul style="position:relative;z-index:1">
      <li><?= $CK ?><span><b>Tout Fidelo</b> : scanner, points, récompenses, statistiques</span></li>
      <li><?= $CK ?><span>Cartes à <b>votre logo</b> · Google Wallet</span></li>
      <li><?= $CK ?><span>Messages groupés à vos clients</span></li>
      <li><?= $CK ?><span>Aucune commission, aucune pub</span></li>
      <li><?= $CK ?><span><b>10 jours en illimité</b> à l'inscription pour tout essayer en grand</span></li>
    </ul>
  </section>

  <div class="grid">

    <!-- ══════════ MENSUEL ══════════ -->
    <div class="pack">
      <h2>Mensuel</h2>
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
        <b>Pour qui ?</b> Le commerce qui dépasse les <?= (int)$FREE ?> clients gratuits et veut
        essayer sérieusement sans s'engager.<br><br>
        <b>Aucune commission</b> sur votre chiffre d'affaires — un client qui revient grâce à
        Fidelo vous rapporte 100 % de ce qu'il dépense.<br><br>
        <b>Le calcul :</b> 29 € par mois, c'est <b>moins d'1 € par jour</b>, pour faire revenir
        ceux qui achètent chez vous.
      </div>
      <div class="cta"><a class="btn btn-g" style="width:100%" href="<?= e($base) ?>/inscription.php">Commencer gratuitement</a></div>
    </div>

    <!-- ══════════ ANNUEL ══════════ -->
    <div class="pack best">
      <span class="flag">Le plus choisi</span>
      <h2>Annuel</h2>
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
        <b>Pour qui ?</b> Le commerce qui sait déjà que la fidélité fait partie de son métier,
        et préfère régler une bonne fois pour l'année.<br><br>
        <b>Le calcul :</b> 250 € l'année, c'est environ <b>21 € par mois</b>. Si Fidelo ramène
        seulement <b>deux clients par mois</b> qui dépensent 15 €, la formule est déjà remboursée.
        Le reste est du bénéfice.
      </div>
      <div class="cta"><a class="btn btn-p" style="width:100%" href="<?= e($base) ?>/inscription.php">Choisir l'annuel</a></div>
    </div>

    <!-- ══════════ À VIE ══════════ -->
    <div class="pack life">
      <span class="flag">★ Places limitées</span>
      <h2>À vie + site web</h2>
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
        <b>Pour qui ?</b> Le commerce installé, qui ne compte pas fermer et en a assez des
        abonnements qui s'accumulent.<br><br>
        <b>Les 50 € par an, c'est quoi ?</b> Uniquement les frais réels tant que votre site est
        en ligne : <b>nom de domaine</b>, <b>hébergement</b> et <b>maintenance</b>. Ce n'est pas
        un abonnement à Fidelo — Fidelo est déjà payé, une fois pour toutes.<br><br>
        <b>Le calcul :</b> 525 € une fois, contre 348 € <i>chaque</i> année en mensuel. Dès la
        deuxième année vous êtes gagnant — et le site professionnel, lui, se facture seul
        plusieurs centaines d'euros.
      </div>
      <div class="cta"><a class="btn btn-ink" style="width:100%" href="<?= e($base) ?>/inscription.php">Réserver ma place</a></div>
    </div>

  </div>
</div>

<section class="band">
  <div class="wrap">
    <div class="sec-head"><span class="eyebrow">Comparatif</span><h2>Le produit ne change pas.</h2>
      <p>Aucune fonctionnalité n'est réservée à une formule. Vous ne payez pas des options, vous choisissez une durée.</p></div>
    <div style="overflow-x:auto">
    <table class="cmp">
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
  </div>
</section>

<section class="band" style="background:var(--bg);border-top:0">
  <div class="wrap faq">
    <div class="sec-head"><span class="eyebrow">Questions</span><h2>Ce que l'on nous demande</h2></div>
    <details><summary>La formule Découverte est-elle vraiment gratuite ? <span class="pl">+</span></summary><p>Oui, entièrement, et sans limite de durée. Tant que vous restez sous <?= (int)$FREE ?> clients, vous ne payez rien et vous n'entrez aucune carte bancaire. Ce n'est pas un essai qui se coupe au bout de quinze jours : c'est gratuit aussi longtemps que vous le souhaitez. À l'inscription, vous profitez même de 10 jours en illimité pour tout tester en grand.</p></details>
    <details><summary>Que se passe-t-il quand j'atteins <?= (int)$FREE ?> clients ? <span class="pl">+</span></summary><p>Vos <?= (int)$FREE ?> clients continuent de fonctionner normalement — scan, points, récompenses, messages. Pour en inscrire davantage, vous passez à une formule payante (mensuelle, annuelle ou à vie). Rien n'est perdu : vos clients et vos points restent intacts, et l'accès se débloque immédiatement.</p></details>
    <details><summary>Puis-je changer de formule plus tard ? <span class="pl">+</span></summary><p>Oui, dans les deux sens. En passant du mensuel à l'annuel, le mois déjà réglé est déduit. En passant à la formule à vie, ce que vous avez payé sur l'année en cours est déduit également.</p></details>
    <details><summary>Que veut dire « à vie » exactement ? <span class="pl">+</span></summary><p>Votre accès à Fidelo n'expire pas et ne fait l'objet d'aucun abonnement. Vous continuez à recevoir les mises à jour. Les 50 € annuels couvrent uniquement les frais qui existent tant que votre site est en ligne : nom de domaine, hébergement et maintenance.</p></details>
    <details><summary>Le site internet offert, c'est quel genre de site ? <span class="pl">+</span></summary><p>Un vrai site professionnel à votre nom : accueil, présentation, carte ou prestations, galerie, contact et horaires. Dessiné sur mesure à vos couleurs, rapide sur téléphone, et préparé pour apparaître sur Google. Ce n'est pas un modèle générique rempli à la va-vite.</p></details>
    <details><summary>Y a-t-il une commission sur mes ventes ? <span class="pl">+</span></summary><p>Jamais. Ni sur vos ventes, ni sur vos clients, ni sur les récompenses distribuées. Le prix affiché est le prix payé.</p></details>
    <details><summary>Et si j'arrête ? Mes clients sont-ils perdus ? <span class="pl">+</span></summary><p>Non. Vous exportez la liste complète de vos clients au format Excel quand vous le souhaitez, y compris après l'arrêt. Vos données vous appartiennent.</p></details>
    <details><summary>Faut-il acheter du matériel ? <span class="pl">+</span></summary><p>Non. Un téléphone ou une tablette suffit — celui que vous avez déjà. Vos clients n'installent aucune application : leur carte s'ouvre dans leur navigateur et s'ajoute à l'écran d'accueil ou à Google Wallet.</p></details>
  </div>
</section>

<footer class="foot">
  <div class="wrap">
    <div class="ll">
      <a href="<?= e($base) ?>/inscription.php">Créer mon compte</a>
      <a href="<?= e($base) ?>/">Accueil</a>
      <a href="<?= e($base) ?>/confidentialite.php">Confidentialité</a>
      <a href="<?= e($base) ?>/mentions-legales.php">Mentions légales</a>
    </div>
    <p>Fidelo — la fidélité qui fait revenir vos clients. Prix en euros, TVA non applicable, art. 293 B du CGI.</p>
  </div>
</footer>
</body></html>
