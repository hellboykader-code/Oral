<?php
/* Fidelo — page d'accueil publique (présentation + inscription). */
require __DIR__ . '/lib.php';
require __DIR__ . '/nav.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title>Fidelo — La fidélité qui fait revenir vos clients</title>
<meta name="description" content="Le programme de fidélité sans carte et sans appli pour cafés, restaurants et commerces. Inscription gratuite, 10 jours d'essai.">
<meta name="theme-color" content="#080F0D">
<link rel="icon" href="<?= e($base) ?>/icon-192.png">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600&family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap">
<style>
/* ============================================================
   FIDELO — système éditorial / kinétique.
   Encre quasi-noire + papier chaud + un seul accent vif (teal),
   or réservé à la récompense, corail réservé à l'emphase.
   Grille 8px. La typographie EST le visuel principal.
   ============================================================ */
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
  /* alias de compatibilité — consommés par nav.php / legal-ui.php (mêmes tokens sur tout le site) */
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
.rule{height:1px;background:var(--line);border:0}

.btn{display:inline-flex;align-items:center;gap:.55rem;padding:1.05rem 1.9rem;border-radius:999px;font-weight:700;font-size:.98rem;line-height:1;transition:transform .2s var(--ease),background .2s,color .2s}
.btn-p{background:var(--ink);color:var(--paper)}
.btn-p:hover{transform:translateY(-3px)}
.btn-line{padding:0;border-radius:0;font-weight:700;color:var(--text);border-bottom:2px solid var(--ink)}
.btn-line:hover{color:var(--tq-d);border-color:var(--tq-d)}
.btn-out{border:1.5px solid rgba(245,242,234,.5);color:var(--paper)}
.btn-out:hover{background:rgba(245,242,234,.1);transform:translateY(-3px)}

/* nav : fond papier, fondu à l'arrivée */
#fnav{animation:navFade .6s var(--ease) both;transition:box-shadow .3s ease}
@keyframes navFade{from{opacity:0}to{opacity:1}}
#fnav.scrolled{box-shadow:0 1px 0 var(--line)}
@media(prefers-reduced-motion:reduce){#fnav{animation:none}}

/* ============================================================
   HERO — la typographie EST le visuel. Pas de mockup en vedette,
   asymétrie franche, chiffres en repère à droite.
   ============================================================ */
.hero{padding:var(--s6) 0 0;position:relative}
.hero .kicker{display:flex;align-items:center;gap:var(--s3);flex-wrap:wrap}
.hero .kicker .n{font-family:var(--mono);font-size:.8rem;color:var(--faint)}
.hero h1{font-size:clamp(3rem,10vw,9.6rem);margin-top:var(--s2);letter-spacing:-.015em;line-height:1.06}
.hero h1 .ln{display:block;overflow:hidden;padding-bottom:.1em;margin-bottom:-.1em}
.hero h1 .ln span{display:inline-block;transform:translateY(100%);animation:lineUp .9s var(--ease) forwards}
@keyframes lineUp{to{transform:translateY(0)}}
.hero h1 em{font-style:italic;font-weight:400;color:var(--tq-d)}
.hero-sub{display:grid;grid-template-columns:1.3fr .7fr;gap:var(--s5);align-items:end;margin-top:var(--s4);padding-bottom:var(--s5);border-bottom:1px solid var(--line)}
@media(max-width:860px){.hero-sub{grid-template-columns:1fr;gap:var(--s3)}}
.hero .lead{font-size:1.35rem;line-height:1.45;color:var(--text);max-width:34ch;opacity:0;animation:pop .8s .5s var(--ease) forwards}
.hero .lead::first-letter{font-family:var(--disp);font-size:3.4em;float:left;line-height:.78;padding-right:.06em;font-weight:700;color:var(--tq-d)}
@keyframes pop{to{opacity:1}}
.hero .cta-col{display:flex;flex-direction:column;align-items:flex-start;gap:var(--s3);opacity:0;animation:pop .8s .65s var(--ease) forwards}
.hero .proof{display:flex;flex-direction:column;gap:.4rem;font-family:var(--mono);font-size:.8rem;color:var(--faint)}
.hero .proof b{color:var(--text);font-weight:600}
@media(prefers-reduced-motion:reduce){.hero h1 .ln span{animation:none;transform:none}.hero .lead,.hero .cta-col{animation:none;opacity:1}}

/* ============================================================
   DÉMO — bandeau plein-bleed encre, carte flottante superposée.
   ============================================================ */
/* bandeau cinématique — plein-bleed, vidéo tournage réel du geste de fidélité */
.cine{position:relative;height:78vh;min-height:460px;max-height:760px;overflow:hidden;background:var(--ink)}
.cine video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center;background:var(--ink)}
.cine .scrim{position:absolute;inset:0;background:linear-gradient(0deg,rgba(8,15,13,.92) 0%,rgba(8,15,13,.35) 42%,rgba(8,15,13,.05) 68%,transparent 100%)}
.cine .cap{position:absolute;left:0;right:0;bottom:0;padding:var(--s5) 0 var(--s5)}
.cine .cap .wrap{display:flex;align-items:flex-end;justify-content:space-between;gap:var(--s4);flex-wrap:wrap}
.cine .cap .eyebrow{color:var(--tq-l)}
.cine .cap h3{font-size:clamp(1.7rem,3.4vw,2.7rem);color:var(--paper);max-width:18ch;margin-top:var(--s1)}
.cine .cap .mono{color:rgba(245,242,234,.55);font-family:var(--mono);font-size:12.5px;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap;padding-bottom:.4rem}
@media(max-width:640px){.cine{height:64vh;min-height:380px}.cine .cap .wrap{align-items:flex-start}}
.demo-band{background:var(--ink);color:var(--paper);position:relative;overflow:hidden;padding:var(--s6) 0 var(--s7)}
.demo-grid{display:grid;grid-template-columns:1fr auto;gap:var(--s6);align-items:center}
@media(max-width:900px){.demo-grid{grid-template-columns:1fr;justify-items:center;text-align:center}}
.demo-copy .eyebrow{color:var(--tq-l)}
.demo-copy h2{font-size:clamp(2.1rem,4.6vw,3.6rem);color:var(--paper);margin-top:var(--s2);max-width:14ch}
.demo-copy p{color:rgba(245,242,234,.7);font-size:1.1rem;margin-top:var(--s3);max-width:42ch;line-height:1.5}

.phone{position:relative;width:280px;max-width:78vw;aspect-ratio:280/560;background:#050807;border-radius:34px;padding:10px;box-shadow:0 40px 90px -30px rgba(0,0,0,.7);opacity:0;transform:translateY(30px) scale(1.02);animation:demoIn 1s .2s var(--ease) forwards}
@keyframes demoIn{to{opacity:1;transform:none}}
.phone .notch{position:absolute;top:10px;left:50%;transform:translateX(-50%);width:84px;height:18px;background:#050807;border-radius:0 0 12px 12px;z-index:5}
.screen{position:relative;width:100%;height:100%;border-radius:26px;overflow:hidden;background:var(--paper);display:flex;flex-direction:column}
.sc-head{padding:22px 14px 10px;display:flex;align-items:center;gap:8px;color:#0A2E38;background:#fff}
.sc-head .ic{width:26px;height:26px;border-radius:8px;background:var(--ink);color:#fff;display:grid;place-items:center;font-family:var(--disp);font-weight:800;font-size:12px}
.sc-head .nm{font-family:var(--disp);font-weight:700;font-size:13px;text-align:left;color:#0A2E38}
.sc-head .sb{font-size:8.5px;color:#7d9ea7;font-family:var(--mono)}
.sc-body{flex:1;padding:11px;overflow:hidden}
.mcard{position:relative;border-radius:16px;overflow:hidden;color:#fff;padding:15px;background:linear-gradient(155deg,#0A2E38,#0A7A88 130%);cursor:pointer;user-select:none;-webkit-tap-highlight-color:transparent;transition:transform .12s}
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
.hprog i{display:block;height:100%;width:0;border-radius:99px;background:var(--tq-l);transition:width .5s var(--ease)}
.sc-rew{margin-top:11px}
.sc-rew .r{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:#fff;border:1px solid #D6EEF1;margin-bottom:6px;transition:border-color .3s}
.sc-rew .r .p{width:30px;height:30px;border-radius:8px;background:#E7F8FA;color:#0A7A88;display:grid;place-items:center;font-family:var(--mono);font-weight:700;font-size:11px;flex-shrink:0}
.sc-rew .r.ok .p{background:linear-gradient(140deg,var(--gold-l),var(--gold));color:#3a2a05}
.sc-rew .r .t{font-size:10.5px;font-weight:600;color:#0A2E38}
.sc-rew .r .st{margin-left:auto;font-size:8.5px;font-weight:700;color:#7d9ea7}
.sc-rew .r.ok .st{color:var(--gold);font-weight:800}
.sc-rew .r.pop{animation:rwpop .55s cubic-bezier(.2,1.5,.4,1)}
@keyframes rwpop{0%{transform:scale(1)}30%{transform:scale(1.05)}100%{transform:scale(1)}}
#confetti{position:absolute;inset:0;z-index:4;pointer-events:none}
.coin{position:absolute;z-index:6;width:30px;height:30px;border-radius:50%;background:radial-gradient(circle at 35% 30%,var(--gold-l),var(--gold));color:#3a2a05;display:grid;place-items:center;font-family:var(--disp);font-weight:800;font-size:12px;pointer-events:none;opacity:0}
@keyframes rise{0%{opacity:0;transform:translateY(0) scale(.5)}20%{opacity:1}100%{opacity:0;transform:translateY(-160px) scale(1)}}
.tapwrap{position:relative;z-index:5;margin-top:var(--s3);display:flex;flex-direction:column;align-items:center;gap:.5rem}
.tapcta{background:transparent;color:var(--paper);font-family:var(--body);font-weight:700;font-size:.85rem;padding:.7rem 1.2rem;border-radius:999px;border:1.5px solid rgba(245,242,234,.4);cursor:pointer;transition:border-color .2s,background .2s}
.tapcta:hover{border-color:var(--paper);background:rgba(245,242,234,.08)}
.taphint{color:rgba(245,242,234,.6);font-size:.75rem;font-family:var(--mono);min-height:16px}
@media(prefers-reduced-motion:reduce){.tapcta,.hprog i,.mc-v.bump,.phone{animation:none!important;opacity:1!important;transform:none!important}#confetti{display:none}}

/* ticker kinétique — grand, rapide, assumé */
.ticker{background:var(--paper);border-bottom:1px solid var(--line);padding:var(--s3) 0;overflow:hidden;white-space:nowrap}
.ticker .run{display:inline-block;animation:scrollx 22s linear infinite;font-family:var(--disp);font-weight:700;font-size:clamp(1.6rem,4vw,3rem);letter-spacing:-.01em;color:var(--line)}
.ticker .run span{margin:0 var(--s4);-webkit-text-stroke:1.5px var(--text);color:transparent}
.ticker .run span.on{-webkit-text-stroke:0;color:var(--tq-d)}
@keyframes scrollx{from{transform:translateX(0)}to{transform:translateX(-50%)}}
@media(prefers-reduced-motion:reduce){.ticker .run{animation:none}}

section.band{padding:var(--s7) 0}
.head{max-width:680px}
.head h2{font-size:clamp(2.2rem,5vw,3.8rem);margin-top:var(--s2)}
.head p{color:var(--muted);font-size:1.15rem;margin-top:var(--s3);line-height:1.5}

/* ============================================================
   LE VRAI PROBLÈME — asymétrique, une pull-quote au centre.
   ============================================================ */
.cmp{display:grid;grid-template-columns:1fr 1px 1fr;gap:var(--s5);max-width:1040px}
@media(max-width:760px){.cmp{grid-template-columns:1fr}.cmp .rule-v{display:none}}
.rule-v{background:var(--line)}
.cmp-card h3{font-size:.85rem;font-weight:700;font-family:var(--mono);text-transform:uppercase;letter-spacing:.06em}
.cmp-sans h3{color:var(--faint)}.cmp-avec h3{color:var(--tq-d)}
.cmp-card ul{list-style:none;padding:0;margin:var(--s3) 0 0;display:flex;flex-direction:column;gap:var(--s3)}
.cmp-card li{display:flex;gap:.7rem;font-size:1.15rem;line-height:1.4}.cmp-card li svg{width:20px;height:20px;flex-shrink:0;margin-top:3px}
.cmp-sans li{color:var(--muted);text-decoration:line-through;text-decoration-color:var(--line);text-decoration-thickness:2px}
.cmp-avec li{color:var(--text);font-weight:600}
.check{color:var(--tq-d)}.cross{color:var(--faint)}

.pullquote{margin:var(--s7) auto 0;max-width:1040px;font-family:var(--disp);font-style:italic;font-weight:400;font-size:clamp(1.6rem,4.2vw,3.1rem);line-height:1.2;color:var(--ink);padding-left:var(--s4);border-left:6px solid var(--coral)}
.pullquote b{font-style:normal;font-weight:700;color:var(--coral-d)}

/* ============================================================
   COMMENT ÇA MARCHE — numéros énormes, liste éditoriale.
   ============================================================ */
.how{display:grid;grid-template-columns:.55fr 1fr;gap:var(--s6)}
@media(max-width:860px){.how{grid-template-columns:1fr;gap:var(--s5)}}
.steps{display:flex;flex-direction:column}
.step{display:grid;grid-template-columns:110px 1fr;gap:var(--s3);padding:var(--s5) 0;border-top:1px solid var(--line)}
.steps .step:last-child{border-bottom:1px solid var(--line)}
.step .no{font-family:var(--disp);font-size:clamp(2.4rem,5vw,4rem);font-weight:800;color:var(--line);line-height:1}
.step h3{font-size:1.5rem}.step p{color:var(--muted);font-size:1.05rem;margin-top:.5rem;line-height:1.5;max-width:44ch}

/* ROI */
.roi{max-width:1100px;display:grid;grid-template-columns:1fr 1fr;gap:var(--s6);align-items:center}
@media(max-width:760px){.roi{grid-template-columns:1fr;gap:var(--s4)}}
.roi-c label{display:flex;justify-content:space-between;font-size:.85rem;font-weight:600;color:var(--text);margin:var(--s3) 0 .4rem}.roi-c label:first-child{margin-top:0}
.roi-c label b{font-family:var(--mono);color:var(--tq-d);font-weight:600}
.roi-c input[type=range]{width:100%;accent-color:var(--ink);height:2px}
.roi-out{background:var(--ink);color:var(--paper);border-radius:24px;padding:var(--s5)}
.roi-out .k{font-size:.78rem;font-family:var(--mono);letter-spacing:.08em;text-transform:uppercase;color:rgba(245,242,234,.6)}
.roi-out .big{font-family:var(--disp);font-weight:800;font-size:3.6rem;line-height:1;margin:.5rem 0 .2rem;color:var(--tq-l)}
.roi-out .cur{color:rgba(245,242,234,.7);font-size:.95rem}.roi-out hr{border:0;border-top:1px solid rgba(245,242,234,.2);margin:var(--s3) 0}
.roi-out .l{display:flex;justify-content:space-between;font-size:.95rem;margin-bottom:.55rem}.roi-out .l span{color:rgba(245,242,234,.65)}.roi-out .l b{font-family:var(--mono);font-weight:600}

/* CHIFFRES — plein papier, numéros géants, règles fines */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr)}
@media(max-width:640px){.stats-grid{grid-template-columns:1fr 1fr}}
.stats-grid>div{padding:var(--s4) var(--s3);border-left:1px solid var(--line)}
.stats-grid>div:first-child{border-left:0}
@media(max-width:640px){.stats-grid>div:nth-child(2n+1){border-left:0}.stats-grid>div{border-top:1px solid var(--line)}.stats-grid>div:nth-child(-n+2){border-top:0}}
.stats-grid .n{font-family:var(--disp);font-weight:800;font-size:clamp(2.4rem,5.6vw,4.2rem);line-height:1;color:var(--ink)}
.stats-grid .n em{font-style:normal;color:var(--tq-d)}
.stats-grid .k{color:var(--muted);font-size:.9rem;margin-top:.7rem;line-height:1.4}
.cite{margin-top:var(--s5);color:var(--faint);font-size:.9rem;max-width:640px}.cite b{color:var(--coral-d);font-weight:600}

/* RÉCOMPENSES */
.cascade{max-width:640px}
.crow{display:flex;align-items:baseline;gap:var(--s3);padding:var(--s4) 0;border-top:1px solid var(--line)}
.cascade .crow:last-child{border-bottom:1px solid var(--line)}
.cnode{font-family:var(--disp);font-weight:800;color:var(--tq-d);font-size:1.8rem;width:74px;flex-shrink:0}
.cnode small{display:block;font-size:.55rem;letter-spacing:.1em;color:var(--faint);font-weight:400;font-family:var(--mono)}
.cinfo h4{font-size:1.3rem;font-weight:700}.cinfo p{color:var(--muted);font-size:1rem;margin-top:.3rem}

/* PRIX */
.pricing{display:grid;grid-template-columns:1.15fr .85fr;gap:var(--s6);align-items:start}
@media(max-width:860px){.pricing{grid-template-columns:1fr;gap:var(--s5)}}
.feat{background:var(--ink);color:var(--paper);border-radius:28px;padding:var(--s5)}
.feat .fl{font-family:var(--mono);font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;color:var(--tq-l);font-weight:700}
.feat .pr{font-family:var(--disp);font-weight:800;font-size:4rem;line-height:1;margin:var(--s2) 0 .3rem;color:var(--paper)}
.feat .pr small{font-size:1rem;color:rgba(245,242,234,.6);font-weight:500}
.feat .u{color:rgba(245,242,234,.65);font-size:.95rem}
.feat ul{list-style:none;padding:0;margin:var(--s4) 0;display:flex;flex-direction:column;gap:.8rem}
.feat li{display:flex;gap:.6rem;font-size:1rem;color:rgba(245,242,234,.92)}.feat li svg{width:18px;height:18px;flex-shrink:0;color:var(--tq-l);margin-top:2px}
.plist{display:flex;flex-direction:column}
.plan{display:flex;align-items:baseline;justify-content:space-between;gap:var(--s2);padding:var(--s3) 0;border-top:1px solid var(--line)}
.plist .plan:last-child{border-bottom:1px solid var(--line)}
.plan .nm{font-weight:700;font-size:1.05rem}.plan .u{color:var(--faint);font-size:.8rem;margin-top:.15rem}
.plan .pr{font-family:var(--mono);font-weight:600;font-size:1.05rem;white-space:nowrap}
.plan a{color:var(--tq-d);font-weight:700;font-size:.85rem;white-space:nowrap}
.plans-more{margin-top:var(--s3)}
.plans-more a{color:var(--tq-d);font-weight:700;font-size:.95rem}

/* FAQ */
.faq{max-width:820px}
.faq details{border-top:1px solid var(--line);padding:var(--s3) 0}
.faq details:last-child{border-bottom:1px solid var(--line)}
.faq summary{list-style:none;cursor:pointer;font-family:var(--disp);font-weight:700;font-size:1.25rem;color:var(--text);display:flex;justify-content:space-between;align-items:center;gap:var(--s2)}
.faq summary::-webkit-details-marker{display:none}.faq summary .pl{color:var(--tq-d);font-size:1.6rem;transition:transform .2s;flex-shrink:0}
.faq details[open] summary .pl{transform:rotate(45deg)}.faq p{color:var(--muted);margin-top:.7rem;font-size:1.05rem;max-width:62ch;line-height:1.6}

/* CTA FINAL — plein-bleed encre, typographie monumentale */
.footcta{background:var(--ink);color:var(--paper);padding:var(--s7) 0}
.footcta h2{font-size:clamp(2.6rem,7vw,5.4rem);color:var(--paper);max-width:15ch}
.footcta p{color:rgba(245,242,234,.7);font-size:1.2rem;margin:var(--s3) 0 var(--s4);max-width:46ch}

footer.site{padding:var(--s5) 0 var(--s6);border-top:1px solid var(--line)}
.foot-grid{display:flex;justify-content:space-between;align-items:center;gap:var(--s3);flex-wrap:wrap;color:var(--muted);font-size:.9rem}
.foot-grid .brand{color:var(--text);font-family:var(--disp);font-weight:800;font-size:1.2rem}.foot-grid .brand .d{color:var(--tq-d)}

/* scroll-reveal + utilitaires */
#scrollbar{position:fixed;top:0;left:0;height:2px;width:0;z-index:200;background:var(--tq-d);transition:width .12s linear}
.reveal-ready .rv{opacity:0;transform:translateY(50px);transition:opacity .9s var(--ease),transform .9s var(--ease);transition-delay:var(--d,0s)}
.reveal-ready .rv.in{opacity:1;transform:none}
.up{position:fixed;right:20px;bottom:20px;z-index:150;width:46px;height:46px;border-radius:14px;background:var(--ink);color:var(--paper);display:grid;place-items:center;opacity:0;transform:translateY(18px);pointer-events:none;transition:opacity .25s,transform .25s,background .2s}
.up.on{opacity:1;transform:none;pointer-events:auto}
.up:hover{background:var(--tq-d)}.up svg{width:20px;height:20px}
@media(prefers-reduced-motion:reduce){.reveal-ready .rv{opacity:1!important;transform:none!important}#scrollbar{display:none}.up{transition:none}}
.flogo{width:1.35em;height:1.35em;display:inline-block;vertical-align:-.32em;margin-right:.34em}
</style></head><body>
<div id="scrollbar"></div>

<?php nav_bar($base, 'accueil'); ?>

<main>
<!-- HERO — typographie monumentale, aucune photo, aucun mockup en vedette -->
<section class="hero"><div class="wrap">
  <div class="kicker"><span class="eyebrow">Programme de fidélité</span><span class="n mono">N° 001 / 2026</span></div>
  <h1 id="h1">
    <span class="ln"><span>Vos clients</span></span>
    <span class="ln"><span><em>reviennent</em>.</span></span>
    <span class="ln"><span>Sans carte, sans appli.</span></span>
  </h1>
  <div class="hero-sub">
    <p class="lead">Le client montre son code, vous le scannez, le point est ajouté. C'est tout. Fidelo transforme un passage en habitude — et une habitude en chiffre d'affaires.</p>
    <div class="cta-col">
      <a href="<?= e($base) ?>/inscription.php" class="btn btn-p">Commencer gratuitement</a>
      <a href="#comment" class="btn-line">Voir comment ça marche →</a>
      <div class="proof"><span><b>0 €</b> pour démarrer</span><span><b>5 min</b> d'installation</span><span><b>0</b> matériel à acheter</span></div>
    </div>
  </div>
</div></section>

<!-- ticker kinétique -->
<div class="ticker"><div class="run" id="marq"></div></div>

<!-- MOMENT — bandeau cinématique plein-bleed, tournage réel du geste -->
<section class="cine rv" id="cine">
  <video id="cineVid" muted loop playsinline preload="none" poster="<?= e($base) ?>/media/hero-cinematic-poster.jpg" data-src="<?= e($base) ?>/media/hero-cinematic.mp4" aria-label="Un commerçant fait scanner la carte de fidélité Fidelo sur le téléphone d'un client, comptoir d'un commerce, lumière du matin."></video>
  <div class="scrim"></div>
  <div class="cap"><div class="wrap">
    <div><span class="eyebrow">Le geste, en vrai</span><h3>Un scan. Un point. Un client qui revient.</h3></div>
    <span class="mono">huit secondes · aucune appli</span>
  </div></div>
</section>

<!-- DÉMO — bandeau plein-bleed encre, carte jouable -->
<section class="demo-band"><div class="wrap demo-grid">
  <div class="demo-copy">
    <span class="eyebrow">À essayer maintenant</span>
    <h2>Touchez la carte.<br>Regardez ce qui se passe.</h2>
    <p>Chaque scan ajoute un point, chaque palier ouvre une récompense. C'est exactement ce que verra votre client — en vrai, à votre caisse.</p>
  </div>
  <div id="stage">
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
      <span class="taphint" id="tapHint">Comme le fera votre commerçant.</span>
    </div>
  </div>
</div></section>

<!-- LE PROBLÈME -->
<section class="band"><div class="wrap">
  <div class="head rv"><span class="eyebrow">Le vrai problème</span><h2>Attirer un client coûte cher.<br>Le garder, presque rien.</h2></div>
  <div class="cmp" style="margin-top:var(--s5)">
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
  <p class="pullquote rv">Vous dépensez tout en publicité pour <b>attirer</b> — puis vous laissez partir. Fidelo travaille l'autre côté.</p>
</div></section>

<!-- COMMENT -->
<section class="band" id="comment" style="background:var(--card)"><div class="wrap how">
  <div class="head rv" style="max-width:none"><span class="eyebrow">Scan &amp; Point</span><h2>Trois secondes<br>à la caisse.</h2><p>Pas de matériel, pas de formation. Votre téléphone suffit.</p></div>
  <div class="steps">
    <div class="step rv"><span class="no mono">01</span><div><h3>Le client montre son code</h3><p>Reçu par lien ou ajouté à son téléphone. Aucune appli à télécharger.</p></div></div>
    <div class="step rv"><span class="no mono">02</span><div><h3>Vous scannez</h3><p>Depuis votre espace Fidelo. C'est le commerçant qui scanne — impossible de tricher.</p></div></div>
    <div class="step rv"><span class="no mono">03</span><div><h3>Le point s'ajoute, la récompense arrive</h3><p>Le client voit son compteur monter et débloque un café, un dessert, une remise.</p></div></div>
  </div>
</div></section>

<!-- ROI -->
<section class="band" id="gains"><div class="wrap">
  <div class="head rv"><span class="eyebrow">Calculez vos gains</span><h2>Combien la fidélité vous rapporte&nbsp;?</h2><p>Bougez les curseurs avec vos vrais chiffres.</p></div>
  <div class="roi rv" style="margin-top:var(--s5)">
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
<section class="band" style="background:var(--card);border-top:1px solid var(--line);border-bottom:1px solid var(--line)"><div class="wrap">
  <div class="head rv" style="margin-bottom:var(--s5)"><span class="eyebrow">Ce que change la fidélité</span><h2>Les habitués font tourner le commerce.</h2></div>
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
  <div class="head rv"><span class="eyebrow">Menu en cascade</span><h2>Une échelle de récompenses,<br>pas un seul palier.</h2></div>
  <div class="cascade" style="margin-top:var(--s5)">
    <div class="crow rv"><div class="cnode mono">3<small>PTS</small></div><div class="cinfo"><h4>☕ Boisson offerte</h4><p>Le premier palier — il déclenche l'habitude.</p></div></div>
    <div class="crow rv"><div class="cnode mono">6<small>PTS</small></div><div class="cinfo"><h4>🍰 Dessert maison</h4><p>La récompense « plaisir » qui fait parler.</p></div></div>
    <div class="crow rv"><div class="cnode mono">10<small>PTS</small></div><div class="cinfo"><h4>💸 −15 % sur l'addition</h4><p>Un vrai geste pour le régulier.</p></div></div>
    <div class="crow rv"><div class="cnode mono">20<small>PTS</small></div><div class="cinfo"><h4>👑 Menu offert · VIP</h4><p>Le sommet : votre meilleur client se sent reconnu.</p></div></div>
  </div>
</div></section>

<!-- PRIX -->
<section class="band" id="prix" style="background:var(--card)"><div class="wrap">
  <div class="head rv"><span class="eyebrow">Nos offres</span><h2>Commencez gratuitement.</h2><p>Jusqu'à 30 clients sans rien payer, sans limite de durée.</p></div>
  <div class="pricing" style="margin-top:var(--s5)">
    <div class="feat rv">
      <span class="fl">Le plus choisi · Annuel</span>
      <div class="pr">250 €<small> / an</small></div>
      <div class="u">soit 20,83 € / mois · clients illimités · prix bloqué 12 mois</div>
      <ul>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg> 28 % moins cher que le mensuel</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg> Clients, récompenses et messages illimités</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6 9 17l-5-5"/></svg> Google Wallet, export Excel, appareils multiples</li>
      </ul>
      <a href="<?= e($base) ?>/inscription.php" class="btn btn-p" style="background:var(--tq-l);color:var(--ink)">Commencer</a>
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
  <div class="faq" style="margin-top:var(--s5)">
    <details open class="rv"><summary>Mon client doit-il installer une application ? <span class="pl">+</span></summary><p>Non. Il reçoit son code de fidélité et vous le scannez depuis votre espace. Rien à télécharger côté client.</p></details>
    <details class="rv"><summary>Comment évite-t-on la triche ? <span class="pl">+</span></summary><p>C'est le commerçant qui scanne le code du client, jamais l'inverse. Un client ne peut pas s'ajouter de points.</p></details>
    <details class="rv"><summary>Faut-il acheter du matériel ? <span class="pl">+</span></summary><p>Non. Un simple smartphone ou tablette suffit. La caméra fait office de lecteur.</p></details>
    <details class="rv"><summary>Que se passe-t-il après les 10 jours ? <span class="pl">+</span></summary><p>Vous choisissez de continuer à 29 €/mois, ou d'arrêter. Sans engagement, aucune carte demandée pour l'essai.</p></details>
  </div>
</div></section>

<!-- CTA FINAL -->
<section class="footcta"><div class="wrap rv">
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
<script id="pageScript">
window.__fideloPageInit=function(){
const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)];
const reduce=matchMedia('(prefers-reduced-motion:reduce)').matches;
const teardown=[];
window.__fideloPageTeardown=function(){teardown.forEach(fn=>{try{fn();}catch(e){}});teardown.length=0;};

$$('#h1 .ln span').forEach((w,i)=>w.style.animationDelay=(0.1+i*0.12)+'s');
try{const q=qrcode(0,'M');q.addData('FIDELO:DEMO12');q.make();$('#hqr').innerHTML=q.createSvgTag(2,0);const s=$('#hqr svg');if(s){s.setAttribute('width','48');s.setAttribute('height','48');}}catch(e){}
const stage=$('#stage');

/* ===== carte de fidélité JOUABLE ===== */
(function(){
  const card=$('#hcard'),ptsEl=$('#hpts'),ptsWrap=$('#hptsWrap'),fill=$('#hgFill'),
        gCount=$('#hgCount'),gLabel=$('#hgLabel'),tier=$('#htier'),since=$('#hsince'),
        rows=$$('#hrew .r'),tapBtn=$('#tapBtn'),tapHint=$('#tapHint'),canvas=$('#confetti');
  if(!card||!canvas)return;
  const rewards=rows.map(r=>({pts:+r.dataset.pts,el:r}));
  const maxPts=rewards[rewards.length-1].pts;
  let points=0,visits=14,busy=false;

  const ctx=canvas.getContext('2d');let parts=[],raf=null;
  function resize(){canvas.width=card.clientWidth;canvas.height=card.clientHeight;}
  resize();addEventListener('resize',resize);
  teardown.push(()=>{removeEventListener('resize',resize);if(raf)cancelAnimationFrame(raf);});
  const COLORS=['#E4C583','#3FC6D6','#C99A3E','#9EE8F0'];
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
    const stopAuto=setTimeout(()=>clearInterval(auto),4500);
    teardown.push(()=>{clearInterval(auto);clearTimeout(stopAuto);});
  }
})();

// ticker — moitié des mots en accent pour l'effet kinétique
const items=['Café','Restaurant','Salon','Boulangerie','Bar à jus','Pizzeria','Glacier','Coiffeur','Fleuriste','Boucherie'];
if($('#marq'))$('#marq').innerHTML=(items.map((i,idx)=>`<span class="${idx%3===0?'on':''}">${i}</span>`).join('')).repeat(2);

const fmt=n=>Math.round(n).toLocaleString('fr-FR')+' €';
function roi(){const c=+$('#rC').value,p=+$('#rP').value,t=+$('#rT').value/100,f=+$('#rF').value;
  $('#vC').textContent=c;$('#vP').textContent=p+' €';$('#vT').textContent=Math.round(t*100)+' %';$('#vF').textContent=f;
  const m=c*t*f*p;$('#rBig').textContent=fmt(m);$('#rYear').textContent=fmt(m*12);$('#rX').textContent='×'+Math.max(1,Math.round(m/29));}
if($('#rC')){['rC','rP','rT','rF'].forEach(id=>$('#'+id).oninput=roi);roi();}
function countUp(){$$('[data-c]').forEach(el=>{const target=+el.dataset.c,suf=el.innerHTML;if(reduce){el.innerHTML=suf.replace('0',target);return;}
    let v=0;const step=Math.max(1,target/28);const iv=setInterval(()=>{v+=step;if(v>=target){v=target;clearInterval(iv);}el.innerHTML=suf.replace(/0(?![^<]*>)/,Math.round(v));},30);});}
if($('.stats-grid')){
  const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){countUp();io.disconnect();}}),{threshold:.4});
  io.observe($('.stats-grid'));
  teardown.push(()=>io.disconnect());
}

/* ===== scroll-reveal + micro-interactions ===== */
document.body.classList.add('reveal-ready');
[['.steps .step'],['.stats-grid>div'],['.cascade .crow'],['.faq details'],['.plist .plan']]
  .forEach(([sel])=>$$(sel).forEach((el,i)=>el.style.setProperty('--d',(i*0.09)+'s')));

if(reduce){$$('.rv').forEach(el=>el.classList.add('in'));}
else{
  const rio=new IntersectionObserver(es=>es.forEach(e=>{
    if(e.isIntersecting){e.target.classList.add('in');rio.unobserve(e.target);}
  }),{threshold:.12,rootMargin:'0px 0px -6% 0px'});
  $$('.rv').forEach(el=>rio.observe(el));
  teardown.push(()=>rio.disconnect());
}

const demoEl=$('.phone');
if(demoEl)demoEl.addEventListener('animationend',()=>{
  demoEl.style.animation='none';demoEl.style.opacity='1';demoEl.style.transform='none';
},{once:true});

/* bandeau cinématique — charge la vidéo seulement à l'approche, joue en vue, coupe hors vue */
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
