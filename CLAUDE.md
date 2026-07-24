# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Langue

Ce projet et ses contenus sont **en français** (site pour cabinets dentaires en
France). Rédigez le contenu utilisateur, les libellés et les commentaires en
français. Le propriétaire de ce dépôt communique en arabe.

## Règles produit (permanentes)

- **⭐ RÈGLE CAPITALE — Réplique fidèle du modèle propre à chaque site.** Chaque
  cabinet a SON PROPRE modèle Framer (fichiers complets fournis par le
  propriétaire). Le site produit doit être une **copie fidèle, page par page, de
  CE modèle-là** : analysez TOUTES les pages, le code, la mise en page, les
  sections, les animations/transitions, la typographie et les couleurs de
  l'original, puis reproduisez-les. **Un nouveau site ne doit JAMAIS ressembler à
  un site déjà conçu** (ex. ne pas ré-habiller « Éclat » en changeant les
  couleurs). Seuls l'**architecture technique React et les conventions** de ce
  dépôt sont réutilisés — jamais le design d'un site précédent.
- **⭐ Images & Hero = ceux du modèle d'origine, UNIQUEMENT.** Interdiction
  absolue de réutiliser les images ou le Hero d'un site déjà conçu. Extrayez et
  utilisez le Hero et les visuels du modèle Framer fourni pour CE site. Si une
  image manque ou qu'il en faut une autre, **discutez-en d'abord** avec le
  propriétaire — ne prenez jamais une image d'un site précédent.
- **Jamais d'avis / témoignages patients** (« client reviews ») sur aucun site.
  Ne recréez pas de section Testimonials, même si le modèle d'origine en a une.
- **⭐ Pages obligatoires (RÈGLE) — chaque site DOIT contenir exactement ces 5
  pages** : **Accueil, À propos, Soins, Équipe, Contact**. Le formulaire de
  rendez-vous (jour + heure) est une **section** (dans l'Accueil et/ou la page
  Contact), pas une page autonome. Pas de FAQ, Blog, Legal en pages autonomes.
  (Remplace l'ancienne liste qui incluait une page « Rendez-vous » autonome et
  excluait « Équipe ».)
- **Fidélité au modèle Framer « Oralcare »** : Hero plein écran (image de fond +
  texte blanc superposé + titre animé lettre par lettre), en-tête en **barre
  blanche pleine largeur** (coins doux 8px, ombre discrète `0 2px 4px
  rgba(13,27,21,.2)`, sans puces, onglet actif en pastille verte, bouton
  d'action foncé), révélations au défilement (texte mot par mot), comparateur
  **avant/après interactif**.
- Le formulaire de rendez-vous inclut **jour (date) et heure**.
- **Logo professionnel pour chaque site.** Concevez toujours un logo soigné et
  sur mesure (icône + typographie de marque) pour chaque cabinet — jamais un
  simple texte brut ni un placeholder générique. Il doit rester cohérent avec la
  palette et les polices du site.

## Commandes

```bash
npm install       # installer les dépendances
npm run dev       # serveur de dev (Vite, Fast Refresh)
npm run build     # build de production -> dist/
npm run preview   # prévisualiser le build
npm run lint      # ESLint
```

Aucune suite de tests n'est configurée à ce jour.

## Stack

React 18 + React Router 6 (SPA, `BrowserRouter`) + Framer Motion, bundlé par
Vite. Pas de TypeScript, pas de Tailwind — **CSS classique** avec des variables
de design (tokens). Chaque composant/section importe son propre fichier `.css`.

## Architecture

- `src/main.jsx` — point d'entrée : `BrowserRouter` → `AppProviders` → `App`.
- `src/App.jsx` — routeur. **Toutes les pages sont en lazy-load** (code-splitting)
  et enveloppées dans `<AnimatePresence>` pour les transitions de page.
- `src/providers/AppProviders.jsx` — `MotionConfig` global (respecte
  `prefers-reduced-motion`) + Context API extensible.
- `src/data/` — **source unique de contenu** (aucune donnée codée en dur dans les
  composants) :
  - `soins.js` — les 21 soins + `CATEGORIES` + helper `soinBySlug`.
  - `site.js` — navigation, coordonnées, horaires, stats, avantages, technologies.
    ⚠️ Coordonnées = **placeholders** à remplacer par le praticien.
  - `temoignages.js` — avis patients.
  - `images.js` — manifeste clé→chemin (`public/images/<clé>.webp`) + générateur
    de `placeholder()` SVG de repli.
- `src/lib/motion.js` — **variants Framer Motion partagés** (`fadeUp`, `scaleIn`,
  `staggerParent`, `pageTransition`…). Réutilisez-les plutôt que de redéfinir des
  animations inline.
- `src/components/ui/` — primitives : `Button`, `Img`, `Reveal`, `SectionHeader`,
  `SoinCard`, `PageWrapper`, `Loader`, `Logo`.
- `src/components/layout/` — `Header` (collant, se masque au scroll), `MobileMenu`,
  `Footer`, `Layout`, `ScrollToTop`.
- `src/components/sections/` — sections réutilisées entre pages : `Hero`,
  `Testimonials`, `MapHours`, `CTA`.
- `src/pages/` — `Home`, `Soins`, `About`, `Contact`, `RendezVous`, `NotFound`.
- `src/styles/` — `tokens.css` (design system), `global.css` (reset), `forms.css`.

## Conventions importantes

- **Images** : n'insérez jamais une `<img>` directement. Utilisez
  `<Img imageKey="…" alt="…" />` — il gère le lazy-loading et le repli sur un
  placeholder de marque si le fichier réel est absent. Toute nouvelle image doit
  avoir une clé dans `src/data/images.js`.
- **Apparition au scroll** : enveloppez dans `<Reveal>` (ou utilisez les variants
  de `lib/motion.js` avec `whileInView`), ne recodez pas la logique d'observation.
- **Boutons / liens** : utilisez `<Button to|href|…>` (rend `Link`, `a` ou
  `button` selon les props) pour garder le retour tactile (hover/tap) cohérent.
- **Couleurs & espacements** : toujours via les variables de `tokens.css`
  (`var(--color-ink)`, `var(--space-4)`…), jamais de valeurs en dur.
- **Surfaces sombres** : ajoutez `data-surface="dark"` sur un conteneur pour
  inverser automatiquement les tokens sémantiques (texte clair sur fond sombre).
- **Le flux « soin → rendez-vous »** : chaque `SoinCard` pointe vers
  `/rendez-vous?soin=<slug>`. La page `RendezVous` lit ce paramètre pour
  pré-sélectionner le soin. Conservez ce contrat si vous modifiez l'un ou l'autre.

## Formulaires

Contact et Rendez-vous valident côté client et affichent un état de succès, mais
**n'envoient rien** : brancher un backend (Netlify Forms, Formspree, e-mail) est
laissé au déploiement.

## Origine du design

Reconstruit à partir d'un export Framer du modèle « Oralcare ». Palette :
`#0d1b15`, `#d1fc71`, `#f2f2ef`, `#595e5c`. Polices : Bricolage Grotesque
(titres), Figtree / Jost (texte), Gilda Display. Respectez cette identité.

## Site 1 — « Éclat » (TERMINÉ, sert de référence)

Premier cabinet livré. ⚠️ On réutilise seulement son **architecture technique et
ses conventions** (structure React, composants, données, déploiement) — JAMAIS
son design : chaque site copie fidèlement SON PROPRE modèle (voir règle capitale).

- **Marque** : « Éclat — Cabinet dentaire » (nom court, fictif). Baseline
  « CABINET DENTAIRE ». Logo sur mesure = badge (dent stylisée + étincelle
  « éclat »), composant `Logo` adaptatif (`onDark` pour surfaces sombres),
  décliné en favicon et en fichier `public/brand/eclat-logo.svg`.
- **Données 100 % fictives (démo)** : coordonnées, e-mail `contact@eclat-dentaire.fr`,
  adresse, téléphones, équipe (Dr. Camille Lefèvre — fondatrice, Dr. Amélie
  Rousseau, Dr. Julien Mercier) — tout est factice et cohérent. Ne pas mettre de
  vraies données.
- **En-tête fidèle au modèle** (valeurs extraites de l'export) : barre blanche
  pleine largeur (max 1360px), coins 8px, ombre `0 2px 4px rgba(13,27,21,.2)`,
  hauteur 64px, sans puces, onglet actif en pastille verte, bouton d'action
  foncé. Logo + wordmark « Éclat » + baseline à gauche.
- **Hero** : plein écran (100vh), titre monumental **96px / graisse 500 /
  interlettre −0.03em**, animé lettre par lettre (`SplitText`).
- **Révélation mot par mot** au défilement : composant `WordReveal`, câblé dans
  `SectionHeader` (titre + intro de chaque section).
- **Comparateur avant/après** : DEUX vraies images dents du modèle
  (`avant-dents.webp` = dents naturelles / `apres-dents.webp` = éclaircies).
  ⚠️ **Aucun filtre CSS** (sepia/hue) sur l'image « avant » : elle est déjà la
  bonne photo — un filtre la rendrait « cariée ».
- **Réservation** : un seul formulaire partagé `BookingForm` (prénom, nom,
  téléphone, e-mail, jour + heure, sélection des 21 soins, message), utilisé à
  l'identique sur la page `/rendez-vous` ET la section de l'accueil.
- **Page Nos soins** : 21 soins filtrables par catégorie (remontage par clé
  `key={filter}`, pas d'`AnimatePresence popLayout` — évite l'écran blanc).
- **Images** : dans `public/images/`, versionner le nom du fichier (`-v2`) quand
  on change le contenu, pour casser le cache navigateur/CDN de GitHub Pages.
- **Déploiement** : GitHub Pages, base `/Oral/`. Le React est LE produit livré.
  L'export Framer d'origine, traduit, reste en **référence visuelle** sous
  `/Oral/comparaison/` (build via `_site2/`), pas un livrable.

## Contexte multi-sites

Ces conventions (structure, français, fidélité au design d'origine) s'appliquent
aux sites de cabinets dentaires produits ici. Le futur « studio » qui présentera
ces sites à la vente est l'exception et suivra ses propres règles.
