<?php
/* Fidelo — page d'accueil publique (présentation + inscription). */
require __DIR__ . '/lib.php';
require __DIR__ . '/nav.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$origin = (is_https() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'fidelo.site');
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title>Fidelo — La fidélité qui fait revenir vos clients</title>
<meta name="description" content="Le programme de fidélité sans carte et sans appli pour cafés, restaurants et commerces. Inscription gratuite, 10 jours d'essai.">
<meta name="theme-color" content="#241A12">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Fidelo">
<meta property="og:title" content="Fidelo — La fidélité qui fait revenir vos clients">
<meta property="og:description" content="Le programme de fidélité sans carte et sans appli pour cafés, restaurants et commerces. Inscription gratuite, 10 jours d'essai.">
<meta property="og:image" content="<?= e($origin . $base) ?>/og-image.png">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">
<meta property="og:url" content="<?= e($origin . $base) ?>/accueil.php">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Fidelo — La fidélité qui fait revenir vos clients">
<meta name="twitter:description" content="Le programme de fidélité sans carte et sans appli pour cafés, restaurants et commerces.">
<meta name="twitter:image" content="<?= e($origin . $base) ?>/og-image.png">
<link rel="icon" href="<?= e($base) ?>/icon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Calistoga&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap">
<style>
/* ============================================================
   TOKENS — palette disciplinée (encre + un seul accent),
   grille d'espacement 8px, échelle typographique nette.
   ============================================================ */
:root{
  --ink:#241A12;--ink-2:#3A2A1D;
  --tq:#C1552F;--tq-d:#9C4024;--tq-l:#E08A5D;
  --gold:#B8863A;--gold-l:#E4C583;
  --bg:#FBF3E7;--card:#FFFFFF;--line:#E9DAC3;
  --text:#241A12;--muted:#6E5B47;--faint:#9C8B74;
  --disp:"Calistoga",-apple-system,system-ui,sans-serif;
  --body:"Inter",-apple-system,system-ui,sans-serif;
  --mono:"JetBrains Mono",monospace;
  --s1:.5rem;--s2:1rem;--s3:1.5rem;--s4:2rem;--s5:3rem;--s6:4rem;--s7:6rem;--s8:8rem;
}
@media(prefers-color-scheme:dark){:root{
  --ink:#F3E8D8;--ink-2:#E4D5BE;--tq:#E08A5D;--tq-d:#F0AE85;--tq-l:#F5C6A5;--gold:#E4C583;--gold-l:#F3DFA6;
  --bg:#0A1618;--card:#0F2226;--line:#1D3438;--text:#F3E8D8;--muted:#C9B79E;--faint:#8F7C64;}}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:var(--body);-webkit-font-smoothing:antialiased;overflow-x:hidden}
h1,h2,h3,h4{font-family:var(--disp);font-weight:700;line-height:1.02;letter-spacing:-.025em;text-wrap:balance;margin:0}
p{margin:0}a{color:inherit;text-decoration:none}button{font-family:inherit;cursor:pointer;border:0;background:none;color:inherit}
img{max-width:100%}[hidden]{display:none!important}.mono{font-family:var(--mono);font-variant-numeric:tabular-nums}
.wrap{max-width:1180px;margin:0 auto;padding-inline:var(--s3)}
.eyebrow{font-family:var(--mono);font-size:.75rem;letter-spacing:.14em;text-transform:uppercase;color:var(--tq-d);font-weight:600}
.rule{height:1px;background:var(--line);border:0;margin:0}

/* boutons — un plein, un texte. pas deux pilules identiques */
.btn{display:inline-flex;align-items:center;gap:.5rem;padding:1rem 1.75rem;border-radius:10px;font-weight:700;font-size:.95rem;line-height:1;transition:background .15s,color .15s,border-color .15s}
.btn-p{background:var(--ink);color:#fff}
.btn-p:hover{background:var(--tq-d)}
.btn-line{padding:0;border-radius:0;font-weight:700;color:var(--text);border-bottom:2px solid var(--line)}
.btn-line:hover{border-color:var(--tq)}
.btn-out{border:1.5px solid rgba(255,255,255,.5);color:#fff}
.btn-out:hover{background:rgba(255,255,255,.12)}

/* header : ombre discrète au scroll, pas de flou de gradient */
#fnav.scrolled{box-shadow:0 1px 0 var(--line)}

/* ============================================================
   HERO — asymétrique : texte à gauche, démo jouable à droite.
   Pas de bandeau plein écran centré, pas de blobs.
   ============================================================ */
.hero{padding:var(--s7) 0 var(--s6)}
.hero-grid{display:grid;grid-template-columns:1.08fr .92fr;gap:var(--s6);align-items:center}
.hero-grid>*{min-width:0}
@media(max-width:960px){.hero-grid{grid-template-columns:1fr;gap:var(--s5)}}
.hero h1{font-size:clamp(2.75rem,6.4vw,5.6rem);margin-top:var(--s3);max-width:14ch}
.hero h1 .w{display:inline-block;opacity:0;transform:translateY(40px);animation:pop .8s cubic-bezier(.22,1,.36,1) forwards}
@keyframes pop{to{opacity:1;transform:none}}
.hero h1 em{font-style:italic;color:var(--tq-d)}
.hero .lead{font-size:1.2rem;line-height:1.5;color:var(--muted);margin:var(--s4) 0 0;max-width:42ch;opacity:0;transform:translateY(40px);animation:pop .8s .15s cubic-bezier(.22,1,.36,1) forwards}
.hero .cta{display:flex;align-items:center;gap:var(--s4);margin-top:var(--s5);flex-wrap:wrap;opacity:0;transform:translateY(24px);animation:pop .6s .38s cubic-bezier(.22,1,.36,1) forwards}
.hero .proof{display:flex;gap:var(--s3);flex-wrap:wrap;margin-top:var(--s5);padding-top:var(--s3);border-top:1px solid var(--line);font-family:var(--mono);font-size:.78rem;letter-spacing:.03em;color:var(--faint);opacity:0;transform:translateY(24px);animation:pop .6s .5s cubic-bezier(.22,1,.36,1) forwards}
.hero .proof b{color:var(--text);font-weight:600}

/* panneau démo : encre pleine, plate, sans halo de couleur */
.demo{position:relative;background:var(--ink);border-radius:20px;padding:var(--s5) var(--s4);opacity:0;transform:translateY(30px) scale(1.02);animation:demoIn 1s .2s cubic-bezier(.22,1,.36,1) forwards}
@keyframes demoIn{to{opacity:1;transform:none}}
.phone{position:relative;width:280px;max-width:100%;margin:0 auto;aspect-ratio:280/560;background:#08181d;border-radius:34px;padding:10px;box-shadow:0 30px 60px -24px rgba(0,0,0,.55)}
.phone .notch{position:absolute;top:10px;left:50%;transform:translateX(-50%);width:84px;height:18px;background:#08181d;border-radius:0 0 12px 12px;z-index:5}
.screen{position:relative;width:100%;height:100%;border-radius:26px;overflow:hidden;background:#FBF3E7;display:flex;flex-direction:column}
.sc-head{padding:22px 14px 10px;display:flex;align-items:center;gap:8px;color:#241A12;background:#fff}
.sc-head .ic{width:26px;height:26px;border-radius:8px;background:#241A12;color:#fff;display:grid;place-items:center;font-family:var(--disp);font-weight:800;font-size:12px}
.sc-head .nm{font-family:var(--disp);font-weight:700;font-size:13px;text-align:left;color:#241A12}
.sc-head .sb{font-size:8.5px;color:#9C8B74;font-family:var(--mono)}
.sc-body{flex:1;padding:11px;overflow:hidden}
.mcard{position:relative;border-radius:16px;overflow:hidden;color:#fff;padding:15px;background:linear-gradient(155deg,#241A12,#9C4024 130%);cursor:pointer;user-select:none;-webkit-tap-highlight-color:transparent;transition:transform .12s}
.mcard:active{transform:scale(.985)}
.mcard>*{position:relative;z-index:2}
.mc-top{display:flex;justify-content:space-between;align-items:flex-start}
.mc-tier{padding:3px 8px;border-radius:6px;background:rgba(228,197,131,.2);border:1px solid rgba(228,197,131,.5);color:var(--gold-l);font-size:8.5px;font-weight:700;letter-spacing:.03em}
.mc-chip{width:26px;height:20px;border-radius:4px;background:linear-gradient(135deg,var(--gold-l),var(--gold))}
.mc-name{font-family:var(--disp);font-weight:700;font-size:16px;margin-top:11px}
.mc-since{font-size:8.5px;color:rgba(255,255,255,.68);font-family:var(--mono)}
.mc-bot{display:flex;align-items:flex-end;justify-content:space-between;margin-top:13px;gap:10px}
.mc-k{font-size:7.5px;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.6)}
.mc-v{display:inline-block;transform-origin:left center;font-family:var(--disp);font-weight:800;font-size:35px;line-height:.85}.mc-v small{font-size:11px;color:var(--gold-l)}
.mc-v.bump{animation:ptbump .4s cubic-bezier(.2,1.6,.4,1)}
@keyframes ptbump{0%{transform:scale(1)}40%{transform:scale(1.28)}100%{transform:scale(1)}}
.mc-qr{background:#fff;border-radius:8px;padding:5px;flex-shrink:0}.mc-qr svg{display:block;width:48px;height:48px}
.hgoal{margin-top:11px;font-size:8.5px;font-family:var(--mono);color:rgba(255,255,255,.55);display:flex;justify-content:space-between;gap:8px}
.hprog{margin-top:5px;height:5px;border-radius:99px;background:rgba(255,255,255,.16);overflow:hidden}
.hprog i{display:block;height:100%;width:0;border-radius:99px;background:var(--tq-l);transition:width .5s cubic-bezier(.2,.8,.3,1)}
.sc-rew{margin-top:11px}
.sc-rew .r{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:#fff;border:1px solid #E9DAC3;margin-bottom:6px;transition:border-color .3s}
.sc-rew .r .p{width:30px;height:30px;border-radius:8px;background:#F6EBDD;color:#9C4024;display:grid;place-items:center;font-family:var(--mono);font-weight:700;font-size:11px;flex-shrink:0}
.sc-rew .r.ok .p{background:linear-gradient(140deg,var(--gold-l),var(--gold));color:#3a2a05}
.sc-rew .r .t{font-size:10.5px;font-weight:600;color:#241A12}
.sc-rew .r .st{margin-left:auto;font-size:8.5px;font-weight:700;color:#9C8B74}
.sc-rew .r.ok .st{color:var(--gold);font-weight:800}
.sc-rew .r.pop{animation:rwpop .55s cubic-bezier(.2,1.5,.4,1)}
@keyframes rwpop{0%{transform:scale(1)}30%{transform:scale(1.05)}100%{transform:scale(1)}}
#confetti{position:absolute;inset:0;z-index:4;pointer-events:none}
.coin{position:absolute;z-index:6;width:30px;height:30px;border-radius:50%;background:radial-gradient(circle at 35% 30%,var(--gold-l),var(--gold));color:#3a2a05;display:grid;place-items:center;font-family:var(--disp);font-weight:800;font-size:12px;pointer-events:none;opacity:0}
@keyframes rise{0%{opacity:0;transform:translateY(0) scale(.5)}20%{opacity:1}100%{opacity:0;transform:translateY(-160px) scale(1)}}
.tapwrap{position:relative;z-index:5;margin-top:var(--s4);display:flex;flex-direction:column;align-items:center;gap:.5rem;opacity:0;animation:pop .6s 1.05s ease forwards}
.tapcta{background:transparent;color:#fff;font-family:var(--body);font-weight:700;font-size:.85rem;padding:.7rem 1.2rem;border-radius:9px;border:1.5px solid rgba(255,255,255,.4);cursor:pointer;transition:border-color .2s,background .2s}
.tapcta:hover{border-color:#fff;background:rgba(255,255,255,.08)}
.taphint{color:rgba(255,255,255,.65);font-size:.75rem;font-family:var(--mono);min-height:16px}
@media(prefers-reduced-motion:reduce){.tapcta,.hprog i,.mc-v.bump,.hero h1 .w,.hero .lead,.hero .cta,.hero .proof,.demo,.tapwrap{animation:none!important;opacity:1!important;transform:none!important}#confetti{display:none}}

/* ticker discret, une ligne, sans bandeau sombre */
.ticker{border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:var(--s3) 0;overflow:hidden;white-space:nowrap}
.ticker .run{display:inline-block;animation:scrollx 28s linear infinite;font-family:var(--mono);font-size:.85rem;letter-spacing:.04em;color:var(--faint)}
.ticker .run span{margin:0 var(--s4)}
@keyframes scrollx{from{transform:translateX(0)}to{transform:translateX(-50%)}}
@media(prefers-reduced-motion:reduce){.ticker .run{animation:none}}

section.band{padding:var(--s7) 0}
.head{max-width:640px;margin-bottom:var(--s5)}
.head h2{font-size:clamp(1.9rem,4vw,2.9rem);margin-top:var(--s2)}
.head p{color:var(--muted);font-size:1.1rem;margin-top:var(--s2);line-height:1.5}

/* ============================================================
   LE PROBLÈME — deux colonnes de texte séparées par une règle,
   pas deux cartes colorées.
   ============================================================ */
.cmp{display:grid;grid-template-columns:1fr 1px 1fr;gap:var(--s5);max-width:960px}
.cmp>*{min-width:0}
@media(max-width:760px){.cmp{grid-template-columns:1fr}.cmp .rule-v{display:none}}
.rule-v{background:var(--line)}
.cmp-card h3{font-size:1.05rem;font-weight:700;font-family:var(--mono);text-transform:uppercase;letter-spacing:.04em}
.cmp-sans h3{color:var(--faint)}.cmp-avec h3{color:var(--tq-d)}
.cmp-card ul{list-style:none;padding:0;margin:var(--s3) 0 0;display:flex;flex-direction:column;gap:var(--s3)}
.cmp-card li{display:flex;gap:.7rem;font-size:1.05rem;line-height:1.45}.cmp-card li svg{width:18px;height:18px;flex-shrink:0;margin-top:3px}
.cmp-sans li{color:var(--muted)}.cmp-avec li{color:var(--text);font-weight:500}
.check{color:var(--tq-d)}.cross{color:var(--faint)}

/* ============================================================
   COMMENT ÇA MARCHE — liste numérotée éditoriale, pas 3 cartes.
   ============================================================ */
.how{display:grid;grid-template-columns:.62fr 1fr;gap:var(--s6)}
.how>*{min-width:0}
@media(max-width:860px){.how{grid-template-columns:1fr;gap:var(--s5)}}
.steps{display:flex;flex-direction:column}
.step{display:grid;grid-template-columns:64px 1fr;gap:var(--s3);padding:var(--s4) 0;border-top:1px solid var(--line)}
.steps .step:last-child{border-bottom:1px solid var(--line)}
.step .no{font-family:var(--mono);font-size:1.4rem;font-weight:600;color:var(--faint)}
.step h3{font-size:1.2rem}.step p{color:var(--muted);font-size:1rem;margin-top:.4rem;line-height:1.5;max-width:44ch}

/* ============================================================
   ROI — cadre plat, chiffre en accent plein (pas de fond dégradé)
   ============================================================ */
.roi{max-width:980px;display:grid;grid-template-columns:1fr 1fr;gap:var(--s6);align-items:center}
.roi>*{min-width:0}
@media(max-width:760px){.roi{grid-template-columns:1fr;gap:var(--s4)}}
.roi-c label{display:flex;justify-content:space-between;font-size:.85rem;font-weight:600;color:var(--text);margin:var(--s3) 0 .4rem}.roi-c label:first-child{margin-top:0}
.roi-c label b{font-family:var(--mono);color:var(--tq-d);font-weight:600}
.roi-c input[type=range]{width:100%;accent-color:var(--ink);height:2px}
.roi-out{border:1px solid var(--line);border-radius:16px;padding:var(--s4)}
.roi-out .k{font-size:.75rem;font-family:var(--mono);letter-spacing:.08em;text-transform:uppercase;color:var(--faint)}
.roi-out .big{font-family:var(--disp);font-weight:800;font-size:3rem;line-height:1;margin:.5rem 0 .2rem;color:var(--tq-d)}
.roi-out .cur{color:var(--muted);font-size:.9rem}.roi-out hr{border:0;border-top:1px solid var(--line);margin:var(--s3) 0}
.roi-out .l{display:flex;justify-content:space-between;font-size:.92rem;margin-bottom:.5rem}.roi-out .l span{color:var(--muted)}.roi-out .l b{font-family:var(--mono);font-weight:600}

/* ============================================================
   CHIFFRES — bande encre plate, nombres séparés par des règles.
   ============================================================ */
.stats-band{background:var(--ink);color:#fff}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr)}
@media(max-width:640px){.stats-grid{grid-template-columns:1fr 1fr}}
.stats-grid>div{padding:var(--s4) var(--s3);border-left:1px solid rgba(255,255,255,.14)}
.stats-grid>div:first-child{border-left:0}
@media(max-width:640px){.stats-grid>div:nth-child(2n+1){border-left:0}.stats-grid>div{border-top:1px solid rgba(255,255,255,.14)}.stats-grid>div:nth-child(-n+2){border-top:0}}
.stats-grid .n{font-family:var(--disp);font-weight:800;font-size:clamp(2rem,4.6vw,3rem);line-height:1;color:#fff}
.stats-grid .n em{font-style:normal;color:var(--tq-l)}
.stats-grid .k{color:rgba(255,255,255,.62);font-size:.85rem;margin-top:.6rem;line-height:1.4}
.cite{margin-top:var(--s5);color:rgba(255,255,255,.55);font-size:.9rem;max-width:640px}.cite b{color:var(--gold-l);font-weight:600}

/* ============================================================
   RÉCOMPENSES — règle verticale fine, pas de cartes à ombre.
   ============================================================ */
.cascade{max-width:600px}
.crow{display:flex;align-items:baseline;gap:var(--s3);padding:var(--s3) 0;border-top:1px solid var(--line)}
.cascade .crow:last-child{border-bottom:1px solid var(--line)}
.cnode{font-family:var(--mono);font-weight:700;color:var(--tq-d);font-size:1.1rem;width:64px;flex-shrink:0}
.cnode small{display:block;font-size:.6rem;letter-spacing:.08em;color:var(--faint);font-weight:400}
.cinfo h4{font-size:1.1rem;font-weight:600}.cinfo p{color:var(--muted);font-size:.92rem;margin-top:.25rem}

/* ============================================================
   PRIX — une offre en typographie large + les autres en liste
   plate. Pas 4 cartes identiques.
   ============================================================ */
.pricing{display:grid;grid-template-columns:1.15fr .85fr;gap:var(--s6);align-items:start}
.pricing>*{min-width:0}
@media(max-width:860px){.pricing{grid-template-columns:1fr;gap:var(--s5)}}
.feat{border:1px solid var(--line);border-radius:20px;padding:var(--s5)}
.feat .fl{font-family:var(--mono);font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;color:var(--tq-d);font-weight:700}
.feat .pr{font-family:var(--disp);font-weight:800;font-size:3.6rem;line-height:1;margin:var(--s2) 0 .3rem}
.feat .pr small{font-size:1rem;color:var(--muted);font-weight:500}
.feat .u{color:var(--muted);font-size:.95rem}
.feat ul{list-style:none;padding:0;margin:var(--s4) 0;display:flex;flex-direction:column;gap:.8rem}
.feat li{display:flex;gap:.6rem;font-size:1rem}.feat li svg{width:18px;height:18px;flex-shrink:0;color:var(--tq-d);margin-top:2px}
.plist{display:flex;flex-direction:column}
.plan{display:flex;align-items:baseline;justify-content:space-between;gap:var(--s2);padding:var(--s3) 0;border-top:1px solid var(--line)}
.plist .plan:last-child{border-bottom:1px solid var(--line)}
.plan .nm{font-weight:700;font-size:1.05rem}.plan .u{color:var(--faint);font-size:.8rem;margin-top:.15rem}
.plan .pr{font-family:var(--mono);font-weight:600;font-size:1.05rem;white-space:nowrap}
.plan a{color:var(--tq-d);font-weight:700;font-size:.85rem;white-space:nowrap}
.plans-more{margin-top:var(--s3)}
.plans-more a{color:var(--tq-d);font-weight:700;font-size:.95rem}

/* ============================================================
   FAQ — liste plate, règle en bas, pas de boîtes bordées.
   ============================================================ */
.faq{max-width:760px}
.faq details{border-top:1px solid var(--line);padding:var(--s3) 0}
.faq details:last-child{border-bottom:1px solid var(--line)}
.faq summary{list-style:none;cursor:pointer;font-family:var(--disp);font-weight:700;font-size:1.15rem;color:var(--text);display:flex;justify-content:space-between;align-items:center;gap:var(--s2)}
.faq summary::-webkit-details-marker{display:none}.faq summary .pl{color:var(--tq-d);font-size:1.5rem;transition:transform .2s;flex-shrink:0}
.faq details[open] summary .pl{transform:rotate(45deg)}.faq p{color:var(--muted);margin-top:.7rem;font-size:1rem;max-width:60ch;line-height:1.55}

/* ============================================================
   CTA FINAL — plein écran encre, une déclaration typographique.
   ============================================================ */
.footcta{background:var(--ink);color:#fff;padding:var(--s7) 0;text-align:left}
.footcta h2{font-size:clamp(2rem,5vw,3.6rem);color:#fff;max-width:16ch}
.footcta p{color:rgba(255,255,255,.7);font-size:1.15rem;margin:var(--s3) 0 var(--s4);max-width:46ch}

footer.site{padding:var(--s5) 0 var(--s6);border-top:1px solid var(--line)}
.foot-grid{display:flex;justify-content:space-between;align-items:center;gap:var(--s3);flex-wrap:wrap;color:var(--muted);font-size:.9rem}
.foot-grid .brand{color:var(--text);font-family:var(--disp);font-weight:800;font-size:1.15rem}.foot-grid .brand .d{color:var(--tq-d)}

/* scroll-reveal + utilitaires */
#scrollbar{position:fixed;top:0;left:0;height:2px;width:0;z-index:200;background:var(--tq-d);transition:width .12s linear}
/* système de mouvement — un seul easing, des durées selon l'importance */
:root{--ease:cubic-bezier(.22,1,.36,1)}
.reveal-ready .rv{opacity:0;transform:translateY(46px);transition:opacity .8s var(--ease),transform .8s var(--ease);transition-delay:var(--d,0s)}
.reveal-ready .rv.in{opacity:1;transform:none}
.reveal-ready .rv-cta{opacity:0;transform:translateY(20px);transition:opacity .6s var(--ease),transform .6s var(--ease)}
.reveal-ready .rv-cta.in{opacity:1;transform:none}
.reveal-ready .cascade::before{content:none}
#fnav{animation:navFade .5s var(--ease) both;transition:box-shadow .3s ease}
@keyframes navFade{from{opacity:0}to{opacity:1}}
.demo{will-change:transform}
@media(prefers-reduced-motion:reduce){#fnav{animation:none}}
.up{position:fixed;right:20px;bottom:20px;z-index:150;width:46px;height:46px;border-radius:12px;background:var(--ink);color:#fff;display:grid;place-items:center;opacity:0;transform:translateY(18px);pointer-events:none;transition:opacity .25s,transform .25s,background .2s}
.up.on{opacity:1;transform:none;pointer-events:auto}
.up:hover{background:var(--tq-d)}.up svg{width:20px;height:20px}
@media(prefers-reduced-motion:reduce){.reveal-ready .rv,.reveal-ready .rv-cta{opacity:1!important;transform:none!important}#scrollbar{display:none}.up{transition:none}}
.flogo{width:1.35em;height:1.35em;display:inline-block;vertical-align:-.32em;margin-right:.34em}
</style></head><body>
<div id="scrollbar"></div>

<?php nav_bar($base, 'accueil'); ?>

<main>
<!-- HERO -->
<section class="hero"><div class="wrap hero-grid">
  <div>
    <span class="eyebrow">Programme de fidélité</span>
    <h1 id="h1"><span class="w">Vos</span> <span class="w">clients</span> <span class="w"><em>reviennent</em>.</span><br><span class="w">Sans</span> <span class="w">carte,</span> <span class="w">sans</span> <span class="w">appli.</span></h1>
    <p class="lead">Le client montre son code, vous le scannez, le point est ajouté. C'est tout. Fidelo transforme un passage en habitude — et une habitude en chiffre d'affaires.</p>
    <div class="cta">
      <a href="<?= e($base) ?>/inscription.php" class="btn btn-p">Commencer gratuitement</a>
      <a href="#comment" class="btn-line">Voir comment ça marche →</a>
    </div>
    <div class="proof"><span><b>0 €</b> pour démarrer</span><span><b>5 min</b> d'installation</span><span><b>0</b> matériel à acheter</span></div>
  </div>

  <!-- démo jouable -->
  <div class="demo" id="stage">
    <div class="phone">
      <div class="notch"></div>
      <div class="screen">
        <div class="sc-head"><div class="ic">C</div><div><div class="nm">Café des Amis</div><div class="sb">CARTE DE FIDÉLITÉ</div></div></div>
        <div class="sc-body">
          <div class="mcard" id="hcard">
            <canvas id="confetti"></canvas>
            <div class="mc-top"><span class="mc-tier" id="htier">⭐ FIDÈLE</span><span class="mc-chip"></span></div>
            <div class="mc-name">Sarah B.</div><div class="mc-since" id="hsince">14 visites</div>
            <div class="mc-bot"><div><div class="mc-k">Points</div><div class="mc-v" id="hptsWrap"><span id="hpts">0</span><small>pts</small></div></div>
              <div class="mc-qr"><div id="hqr"></div></div></div>
            <div class="hgoal"><span id="hgLabel">Prochain palier</span><span id="hgCount">0/6</span></div>
            <div class="hprog"><i id="hgFill"></i></div>
          </div>
          <div class="sc-rew" id="hrew">
            <div class="r" data-pts="3"><span class="p">3</span><span class="t">☕ Boisson offerte</span><span class="st">3 pts</span></div>
            <div class="r" data-pts="6"><span class="p">6</span><span class="t">🍰 Dessert maison</span><span class="st">6 pts</span></div>
            <div class="r" data-pts="10"><span class="p">10</span><span class="t">💸 −15 % addition</span><span class="st">10 pts</span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="tapwrap">
      <button class="tapcta" id="tapBtn" type="button">Touchez la carte pour tester</button>
      <span class="taphint" id="tapHint">Comme le fera votre commerçant, à chaque passage.</span>
    </div>
  </div>
</div></section>

<!-- ticker -->
<div class="ticker"><div class="run" id="marq"></div></div>

<!-- LE PROBLÈME -->
<section class="band"><div class="wrap">
  <div class="head rv"><span class="eyebrow">Le vrai problème</span><h2>Attirer un client coûte cher.<br>Le garder, presque rien.</h2><p>Vous dépensez tout en publicité pour attirer — puis vous laissez partir. Fidelo travaille l'autre côté.</p></div>
  <div class="cmp">
    <div class="cmp-card cmp-sans rv"><h3>Sans Fidelo</h3><ul>
      <li><svg class="cross" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M18 6 6 18M6 6l12 12"/></svg> Le client vient une fois… et vous l'oubliez.</li>
      <li><svg class="cross" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M18 6 6 18M6 6l12 12"/></svg> Cartes à tampons perdues, oubliées, trichées.</li>
      <li><svg class="cross" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M18 6 6 18M6 6l12 12"/></svg> Aucune donnée : impossible de recontacter.</li></ul></div>
    <div class="rule-v"></div>
    <div class="cmp-card cmp-avec rv"><h3>Avec Fidelo</h3><ul>
      <li><svg class="check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg> Un scan, un point : le client gagne à revenir.</li>
      <li><svg class="check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg> Rien à perdre : tout est dans son téléphone.</li>
      <li><svg class="check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg> Anti-fraude : c'est VOUS qui scannez, jamais le client.</li></ul></div>
  </div>
</div></section>

<!-- COMMENT -->
<section class="band" id="comment" style="background:var(--card)"><div class="wrap how">
  <div class="head rv" style="margin-bottom:0"><span class="eyebrow">Scan &amp; Point</span><h2>Trois secondes à la caisse.<br>C'est tout.</h2><p>Pas de matériel, pas de formation. Votre téléphone suffit.</p></div>
  <div class="steps">
    <div class="step rv"><span class="no">01</span><div><h3>Le client montre son code</h3><p>Reçu par lien ou ajouté à son téléphone. Aucune appli à télécharger.</p></div></div>
    <div class="step rv"><span class="no">02</span><div><h3>Vous scannez</h3><p>Depuis votre espace Fidelo. C'est le commerçant qui scanne — impossible de tricher.</p></div></div>
    <div class="step rv"><span class="no">03</span><div><h3>Le point s'ajoute, la récompense arrive</h3><p>Le client voit son compteur monter et débloque un café, un dessert, une remise.</p></div></div>
  </div>
</div></section>

<!-- ROI -->
<section class="band" id="gains"><div class="wrap">
  <div class="head rv"><span class="eyebrow">Calculez vos gains</span><h2>Combien la fidélité vous rapporte&nbsp;?</h2><p>Bougez les curseurs avec vos vrais chiffres.</p></div>
  <div class="roi rv">
    <div class="roi-c">
      <label>Clients par jour <b id="vC">80</b></label><input type="range" id="rC" min="10" max="400" step="5" value="80">
      <label>Panier moyen <b id="vP">14 €</b></label><input type="range" id="rP" min="5" max="80" step="1" value="14">
      <label>Clients fidélisés en plus <b id="vT">20 %</b></label><input type="range" id="rT" min="5" max="40" step="1" value="20">
      <label>Visites gagnées / mois <b id="vF">2</b></label><input type="range" id="rF" min="1" max="6" step="1" value="2">
    </div>
    <div class="roi-out"><div class="k">Chiffre d'affaires récupéré</div><div class="big" id="rBig">0 €</div><div class="cur">par mois, estimé</div><hr>
      <div class="l"><span>Sur l'année</span><b id="rYear">0 €</b></div><div class="l"><span>Coût de Fidelo</span><b>29 € / mois</b></div><div class="l"><span>Retour</span><b id="rX">×0</b></div></div>
  </div>
</div></section>

<!-- CHIFFRES -->
<section class="band stats-band"><div class="wrap">
  <div class="head rv" style="margin-bottom:var(--s5)"><span class="eyebrow" style="color:var(--tq-l)">Ce que change la fidélité</span><h2 style="color:#fff">Les habitués font tourner le commerce</h2></div>
  <div class="stats-grid">
    <div class="rv"><div class="n" data-c="65">0<em>%</em></div><div class="k">du CA vient de clients connus</div></div>
    <div class="rv"><div class="n" data-c="5"><em>×</em>0</div><div class="k">plus cher d'acquérir que de fidéliser</div></div>
    <div class="rv"><div class="n" data-c="27">+0<em>%</em></div><div class="k">de fréquence de visite en moyenne</div></div>
    <div class="rv"><div class="n" data-c="3">0<em>s</em></div><div class="k">pour créditer un point</div></div>
  </div>
  <p class="cite">D'après <b>Harvard Business Review</b> et <b>Bain &amp; Company</b> : +5 % de rétention peut accroître les profits de 25 à 95 %.</p>
</div></section>

<!-- RÉCOMPENSES -->
<section class="band"><div class="wrap">
  <div class="head rv"><span class="eyebrow">Menu en cascade</span><h2>Une échelle de récompenses,<br>pas un seul palier</h2><p>Le client garde toujours un objectif devant lui. Vous composez le menu.</p></div>
  <div class="cascade">
    <div class="crow rv"><div class="cnode">3<small>PTS</small></div><div class="cinfo"><h4>☕ Boisson offerte</h4><p>Le premier palier — il déclenche l'habitude.</p></div></div>
    <div class="crow rv"><div class="cnode">6<small>PTS</small></div><div class="cinfo"><h4>🍰 Dessert maison</h4><p>La récompense « plaisir » qui fait parler.</p></div></div>
    <div class="crow rv"><div class="cnode">10<small>PTS</small></div><div class="cinfo"><h4>💸 −15 % sur l'addition</h4><p>Un vrai geste pour le régulier.</p></div></div>
    <div class="crow rv"><div class="cnode">20<small>PTS</small></div><div class="cinfo"><h4>👑 Menu offert · VIP</h4><p>Le sommet : votre meilleur client se sent reconnu.</p></div></div>
  </div>
</div></section>

<!-- PRIX -->
<section class="band" id="prix" style="background:var(--card)"><div class="wrap">
  <div class="head rv"><span class="eyebrow">Nos offres</span><h2>Commencez gratuitement.</h2><p>Jusqu'à 30 clients sans rien payer, sans limite de durée. Puis la formule qui vous ressemble — pas de commission, pas de matériel, pas de surprise.</p></div>
  <div class="pricing">
    <div class="feat rv">
      <span class="fl">Le plus choisi · Annuel</span>
      <div class="pr">250 €<small> / an</small></div>
      <div class="u">soit 20,83 € / mois · clients illimités · prix bloqué 12 mois</div>
      <ul>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg> 28 % moins cher que le mensuel</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg> Clients, récompenses et messages illimités</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg> Google Wallet, export Excel, appareils multiples</li>
      </ul>
      <a href="<?= e($base) ?>/inscription.php" class="btn btn-p">Commencer</a>
    </div>
    <div class="plist rv">
      <div class="plan"><div><div class="nm">Découverte</div><div class="u">jusqu'à 30 clients · pour toujours</div></div><div style="text-align:right"><div class="pr">0 €</div><a href="<?= e($base) ?>/inscription.php">Essayer →</a></div></div>
      <div class="plan"><div><div class="nm">Mensuel</div><div class="u">sans engagement</div></div><div style="text-align:right"><div class="pr">29 €/mo</div><a href="<?= e($base) ?>/tarifs.php">Détails →</a></div></div>
      <div class="plan"><div><div class="nm">À vie + site web</div><div class="u">une fois, puis 50 €/an</div></div><div style="text-align:right"><div class="pr">525 €</div><a href="<?= e($base) ?>/tarifs.php">Détails →</a></div></div>
      <div class="plans-more"><a href="<?= e($base) ?>/tarifs.php">Comparer les formules en détail →</a></div>
    </div>
  </div>
</div></section>

<!-- FAQ -->
<section class="band"><div class="wrap">
  <div class="head rv"><span class="eyebrow">Questions fréquentes</span><h2>Ce qu'on nous demande souvent</h2></div>
  <div class="faq">
    <details open class="rv"><summary>Mon client doit-il installer une application ? <span class="pl">+</span></summary><p>Non. Il reçoit son code de fidélité et vous le scannez depuis votre espace. Rien à télécharger côté client.</p></details>
    <details class="rv"><summary>Comment évite-t-on la triche ? <span class="pl">+</span></summary><p>C'est le commerçant qui scanne le code du client, jamais l'inverse. Un client ne peut pas s'ajouter de points.</p></details>
    <details class="rv"><summary>Faut-il acheter du matériel ? <span class="pl">+</span></summary><p>Non. Un simple smartphone ou tablette suffit. La caméra fait office de lecteur.</p></details>
    <details class="rv"><summary>Que se passe-t-il après les 10 jours ? <span class="pl">+</span></summary><p>Vous choisissez de continuer à 29 €/mois, ou d'arrêter. Sans engagement, aucune carte demandée pour l'essai.</p></details>
  </div>
</div></section>

<!-- CTA FINAL -->
<section class="footcta"><div class="wrap rv-cta">
  <h2>Vos habitués valent de l'or.<br>Commencez à les récompenser.</h2>
  <p>Inscription gratuite, 10 jours d'essai. Vos premiers points sont crédités dès le prochain client.</p>
  <a href="<?= e($base) ?>/inscription.php" class="btn btn-out">Créer mon compte gratuitement →</a>
</div></section>
</main>

<button id="up" class="up" aria-label="Remonter en haut"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg></button>

<footer class="site"><div class="wrap foot-grid">
  <a class="brand" href="#">Fidelo<span class="d">.</span></a>
  <span>Le programme de fidélité des commerces de proximité.</span>
  <span style="display:flex;gap:15px;flex-wrap:wrap">
    <a href="tarifs.php" style="color:var(--muted);font-weight:600">Nos offres</a>
    <a href="confidentialite.php" style="color:var(--muted);font-weight:600">Confidentialité</a>
    <a href="mentions-legales.php" style="color:var(--muted);font-weight:600">Mentions légales</a>
  </span>
  <span class="mono" style="font-size:12px;color:var(--faint)">© 2026 Fidelo</span>
</div></footer>

<script src="qrcode.min.js"></script>
<script>
const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)];
$$('#h1 .w').forEach((w,i)=>w.style.animationDelay=(0.05+i*0.09)+'s');
try{const q=qrcode(0,'M');q.addData('FIDELO:DEMO12');q.make();$('#hqr').innerHTML=q.createSvgTag(2,0);const s=$('#hqr svg');if(s){s.setAttribute('width','48');s.setAttribute('height','48');}}catch(e){}
const stage=$('#stage');

/* ===== carte de fidélité JOUABLE : on laisse le visiteur essayer le geste ===== */
(function(){
  const card=$('#hcard'),ptsEl=$('#hpts'),ptsWrap=$('#hptsWrap'),fill=$('#hgFill'),
        gCount=$('#hgCount'),gLabel=$('#hgLabel'),tier=$('#htier'),since=$('#hsince'),
        rows=$$('#hrew .r'),tapBtn=$('#tapBtn'),tapHint=$('#tapHint'),canvas=$('#confetti');
  if(!card||!canvas)return;
  const reduce=matchMedia('(prefers-reduced-motion:reduce)').matches;
  const rewards=rows.map(r=>({pts:+r.dataset.pts,el:r}));
  const maxPts=rewards[rewards.length-1].pts;
  let points=0,visits=14,busy=false;

  /* mini-confetti maison (canvas, sans librairie externe) */
  const ctx=canvas.getContext('2d');let parts=[],raf=null;
  function resize(){canvas.width=card.clientWidth;canvas.height=card.clientHeight;}
  resize();addEventListener('resize',resize);
  const COLORS=['#E4C583','#E08A5D','#B8863A','#F0AE85'];
  function loop(){
    ctx.clearRect(0,0,canvas.width,canvas.height);
    parts.forEach(p=>{p.x+=p.vx;p.y+=p.vy;p.vy+=p.g;p.r+=p.vr;p.life--;
      ctx.save();ctx.translate(p.x,p.y);ctx.rotate(p.r);ctx.globalAlpha=Math.max(0,p.life/70);
      ctx.fillStyle=p.c;ctx.fillRect(-p.s/2,-p.s/2,p.s,p.s*.6);ctx.restore();});
    parts=parts.filter(p=>p.life>0&&p.y<canvas.height+20);
    if(parts.length)raf=requestAnimationFrame(loop);else{raf=null;ctx.clearRect(0,0,canvas.width,canvas.height);}
  }
  function burst(){
    if(reduce)return;
    for(let i=0;i<22;i++)parts.push({x:canvas.width*.78,y:canvas.height*.32,
      vx:(Math.random()-.5)*7,vy:-Math.random()*7-2,g:.22+Math.random()*.08,s:3+Math.random()*3,
      c:COLORS[i%COLORS.length],r:Math.random()*Math.PI,vr:(Math.random()-.5)*.3,life:60+Math.random()*20});
    if(!raf)loop();
  }

  function coin(){
    if(reduce)return;
    const c=document.createElement('div');c.className='coin';c.textContent='+1';
    c.style.left=(38+Math.random()*24)+'%';c.style.top='42%';
    c.style.animation='rise '+(1.7+Math.random()*.6)+'s ease-out forwards';
    stage.appendChild(c);setTimeout(()=>c.remove(),2600);
  }

  function nextReward(){return rewards.find(r=>r.pts>points)||null;}
  function render(justUnlocked){
    ptsEl.textContent=points;
    const nx=nextReward();
    if(nx){gLabel.textContent='Prochain palier';gCount.textContent=points+'/'+nx.pts;fill.style.width=Math.min(100,points/nx.pts*100)+'%';}
    else{gLabel.textContent='Palier maximum';gCount.textContent=points+'/'+maxPts;fill.style.width='100%';}
    rows.forEach(r=>{const p=+r.dataset.pts,st=r.querySelector('.st');
      if(points>=p){r.classList.add('ok');st.textContent='DISPO';}
      else{r.classList.remove('ok');st.textContent=p+' pts';}});
    tier.textContent=points>=maxPts?'👑 VIP':points>=rewards[1].pts?'⭐ FIDÈLE':'🌱 NOUVEAU';
    if(justUnlocked){
      const row=rewards.find(r=>r.pts===points);
      if(row){row.el.classList.remove('pop');void row.el.offsetWidth;row.el.classList.add('pop');burst();}
    }
  }

  function tap(auto){
    if(busy&&!auto)return;
    ptsWrap.classList.remove('bump');void ptsWrap.offsetWidth;ptsWrap.classList.add('bump');
    coin();
    if(points>=maxPts){
      visits++;since.textContent=visits+' visites';
      tapHint.textContent='🎉 Récompense réclamée — nouveau cycle !';
      burst();busy=true;
      setTimeout(()=>{points=0;render(false);tapHint.textContent='À vous de jouer — touchez la carte !';busy=false;},1100);
      return;
    }
    const willUnlock=rewards.some(r=>r.pts===points+1);
    points++;render(willUnlock);
    tapHint.textContent=willUnlock?'🔓 Récompense débloquée !':'+1 point — comme à la caisse.';
  }

  card.addEventListener('click',()=>tap(false));
  tapBtn.addEventListener('click',()=>{tap(false);tapBtn.blur();});

  render(false);
  if(!reduce){
    let n=0;const auto=setInterval(()=>{if(n>=3){clearInterval(auto);return;}tap(true);n++;},900);
    setTimeout(()=>clearInterval(auto),4500);
  }
})();

// ticker
const items=['Café','Restaurant','Salon','Boulangerie','Bar à jus','Pizzeria','Glacier','Coiffeur','Fleuriste','Boucherie'];
$('#marq').innerHTML=(items.map(i=>`<span>${i}</span>`).join('')).repeat(2);

const fmt=n=>Math.round(n).toLocaleString('fr-FR')+' €';
function roi(){const c=+$('#rC').value,p=+$('#rP').value,t=+$('#rT').value/100,f=+$('#rF').value;
  $('#vC').textContent=c;$('#vP').textContent=p+' €';$('#vT').textContent=Math.round(t*100)+' %';$('#vF').textContent=f;
  const m=c*t*f*p;$('#rBig').textContent=fmt(m);$('#rYear').textContent=fmt(m*12);$('#rX').textContent='×'+Math.max(1,Math.round(m/29));}
['rC','rP','rT','rF'].forEach(id=>$('#'+id).oninput=roi);roi();
function countUp(){const red=matchMedia('(prefers-reduced-motion:reduce)').matches;
  $$('[data-c]').forEach(el=>{const target=+el.dataset.c,suf=el.innerHTML;if(red){el.innerHTML=suf.replace('0',target);return;}
    let v=0;const step=Math.max(1,target/28);const iv=setInterval(()=>{v+=step;if(v>=target){v=target;clearInterval(iv);}el.innerHTML=suf.replace(/0(?![^<]*>)/,Math.round(v));},30);});}
const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){countUp();io.disconnect();}}),{threshold:.4});
io.observe($('.stats-band'));

/* ===== scroll-reveal + micro-interactions ===== */
(function(){
  const reduce=matchMedia('(prefers-reduced-motion:reduce)').matches;
  document.body.classList.add('reveal-ready');
  const revealSel='.rv,.rv-cta';

  /* l'entrée du panneau démo utilise une animation CSS (forwards) ; une fois
     terminée, on la libère pour que le parallax au scroll puisse reprendre
     la main sur "transform" (sinon le fill-mode la bloquerait pour toujours). */
  const demoEl=$('.demo');
  if(demoEl)demoEl.addEventListener('animationend',()=>{
    demoEl.style.animation='none';demoEl.style.opacity='1';demoEl.style.transform='none';
  },{once:true});

  /* décalage de texte : 80–120 ms entre les éléments d'un même groupe
     (étapes, chiffres, cascade, FAQ, liste de prix) — un seul mouvement
     perçu comme une vague, pas des blocs qui apparaissent d'un coup. */
  [['.steps .step'],['.stats-grid>div'],['.cascade .crow'],['.faq details'],['.plist .plan']]
    .forEach(([sel])=>$$(sel).forEach((el,i)=>el.style.setProperty('--d',(i*0.09)+'s')));

  if(reduce){$$(revealSel).forEach(el=>el.classList.add('in'));}
  else{
    const rio=new IntersectionObserver(es=>es.forEach(e=>{
      if(e.isIntersecting){e.target.classList.add('in');rio.unobserve(e.target);}
    }),{threshold:.12,rootMargin:'0px 0px -6% 0px'});
    $$(revealSel).forEach(el=>rio.observe(el));
  }

  const bar=$('#scrollbar'),up=$('#up'),fnav=$('#fnav'),demo=$('.demo');
  let tick=false;
  function onScroll(){
    const h=document.documentElement,sc=h.scrollTop||document.body.scrollTop;
    const max=h.scrollHeight-h.clientHeight;
    if(bar)bar.style.width=(max>0?sc/max*100:0)+'%';
    if(up)up.classList.toggle('on',sc>520);
    if(fnav)fnav.classList.toggle('scrolled',sc>10);
    /* parallax discret sur le panneau de démo : lent, borné, jamais brusque */
    if(demo&&!reduce){
      const r=demo.getBoundingClientRect(),vh=innerHeight||document.documentElement.clientHeight;
      const p=Math.max(-1,Math.min(1,(r.top+r.height/2-vh/2)/vh));
      demo.style.transform='translateY('+(p*-40).toFixed(1)+'px)';
    }
    tick=false;
  }
  addEventListener('scroll',()=>{if(!tick){tick=true;requestAnimationFrame(onScroll);}},{passive:true});
  onScroll();
  if(up)up.onclick=()=>scrollTo({top:0,behavior:reduce?'auto':'smooth'});
})();
</script>
</body></html>
