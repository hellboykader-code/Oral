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
      <?php foreach ($links as [$k,$href,$lbl]): ?><a href="<?= e($href) ?>"<?= $on($k) ?>><?= e($lbl) ?></a><?php endforeach; ?>
    </nav>
    <span class="sp"></span>
    <div class="act">
      <a class="b g" href="<?= e($base) ?>/index.php">Se connecter</a>
      <a class="b p" href="<?= e($base) ?>/inscription.php">Essai gratuit</a>
    </div>
    <button class="burger" id="fnavBurger" aria-label="Menu" aria-expanded="false"><span></span><span></span><span></span></button>
  </div>
  <div class="drop"><div class="din">
    <?php foreach ($links as [$k,$href,$lbl]): ?><a href="<?= e($href) ?>"<?= $on($k) ?>><?= e($lbl) ?></a><?php endforeach; ?>
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
</script>
  <?php
}
