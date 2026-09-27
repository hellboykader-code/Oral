<?php
/* Fidelo — page Contact (design 3D cinématique). Envoi réel par mail(), même
   logique anti-spam que l'ancienne page : honeypot + rate-limit + validation. */
require __DIR__ . '/lib.php';
$E = EDITEUR;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Content-Type: application/json; charset=utf-8');
  $data = json_decode(file_get_contents('php://input'), true) ?: [];
  $nom = mb_substr(trim($data['nom'] ?? ''), 0, 80);
  $email = mb_substr(trim($data['email'] ?? ''), 0, 120);
  $message = mb_substr(trim($data['message'] ?? ''), 0, 2000);
  $hp = trim($data['website'] ?? '');
  if ($hp !== '') { echo json_encode(['ok' => false, 'message' => 'Erreur, réessayez.']); exit; }
  if ($nom === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'message' => "Merci de remplir les champs obligatoires (*) avec un e-mail valide."]); exit;
  }
  try { $db = db_load(); } catch (Throwable $e) { $db = null; }
  if ($db !== null && !rate_hit($db, 'contact:' . client_ip(), 5, 3600)) {
    db_save($db);
    echo json_encode(['ok' => false, 'message' => 'Trop de messages envoyés. Réessayez plus tard.']); exit;
  }
  if ($db !== null) db_save($db);
  $to = $E['inbox'] ?? $E['email'];
  $subject = 'Contact Fidelo — ' . $nom;
  $body = "Nom : $nom\r\nE-mail : $email\r\n\r\nMessage :\r\n$message\r\n\r\n—\r\nEnvoyé depuis fidelo.site le " . date('d/m/Y H:i');
  $headers = "From: Fidelo <" . $E['email'] . ">\r\n"
           . "Reply-To: " . $email . "\r\n"
           . "Content-Type: text/plain; charset=UTF-8\r\n";
  @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
  echo json_encode(['ok' => true, 'message' => 'Message envoyé. Réponse sous 24 h ouvrées.']);
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="./support.js"></script>
</head>
<body>
<x-dc>
<helmet>
<title>Contact — Fidelo</title>
<link href="https://fonts.googleapis.com/css2?family=Gloock&family=Geist:wght@400;500;600&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>html,body{margin:0;background:#0c0a08;color:#f4efe6;-webkit-font-smoothing:antialiased}body{font-family:Geist,system-ui,sans-serif}a{color:#f2a65a;text-decoration:none}a:hover{color:#ffcf99}input::placeholder,textarea::placeholder{color:#7d7568}</style>
</helmet>
<div style="position:relative;min-height:100vh;background:radial-gradient(ellipse 60% 45% at 20% 0%,rgba(242,166,90,.18),rgba(12,10,8,0) 70%),#0c0a08">
  <dc-import name="Nav" active="contact" hint-size="100%,72px"></dc-import>
  <main style="width:min(1240px,92%);margin:0 auto;padding:150px 0 90px;display:flex;flex-direction:column;gap:56px">
    <section style="display:flex;flex-direction:column;gap:20px;max-width:760px">
      <div style="font:500 12px 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a">Contact</div>
      <h1 style="margin:0;font:400 clamp(2.8rem,6vw,5.4rem)/.96 Gloock,serif;letter-spacing:-.025em;text-wrap:balance">Parlons de votre commerce.</h1>
      <p style="margin:0;font:400 1.1rem/1.6 Geist,sans-serif;color:#d6cdbf">Une question sur Fidelo ou sur une formule ? Nous répondons sous 24 heures ouvrées.</p>
    </section>

    <section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(320px,100%),1fr));gap:20px;align-items:start">
      <form onSubmit="{{ submit }}" style="display:flex;flex-direction:column;gap:18px;padding:32px;border-radius:24px;background:rgba(244,239,230,.03);border:1px solid rgba(244,239,230,.1)">
        <label style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">Ne pas remplir<input name="website" value="{{ website }}" onChange="{{ onWebsite }}" tabIndex="-1" autoComplete="off"></label>
        <label style="display:flex;flex-direction:column;gap:8px;font:500 13px Geist,sans-serif;color:#d6cdbf">Nom *<input name="nom" value="{{ nom }}" onChange="{{ onNom }}" placeholder="Camille Martin" style="height:50px;padding:0 16px;border-radius:12px;border:1px solid rgba(244,239,230,.16);background:rgba(12,10,8,.6);color:#f4efe6;font:400 15px Geist,sans-serif;outline:none" style-focus="border-color:#f2a65a"></label>
        <label style="display:flex;flex-direction:column;gap:8px;font:500 13px Geist,sans-serif;color:#d6cdbf">E-mail *<input name="email" type="email" value="{{ email }}" onChange="{{ onEmail }}" placeholder="vous@exemple.fr" style="height:50px;padding:0 16px;border-radius:12px;border:1px solid rgba(244,239,230,.16);background:rgba(12,10,8,.6);color:#f4efe6;font:400 15px Geist,sans-serif;outline:none" style-focus="border-color:#f2a65a"></label>
        <label style="display:flex;flex-direction:column;gap:8px;font:500 13px Geist,sans-serif;color:#d6cdbf">Message *<textarea name="message" value="{{ message }}" onChange="{{ onMsg }}" rows="6" placeholder="Parlez-nous de votre commerce et de votre question." style="padding:14px 16px;border-radius:12px;border:1px solid rgba(244,239,230,.16);background:rgba(12,10,8,.6);color:#f4efe6;font:400 15px/1.5 Geist,sans-serif;outline:none;resize:vertical" style-focus="border-color:#f2a65a"></textarea></label>
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px">
          <span style="font:400 13px/1.45 Geist,sans-serif;color:{{ noteColor }}">{{ note }}</span>
          <button type="submit" disabled="{{ busy }}" style="height:50px;padding:0 26px;border-radius:999px;border:0;background:#f2a65a;color:#1a1007;font:600 15px Geist,sans-serif;cursor:pointer;opacity:{{ busyOpacity }}" style-hover="background:#ffbe7d">{{ btnLabel }}</button>
        </div>
        <p style="margin:0;font:400 12px/1.5 Geist,sans-serif;color:#7d7568">Vos données servent uniquement à vous répondre. Voir notre <a href="confidentialite.php">politique de confidentialité</a>.</p>
      </form>

      <div style="display:flex;flex-direction:column;gap:20px">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1px;border-radius:24px;overflow:hidden;background:rgba(244,239,230,.1);border:1px solid rgba(244,239,230,.1)">
          <a href="{{ waLink }}" target="_blank" rel="noopener" style="display:flex;flex-direction:column;gap:6px;padding:22px;background:#12100d;color:#f4efe6" style-hover="background:#1a1611;color:#f4efe6"><span style="font:500 11px 'Geist Mono',monospace;letter-spacing:.14em;text-transform:uppercase;color:#f2a65a">WhatsApp</span><span style="font:400 16px Geist,sans-serif">{{ whatsapp }}</span></a>
          <a href="{{ telLink }}" style="display:flex;flex-direction:column;gap:6px;padding:22px;background:#12100d;color:#f4efe6" style-hover="background:#1a1611;color:#f4efe6"><span style="font:500 11px 'Geist Mono',monospace;letter-spacing:.14em;text-transform:uppercase;color:#f2a65a">Téléphone</span><span style="font:400 16px Geist,sans-serif">{{ phone }}</span></a>
          <a href="{{ mailLink }}" style="display:flex;flex-direction:column;gap:6px;padding:22px;background:#12100d;color:#f4efe6" style-hover="background:#1a1611;color:#f4efe6"><span style="font:500 11px 'Geist Mono',monospace;letter-spacing:.14em;text-transform:uppercase;color:#f2a65a">E-mail</span><span style="font:400 16px Geist,sans-serif;word-break:break-all">{{ mail }}</span></a>
          <div style="display:flex;flex-direction:column;gap:6px;padding:22px;background:#12100d"><span style="font:500 11px 'Geist Mono',monospace;letter-spacing:.14em;text-transform:uppercase;color:#f2a65a">Adresse</span><span style="font:400 16px/1.45 Geist,sans-serif">AK DEV<br>{{ address }}</span></div>
        </div>
        <div style="position:relative;height:340px;border-radius:24px;overflow:hidden;border:1px solid rgba(244,239,230,.1);background:#12100d">
          <iframe title="Carte — AK DEV" src="{{ mapSrc }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="width:100%;height:100%;border:0;filter:grayscale(.85) invert(.9) hue-rotate(180deg) sepia(.25) contrast(.9)"></iframe>
        </div>
      </div>
    </section>
  </main>
  <dc-import name="Footer" hint-size="100%,260px"></dc-import>
</div>
</x-dc>
<script type="text/x-dc" data-dc-script data-props="{&quot;phone&quot;:{&quot;editor&quot;:&quot;text&quot;,&quot;default&quot;:&quot;+33 7 45 92 95 20&quot;,&quot;tsType&quot;:&quot;string&quot;,&quot;section&quot;:&quot;Coordonnées&quot;},&quot;whatsapp&quot;:{&quot;editor&quot;:&quot;text&quot;,&quot;default&quot;:&quot;+33 7 45 92 95 20&quot;,&quot;tsType&quot;:&quot;string&quot;,&quot;section&quot;:&quot;Coordonnées&quot;},&quot;email&quot;:{&quot;editor&quot;:&quot;text&quot;,&quot;default&quot;:&quot;contact@fidelo.site&quot;,&quot;tsType&quot;:&quot;string&quot;,&quot;section&quot;:&quot;Coordonnées&quot;},&quot;address&quot;:{&quot;editor&quot;:&quot;text&quot;,&quot;default&quot;:&quot;4 avenue du Maréchal de Lattre de Tassigny, 94000 Créteil, France&quot;,&quot;tsType&quot;:&quot;string&quot;,&quot;section&quot;:&quot;Coordonnées&quot;},&quot;mapQuery&quot;:{&quot;editor&quot;:&quot;text&quot;,&quot;default&quot;:&quot;4 avenue du Maréchal de Lattre de Tassigny, 94000 Créteil&quot;,&quot;tsType&quot;:&quot;string&quot;,&quot;section&quot;:&quot;Coordonnées&quot;}}">
class Component extends DCLogic {
  state = { nom: '', email: '', message: '', website: '', note: 'Champs obligatoires (*)', ok: null, busy: false };
  renderVals() {
    const p = this.props, s = this.state;
    const phone = p.phone ?? '+33 7 45 92 95 20', wa = p.whatsapp ?? '+33 7 45 92 95 20', mail = p.email ?? 'contact@fidelo.site', address = p.address ?? '4 avenue du Maréchal de Lattre de Tassigny, 94000 Créteil, France';
    const digits = v => v.replace(/[^\d+]/g, '');
    return {
      nom: s.nom, email: s.email, message: s.message, website: s.website, note: s.note, noteColor: s.ok === false ? '#ff8a7a' : s.ok ? '#f2a65a' : '#7d7568',
      busy: s.busy, busyOpacity: s.busy ? '.6' : '1', btnLabel: s.busy ? 'Envoi…' : 'Envoyer le message',
      onNom: e => this.setState({ nom: e.target.value }), onEmail: e => this.setState({ email: e.target.value }), onMsg: e => this.setState({ message: e.target.value }), onWebsite: e => this.setState({ website: e.target.value }),
      submit: async e => {
        e.preventDefault();
        if (s.busy) return;
        if (!s.nom.trim() || !/^\S+@\S+\.\S+$/.test(s.email) || !s.message.trim()) return this.setState({ ok: false, note: 'Merci de remplir les champs obligatoires (*) avec un e-mail valide.' });
        this.setState({ busy: true, note: 'Envoi en cours…' });
        try {
          const res = await fetch(location.pathname, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ nom: s.nom, email: s.email, message: s.message, website: s.website }) });
          const data = await res.json();
          if (data.ok) this.setState({ ok: true, busy: false, nom: '', email: '', message: '', note: data.message || 'Message envoyé. Réponse sous 24 h ouvrées.' });
          else this.setState({ ok: false, busy: false, note: data.message || "Erreur, réessayez." });
        } catch (err) { this.setState({ ok: false, busy: false, note: "Erreur réseau, réessayez." }); }
      },
      phone, whatsapp: wa, mail, address,
      telLink: 'tel:' + digits(phone), waLink: 'https://wa.me/' + digits(wa).replace('+', ''), mailLink: 'mailto:' + mail,
      mapSrc: 'https://www.google.com/maps?q=' + encodeURIComponent(p.mapQuery || '4 avenue du Maréchal de Lattre de Tassigny, 94000 Créteil') + '&output=embed',
    };
  }
}
</script>
</body>
</html>
