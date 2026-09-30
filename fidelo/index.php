<?php require __DIR__ . '/lib.php'; fidelo_session(); $CSRF = csrf_token(); $BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<link rel="icon" href="<?= e($BASE) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($BASE) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($BASE) ?>/apple-touch-icon.png">
<title>Fidelo — Espace commerçant</title>
<meta name="theme-color" content="#0A2E38">
<link rel="manifest" href="<?= e($BASE) ?>/manifest.php">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Instrument+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap">
<script src="jsqr.min.js"></script>
<script src="qrcode.min.js"></script>
<style>
:root{--em:#06B6D4;--em-d:#0891B2;--em-l:#22D3EE;--or:#D9A94E;--or-l:#f0d488;--or-d:#bd8f38;
--iv:#F3FCFD;--me:#E7F8FA;--me-d:#D6EEF1;--card:#fff;--line:#D6EEF1;--text:#1c2a25;--muted:#5a675f;--faint:#8a958e;
--deep:#0A2E38;--deep2:#0F3D49;--good:#06B6D4;--warn:#c98a1e;--bad:#c0492f;--grid:#E2F2F4;
--disp:"Bricolage Grotesque",system-ui,sans-serif;--body:"Instrument Sans",system-ui,sans-serif;--mono:"IBM Plex Mono",monospace;}
@media(prefers-color-scheme:dark){:root{--em:#22D3EE;--em-d:#0891B2;--em-l:#67E8F9;--or:#e3ba63;--or-l:#f2d78f;--or-d:#c99f45;
--iv:#07222A;--me:#0B2C35;--me-d:#183B45;--card:#0E323C;--line:#1C4650;--text:#e6efea;--muted:#9fc4cc;--faint:#728178;--grid:#20342b;}}
*{box-sizing:border-box}body{margin:0;background:var(--iv);color:var(--text);font-family:var(--body);-webkit-font-smoothing:antialiased;overscroll-behavior:none}
h1,h2,h3,h4{font-family:var(--disp);font-weight:600;letter-spacing:-.02em;margin:0;line-height:1.12}
button{font-family:inherit;cursor:pointer;border:0;background:none;color:inherit}input{font-family:inherit}
[hidden]{display:none!important}.mono{font-family:var(--mono);font-variant-numeric:tabular-nums}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:13px 20px;border-radius:12px;font-weight:600;font-size:15px;transition:transform .12s,background .2s,border-color .2s}
.btn:active{transform:translateY(1px)}.btn-p{background:var(--em);color:#fff}.btn-p:hover{background:var(--em-d)}
.btn-g{background:var(--card);border:1.5px solid var(--line);color:var(--text)}.btn-gold{background:var(--or);color:#241a05}
.field{width:100%;padding:13px;border-radius:12px;border:1.5px solid var(--line);background:var(--card);color:var(--text);font-size:15px}
.field:focus{outline:none;border-color:var(--em)}

/* ===== LOCK ===== */
#lock{position:fixed;inset:0;z-index:50;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:26px;text-align:center;
background:radial-gradient(70% 45% at 78% 8%,rgba(6,182,212,.18),transparent 60%),radial-gradient(60% 40% at 15% 100%,rgba(227,186,99,.14),transparent 62%),linear-gradient(168deg,#0A2E38,#0B2C35 55%,#0A2E38);color:#eef7f2}
#lock .brand{position:absolute;top:22px;left:24px;font-family:var(--disp);font-weight:700;font-size:19px;color:#fff}#lock .brand i{color:var(--or);font-style:normal}
.lshop{display:flex;align-items:center;gap:10px;margin-bottom:26px}.lshop .ic{width:40px;height:40px;border-radius:12px;background:var(--em);color:#053642;display:grid;place-items:center;font-family:var(--disp);font-weight:700}
.lshop .nm{font-family:var(--disp);font-weight:600;font-size:16px;color:#fff}.lshop .sb{font-size:11px;color:rgba(238,247,242,.5);font-family:var(--mono);text-align:left}
.lview{display:none;flex-direction:column;align-items:center;width:100%;max-width:340px}.lview.on{display:flex}
.scanner{position:relative;width:150px;height:150px;display:grid;place-items:center;margin-bottom:22px}
.ring{position:absolute;inset:0;border-radius:50%;border:2px solid rgba(255,255,255,.13)}.ring.r2{inset:14px;border-color:rgba(6,182,212,.28)}.ring.r3{inset:28px;border-color:rgba(6,182,212,.15)}
.fico{width:66px;height:66px;color:var(--em-l);z-index:2}
.corner{position:absolute;width:30px;height:30px;border:3px solid var(--em-l);z-index:2}
.corner.tl{top:8px;left:8px;border-right:0;border-bottom:0;border-radius:11px 0 0 0}.corner.tr{top:8px;right:8px;border-left:0;border-bottom:0;border-radius:0 11px 0 0}
.corner.bl{bottom:8px;left:8px;border-right:0;border-top:0;border-radius:0 0 0 11px}.corner.br{bottom:8px;right:8px;border-left:0;border-top:0;border-radius:0 0 11px 0}
.scanbar{position:absolute;left:14px;right:14px;height:2px;background:var(--or);box-shadow:0 0 12px var(--or);z-index:3;opacity:0}
.scanner.scan .scanbar{opacity:1;animation:sweep 1.4s ease-in-out}.scanner.scan .ring{animation:pulse 1.1s ease-in-out infinite}
@keyframes sweep{0%{top:16px}50%{top:130px}100%{top:16px}}@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.04)}}
.scanner.ok{animation:okpop .4s cubic-bezier(.2,1.4,.4,1)}@keyframes okpop{0%{transform:scale(.9)}60%{transform:scale(1.06)}100%{transform:scale(1)}}
.lview h2{font-size:22px;color:#fff}.lview .hint{color:rgba(238,247,242,.66);font-size:14px;margin-top:8px}
.big-btn{margin-top:24px;width:100%;max-width:290px;padding:15px;border-radius:14px;background:var(--em);color:#053642;font-weight:700;font-size:15px}
.link{margin-top:15px;color:rgba(238,247,242,.7);font-size:13.5px;font-weight:600;text-decoration:underline;text-underline-offset:3px}
.dots{display:flex;gap:15px;margin:6px 0 24px}.dots i{width:14px;height:14px;border-radius:50%;border:2px solid rgba(255,255,255,.2)}
.dots i.f{background:var(--em-l);border-color:var(--em-l)}.dots.err i{border-color:var(--bad)}.dots.err{animation:shake .4s}
@keyframes shake{0%,100%{transform:translateX(0)}25%{transform:translateX(-8px)}75%{transform:translateX(8px)}}
.pad{display:grid;grid-template-columns:repeat(3,1fr);gap:13px;width:100%;max-width:280px}
.key{aspect-ratio:1;border-radius:50%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);font-family:var(--disp);font-size:25px;font-weight:600;color:#fff;display:grid;place-items:center;-webkit-tap-highlight-color:transparent}
.key:active{transform:scale(.94);background:var(--em);color:#053642}.key.fn{background:transparent;border:0}.key.fn svg{width:23px;height:23px;color:rgba(238,247,242,.75)}
.lform{width:100%;max-width:300px;text-align:left}.lform label{display:block;font-size:12.5px;font-weight:600;color:rgba(238,247,242,.7);margin:12px 0 6px}
.lform .field{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.14);color:#fff}
.attempts{margin-top:14px;font-size:12.5px;color:#e88068;min-height:16px}

/* ===== APP ===== */
#app{display:none;flex-direction:column;min-height:100vh;padding-bottom:78px}
#app.on{display:flex}
.ahead{padding:calc(16px + env(safe-area-inset-top)) 16px 14px;background:linear-gradient(160deg,var(--deep),var(--em-d) 140%);color:#eaf5f0;position:sticky;top:0;z-index:10}
.ahead .row{display:flex;align-items:center;gap:10px;max-width:900px;margin:0 auto}
.ahead .av{width:36px;height:36px;border-radius:11px;background:var(--or);color:#241a05;display:grid;place-items:center;font-family:var(--disp);font-weight:700}
.ahead .nm{font-family:var(--disp);font-weight:600;font-size:16px;color:#fff}.ahead .sb{font-size:11px;color:rgba(234,245,240,.6);font-family:var(--mono)}
.ahead .set{margin-left:auto;width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.1);display:grid;place-items:center}
.wrap{max-width:900px;margin:0 auto;width:100%;padding:14px 16px}
.screen{display:none;animation:fade .22s}.screen.on{display:block}@keyframes fade{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
.tiles{display:grid;grid-template-columns:1fr 1fr 1fr;gap:9px}.tile{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:12px 8px;text-align:center}
.tile .n{font-family:var(--mono);font-size:23px;font-weight:500;color:var(--text);line-height:1}.tile .k{font-size:10.5px;color:var(--muted);margin-top:5px}
.goalcard{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:13px;margin-top:10px}
.goalbar{height:9px;border-radius:99px;background:var(--me-d);overflow:hidden;margin-top:9px}.goalbar>i{display:block;height:100%;background:linear-gradient(90deg,var(--em),var(--em-l));border-radius:99px;transition:width .6s}
.sect{font-family:var(--disp);font-weight:600;font-size:15px;margin:18px 2px 10px;display:flex;justify-content:space-between;align-items:center}.sect a,.sect button{font-size:12.5px;color:var(--em);font-weight:600}
.cli{display:flex;align-items:center;gap:11px;padding:11px 12px;background:var(--card);border:1px solid var(--line);border-radius:12px;margin-bottom:8px;width:100%;text-align:left}
.cli .av{width:38px;height:38px;border-radius:11px;background:var(--me);color:var(--em-d);display:grid;place-items:center;font-weight:600;flex-shrink:0}
.cli .nm{font-weight:600;color:var(--text);font-size:14px}.cli .mt{font-size:11px;color:var(--faint);font-family:var(--mono)}
.cli .pts{margin-left:auto;text-align:right}.cli .pts b{font-family:var(--mono);font-size:16px;color:var(--em);font-weight:600}.cli .pts span{font-size:9px;color:var(--faint);display:block;letter-spacing:.05em}
.badge{background:var(--or);color:#241a05;font-size:9px;font-weight:700;padding:2px 6px;border-radius:6px}
/* scanner */
.cam-box{position:relative;height:56vh;min-height:340px;background:#07222A;border-radius:16px;overflow:hidden;display:grid;place-items:center}
.cam-box video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.cam-frame{position:relative;z-index:2;width:62%;aspect-ratio:1;border-radius:20px;box-shadow:0 0 0 100vmax rgba(11,21,18,.5)}
.cam-frame::before,.cam-frame::after{content:"";position:absolute;width:26px;height:26px;border:3px solid var(--or)}
.cam-frame::before{top:-3px;left:-3px;border-right:0;border-bottom:0;border-radius:8px 0 0 0}.cam-frame::after{bottom:-3px;right:-3px;border-left:0;border-top:0;border-radius:0 0 8px 0}
.cam-hint{position:absolute;z-index:3;bottom:12px;left:0;right:0;text-align:center;color:#eaf5f0;font-size:12px;font-family:var(--mono)}
.scan-actions{display:flex;flex-direction:column;gap:9px;margin-top:12px}
/* rewards edit */
.rw-e{display:flex;align-items:center;gap:10px;padding:10px;background:var(--card);border:1px solid var(--line);border-radius:12px;margin-bottom:8px}
.rw-e .p{width:60px}.rw-e .p .field{text-align:center;font-family:var(--mono);font-weight:600;padding:9px 4px}.rw-e .field{padding:9px 11px;font-size:14px}
.rw-e .m{flex:1}.rw-e .del{width:32px;height:32px;border-radius:8px;display:grid;place-items:center;color:var(--faint);border:1px solid var(--line)}
.addrw{width:100%;padding:11px;border-radius:12px;border:1.5px dashed var(--line);color:var(--em);font-weight:600;margin-top:2px}
/* stats */
.kpis{display:grid;grid-template-columns:1fr 1fr;gap:9px}.kpi{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:13px}
.kpi .k{font-size:11.5px;color:var(--muted)}.kpi .v{font-family:var(--disp);font-weight:700;font-size:24px;margin-top:6px}
.panel{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:15px;margin-top:12px}
.chart{position:relative}.chart svg{display:block;width:100%;height:auto;overflow:visible}
.segbar{display:flex;height:13px;border-radius:7px;overflow:hidden;gap:2px;margin:12px 0}
.seg-i{display:flex;align-items:center;gap:9px;font-size:13px;margin-bottom:8px}.seg-i .d{width:10px;height:10px;border-radius:3px}.seg-i .c{margin-left:auto;font-family:var(--mono);font-weight:600}
.reconq{background:linear-gradient(160deg,var(--deep),var(--deep2));color:#eef7f2;border-radius:16px;padding:16px;margin-top:12px}
.reconq h3{color:#fff;font-size:17px}.reconq .l{font-size:12.5px;color:rgba(238,247,242,.75);margin-top:4px}
.slp{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:10px;margin-top:8px}
.slp .av{width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.12);color:#fff;display:grid;place-items:center;font-weight:600;font-size:12px}
.slp .nm{font-weight:600;font-size:13px;color:#fff}.slp .mt{font-size:11px;color:rgba(238,247,242,.6);font-family:var(--mono)}
.slp .days{margin-left:auto;font-size:11px;font-weight:600;color:#ffb9a6;background:rgba(224,122,99,.16);padding:3px 8px;border-radius:99px}
.slp .rel{padding:8px 12px;border-radius:9px;background:var(--or);color:#241a05;font-weight:600;font-size:12px}.slp.done .rel{background:rgba(255,255,255,.12);color:rgba(238,247,242,.7);pointer-events:none}
/* nav */
.nav{position:fixed;bottom:0;left:0;right:0;z-index:20;display:grid;grid-template-columns:repeat(5,1fr);background:var(--card);border-top:1px solid var(--line);padding:6px 6px calc(6px + env(safe-area-inset-bottom));max-width:900px;margin:0 auto}
.nav button{display:flex;flex-direction:column;align-items:center;gap:3px;padding:5px;border-radius:10px;color:var(--faint);font-size:10px;font-weight:500}
.nav button svg{width:21px;height:21px}.nav button.on{color:var(--em)}
.nav .fab{position:relative;top:-12px}.nav .fab .i{width:46px;height:46px;border-radius:15px;background:var(--em);color:#fff;display:grid;place-items:center;box-shadow:0 8px 18px -6px rgba(14,131,103,.7)}.nav .fab svg{width:23px;height:23px}
/* sheet */
.sheet{position:fixed;inset:0;z-index:40;background:rgba(11,21,18,.45);display:none;align-items:flex-end}.sheet.on{display:flex;animation:fade .2s}
.sheet .card{width:100%;max-width:900px;margin:0 auto;background:var(--card);border-radius:22px 22px 0 0;padding:18px 16px calc(18px + env(safe-area-inset-bottom));animation:up .28s cubic-bezier(.2,.9,.3,1);max-height:88vh;overflow-y:auto;-webkit-overflow-scrolling:touch}
@keyframes up{from{transform:translateY(30px)}to{transform:none}}
.qr-box{background:#fff;padding:12px;border-radius:14px;width:fit-content;margin:6px auto 0}.qr-box img{width:180px;height:180px;image-rendering:pixelated;display:block}
.rwlist{max-height:38vh;overflow-y:auto}
.rwl{display:flex;align-items:center;gap:11px;padding:10px 12px;background:var(--card);border:1px solid var(--line);border-radius:12px;margin-bottom:8px}
.rwl.ok{border-color:var(--or);background:color-mix(in srgb,var(--or) 9%,var(--card))}.rwl.lock{opacity:.55}
.rwl .rp{width:44px;height:44px;border-radius:12px;flex-shrink:0;display:grid;place-items:center;flex-direction:column;line-height:1;font-family:var(--mono);font-weight:600;font-size:15px;background:var(--me);color:var(--em-d)}
.rwl.ok .rp{background:linear-gradient(140deg,var(--or-l),var(--or));color:#3a2a05}.rwl .rp small{font-size:7.5px;letter-spacing:.05em;margin-top:2px;opacity:.7}
.rwl .rt{font-weight:600;font-size:13.5px;color:var(--text)}.rwl .rlk{font-size:11.5px;color:var(--faint);font-family:var(--mono);flex-shrink:0}
.ptA{position:fixed;inset:0;z-index:60;display:none;place-items:center;background:rgba(11,21,18,.55)}.ptA.on{display:grid}
.coin{width:120px;height:120px;border-radius:50%;background:radial-gradient(circle at 35% 30%,#f0d488,var(--or) 60%,var(--or-d));display:grid;place-items:center;color:#241a05;font-family:var(--disp);font-weight:700;font-size:34px;box-shadow:0 0 0 10px rgba(217,169,78,.25);animation:okpop .5s cubic-bezier(.2,1.4,.4,1)}
.toast{position:fixed;left:50%;bottom:90px;transform:translateX(-50%) translateY(16px);background:var(--deep);color:#eef7f2;padding:12px 18px;border-radius:12px;font-size:14px;font-weight:500;z-index:100;opacity:0;transition:.3s;pointer-events:none;max-width:90vw;text-align:center}.toast.on{opacity:1;transform:translateX(-50%) translateY(0)}
@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
.flogo{width:1.35em;height:1.35em;display:inline-block;vertical-align:-.32em;margin-right:.32em}
</style></head><body>

<!-- ===================== LOCK ===================== -->
<div id="lock">
  <div class="brand"><svg class="flogo" viewBox="0 0 64 64" aria-hidden="true"><defs><linearGradient id="ixa" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#06B6D4"/><stop offset=".55" stop-color="#22D3EE"/><stop offset="1" stop-color="#FF6B6B"/></linearGradient><linearGradient id="ixb" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".3"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#ixa)"/><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#ixb)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>Fidelo<i>.</i></div>
  <div class="lshop"><div class="ic" id="lIc">·</div><div><div class="nm" id="lShop">Fidelo</div><div class="sb">ESPACE COMMERÇANT</div></div></div>

  <div class="lview" id="lFace">
    <div class="scanner" id="scanner">
      <div class="ring"></div><div class="ring r2"></div><div class="ring r3"></div>
      <span class="corner tl"></span><span class="corner tr"></span><span class="corner bl"></span><span class="corner br"></span><div class="scanbar"></div>
      <svg class="fico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 8V6a2 2 0 0 1 2-2h2M16 4h2a2 2 0 0 1 2 2v2M20 16v2a2 2 0 0 1-2 2h-2M8 20H6a2 2 0 0 1-2-2v-2"/><circle cx="9" cy="11" r=".6" fill="currentColor"/><circle cx="15" cy="11" r=".6" fill="currentColor"/><path d="M9.4 15.2s1 1 2.6 1 2.6-1 2.6-1"/></svg>
    </div>
    <h2>Déverrouillage par le visage</h2><p class="hint">Regardez votre écran pour ouvrir votre espace.</p>
    <button class="big-btn" id="faceBtn">Déverrouiller</button>
    <button class="link" id="toPin">Utiliser le code à 4 chiffres</button>
  </div>

  <div class="lview" id="lPin">
    <h2 style="margin-bottom:18px">Entrez votre code</h2>
    <div class="dots" id="dots"><i></i><i></i><i></i><i></i></div>
    <div class="pad" id="pad">
      <button class="key">1</button><button class="key">2</button><button class="key">3</button>
      <button class="key">4</button><button class="key">5</button><button class="key">6</button>
      <button class="key">7</button><button class="key">8</button><button class="key">9</button>
      <button class="key fn" id="kFace"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 8V6a2 2 0 0 1 2-2h2M16 4h2a2 2 0 0 1 2 2v2M20 16v2a2 2 0 0 1-2 2h-2M8 20H6a2 2 0 0 1-2-2v-2"/><circle cx="9" cy="11" r=".7" fill="currentColor"/><circle cx="15" cy="11" r=".7" fill="currentColor"/></svg></button>
      <button class="key">0</button>
      <button class="key fn" id="kDel"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M21 5H8L2 12l6 7h13a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1z"/><path d="m16 9-6 6M10 9l6 6"/></svg></button>
    </div>
    <div class="attempts" id="pinMsg"></div>
    <button class="link" id="toPass">Se connecter par mot de passe</button>
  </div>

  <div class="lview" id="lPass">
    <h2>Connexion</h2><p class="hint">Entrez vos identifiants de commerçant.</p>
    <div class="lform">
      <label>E-mail</label><input class="field" id="pMail" type="email" autocomplete="username" placeholder="vous@exemple.fr">
      <label>Mot de passe</label><input class="field" id="pPass" type="password" autocomplete="current-password" placeholder="••••••••">
    </div>
    <button class="big-btn" id="passBtn">Se connecter</button>
    <div class="attempts" id="passMsg"></div>
    <a class="link" href="<?= e($BASE) ?>/inscription.php">Créer un compte commerçant</a>
    <div style="margin-top:10px;font-size:12px;color:rgba(238,247,242,.5)">Mot de passe oublié ? Contactez votre fournisseur Fidelo.</div>
  </div>
</div>

<!-- ===================== APP ===================== -->
<div id="app">
  <div class="ahead"><div class="row">
    <div class="av" id="hIc">·</div>
    <div style="flex:1"><div class="nm" id="hShop">Fidelo</div><div class="sb">Espace commerçant <span id="qBadge" style="color:var(--or-l);font-weight:600"></span></div></div>
    <button class="set" id="btnInbox" aria-label="Messages de mes clients" title="Messages" style="margin-right:8px;position:relative"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#eaf5f0" stroke-width="2"><path d="M4 4h16v16H4z" opacity="0"/><path d="M21 8V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h9"/><path d="M3 7l9 6 9-6"/><circle cx="19" cy="17" r="4" fill="#E85D4B" stroke="none" style="display:none" id="inboxDot"/></svg></button>
    <button class="set" id="btnInstall" aria-label="Installer l'app" title="Ajouter à l'écran d'accueil" style="margin-right:8px"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#eaf5f0" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg></button>
    <button class="set" id="btnSet" aria-label="Réglages"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#eaf5f0" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 1 1-4 0v-.1A1.6 1.6 0 0 0 6.6 19.4l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0-1.1-2.7H2a2 2 0 1 1 0-4h.1A1.6 1.6 0 0 0 4.6 7.4l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 1.8.3H9.4A1.6 1.6 0 0 0 10.5 3.6V3a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 2.7 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8V9.4a1.6 1.6 0 0 0 1.5 1.1H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1.5z"/></svg></button>
  </div></div>

  <div class="wrap">
    <!-- ACCUEIL -->
    <div class="screen on" data-s="home">
      <div class="tiles"><div class="tile"><div class="n" id="tPts">0</div><div class="k">points aujourd'hui</div></div>
        <div class="tile"><div class="n" id="tCli">0</div><div class="k">clients</div></div>
        <div class="tile"><div class="n" id="tRw">0</div><div class="k">récompenses</div></div></div>
      <div class="goalcard"><div style="display:flex;justify-content:space-between;font-size:12px"><span style="color:var(--muted)">Objectif du jour</span><span class="mono" id="goalTxt" style="color:var(--em);font-weight:600">0/25</span></div><div class="goalbar"><i id="goalFill" style="width:0"></i></div></div>
      <div class="sect">Derniers passages <button data-go="clients">Tous</button></div>
      <div id="feed"></div>
    </div>
    <!-- CLIENTS -->
    <div class="screen" data-s="clients">
      <input class="field" id="cQ" placeholder="Rechercher un client…">
      <div style="display:flex;gap:8px;margin-top:8px"><button class="btn btn-p" id="cJoin" style="flex:1">📲 QR d'inscription</button><button class="btn btn-g" id="cAdd" style="flex:1">+ Client</button></div>
      <button class="btn btn-g" id="cBlank" style="width:100%;margin-top:8px">🖨️ Imprimer des cartes vierges</button>
      <button class="btn btn-g" id="cBroadcast" style="width:100%;margin-top:8px">📣 Message groupé</button>
      <div id="cList" style="margin-top:12px"></div>
    </div>
    <!-- SCANNER -->
    <div class="screen" data-s="scan">
      <div class="cam-box"><video id="cam" playsinline muted></video><div class="cam-frame"></div><div class="cam-hint" id="camHint">Visez le code du client</div></div>
      <div class="scan-actions"><button class="btn btn-p" id="camBtn">Démarrer la caméra</button><button class="btn btn-gold" id="simBtn">Saisir un code manuellement</button></div>
    </div>
    <!-- CADEAUX -->
    <div class="screen" data-s="rw">
      <div class="sect">Menu de récompenses <button id="rwSave">Enregistrer</button></div>
      <div id="rwList"></div>
      <button class="addrw" id="rwAdd">+ Ajouter un palier</button>
    </div>
    <!-- STATS -->
    <div class="screen" data-s="stats">
      <div class="kpis"><div class="kpi"><div class="k">Clients actifs</div><div class="v" id="kActive">0</div></div><div class="kpi"><div class="k">Total clients</div><div class="v" id="kTotal">0</div></div></div>
      <div class="panel"><div style="font-family:var(--disp);font-weight:600;font-size:14px;margin-bottom:6px">Points donnés · 8 jours</div><div class="chart" id="chart"></div></div>
      <div class="panel"><div style="font-family:var(--disp);font-weight:600;font-size:14px">Vos clients</div><div class="segbar" id="segbar"></div><div id="seglist"></div></div>
      <div class="reconq"><h3>Réveillez vos clients endormis</h3><p class="l">Inactifs depuis 14 jours et plus. Relance gratuite (notification / WhatsApp).</p><div id="sleepers"></div></div>
      <div class="panel"><div style="font-family:var(--disp);font-weight:600;font-size:14px;margin-bottom:8px">Meilleurs clients</div><div id="top"></div></div>
    </div>
  </div>

  <div class="nav">
    <button class="on" data-nav="home"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10l9-7 9 7v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>Accueil</button>
    <button data-nav="clients"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>Clients</button>
    <button class="fab" data-nav="scan"><span class="i"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M7 12h10"/></svg></span>Scanner</button>
    <button data-nav="rw"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7A3.5 3.5 0 1 0 8.5 3.5C8.5 6 12 7 12 7z"/></svg>Cadeaux</button>
    <button data-nav="stats"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 14l3-3 3 3 4-5"/></svg>Stats</button>
  </div>

  <div class="sheet" id="sheet"><div class="card" id="sheetC"></div></div>
  <div class="ptA" id="ptA"><div class="coin">+1</div></div>

  <!-- Aide rapide : un souci ? contacter AK DEV directement sur WhatsApp. -->
  <a href="https://wa.me/33745929520?text=Bonjour%2C%20j%27ai%20besoin%20d%27aide%20avec%20mon%20espace%20Fidelo" target="_blank" rel="noopener" aria-label="Besoin d'aide ? Contactez-nous sur WhatsApp" title="Besoin d'aide ? WhatsApp"
     style="position:fixed;right:16px;bottom:calc(92px + env(safe-area-inset-bottom));z-index:25;width:48px;height:48px;border-radius:50%;background:#25D366;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 18px rgba(0,0,0,.35);text-decoration:none">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="#fff"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.85.5 3.58 1.35 5.05L2 22l5.25-1.37a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.45 9.9-9.91C21.96 6.45 17.5 2 12.04 2zm5.8 14.02c-.24.68-1.4 1.3-1.93 1.38-.5.08-1.12.11-1.8-.11-.42-.13-.96-.31-1.65-.6-2.9-1.25-4.79-4.17-4.94-4.36-.14-.2-1.18-1.57-1.18-3 0-1.42.75-2.12 1.02-2.41.26-.29.57-.36.76-.36h.55c.18 0 .42-.07.65.5.24.58.82 2 .89 2.14.07.14.12.31.02.5-.1.19-.15.31-.29.48-.14.17-.3.37-.43.5-.14.14-.29.29-.12.57.17.29.75 1.24 1.61 2 1.11.99 2.04 1.3 2.33 1.45.29.14.46.12.63-.07.17-.19.72-.84.92-1.13.19-.29.38-.24.65-.14.26.1 1.66.78 1.94.92.29.14.48.21.55.33.07.12.07.7-.17 1.38z"/></svg>
  </a>
</div>

<div class="toast" id="toast"></div>

<script>
const BASE=<?= json_encode($BASE) ?>;
let CSRF=<?= json_encode($CSRF) ?>;
const $=s=>document.querySelector(s),$$=s=>[...document.querySelectorAll(s)];
let SHOP=null;
const initials=n=>(n||'?').split(/\s+/).map(w=>w[0]).join('').slice(0,2).toUpperCase();
let tT;function toast(m){const t=$('#toast');t.textContent=m;t.classList.add('on');clearTimeout(tT);tT=setTimeout(()=>t.classList.remove('on'),2400);}

async function api(a,data={}){
  const b=new URLSearchParams({a,...data});
  const r=await fetch(BASE+'/api.php',{method:'POST',headers:{'X-CSRF':CSRF,'Content-Type':'application/x-www-form-urlencoded'},body:b});
  const j=await r.json().catch(()=>({ok:false,error:'net'}));
  if(j&&j.error==='expired'){toast(j.message||'Abonnement arrêté');}
  if(j.csrf)CSRF=j.csrf;
  if(j.error==='suspended'){showSuspended();}
  return j;
}
function showSuspended(){
  document.body.innerHTML='<div style="position:fixed;inset:0;display:grid;place-items:center;padding:30px;text-align:center;background:linear-gradient(168deg,#0A2E38,#0B2C35);color:#eef7f2;font-family:var(--body)">'
   +'<div><div style="font-size:46px">🔒</div><h2 style="font-family:var(--disp);color:#fff;margin-top:14px">Compte suspendu</h2>'
   +'<p style="color:rgba(238,247,242,.7);margin-top:10px;max-width:34ch">Votre accès Fidelo est temporairement suspendu. Contactez votre fournisseur pour le réactiver.</p></div></div>';
}
function qrURL(t,cell){const q=qrcode(0,'M');q.addData(t);q.make();return q.createDataURL(cell||5,6);}

/* ---------------- WebAuthn (visage / empreinte réels) ---------------- */
let WA_READY=false;
const b64uBuf=s=>{const p='='.repeat((4-s.length%4)%4);const b=atob((s+p).replace(/-/g,'+').replace(/_/g,'/'));return Uint8Array.from([...b].map(c=>c.charCodeAt(0)));};
const bufB64=b=>btoa(String.fromCharCode(...new Uint8Array(b)));
const bufB64u=b=>bufB64(b).replace(/\+/g,'-').replace(/\//g,'_').replace(/=+$/,'');
async function faceAuth(){
  if(!window.PublicKeyCredential)return false;
  const o=await api('wa_aopts');if(!o.ok)return false;
  try{
    const as=await navigator.credentials.get({publicKey:{challenge:b64uBuf(o.challenge),rpId:o.rpId,timeout:60000,
      userVerification:'required',allowCredentials:o.allow.map(c=>({type:'public-key',id:b64uBuf(c.id)}))}});
    const r=await api('wa_auth',{id:bufB64u(as.rawId),authData:bufB64(as.response.authenticatorData),
      clientData:bufB64(as.response.clientDataJSON),sig:bufB64(as.response.signature)});
    if(r.ok){SHOP=r.shop;$('#lock').style.display='none';enterApp();return true;}
  }catch(e){}
  return false;
}
async function offerFace(){
  try{ if(!window.PublicKeyCredential)return;
    if(!(await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable().catch(()=>false)))return;
    if(!confirm('Activer le déverrouillage par le visage / l\'empreinte sur cet appareil ?'))return;
    const o=await api('wa_ropts');if(!o.ok)return;
    const cred=await navigator.credentials.create({publicKey:{challenge:b64uBuf(o.challenge),rp:{id:o.rpId,name:'Fidelo'},
      user:{id:b64uBuf(o.userId),name:o.userName,displayName:o.userName},
      pubKeyCredParams:[{type:'public-key',alg:-7},{type:'public-key',alg:-257}],
      authenticatorSelection:{authenticatorAttachment:'platform',userVerification:'required'},timeout:60000,attestation:'none'}});
    const pub=cred.response.getPublicKey?cred.response.getPublicKey():null;
    if(!pub){toast('Biométrie non disponible ici');return;}
    const r=await api('wa_reg',{id:bufB64u(cred.rawId),pub:bufB64(pub),clientData:bufB64(cred.response.clientDataJSON)});
    toast(r.ok?'✅ Visage / empreinte activé':'Échec de l\'activation');
  }catch(e){/* annulé */}
}

/* ---------------- BOOT ---------------- */
(async()=>{
  const me=await api('me');
  if(me.csrf)CSRF=me.csrf; WA_READY=!!me.waReady;
  if(me.shop){$('#lShop').textContent=me.shop.name;$('#lIc').textContent=initials(me.shop.name);}
  if(me.auth){SHOP=me.shop;enterApp();}
  else{lockShow((me.deviceKnown||WA_READY)?'lFace':'lPass');if(me.deviceKnown||WA_READY)setTimeout(runFace,500);}
})();

/* ---------------- LOCK ---------------- */
function lockShow(id){$$('#lock .lview').forEach(v=>v.classList.toggle('on',v.id===id));$('#lock').style.display='flex';}
async function runFace(){
  const sc=$('#scanner');
  if(WA_READY){
    $('#faceHint').textContent='Authentification…';$('#faceBtn').disabled=true;sc.classList.add('scan');
    const ok=await faceAuth();sc.classList.remove('scan');$('#faceBtn').disabled=false;
    if(!ok){$('#faceHint').textContent='Échec du visage — utilisez votre code.';lockShow('lPin');pin='';renderDots();}
    return;
  }
  // aucune biométrie enregistrée sur cet appareil → animation puis code
  sc.classList.remove('ok');sc.classList.add('scan');$('#faceBtn').disabled=true;
  setTimeout(()=>{sc.classList.remove('scan');sc.classList.add('ok');
    setTimeout(()=>{lockShow('lPin');$('#faceBtn').disabled=false;pin='';renderDots();},420);},1500);}
$('#faceBtn').onclick=runFace;
$('#toPin').onclick=()=>lockShow('lPin');
$('#toPass').onclick=()=>lockShow('lPass');
$('#kFace').onclick=()=>{lockShow('lFace');};

let pin='';
function renderDots(){$$('#dots i').forEach((d,i)=>d.classList.toggle('f',i<pin.length));}
$('#pad').addEventListener('click',e=>{const k=e.target.closest('.key');if(!k||k.classList.contains('fn'))return;
  if(pin.length>=4)return;pin+=k.textContent.trim();renderDots();if(pin.length===4)setTimeout(submitPin,130);});
$('#kDel').onclick=()=>{pin=pin.slice(0,-1);renderDots();};
async function submitPin(){
  const r=await api('pin',{pin});
  if(r.ok){SHOP=r.shop;$('#lock').style.display='none';enterApp();return;}
  $('#dots').classList.add('err');
  $('#pinMsg').textContent = r.error==='locked'?'Trop d\'essais — connectez-vous par mot de passe.'
    : r.error==='nodevice'?'Appareil non reconnu — mot de passe requis.':'Code incorrect.';
  if(r.error==='locked'||r.error==='nodevice')setTimeout(()=>lockShow('lPass'),900);
  setTimeout(()=>{$('#dots').classList.remove('err');pin='';renderDots();},450);
}
$('#passBtn').onclick=async()=>{
  const email=$('#pMail').value.trim(),pass=$('#pPass').value;
  if(!email||!pass){$('#passMsg').textContent='Renseignez e-mail et mot de passe.';return;}
  $('#passBtn').disabled=true;
  const r=await api('login',{email,pass});$('#passBtn').disabled=false;
  if(r.ok){SHOP=r.shop;$('#lShop').textContent=r.shop.name;$('#lIc').textContent=initials(r.shop.name);$('#lock').style.display='none';enterApp();setTimeout(offerFace,900);}
  else $('#passMsg').textContent = r.error==='ratelimit'?'Trop de tentatives, réessayez plus tard.':'E-mail ou mot de passe incorrect.';
};

/* ---------------- APP ---------------- */
function enterApp(){
  $('#lock').style.display='none';        // toujours masquer le verrou en entrant
  $('#app').classList.add('on');
  $('#hShop').textContent=SHOP.name;$('#hIc').textContent=initials(SHOP.name);
  if(SHOP.alert)showAlertBanner(SHOP.alert);
  loadHome();renderRewards();qBadge();flushQueue();
}
/* Formule Découverte : on montre les places restantes, sans alarmisme. */
function showQuota(q,max){
  const old=$('#quotaBar'); if(old)old.remove();
  if(q===null||q===undefined)return;
  const pris=max-q, pct=Math.min(100,Math.round(pris/max*100));
  const plein=q<=0, bas=q<=5;
  const b=document.createElement('div'); b.id='quotaBar';
  b.style.cssText='background:var(--card);border:1px solid '+(plein?'#e4bcb2':'var(--line)')+
    ';border-radius:14px;padding:12px 14px;margin-bottom:12px';
  b.innerHTML=`<div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600">
      <span>${plein?'Offre Découverte complète':'Offre Découverte'}</span>
      <span style="color:${plein?'#c0492f':(bas?'#c98a1e':'var(--em)')}">${pris} / ${max} clients</span></div>
    <div style="height:7px;border-radius:99px;background:var(--me);margin-top:8px;overflow:hidden">
      <div style="height:100%;width:${pct}%;border-radius:99px;background:${plein?'#c0492f':'var(--em)'}"></div></div>
    <div style="font-size:12px;color:var(--muted);margin-top:8px">${plein
      ? 'Vos '+max+' clients restent actifs. Passez en illimité pour en inscrire d\'autres.'
      : 'Il vous reste <b>'+q+' place'+(q>1?'s':'')+'</b> dans l\'offre gratuite.'}</div>`;
  const home=$('.screen[data-s=home]');
  if(home)home.insertBefore(b,home.firstChild);
}
function showAlertBanner(kind){
  if($('#alertBanner'))return;
  let msg,couleur='#c98a1e';
  if(kind==='impaye'){msg='⚠️ Paiement en attente — régularisez pour continuer sans interruption.';couleur='#c0492f';}
  else if(kind==='trial_over'){msg='⏳ Votre essai gratuit est terminé — contactez-nous pour vous abonner.';couleur='#c0492f';}
  else if(kind.startsWith('trial_')){
    const j=parseInt(kind.slice(6),10);
    msg=j<=0?'⏳ Dernier jour d\'essai gratuit.'
      :'⏳ Il vous reste '+j+' jour'+(j>1?'s':'')+' d\'essai gratuit.';
  } else return;
  const b=document.createElement('div');b.id='alertBanner';
  b.style.cssText='background:'+couleur+';color:#fff;font-size:13px;font-weight:600;text-align:center;padding:9px 14px';
  b.textContent=msg;
  $('#app').insertBefore(b,$('#app').firstChild);
}
function nav(s){$$('.nav button').forEach(b=>b.classList.toggle('on',b.dataset.nav===s));
  $$('.screen').forEach(x=>x.classList.toggle('on',x.dataset.s===s));
  if(s!=='scan')stopCam();
  if(s==='clients')loadClients();if(s==='stats')loadStats();if(s==='home')loadHome();}
$$('.nav button').forEach(b=>b.onclick=()=>nav(b.dataset.nav));
$$('[data-go]').forEach(b=>b.onclick=()=>nav(b.dataset.go));

function cliRow(c){const rw=c.claim;return `<button class="cli" data-cid="${c.id}"><span class="av">${initials(c.name)}</span>
  <span style="flex:1;min-width:0"><span class="nm">${esc(c.name)}</span><br><span class="mt">${c.visits} visites</span></span>
  ${rw?'<span class="badge">CADEAU</span>':''}<span class="pts"><b>${c.points}</b><span>points</span></span></button>`;}
function esc(s){return (s||'').replace(/[<>&"]/g,m=>({'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[m]));}
function bindCli(root){$$('.cli[data-cid]',root||document).forEach(b=>b.onclick=()=>openClient(b.dataset.cid));}

async function loadHome(){
  const r=await api('home');if(!r.ok)return;
  $('#tPts').textContent=r.today.pts;$('#tCli').textContent=r.nClients;$('#tRw').textContent=r.today.rw;
  const g=Math.min(100,Math.round(r.today.pts/r.goal*100));$('#goalFill').style.width=g+'%';$('#goalTxt').textContent=r.today.pts+'/'+r.goal;
  $('#feed').innerHTML=r.feed.map(cliRow).join('')||'<div style="color:var(--faint);font-size:13px;text-align:center;padding:16px">Aucun passage pour l\'instant.</div>';
  bindCli($('#feed'));
  showQuota(r.quota, r.freeMax);
  const dot=$('#inboxDot'); if(dot) dot.style.display=r.inboxUnread?'block':'none';
}
async function openInbox(){
  sheet('<h3 style="font-size:18px">📨 Messages de vos clients</h3><div id="inboxList" style="margin-top:10px">Chargement…</div>');
  const r=await api('inbox');
  const dot=$('#inboxDot'); if(dot) dot.style.display='none';
  if(!r.ok){$('#inboxList').textContent='Erreur.';return;}
  $('#inboxList').innerHTML=r.messages.length?r.messages.map(m=>`<div style="padding:10px 0;border-top:1px solid var(--line)">
    <div style="display:flex;justify-content:space-between;gap:8px;font-size:12px;color:var(--muted)"><b style="color:var(--text)">${esc(m.name)}</b><span>${new Date(m.at*1000).toLocaleString('fr-FR',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'})}</span></div>
    <div style="font-size:14px;margin-top:4px">${esc(m.text)}</div></div>`).join('')
    :'<div style="color:var(--faint);font-size:13px;text-align:center;padding:16px">Aucun message pour l\'instant.</div>';
}
$('#btnInbox').onclick=openInbox;
async function loadClients(){const r=await api('clients',{q:$('#cQ').value});if(!r.ok)return;
  $('#cList').innerHTML=r.clients.map(cliRow).join('')||'<div style="color:var(--faint);font-size:13px;text-align:center;padding:16px">Aucun client.</div>';bindCli($('#cList'));}
$('#cQ').oninput=()=>loadClients();
function joinUrl(){return location.origin+BASE+'/rejoindre.php?s='+SHOP.join;}
$('#cJoin').onclick=()=>{
  sheet(`<h3 style="font-size:17px;text-align:center">Inscription des clients</h3>
    <p style="text-align:center;font-size:12.5px;color:var(--muted);margin-top:4px">Le client scanne ce code avec son appareil photo, entre son prénom, et reçoit sa carte.</p>
    <div class="qr-box"><img src="${qrURL(joinUrl(),5)}"></div>
    <div style="text-align:center;font-size:11.5px;color:var(--faint);margin-top:8px">Sans scanner : <b class="mono" style="color:var(--text)">${location.host}${BASE}/j/${SHOP.short}</b></div>
    <div style="display:flex;gap:8px;margin-top:12px">
      <a class="btn btn-p" style="flex:1" href="${BASE}/affiche.php?s=${SHOP.join}" target="_blank">🖨️ Imprimer l'affichette</a>
      <button class="btn btn-g" style="flex:1" id="jCopy">Copier le lien</button>
    </div>
    <button class="btn btn-g" style="width:100%;margin-top:8px" onclick="document.getElementById('sheet').classList.remove('on')">Fermer</button>`);
  $('#jCopy').onclick=()=>{navigator.clipboard?.writeText(joinUrl()).then(()=>toast('🔗 Lien copié'),()=>toast(joinUrl()));};
};
$('#cAdd').onclick=()=>{sheet(`<h3 style="font-size:18px">Nouveau client</h3>
  <input class="field" id="nName" placeholder="Prénom / nom" style="margin-top:10px">
  <input class="field" id="nTel" placeholder="Téléphone (optionnel)" style="margin-top:8px">
  <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:10px 0 6px">Date de naissance <span style="font-weight:400;color:var(--faint)">(optionnel — pour le cadeau d'anniversaire 🎂)</span></label>
  <input class="field" id="nBday" type="date">
  <button class="btn btn-p" id="nOk" style="width:100%;margin-top:12px">Créer & afficher le code</button>`);
  $('#nOk').onclick=async()=>{const name=$('#nName').value.trim();if(!name){$('#nName').focus();return;}
    const r=await api('client_add',{name,tel:$('#nTel').value,bday:$('#nBday').value});
    if(r.ok){if(r.existing)toast('Ce client a déjà une carte — la voici');loadClients();showQR(r.client);}
    else if(r.error==='quota')showUpgrade(r.message);
    else toast(r.message||'Erreur');};};

$('#cBlank').onclick=()=>{sheet(`<h3 style="font-size:18px">🖨️ Cartes vierges</h3>
  <p style="font-size:12.5px;color:var(--muted);margin:6px 0 12px">Imprimez un lot de cartes à l'avance, gardez-les au comptoir. Quand vous en remettez une à un client, scannez-la pour l'activer avec son nom — ça compte comme sa première visite.</p>
  <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:10px 0 6px">Combien de cartes ?</label>
  <input class="field" id="bqN" type="number" inputmode="numeric" value="10" min="1" max="200">
  <button class="btn btn-p" id="bqOk" style="width:100%;margin-top:12px">Générer & imprimer</button>`);
  $('#bqOk').onclick=async()=>{
    const n=Math.max(1,Math.min(200,parseInt($('#bqN').value)||0));
    const r=await api('blank_batch',{n});
    if(!r.ok){if(r.error==='quota'){closeSheet();showUpgrade(r.message);}else toast(r.message||'Erreur');return;}
    closeSheet();loadHome();
    const ids=r.cards.map(c=>c.card).join(',');
    window.open(BASE+'/cartesvierges.php?ids='+encodeURIComponent(ids),'_blank');
    toast('✓ '+r.cards.length+' carte(s) générée(s)');
  };};

/* fiche client */
function sheet(h){$('#sheetC').innerHTML=h;$('#sheet').classList.add('on');$('#sheetC').scrollTop=0;}
$('#sheet').onclick=e=>{if(e.target.id==='sheet')$('#sheet').classList.remove('on');};
function closeSheet(){$('#sheet').classList.remove('on');}

/* Quota Découverte atteint : propose de passer à une formule payante,
   paiement par Stripe Checkout (redirection, retour automatique ici). */
function showUpgrade(msg){
  const plans=[['mensuel','Mensuel','29 € / mois'],['annuel','Annuel','250 € / an'],['avie','À vie','525 € une fois']];
  sheet(`<h3 style="font-size:18px">Formule Découverte complète</h3>
    <p style="font-size:13px;color:var(--muted);margin-top:4px">${msg||'Passez en illimité pour continuer — vos clients actuels restent intacts.'}</p>
    <div style="display:flex;flex-direction:column;gap:8px;margin-top:12px">
      ${plans.map(p=>`<button class="btn btn-g upP" data-plan="${p[0]}" style="width:100%;display:flex;justify-content:space-between;align-items:center"><span>${p[1]}</span><b>${p[2]}</b></button>`).join('')}
    </div>
    <div id="upMsg" style="font-size:12.5px;color:var(--faint);margin-top:10px"></div>
    <button class="btn btn-g" style="width:100%;margin-top:8px" onclick="closeSheet()">Plus tard</button>`);
  $$('.upP').forEach(b=>b.onclick=async()=>{
    $('#upMsg').textContent='Redirection vers le paiement…';
    const r=await api('stripe_checkout',{plan:b.dataset.plan});
    if(r.ok&&r.url)location.href=r.url;
    else $('#upMsg').textContent=r.error==='stripe_off'?'Paiement pas encore configuré — contactez-nous.':'Erreur, réessayez.';
  });
}
async function openClient(cid){const r=await api('client_get',{cid});if(!r.ok)return;const c=r.client;
  const nx=c.next,prog=nx?Math.round(c.points/nx.pts*100):100;
  const rws=[...(SHOP.rewards||[])].sort((a,b)=>a.pts-b.pts);
  const rwHtml=rws.map(x=>{const ok=c.points>=x.pts;
    return `<div class="rwl ${ok?'ok':'lock'}"><div class="rp">${x.pts}<small>PTS</small></div>
      <div style="flex:1;min-width:0"><div class="rt">${esc(x.t)}</div></div>
      ${ok?`<button class="btn btn-gold rmt" data-pts="${x.pts}" data-t="${esc(x.t)}" style="padding:8px 13px;font-size:13px">Remettre</button>`
          :`<span class="rlk">encore ${x.pts-c.points} pt${(x.pts-c.points)>1?'s':''}</span>`}</div>`;}).join('');
  sheet(`<div style="display:flex;align-items:center;gap:12px"><span class="av" style="width:46px;height:46px;border-radius:12px;background:var(--me);color:var(--em-d);display:grid;place-items:center;font-weight:600;font-size:17px">${initials(c.name)}</span>
    <div style="flex:1"><div style="font-family:var(--disp);font-weight:600;font-size:18px">${esc(c.name)}</div><div class="mono" style="font-size:11px;color:var(--faint)">${esc(c.tel)} · ${c.visits} visites</div></div>
    <div style="text-align:right"><div class="mono" style="font-size:26px;color:var(--em);font-weight:600;line-height:1">${c.points}</div><div style="font-size:9px;color:var(--faint)">POINTS</div></div></div>
    <div style="margin-top:12px;font-size:12px;color:var(--muted);display:flex;justify-content:space-between"><span>${nx?('Prochain palier : '+esc(nx.t)):'Palier max 🎉'}</span><span class="mono">${nx?(c.points+'/'+nx.pts):''}</span></div>
    <div class="goalbar"><i style="width:${prog}%"></i></div>
    <div style="display:flex;gap:8px;margin-top:14px"><button class="btn btn-p" id="ap" style="flex:1">+1 point</button><button class="btn btn-g" id="sq" style="flex:1">Code à scanner</button></div>
    <div style="display:flex;gap:8px;margin-top:8px"><button class="btn btn-g" id="undo" style="flex:1">− Annuler un point</button><button class="btn btn-g" id="mycard" style="flex:1">🪪 Sa carte</button></div>
    <div style="display:flex;gap:8px;margin-top:8px"><button class="btn btn-g" id="cedit" style="flex:1">✏️ Modifier</button><button class="btn btn-g" id="cdel" style="flex:1;color:#c0492f">🗑 Supprimer</button></div>
    <div style="font-size:12px;color:var(--muted);margin:16px 2px 8px;font-weight:600">Récompenses <span style="color:var(--faint);font-weight:400">— le client choisit, ou continue à cumuler</span></div>
    <div class="rwlist">${rwHtml}</div>`);
  $('#ap').onclick=()=>{closeSheet();addPoint({cid:c.id});};
  $('#sq').onclick=()=>showQR(c);
  $('#undo').onclick=async()=>{if(c.points<1){toast('Aucun point à annuler');return;}
    if(!confirm('Retirer 1 point à '+c.name+' ?'))return;
    const x=await api('adjust',{cid:c.id,delta:-1});if(x.ok){toast('− 1 point ('+x.client.points+' pts)');openClient(c.id);loadHome();}};
  $('#mycard').onclick=()=>showCustomerCard(c);
  $('#cedit').onclick=()=>editClient(c);
  $('#cdel').onclick=async()=>{
    if(!confirm('Supprimer définitivement '+c.name+' ?\n\nSes '+c.points+' point(s) et sa carte seront perdus.'))return;
    const x=await api('client_del',{cid:c.id});
    if(x.ok){toast('Client supprimé');closeSheet();loadHome();if($('.screen[data-s=clients]').classList.contains('on'))loadClients();}
    else toast('Erreur');};
  $$('.rmt').forEach(b=>b.onclick=async()=>{
    if(!confirm('Remettre « '+b.dataset.t+' » à '+c.name+' ? '+b.dataset.pts+' points seront déduits.'))return;
    const x=await api('redeem',{cid:c.id,rw:b.dataset.pts});
    if(x.ok){toast('🎁 « '+x.reward.t+' » remise — reste '+x.client.points+' pts');closeSheet();loadHome();if($('.screen[data-s=clients]').classList.contains('on'))loadClients();}
    else toast(x.error==='insufficient'?'Points insuffisants':'Erreur');});}
function editClient(c){
  sheet(`<h3 style="font-size:18px">✏️ Modifier la fiche</h3>
    <p style="font-size:12.5px;color:var(--muted);margin:6px 0 10px">Corrigez le nom ou le téléphone. Les points ne changent pas.</p>
    <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:10px 0 6px">Nom du client</label>
    <input class="field" id="ceName" maxlength="50" value="${esc(c.name)}">
    <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:10px 0 6px">Téléphone</label>
    <input class="field" id="ceTel" maxlength="30" inputmode="tel" value="${c.tel==='—'?'':esc(c.tel)}">
    <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:10px 0 6px">Date de naissance <span style="font-weight:400;color:var(--faint)">(optionnel — pour le cadeau d'anniversaire 🎂)</span></label>
    <input class="field" id="ceBday" type="date" value="${c.bday?('2000-'+c.bday):''}">
    <button class="btn btn-p" id="ceOk" style="width:100%;margin-top:14px">Enregistrer</button>`);
  $('#ceOk').onclick=async()=>{
    const name=$('#ceName').value.trim();
    if(!name){$('#ceName').focus();return;}
    const x=await api('client_edit',{cid:c.id,name,tel:$('#ceTel').value.trim(),bday:$('#ceBday').value});
    if(x.ok){toast('✓ Fiche mise à jour');openClient(c.id);loadHome();
      if($('.screen[data-s=clients]').classList.contains('on'))loadClients();}
    else toast('Erreur');};
}
function showQR(c){sheet(`<h3 style="font-size:17px;text-align:center">Code de ${esc(c.name)}</h3>
  <p style="text-align:center;font-size:12px;color:var(--muted);margin-top:4px">Le client le présente, vous le scannez.</p>
  <div class="qr-box"><img src="${qrURL('FIDELO:'+c.card,5)}"></div>
  <div class="mono" style="text-align:center;font-size:11px;color:var(--faint);margin-top:8px">FIDELO:${c.card}</div>
  <button class="btn btn-g" style="width:100%;margin-top:12px" onclick="document.getElementById('sheet').classList.remove('on')">Fermer</button>`);}
function cardUrl(c){return location.origin+BASE+'/carte.php?c='+c.card;}
function showCustomerCard(c){
  sheet(`<h3 style="font-size:17px;text-align:center">Carte de ${esc(c.name)}</h3>
    <p style="text-align:center;font-size:12px;color:var(--muted);margin-top:4px">Le client scanne pour ouvrir SA carte sur son téléphone (utile s'il l'a perdue, ou pour l'imprimer sur une carte physique).</p>
    <div class="qr-box"><img src="${qrURL(cardUrl(c),5)}"></div>
    <div style="text-align:center;font-size:11px;color:var(--faint);margin-top:8px" class="mono">${location.host}${BASE}/carte.php?c=${c.card}</div>
    <div style="display:flex;gap:8px;margin-top:12px">
      <a class="btn btn-p" style="flex:1" href="${BASE}/carteimp.php?c=${c.card}" target="_blank">🖨️ Imprimer sa carte</a>
      <button class="btn btn-g" style="flex:1" onclick="navigator.clipboard?.writeText('${cardUrl(c)}').then(()=>toast('🔗 Lien copié'))">Copier le lien</button>
    </div>
    <button class="btn btn-g" style="width:100%;margin-top:8px" onclick="document.getElementById('sheet').classList.remove('on')">Fermer</button>`);}

/* --- file d'attente hors-ligne : si le réseau tombe, le scan est mémorisé
   et crédité automatiquement au retour de la connexion --- */
function qGet(){try{return JSON.parse(localStorage.getItem('fidelo_q')||'[]');}catch(e){return[];}}
function qSet(a){try{localStorage.setItem('fidelo_q',JSON.stringify(a));}catch(e){}}
function qBadge(){const n=qGet().length;const b=$('#qBadge');if(b)b.textContent=n?('⏳ '+n+' en attente'):'';}
async function flushQueue(){
  let Q=qGet();if(!Q.length)return;const rest=[];let done=0;
  for(const it of Q){const r=await api('add_point',it);if(r&&r.error==='net')rest.push(it);else if(r&&r.ok)done++;}
  qSet(rest);qBadge();
  if(done){toast('✓ '+done+' point(s) en attente crédité(s)');loadHome();}
}
addEventListener('online',flushQueue);
async function addPoint(q){
  const r=await api('add_point',q);
  if(r.error==='net'){const Q=qGet();Q.push(q);qSet(Q);qBadge();toast('⏳ Hors ligne — sera crédité au retour du réseau');return;}
  /* Le même client vient d'être crédité il y a quelques secondes : on demande
     confirmation plutôt que de doubler son point sans rien dire. */
  if(r.error==='recent'){
    const c=r.client, s=r.seconds;
    sheet(`<h3 style="font-size:18px">⏱️ Déjà crédité</h3>
      <p style="font-size:13.5px;color:var(--muted);margin:8px 0 2px">
        <b>${esc(c.name)}</b> a reçu un point il y a ${s<60?s+' seconde'+(s>1?'s':''):'moins de 2 minutes'}.
        Il a ${c.points} point${c.points>1?'s':''}.</p>
      <p style="font-size:12.5px;color:var(--faint);margin-top:8px">C'est souvent un double scan : deux personnes ont scanné, ou l'écran a été touché deux fois.</p>
      <div style="display:flex;gap:8px;margin-top:14px">
        <button class="btn btn-g" id="rcN" style="flex:1">Annuler</button>
        <button class="btn btn-p" id="rcY" style="flex:1">Créditer quand même</button></div>`);
    $('#rcN').onclick=closeSheet;
    $('#rcY').onclick=()=>{closeSheet();addPoint(Object.assign({},q,{force:'1'}));};
    return;
  }
  /* Carte pré-imprimée pas encore remise à un client : on demande son nom
     au lieu de créditer un point à une carte anonyme. */
  if(r.error==='blank'){
    const card=r.card;
    sheet(`<h3 style="font-size:18px">🆕 Nouvelle carte</h3>
      <p style="font-size:12.5px;color:var(--muted);margin:6px 0 12px">Cette carte vient du stock pré-imprimé. Entrez le nom du client pour l'activer — ça comptera comme sa première visite.</p>
      <input class="field" id="baName" placeholder="Prénom / nom">
      <input class="field" id="baTel" placeholder="Téléphone (optionnel)" style="margin-top:8px">
      <button class="btn btn-p" id="baOk" style="width:100%;margin-top:12px">Activer la carte</button>`);
    $('#baName').focus();
    $('#baOk').onclick=async()=>{
      const name=$('#baName').value.trim();if(!name){$('#baName').focus();return;}
      const x=await api('blank_activate',{card,name,tel:$('#baTel').value});
      if(x.ok){closeSheet();toast('✓ Carte activée pour '+x.client.name);loadHome();
        if($('.screen[data-s=clients]').classList.contains('on'))loadClients();}
      else if(x.error==='quota'){closeSheet();showUpgrade(x.message);}
      else toast(x.message||'Erreur');
    };
    return;
  }
  if(!r.ok){toast(r.error==='notfound'?'Code inconnu dans votre commerce':'Erreur');return;}
  $('#ptA').classList.add('on');
  setTimeout(()=>{$('#ptA').classList.remove('on');
    if(r.unlocked)toast('🎉 '+r.client.name+' débloque : '+r.unlocked.t);
    else toast('✓ +1 point pour '+r.client.name+' ('+r.client.points+' pts)');
    loadHome();if($('.screen[data-s=clients]').classList.contains('on'))loadClients();},700);
}

/* SCANNER */
let stream=null,raf=null,vfc=null,scanning=false,warned=false,frames=0,errs=0;const cam=$('#cam'),cv=document.createElement('canvas');
$('#camBtn').onclick=()=>{scanning?stopCam():startCam();};
async function getStream(){
  try{return await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'}},audio:false});}
  catch(e){return await navigator.mediaDevices.getUserMedia({video:true,audio:false});}
}
async function startCam(){
  stopCam();warned=false;frames=0;errs=0;
  if(!navigator.mediaDevices||!navigator.mediaDevices.getUserMedia){$('#camHint').textContent='Caméra non supportée — utilisez « Saisir un code »';return;}
  if(typeof jsQR!=='function'){$('#camHint').textContent='⚠️ Lecteur QR non chargé — rechargez la page';toast('Lecteur QR indisponible. Rechargez la page.');return;}
  $('#camBtn').textContent='…';$('#camHint').textContent='Démarrage de la caméra…';
  try{
    stream=await getStream();
    cam.setAttribute('playsinline','');cam.setAttribute('muted','');cam.muted=true;cam.srcObject=stream;
    await new Promise(r=>{if(cam.readyState>=1)return r();cam.onloadedmetadata=()=>r();setTimeout(r,1200);});
    await cam.play().catch(()=>{});
    scanning=true;$('#camBtn').textContent='Arrêter';$('#camHint').textContent='Centrez le QR du client dans le cadre';
    schedule();
  }catch(e){
    scanning=false;stream=null;$('#camBtn').textContent='Réessayer la caméra';
    $('#camHint').textContent='Caméra refusée — autorisez-la, ou « Saisir un code »';
    toast('Autorisez la caméra (réglages du navigateur), ou saisissez le code.');
  }
}
function stopCam(){scanning=false;if(raf)cancelAnimationFrame(raf);raf=null;if(vfc&&cam.cancelVideoFrameCallback){try{cam.cancelVideoFrameCallback(vfc);}catch(e){}}vfc=null;if(stream){try{stream.getTracks().forEach(t=>t.stop());}catch(e){}stream=null;}try{cam.srcObject=null;}catch(e){}const b=$('#camBtn');if(b)b.textContent='Démarrer la caméra';}
function schedule(){
  if(!scanning)return;
  if(cam.requestVideoFrameCallback){vfc=cam.requestVideoFrameCallback(()=>tick());}
  else{raf=requestAnimationFrame(tick);}
}
/* Lecture d'une image : on alterne carré central (net, rapide) et image
   entière (rattrape un QR décentré). jsQR 1.4 plante avec 'onlyInvert' :
   on utilise 'attemptBoth', qui lit les QR normaux ET inversés sans erreur. */
function readFrame(useCrop){
  const vw=cam.videoWidth,vh=cam.videoHeight;
  if(!vw||!vh)return null;
  let sx=0,sy=0,sw=vw,sh=vh,w,h;
  if(useCrop){const side=Math.min(vw,vh);sx=(vw-side)/2;sy=(vh-side)/2;sw=sh=side;w=h=Math.min(512,side);}
  else{const k=Math.min(1,560/Math.max(vw,vh));w=Math.round(vw*k);h=Math.round(vh*k);}
  cv.width=w;cv.height=h;
  const x=cv.getContext('2d',{willReadFrequently:true});
  x.drawImage(cam,sx,sy,sw,sh,0,0,w,h);
  const img=x.getImageData(0,0,w,h);
  return jsQR(img.data,w,h,{inversionAttempts:'attemptBoth'});
}
function tick(){
  if(!scanning)return;
  frames++;
  if(cam.readyState>=2&&cam.videoWidth){
    try{
      const code=readFrame(frames%2===0);
      if(code&&code.data){errs=0;onScan(code.data);return;}
      errs=0;
    }catch(err){
      /* une image illisible ne doit JAMAIS tuer le scanner : on continue */
      errs++;
      if(errs===45){$('#camHint').textContent='Lecture difficile — rapprochez le QR, plus de lumière';}
      if(errs>400){scanning=false;$('#camHint').textContent='Lecteur en erreur — utilisez « Saisir un code »';
        $('#camBtn').textContent='Réessayer la caméra';return;}
    }
    if(frames===150&&errs===0)$('#camHint').textContent='Rapprochez le QR du cadre (20-25 cm)';
  }
  schedule();
}
function onScan(data){
  data=(data||'').trim();
  if(!/^FIDELO:/i.test(data)){ $('#camHint').textContent='Visez le QR de la carte du client 🎫'; schedule(); return; }
  if(navigator.vibrate)navigator.vibrate(60);
  stopCam();addPoint({card:data.slice(7)});
}
$('#simBtn').onclick=()=>{sheet(`<h3 style="font-size:17px">Saisir un code</h3>
  <p style="font-size:12px;color:var(--muted);margin-top:4px">Le code figure sous le QR du client (FIDELO:…).</p>
  <input class="field" id="mCode" placeholder="FIDELO:XXXXXXXX" style="margin-top:10px">
  <button class="btn btn-p" id="mOk" style="width:100%;margin-top:10px">Valider +1</button>`);
  $('#mOk').onclick=()=>{let v=$('#mCode').value.trim();if(v.startsWith('FIDELO:'))v=v.slice(7);if(!v)return;closeSheet();addPoint({card:v});};};

/* RÉCOMPENSES */
let RW=[];
function renderRewards(){RW=JSON.parse(JSON.stringify(SHOP.rewards||[]));drawRw();}
function drawRw(){$('#rwList').innerHTML=RW.map((r,i)=>`<div class="rw-e"><div class="p"><input class="field" type="number" min="1" value="${r.pts}" data-p="${i}"></div>
  <div class="m"><input class="field" value="${esc(r.t)}" placeholder="Récompense" data-t="${i}"></div>
  <button class="del" data-d="${i}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg></button></div>`).join('');
  $$('#rwList [data-p]').forEach(x=>x.oninput=()=>RW[+x.dataset.p].pts=Math.max(1,+x.value||1));
  $$('#rwList [data-t]').forEach(x=>x.oninput=()=>RW[+x.dataset.t].t=x.value);
  $$('#rwList [data-d]').forEach(b=>b.onclick=()=>{RW.splice(+b.dataset.d,1);drawRw();});}
$('#rwAdd').onclick=()=>{RW.push({pts:(RW.length?RW[RW.length-1].pts:0)+5,t:'Nouvelle récompense',d:''});drawRw();};
$('#rwSave').onclick=async()=>{const r=await api('rewards_set',{rewards:JSON.stringify(RW)});if(r.ok){SHOP.rewards=r.rewards;toast('✓ Récompenses enregistrées');}else toast('Gardez au moins une récompense');};

/* STATS */
async function loadStats(){const r=await api('stats');if(!r.ok)return;
  $('#kActive').textContent=r.active;$('#kTotal').textContent=r.nClients;
  drawChart(r.series);
  const seg=[['Champions',r.seg.champions,'var(--or)'],['Fidèles',r.seg.fideles,'var(--em)'],['Nouveaux',r.seg.nouveaux,'var(--em-l)'],['Endormis',r.seg.endormis,'var(--warn)']];
  const tot=seg.reduce((a,b)=>a+b[1],0)||1;
  $('#segbar').innerHTML=seg.map(s=>`<i style="flex:${s[1]||0.001};background:${s[2]}"></i>`).join('');
  $('#seglist').innerHTML=seg.map(s=>`<div class="seg-i"><span class="d" style="background:${s[2]}"></span><span>${s[0]}</span><span class="c">${s[1]}</span></div>`).join('');
  $('#sleepers').innerHTML=r.sleepers.length?r.sleepers.map(s=>`<div class="slp" data-sid="${s.id}"><span class="av">${initials(s.name)}</span><div><div class="nm">${esc(s.name)}</div><div class="mt">${esc(s.tel)} · ${s.points} pts</div></div><span class="days">💤 ${s.days} j</span><button class="rel" data-rel="${s.id}">Relancer</button></div>`).join('')
    :'<div style="color:rgba(238,247,242,.6);font-size:13px;text-align:center;padding:14px">Aucun client endormi ✓</div>';
  $$('#sleepers [data-rel]').forEach(b=>b.onclick=async()=>{await api('relance',{cid:b.dataset.rel});const el=b.closest('.slp');el.classList.add('done');b.textContent='✓ Relancé';toast('🔔 Relance envoyée (gratuit)');});
  $('#top').innerHTML=r.top.map((c,i)=>`<div class="cli" style="cursor:default"><span class="av" style="background:${i<3?'var(--or)':'var(--me)'};color:${i<3?'#241a05':'var(--em-d)'}">${i+1}</span><span style="flex:1"><span class="nm">${esc(c.name)}</span><br><span class="mt">${c.visits} visites</span></span><span class="pts"><b>${c.points}</b><span>PTS</span></span></div>`).join('');
}
function drawChart(s){const W=680,H=200,PL=36,PR=12,PT=12,PB=28,data=s.pts,n=data.length,max=Math.max(5,Math.ceil(Math.max(...data)/5)*5);
  const x=i=>PL+i*((W-PL-PR)/(n-1||1)),y=v=>(H-PB)-(v/max)*((H-PB)-PT);
  const gy=[0,.5,1].map(f=>({v:Math.round(max*f),yy:(H-PB)-f*((H-PB)-PT)}));
  const line=data.map((v,i)=>`${i?'L':'M'}${x(i).toFixed(1)} ${y(v).toFixed(1)}`).join(' ');
  const area=`M${x(0)} ${H-PB} `+data.map((v,i)=>`L${x(i).toFixed(1)} ${y(v).toFixed(1)}`).join(' ')+` L${x(n-1)} ${H-PB} Z`;
  $('#chart').innerHTML=`<svg viewBox="0 0 ${W} ${H}"><defs><linearGradient id="g" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--em)" stop-opacity=".26"/><stop offset="1" stop-color="var(--em)" stop-opacity="0"/></linearGradient></defs>
    ${gy.map(g=>`<line x1="${PL}" x2="${W-PR}" y1="${g.yy}" y2="${g.yy}" stroke="var(--grid)"/><text x="${PL-8}" y="${g.yy+4}" text-anchor="end" font-size="11" fill="var(--faint)" font-family="var(--mono)">${g.v}</text>`).join('')}
    <path d="${area}" fill="url(#g)"/><path d="${line}" fill="none" stroke="var(--em)" stroke-width="2.4" stroke-linejoin="round" stroke-linecap="round"/>
    ${data.map((v,i)=>`<text x="${x(i)}" y="${H-10}" text-anchor="middle" font-size="10" fill="var(--faint)" font-family="var(--mono)">${s.xl[i]}</text>`).join('')}
    <circle cx="${x(n-1)}" cy="${y(data[n-1])}" r="4.5" fill="var(--em)" stroke="var(--card)" stroke-width="2.5"/></svg>`;}

/* RÉGLAGES */
$('#btnSet').onclick=()=>{sheet(`<h3 style="font-size:18px">Réglages</h3>
  <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:12px 0 6px">Nom du commerce</label>
  <input class="field" id="sName" value="${esc(SHOP.name)}">
  <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:12px 0 6px">Objectif de points / jour</label>
  <input class="field" id="sGoal" type="number" min="1" value="${SHOP.goal}">
  <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:12px 0 6px">Lien avis Google <span style="font-weight:400;color:var(--faint)">(pour le proposer à vos clients)</span></label>
  <input class="field" id="sGrev" type="url" placeholder="https://g.page/r/…/review" value="${esc(SHOP.googleReview||'')}">
  <label style="display:flex;gap:9px;align-items:flex-start;margin-top:14px;font-size:12.5px;color:var(--muted);cursor:pointer">
    <input type="checkbox" id="sBdayGift" style="margin-top:2px;width:16px;height:16px" ${SHOP.birthdayGift?'checked':''}>
    <span>🎂 Offrir automatiquement 1 point le jour de l'anniversaire d'un client (si sa date de naissance est renseignée).</span></label>
  <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:14px 0 6px">Logo du commerce</label>
  <div style="display:flex;align-items:center;gap:12px">
    <img id="sLogoImg" src="${BASE}/brand.php?s=${SHOP.id}&t=logo&v=${Date.now()}" alt=""
         style="width:58px;height:58px;border-radius:50%;background:var(--line);object-fit:cover">
    <div style="flex:1">
      <button class="btn btn-g" id="sLogoPick" style="width:100%">📷 Choisir un logo</button>
      <button class="link" id="sLogoDel" style="color:var(--muted);font-size:12px;margin-top:6px">Revenir au logo automatique</button>
    </div>
  </div>
  <input type="file" id="sLogoFile" accept="image/png,image/jpeg,image/webp" hidden>
  <div id="sLogoMsg" style="font-size:12px;color:var(--muted);margin-top:6px">Il apparaît sur la carte du client, l'affichette et Google Wallet.</div>
  <button class="btn btn-p" id="sOk" style="width:100%;margin-top:14px">Enregistrer</button>
  <button class="btn btn-p" id="sParrain" style="width:100%;margin-top:8px">🤝 Parrainer un commerçant — 2 mois offerts</button>
  <button class="btn btn-g" id="sExport" style="width:100%;margin-top:8px">⬇️ Exporter mes clients (Excel)</button>
  <button class="btn btn-g" id="sDev" style="width:100%;margin-top:8px">📱 Appareils connectés</button>
  <button class="btn btn-g" id="sPass" style="width:100%;margin-top:8px">🔒 Changer mon mot de passe</button>
  <button class="btn btn-g" id="sPin" style="width:100%;margin-top:8px">🔢 Changer mon code à 4 chiffres</button>
  <button class="btn btn-g" id="sTrash" style="width:100%;margin-top:8px">🗑 Corbeille (30 jours)</button>
  <button class="btn btn-g" id="sDedup" style="width:100%;margin-top:8px">🧹 Fusionner les doublons</button>
  <button class="btn btn-g" id="sOut" style="width:100%;margin-top:8px">Se déconnecter</button>
  <div style="text-align:center;margin-top:12px"><button class="link" id="sInstall" style="color:var(--em)">📲 Ajouter à l'écran d'accueil</button></div>`);
  $('#sOk').onclick=async()=>{const r=await api('settings_set',{name:$('#sName').value,goal:$('#sGoal').value,googleReview:$('#sGrev').value.trim(),birthdayGift:$('#sBdayGift').checked?'1':'0'});
    if(r.ok){SHOP=r.shop;$('#hShop').textContent=SHOP.name;$('#hIc').textContent=initials(SHOP.name);toast('✓ Enregistré');closeSheet();}
    else toast(r.error==='googleReview'?'Lien Google invalide':'Erreur');};
  /* Logo : lecture locale → envoi en base64 (aucune dépendance serveur). */
  $('#sLogoPick').onclick=()=>$('#sLogoFile').click();
  $('#sLogoFile').onchange=async e=>{
    const f=e.target.files[0];if(!f)return;
    if(f.size>2*1024*1024){toast('Image trop lourde (2 Mo maximum)');return;}
    $('#sLogoMsg').textContent='Envoi du logo…';
    const img=await new Promise(res=>{const r=new FileReader();r.onload=()=>res(r.result);r.readAsDataURL(f);});
    const r=await api('logo_set',{img});
    if(r.ok){$('#sLogoImg').src=BASE+'/brand.php?s='+SHOP.id+'&t=logo&v='+Date.now();
      $('#sLogoMsg').textContent='✓ Logo enregistré — visible partout dans quelques secondes.';toast('✓ Logo mis à jour');}
    else $('#sLogoMsg').textContent=r.error==='size'?'Image trop lourde.':'Format non reconnu (PNG ou JPEG).';};
  $('#sLogoDel').onclick=async()=>{const r=await api('logo_set',{img:'none'});
    if(r.ok){$('#sLogoImg').src=BASE+'/brand.php?s='+SHOP.id+'&t=logo&v='+Date.now();
      $('#sLogoMsg').textContent='Logo automatique rétabli.';}};
  /* Export CSV : téléchargement via fetch (l'API exige un jeton CSRF). */
  $('#sParrain').onclick=async()=>{
    const r=await api('parrain');
    if(!r.ok){toast('Erreur');return;}
    const h=(r.histo||[]).slice().reverse().map(x=>`<div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--line);font-size:13px">
        <span>${esc(x.shop)}</span><span style="color:var(--em);font-weight:600">+${x.mois} mois</span></div>`).join('');
    sheet(`<h3 style="font-size:18px">🤝 Parrainez un commerçant</h3>
      <p style="font-size:13px;color:var(--muted);margin:8px 0 2px">Vous connaissez un commerce qui gagnerait à fidéliser ses clients&nbsp;?
      Envoyez-lui votre lien : <b>vous recevez 2 mois de Fidelo complet, et lui aussi.</b></p>
      <div style="display:flex;gap:10px;margin-top:14px">
        <div style="flex:1;background:var(--me);border-radius:14px;padding:12px;text-align:center">
          <div style="font-size:24px;font-weight:700;color:var(--em)">${r.filleuls}</div>
          <div style="font-size:11px;color:var(--muted)">commerce${r.filleuls>1?'s':''} parrainé${r.filleuls>1?'s':''}</div></div>
        <div style="flex:1;background:var(--me);border-radius:14px;padding:12px;text-align:center">
          <div style="font-size:24px;font-weight:700;color:var(--em)">${r.creditMois}</div>
          <div style="font-size:11px;color:var(--muted)">mois offerts</div></div>
      </div>
      <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:14px 0 6px">Votre lien de parrainage</label>
      <input class="field" id="paLien" readonly value="${esc(r.lien)}" style="font-size:12.5px">
      <div style="display:flex;gap:8px;margin-top:8px">
        <button class="btn btn-p" id="paCopy" style="flex:1">📋 Copier le lien</button>
        <button class="btn btn-g" id="paWa" style="flex:1">💬 WhatsApp</button>
      </div>
      <div style="font-size:12px;color:var(--faint);margin-top:10px">Votre code : <b class="mono">${esc(r.code)}</b></div>
      ${h?`<div style="font-size:12.5px;font-weight:600;color:var(--muted);margin:16px 2px 4px">Vos parrainages</div>${h}`:''}`);
    $('#paCopy').onclick=async()=>{
      try{await navigator.clipboard.writeText(r.lien);toast('✓ Lien copié');}
      catch(e){$('#paLien').select();document.execCommand('copy');toast('✓ Lien copié');}};
    $('#paWa').onclick=()=>{
      const t=encodeURIComponent("Salut ! J'utilise Fidelo pour la carte de fidélité de mon commerce, mes clients reviennent plus souvent. Avec ce lien on gagne 2 mois gratuits tous les deux : "+r.lien);
      window.open('https://wa.me/?text='+t,'_blank');};
  };

  $('#sTrash').onclick=async()=>{
    const r=await api('trash');
    if(!r.ok){toast('Erreur');return;}
    const fmt=t=>new Date(t*1000).toLocaleDateString('fr-FR',{day:'2-digit',month:'short'});
    const rows=r.trash.length?r.trash.map(c=>`
      <div style="display:flex;align-items:center;gap:10px;padding:11px 0;border-bottom:1px solid var(--line)">
        <div style="flex:1;min-width:0"><div style="font-weight:600;font-size:13.5px">${esc(c.name)}</div>
          <div style="font-size:11.5px;color:var(--faint)">${esc(c.tel||'—')} · ${c.points} pts · supprimé le ${fmt(c.at)}</div></div>
        <button class="btn btn-g trr" data-id="${c.id}" style="padding:7px 11px;font-size:12px">Restaurer</button>
      </div>`).join('') : '<div style="font-size:13px;color:var(--muted);padding:14px 0">La corbeille est vide.</div>';
    sheet(`<h3 style="font-size:18px">🗑 Corbeille</h3>
      <p style="font-size:12.5px;color:var(--muted);margin:6px 0 4px">Les clients supprimés restent récupérables 30 jours.</p>${rows}`);
    $$('.trr').forEach(b=>b.onclick=async()=>{
      const x=await api('trash_restore',{cid:b.dataset.id});
      if(x.ok){toast('✓ '+x.client.name+' restauré ('+x.client.points+' pts)');closeSheet();loadHome();
        if($('.screen[data-s=clients]').classList.contains('on'))loadClients();}
      else toast('Erreur');});
  };

  $('#sExport').onclick=async()=>{
    toast('Préparation du fichier…');
    try{
      const r=await fetch(BASE+'/api.php',{method:'POST',
        headers:{'X-CSRF':CSRF,'Content-Type':'application/x-www-form-urlencoded'},
        body:new URLSearchParams({a:'export'})});
      if(!r.ok){toast('Export impossible');return;}
      const blob=await r.blob();
      const url=URL.createObjectURL(blob), a=document.createElement('a');
      a.href=url; a.download='clients-'+(SHOP.name||'fidelo').replace(/[^a-z0-9]+/gi,'-')+'.csv';
      document.body.appendChild(a); a.click(); a.remove();
      setTimeout(()=>URL.revokeObjectURL(url),4000);
      toast('✓ Fichier téléchargé');
    }catch(e){toast('Export impossible');}
  };

  /* Appareils de confiance : voir et couper les accès par code. */
  $('#sDev').onclick=async()=>{
    const r=await api('devices');
    if(!r.ok){toast('Erreur');return;}
    const fmt=t=>t?new Date(t*1000).toLocaleDateString('fr-FR',{day:'2-digit',month:'short',year:'numeric'}):'—';
    const rows=r.devices.length?r.devices.map(d=>`
      <div style="display:flex;align-items:center;gap:10px;padding:11px 0;border-bottom:1px solid var(--line)">
        <span style="font-size:18px">${d.face?'🔐':'📱'}</span>
        <div style="flex:1;min-width:0">
          <div style="font-weight:600;font-size:13.5px">${d.me?'Cet appareil':'Appareil '+d.k.slice(0,6)}
            ${d.face?'<span style="color:var(--em);font-size:11px"> · visage activé</span>':''}</div>
          <div style="font-size:11.5px;color:var(--faint)">connecté le ${fmt(d.at)} · expire le ${fmt(d.expire)}</div>
        </div>
        ${d.me?'<span style="font-size:11px;color:var(--faint)">actuel</span>'
              :`<button class="btn btn-g dvr" data-k="${d.k}" style="padding:7px 11px;font-size:12px">Couper</button>`}
      </div>`).join('') : '<div style="font-size:13px;color:var(--muted);padding:14px 0">Aucun appareil enregistré.</div>';
    sheet(`<h3 style="font-size:18px">📱 Appareils connectés</h3>
      <p style="font-size:12.5px;color:var(--muted);margin:6px 0 4px">Ces appareils ouvrent votre espace avec le code à 4 chiffres. Coupez ceux que vous ne reconnaissez pas.</p>
      ${rows}
      ${r.devices.length>1?'<button class="btn btn-g" id="dvAll" style="width:100%;margin-top:12px;color:#c0492f">Couper tous les autres appareils</button>':''}`);
    $$('.dvr').forEach(b=>b.onclick=async()=>{
      const x=await api('device_revoke',{k:b.dataset.k});
      if(x.ok){toast('Appareil déconnecté');$('#sDev').click();}});
    const all=$('#dvAll');
    if(all)all.onclick=async()=>{
      if(!confirm('Déconnecter tous les autres appareils ? Ils devront se reconnecter avec le mot de passe.'))return;
      const x=await api('device_revoke',{k:'all'});
      if(x.ok){toast('Appareils déconnectés — reconnectez-vous');setTimeout(()=>location.reload(),900);}};
  };

  $('#sPass').onclick=()=>{sheet(`<h3 style="font-size:18px">🔒 Changer mon mot de passe</h3>
    <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:12px 0 6px">Mot de passe actuel</label>
    <input class="field" id="pwOld" type="password" autocomplete="current-password">
    <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:10px 0 6px">Nouveau mot de passe (8 caractères minimum)</label>
    <input class="field" id="pwNew" type="password" autocomplete="new-password">
    <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:10px 0 6px">Confirmer</label>
    <input class="field" id="pwNew2" type="password" autocomplete="new-password">
    <label style="display:flex;gap:9px;align-items:flex-start;margin-top:12px;font-size:12.5px;color:var(--muted);cursor:pointer">
      <input type="checkbox" id="pwRev" style="margin-top:2px;width:16px;height:16px">
      <span>Déconnecter aussi tous les autres appareils (à cocher si vous pensez que quelqu'un d'autre a eu accès à votre espace).</span></label>
    <div id="pwMsg" style="font-size:12.5px;color:#c0492f;margin-top:8px"></div>
    <button class="btn btn-p" id="pwOk" style="width:100%;margin-top:12px">Enregistrer</button>`);
    $('#pwOk').onclick=async()=>{
      const a=$('#pwOld').value,b=$('#pwNew').value,c2=$('#pwNew2').value;
      if(b.length<8){$('#pwMsg').textContent='8 caractères minimum.';return;}
      if(b!==c2){$('#pwMsg').textContent='Les deux mots de passe ne correspondent pas.';return;}
      const r=await api('pass_set',{old:a,new:b,revoke:$('#pwRev').checked?'1':''});
      if(r.ok){toast(r.revoked?('✓ Modifié — '+r.revoked+' appareil(s) déconnecté(s)'):'✓ Mot de passe modifié');closeSheet();}
      else $('#pwMsg').textContent=r.error==='old'?'Mot de passe actuel incorrect.'
        :r.error==='same'?'Choisissez un mot de passe différent.'
        :r.error==='ratelimit'?'Trop de tentatives, réessayez dans 15 minutes.':'Erreur.';};};

  $('#sPin').onclick=()=>{sheet(`<h3 style="font-size:18px">🔢 Changer mon code</h3>
    <p style="font-size:12.5px;color:var(--muted);margin:6px 0 10px">Ce code ouvre l'espace sur les appareils de confiance.</p>
    <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:10px 0 6px">Mot de passe (pour confirmer)</label>
    <input class="field" id="pnPass" type="password" autocomplete="current-password">
    <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:10px 0 6px">Nouveau code à 4 chiffres</label>
    <input class="field" id="pnPin" inputmode="numeric" maxlength="4" placeholder="••••" style="letter-spacing:.5em;text-align:center;font-size:22px">
    <div id="pnMsg" style="font-size:12.5px;color:#c0492f;margin-top:8px"></div>
    <button class="btn btn-p" id="pnOk" style="width:100%;margin-top:12px">Enregistrer</button>`);
    $('#pnPin').oninput=e=>e.target.value=e.target.value.replace(/\D/g,'');
    $('#pnOk').onclick=async()=>{
      const pin=$('#pnPin').value;
      if(pin.length!==4){$('#pnMsg').textContent='Il faut exactement 4 chiffres.';return;}
      const r=await api('pin_set',{pass:$('#pnPass').value,pin});
      if(r.ok){toast('✓ Code modifié');closeSheet();}
      else $('#pnMsg').textContent=r.error==='pass'?'Mot de passe incorrect.'
        :r.error==='weak'?'Ce code est trop courant (1234, 0000…). Choisissez-en un autre.'
        :r.error==='ratelimit'?'Trop de tentatives, réessayez dans 15 minutes.':'Erreur.';};};

  $('#sOut').onclick=async()=>{await api('logout');location.reload();};
  $('#sDedup').onclick=async()=>{if(!confirm('Fusionner les clients ayant le même numéro de téléphone ? (points additionnés)'))return;
    const r=await api('dedup');if(r.ok){toast(r.merged?('✓ '+r.merged+' doublon(s) fusionné(s)'):'Aucun doublon trouvé');if(r.merged){closeSheet();loadHome();}}};
  $('#sInstall').onclick=doInstall;};

/* Message groupé — accessible depuis l'onglet Clients */
const BC_SEG_LABEL={all:'Tous les clients',champions:'🏆 Champions',fideles:'❤️ Fidèles',nouveaux:'🌱 Nouveaux',endormis:'💤 Endormis'};
function openBroadcast(){
  sheet(`<h3 style="font-size:18px">📣 Message groupé</h3>
    <p style="font-size:12.5px;color:var(--muted);margin:6px 0 10px">Le message s'affiche sur la carte des clients ciblés, et part en notification à ceux qui les ont activées.</p>
    <label style="display:block;font-size:12.5px;font-weight:600;color:var(--muted);margin:10px 0 6px">Destinataires</label>
    <select class="field" id="bcSeg">
      <option value="all">Tous les clients</option>
      <option value="champions">🏆 Champions (20 pts et +)</option>
      <option value="fideles">❤️ Fidèles (10 à 19 pts)</option>
      <option value="nouveaux">🌱 Nouveaux (moins de 10 pts)</option>
      <option value="endormis">💤 Endormis (inactifs 14 j et +)</option>
    </select>
    <input class="field" id="bcT" placeholder="Titre (ex. Offre du jour)" maxlength="60" style="margin-top:8px">
    <textarea class="field" id="bcB" placeholder="Votre message…" maxlength="160" style="min-height:80px;margin-top:8px;resize:vertical"></textarea>
    <button class="btn btn-p" id="bcOk" style="width:100%;margin-top:12px">Envoyer</button>
    <button class="btn btn-g" id="bcHist" style="width:100%;margin-top:8px">🕓 Historique des messages envoyés</button>`);
  $('#bcOk').onclick=async()=>{
    const title=$('#bcT').value.trim(),body=$('#bcB').value.trim(),segment=$('#bcSeg').value;
    if(!body){$('#bcB').focus();return;}
    $('#bcOk').disabled=true;$('#bcOk').textContent='Envoi…';
    const form=new URLSearchParams({title,body,segment});
    const r=await fetch(BASE+'/push.php?a=broadcast',{method:'POST',headers:{'X-CSRF':CSRF,'Content-Type':'application/x-www-form-urlencoded'},body:form}).then(x=>x.json()).catch(()=>({ok:false,error:'net'}));
    if(r.ok){
      let t='📣 Message affiché sur '+r.clients+' carte'+(r.clients>1?'s':'');
      if(r.sent)t+=' · '+r.sent+' notification'+(r.sent>1?'s':'')+' envoyée'+(r.sent>1?'s':'');
      toast(t);closeSheet();
    } else {toast(r.error==='net'?'Pas de connexion — réessayez':(r.error==='empty'?'Écrivez un message':'Erreur d\'envoi'));$('#bcOk').disabled=false;$('#bcOk').textContent='Envoyer';}
  };
  $('#bcHist').onclick=openBroadcastHistory;
}
function openBroadcastHistory(){
  sheet(`<h3 style="font-size:18px">🕓 Historique des messages</h3><div id="bcHList" style="margin-top:10px;font-size:13px;color:var(--muted)">Chargement…</div>`);
  api('broadcast_history').then(r=>{
    if(!r.ok||!r.items.length){$('#bcHList').textContent='Aucun message envoyé pour le moment.';return;}
    $('#bcHList').innerHTML=r.items.map(m=>{
      const d=new Date(m.at*1000).toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
      return `<div style="padding:10px 0;border-bottom:1px solid var(--line)">
        <div style="font-weight:600;color:var(--text)">${esc(m.title)}</div>
        <div style="font-size:12.5px;margin:2px 0">${esc(m.body)}</div>
        <div style="font-size:11.5px;color:var(--faint)">${esc(BC_SEG_LABEL[m.segment]||m.segment)} · ${m.clients} carte${m.clients>1?'s':''}${m.sent?(' · '+m.sent+' notif.'):''} · ${d}</div>
      </div>`;
    }).join('');
  });
}

$('#cBroadcast').onclick=openBroadcast;

/* PWA install */
let deferred=null;addEventListener('beforeinstallprompt',e=>{e.preventDefault();deferred=e;});
function doInstall(){if(deferred){deferred.prompt();deferred=null;return;}
  const ua=navigator.userAgent,iOS=/iphone|ipad|ipod/i.test(ua),mac=/macintosh/i.test(ua)&&!('ontouchend'in document);
  toast(iOS?'Appuyez sur Partager ⬆︎ en bas, puis « Sur l\'écran d\'accueil »'
    :mac?'Menu Fichier de Safari → « Ajouter au Dock »'
    :'Menu du navigateur ⋮ → « Ajouter à l\'écran d\'accueil »');}
// bouton d'installation dans l'en-tête (masqué si déjà installé)
{const ib=document.getElementById('btnInstall');
 if(ib){ if(matchMedia('(display-mode: standalone)').matches||navigator.standalone){ib.style.display='none';} else {ib.onclick=doInstall;} }}
if('serviceWorker'in navigator){navigator.serviceWorker.register(BASE+'/sw.js').catch(()=>{});}
</script></body></html>
