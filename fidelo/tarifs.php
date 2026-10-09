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
<title>Tarifs — Fidelo</title>
<link href="https://fonts.googleapis.com/css2?family=Gloock&family=Geist:wght@400;500;600&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>html,body{margin:0;background:#0c0a08;color:#f4efe6;-webkit-font-smoothing:antialiased}body{font-family:Geist,system-ui,sans-serif}a{color:#f2a65a;text-decoration:none}a:hover{color:#ffcf99}summary{list-style:none;cursor:pointer}summary::-webkit-details-marker{display:none}details[open] summary span:last-child{transform:rotate(45deg)}</style>
</helmet>
<div style="position:relative;min-height:100vh;background:radial-gradient(ellipse 70% 50% at 50% 0%,rgba(242,166,90,.2),rgba(12,10,8,0) 70%),#0c0a08;overflow:hidden">
  <dc-import name="Nav" active="tarifs" hint-size="100%,72px"></dc-import>
  <main style="width:min(1240px,92%);margin:0 auto;padding:150px 0 90px;display:flex;flex-direction:column;gap:90px">
    <section style="display:flex;flex-direction:column;align-items:center;text-align:center;gap:22px">
      <div style="font:500 12px 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a">Tarifs</div>
      <h1 style="margin:0;max-width:880px;font:400 clamp(2.8rem,6vw,5.6rem)/.96 Gloock,serif;letter-spacing:-.025em;text-wrap:balance">Une formule pour chaque comptoir.</h1>
      <p style="margin:0;max-width:560px;font:400 1.1rem/1.6 Geist,sans-serif;color:#d6cdbf;text-wrap:pretty">Commencez gratuitement, passez à la vitesse supérieure quand vous voulez. Aucune commission sur vos ventes, quelle que soit la formule.</p>
    </section>

    <section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px;align-items:stretch">
      <div style="display:flex;flex-direction:column;gap:18px;padding:30px;border-radius:24px;background:rgba(244,239,230,.03);border:1px solid rgba(244,239,230,.1)">
        <span style="font:500 12px 'Geist Mono',monospace;letter-spacing:.16em;text-transform:uppercase;color:#a39a8c">Découverte</span>
        <div style="display:flex;align-items:baseline;gap:8px"><span style="font:400 3.6rem/1 Gloock,serif">0 €</span></div>
        <p style="margin:0;font:400 15px/1.55 Geist,sans-serif;color:#d6cdbf">Pour tester Fidelo avec vos premiers clients, sans risque.</p>
        <div style="display:flex;flex-direction:column;gap:10px;padding-top:18px;border-top:1px solid rgba(244,239,230,.1);font:400 14px/1.45 Geist,sans-serif;color:#e4dccf;flex:1">
          <span>✓ Jusqu'à 30 clients</span><span>✓ Sans limite de durée</span><span>✓ Sans carte bancaire</span><span>✓ 10 jours en illimité à l'inscription</span>
        </div>
        <a href="inscription.php" style="text-align:center;padding:14px;border-radius:999px;border:1px solid rgba(244,239,230,.22);color:#f4efe6;font:600 14px Geist,sans-serif" style-hover="background:rgba(244,239,230,.08);color:#f4efe6">Commencer gratuitement</a>
      </div>
      <div style="display:flex;flex-direction:column;gap:18px;padding:30px;border-radius:24px;background:rgba(244,239,230,.03);border:1px solid rgba(244,239,230,.1)">
        <span style="font:500 12px 'Geist Mono',monospace;letter-spacing:.16em;text-transform:uppercase;color:#a39a8c">Mensuel</span>
        <div style="display:flex;align-items:baseline;gap:8px"><span style="font:400 3.6rem/1 Gloock,serif">29 €</span><span style="color:#a39a8c">/ mois</span></div>
        <p style="margin:0;font:400 15px/1.55 Geist,sans-serif;color:#d6cdbf">La souplesse totale, mois après mois.</p>
        <div style="display:flex;flex-direction:column;gap:10px;padding-top:18px;border-top:1px solid rgba(244,239,230,.1);font:400 14px/1.45 Geist,sans-serif;color:#e4dccf;flex:1">
          <span>✓ Clients illimités</span><span>✓ Sans engagement</span><span>✓ Aucune commission</span>
        </div>
        <a href="inscription.php" style="text-align:center;padding:14px;border-radius:999px;border:1px solid rgba(244,239,230,.22);color:#f4efe6;font:600 14px Geist,sans-serif" style-hover="background:rgba(244,239,230,.08);color:#f4efe6">Choisir Mensuel</a>
      </div>
      <div style="position:relative;display:flex;flex-direction:column;gap:18px;padding:30px;border-radius:24px;background:linear-gradient(160deg,rgba(242,166,90,.2),rgba(242,166,90,.05));border:1px solid rgba(242,166,90,.55);box-shadow:0 30px 80px rgba(242,166,90,.12)">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px"><span style="font:500 12px 'Geist Mono',monospace;letter-spacing:.16em;text-transform:uppercase;color:#f2a65a">Annuel</span><span style="padding:5px 10px;border-radius:999px;background:#f2a65a;color:#1a1007;font:600 11px Geist,sans-serif">Le plus choisi</span></div>
        <div style="display:flex;align-items:baseline;gap:8px"><span style="font:400 3.6rem/1 Gloock,serif">250 €</span><span style="color:#a39a8c">/ an</span></div>
        <p style="margin:0;font:400 15px/1.55 Geist,sans-serif;color:#d6cdbf">Soit 20,83 € par mois : 98 € économisés par rapport au Mensuel.</p>
        <div style="display:flex;flex-direction:column;gap:10px;padding-top:18px;border-top:1px solid rgba(242,166,90,.25);font:400 14px/1.45 Geist,sans-serif;color:#e4dccf;flex:1">
          <span>✓ Clients illimités</span><span>✓ Prix garanti 12 mois</span><span>✓ Aucune commission</span>
        </div>
        <a href="inscription.php" style="text-align:center;padding:14px;border-radius:999px;background:#f2a65a;color:#1a1007;font:600 14px Geist,sans-serif" style-hover="background:#ffbe7d;color:#1a1007">Choisir Annuel</a>
      </div>
      <div style="display:flex;flex-direction:column;gap:18px;padding:30px;border-radius:24px;background:rgba(244,239,230,.03);border:1px solid rgba(244,239,230,.1)">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px"><span style="font:500 12px 'Geist Mono',monospace;letter-spacing:.16em;text-transform:uppercase;color:#a39a8c">À vie + site web</span><span style="padding:5px 10px;border-radius:999px;border:1px solid rgba(242,166,90,.5);color:#f2a65a;font:500 11px Geist,sans-serif">Places limitées</span></div>
        <div style="display:flex;align-items:baseline;gap:8px"><span style="font:400 3.6rem/1 Gloock,serif">525 €</span><span style="color:#a39a8c">une fois</span></div>
        <p style="margin:0;font:400 15px/1.55 Geist,sans-serif;color:#d6cdbf">Offre de lancement : Fidelo à vie et un site web professionnel offert.</p>
        <div style="display:flex;flex-direction:column;gap:10px;padding-top:18px;border-top:1px solid rgba(244,239,230,.1);font:400 14px/1.45 Geist,sans-serif;color:#e4dccf;flex:1">
          <span>✓ Paiement unique</span><span>✓ Site web pro inclus</span><span>✓ Puis 50 € / an : domaine + hébergement uniquement</span>
        </div>
        <a href="inscription.php" style="text-align:center;padding:14px;border-radius:999px;border:1px solid rgba(244,239,230,.22);color:#f4efe6;font:600 14px Geist,sans-serif" style-hover="background:rgba(244,239,230,.08);color:#f4efe6">Réserver ma place</a>
      </div>
    </section>

    <section style="display:flex;flex-direction:column;gap:26px">
      <h2 style="margin:0;font:400 clamp(2rem,3.6vw,3.2rem)/1 Gloock,serif;letter-spacing:-.02em">Comparer les formules</h2>
      <div style="overflow-x:auto;border-radius:20px;border:1px solid rgba(244,239,230,.1)">
        <table style="width:100%;min-width:760px;border-collapse:collapse;font:400 14px/1.45 Geist,sans-serif">
          <thead><tr style="background:rgba(244,239,230,.04);text-align:left;font:500 11px 'Geist Mono',monospace;letter-spacing:.14em;text-transform:uppercase;color:#a39a8c"><th style="padding:16px 20px"></th><th style="padding:16px 20px">Découverte</th><th style="padding:16px 20px">Mensuel</th><th style="padding:16px 20px;color:#f2a65a">Annuel</th><th style="padding:16px 20px">À vie + site</th></tr></thead>
          <tbody style="color:#e4dccf">
            <tr style="border-top:1px solid rgba(244,239,230,.08)"><td style="padding:16px 20px;color:#a39a8c">Prix</td><td style="padding:16px 20px">0 €</td><td style="padding:16px 20px">29 € / mois</td><td style="padding:16px 20px">250 € / an</td><td style="padding:16px 20px">525 € une fois</td></tr>
            <tr style="border-top:1px solid rgba(244,239,230,.08)"><td style="padding:16px 20px;color:#a39a8c">Équivalent mensuel</td><td style="padding:16px 20px">0 €</td><td style="padding:16px 20px">29 €</td><td style="padding:16px 20px">20,83 €</td><td style="padding:16px 20px">—</td></tr>
            <tr style="border-top:1px solid rgba(244,239,230,.08)"><td style="padding:16px 20px;color:#a39a8c">Clients</td><td style="padding:16px 20px">Jusqu'à 30</td><td style="padding:16px 20px">Illimités</td><td style="padding:16px 20px">Illimités</td><td style="padding:16px 20px">Illimités</td></tr>
            <tr style="border-top:1px solid rgba(244,239,230,.08)"><td style="padding:16px 20px;color:#a39a8c">Durée</td><td style="padding:16px 20px">Sans limite</td><td style="padding:16px 20px">Sans engagement</td><td style="padding:16px 20px">Prix fixe 12 mois</td><td style="padding:16px 20px">À vie</td></tr>
            <tr style="border-top:1px solid rgba(244,239,230,.08)"><td style="padding:16px 20px;color:#a39a8c">Carte bancaire à l'inscription</td><td style="padding:16px 20px">Non requise</td><td style="padding:16px 20px">—</td><td style="padding:16px 20px">—</td><td style="padding:16px 20px">—</td></tr>
            <tr style="border-top:1px solid rgba(244,239,230,.08)"><td style="padding:16px 20px;color:#a39a8c">Site web professionnel</td><td style="padding:16px 20px">—</td><td style="padding:16px 20px">—</td><td style="padding:16px 20px">—</td><td style="padding:16px 20px">Offert</td></tr>
            <tr style="border-top:1px solid rgba(244,239,230,.08)"><td style="padding:16px 20px;color:#a39a8c">Frais récurrents</td><td style="padding:16px 20px">Aucun</td><td style="padding:16px 20px">29 € / mois</td><td style="padding:16px 20px">250 € / an</td><td style="padding:16px 20px">50 € / an (domaine + hébergement)</td></tr>
            <tr style="border-top:1px solid rgba(244,239,230,.08)"><td style="padding:16px 20px;color:#a39a8c">Commission sur vos ventes</td><td style="padding:16px 20px">Aucune</td><td style="padding:16px 20px">Aucune</td><td style="padding:16px 20px">Aucune</td><td style="padding:16px 20px">Aucune</td></tr>
          </tbody>
        </table>
      </div>
    </section>

    <section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:40px">
      <div style="display:flex;flex-direction:column;gap:14px">
        <div style="font:500 12px 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a">Questions sur les tarifs</div>
        <h2 style="margin:0;font:400 clamp(2rem,3.6vw,3.2rem)/1 Gloock,serif;letter-spacing:-.02em">Tout est écrit, rien de caché.</h2>
        <p style="margin:0;color:#a39a8c;font-size:15px;line-height:1.6">Une autre question ? <a href="contact.php">Écrivez-nous</a>, réponse sous 24 h ouvrées.</p>
      </div>
      <div style="display:flex;flex-direction:column">
        <details style="border-top:1px solid rgba(244,239,230,.1)"><summary style="display:flex;justify-content:space-between;gap:16px;padding:18px 0;font:500 16px Geist,sans-serif"><span>La formule gratuite l'est-elle vraiment ?</span><span style="color:#f2a65a;transition:transform .2s">+</span></summary><p style="margin:0 0 18px;color:#cfc6b8;font-size:15px;line-height:1.6">Oui : 0 €, sans limite de durée et sans carte bancaire, jusqu'à 30 clients. Vous profitez en plus de 10 jours en illimité à l'inscription.</p></details>
        <details style="border-top:1px solid rgba(244,239,230,.1)"><summary style="display:flex;justify-content:space-between;gap:16px;padding:18px 0;font:500 16px Geist,sans-serif"><span>Que se passe-t-il quand j'atteins 30 clients ?</span><span style="color:#f2a65a;transition:transform .2s">+</span></summary><p style="margin:0 0 18px;color:#cfc6b8;font-size:15px;line-height:1.6">Pour inscrire plus de 30 clients, il suffit de passer à une formule Mensuel, Annuel ou À vie, toutes en clients illimités.</p></details>
        <details style="border-top:1px solid rgba(244,239,230,.1)"><summary style="display:flex;justify-content:space-between;gap:16px;padding:18px 0;font:500 16px Geist,sans-serif"><span>Puis-je changer de formule plus tard ?</span><span style="color:#f2a65a;transition:transform .2s">+</span></summary><p style="margin:0 0 18px;color:#cfc6b8;font-size:15px;line-height:1.6">Oui, vous pouvez changer de formule selon l'évolution de votre commerce.</p></details>
        <details style="border-top:1px solid rgba(244,239,230,.1)"><summary style="display:flex;justify-content:space-between;gap:16px;padding:18px 0;font:500 16px Geist,sans-serif"><span>Que veut dire « à vie » ?</span><span style="color:#f2a65a;transition:transform .2s">+</span></summary><p style="margin:0 0 18px;color:#cfc6b8;font-size:15px;line-height:1.6">Vous payez Fidelo une seule fois, 525 €. Les 50 € annuels qui suivent couvrent uniquement le nom de domaine et l'hébergement de votre site : ce n'est pas un abonnement Fidelo.</p></details>
        <details style="border-top:1px solid rgba(244,239,230,.1)"><summary style="display:flex;justify-content:space-between;gap:16px;padding:18px 0;font:500 16px Geist,sans-serif"><span>Quel site web est offert ?</span><span style="color:#f2a65a;transition:transform .2s">+</span></summary><p style="margin:0 0 18px;color:#cfc6b8;font-size:15px;line-height:1.6">Un site professionnel pour présenter votre commerce, inclus dans la formule À vie. C'est une offre de lancement, avec un nombre de places limité.</p></details>
        <details style="border-top:1px solid rgba(244,239,230,.1)"><summary style="display:flex;justify-content:space-between;gap:16px;padding:18px 0;font:500 16px Geist,sans-serif"><span>Prenez-vous une commission ?</span><span style="color:#f2a65a;transition:transform .2s">+</span></summary><p style="margin:0 0 18px;color:#cfc6b8;font-size:15px;line-height:1.6">Non. Aucune commission sur vos ventes, dans aucune formule.</p></details>
        <details style="border-top:1px solid rgba(244,239,230,.1)"><summary style="display:flex;justify-content:space-between;gap:16px;padding:18px 0;font:500 16px Geist,sans-serif"><span>Si j'arrête, je perds mes données clients ?</span><span style="color:#f2a65a;transition:transform .2s">+</span></summary><p style="margin:0 0 18px;color:#cfc6b8;font-size:15px;line-height:1.6">Vos données clients vous appartiennent. Contactez-nous avant d'arrêter et nous vous aidons à les récupérer.</p></details>
        <details style="border-top:1px solid rgba(244,239,230,.1);border-bottom:1px solid rgba(244,239,230,.1)"><summary style="display:flex;justify-content:space-between;gap:16px;padding:18px 0;font:500 16px Geist,sans-serif"><span>Dois-je acheter du matériel ?</span><span style="color:#f2a65a;transition:transform .2s">+</span></summary><p style="margin:0 0 18px;color:#cfc6b8;font-size:15px;line-height:1.6">Non. Vous scannez depuis votre espace Fidelo, sur le téléphone ou la tablette que vous avez déjà.</p></details>
      </div>
    </section>

    <section style="display:flex;flex-direction:column;align-items:center;text-align:center;gap:22px;padding:70px 24px;border-radius:32px;background:radial-gradient(ellipse at 50% 0%,rgba(242,166,90,.28),rgba(242,166,90,0) 70%),rgba(244,239,230,.03);border:1px solid rgba(244,239,230,.1)">
      <h2 style="margin:0;font:400 clamp(2.2rem,4.6vw,4rem)/1 Gloock,serif;letter-spacing:-.02em;text-wrap:balance">Lancez votre programme dès aujourd'hui.</h2>
      <p style="margin:0;color:#d6cdbf">0 € · jusqu'à 30 clients · sans carte bancaire</p>
      <a href="inscription.php" style="padding:17px 28px;border-radius:999px;background:#f2a65a;color:#1a1007;font:600 15px Geist,sans-serif" style-hover="background:#ffbe7d;color:#1a1007">Commencer gratuitement</a>
    </section>
  </main>
  <dc-import name="Footer" hint-size="100%,260px"></dc-import>
</div>
</x-dc>
</body>
</html>
