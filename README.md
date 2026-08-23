# Cabinet Dentaire — Site vitrine (France)

Site professionnel pour cabinets dentaires en France, reconstruit à partir du
modèle **Oralcare** en **React 18 + React Router + Framer Motion** (Vite).
Interface entièrement en **français**, responsive, animée et optimisée.

## Démarrage

```bash
npm install
npm run dev      # serveur de développement (Vite + Fast Refresh)
npm run build    # build de production (dist/)
npm run preview  # prévisualiser le build
```

## Pages

| Route          | Page          | Contenu principal                                             |
| -------------- | ------------- | ------------------------------------------------------------- |
| `/`            | Accueil       | Hero, 6 soins, pourquoi nous, technologies, avant/après, galerie, témoignages, carte + horaires, CTA |
| `/nos-soins`   | Nos soins     | Les 21 soins, filtrables par catégorie                        |
| `/a-propos`    | À propos      | Histoire, praticien, diplômes, équipe médicale, chiffres clés |
| `/contact`     | Contact       | Formulaire simple, carte, horaires, coordonnées               |
| `/rendez-vous` | Rendez-vous   | Formulaire complet + sélection des 21 soins (« Pourquoi ? »)  |

## Personnalisation rapide

- **Coordonnées / horaires** : `src/data/site.js` (valeurs d'exemple à remplacer).
- **Les 21 soins** : `src/data/soins.js`.
- **Témoignages** : `src/data/temoignages.js`.
- **Images** : déposez vos fichiers dans `public/images/` selon les clés de
  `src/data/images.js` (ex. `public/images/hero.webp`). Tant qu'une image est
  absente, un placeholder de marque s'affiche automatiquement.
- **Couleurs / typographie / espacements** : `src/styles/tokens.css`.

## Formulaires

Les formulaires (Contact, Rendez-vous) sont fonctionnels côté client
(validation + état de succès) mais **nécessitent un backend d'envoi** pour être
opérationnels en production : Netlify Forms, Formspree, ou un service e-mail.

## Design

Palette et typographies extraites fidèlement du modèle original :

- Couleurs : `#0d1b15` (vert forêt), `#d1fc71` (accent), `#f2f2ef` (crème), `#595e5c` (gris).
- Polices : Bricolage Grotesque (titres), Figtree / Jost (texte), Gilda Display.
