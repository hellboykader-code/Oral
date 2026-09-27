<?php
/* Fidelo — politique de confidentialité (RGPD). */
require __DIR__ . '/legal-ui.php';
$E = EDITEUR;
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

legal_open(
  'Politique de confidentialité',
  "Comment Fidelo collecte, utilise et protège les données personnelles des commerçants et de leurs clients. Conforme au RGPD.",
  "Fidelo est un programme de fidélité pour les commerces de proximité. Cette page explique, sans jargon inutile, <b>quelles données nous traitons, pourquoi, combien de temps</b> et <b>comment exercer vos droits</b>."
);
?>

<div class="box key">
  <p><b>L'essentiel en cinq lignes.</b></p>
  <ul>
    <li>Nous demandons <b>le strict minimum</b> : pour un client de commerce, un prénom et un numéro de téléphone suffisent.</li>
    <li>Nous ne vendons, ne louons et n'échangeons <b>aucune donnée</b>, à personne.</li>
    <li>Aucune publicité, aucun traçage publicitaire, aucun revendeur de données.</li>
    <li>Chaque commerce ne voit <b>que ses propres clients</b> — jamais ceux d'un autre.</li>
    <li>Vous pouvez demander la suppression de vos données à tout moment.</li>
  </ul>
</div>

<h2>1. Qui est responsable ?</h2>
<p>Le service Fidelo est édité par <b><?= e($E['societe']) ?></b> (<?= e($E['forme']) ?>),
représentée par <?= e($E['dirigeant']) ?>, <?= e($E['adresse']) ?>, <?= e($E['cp_ville']) ?> —
SIRET <?= e($E['siret']) ?>.</p>
<p>Pour toute question relative à vos données :
<a href="mailto:<?= e($E['email']) ?>"><?= e($E['email']) ?></a>.</p>

<h2>2. Deux situations différentes</h2>
<p>Fidelo réunit deux types de personnes, et notre rôle n'est pas le même dans les deux cas.
C'est une distinction importante : elle détermine à qui vous devez vous adresser.</p>

<table>
  <thead><tr><th>Vous êtes…</th><th>Notre rôle</th><th>À qui vous adresser</th></tr></thead>
  <tbody>
    <tr>
      <td><b>Un commerçant</b> qui a créé un compte Fidelo</td>
      <td><b>Responsable de traitement.</b> Nous décidons des données nécessaires à votre compte.</td>
      <td>Directement à nous : <a href="mailto:<?= e($E['email']) ?>"><?= e($E['email']) ?></a></td>
    </tr>
    <tr>
      <td><b>Un client d'un commerce</b> qui possède une carte de fidélité</td>
      <td><b>Sous-traitant.</b> Le commerce est responsable de vos données ; nous ne faisons que les héberger et les traiter pour son compte, selon ses instructions.</td>
      <td>En priorité au commerce concerné. Nous pouvons relayer votre demande si besoin.</td>
    </tr>
  </tbody>
</table>

<h2>3. Quelles données sont collectées ?</h2>

<h3>Pour le compte d'un commerçant</h3>
<ul>
  <li>Nom du commerce, type d'activité, logo et couleurs si vous les ajoutez</li>
  <li>Adresse e-mail et numéro de téléphone de contact</li>
  <li>Mot de passe, stocké uniquement sous forme <b>chiffrée et irréversible</b> (hachage) — nous ne pouvons pas le lire</li>
  <li>Code à 4 chiffres de déverrouillage rapide, également haché</li>
  <li>Identifiants techniques des appareils de confiance (pour éviter de retaper le mot de passe)</li>
  <li>Formule choisie, date de création et journal des connexions</li>
</ul>

<h3>Pour un client de commerce</h3>
<p>C'est volontairement très peu :</p>
<ul>
  <li><b>Prénom (ou nom d'usage)</b> — pour vous reconnaître en caisse</li>
  <li><b>Numéro de téléphone</b> — pour retrouver votre carte si vous perdez le lien</li>
  <li>Le <b>numéro de votre carte</b>, vos <b>points</b>, le <b>nombre de visites</b> et la <b>date du dernier passage</b></li>
  <li>Si — et seulement si — vous les activez : votre abonnement aux <b>notifications</b></li>
</ul>
<div class="box">
  <p><b>Ce que nous ne demandons jamais à un client :</b> adresse postale, date de naissance,
  moyen de paiement, montant de vos achats, ni le détail de ce que vous consommez.
  Fidelo compte des passages, pas des euros.</p>
</div>

<h2>4. Pourquoi, et sur quelle base légale ?</h2>
<table>
  <thead><tr><th>Finalité</th><th>Base légale (RGPD)</th></tr></thead>
  <tbody>
    <tr><td>Fournir le compte commerçant et le service de fidélité</td><td>Exécution du contrat — art. 6.1.b</td></tr>
    <tr><td>Tenir le compteur de points et attribuer les récompenses</td><td>Exécution du contrat (commerce ↔ client)</td></tr>
    <tr><td>Sécurité : limitation des tentatives, appareils de confiance, sauvegardes</td><td>Intérêt légitime — art. 6.1.f</td></tr>
    <tr><td>Messages groupés et notifications envoyés par le commerce</td><td><b>Consentement</b> — art. 6.1.a (révocable à tout moment)</td></tr>
    <tr><td>Ajout de la carte à Google Wallet ou Apple Wallet</td><td><b>Consentement</b> — à votre seule initiative</td></tr>
    <tr><td>Facturation et obligations comptables</td><td>Obligation légale — art. 6.1.c</td></tr>
  </tbody>
</table>

<h2>5. Combien de temps les gardons-nous ?</h2>
<table>
  <thead><tr><th>Donnée</th><th>Durée</th></tr></thead>
  <tbody>
    <tr><td>Compte commerçant</td><td>Tant que le compte est actif, puis <b>12 mois</b> après la dernière connexion</td></tr>
    <tr><td>Fiche d'un client de commerce</td><td>Tant que le commerce la conserve — supprimable à tout moment</td></tr>
    <tr><td>Fiche supprimée (corbeille)</td><td><b>30 jours</b>, puis effacement définitif</td></tr>
    <tr><td>Sauvegardes techniques</td><td><b>30 jours</b> glissants</td></tr>
    <tr><td>Appareil de confiance</td><td><b>120 jours</b> sans usage, puis expiration automatique</td></tr>
    <tr><td>Factures</td><td><b>10 ans</b> (obligation comptable française)</td></tr>
  </tbody>
</table>

<h2>6. Qui d'autre y a accès ?</h2>
<p>Personne, en dehors des prestataires strictement nécessaires au fonctionnement du service :</p>
<ul>
  <li><b>Hébergeur</b> — <?= e($E['hebergeur']) ?>. Les données sont stockées sur ses serveurs.</li>
  <li><b>Google Wallet</b> (Google Ireland Ltd) — <b>uniquement</b> si un client choisit d'ajouter sa carte à son portefeuille. Dans ce cas, le nom du commerce, le prénom du porteur et le nombre de points sont transmis à Google pour afficher la carte.</li>
  <li><b>Apple Wallet</b> (Apple Distribution International Ltd) — mêmes conditions, à la seule initiative du client.</li>
  <li><b>Services de notification</b> (Google, Mozilla, Apple selon le navigateur) — si les notifications sont activées. Le contenu du message transite par eux pour atteindre votre téléphone.</li>
</ul>
<p><b>Aucune donnée n'est vendue, louée, échangée ni cédée</b> à un annonceur, un courtier
en données ou un tiers à des fins commerciales. Cela ne fait pas partie de notre modèle
économique : nos revenus proviennent uniquement de l'abonnement payé par les commerçants.</p>

<h3>Transferts hors Union européenne</h3>
<p>Certains prestataires ci-dessus sont établis aux États-Unis. Ces transferts sont encadrés
par les clauses contractuelles types de la Commission européenne et, le cas échéant, par le
<i>EU-U.S. Data Privacy Framework</i>. Un client qui n'ajoute pas sa carte à un portefeuille
et n'active pas les notifications ne fait l'objet d'<b>aucun transfert</b> de ce type.</p>

<h2>7. Cookies</h2>
<p>Fidelo n'utilise <b>aucun cookie publicitaire, ni aucun traceur d'audience</b>.
Il n'y a donc pas de bandeau à cliquer. Seuls des cookies strictement nécessaires sont déposés :</p>
<table>
  <thead><tr><th>Cookie</th><th>Rôle</th><th>Durée</th></tr></thead>
  <tbody>
    <tr><td><code>fidelo_sid</code></td><td>Maintenir la session du commerçant connecté</td><td>Session</td></tr>
    <tr><td><code>fidelo_dev</code></td><td>Reconnaître un appareil de confiance (déverrouillage par code)</td><td>120 jours</td></tr>
    <tr><td><code>fidelo_card</code></td><td>Retrouver votre carte et recevoir les messages du commerce</td><td>12 mois</td></tr>
  </tbody>
</table>

<h2>8. Sécurité</h2>
<ul>
  <li>Le site est servi <b>exclusivement en HTTPS</b> (chiffrement de bout en bout).</li>
  <li>Les mots de passe sont <b>hachés</b> : même nous ne pouvons pas les lire.</li>
  <li>Chaque commerce est <b>cloisonné</b> : il lui est techniquement impossible de consulter les clients d'un autre commerce.</li>
  <li>Les tentatives de connexion sont <b>limitées</b> pour bloquer les attaques par essais répétés.</li>
  <li>Les points sont crédités <b>par le commerçant depuis son espace</b> : un client ne peut pas s'en attribuer lui-même.</li>
  <li>Les données sont <b>sauvegardées automatiquement</b> et restaurables.</li>
</ul>

<h2>9. Vos droits</h2>
<p>Conformément au RGPD, vous disposez des droits d'<b>accès</b>, de <b>rectification</b>,
d'<b>effacement</b>, de <b>limitation</b>, d'<b>opposition</b> et de <b>portabilité</b> de vos données.</p>
<ul>
  <li><b>Vous êtes commerçant :</b> tout est dans votre espace. Vous modifiez vos informations, vous exportez vos clients au format tableur et vous supprimez ce que vous voulez. Pour supprimer l'ensemble de votre compte, écrivez-nous.</li>
  <li><b>Vous êtes client d'un commerce :</b> demandez au commerce de modifier ou supprimer votre fiche — il le fait en un clic. Si le commerce ne répond pas, écrivez-nous à <a href="mailto:<?= e($E['email']) ?>"><?= e($E['email']) ?></a> et nous ferons le nécessaire.</li>
</ul>
<p>Nous répondons sous <b>30 jours maximum</b>, gratuitement.</p>
<div class="box">
  <p>Si une réponse ne vous satisfait pas, vous pouvez introduire une réclamation auprès de la
  <b>CNIL</b> — 3 place de Fontenoy, TSA 80715, 75334 Paris Cedex 07 —
  <a href="https://www.cnil.fr" target="_blank" rel="noopener">www.cnil.fr</a>.</p>
</div>

<h2>10. Mineurs</h2>
<p>Fidelo n'est pas destiné aux enfants de moins de 15 ans. Les commerçants sont invités à ne
pas inscrire de mineur sans l'accord de son représentant légal. Toute fiche signalée comme
telle est supprimée sans délai.</p>

<h2>11. Modifications</h2>
<p>Cette politique peut évoluer avec le service. La date de mise à jour figure en haut de page.
En cas de changement important, les commerçants sont prévenus par e-mail et depuis leur espace.</p>

<h2>12. Nous écrire</h2>
<p><?= e($E['societe']) ?> — <?= e($E['dirigeant']) ?><br>
<?= e($E['adresse']) ?>, <?= e($E['cp_ville']) ?><br>
<a href="mailto:<?= e($E['email']) ?>"><?= e($E['email']) ?></a></p>

<?php legal_close(); ?>
