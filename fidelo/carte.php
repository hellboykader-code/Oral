<?php
/* ==================================================================
   Fidelo — carte client (Part 2). Accès par jeton ?c=<card>.
   Lecture seule côté client : le client ne peut PAS s'ajouter de
   points (anti-fraude). Petite API interne : abonnement Push.
   ================================================================== */
require __DIR__ . '/lib.php'; require __DIR__ . '/gwallet.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$card = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['c'] ?? ($_POST['c'] ?? ''));

/* ---- micro-API (POST a=...) ---- */
$a = $_POST['a'] ?? '';
if ($a !== '') {
  /* verrou limité au commerce de cette carte : les autres commerces
     continuent de travailler pendant ce temps */
  $cardsIdx = cards_load();
  $LOCK = db_lock($cardsIdx[$card] ?? null);
  $db = db_load();
  $found = client_by_card($db, $card);
  if (!$found) json_out(['ok' => false, 'error' => 'notfound'], 404);

  if ($a === 'get') {
    $s = $found['shop']; $c = $found['client'];
    json_out(['ok' => true,
      'shop' => ['id' => $s['id'], 'name' => $s['name'], 'type' => $s['type'] ?? 'Commerce',
                 'brandAt' => (int)($s['brandAt'] ?? 1),
                 'c1' => brand_colors($s)[0], 'c2' => brand_colors($s)[1]],
      'rewards' => array_values($s['rewards'] ?? []),
      'client' => ['id' => $c['id'], 'name' => $c['name'], 'points' => (int)$c['points'],
        'visits' => (int)$c['visits'], 'card' => $c['card'], 'created' => (int)($c['created'] ?? now())],
      'vapidPub' => vapid_ensure($db),
      'msg' => $found['client']['msg'] ?? null,
      'gw' => gw_live($db),
    ]);
  }
  if ($a === 'gwsave') {
    if (!gw_live($db)) json_out(['ok' => false, 'error' => 'off'], 400);
    // endpoint public : on borne les appels à l'API Google
    if (!rate_hit($db, 'gwsave:' . client_ip(), 20, 3600)) { db_save($db); json_out(['ok'=>false,'error'=>'rate'],429); }
    $url = gw_save_url($db, $found['shop'], $found['client']);
    if ($url) {
      // on retient que ce client utilise Google Wallet → ses points y seront tenus à jour
      $sid = $found['shop']['id'];
      foreach ($db['shops'] as $si => $sh) {
        if ($sh['id'] !== $sid) continue;
        foreach ($sh['clients'] as $ci => $cc)
          if (($cc['card'] ?? '') === $card || in_array($card, $cc['alias'] ?? [], true))
            $db['shops'][$si]['clients'][$ci]['gw'] = 1;
      }
      db_save($db);
    }
    json_out($url ? ['ok' => true, 'url' => $url] : ['ok' => false, 'error' => 'gw'], $url ? 200 : 502);
  }
  if ($a === 'msgseen') {
    // le client a lu le message du commerce : on le retire de sa carte
    $sid = $found['shop']['id'];
    foreach ($db['shops'] as $si => $sh) {
      if ($sh['id'] !== $sid) continue;
      foreach ($sh['clients'] as $ci => $c)
        if (($c['card'] ?? '') === $card || in_array($card, $c['alias'] ?? [], true)) {
          unset($db['shops'][$si]['clients'][$ci]['msg']);
          db_save($db);
          json_out(['ok' => true]);
        }
    }
    json_out(['ok' => false, 'error' => 'notfound'], 404);
  }
  if ($a === 'subscribe') {
    // enregistre l'abonnement push du client (gratuit)
    $sub = json_decode($_POST['sub'] ?? '', true);
    if (!$sub || empty($sub['endpoint'])) json_out(['ok' => false, 'error' => 'sub'], 400);
    $sid = $found['shop']['id'];
    foreach ($db['shops'] as $si => $s) foreach (($s['id'] === $sid ? $s['clients'] : []) as $ci => $c) {
      if (($c['card'] ?? '') === $card || in_array($card, $c['alias'] ?? [], true)) {
        $db['shops'][$si]['clients'][$ci]['push'] = array_slice(
          array_values(array_filter(($c['push'] ?? []), fn($p) => ($p['endpoint'] ?? '') !== $sub['endpoint'])), 0, 2);
        $db['shops'][$si]['clients'][$ci]['push'][] = $sub;
        db_save($db);
        // cookie pour que le service worker s'identifie lors du "peek" (push sans payload)
        setcookie('fidelo_card', $card, ['expires' => now() + 60*60*24*365, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']), 'httponly' => true, 'samesite' => 'Lax']);
        json_out(['ok' => true]);
      }
    }
    json_out(['ok' => false, 'error' => 'notfound'], 404);
  }
  json_out(['ok' => false, 'error' => 'unknown'], 400);
}

/* ---- page ---- */
$db = db_load();
$found = $card ? client_by_card($db, $card) : [];
$exists = (bool)$found;
$shopName = $exists ? $found['shop']['name'] : 'Fidelo';
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title>Ma carte <?= e($shopName) ?></title><meta name="theme-color" content="#0A2E38">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="<?= e($shopName) ?>">
<link rel="apple-touch-icon" href="<?= e($base) ?>/icon-192.png">
<link rel="manifest" href="<?= e($base) ?>/manifest.php?c=<?= e($card) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Instrument+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap">
<script src="qrcode.min.js"></script>
<style>
:root{--em:#06B6D4;--em-d:#0891B2;--em-l:#22D3EE;--or:#D9A94E;--or-l:#f0d488;--or-d:#bd8f38;--iv:#F3FCFD;--me:#E7F8FA;--me-d:#D6EEF1;--card:#fff;--line:#D6EEF1;--text:#1c2a25;--muted:#5a675f;--faint:#6f7c76;--deep:#0A2E38;--deep2:#0F3D49;
--disp:"Bricolage Grotesque",system-ui,sans-serif;--body:"Instrument Sans",system-ui,sans-serif;--mono:"IBM Plex Mono",monospace;}
@media(prefers-color-scheme:dark){:root{--em:#22D3EE;--em-d:#0891B2;--em-l:#67E8F9;--or:#e3ba63;--or-l:#f2d78f;--iv:#07222A;--me:#0B2C35;--me-d:#183B45;--card:#0E323C;--line:#1C4650;--text:#e6efea;--muted:#9fc4cc;--faint:#728178;}}
*{box-sizing:border-box}body{margin:0;background:var(--iv);color:var(--text);font-family:var(--body);-webkit-font-smoothing:antialiased}
h1,h2,h3{font-family:var(--disp);font-weight:600;letter-spacing:-.02em;margin:0}button{font-family:inherit;cursor:pointer;border:0;background:none;color:inherit}[hidden]{display:none!important}.mono{font-family:var(--mono)}
.app{max-width:460px;margin:0 auto;padding:16px 16px 40px}
.bar{display:flex;align-items:center;gap:10px;padding:8px 2px 16px}.bar .ic{width:38px;height:38px;border-radius:11px;background:var(--em);color:#fff;display:grid;place-items:center;font-family:var(--disp);font-weight:700;overflow:hidden}
.bar .ic img{width:100%;height:100%;object-fit:cover;display:block;opacity:0;animation:logoIn .6s .1s forwards}
@keyframes logoIn{to{opacity:1}}
.bar .shop{font-family:var(--disp);font-weight:600;font-size:16px}.bar .sub{font-size:11px;color:var(--faint);font-family:var(--mono)}.bar .brand{margin-left:auto;font-family:var(--disp);font-weight:700;color:var(--muted)}.bar .brand i{color:var(--or);font-style:normal}
.card{position:relative;border-radius:24px;overflow:hidden;color:#eef7f2;padding:20px;background:linear-gradient(150deg,#0A2E38,#0891B2 76%,#FF6B6B 138%);box-shadow:0 20px 44px -18px rgba(10,40,32,.6)}
.card::before{content:"";position:absolute;inset:0;background:radial-gradient(120% 80% at 85% -10%,rgba(217,169,78,.28),transparent 55%)}
.card::after{content:"";position:absolute;top:-60%;left:-30%;width:60%;height:220%;background:linear-gradient(105deg,transparent,rgba(255,255,255,.13),transparent);transform:rotate(8deg);animation:sh 5.5s ease-in-out infinite}
@keyframes sh{0%,100%{left:-40%}55%{left:130%}}.card>*{position:relative;z-index:2}
.ctop{display:flex;justify-content:space-between;align-items:flex-start}
.tier{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:99px;background:rgba(217,169,78,.16);border:1px solid rgba(217,169,78,.5);color:var(--or-l);font-size:11px;font-weight:600}.tier svg{width:13px;height:13px}
.chip{width:38px;height:30px;border-radius:7px;background:linear-gradient(135deg,var(--or-l),var(--or-d))}
.cname{font-family:var(--disp);font-size:23px;font-weight:600;margin-top:16px;color:#fff}.csince{font-size:11.5px;color:rgba(238,247,242,.7);font-family:var(--mono);margin-top:2px}
.cbot{display:flex;align-items:flex-end;justify-content:space-between;margin-top:18px;gap:14px}
.pk{font-size:10.5px;letter-spacing:.14em;text-transform:uppercase;color:rgba(238,247,242,.6)}.pv{font-family:var(--disp);font-weight:700;font-size:50px;line-height:.9;color:#fff}.pv small{font-size:14px;color:var(--or-l)}
.card .brandmark{position:absolute!important;z-index:3!important;right:18px;top:18px;width:58px;height:58px;
  border-radius:50%;background-size:cover;background-position:center;pointer-events:none;
  box-shadow:0 0 0 3px rgba(255,255,255,.28), 0 8px 20px -6px rgba(0,0,0,.45);
  animation:markIn .9s .15s cubic-bezier(.2,.9,.3,1) both}
@keyframes markIn{from{opacity:0;transform:scale(.55) rotate(-18deg)}to{opacity:1;transform:none}}
.qm{background:#fff;border-radius:12px;padding:7px;flex-shrink:0}.qm img{width:78px;height:78px;image-rendering:pixelated;display:block}.qm .t{font-size:8px;color:#0891B2;text-align:center;margin-top:2px;font-family:var(--mono)}
.prog{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:15px;margin-top:14px}
.prog .r{display:flex;justify-content:space-between;font-size:13.5px}.prog .r .g{color:var(--muted)}.prog .r .c{font-family:var(--mono);color:var(--em);font-weight:600}
.track{height:10px;border-radius:99px;background:var(--me-d);overflow:hidden;margin-top:10px}.track>i{height:100%;background:linear-gradient(90deg,var(--em),var(--em-l));border-radius:99px;width:0;transition:width 1s}
.prog .h{font-size:12px;color:var(--faint);margin-top:9px}.prog .h b{color:var(--or-d)}
.acts{display:grid;grid-template-columns:1fr 1fr;gap:9px;margin-top:12px}.btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:13px;border-radius:13px;font-weight:600;font-size:14px}.btn svg{width:17px;height:17px}
.btn-gw{width:100%;margin-top:9px;background:#000;color:#fff;border:1px solid #3c4043}
.btn-gw:disabled{opacity:.6}
.btn-install{width:100%;margin-top:9px;background:linear-gradient(118deg,#06B6D4,#22D3EE 42%,#FF6B6B);color:#fff;box-shadow:0 10px 22px -8px rgba(6,182,212,.5)}
.btn-p{background:var(--em);color:#fff}.btn-o{background:var(--card);border:1.5px solid var(--line);color:var(--text)}
.sect{font-family:var(--disp);font-weight:600;font-size:16px;margin:24px 2px 12px}
.rw{display:flex;align-items:center;gap:13px;padding:13px 14px;background:var(--card);border:1px solid var(--line);border-radius:15px;margin-bottom:10px}
.rw.ok{border-color:var(--or)}.rw.lock{opacity:.6}.rw .n{width:50px;height:50px;border-radius:14px;display:grid;place-items:center;font-family:var(--mono);font-weight:600;background:var(--me);color:var(--em-d)}
.rw.ok .n{background:linear-gradient(140deg,var(--or-l),var(--or));color:#3a2a05}.rw .t{font-weight:600;font-size:14px}.rw .d{font-size:12px;color:var(--muted)}.rw .st{margin-left:auto;font-size:11px;font-weight:700}.rw.ok .st{color:var(--or-d)}.rw .st.done{color:var(--em)}.rw.lock .st{color:var(--faint)}
.foot{text-align:center;margin-top:26px;color:var(--faint);font-size:12px;line-height:1.6}.foot b{color:var(--em)}.foot i{color:var(--or);font-style:normal}
.qr-full{position:fixed;inset:0;z-index:100;background:rgba(6,14,11,.72);display:none;place-items:center;padding:24px}.qr-full.on{display:grid}
.qr-full .s{background:#fff;border-radius:24px;padding:26px;text-align:center;max-width:340px;width:100%}.qr-full .w{font-family:var(--disp);font-weight:600;font-size:18px;color:#0A2E38}.qr-full .su{font-size:12.5px;color:#5a675f;margin-top:4px}
.qr-full img{width:230px;height:230px;image-rendering:pixelated;margin-top:14px}.qr-full .cd{font-family:var(--mono);font-size:12px;color:#8a958e;margin-top:8px}.qr-full .cl{margin-top:16px;width:100%;padding:13px;border-radius:13px;background:#0A2E38;color:#fff;font-weight:600}
.msg{display:none;margin-top:14px;padding:15px 16px;border-radius:16px;position:relative;
  background:linear-gradient(126deg,rgba(6,182,212,.18),rgba(255,107,107,.16));border:1px solid rgba(34,211,238,.42)}
.msg.on{display:block;animation:msgIn .5s cubic-bezier(.2,.9,.3,1)}
@keyframes msgIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none}}
.msg .k{font-size:10.5px;letter-spacing:.14em;color:var(--em-l);font-family:var(--mono);text-transform:uppercase}
.msg .t{font-family:var(--disp);font-weight:600;font-size:16px;margin-top:5px;padding-right:26px}
.msg .b{font-size:13.5px;color:var(--text);opacity:.9;margin-top:5px;line-height:1.5;white-space:pre-wrap}
.msg .x{position:absolute;top:10px;right:10px;width:26px;height:26px;border-radius:9px;color:var(--muted);font-size:15px;line-height:1}
.empty{text-align:center;padding:60px 20px;color:var(--muted)}.toast{position:fixed;left:50%;bottom:24px;transform:translateX(-50%);background:var(--deep);color:#eef7f2;padding:12px 18px;border-radius:12px;font-size:14px;z-index:200;opacity:0;transition:.3s;pointer-events:none}.toast.on{opacity:1}
@media(prefers-reduced-motion:reduce){.card::after{display:none}*{transition:none!important}}
.flogo{width:1.35em;height:1.35em;display:inline-block;vertical-align:-.32em;margin-right:.32em}
</style></head><body>
<?php if(!$exists): ?>
<div class="app"><div class="bar"><div class="ic">F</div><div><div class="shop">Fidelo</div><div class="sub">CARTE DE FIDÉLITÉ</div></div></div>
<div class="empty"><h2>Carte introuvable</h2><p style="margin-top:10px">Ce lien n'est pas valide. Demandez à votre commerçant de vous renvoyer votre carte.</p></div></div>
<?php else: ?>
<div class="app">
  <div class="bar"><div class="ic" id="ic">·</div><div><div class="shop" id="shop">…</div><div class="sub">CARTE DE FIDÉLITÉ</div></div><div class="brand"><svg class="flogo" viewBox="0 0 64 64" aria-hidden="true"><defs><linearGradient id="caa" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#06B6D4"/><stop offset=".55" stop-color="#22D3EE"/><stop offset="1" stop-color="#FF6B6B"/></linearGradient><linearGradient id="cab" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".3"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#caa)"/><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#cab)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>Fidelo<i>.</i></div></div>
  <div class="card"><div class="ctop"><span class="tier"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.4 5 5.6.6-4.2 3.8 1.2 5.6L12 19.8 6.9 17l1.2-5.6L4 7.6 9.6 7z"/></svg><span id="tier">Client</span></span></div>
    <div class="cname" id="name">…</div><div class="csince" id="since"></div>
    <div class="brandmark" id="brandMark"></div>
    <div class="cbot"><div><div class="pk">Solde de points</div><div class="pv"><span id="pts">0</span><small>pts</small></div></div>
      <button class="qm" id="qm"><div id="qmImg"></div><div class="t">Agrandir</div></button></div>
  </div>
  <div class="msg" id="msgBox"><button class="x" id="msgX">✕</button>
    <div class="k">Message de votre commerce</div><div class="t" id="msgT"></div><div class="b" id="msgB"></div></div>
  <div class="prog"><div class="r"><span class="g">Prochaine récompense : <b id="nextL">—</b></span><span class="c" id="nextC">0/0</span></div><div class="track"><i id="fill"></i></div><div class="h" id="hint"></div></div>
  <div class="acts"><button class="btn btn-p" id="showBtn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M7 12h10"/></svg>Mon code</button>
    <button class="btn btn-o" id="notifBtn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>Notifications</button></div>
  <button class="btn btn-install" id="installBtn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>Ajouter la carte à l'écran d'accueil</button>
  <button class="btn btn-gw" id="gwBtn" hidden>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="3"/><path d="M2 10h20"/><circle cx="17.5" cy="14.5" r="1.6" fill="currentColor" stroke="none"/></svg>
    Ajouter à Google Wallet</button>
  <div class="sect">Mes récompenses</div><div id="rwList"></div>
  <div class="foot">Carte propulsée par <b>Fidelo</b><i>.</i><br>Vos points sont crédités par le commerçant.<br>Aucune application à installer.</div>
</div>
<div class="qr-full" id="qrFull"><div class="s"><div class="w" id="fw">…</div><div class="su">Montrez ce code au commerçant.<br>Il le scanne, votre point est ajouté.</div><img id="fq"><div class="cd" id="fc"></div><button class="cl" id="fClose">Fermer</button></div></div>
<div class="toast" id="toast"></div>
<script>
const BASE=<?= json_encode($base) ?>, CARD=<?= json_encode($card) ?>;
const $=s=>document.querySelector(s);let M=null,VAPID='';
function esc(s){return String(s??'').replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));}
let tT;function toast(m){const t=$('#toast');t.textContent=m;t.classList.add('on');clearTimeout(tT);tT=setTimeout(()=>t.classList.remove('on'),2600);}
const initials=n=>(n||'?').split(/\s+/).map(w=>w[0]).join('').slice(0,2).toUpperCase();
function qrURL(t,cell){const q=qrcode(0,'M');q.addData(t);q.make();return q.createDataURL(cell,6);}
const PAY=()=>'FIDELO:'+M.client.card;
function tier(p){return p>=20?'Membre VIP Or':p>=10?'Client fidèle':p>=3?'Client habitué':'Nouveau client';}
async function api(a,d={}){const b=new URLSearchParams({a,c:CARD,...d});const r=await fetch(BASE+'/carte.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:b});return r.json();}
let firstLoad=true;
async function load(){const r=await api('get');if(!r.ok)return;M=r;VAPID=r.vapidPub||'';render();firstLoad=false;}
function showMsg(){const m=M&&M.msg;const box=$('#msgBox');if(!box)return;
  if(m&&m.body){$('#msgT').textContent=m.title||'Fidelo';$('#msgB').textContent=m.body;box.classList.add('on');}
  else box.classList.remove('on');}
function render(){const s=M.shop,c=M.client,rw=M.rewards.slice().sort((a,b)=>a.pts-b.pts);showMsg();gwShow();
  if(s.c1){const el=document.querySelector('.card');
    el.style.background='linear-gradient(150deg,'+s.c1+' 0%,'+s.c1+' 22%,'+s.c2+' 96%)';
    document.documentElement.style.setProperty('--or',s.c2);
    document.documentElement.style.setProperty('--or-d',s.c2);}
  $('#shop').textContent=s.name;
  $('#ic').innerHTML='<img src="'+BASE+'/brand.php?s='+encodeURIComponent(s.id)+'&t=logo&v='+(s.brandAt||1)+'" alt="">';
  const bm=$('#brandMark');
  if(bm)bm.style.backgroundImage='url("'+BASE+'/brand.php?s='+encodeURIComponent(s.id)+'&t=logo&v='+(s.brandAt||1)+'")';$('#name').textContent=c.name;$('#fw').textContent=c.name;
  $('#tier').textContent=tier(c.points);$('#since').textContent=c.visits+' visites';
  $('#qmImg').innerHTML='<img src="'+qrURL(PAY(),4)+'">';$('#fq').src=qrURL(PAY(),8);$('#fc').textContent=PAY();
  if(firstLoad){let v=0;const iv=setInterval(()=>{v+=Math.max(1,Math.ceil(c.points/20));if(v>=c.points){v=c.points;clearInterval(iv);}$('#pts').textContent=v;},34);}
  else $('#pts').textContent=c.points;
  const nx=rw.find(r=>r.pts>c.points),claim=[...rw].reverse().find(r=>r.pts<=c.points);
  if(nx){const prev=[...rw].reverse().find(r=>r.pts<=c.points)?.pts||0;const done=c.points-prev,span=nx.pts-prev;
    $('#nextL').textContent=nx.t;$('#nextC').textContent=c.points+'/'+nx.pts;setTimeout(()=>$('#fill').style.width=Math.max(6,Math.round(done/span*100))+'%',60);
    $('#hint').innerHTML='Plus que <b>'+(nx.pts-c.points)+' point'+((nx.pts-c.points)>1?'s':'')+'</b> pour « '+esc(nx.t)+' ».';}
  else{$('#nextL').textContent='Palier max 🎉';$('#nextC').textContent=c.points+' pts';setTimeout(()=>$('#fill').style.width='100%',60);$('#hint').innerHTML='Vous êtes au sommet. <b>Merci !</b>';}
  $('#rwList').innerHTML=rw.map(r=>{const u=c.points>=r.pts;
    return '<div class="rw '+(u?'ok':'lock')+'"><div class="n">'+r.pts+'<span style="font-size:8px;margin-left:1px">PTS</span></div><div style="flex:1"><div class="t">'+esc(r.t)+'</div><div class="d">'+esc(r.d||'')+'</div></div><div class="st">'+(u?'DISPONIBLE':(r.pts-c.points)+' pts')+'</div></div>';}).join('');
}
$('#qm').onclick=$('#showBtn').onclick=()=>$('#qrFull').classList.add('on');
$('#fClose').onclick=()=>$('#qrFull').classList.remove('on');
$('#qrFull').onclick=e=>{if(e.target.id==='qrFull')$('#qrFull').classList.remove('on');};

/* ---- Push (gratuit) ---- */
function b64u(s){const p='='.repeat((4-s.length%4)%4);const b=atob((s+p).replace(/-/g,'+').replace(/_/g,'/'));return Uint8Array.from([...b].map(c=>c.charCodeAt(0)));}
/* Google Wallet — Android : la carte entre dans le portefeuille et les
   points s'y actualisent tout seuls. Le QR du pass est le même que le
   nôtre, donc le commerçant le scanne directement depuis Wallet. */
function gwShow(){const b=$('#gwBtn');if(!b)return;
  const android=/Android/i.test(navigator.userAgent);
  b.hidden=!(M&&M.gw&&android);}
$('#gwBtn').onclick=async()=>{
  const b=$('#gwBtn');b.disabled=true;const t=b.textContent;b.textContent='Préparation…';
  try{const r=await api('gwsave');
    if(r.ok&&r.url)location.href=r.url;
    else toast('Indisponible pour le moment, réessayez.');}
  catch(e){toast('Pas de connexion.');}
  b.disabled=false;b.textContent=t;
};
$('#msgX').onclick=()=>{$('#msgBox').classList.remove('on');M.msg=null;api('msgseen').catch(()=>{});};
const isIOS=/iPad|iPhone|iPod/.test(navigator.userAgent)||(navigator.platform==='MacIntel'&&navigator.maxTouchPoints>1);
const standalone=matchMedia('(display-mode: standalone)').matches||navigator.standalone===true;
$('#notifBtn').onclick=async()=>{
  // iOS n'autorise les notifications QUE depuis la carte ajoutée à l'écran d'accueil
  if(isIOS&&!standalone){toast('Sur iPhone : ajoutez d\'abord la carte à l\'écran d\'accueil, puis activez les notifications.');return;}
  if(!('serviceWorker'in navigator)||!('PushManager'in window)||typeof Notification==='undefined'){
    toast('Ce navigateur ne gère pas les notifications. Vos messages restent visibles sur la carte.');return;}
  if(!VAPID){toast('Service de notifications indisponible — réessayez dans un instant.');return;}
  try{
    const reg=await navigator.serviceWorker.register(BASE+'/sw.js');
    await navigator.serviceWorker.ready;
    const perm=await Notification.requestPermission();
    if(perm==='denied'){toast('Notifications bloquées : autorisez-les dans les réglages du navigateur.');return;}
    if(perm!=='granted'){toast('Autorisez les notifications pour être prévenu.');return;}
    let sub=await reg.pushManager.getSubscription();
    if(!sub)sub=await reg.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:b64u(VAPID)});
    const r=await api('subscribe',{sub:JSON.stringify(sub)});
    toast(r.ok?'🔔 Notifications activées !':'Erreur, réessayez.');
  }catch(e){toast('Activation impossible : '+(e&&e.message?e.message:'erreur')); }
};
/* ---- Ajouter à l'écran d'accueil (PWA) ---- */
let deferredPrompt=null;
addEventListener('beforeinstallprompt',e=>{e.preventDefault();deferredPrompt=e;});
if('serviceWorker'in navigator){navigator.serviceWorker.register(BASE+'/sw.js').catch(()=>{});}
// masquer le bouton si déjà installée (mode standalone)
if(matchMedia('(display-mode: standalone)').matches||navigator.standalone){const ib=document.getElementById('installBtn');if(ib)ib.style.display='none';}
document.getElementById('installBtn').onclick=async()=>{
  if(deferredPrompt){deferredPrompt.prompt();await deferredPrompt.userChoice;deferredPrompt=null;return;}
  const iOS=/iphone|ipad|ipod/i.test(navigator.userAgent);
  toast(iOS?'Appuyez sur Partager ⬆︎ en bas, puis « Sur l\'écran d\'accueil »'
           :'Menu du navigateur ⋮ → « Ajouter à l\'écran d\'accueil »');
};
load();
{const p=new URLSearchParams(location.search);
 if(p.has('bienvenue'))setTimeout(()=>toast('🎉 Bienvenue ! Votre carte est prête. Ajoutez-la à votre écran d\'accueil 👇'),700);
 else if(p.has('deja'))setTimeout(()=>toast('👋 Vous avez déjà une carte — la voici.'),600);}
// tenir la carte à jour (le commerçant vient d'ajouter un point / une récompense)
setInterval(()=>{if(document.visibilityState==='visible')load();},20000);
document.addEventListener('visibilitychange',()=>{if(document.visibilityState==='visible')load();});
</script>
<?php endif; ?>
</body></html>
