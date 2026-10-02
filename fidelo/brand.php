<?php
/* ==================================================================
   Fidelo — identité visuelle de chaque commerce (PNG générés).
     brand.php?s=<shopId>&t=logo   → 660×660  (logo rond du programme)
     brand.php?s=<shopId>&t=hero   → 1032×336 (bandeau de la carte)
   Si le commerçant a téléversé SON logo, c'est celui-là qui sert ;
   sinon on dessine un monogramme soigné aux couleurs du commerce.
   Ces URL sont lues par Google Wallet ET par la carte du client.
   ================================================================== */
require __DIR__ . '/lib.php';

$sid = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['s'] ?? '');
$type = ($_GET['t'] ?? 'logo') === 'hero' ? 'hero' : 'logo';
$dir = __DIR__ . '/data/brand';
$up  = $dir . '/' . $sid . '-logo.png';          // logo téléversé par le commerçant

/* ⚡ Le cache disque est consulté AVANT d'ouvrir la base : une carte client
   demande deux images, inutile de relire les données du site à chaque fois. */
$stamp = (int)($_GET['v'] ?? 0);
$cache = $dir . '/' . $sid . '-' . $type . '-' . $stamp . '.png';
if ($stamp > 0 && is_file($cache)) {
  header('Content-Type: image/png');
  header('Cache-Control: public, max-age=31536000, immutable');
  readfile($cache); exit;
}

$db = db_load();
$shop = null;
foreach ($db['shops'] as $s) if ($s['id'] === $sid) { $shop = $s; break; }
if (!$shop) { http_response_code(404); exit; }
$stamp = (int)($shop['brandAt'] ?? 0);
$cache = $dir . '/' . $sid . '-' . $type . '-' . $stamp . '.png';
if (is_file($cache)) {
  header('Content-Type: image/png');
  header('Cache-Control: public, max-age=31536000, immutable');
  readfile($cache); exit;
}
if (!is_dir($dir)) @mkdir($dir, 0775, true);

$FB = __DIR__ . '/fonts/brand-bold.ttf';
$FR = __DIR__ . '/fonts/brand-regular.ttf';
$hasFont = is_file($FB) && function_exists('imagettftext');
[$dark, $light] = brand_colors($shop);

/* ---------- LOGO 660×660 ---------- */
if ($type === 'logo') {
  $N = 660;
  $im = imagecreatetruecolor($N, $N);
  imagesavealpha($im, true);
  imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));

  if (is_file($up) && ($src = @imagecreatefrompng($up))) {
    // logo du commerçant, centré dans un disque blanc
    $disc = imagecolorallocate($im, 255, 255, 255);
    imagefilledellipse($im, $N/2, $N/2, $N, $N, $disc);
    $sw = imagesx($src); $sh = imagesy($src);
    $k = min(($N*0.66)/$sw, ($N*0.66)/$sh);
    $nw = (int)($sw*$k); $nh = (int)($sh*$k);
    imagecopyresampled($im, $src, (int)(($N-$nw)/2), (int)(($N-$nh)/2), 0, 0, $nw, $nh, $sw, $sh);
    imagedestroy($src);
  } else {
    // dégradé vertical dans un disque + monogramme
    [$r1,$g1,$b1] = rgb($dark); [$r2,$g2,$b2] = rgb($light);
    $tmp = imagecreatetruecolor($N, $N);
    for ($y = 0; $y < $N; $y++) {
      $p = $y / ($N - 1);
      imageline($tmp, 0, $y, $N, $y, imagecolorallocate($tmp,
        (int)($r1+($r2-$r1)*$p), (int)($g1+($g2-$g1)*$p), (int)($b1+($b2-$b1)*$p)));
    }
    // masque circulaire
    $mask = imagecreatetruecolor($N, $N);
    imagesavealpha($mask, true);
    imagefill($mask, 0, 0, imagecolorallocatealpha($mask, 0, 0, 0, 127));
    imagefilledellipse($mask, $N/2, $N/2, $N, $N, imagecolorallocate($mask, 255, 255, 255));
    for ($y = 0; $y < $N; $y++) for ($x = 0; $x < $N; $x++) {
      if (((imagecolorat($mask, $x, $y) >> 24) & 0x7F) < 64)
        imagesetpixel($im, $x, $y, imagecolorat($tmp, $x, $y));
    }
    imagedestroy($tmp); imagedestroy($mask);
    // anneau clair
    imagesetthickness($im, 10);
    imageellipse($im, $N/2, $N/2, $N-40, $N-40, imagecolorallocatealpha($im, 255,255,255, 96));
    $mg = monogram($shop['name']);
    if ($hasFont) {
      $size = mb_strlen($mg) > 1 ? 210 : 260;
      $bb = imagettfbbox($size, 0, $FB, $mg);
      $w = $bb[2]-$bb[0]; $h = $bb[1]-$bb[7];
      imagettftext($im, $size, 0, (int)(($N-$w)/2 - $bb[0]), (int)(($N+$h)/2 - ($bb[1]-$bb[7]) + $h - 8),
        imagecolorallocate($im, 255,255,255), $FB, $mg);
    }
  }
  imagepng($im, $cache, 9);
  header('Content-Type: image/png'); header('Cache-Control: public, max-age=31536000, immutable');
  readfile($cache); imagedestroy($im); exit;
}

/* ---------- HERO 1032×336 ---------- */
$W = 1032; $H = 336;
$im = imagecreatetruecolor($W, $H);
[$r1,$g1,$b1] = rgb($dark); [$r2,$g2,$b2] = rgb($light);
for ($x = 0; $x < $W; $x++) {                    // dégradé diagonal doux
  for ($y = 0; $y < $H; $y += 8) {
    $p = min(1, max(0, ($x/$W)*0.75 + ($y/$H)*0.25));
    imagefilledrectangle($im, $x, $y, $x, $y+7, imagecolorallocate($im,
      (int)($r1+($r2-$r1)*$p), (int)($g1+($g2-$g1)*$p), (int)($b1+($b2-$b1)*$p)));
  }
}
// arcs décoratifs discrets
imagesetthickness($im, 3);
$soft = imagecolorallocatealpha($im, 255, 255, 255, 108);
for ($i = 0; $i < 5; $i++) imageellipse($im, $W-150, $H/2, 260+$i*90, 260+$i*90, $soft);
// texte
if ($hasFont) {
  $name = $shop['name'];
  $size = 58; $bb = imagettfbbox($size, 0, $FB, $name);
  while (($bb[2]-$bb[0]) > $W-300 && $size > 26) { $size -= 2; $bb = imagettfbbox($size, 0, $FB, $name); }
  imagettftext($im, $size, 0, 70, 175, imagecolorallocate($im, 255,255,255), $FB, $name);
  if (is_file($FR)) {
    imagettftext($im, 22, 0, 72, 225, imagecolorallocatealpha($im, 255,255,255, 40), $FR,
      mb_strtoupper($shop['type'] ?? 'Commerce') . '  ·  CARTE DE FIDÉLITÉ');
  }
}
imagepng($im, $cache, 9);
header('Content-Type: image/png'); header('Cache-Control: public, max-age=31536000, immutable');
readfile($cache); imagedestroy($im);
