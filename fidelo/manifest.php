<?php
/* Manifeste PWA dynamique.
   - Sans paramètre  → espace commerçant (index.php).
   - ?c=<card>       → carte de fidélité du CLIENT (carte.php?c=…), pour
                       « Ajouter à l'écran d'accueil » côté client. */
require __DIR__ . '/lib.php';
header('Content-Type: application/manifest+json; charset=utf-8');
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$card = preg_replace('/[^A-Za-z0-9_-]/', '', $_GET['c'] ?? '');

if ($card !== '') {
  $db = db_load();
  $found = client_by_card($db, $card);
  $shopName = $found ? $found['shop']['name'] : 'Ma carte';
  echo json_encode([
    'name' => 'Carte ' . $shopName,
    'short_name' => mb_substr($shopName, 0, 16),
    'description' => 'Ma carte de fidélité ' . $shopName,
    'start_url' => ($base ?: '') . '/carte.php?c=' . $card,
    'scope' => ($base ?: '') . '/carte.php',
    'display' => 'standalone',
    'orientation' => 'portrait',
    'background_color' => '#0A2E38',
    'theme_color' => '#0A2E38',
    'icons' => [
      ['src' => ($base ?: '') . '/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
      ['src' => ($base ?: '') . '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
      ['src' => ($base ?: '') . '/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  exit;
}

echo json_encode([
  'name' => 'Fidelo — Espace commerçant',
  'short_name' => 'Fidelo',
  'description' => 'Scannez, créditez des points, fidélisez.',
  'start_url' => ($base ?: '') . '/index.php',
  'scope' => ($base ?: '') . '/',
  'display' => 'standalone',
  'orientation' => 'portrait',
  'background_color' => '#0A2E38',
  'theme_color' => '#0A2E38',
  'icons' => [
    ['src' => ($base ?: '') . '/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
    ['src' => ($base ?: '') . '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
