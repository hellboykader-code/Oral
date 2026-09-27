<?php
/* Fidelo — habillage commun des pages légales (mentions, confidentialité).
   N'affiche rien par lui-même : fournit legal_open() / legal_close(). */
require_once __DIR__ . '/lib.php';
require_once __DIR__ . '/nav.php';

function legal_icon(): string {
  return '<svg class="flogo" viewBox="0 0 64 64" aria-hidden="true"><defs><linearGradient id="lga" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#06B6D4"/><stop offset=".55" stop-color="#22D3EE"/><stop offset="1" stop-color="#FF6B6B"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#lga)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>';
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
<meta name="theme-color" content="#06B6D4">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap">
<style>
:root{
  --tq:#06B6D4;--tq-d:#0891B2;--co:#FF6B6B;--am-d:#F7B500;
  --ink-fixed:#0A2E38;--accent-txt:#0B7C97;
  --bg:#F3FCFD;--bg-2:#E7F8FA;--card:#ffffff;--line:#D6EEF1;
  --text:#0F343D;--muted:#4e767f;--faint:#7d9ea7;
  --disp:"Bricolage Grotesque",-apple-system,system-ui,sans-serif;
  --body:"Plus Jakarta Sans",-apple-system,system-ui,sans-serif;--mono:"IBM Plex Mono",monospace;
  --grad:linear-gradient(118deg,#06B6D4 0%,#22D3EE 38%,#FF6B6B 100%);
}
@media(prefers-color-scheme:dark){:root{
  --tq:#22D3EE;--tq-d:#06B6D4;--co:#FF8080;--accent-txt:#7DE9FB;--ink-fixed:#04161c;
  --bg:#07222a;--bg-2:#0b2c35;--card:#0e323c;--line:#1c4650;
  --text:#EAFBFD;--muted:#9fc4cc;--faint:#6f939b;}}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:var(--body);-webkit-font-smoothing:antialiased;line-height:1.7}
a{color:var(--accent-txt);text-decoration:none;font-weight:600}
a:hover{text-decoration:underline}
.wrap{max-width:820px;margin:0 auto;padding:0 22px}
.flogo{width:1.25em;height:1.25em;display:inline-block;vertical-align:-.28em;margin-right:.3em}
.top{display:flex;align-items:center;gap:16px;padding:20px 0;max-width:1180px;margin:0 auto}
.brand{font-family:var(--disp);font-weight:800;font-size:23px;letter-spacing:-.02em;color:var(--text);text-decoration:none}
.brand .d{color:var(--co)}
.top .sp{margin-left:auto}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:11px 19px;border-radius:12px;font-weight:700;font-size:14.5px;border:0;text-decoration:none}
.btn-g{background:var(--card);border:1.6px solid var(--line);color:var(--text)}
.head{padding:34px 0 8px}
.eyebrow{font-family:var(--mono);font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:var(--accent-txt);font-weight:600}
h1{font-family:var(--disp);font-weight:800;font-size:clamp(30px,5vw,44px);line-height:1.08;letter-spacing:-.03em;margin:12px 0 0}
.chapeau{color:var(--muted);font-size:17px;margin-top:14px}
.maj{font-family:var(--mono);font-size:12.5px;color:var(--faint);margin-top:18px}
h2{font-family:var(--disp);font-weight:700;font-size:23px;letter-spacing:-.02em;margin:42px 0 0;padding-top:22px;border-top:1px solid var(--line)}
h3{font-family:var(--disp);font-weight:600;font-size:17.5px;margin:26px 0 0}
p{color:var(--text);font-size:15.5px;margin:12px 0 0}
ul{margin:12px 0 0;padding-left:20px}
li{font-size:15.5px;margin-top:7px}
.box{background:var(--card);border:1.5px solid var(--line);border-radius:16px;padding:20px 22px;margin-top:18px}
.box.key{border-color:var(--tq);background:var(--bg-2)}
.box p:first-child,.box ul:first-child{margin-top:0}
table{width:100%;border-collapse:collapse;margin-top:16px;background:var(--card);border-radius:14px;overflow:hidden;font-size:14.5px;border:1px solid var(--line)}
th,td{padding:12px 14px;text-align:left;border-bottom:1px solid var(--line);vertical-align:top}
thead th{background:var(--ink-fixed);color:#EAFBFD;font-family:var(--disp);font-weight:600;font-size:14.5px}
tbody tr:last-child td{border-bottom:0}
@media(max-width:700px){table{font-size:13px}th,td{padding:9px 10px}}
.foot{padding:46px 0 60px;margin-top:56px;border-top:2px solid var(--line);color:var(--muted);font-size:13.5px;text-align:center}
.foot .l{display:flex;gap:18px;justify-content:center;flex-wrap:wrap;margin-bottom:12px}
</style></head><body>

<?php nav_bar($base, ''); ?>

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
<footer class="foot"><div class="wrap">
  <div class="l">
    <a href="<?= e($base) ?>/">Accueil</a>
    <a href="<?= e($base) ?>/tarifs.php">Nos offres</a>
    <a href="<?= e($base) ?>/confidentialite.php">Confidentialité</a>
    <a href="<?= e($base) ?>/mentions-legales.php">Mentions légales</a>
    <a href="mailto:<?= e($E['email']) ?>"><?= e($E['email']) ?></a>
  </div>
  <p style="margin:0">© <?= date('Y') ?> <?= e($E['marque']) ?> — <?= e($E['societe']) ?>. <?= e($E['tva']) ?>.</p>
</div></footer>
</body></html>
<?php
}
