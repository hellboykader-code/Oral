# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Langue

Ce projet et ses contenus sont **en français** (site pour cabinets dentaires en
France). Rédigez le contenu utilisateur, les libellés et les commentaires en
français. Le propriétaire de ce dépôt communique en arabe.

## Règles produit (permanentes)

- **Jamais d'avis / témoignages patients** (« client reviews ») sur aucun site.
  Ne recréez pas de section Testimonials, même si le modèle d'origine en a une.
- Pages conservées uniquement : **Accueil, Nos soins (21 soins), À propos,
  Contact, Rendez-vous**. Pas de FAQ, Blog, Team, Legal en pages autonomes.
- **Fidélité au modèle Framer « Oralcare »** : Hero plein écran (image de fond +
  texte blanc superposé + titre animé lettre par lettre), en-tête en **capsule
  blanche flottante** (puces « • », onglet actif en vert), révélations au
  défilement (texte mot par mot), comparateur **avant/après interactif**.
- Le formulaire de rendez-vous inclut **jour (date) et heure**.

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

## Contexte multi-sites

Ces conventions (structure, français, fidélité au design d'origine) s'appliquent
aux sites de cabinets dentaires produits ici. Le futur « studio » qui présentera
ces sites à la vente est l'exception et suivra ses propres règles.
