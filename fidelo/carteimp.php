<?php
/* Fidelo — carte physique imprimable d'un client (format carte bancaire).
   Le QR encode FIDELO:<card> : le commerçant la SCANNE pour créditer un point.
   Utile pour les clients sans smartphone / vieux téléphone. */
require __DIR__ . '/lib.php';
$card = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['c'] ?? '');
$db = db_load();
$found = client_by_card($db, $card);
if (!$found) { http_response_code(404); exit('Carte introuvable.'); }
$shop = $found['shop']; $client = $found['client'];
$rw = $shop['rewards'] ?? []; usort($rw, fn($a,$b)=>$a['pts']-$b['pts']);
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="<?= e($base) ?>/favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="<?= e($base) ?>/logo.svg"><link rel="apple-touch-icon" href="<?= e($base) ?>/apple-touch-icon.png">
<title>Carte — <?= e($client['name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&family=Instrument+Sans:wght@400;500;600&display=swap">
<script src="qrcode.min.js"></script>
<style>
*{box-sizing:border-box}body{margin:0;font-family:"Instrument Sans",system-ui,sans-serif;background:#e8e6e0;color:#0A2E38}
.bar{max-width:560px;margin:0 auto;padding:16px 20px;display:flex;gap:10px;align-items:center}
.bar .t{font-family:"Bricolage Grotesque",sans-serif;font-weight:700;font-size:18px}.bar .t i{color:#D9A94E;font-style:normal}
.btn{margin-left:auto;padding:11px 18px;border-radius:11px;background:#06B6D4;color:#fff;font-weight:600;border:0;cursor:pointer;font-size:14px}
.stage{max-width:560px;margin:0 auto 40px;padding:0 20px}
.tip{font-size:13px;color:#5a675f;margin-bottom:14px;text-align:center}
/* carte 85.6 x 54 mm */
.card{width:85.6mm;height:54mm;margin:0 auto;border-radius:5mm;overflow:hidden;position:relative;color:#eef7f2;
  background:linear-gradient(150deg,#0A2E38,#0891B2 90%,#0F3D49 130%);box-shadow:0 12px 30px -12px rgba(0,0,0,.4);padding:5mm}
.card::before{content:"";position:absolute;inset:0;background:radial-gradient(120% 80% at 88% -10%,rgba(217,169,78,.3),transparent 55%)}
.card>*{position:relative;z-index:2}
.chd{display:flex;justify-content:space-between;align-items:flex-start}
.brand{font-family:"Bricolage Grotesque",sans-serif;font-weight:700;font-size:5mm}.brand i{color:#e3ba63;font-style:normal}
.shop{font-size:3mm;color:rgba(238,247,242,.7);margin-top:.5mm}
.qr{position:absolute;top:5mm;right:5mm;background:#fff;padding:1.5mm;border-radius:2mm}
.qr img{display:block;width:18mm;height:18mm;image-rendering:pixelated}
.who{position:absolute;bottom:5mm;left:5mm}
.who .lb{font-size:2.4mm;letter-spacing:.1em;text-transform:uppercase;color:rgba(238,247,242,.6)}
.who .nm{font-family:"Bricolage Grotesque",sans-serif;font-weight:600;font-size:5.5mm;color:#fff}
.who .id{font-size:2.6mm;color:rgba(238,247,242,.55);font-family:monospace;margin-top:.5mm}
.back{margin-top:16px;font-size:12px;color:#5a675f;text-align:center}
@media print{body{background:#fff}.bar,.tip,.back{display:none}.stage{padding:0}.card{box-shadow:none;margin:10mm auto}}
.flogo{width:1.35em;height:1.35em;display:inline-block;vertical-align:-.32em;margin-right:.32em}
</style></head><body>
<div class="bar"><span class="t"><svg class="flogo" viewBox="0 0 64 64" aria-hidden="true"><defs><linearGradient id="cia" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#06B6D4"/><stop offset=".55" stop-color="#22D3EE"/><stop offset="1" stop-color="#FF6B6B"/></linearGradient><linearGradient id="cib" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".3"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#cia)"/><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#cib)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>Fidelo<i>.</i></span><button class="btn" onclick="window.print()">🖨️ Imprimer la carte</button></div>
<div class="stage">
  <div class="tip">Imprimez, découpez (format carte bancaire), remettez au client. Le commerçant scanne ce QR pour créditer les points.</div>
  <div class="card">
    <div class="chd"><div><div class="brand">Fidelo<i>.</i></div><div class="shop"><?= e($shop['name']) ?> · Fidélité</div></div></div>
    <div class="qr"><div id="qr"></div></div>
    <div class="who"><div class="lb">Membre</div><div class="nm"><?= e($client['name']) ?></div><div class="id"><?= e($card) ?></div></div>
  </div>
  <div class="back">💡 Astuce : collez une étiquette NFC (NTAG213) au dos, programmée avec le lien de la carte, pour un « tap » sans scan.</div>
</div>
<script>
const q=qrcode(0,'M');q.addData('FIDELO:'+<?= json_encode($card) ?>);q.make();
document.getElementById('qr').innerHTML=q.createImgTag(4,0).replace('<img','<img alt="Code"');
</script>
</body></html>
