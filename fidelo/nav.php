<?php
/* Fidelo — barre de navigation publique, partagée par toutes les pages.
   Utilisation :  nav_bar($base, 'accueil'|'services'|'tarifs'|'contact');
   S'appuie sur les tokens de design déjà définis par chaque page
   (--card, --line, --text, --tq, --grad, --co, --muted, --bg-2, --accent-txt). */

function nav_css_once(): string {
  static $done = false;
  if ($done) return '';
  $done = true;
  return <<<CSS
<style>
.fnav{position:sticky;top:0;z-index:120;background:color-mix(in srgb,var(--card) 90%,transparent);
  backdrop-filter:saturate(1.4) blur(12px);-webkit-backdrop-filter:saturate(1.4) blur(12px);
  border-bottom:1px solid var(--line)}
.fnav .in{max-width:1180px;margin:0 auto;padding:0 22px;height:64px;display:flex;align-items:center;gap:22px}
.fnav .lg{display:flex;align-items:center;font-family:var(--disp,"Bricolage Grotesque",sans-serif);
  font-weight:800;font-size:21px;letter-spacing:-.02em;color:var(--text);white-space:nowrap}
.fnav .lg .d{color:var(--co)}
.fnav .lg svg{width:1.3em;height:1.3em;margin-right:.34em}
.fnav .lk{display:flex;align-items:center;gap:4px;margin-left:8px}
.fnav .lk a{font-size:14.5px;font-weight:600;color:var(--muted);padding:9px 13px;border-radius:9px;transition:.15s;white-space:nowrap}
.fnav .lk a:hover{color:var(--text);background:var(--bg-2)}
.fnav .lk a.on{color:var(--accent-txt);background:var(--bg-2)}
.fnav .sp{flex:1}
.fnav .act{display:flex;align-items:center;gap:10px}
.fnav .b{display:inline-flex;align-items:center;justify-content:center;padding:10px 17px;border-radius:11px;
  font-weight:700;font-size:14px;border:0;cursor:pointer;white-space:nowrap;font-family:inherit;transition:transform .15s,box-shadow .2s}
.fnav .b:hover{transform:translateY(-1px)}
.fnav .b.g{background:transparent;border:1.6px solid var(--line);color:var(--text)}
.fnav .b.p{background:var(--grad);color:#fff;box-shadow:0 12px 26px -12px rgba(6,182,212,.7)}
.fnav .burger{display:none;margin-left:auto;width:44px;height:44px;border:1.6px solid var(--line);border-radius:12px;
  background:var(--card);cursor:pointer;align-items:center;justify-content:center;flex-direction:column;gap:4px}
.fnav .burger span{display:block;width:20px;height:2px;background:var(--text);border-radius:2px;transition:.25s}
.fnav.open .burger span:nth-child(1){transform:translateY(6px) rotate(45deg)}
.fnav.open .burger span:nth-child(2){opacity:0}
.fnav.open .burger span:nth-child(3){transform:translateY(-6px) rotate(-45deg)}
.fnav .drop{display:none}
@media(max-width:860px){
  .fnav .lk,.fnav .act{display:none}
  .fnav .burger{display:flex}
  .fnav .drop{display:block;position:absolute;left:0;right:0;top:64px;background:var(--card);
    border-bottom:1px solid var(--line);box-shadow:0 24px 40px -22px rgba(10,46,56,.4);
    max-height:0;overflow:hidden;transition:max-height .28s ease}
  .fnav.open .drop{max-height:70vh}
  .fnav .drop .din{padding:10px 22px 18px;display:flex;flex-direction:column;gap:4px}
  .fnav .drop a{font-size:16px;font-weight:600;color:var(--text);padding:13px 12px;border-radius:11px}
  .fnav .drop a:hover,.fnav .drop a.on{background:var(--bg-2);color:var(--accent-txt)}
  .fnav .drop .sep{height:1px;background:var(--line);margin:8px 0}
  .fnav .drop .b{width:100%;margin-top:4px;padding:14px;font-size:15px}
}
::view-transition-old(root),::view-transition-new(root){animation-duration:.45s;animation-timing-function:cubic-bezier(.16,1,.3,1)}
@media(prefers-reduced-motion:reduce){::view-transition-old(root),::view-transition-new(root){animation:none!important}}
</style>
CSS;
}

function nav_logo_svg(): string {
  return '<svg viewBox="0 0 64 64" aria-hidden="true"><defs><linearGradient id="fnvg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#06B6D4"/><stop offset=".55" stop-color="#22D3EE"/><stop offset="1" stop-color="#FF6B6B"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#fnvg)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>';
}

/** Rend la barre de navigation. $active ∈ accueil|services|tarifs|contact */
function nav_bar(string $base, string $active = ''): void {
  echo nav_css_once();
  $on = fn($k) => $active === $k ? ' class="on"' : '';
  $links = [
    ['accueil',  $base . '/accueil.php',            'Accueil'],
    ['services', $base . '/accueil.php#comment',    'Services'],
    ['tarifs',   $base . '/tarifs.php',             'Tarifs'],
    ['contact',  $base . '/contact.php',            'Contact'],
  ];
  ?>
<header class="fnav" id="fnav">
  <div class="in">
    <a class="lg" href="<?= e($base) ?>/accueil.php"><?= nav_logo_svg() ?>Fidelo<span class="d">.</span></a>
    <nav class="lk">
      <?php foreach ($links as [$k,$href,$lbl]): ?><a href="<?= e($href) ?>" data-key="<?= e($k) ?>"<?= $on($k) ?>><?= e($lbl) ?></a><?php endforeach; ?>
    </nav>
    <span class="sp"></span>
    <div class="act">
      <a class="b g" href="<?= e($base) ?>/index.php">Se connecter</a>
      <a class="b p" href="<?= e($base) ?>/inscription.php">Essai gratuit</a>
    </div>
    <button class="burger" id="fnavBurger" aria-label="Menu" aria-expanded="false"><span></span><span></span><span></span></button>
  </div>
  <div class="drop"><div class="din">
    <?php foreach ($links as [$k,$href,$lbl]): ?><a href="<?= e($href) ?>" data-key="<?= e($k) ?>"<?= $on($k) ?>><?= e($lbl) ?></a><?php endforeach; ?>
    <div class="sep"></div>
    <a class="b g" href="<?= e($base) ?>/index.php">Se connecter</a>
    <a class="b p" href="<?= e($base) ?>/inscription.php">Essai gratuit</a>
  </div></div>
</header>
<script>
(function(){
  var n=document.getElementById('fnav'),b=document.getElementById('fnavBurger');
  if(!n||!b)return;
  b.addEventListener('click',function(){
    var o=n.classList.toggle('open');
    b.setAttribute('aria-expanded',o?'true':'false');
  });
  n.querySelectorAll('.drop a').forEach(function(a){
    a.addEventListener('click',function(){n.classList.remove('open');b.setAttribute('aria-expanded','false');});
  });
})();

/* ===== chrome persistant — barre de progression, bouton remonter, nav qui rétrécit =====
   Lié UNE SEULE FOIS au chargement réel de la page ; ne redémarre jamais lors d'une
   navigation pseudo-SPA (seul <main> est remplacé, ce bloc et #fnav restent en vie). */
(function(){
  var reduce=matchMedia('(prefers-reduced-motion:reduce)').matches;
  var bar=document.getElementById('scrollbar'),up=document.getElementById('up'),fnav=document.getElementById('fnav');
  var tick=false;
  function onScroll(){
    var h=document.documentElement,sc=h.scrollTop||document.body.scrollTop;
    var max=h.scrollHeight-h.clientHeight;
    if(bar)bar.style.width=(max>0?sc/max*100:0)+'%';
    if(up)up.classList.toggle('on',sc>520);
    if(fnav)fnav.classList.toggle('scrolled',sc>10);
    tick=false;
  }
  addEventListener('scroll',function(){if(!tick){tick=true;requestAnimationFrame(onScroll);}},{passive:true});
  onScroll();
  if(up)up.onclick=function(){scrollTo({top:0,behavior:reduce?'auto':'smooth'});};
})();

/* ===== navigation pseudo-SPA (fetch + remplacement de <main>) =====
   Périmètre : uniquement les pages marketing (accueil/tarifs/contact/légal).
   Tout lien hors périmètre (connexion, inscription, app commerçant…) navigue
   normalement. Les formulaires ne sont JAMAIS interceptés (ce ne sont pas des <a>). */
(function(){
  var SCOPE=['accueil.php','tarifs.php','contact.php','confidentialite.php','mentions-legales.php'];
  var ACTIVE={'accueil.php':'accueil','tarifs.php':'tarifs','contact.php':'contact','confidentialite.php':'','mentions-legales.php':''};
  var BASE=<?= json_encode($base) ?>;
  var reduce=matchMedia('(prefers-reduced-motion:reduce)').matches;
  /* NB : ne PAS mettre <main> en cache ici — nav_bar() s'exécute AVANT que <main>
     existe dans le DOM (le script tourne au moment du parse, juste après <body>).
     On le relit à chaque usage. */
  if(!window.fetch||!window.history||!history.pushState)return;

  try{history.scrollRestoration='manual';}catch(e){}
  try{history.replaceState({fidelo:true,y:window.scrollY||0},'',location.href);}catch(e){}

  function fileOf(pathname){var p=pathname.split('/');return p[p.length-1]||'accueil.php';}
  function setActive(file){
    var key=ACTIVE[file];
    document.querySelectorAll('.fnav .lk a[data-key], .fnav .drop a[data-key]').forEach(function(a){
      a.classList.toggle('on', !!key && a.dataset.key===key);
    });
  }
  var depLoading=null;
  function ensureDeps(doc){
    var needsQr=!!doc.querySelector('script[src*="qrcode.min.js"]');
    if(!needsQr||typeof qrcode!=='undefined')return Promise.resolve();
    if(depLoading)return depLoading;
    depLoading=new Promise(function(res){
      var s=document.createElement('script');s.src=BASE+'/qrcode.min.js';
      s.onload=res;s.onerror=res;document.body.appendChild(s);
    });
    return depLoading;
  }

  var navToken=0;
  function go(url,opts){
    opts=opts||{};
    var push=opts.push!==false,anchor=opts.anchor||'',restoreY=opts.restoreY;
    var myToken=++navToken;
    fetch(url,{credentials:'same-origin'}).then(function(res){
      if(!res.ok)throw new Error('http '+res.status);
      return res.text();
    }).then(function(html){
      if(myToken!==navToken)return;
      var doc=new DOMParser().parseFromString(html,'text/html');
      var newMain=doc.querySelector('main');
      if(!newMain){location.href=url;return;}
      return ensureDeps(doc).then(function(){
        if(myToken!==navToken)return;
        var newScript=doc.getElementById('pageScript');
        var apply=function(){
          try{window.__fideloPageTeardown&&window.__fideloPageTeardown();}catch(e){}
          document.title=doc.title;
          var main=document.querySelector('main');
          if(main)main.innerHTML=newMain.innerHTML;
          var old=document.getElementById('pageScript');if(old)old.remove();
          if(newScript){
            var s=document.createElement('script');s.id='pageScript';s.textContent=newScript.textContent;
            document.body.appendChild(s);
          }
          setActive(fileOf(new URL(url,location.href).pathname));
          if(anchor){var el=document.getElementById(anchor);if(el)el.scrollIntoView({behavior:reduce?'auto':'smooth',block:'start'});}
          else window.scrollTo(0,typeof restoreY==='number'?restoreY:0);
        };
        if(!reduce&&document.startViewTransition){
          try{document.startViewTransition(apply);}catch(e){apply();}
        }else{
          apply();
        }
        if(push){
          try{history.replaceState({fidelo:true,y:window.scrollY||0},'',location.href);}catch(e){}
          var u=new URL(url,location.href);
          try{history.pushState({fidelo:true,y:0},'',u.pathname+u.search+(anchor?'#'+anchor:''));}catch(e){}
        }
      });
    }).catch(function(){location.href=url;});
  }

  document.addEventListener('click',function(e){
    if(e.defaultPrevented||e.button!==0||e.metaKey||e.ctrlKey||e.shiftKey||e.altKey)return;
    var a=e.target.closest('a[href]');
    if(!a)return;
    if(a.target&&a.target!=='_self')return;
    if(a.hasAttribute('download'))return;
    var href=a.getAttribute('href');
    if(!href||/^(mailto:|tel:|javascript:|#)/.test(href))return;
    var url;
    try{url=new URL(href,location.href);}catch(e){return;}
    if(url.origin!==location.origin)return;
    if(SCOPE.indexOf(fileOf(url.pathname))===-1)return;
    var samePath=url.pathname===location.pathname;
    var anchor=url.hash?url.hash.slice(1):'';
    if(samePath&&anchor)return;
    e.preventDefault();
    if(samePath&&!anchor)return;
    go(url.href,{push:true,anchor:anchor});
  });

  addEventListener('popstate',function(ev){
    var anchor=location.hash?location.hash.slice(1):'';
    var y=ev.state&&typeof ev.state.y==='number'?ev.state.y:0;
    go(location.href,{push:false,anchor:anchor,restoreY:y});
  });

  setActive(fileOf(location.pathname));
})();
</script>
  <?php
}
