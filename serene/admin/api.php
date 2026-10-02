<?php
/* ============================================================
   Espace Praticien — Sérène · backend cPanel (optionnel)
   ------------------------------------------------------------
   À déposer tel quel dans le même dossier que index.html sur un
   hébergement PHP (cPanel). L'application détecte sa présence et
   bascule alors du mode localStorage au mode serveur :
     - POST action=login    {pass}            → jeton de session
     - GET  action=load     (jeton)           → base JSON complète
     - POST action=save     (jeton, data)     → écrit la base
     - POST action=booking  {prenom, nom, …}  → dépôt public d'un RDV
       (à brancher sur le formulaire du site : les demandes tombent
        directement dans l'espace praticien, plus besoin d'e-mail).
   Stockage : fichier data/espace.json (créé automatiquement),
   mot de passe haché (password_hash) dans data/auth.json.
   ============================================================ */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');

$DIR = __DIR__ . '/data';
if (!is_dir($DIR)) { mkdir($DIR, 0755, true); file_put_contents($DIR . '/.htaccess', "Deny from all\n"); }
$DATA = $DIR . '/espace.json';
$AUTH = $DIR . '/auth.json';
$MDP_INITIAL = 'serene2026'; // ⚠️ à changer dès la première connexion

function out(array $x, int $code = 200): void { http_response_code($code); echo json_encode($x, JSON_UNESCAPED_UNICODE); exit; }
function body(): array { $b = json_decode(file_get_contents('php://input') ?: '[]', true); return is_array($b) ? $b : []; }

if (!file_exists($AUTH)) file_put_contents($AUTH, json_encode(['hash' => password_hash($MDP_INITIAL, PASSWORD_DEFAULT), 'token' => '']));
$auth = json_decode(file_get_contents($AUTH), true);

$action = $_GET['action'] ?? ($_POST['action'] ?? (body()['action'] ?? 'ping'));

switch ($action) {
  case 'ping':
    out(['ok' => true, 'mode' => 'serveur']);

  case 'login': {
    $b = body();
    if (!password_verify((string)($b['pass'] ?? ''), $auth['hash'])) { sleep(1); out(['ok' => false, 'err' => 'Mot de passe incorrect'], 401); }
    $auth['token'] = bin2hex(random_bytes(24));
    file_put_contents($AUTH, json_encode($auth));
    out(['ok' => true, 'token' => $auth['token']]);
  }

  case 'password': {
    $b = body();
    if (($b['token'] ?? '') !== $auth['token'] || $auth['token'] === '') out(['ok' => false], 401);
    if (strlen((string)($b['pass'] ?? '')) < 8) out(['ok' => false, 'err' => '8 caractères minimum'], 400);
    $auth['hash'] = password_hash((string)$b['pass'], PASSWORD_DEFAULT);
    file_put_contents($AUTH, json_encode($auth));
    out(['ok' => true]);
  }

  case 'load': {
    if (($_GET['token'] ?? '') !== $auth['token'] || $auth['token'] === '') out(['ok' => false], 401);
    out(['ok' => true, 'data' => file_exists($DATA) ? json_decode(file_get_contents($DATA), true) : null]);
  }

  case 'save': {
    $b = body();
    if (($b['token'] ?? '') !== $auth['token'] || $auth['token'] === '') out(['ok' => false], 401);
    file_put_contents($DATA, json_encode($b['data'] ?? [], JSON_UNESCAPED_UNICODE));
    out(['ok' => true]);
  }

  /* Dépôt PUBLIC d'une demande de rendez-vous (formulaire du site).
     Validation minimale + anti-spam très simple (honeypot "web"). */
  case 'booking': {
    $b = body();
    if (!empty($b['web'])) out(['ok' => true]); // robot piégé
    $prenom = trim((string)($b['prenom'] ?? ''));
    $tel = trim((string)($b['tel'] ?? ''));
    if ($prenom === '' || $tel === '') out(['ok' => false, 'err' => 'Prénom et téléphone requis'], 400);
    $db = file_exists($DATA) ? json_decode(file_get_contents($DATA), true) : ['rdv' => []];
    $db['rdv'][] = [
      'id' => uniqid('r'), 'prenom' => $prenom, 'nom' => trim((string)($b['nom'] ?? '')),
      'tel' => $tel, 'email' => trim((string)($b['email'] ?? '')),
      'soin' => (string)($b['soin'] ?? ''), 'date' => (string)($b['date'] ?? ''),
      'heure' => (string)($b['heure'] ?? ''), 'message' => trim((string)($b['message'] ?? '')),
      'source' => (string)($b['source'] ?? ''), 'statut' => 'nouveau', 'notes' => '',
      'rappel' => false, 'naissance' => (string)($b['naissance'] ?? ''), 'createdAt' => date('Y-m-d'),
    ];
    file_put_contents($DATA, json_encode($db, JSON_UNESCAPED_UNICODE));
    /* notification e-mail au cabinet (si mail() est disponible) */
    $to = $db['settings']['emailNotif'] ?? '';
    if ($to) @mail($to, 'Nouveau rendez-vous — ' . $prenom, "Nouvelle demande de RDV via le site.\nNom : $prenom\nTéléphone : $tel", 'From: site@' . ($_SERVER['HTTP_HOST'] ?? 'serene'));
    out(['ok' => true]);
  }

  default:
    out(['ok' => false, 'err' => 'Action inconnue'], 404);
}
