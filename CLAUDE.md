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
- **⭐ RÈGLE — 5 propositions de logo au choix.** À chaque conception d'un logo
  (nouveau site ou refonte), proposez **5 variantes** au propriétaire sous forme
  d'images et laissez-le **choisir** avant de finaliser. Ne jamais intégrer un
  logo sans que le propriétaire ait sélectionné sa version parmi les 5.

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
aux sites de cabinets dentaires produits ici. Le « studio » qui présente ces
sites à la vente suit ses propres règles (voir plus bas).

## Leçons apprises — erreurs à NE PAS répéter (IMPORTANT)

Retours d'expérience sur les exports Framer (NoCodeExport). À relire avant chaque
nouveau site.

- **⭐ Framer réinjecte l'anglais / les sections APRÈS hydratation, depuis les
  chunks `.mjs`.** Traduire le SSR (HTML) ne suffit pas : le texte anglais
  revient sur un vrai navigateur. → Traduire aussi les chaînes dans les `.mjs`,
  ET masquer les sections indésirables via une règle **CSS `!important`** sur un
  sélecteur `[data-framer-name="…"]` (elle survit à la reconstruction React).
  Ne PAS masquer via `el.style.display='none'` en JS seul : React l'annule.
- **⭐ Tester VISUELLEMENT (screenshot) chaque page après changement, avant de
  déployer** — pas seulement le DOM. Un test headless court (6 s) ne reproduit
  pas toujours ce que voit l'utilisateur (hydratation, cache, breakpoints).
- **Une modification à la fois**, on vérifie, puis on déploie **une seule fois**
  à la fin. Éviter le « je corrige une chose, j'en casse une autre ».
- **Cache + délai GitHub Pages** : après `git push`, le déploiement prend 2-3 min ;
  le navigateur garde l'ancienne version. Toujours attendre + **hard refresh
  (Cmd+Shift+R)** et **vérifier le live** avant d'affirmer que c'est réglé.
- **Titres animés `SplitText`** : le texte est découpé en `<span>` par lettre/mot ;
  traduire la chaîne contiguë ne suffit pas → remplacer le contenu de l'élément
  après hydratation (JS qui matche le `textContent` normalisé) ou dans le `.mjs`.
- **Logo** : Framer remplace le `<img>` local par une URL CDN à l'hydratation →
  rediriger l'URL CDN vers le fichier local (dans le `.mjs` + JS `forceLogo`).
  Logos toujours à **fond transparent** (pas de pastille foncée).
- **Formulaire injecté dans Framer** : piloter l'envoi par un **clic bouton**
  (`type="button"`, pas de submit natif → évite la navigation du routeur Framer),
  et **re-cibler l'ancre vivante** (le vrai `<form>` Framer) à chaque rendu pour
  que l'injection soit auto-réparante si React reconstruit la zone.
- **Anciennes cartes de services** : les remplacer par NOS cartes photo — accueil
  = aperçu 6 soins + bouton « Voir tous nos soins », page Soins = 21 soins.
- **Images CDN Framer indisponibles dans cet environnement** (proxy) : pour les
  aperçus/vidéos, l'utilisateur fournit les captures/enregistrements depuis SON
  navigateur (où le rendu est complet). Pour récupérer une vidéo Drive : la rendre
  « lien public », `curl` via `drive.usercontent.google.com/download?...&confirm=t`
  (le connecteur Drive refuse > 10 Mo), puis compresser avec `ffmpeg`
  (`imageio-ffmpeg` si ffmpeg absent) : `scale=760:-2, fps=24, -c:v libx264,
  -crf 30, -an, -movflags +faststart` → ~300 Ko, + poster (`-ss 1 -vframes 1`).

## ⭐ RÈGLE — Intégrer chaque site livré au studio (systématique)

Le studio est le repo **`export-kader-framer-website-mrz06s5b`** (marque
« DentWebPro »), déployé sur GitHub Pages. Sa galerie « Choisissez le site de
votre cabinet » est générée par `assets/dwp-home.js` (tableau `LIVE` = sites réels
en tête, tableau `CLINICS` = modèles démo ensuite) + `assets/dwp-portfolio.css`.

**À la fin de CHAQUE site de cabinet livré, l'ajouter IMMÉDIATEMENT au studio**
comme **carte vidéo** (exactement comme Oléa / Novéo / Zenta) :

1. L'utilisateur enregistre une courte vidéo (scroll ~8-10 s) du site en ligne et
   la partage (Google Drive, lien public).
2. Télécharger + compresser (ffmpeg, réglages ci-dessus) dans
   `assets/realisations/<slug>.mp4` + poster `<slug>-poster.jpg`.
3. Ajouter une entrée au tableau `LIVE` de `assets/dwp-home.js` :
   `{name, city, url:"<URL live>", vid:"<slug>", brand:"#…", accent:"#…"}`.
   → La carte s'affiche en tête de galerie : vidéo **autoplay muted loop**, badge
   « En ligne », clic → ouverture du site en direct (nouvel onglet).
4. Déployer le studio et **vérifier le live**.

## Registre des sites livrés (à jour)

| Site | Ville | Repo | URL live |
|------|-------|------|----------|
| Éclat (référence React) | — | `hellboykader-code/Oral` | `/Oral/` |
| Oléa | Marseille | `export-kader1-framer-website-ms074a1w` | `…-ms074a1w/` |
| Novéo | Lyon | `export-kader9-framer-website-mrzfoouq` | `…-mrzfoouq/` |
| Zenta | Paris | `export-kader10-framer-website-mrzfwfoi` | `…-mrzfwfoi/` |
| **Studio** DentWebPro | — | `export-kader-framer-website-mrz06s5b` | `…-mrz06s5b/` |

Tenir ce tableau à jour à chaque nouveau site, et l'ajouter au studio (règle
ci-dessus). Note : chaque formulaire de réservation utilise encore un e-mail
**placeholder** — le remplacer par l'e-mail réel du praticien + activer FormSubmit.

## Rétrospective complète — demandes, erreurs, corrections (à mémoriser)

Analyse de tout ce qui a été fait sur les sites Framer (Oléa, Novéo, Zenta) et le
studio, pour ne PAS refaire les mêmes erreurs.

### A. Historique des demandes (intentions de l'utilisateur)
1. Parler **arabe (dialecte algérien)** avec l'utilisateur ; contenu des sites en
   **français**.
2. Éditer chaque export Framer → site de cabinet FR complet : traduction, rebrand
   (nom, médecins, adresse FR, tél FR, e-mail, horaires), **retrait des avis
   patients**, **logo sur mesure** (fond transparent), **5 pages**.
3. Corriger : hero qui disparaît, logo qui reste l'ancien, « beaucoup de phrases
   non traduites » (titres SplitText), performance au scroll.
4. Soins : remplacer les 6 services par **21 soins** (grille filtrable + 21 options
   dans le RDV) ; accueil = **6 + « Voir plus »**, page Soins = 21 ; **photos** par
   soin.
5. Nouveau **formulaire de réservation** (prénom, nom, e-mail, tél, soin (21),
   jour, heure) → envoi e-mail praticien + **message de remerciement**.
6. Rendre le formulaire ET les cartes **professionnels** (niveau Éclat).
7. Supprimer les sections **anglaises / filler** (investisseurs, « Our Client's
   Words ») sur toutes les pages.
8. **Studio** : afficher les sites livrés à la vente, DANS la galerie, avec **nom +
   visuel**, clic → site en direct ; puis passer du visuel à la **vidéo autoplay**.
9. Fournir des **prompts** (Claude Design, commandes d'édition) et **mémoriser les
   règles**.

### B. Erreurs commises → correction → règle
1. **Affirmé qu'un produit (claude.ai/design) n'existait pas** → il existe (beta).
   → Règle : ne jamais nier l'existence d'un produit récent ; vérifier / demander.
2. **Corrigé seulement `/services/` en oubliant l'ACCUEIL** (mêmes cartes anglaises).
   → Règle : appliquer chaque correction à **TOUTES les pages/instances**, pas une
   seule ; l'accueil a `Service`, la page Soins a `Sevice Cards` (noms distincts).
3. **Répété « c'est le cache » alors que c'était un vrai manque de ma part**.
   → Règle : vérifier que MON code couvre tous les cas AVANT de blâmer le cache ;
   confirmer sur le live.
4. **Masquage brut d'une section → grand vide gris** (la section testimonials
   « Sticky Section » restait, vide). → Règle : après avoir masqué, vérifier qu'il
   ne reste pas de section sœur vide ; masquer aussi les blocs testimonials/filler
   voisins.
5. **Tests headless courts (6 s) « tout en français » mais live en anglais**.
   → Règle : Framer réhydrate depuis les `.mjs` ; toujours **screenshot + live**,
   traduire les `.mjs`, masquer en **CSS `!important`**.
6. **Formulaire qui disparaît à l'envoi** (React reconcilie + submit natif navigue).
   → Correction : bouton `type="button"` (pas de submit), re-cibler l'ancre vivante,
   injection auto-réparante. Règle : ne jamais compter sur un nœud injecté stable
   dans un arbre React — le re-cibler à chaque rendu.
7. **Réutilisé des images d'Éclat** (contre la règle capitale) → l'utilisateur a
   donné une raison métier (Éclat sera supprimé) → accepté. Règle : énoncer la
   règle et **pousser une fois**, mais respecter l'override motivé du propriétaire.
8. **Studio : hypothèses fausses** (« Éclat y est » = juste le mot français
   « éclat » ; « sites déjà ajoutés » = faux, que des démos). → Règle : **vérifier
   les prémices** (grep + rendu) avant d'agir, et remonter l'écart au lieu de
   foncer.
9. **Mauvais emplacement dans le studio** (Artifact, puis bande séparée) alors que
   l'utilisateur voulait les cartes **dans la même galerie**. → Règle : clarifier
   l'emplacement exact avant de construire.
10. **Captures d'écran des sites impossibles** (images Hero CDN bloquées) →
    solution : l'utilisateur enregistre depuis SON navigateur ; récupérer via Drive
    public + `curl …&confirm=t` ; compresser avec ffmpeg.

### C. Playbook technique (procédures réutilisables)
- **Traduction** : SSR + `.mjs` + titres SplitText (matcher `textContent`
  normalisé). Vérifier sur un vrai rendu, pas seulement le DOM.
- **Masquer sections** (testimonials, filler, pricing, blog, anciennes cartes) :
  CSS `[data-framer-name="…"]{display:none !important}` (survit à l'hydratation).
- **Formulaire RDV injecté** : carte blanche, champs 2 colonnes, focus lime,
  bouton foncé ; envoi FormSubmit par clic bouton ; `.ob-thanks` de remerciement ;
  `render()` re-cible le `<form>` Framer vivant → auto-réparant.
- **Cartes soins** : média 4/3 + pastille catégorie superposée + titre + desc +
  lien fléché ; accueil = 6 + bouton, Soins = 21 filtrables ; images locales dans
  `assets/soins/` (issues des `soin-*.webp` d'Éclat, réutilisables car techniques).
- **Logo** : rediriger l'URL CDN → fichier local (mjs + JS `forceLogo`) ; fond
  transparent ; favicon assorti.
- **Studio (vidéo)** : Drive public → `curl …confirm=t` → ffmpeg
  (`scale=760:-2,fps=24,libx264,crf 30,-an,+faststart` ≈ 300 Ko) + poster →
  entrée `LIVE` dans `assets/dwp-home.js` (`vid` = slug) → vidéo autoplay muted
  loop, clic → site live.
- **Déploiement** : push → GitHub Pages (2-3 min) → **hard refresh + vérif live**.
- **Communication** : arabe algérien avec l'utilisateur ; jamais annoncer « réglé »
  sans avoir vérifié le live.
