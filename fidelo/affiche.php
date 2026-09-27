<?php
/* Fidelo — affichette imprimable (QR d'inscription à coller dans le commerce). */
require __DIR__ . '/lib.php';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$s = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['s'] ?? '');
$db = db_load();
$shop = shop_by_join($db, $s);
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$joinUrl = $scheme . '://' . $host . ($base ?: '') . '/rejoindre.php?s=' . $s;
if (!$shop) { http_response_code(404); exit('Commerce introuvable.'); }
$shortUrl = preg_replace('#^https?://#', '', $scheme . '://' . $host . ($base ?: '') . '/j/' . ($shop['shortCode'] ?? ''));
$rw = $shop['rewards'] ?? []; usort($rw, fn($a,$b)=>$a['pts']-$b['pts']);
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title>Affichette — <?= e($shop['name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Calistoga&family=Inter:wght@500;600;700;800&display=swap">
<style>
*{box-sizing:border-box;-webkit-print-color-adjust:exact;print-color-adjust:exact}
:root{--tq:#C1552F;--tq-d:#9C4024;--co:#FF6B6B;--ink:#241A12;--cream:#FBF3E7;--line:#E9DAC3;--muted:#6E5B47;
--grad:linear-gradient(125deg,#C1552F 0%,#E08A5D 45%,#FF6B6B 112%);
--disp:"Calistoga",sans-serif;--body:"Inter",sans-serif}
body{margin:0;font-family:var(--body);background:#e2e7e9;color:var(--ink)}
.bar{max-width:640px;margin:0 auto;padding:16px 20px;display:flex;gap:10px;align-items:center}
.bar .t{display:flex;align-items:center;gap:7px;font-family:var(--disp);font-weight:800;font-size:18px}
.bar .t i{color:var(--co);font-style:normal}.bar .t svg{width:24px;height:24px}
.btn{margin-left:auto;padding:12px 20px;border-radius:12px;background:var(--grad);color:#fff;font-weight:700;border:0;cursor:pointer;font-size:14.5px;font-family:var(--body);box-shadow:0 12px 26px -12px rgba(193,85,47,.7)}
.hint{max-width:640px;margin:0 auto 8px;padding:0 20px;color:var(--muted);font-size:13px}
.sheet{max-width:640px;margin:0 auto 40px;background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 22px 54px -22px rgba(0,0,0,.34)}
.poster{aspect-ratio:210/297;display:flex;flex-direction:column;align-items:center;text-align:center;padding:8.5% 8%;
  background:radial-gradient(80% 42% at 82% 6%,rgba(193,85,47,.10),transparent 60%),#fff}
.top{display:flex;align-items:center;gap:7px;font-family:var(--disp);font-weight:800;font-size:21px;color:var(--ink)}
.top i{color:var(--co);font-style:normal}.top svg{width:27px;height:27px}
.h1{font-family:var(--disp);font-weight:800;font-size:min(8.4vw,38px);line-height:1.04;letter-spacing:-.02em;margin-top:8%}
.shopn{font-family:var(--disp);font-weight:800;font-size:min(5.4vw,23px);color:var(--tq-d);margin-top:12px}
.sub{color:var(--muted);font-size:min(3.9vw,16px);margin-top:10px;max-width:26ch}
.qrbox{width:46%;max-width:230px;aspect-ratio:1;background:#fff;border:2px solid var(--line);border-radius:20px;padding:4%;margin-top:6%;
  box-shadow:0 18px 34px -18px rgba(193,85,47,.5)}
.qrbox svg{display:block;width:100%;height:100%}
.cta{font-family:var(--disp);font-weight:800;font-size:min(4.8vw,20px);color:var(--tq);margin-top:5%}
/* rabais / lien court pour les téléphones anciens */
.link{margin-top:14px;background:var(--cream);border:1.5px solid var(--line);border-radius:12px;padding:10px 16px;font-size:min(3.4vw,14px);color:var(--muted);line-height:1.5}
.link b{color:var(--ink);font-family:var(--body);font-weight:800;letter-spacing:.01em}
/* étapes */
.steps{display:flex;gap:4%;margin-top:6%;width:100%;justify-content:center}
.steps .st{display:flex;flex-direction:column;align-items:center;gap:7px;width:30%;color:var(--muted);font-size:min(3.1vw,12.5px);line-height:1.35}
.steps .n{width:34px;height:34px;border-radius:50%;background:var(--grad);color:#fff;display:flex;align-items:center;justify-content:center;font-family:var(--disp);font-weight:800;font-size:16px}
/* récompenses */
.chips{display:flex;gap:7px;flex-wrap:wrap;justify-content:center;margin-top:6%}
.chips span{background:var(--cream);color:var(--tq-d);padding:6px 13px;border-radius:99px;font-size:min(3.2vw,13px);font-weight:700}
.foot{margin-top:auto;color:#9C8B74;font-size:min(2.9vw,12px);padding-top:5%}
@media print{
  @page{size:A4;margin:0}
  body{background:#fff}.bar,.hint{display:none}
  .sheet{box-shadow:none;border-radius:0;margin:0;max-width:none}
  .poster{aspect-ratio:auto;height:100vh;padding:9% 8%}
}
</style></head><body>
<div class="bar">
  <span class="t"><svg viewBox="0 0 64 64"><defs><linearGradient id="afa" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#C1552F"/><stop offset=".55" stop-color="#E08A5D"/><stop offset="1" stop-color="#FF6B6B"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#afa)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>Fidelo<i>.</i></span>
  <button class="btn" onclick="window.print()">🖨️ Imprimer l'affichette</button>
</div>
<div class="hint">Imprimez en A4, puis posez l'affichette sur votre comptoir. Vos clients scannent avec l'appareil photo de leur téléphone.</div>

<div class="sheet"><div class="poster">
  <div class="top">◆ Fidelo<i>.</i></div>
  <div class="h1">Votre carte de fidélité</div>
  <div class="shopn"><?= e($shop['name']) ?></div>
  <div class="sub">Cumulez des points à chaque visite. C'est gratuit, sans application.</div>

  <div class="qrbox"><div id="qr"></div></div>
  <div class="cta">📸 Scannez avec votre appareil photo</div>

  <div class="link">📱 Ancien téléphone&nbsp;? Ouvrez votre navigateur et tapez&nbsp;:<br><b><?= e($shortUrl) ?></b></div>

  <div class="steps">
    <div class="st"><div class="n">1</div>Scannez le code (ou tapez le lien)</div>
    <div class="st"><div class="n">2</div>Entrez votre prénom &amp; téléphone</div>
    <div class="st"><div class="n">3</div>Votre carte est prête&nbsp;!</div>
  </div>

  <?php if ($rw): ?><div class="chips"><?php foreach (array_slice($rw, 0, 3) as $r): ?><span><?= (int)$r['pts'] ?> pts · <?= e($r['t']) ?></span><?php endforeach; ?></div><?php endif; ?>

  <div class="foot">Aucune application à installer · Propulsé par Fidelo</div>
</div></div>

<script src="qrcode.min.js"></script>
<script>
var url=<?= json_encode($joinUrl) ?>;
var q=qrcode(0,'M');q.addData(url);q.make();
document.getElementById('qr').innerHTML=q.createSvgTag({cellSize:4,margin:0,scalable:true});
</script>
</body></html>
