<?php
/* Fidelo — mentions légales (LCEN art. 6-III). */
require __DIR__ . '/legal-ui.php';
$E = EDITEUR;
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

legal_open(
  'Mentions légales',
  "Éditeur, hébergeur et conditions d'utilisation du service Fidelo.",
  "Informations légales relatives au site <b>fidelo.site</b> et au service Fidelo, conformément à la loi n°2004-575 du 21 juin 2004 pour la confiance dans l'économie numérique."
);
?>

<h2>1. Éditeur du site</h2>
<table>
  <tbody>
    <tr><td>Dénomination</td><td><b><?= e($E['societe']) ?></b> (<?= e($E['forme']) ?>)</td></tr>
    <tr><td>Responsable de la publication</td><td><?= e($E['dirigeant']) ?></td></tr>
    <tr><td>Siège</td><td><?= e($E['adresse']) ?>, <?= e($E['cp_ville']) ?></td></tr>
    <tr><td>SIRET</td><td><?= e($E['siret']) ?></td></tr>
    <tr><td>TVA</td><td><?= e($E['tva']) ?></td></tr>
    <tr><td>Contact</td><td><a href="mailto:<?= e($E['email']) ?>"><?= e($E['email']) ?></a></td></tr>
  </tbody>
</table>

<h2>2. Hébergement</h2>
<p>Le site est hébergé par <?= e($E['hebergeur']) ?>.</p>

<h2>3. Objet du service</h2>
<p>Fidelo est un service en ligne permettant à un commerce de proximité de gérer un programme
de fidélité : inscription des clients, comptage des points, attribution de récompenses,
cartes numériques et messages adressés à sa propre clientèle.</p>
<p>Le service est accessible par abonnement ou par règlement unique, selon la formule choisie.
Les tarifs en vigueur sont indiqués sur la page <a href="<?= e($base) ?>/tarifs.php">Nos offres</a>.
Une formule <b>Découverte gratuite</b> est proposée sans limite de durée, dans la limite du
nombre de clients qu'elle prévoit.</p>

<h2>4. Responsabilité du commerçant</h2>
<p>Chaque commerçant reste responsable des données qu'il saisit et du programme de fidélité
qu'il définit. Il lui appartient notamment :</p>
<ul>
  <li>d'informer ses clients de l'existence du programme et de recueillir leur accord ;</li>
  <li>d'honorer les récompenses qu'il annonce ;</li>
  <li>de n'utiliser les messages groupés que pour informer sa propre clientèle, sans abus ;</li>
  <li>de respecter la réglementation applicable à son activité.</li>
</ul>
<p>Fidelo ne perçoit aucune commission sur le chiffre d'affaires du commerce et n'intervient
pas dans la relation commerciale entre le commerçant et ses clients.</p>

<h2>5. Disponibilité</h2>
<p>Nous mettons tout en œuvre pour assurer la continuité du service, sans pouvoir garantir une
disponibilité absolue : des interruptions peuvent survenir pour maintenance, mise à jour ou
en raison d'un incident indépendant de notre volonté. Des sauvegardes automatiques sont
réalisées afin de limiter toute perte de données.</p>

<h2>6. Propriété intellectuelle</h2>
<p>La marque Fidelo, son logo, la conception du site et son code sont la propriété de
<?= e($E['societe']) ?>. Toute reproduction ou réutilisation, totale ou partielle, sans
autorisation écrite est interdite.</p>
<p>Le logo, le nom et les visuels que le commerçant dépose dans son espace <b>restent sa
propriété</b>. Ils ne sont utilisés que pour afficher ses propres cartes de fidélité, et sont
supprimés avec son compte.</p>

<h2>7. Données personnelles</h2>
<p>Le traitement des données personnelles est décrit en détail dans notre
<a href="<?= e($base) ?>/confidentialite.php">politique de confidentialité</a>.</p>

<h2>8. Droit applicable</h2>
<p>Les présentes mentions sont soumises au droit français. En cas de litige, une solution
amiable sera recherchée en priorité. À défaut, les tribunaux français sont compétents.</p>

<?php legal_close(); ?>
