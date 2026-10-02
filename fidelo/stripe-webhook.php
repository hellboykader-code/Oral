<?php
/* ==================================================================
   Fidelo — Webhook Stripe (paiement confirmé / échoué / annulé).
   Endpoint PUBLIC (Stripe l'appelle sans cookie) : l'authenticité
   vient UNIQUEMENT de la signature HMAC (st_verify_sig), jamais d'une
   session. Répond toujours 200 dès que l'évènement est traité ou
   ignoré à raison, pour éviter les répétitions inutiles de Stripe.
   URL à saisir dans Stripe → Developers → Webhooks :
     https://votre-domaine/stripe-webhook.php
   Évènements à cocher : checkout.session.completed,
   customer.subscription.updated, customer.subscription.deleted.
   ================================================================== */
require __DIR__ . '/lib.php';
require __DIR__ . '/stripe.php';

header('Content-Type: application/json; charset=utf-8');

$payload = file_get_contents('php://input') ?: '';
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

$LOCK = db_lock();
$db = db_load();
$whsec = st_conf($db)['whsec'];

if ($whsec === '' || !st_verify_sig($payload, $sigHeader, $whsec)) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'signature']);
  exit;
}

$event = json_decode($payload, true);
if (!is_array($event) || empty($event['id']) || empty($event['type'])) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'error' => 'payload']);
  exit;
}

/* Évènement déjà traité (Stripe retente tant qu'il ne reçoit pas 200) : on
   répond 200 sans rien rejouer. */
if (st_event_seen($db, $event['id'])) {
  db_save($db);
  echo json_encode(['ok' => true, 'dup' => true]);
  exit;
}

$obj = $event['data']['object'] ?? [];
$type = $event['type'];

if ($type === 'checkout.session.completed') {
  $shopId = $obj['client_reference_id'] ?? ($obj['metadata']['shop_id'] ?? '');
  $plan   = $obj['metadata']['plan'] ?? '';
  if ($shopId !== '' && in_array($plan, ['mensuel', 'annuel', 'avie'], true)) {
    $s = &shop_ref($db, $shopId);
    if ($s) { $s['plan'] = $plan; $s['status'] = 'active'; unset($s['impayeDepuis']); }
  }
} elseif ($type === 'customer.subscription.updated') {
  $shopId = $obj['metadata']['shop_id'] ?? '';
  $st = $obj['status'] ?? '';
  if ($shopId !== '') {
    $s = &shop_ref($db, $shopId);
    if ($s) {
      if (in_array($st, ['past_due', 'unpaid'], true)) {
        if (($s['status'] ?? '') !== 'impaye') $s['impayeDepuis'] = now();
        $s['status'] = 'impaye';
      } elseif ($st === 'active') {
        $s['status'] = 'active'; unset($s['impayeDepuis']);
      }
    }
  }
} elseif ($type === 'customer.subscription.deleted') {
  $shopId = $obj['metadata']['shop_id'] ?? '';
  if ($shopId !== '') {
    $s = &shop_ref($db, $shopId);
    /* L'abonnement s'arrête : retour à la formule gratuite. Les clients
       déjà inscrits restent intacts (voir quota_restant() dans lib.php). */
    if ($s) { $s['plan'] = 'decouverte'; $s['status'] = 'annule'; unset($s['impayeDepuis']); }
  }
}

db_save($db);
echo json_encode(['ok' => true]);
