# RestoWebPro — Studio (basé sur le template Foodee)

Studio de vente de sites pour restaurants. La galerie « Nos réalisations »
liste les sites créés (Braise, Oliva, SAFRAN).

## ⚠️ Déployer à la RACINE d'un hébergement (pas dans un sous-dossier)
Les chemins des assets sont absolus (`/assets/...`) → le site doit être servi
à la racine du domaine, sinon les images/polices ne chargent pas.

### Le plus simple (30 s, lien HTTPS instantané)
1. Aller sur https://app.netlify.com/drop
2. Glisser-déposer CE dossier entier.
3. Netlify donne un lien `https://xxxx.netlify.app` → studio en ligne.

## Modifier la liste des sites
Éditer le tableau `SITES` en haut de `assets/rwp/studio.js` : remplacer chaque
`url` par l'URL EN LIGNE réelle du site. Ajouter un objet = une carte de plus.

## Fait (surcouche non destructive)
- Rebrand Foodee/Palace → RestoWebPro (HTML + chunk shared-lib.*.mjs).
- Sections restaurant masquées via assets/rwp/studio.css (!important).
- Galerie « Nos réalisations » + CTA 390 € + WhatsApp via assets/rwp/studio.js.
- Nav nettoyée ; formulaire de contact conservé (à brancher).

## À vérifier en ligne (navigateur réel)
- Image de fond du Hero + logo = servis par le CDN Framer (visibles en ligne).
- Mot géant « RESTOWEBPRO » long (template visait « Palace ») → raccourcissable.
- Remplacer les liens de démo par les URLs live des sites une fois déployés.
