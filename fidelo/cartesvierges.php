<?php
/* Fidelo — planche de cartes VIERGES pré-imprimées (format carte bancaire).
   Chaque QR encode FIDELO:<card> comme une carte normale : le commerçant
   scanne cette carte plus tard (côté client absent) pour l'activer avec
   le nom du client (voir blank_activate dans api.php). Pas de nom dessus :
   c'est justement le principe — on les imprime À L'AVANCE, en stock. */
require __DIR__ . '/lib.php';
$ids = array_filter(array_map('trim', explode(',', $_GET['ids'] ?? '')));
$ids = array_slice($ids, 0, 200);
$db = db_load();

$shop = null; $cards = [];
foreach ($ids as $token) {
  $token = preg_replace('/[^A-Za-z0-9_-]/', '', $token);
  if ($token === '') continue;
  $found = client_by_card($db, $token);
  if (!$found || empty($found['client']['blank'])) continue;   // déjà activée ou inconnue : on l'ignore
  if ($shop === null) $shop = $found['shop'];
  elseif ($found['shop']['id'] !== $shop['id']) continue;      // sécurité : un seul commerce par planche
  $cards[] = $token;
}
if (!$shop || !$cards) { http_response_code(404); exit('Aucune carte vierge à afficher (déjà activées, ou lien invalide).'); }
?>
<!doctype html><html lang="fr"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" href="favicon.ico" sizes="any"><link rel="icon" type="image/svg+xml" href="logo.svg"><link rel="apple-touch-icon" href="apple-touch-icon.png">
<title>Cartes vierges — <?= e($shop['name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Calistoga&family=Inter:wght@400;500;600&display=swap">
<script src="qrcode.min.js"></script>
<style>
*{box-sizing:border-box}body{margin:0;font-family:"Inter",system-ui,sans-serif;background:#e8e6e0;color:#241A12}
.bar{max-width:900px;margin:0 auto;padding:16px 20px;display:flex;gap:10px;align-items:center}
.bar .t{font-family:"Calistoga",sans-serif;font-weight:700;font-size:18px}.bar .t i{color:#D9A94E;font-style:normal}
.btn{margin-left:auto;padding:11px 18px;border-radius:11px;background:#C1552F;color:#fff;font-weight:600;border:0;cursor:pointer;font-size:14px}
.stage{max-width:900px;margin:0 auto 40px;padding:0 20px}
.tip{font-size:13px;color:#6E5B47;margin-bottom:18px;text-align:center}
.grid{display:grid;grid-template-columns:repeat(2,85.6mm);gap:8mm;justify-content:center}
/* carte 85.6 x 54 mm, identique au format des cartes clients imprimées */
.card{width:85.6mm;height:54mm;border-radius:5mm;overflow:hidden;position:relative;color:#F3ECE1;
  background:linear-gradient(150deg,#241A12,#9C4024 90%,#3A2A1D 130%);box-shadow:0 12px 30px -12px rgba(0,0,0,.4);padding:5mm;
  break-inside:avoid;page-break-inside:avoid}
.card::before{content:"";position:absolute;inset:0;background:radial-gradient(120% 80% at 88% -10%,rgba(217,169,78,.3),transparent 55%)}
.card>*{position:relative;z-index:2}
.chd{display:flex;justify-content:space-between;align-items:flex-start}
.brand{font-family:"Calistoga",sans-serif;font-weight:700;font-size:5mm}.brand i{color:#e3ba63;font-style:normal}
.shop{font-size:3mm;color:rgba(243,236,225,.7);margin-top:.5mm}
.qr{position:absolute;top:5mm;right:5mm;background:#fff;padding:1.5mm;border-radius:2mm}
.qr img{display:block;width:18mm;height:18mm;image-rendering:pixelated}
.who{position:absolute;bottom:5mm;left:5mm}
.who .lb{font-size:2.4mm;letter-spacing:.1em;text-transform:uppercase;color:rgba(243,236,225,.6)}
.who .nm{font-family:"Calistoga",sans-serif;font-weight:600;font-size:4.6mm;color:rgba(255,255,255,.85);font-style:italic}
.who .id{font-size:2.6mm;color:rgba(243,236,225,.55);font-family:monospace;margin-top:.5mm}
.back{margin-top:18px;font-size:12px;color:#6E5B47;text-align:center}
@media print{body{background:#fff}.bar,.tip,.back{display:none}.stage{padding:0}.grid{gap:6mm}.card{box-shadow:none}}
.flogo{width:1.35em;height:1.35em;display:inline-block;vertical-align:-.32em;margin-right:.32em}
</style></head><body>
<div class="bar"><span class="t"><svg class="flogo" viewBox="0 0 64 64" aria-hidden="true"><defs><linearGradient id="cia" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#C1552F"/><stop offset=".55" stop-color="#E08A5D"/><stop offset="1" stop-color="#FF6B6B"/></linearGradient><linearGradient id="cib" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".3"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/></linearGradient></defs><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#cia)"/><rect x="3" y="13" width="58" height="40" rx="11" fill="url(#cib)"/><rect x="11" y="24" width="24" height="4.4" rx="2.2" fill="#fff" opacity=".95"/><rect x="11" y="33" width="14" height="4.4" rx="2.2" fill="#fff" opacity=".6"/><path d="M45 16.4C45.5 18 46.4 18.9 54.6 26C46.4 33.1 45.5 34 45 35.6C44.5 34 43.6 33.1 35.4 26C43.6 18.9 44.5 18 45 16.4Z" fill="#fff"/><path d="M53.4 34.1C53.6 34.8 54 35.2 57.8 38.5C54 41.8 53.6 42.2 53.4 42.9C53.2 42.2 52.8 41.8 49 38.5C52.8 35.2 53.2 34.8 53.4 34.1Z" fill="#fff" opacity=".88"/></svg>Fidelo<i>.</i></span><button class="btn" onclick="window.print()">🖨️ Imprimer <?= count($cards) ?> carte<?= count($cards)>1?'s':'' ?></button></div>
<div class="stage">
  <div class="tip">Imprimez, découpez (format carte bancaire), gardez en stock au comptoir. Quand vous remettez une carte à un client, scannez-la dans l'app pour l'activer avec son nom.</div>
  <div class="grid">
    <?php foreach ($cards as $i => $token): ?>
    <div class="card">
      <div class="chd"><div><div class="brand">Fidelo<i>.</i></div><div class="shop"><?= e($shop['name']) ?> · Fidélité</div></div></div>
      <div class="qr"><div id="qr<?= $i ?>"></div></div>
      <div class="who"><div class="lb">Carte disponible</div><div class="nm">Non attribuée</div><div class="id"><?= e($token) ?></div></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="back">💡 Astuce : collez une étiquette NFC (NTAG213) au dos de chaque carte, programmée avec son lien (<span class="mono">carte.php?c=<?= e($cards[0]) ?></span>), pour un « tap » sans scan.</div>
</div>
<script>
const tokens = <?= json_encode($cards) ?>;
tokens.forEach((t,i)=>{
  const q=qrcode(0,'M');q.addData('FIDELO:'+t);q.make();
  document.getElementById('qr'+i).innerHTML=q.createImgTag(4,0).replace('<img','<img alt="Code"');
});
</script>
</body></html>
