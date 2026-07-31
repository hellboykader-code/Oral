# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Langue

Ce projet et ses contenus sont **en français** (site pour cabinets dentaires en
France). Rédigez le contenu utilisateur, les libellés et les commentaires en
français. Le propriétaire de ce dépôt communique en arabe.

## Règles produit (permanentes)

- **⭐ RÈGLE CAPITALE — AUCUNE ville / localisation / adresse réelle sur un site.**
  Ne JAMAIS inscrire une ville (Paris, Créteil, Toulouse, Marseille, Lyon…) ni une
  adresse précise sur un site de cabinet de démonstration. Un acheteur potentiel
  penserait que le site a **déjà été vendu** à un autre praticien. Utiliser des
  **placeholders neutres** (« Votre ville », « Votre adresse », « — ») que le
  client remplira lui-même. **Supprimer toute mention de ville existante** dans le
  contenu, les adresses, les titres de pages et les données.
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

## ⭐ RÉFÉRENCE — Comment fonctionne Framer (`FRAMER-SYSTEM.md`)

**À lire AVANT toute édition d'un export Framer.** Le fichier `FRAMER-SYSTEM.md`
(racine du dépôt) documente le système Framer en entier : modèle SSR + hydratation
React, chunks `.mjs`, **routeur** (règle `Ei` : `/route/` matche, `/route/index.html`
= 404), tokens/presets de design, breakpoints, SplitText, CMS, formulaires, code
overrides, et le **playbook d'édition** (traduire les `.mjs`, filet `clinic-fix`,
liens en `/route/`, cache-bust, sources d'anglais cachées). Consulter ce fichier
plutôt que deviner.

- **⭐ Cause racine « les sous-pages retombent sur l'accueil » (section K de
  FRAMER-SYSTEM.md).** La route INITIALE vient du JSON `data-framer-hydrate-v2` du
  SSR (`#main`), PAS de l'URL. Si une sous-page n'a pas ce dataset, elle tombe dans
  le fallback qui matche `location.pathname` (`/export-<repo>/about`) contre `/about`
  → aucun match → l'accueil s'affiche par-dessus (« apparaît 1 s puis disparaît »).
  **Correctif définitif** : injecter `data-framer-hydrate-v2` (routeId de la route +
  breakpoints copiés de l'accueil) sur CHAQUE sous-page. Extraire path→routeId de la
  table de routes en remontant les accolades depuis chaque `path:`. Certains exports
  (kader1, kadaaaaa-ms1twfho) ont déjà le dataset partout → immunisés ; d'autres
  (reddent1, kader9, kader10, med12, vivadent, dentartt) ne l'avaient que sur
  `index.html` → corrigés. Vérifier aussi que le dataset d'accueil pointe la route
  d'accueil (`hero-banner`) et pas `/blogs` (bug vu sur med12).

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
- **⭐ Vérification « zéro anglais » = EXHAUSTIVE, jamais par regex étroite.**
  Erreur commise : j'ai testé avec une regex de quelques mots-clés et conclu
  « tout en français » alors que la page live était pleine d'anglais (héros,
  cartes de services, stats, témoignages, FAQ…). → Extraire TOUT le texte de
  CHAQUE page rendue (textContent de chaque feuille), lister toutes les chaînes
  contenant des mots anglais, traduire, ré-extraire jusqu'à liste VIDE. Ne jamais
  dire « fini » avant d'avoir confirmé zéro anglais sur le rendu réel de toutes
  les pages (et sur le live après déploiement).
- **⭐ Attention au dépôt/URL de déploiement.** Le site peut être servi à la
  RACINE (`user.github.io/`) et non sous `/export-<repo>/`. Si je pousse dans le
  dépôt projet mais que l'utilisateur déploie à la racine, il voit une ancienne
  version. Confirmer où le site est réellement en ligne, et faire correspondre
  les base paths (`/` racine vs `/export-<repo>/` projet).
- **Convertir un template d'un autre secteur (médical → dentaire) est un
  combat** : préférer partir d'un template DÉJÀ dentaire. Si conversion imposée,
  traduire ET remplacer tout le vocabulaire métier (spécialités, conditions,
  rôles des médecins) + retirer avis/FAQ/blog.
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

### D. Purge exhaustive de l'anglais (leçon med12 « Vitaléa ») — sources cachées

Traduire les pages ne suffit PAS. L'anglais se planque dans des fichiers annexes
qu'un simple parcours des `index.html` ignore. Checklist **obligatoire** avant de
dire « zéro anglais » :

1. **`searchIndex-*.json` (Framer)** sous `assets/framer/sites/<id>/` : index de
   recherche = tout le contenu anglais du template (titres, descriptions, blogs
   supprimés). Souvent **non référencé** par le code → le **vider** (`{}`) ou le
   traduire. Vérifier `grep -rl searchIndex assets/framer` pour savoir s'il est lu.
2. **`search-index.json` (racine) + `<meta name="framer-search-index">`** : petit
   index des pages → réécrire en français (titres/descriptions/excerpts dentaires).
3. **`seo-report/`** (data.json + index.html `lang="en"`) : artefact d'audit,
   **non référencé** → **supprimer** le dossier.
4. **`.mjs` — fragments SplitText** : un titre coupé mot par mot (« Comprehensive
   Medical », « Mission & Vision », « Trusted by Patients, » / « Proven by Care »)
   n'est PAS attrapé par un remplacement de la phrase entière. Les traduire
   fragment par fragment dans le `.mjs` **et** les ajouter à la map `SPLIT` du JS
   post-hydratation (double filet).
5. **Bios praticiens dans le `.mjs`** (« Specialist in cardiovascular… »,
   « Expert in neurological… ») = texte visible → traduire en rôle **dentaire**.
6. **`alt=` / `originalFilename=`** (« hospital-img », « waiting-room-hospital ») :
   texte lu par lecteurs d'écran → traduire aussi.
7. **Blog** : ne pas seulement masquer — **`rm -rf blogs/`** (pages + articles) ;
   garder le masquage CSS de la *section aperçu* d'accueil (`[data-framer-name*="Blog"]`).
8. **Faux positifs à NE PAS toucher** : `data-framer-name` = **noms de calques
   internes** (« About Block », « Healthcare Process », « Services Main »),
   identifiants de code (`ico.hospital`), noms de police (`GF;Parisienne` ≠ ville).
   Invisibles → les modifier casse animations/sélecteurs. Ne jamais remplacer un
   token nu `"Home"`/`"Services"` dans le `.mjs` (route/composant) → passer par le
   JS `fixNav`.

**Méthode de vérif** : extraire le texte visible de **toutes** les pages
(HTML strip-tags + `.mjs` littéraux backtick/`children:`), lister les segments
anglais, traduire, **re-extraire jusqu'à 0**. Puis **vérif live** (le CDN Pages
sert l'ancienne version ~1-3 min : re-poll jusqu'à disparition du marqueur).

**Cible de déploiement** : vérifier `curl -I` la **racine** `…github.io/` (souvent
404 = pas de user-pages) **et** le **project-pages** `…github.io/<repo>/` (200).
med12 n'a QUE le project-pages → c'est là que vivent les corrections.

### E. Pièges de traduction par `str.replace` (leçons vivadent + dentitive)

- **⭐ NE JAMAIS mapper un mot court/générique seul** (`treatments`, `experience`,
  `professionals`, `Services`, `Blog`) : il est **sous-chaîne** de phrases plus
  longues et les **corrompt** (`Gentle treatments`→`Gentle de précision`,
  `Our Dental Treatments`→`Our Dental Soins`, `dental treatments?`→`dental de
  précision?`). Toujours traduire la **phrase entière**, jamais le mot nu ; pour la
  nav, passer par le JS `fixNav` (match `textContent` exact), pas un replace global.
- **Ordre des remplacements** : phrases longues **d'abord**, marque en **dernier**.
  Si un mot court est remplacé avant la phrase qui le contient, la phrase ne matche
  plus → chaîne mixte FR/EN. (C'est la même cause que les fragments SplitText.)
- **Entités HTML** : le SSR encode `&`→`&amp;`. Un map avec `&` littéral **rate**
  `General &amp; Cosmetic`. Prévoir la variante `&amp;` (le `.mjs`, lui, garde `&`).
- **Apostrophes typographiques** : le texte Framer utilise `’` (U+2019), pas `'`.
  Un map écrit avec `'` droit **rate** `You’ll`, `smile’s`. Copier l'apostrophe
  exacte de la source.
- **Détection des chaînes mixtes** : le grep source ne les voit pas (moitié FR).
  Seul le **rendu réel** (Playwright, texte visible par nœud) les révèle → toujours
  re-render et scanner `innerText` mot par mot après traduction.
- **Templates déjà dentaires (vivadent « Verve Dent », dentitive)** : souvent une
  **ville réelle** en dur (Paris, Chennai) → appliquer la RÈGLE no-city. Retirer
  aussi les badges promo du template : `framer.link`/`framer.com`/`uihub.design`,
  « Made by … », « Built in Framer », « Created by … », « Get/Remix Template »,
  et le dossier `seo-report/`. Landing 1 page (dentitive) : la règle « 5 pages »
  ne s'applique pas telle quelle → à valider avec le propriétaire.

### F. Gros site multi-pages (leçon dentartt « DentArt », 17 pages)

- **Réduire le volume AVANT de traduire** : `rm -rf blog legal seo-report` (blog +
  pages légales = des centaines de lignes d'anglais non requises par les 5 pages).
- **Mot de pays/nom propre = même piège que LESSON E** : `Australia`→`France`
  **corrompt** `Australian Dental Association`→`Francen…`. Ne pas mapper un mot nu
  qui est sous-chaîne (pays, université, « Whitening » dans « Teeth Whitening »).
- **SplitText n'est PAS que dans les `<h*>`** : un titre animé peut vivre dans un
  `<p>` (hero, footer). Le `clinic-fix` qui remplace un titre par son texte FR doit
  balayer `h1,h2,h3,h4,p` (borne longueur < 400 pour éviter les gros blocs).
- **Avis dans un carousel sans nom fiable** (`data-framer-name` = `Quote`/`Star`/
  `Big`…) : les masquer **par contenu** (regex sur une phrase d'avis → remonter au
  `topSection` et `display:none`), comme `hideFiller` de med12 — pas par nom.
- **`placeholder=` des formulaires** = texte visible : traduire (nom « Emily
  Anderson », tél `+61…`, `emily@email.com` → FR).
- **Méthode qui marche** : traduire par lots (accueil/à-propos, soins, bios), puis
  **render Playwright page par page** avec un scan `innerText` par nœud, en
  ignorant les faux positifs FR (`consultation`, `patient`, `implant` = français) ;
  itérer jusqu'à 0 anglais visible réel sur CHAQUE type de page.

### G. ⭐⭐ SESSION DÉCISIVE — « les boutons/sous-pages retombent tous sur l'accueil » (RÉSOLU)

Le bug le plus tenace de tous les sites Framer. Symptôme rapporté par le
propriétaire : « les boutons cliquent mais mènent tous à l'accueil » puis « la
page apparaît une seconde puis disparaît ». **Cause racine trouvée et corrigée
définitivement** — tout est ici pour ne JAMAIS refaire les fausses pistes.

**1. La vraie cause (démontrée dans le code, pas devinée).** Au chargement,
Framer ne déduit PAS la route depuis l'URL en priorité. `script_main` lit dans
l'ordre : (a) `document.querySelector('#main').dataset.framerHydrateV2` — un JSON
`{routeId, localeId, breakpoints}` **injecté dans le SSR** → routeId pris
directement, AUCUN matching d'URL ; (b) l'en-tête `Server-Timing` (absent sur
GitHub Pages) ; (c) **seulement sinon** (`if(!r||!i)`) le fallback
`match(routes, decodeURIComponent(location.pathname))`. Or, sur beaucoup
d'exports, SEUL `index.html` porte `data-framer-hydrate-v2` ; les sous-pages ont
un `<div id="main">` **nu** → elles tombent dans le fallback (c), qui compare
`/export-<repo>/about` à une route `/about` → aucun match → **rendu de l'accueil
par-dessus le SSR correct**. C'est exactement le « apparaît 1 s puis disparaît ».

**2. Fausses pistes écartées (ne pas y retourner).**
- ❌ « c'est le cache » — non, c'était un vrai défaut de routing.
- ❌ prefixer les `path:` de la table de routes SEUL — utile mais **insuffisant**
  si le matcher ne normalise pas le `/` final. La vraie clé, c'est le dataset.
- ✅ Diagnostic sûr : `grep 'framer-hydrate-v2'` sur `index.html` vs une sous-page.
  Si présent partout (kader1, kadaaaaa-ms1twfho) → immunisé. Si présent seulement
  sur l'accueil (reddent1, kader9, kader10, med12, vivadent, dentartt) → **bug**.

**3. Correctif DÉFINITIF (robuste, indépendant du chemin de déploiement).**
Injecter le dataset sur CHAQUE sous-page :
`<div id="main" data-framer-hydrate-v2="{&quot;routeId&quot;:&quot;<ID>&quot;,&quot;localeId&quot;:&quot;default&quot;,&quot;breakpoints&quot;:[…copiés de l'accueil…]}">`.
- `breakpoints` = copier tel quel le tableau du dataset de `index.html` (identique
  pour tout le site). `localeId` = `default`.
- `<ID>` = routeId de la route dont le `path` finit par `/X` (X = nom du dossier).
- **Extraction path→routeId (piège technique résolu)** : la table est
  `…<id>:{elements:{…},page:P(()=>import(…)),path:`/X`}`. Un regex simple CASSE car
  les objets ont des accolades imbriquées non vides (`{…trigger}`). **Solution qui
  marche** : pour chaque `path:`…`` remonter caractère par caractère en comptant
  les accolades (`}`→+1, `{`→si depth 0 c'est l'ouvrante sinon −1) ; l'`<id>` est
  l'identifiant juste avant `:{`. Validé en croisant avec le dataset d'accueil
  (home doit = augiA20Il, l'`elements` du home contient `hero-banner`).
- **Vérification finale obligatoire** : pour chaque sous-page, `routeId` injecté ==
  `routeId` du path dans la table → script « ALL CONSISTENT ». Fait sur les 6 sites.
- Garder AUSSI le préfixe des `path:` (URLs correctes en nav client-side) + le
  cache-bust (`script_main.…mjs?fr=2` sur les refs HTML, car le `.mjs` garde son
  nom → CDN sert l'ancien ~10 min).

**4. Bug bonus trouvé sur med12** : le dataset de `index.html` pointait `nQCqzkvcq`
(= route `/blogs`) au lieu de la route d'accueil `augiA20Il` → l'accueil hydratait
en page blog. Corrigé. **Règle** : toujours vérifier que le dataset d'accueil pointe
la route home (dont `elements` contient `hero-banner`), pas `/blogs`.

**5. Nav injectée maison (reddent)** : boutons en `<div role="link">` +
`window.location.assign('/export-<repo>/route/')` (jamais `<a>` : href réécrit en
`javascript:void(0)`). Une fois les datasets injectés, la navigation dure (assign)
charge la sous-page qui s'hydrate correctement via SON dataset. C'est ça qui a
enfin fait « marcher les boutons ».

**6. État des 6 sites corrigés** : reddent1 ✅push, kader9 ✅push, kader10 ✅push,
dentartt ✅push, **med12 ⚠️ commit local** (proxy `repository not found`),
**vivadent ⚠️ commit local** (idem). Immunisés (datasets déjà présents, NON
touchés) : kader1/Oléa, kadaaaaa-ms1twfho, dentitive1 (mono-page). Vides
(README seul, pas des sites) : froore ×2, kadaaaaa-ms0u234t.

**7. Pièges d'outillage rencontrés (à retenir)** :
- `git push … | tail -1` **masque le code de sortie** → un échec (« not found »)
  s'affiche faussement comme « PUSHED ». Tester `if git push …; then … else …`.
- Ne pas tester le comportement client via Playwright ici : le CDN
  `framerusercontent.com` est bloqué → React #405, rendu blanc. **La vérification
  fiable est au niveau source** (grep du dataset + cohérence routeId/table).

**8. Doc mémoire** : tout ce mécanisme est détaillé dans `FRAMER-SYSTEM.md`
**section K** (à lire avant tout futur export). Réappliquer le correctif 3 à tout
nouveau site multi-pages dont les sous-pages n'ont pas `data-framer-hydrate-v2`.

**9. Bouton « En savoir plus » qui RÉSISTE (reddent, capture live)** : la détection
exacte par texte a échoué car le bouton contient une **icône flèche (↗) enfant** et
des **guillemets `»`**. Le garde `if(kids.length>0) return` sautait donc le bouton,
et `t==='En savoir plus'` ne matchait pas `"En savoir plus »↗"`. **Correctif** :
`isMoreLabel(t)` qui **normalise** (retire `« » ↗ →`, minuscule) puis compare, sur
sélecteur élargi `a,button,[role="link"],[role="button"]` avec garde longueur
`t.length>42 → skip` (au lieu du garde enfants) pour ne pas attraper la carte
entière, et exclusion de `#rd-navbar`. **Règle** : ne jamais matcher un libellé de
bouton par égalité stricte — normaliser icône/guillemets/casse d'abord.

**10. ⭐⭐ VRAIE cause : Framer DOUBLE le libellé des boutons.** Après enquête sur le
DOM réel (strip-tags de l'`<a data-framer-name="Button Primary">`), `textContent` =
« À propos de RedDent**À propos de RedDent** » — Framer rend une **2ᵉ copie masquée
du texte pour l'effet hover**. C'est pour ÇA que TOUS les matches par égalité stricte
échouaient (À propos, En savoir plus, Voir tous nos soins) — jamais une histoire de
cache. **Correctifs cumulés (les 3)** :
- `btnText(el)` = normalise + **dé-duplique** (`while moitié==moitié → couper`) ;
- matching en **`startsWith`** (pas `===`) car quand l'icône/guillemet est entre les
  deux copies, la dé-dup laisse `"en savoir plus en savoir plus"` (asymétrique) ;
- **intercepteur de clic GLOBAL** `document.addEventListener('click',fn,true)`
  (capture) qui `closest('a,button,[role])` → mappe le libellé → `preventDefault()`
  + `stopImmediatePropagation()` + `location.assign(dest)`. **Un seul** listener sur
  `document` → survit à tous les re-render React et **précède le routeur Framer**
  (bien plus fiable que réattacher un handler par nœud, qui saute au re-render).
- **Masquer un bouton** (« Voir tous nos soins ») : CSS `!important` par conteneur
  (`[data-framer-name="Service Section"] [data-framer-name="Button Primary"]`) — pas
  par JS `display=none` (React l'annule). Vérifier d'abord qu'il n'y a qu'UN
  `Button Primary` dans la section (grep de bornes de `<section>`).
**Règle d'or** : pour un CTA Framer, toujours (a) lire le `textContent` RÉEL du nœud
(il est doublé), (b) matcher en `startsWith` après dé-dup, (c) piloter le clic par un
**listener global capture sur `document`**, jamais par le href (réécrit en void(0)).

**11. ⭐⭐⭐ POURQUOI les boutons de l'ACCUEIL résistaient encore (RÉSOLU, vérifié
Playwright).** Sur hydratation COMPLÈTE (celle du vrai navigateur, pas reproductible
ici car `init.mjs`/`.framercms` viennent du CDN framerusercontent bloqué), le clic sur
un `<a>` interne ne déclenche PAS le listener `document` capture — Framer neutralise
l'événement au niveau de son gestionnaire de gestes (motion) sur le nœud. Le listener
`document` marchait en test synthétique/jsdom mais PAS sur le vrai geste. **La barre de
nav marchait** parce qu'elle est faite de `<div>` dans `<body>` (hors #main) avec un
`addEventListener('click')` direct + `location.assign`. **Correctif définitif = même
technique pour les boutons de page : une COUCHE D'OVERLAYS.** Un `<div id="rd-ovl-layer">`
dans `<body>`, contenant un `<div>` transparent `position:fixed` par CTA, repositionné
sur le bouton via `getBoundingClientRect()` (throttle rAF sur scroll/resize + interval).
Chaque overlay porte `data-dest` et navigue par `location.assign` au clic. Le mapping
libellé→destination réutilise `destFor` (nav, pastilles, cartes soins→Contact, cartes
praticiens→Équipe). Vérifié : en forçant tous les `#main a[href]` à `void(0)` (= l'état
réel), les 4 familles de boutons naviguent correctement.
- **⚠️ Piège décisif** : NE PAS faire les overlays en `<a href>` — **Framer réécrit
  TOUS les `<a href>` du DOCUMENT en `javascript:void(0)`, même hors `#main`** (balayage
  global). Un overlay `<a>` voit donc son href vidé → ne navigue plus. D'où le `<div>`.
- **Règle** : tout élément cliquable injecté qui doit naviguer = `<div>`/`<span>` +
  handler `location.assign`, JAMAIS `<a href>` (Framer le neutralise partout).

**12. ⭐ Composant « rolling text » (hover) → le `textContent` du bouton contient du CSS.**
Sur Novéo (kader9), les liens de nav ont un effet « rolling text » : Framer injecte un
`<style>` DANS le `<a>`, donc `el.textContent` = « soins { --font-size:14px; … } span { … } »
(le CSS du `<style>` fuit dans le textContent). Le mappage `destFor` par libellé échouait →
0 overlay sur la nav. **Correctif** : dans `btnText`, **couper au premier `{`** (`t.split('{')[0]`)
AVANT de normaliser/dé-dupliquer, pour ne garder que le libellé. Vérifié Playwright (#main
`<a>`→void(0)) : nav + CTA + cartes soins naviguent (31 overlays). **Règle** : pour lire le
libellé d'un bouton Framer, retirer d'abord tout bloc `{…}` (CSS injecté) + l'artefact
`rolling-text…`, puis dé-dupliquer.

**13. ⭐ Nos overlays de navigation TUENT les interactions de survol natives (image qui
suit le curseur).** RedDent : le modèle Framer avait, dans la section Soins, une **image
qui suit le curseur** au survol de chacune des cartes (chaque `[data-framer-name="Service
Card Tablet"]` a son propre `[data-framer-name="Image Wrapper"] img` en `opacity:0
scale(.5)`, révélé au hover). Nos overlays de clic (`pointer-events:auto`, posés SUR les
cartes pour rattraper les `href` voidés) **interceptent le hover** → l'effet natif ne se
déclenche plus. **Correctif réutilisable** : ré-implémenter le hover **au niveau
`document`** (un listener `mousemove` sur `document` fire TOUJOURS, même quand un overlay
est la cible, car `mousemove` bubble). Une `<img id="rd-follow-img">` flottante dans
`<body>` (hors React → auto-réparante, `pointer-events:none`, `z-index` sous la navbar)
suit la souris avec un **lerp** (`fx+=(tx-fx)*.18`) ; à chaque move on **hit-teste** les
rects des cartes (`getBoundingClientRect`) et on affiche la **photo lue dans l'Image
Wrapper de la carte survolée** (`im.currentSrc||src||srcset[0]`) — donc auto-adaptatif au
nombre de cartes (4 ici). Refresh de la liste des cartes dans `apply()`. Vérifié
Playwright : hover carte → `opacity:1` + bonne image, sortie → `opacity:0`. **Règle** :
quand on pose une couche d'overlays sur des éléments Framer, tout effet de **survol**
natif (image-follow, tilt, reveal) est perdu → le réimplémenter via un listener
`document`+`mousemove` (jamais un handler sur la carte, masquée par l'overlay).
- **⭐⭐ PIÈGE DÉCISIF — Framer RENOMME le calque à l'hydratation.** En SSR les cartes
  s'appellent `data-framer-name="Service Card Tablet"` ; **après hydratation complète**
  (desktop), Framer les **remplace** par `data-framer-name="Service Card"` → un sélecteur
  sur le nom SSR trouve **0 carte** sur le vrai navigateur (l'effet ne marche qu'en test
  partiel-hydraté, jamais en live !). **Ne JAMAIS cibler par le nom du calque.** Cibler un
  ancrage **stable** (ici les 4 `[data-framer-name="Image Wrapper"]` DANS la section, qui
  survivent au renommage) puis remonter au lien/carte parent (`closest('a')`) comme zone
  de survol. + **Refresh perpétuel** de la liste (pas seulement < 20 s) car React
  reconstruit les nœuds. **Vérif OBLIGATOIRE en hydratation COMPLÈTE** : le CDN
  `framerusercontent` étant bloqué, le tester via **interception de route Playwright**
  (`ctx.route(/framerusercontent…/)` → `fetch` via `undici ProxyAgent` sur
  `HTTPS_PROXY` + `NODE_EXTRA_CA_CERTS` → `route.fulfill`). C'est le SEUL moyen de voir le
  DOM desktop réel (renommage, carousel, nœuds remplacés). Le test partiel-hydraté ment.

## ⭐⭐ RÈGLES PERMANENTES — Prospection (listes de médecins via Google API)

Le propriétaire fournira une **clé Google API**. À CHAQUE demande de « liste »,
appliquer ces règles SANS exception (les rappeler mentalement avant d'agir) :

0. **⭐ DÉCLENCHEUR « ( الامر الاحمر ) » (« l'ordre rouge »)** = quand le propriétaire
   écrit cette expression, lancer une recherche et **envoyer une liste de EXACTEMENT
   100 médecins**, en appliquant toutes les règles ci-dessous (sans site, ordre géo,
   zéro doublon). Chaque liste = **100 médecins**, jamais plus. Outil prêt :
   `scratchpad/prospect.py` (Places API New `places:searchText`, `MAX=100`, registre
   `.prospection-ledger.json` = SHA1 des numéros déjà fournis → exclusion auto).
   Clé Google API fournie par le propriétaire (dans l'historique de session).
1. **Uniquement les dentistes SANS site web.** Interroger Google Places, récupérer
   le champ `website`. **Ne garder QUE ceux dont `website` est vide.** S'ils ont un
   **Doctolib** ou une page **Facebook/Instagram** mais **pas de vrai site web**, on
   les **garde** (ils restent des prospects). Un `website` = domaine propre → **exclure**.
   (Un lien Doctolib/Facebook n'est PAS un site web.)
2. **Ordre géographique imposé :** commencer par **Paris**, puis **Île-de-France**,
   puis **le reste de la France**. Ne pas sauter à une autre région tant que la
   précédente n'est pas épuisée.
3. **⛔ JAMAIS de numéro de téléphone en double — y compris entre les jours.** Une
   liste aujourd'hui, une autre demain, une autre après-demain : **aucun médecin
   déjà donné une fois ne doit réapparaître**. Dédup par **numéro normalisé**
   (chiffres seuls). La mémoire des numéros déjà fournis = **la base de l'espace**
   (`espace/data/db.json`) : chaque liste générée est **importée dans l'espace**
   (source « Google ») → elle devient le registre permanent, et toute génération
   future **exclut tous les téléphones déjà présents** (lire via l'action admin
   `admin_data`/export avant de générer). Ainsi le « zéro doublon inter-jours » est
   garanti par construction, pas par mémoire.
4. **Caveats à garder en tête** (déjà signalés au propriétaire) : les CGU Google
   Places restreignent le stockage durable des données ; ajouter un champ/indicateur
   « Ne pas appeler » (opposition) pour le RGPD. Ne pas bloquer, mais rester correct.

## ⭐ RÈGLES — Documents commerciaux DentWebPro (déclencheurs à mémoriser)

Deux documents ont été créés avec le propriétaire et sont **stockés dans le dépôt**
(donc toujours disponibles, même après recyclage du conteneur) :

- **Facture** → `documents/facture-dentwebpro.html`
- **Contrat / Bon de commande** → `documents/contrat-dentwebpro.html`

Les deux sont des pages HTML `contenteditable` (champs surlignés à remplir → bouton
« Imprimer / Enregistrer en PDF »), au design DentWebPro (corail #f55733), avec les
infos du prestataire **déjà pré-remplies** :
- AK DEV — Hammou-Boutrig Abdelkader
- 4 Avenue du Mal de Lattre de Tassigny, 94000 Créteil
- SIRET 991 470 212 00010 — contact@dentwebpro.site

**Déclencheurs (envoi direct, sans reconstruire) :**
- Quand le propriétaire écrit **« facture »** → envoyer directement
  `documents/facture-dentwebpro.html` via SendUserFile (display:render).
- Quand le propriétaire écrit **« contrat »** → envoyer directement
  `documents/contrat-dentwebpro.html` via SendUserFile (display:render).

Ne rien redemander : les documents existent déjà, il suffit de les renvoyer tels quels.

## Note RGPD — « Ne pas appeler » = « Pas intéressé »

Le champ « Ne pas appeler » est **déjà couvert** par le statut **« Pas intéressé »**
déjà présent dans l'espace. Ne PAS ajouter un champ séparé : marquer un prospect
« Pas intéressé » vaut opposition (ne plus rappeler).
