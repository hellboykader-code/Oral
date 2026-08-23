# FRAMER — Comment le système fonctionne (référence de travail)

But : comprendre Framer **en entier** (design + code export) pour éditer un site
exporté en toute maîtrise, au lieu de deviner. Basé sur la doc officielle Framer,
la communauté, et l'analyse directe des exports réels (RedDent, Zenta…).

---

## 1. Ce qu'est Framer
Outil de design **no-code** dont le rendu est un **vrai site React**. On dessine sur
un canvas (composants, variantes, breakpoints, animations Framer Motion, CMS,
formulaires). À la publication/export, Framer **compile en React + JS** (bundler
« rolldown ») : le site publié EST une application React, pas une image.

Deux façons d'obtenir du code :
- **Publish** (hébergement Framer) : SSR + hydratation React.
- **Export / CodeFreeExport** (ce qu'on reçoit) : site **statique** = HTML pré-rendu
  (SSR) + chunks `.mjs` + assets. Chaque page = un dossier avec `index.html`.

---

## 2. Anatomie d'un export statique
```
index.html                      page d'accueil (SSR complet + <link modulepreload>)
about/index.html                page /about (deep-link autonome)
service/index.html              page /service
service/<slug>/index.html       page détail CMS (1 par item de collection)
doctors/…, contact/…            idem
assets/framer/sites/<siteId>/   TOUS les chunks .mjs + images/fonts
  rolldown-runtime.*.mjs        runtime du bundler (charge les modules)
  react.*.mjs                   React 18
  motion.*.mjs                  Framer Motion (animations)
  framer.*.mjs                  cœur Framer : ROUTEUR, contexte, primitives
  script_main.*.mjs             composants partagés (nav, header, footer, menu)
  <hash>.*.mjs                  1 chunk par page/composant (contient le CONTENU)
  Phosphor.*.mjs, Video.*.mjs   bibliothèques d'icônes / lecteurs
  fontshare/google-*.mjs        chargeurs de polices
404/index.html                  page 404
sitemap.xml, robots.txt, search-index.json, searchIndex-*.json
```

### Modèle SSR + hydratation (CRUCIAL)
1. Le serveur envoie le **HTML pré-rendu** (SSR) → l'utilisateur voit le contenu tout de suite.
2. Les `.mjs` se chargent (ordre : rolldown-runtime → react → motion → framer →
   shared-lib → chunks de page).
3. React **hydrate** : il RE-RENDER le DOM à partir des **données dans les `.mjs`**.
   → **Tout texte/contenu vient des `.mjs`.** Traduire seulement le HTML SSR ne suffit
   pas : à l'hydratation, l'anglais des `.mjs` revient. **Il FAUT traduire les `.mjs`.**

---

## 3. Le routeur Framer (ce qui a causé nos 404)
- Routeur **client** dans `framer.*.mjs`. Routes déclarées `path:`/``, `/about`,
  `/service`, `/doctors`, `/contact`, `/blog`… (relatives à la base du site).
- **Normalisation du chemin** : `Ei(p) = p.replace(/^\/|\/$/gu,'')` → enlève le slash
  de début ET de fin, mais **PAS `index.html`**.
  - `/service/`      → `service`            → **matche** ✅
  - `/service`       → `service`            → matche ✅ (mais GitHub Pages 301 → `/service/`)
  - `/service/index.html` → `service/index.html` → **ne matche pas → 404** ❌
- **Écoute** : `popstate` **ET** `window.navigation` (Navigation API, Chrome). Mais le
  handler `popstate` **valide `event.state`** (`if(!Or(state))return`) → un
  `pushState({},…)+popstate` avec état vide est **ignoré**.
- **Interception des clics** : par **composant** (chaque `<Link>` a son `onClick`
  qui appelle `a.navigate()`), **PAS** une délégation globale sur `document`.
  → Framer n'intercepte que SES propres liens. Un `<a>` que j'injecte n'est pas intercepté.
- **Deep-links** : comme chaque page a son `index.html`, une **navigation complète
  vers `/service/`** (slash final, sans `index.html`) charge le fichier ET le routeur
  matche la route → fonctionne.
- **⭐⭐ PIÈGE MAJEUR (découvert en debug) : Framer réécrit le `href` de TOUS les
  `<a>` internes du document en `javascript:void(0)`** (il gère la nav en JS via sa
  fonction `yu` qui crée un `<a>` temporaire et le `.click()`). Cela s'applique AUSSI
  aux `<a>` qu'on injecte soi-même → **nos liens perdent leur href et deviennent morts**
  (clic = rien). C'est LA cause de « les boutons ne marchent pas ».
  → **Solution fiable pour une nav custom : ne PAS utiliser `<a>`. Utiliser des
  `<div role="link">` (Framer ne touche pas les non-`<a>`) avec un handler de clic
  qui fait `window.location.assign(BASE+'/route/')`** (navigation complète, slash final).
  Ne pas se fier non plus à `pushState`+`popstate` (état validé par `Or(state)`) ni à
  l'interception (par composant, pas globale : aucun listener `click` global sur `document`).

---

## 4. Composants, variantes, breakpoints
- **`data-framer-name="…"`** = **nom de calque interne** (ex. `Header`, `Service Card`,
  `Hero`, `Menu Link`). C'est un IDENTIFIANT, **PAS du texte affiché**. Sert aux
  sélecteurs/animations. **Ne jamais le traduire** (casse tout). **Sert à masquer**
  une section : `[data-framer-name="FAQ"]{display:none!important}` (survit à React).
- **Texte affiché** dans les `.mjs` : via `children:`…`` ou une **prop de texte**
  (ex. `jCPJ6vY2r:`Home``) — ces props ont des id aléatoires par composant. C'est CE
  qu'il faut traduire (pas les `data-framer-name`).
- **Variantes** : états d'un composant (default/hover, primary/secondary…). Dans le
  `.mjs` : `variant:`…``, `cycleOrder`, `variantClassNames`.
- **Breakpoints** (3 par défaut) : **Desktop ≥ 1360px**, **Tablet 810–1359.98px**,
  **Phone < 810px**. Le Desktop est la source ; les autres héritent et peuvent
  surcharger (layout, variante…). CSS via `@media` dans les chunks.

---

## 5. Design system : tokens & typographie
- **Couleurs** = **tokens** = variables CSS `--token-<uuid>` avec repli rgb :
  `var(--token-303ba8c9-…, rgb(209, 252, 113))`. Changer un token change partout.
- **Typographie** = **presets** : attribut `data-styles-preset="<id>"` +
  classe `framer-styles-preset-<id>`. Un même preset = un style de texte réutilisé
  (titre H1, corps…).
- **SplitText** (titres animés lettre/mot) : le titre est **découpé** en plusieurs
  `<span>` (par mot) et parfois **plusieurs `<h1>`** (ex. RedDent : `children:`Digitally-driven ``
  puis un 2e `<h1>` `[`oral health and `,br,`hygiene solution`]`). → Traduire une
  phrase entière échoue ; traduire **fragment par fragment** dans le `.mjs`, ou
  remplacer le contenu de l'élément après hydratation (filet JS `clinic-fix`).

---

## 6. CMS (collections)
- Une **collection** = base de données (ex. Soins, Praticiens, Articles). Chaque
  **item** a `id`, `slug`, et `fieldData` (les champs). Les pages détail sont
  générées **1 par item** : `service/<slug>/index.html`.
- Dans l'URL, seul le **`:slug`** est dynamique ; le reste du chemin est statique.
- Le contenu des items est **rendu côté client** depuis les `.mjs`/données → même
  logique : traduire les `.mjs`, pas seulement le SSR.
- `searchIndex-*.json` = index de recherche Framer (souvent tout l'anglais du
  template, y compris pages supprimées). **Le vider (`{}`)** — non référencé visuellement.

---

## 7. Formulaires
- Champs rendus côté client (`placeholder:`…``, options `{title:`…`,type:`option`,value:`…`}`).
- **N'envoient rien** par défaut (pas de backend). Pour activer : Formspree/FormSubmit
  branché au déploiement.
- Traduire `title:` (affiché), garder `value:` (clé). Traduire les `placeholder:`.

---

## 8. Code Overrides & Code Components (le vrai « code » de Framer)
- **Code Override** = fonction (HOC React) appliquée à un calque, active seulement en
  preview/publié. Reçoit l'élément en prop, renvoie une version modifiée. Doit être
  **React 18** + `forwardRef` pour préserver liens/effets.
- **Code Component** = composant React écrit dans l'éditeur Framer, avec
  **`addPropertyControls(Comp, { prop: { type: ControlType.String|Color|Boolean|Enum… } })`**
  → génère des contrôles dans le panneau de droite.
- **`RenderTarget.current()`** : savoir si on est sur le canvas / preview / export.
- **State partagé** : hooks type `useStore` (store partagé entre composants).
- **Framer Motion** : `motion.div`, `variants`, `animate`, `whileInView`,
  `AnimatePresence` (transitions de page/scroll).

---

## 9. PLAYBOOK d'édition d'un export (ce qui marche, testé)
1. **Traduire = SSR HTML _et_ `.mjs`** (source de vérité du rendu client). Vérifier
   par `grep` dans les `.mjs`, pas seulement le rendu (le CDN Framer est bloqué dans
   notre env → le rendu Playwright échoue avec React #405, faux négatifs).
2. **Nom de calque ≠ texte** : traduire `children:`/props texte, jamais `data-framer-name`.
3. **Masquer une section** (FAQ, avis, blog, offres) : CSS `!important` sur
   `[data-framer-name*="…"]` injecté par `clinic-fix.js` (survit à l'hydratation).
   Le `display:none` en JS seul est annulé par React.
4. **Filet runtime `clinic-fix.js`** injecté dans chaque `<head>` : re-traduit /
   corrige après hydratation, avec `MutationObserver` (React reconstruit → on ré-applique).
   Remplacer le **nœud texte** (pas `textContent=`) pour préserver les `<span>` stylés.
5. **Nav / liens vers d'autres pages** : simples `<a href=".../route/">` (slash final,
   **sans `index.html`**) → navigation complète que le routeur Framer résout (règle §3).
   Ne pas compter sur pushState (état validé) ni sur l'interception (par composant).
6. **Cache** : les chunks `.mjs` gardent le **même nom** quand on change leur contenu →
   le navigateur/CDN sert l'ancienne version. Pour `clinic-fix.js`, **versionner l'URL**
   (`clinic-fix.js?v=N`) dans le HTML. Le contenu des `.mjs` se met à jour côté serveur
   (vérifiable par `curl`), mais le navigateur peut garder ~10 min (max-age=600) →
   **tester en navigation privée / hard-refresh**.
7. **Déploiement** : GitHub Pages, base `/export-<repo>/`. Réécrire les chemins absolus
   `"/assets/"` → `"/export-<repo>/assets/"` dans HTML + `.mjs`. Ajouter **`.nojekyll`**.
   Le build « pages build and deployment » doit tourner (activer Pages : Settings → Pages
   → main / root).
8. **Sources d'anglais cachées** : `.mjs` (contenu + bios + alt), `searchIndex-*.json`,
   `search-index.json`, `seo-report/`, fragments SplitText, `placeholder=`, `title:` d'options.

---

## 10. Devenir « designer Framer »
Pour reproduire/concevoir comme Framer (sans l'app) :
- Respecter le **modèle tokens (couleurs) + presets (typo)** : cohérence globale.
- **3 breakpoints** (Desktop/Tablet/Phone), Desktop = source, surcharges descendantes.
- **Composants réutilisables** (nav, footer, cartes) + **variantes** (états).
- **Framer Motion** pour les animations (fadeUp, stagger, whileInView, SplitText).
- **CMS** pour tout contenu répété (soins, équipe) : 1 gabarit + N items.
- Hero plein écran, révélations au scroll, layout `flex/grid`, unités relatives.
- (Notre stack React « Éclat » applique déjà ces principes en code classique.)

Sources : framer.com/developers (overrides, cms), framer.com/dictionary
(breakpoint-variant, cms-collection), framer.com/academy (CMS), unframer.co
(export React), frontendpatterns.dev (client-side routing) + analyse des exports réels.

---

# ANNEXE — Étude approfondie (recherche dédiée, ne pas re-deviner)

## A. Routeur — mécanique EXACTE (analyse du code réel `framer.*.mjs` + `script_main`)
- **Table de routes** dans `script_main.*.mjs` : objets
  `{<routeId>:{elements:{…}, page:()=>import('…chunk.mjs'), path:`/route`}}`.
  Paths trouvés : `/`, `/about`, `/service`, `/doctors`, `/contact`, `/blog`, `/404`.
  → **Les paths sont RELATIFS À LA RACINE (root-relative), SANS le sous-dossier de déploiement.**
- **Route initiale au chargement** : dérivée de l'URL, PAS du SSR :
  `oe(routes, decodeURIComponent(location.pathname), …)`. Le SSR n'impose pas la route ;
  l'hydratation **re-matche `location.pathname`**.
- **Normalisation** : `t.pathname.endsWith('/') ? pathname.slice(0,-1) : pathname`
  (enlève UN slash final). `Ei(p)=p.replace(/^\/|\/$/,'').split('/').length` = **compte les
  segments** (ce n'est PAS un normaliseur de chemin — erreur d'analyse précédente corrigée).
- **⭐⭐ BUG « tous les liens mènent à l'accueil » (déploiement en sous-dossier)** :
  le site est servi sous `/export-<repo>/` mais les routes sont `/service` etc.
  → `location.pathname = /export-<repo>/service` ne matche AUCUNE route → l'hydratation
  bascule sur l'accueil (route `/`). Confirmé par la doc Framer : « the URL paths must
  match between your domain and the Framer site » ; « Framer's client-side routing expects
  consistent paths ». **Framer est conçu pour être servi à la RACINE d'un domaine.**
  → **Correctif : préfixer les paths de la table de routes avec le sous-dossier**
  (`path:`/service`` → `path:`/export-<repo>/service``, home `path:`/`` →
  `path:`/export-<repo>``). Alors `location.pathname` matche. (Ou déployer à la racine.)
- **Navigation d'un lien Framer** : chaque `<Link>` a un `onClick` par composant (PAS de
  listener `click` global sur `document`) qui appelle `a.navigate?.()` (SPA) ou
  `yu(href)` — et `yu` crée un `<a>` temporaire et le `.click()` = **navigation complète**.
- **⭐ Framer réécrit le `href` de TOUS les `<a>` internes en `javascript:void(0)`**
  (gestion JS). Touche aussi nos `<a>` injectés → liens morts. **Nav custom = `<div role="link">`
  (Framer ne touche pas les non-`<a>`) + handler `window.location.assign(url)`.**
- Le routeur écoute `popstate` MAIS **valide `event.state`** (`if(!Or(state))return`) →
  un `pushState({})`+popstate d'état vide est ignoré. Écoute aussi la **Navigation API**
  (`window.navigation`), mais l'interception dépend de la config.

## B. Rendu & hydratation (analyse + reverse-engineering communautaire)
- SSR HTML complet + hydratation React 18 depuis les `.mjs`. Un site Framer ≈ **800 Ko+ JS**
  (React + Motion + Framer + chunks). Ordre de chargement (modulepreload) :
  `rolldown-runtime → react → motion → framer → shared-lib → chunks de page`.
- **Convertir en HTML statique = retirer les scripts d'hydratation** (le `<script
  type="module" data-framer-bundle="main">`, les `modulepreload`, tout `hydrateRoot`).
  MAIS alors : accordéons/onglets/menus morts, **et surtout les éléments d'apparition
  restent invisibles** (voir C). Donc conversion statique = uniquement si on force
  aussi `opacity:1` et qu'on accepte de perdre les animations.

## C. Animations d'apparition (appear) — pourquoi « écran par écran » sans JS échoue
- Attribut racine `data-framer-appear-animation="no-preference"`.
- Les éléments animés ont un **style inline** `opacity:0.001; transform:translateY(10px)…`
  dans le SSR. C'est le JS (Framer Motion) qui les passe à `opacity:1` à l'entrée dans
  le viewport. **Sans JS → ils restent quasi invisibles (0.001).**
- `will-change:transform` / `will-change:var(--framer-will-change-override,transform)`.
- Titres animés = **SplitText** (découpe en `<span>` par mot ; parfois plusieurs `<h1>`).

## D. Design system (confirmé)
- **Couleurs = tokens** `--token-<uuid>` avec repli `rgb(...)`. Dark mode : color styles
  ont valeur claire+sombre ; bascule via `[data-framer-theme="dark"|"light"]`
  (`document.body.dataset.framerTheme`).
- **Typographie = presets** : `data-styles-preset="<id>"` + classe `framer-styles-preset-<id>`.
  Polices via `@font-face` (chunks `fontshare-*` / `google-*`) — Bricolage Grotesque, Inter…
- **Breakpoints** desktop-first : Desktop (≥1200/1360) source → Tablet → Phone héritent,
  surcharge possible (layout, **variante**, style). Media queries dans les chunks.

## E. Composants & code (API complète — réf. Framer Developers)
- **Code Override** = HOC React (React 18 + `forwardRef`), actif en preview/publié.
- **Code Component** = composant React + `addPropertyControls(Comp,{prop:{type:ControlType.…}})`.
  **ControlType** : String (placeholder/maxLength/obscured/displayTextArea), Number
  (min/max/step/unit/displayStepper), Boolean (enabledTitle/disabledTitle), Color, Enum
  (options/optionTitles/displaySegmentedControl), ComponentInstance (children), File
  (allowedFileTypes), ResponsiveImage (src/srcSet/alt), Font, Padding, BorderRadius, Border,
  BoxShadow, Gap, Cursor, Array (control/maxCount), Object (controls/optional), Date
  (displayTime), Transition, Link, TrackingId. Options communes : `defaultValue`,
  `hidden(props)`, `description`. (SegmentedEnum/Image/FusedNumber = dépréciés.)
- `RenderTarget.current()` = canvas | preview | export. `useStore` (store partagé).
  `data-framer-component-type="RichTextContainer"`, `data-framer-name` = calque interne.

## F. CMS (réf. Framer Developers)
- Collection → items `{id, slug, draft, fieldData{fieldId:value}}`, fields `{id,name,type}`.
  15 types : boolean,color,number,string,formattedText,image,file,link,date,enum,
  collectionReference,multiCollectionReference,array,unsupported. API :
  `framer.getCollections()/getActiveCollection()`, `collection.getFields()/getItems()`.
  Pages CMS : 1 par item, URL `.../:slug`. Contenu rendu côté client → **traduire les `.mjs`**.

## G. Formulaires
- `<input>` dans un `<form>` → inclus à la soumission. Destinations Framer : e-mail,
  Google Sheet, webhook. Export statique : **aucun envoi** → brancher Formspree/FormSubmit.
- Options `<select>` : `{title:`…`(affiché), type:`option`, value:`…`(clé)}`. Placeholders visibles.

## H. Localisation (i18n) native Framer
- Locales (langue+région), Localization Groups (pages/CMS), Localization Sources (string→traduction).
  Routing `/en/about`, `/fr/about`. Traduction manuelle ou IA. **Nos exports = mono-locale
  (anglais)** → on traduit au niveau code (SSR + `.mjs`), équivalent d'une localisation manuelle.

## I. SEO / assets (confirmé dans l'export)
- `<link rel="canonical">`, `<meta name="description|robots|viewport">`, `og:title/description/
  image/url/type`. `sitemap.xml`, `robots.txt`, `search-index.json`, `searchIndex-*.json`.
- Images : `srcset` responsive + `loading="lazy"` ; URLs CDN `framerusercontent.com` → à
  réécrire en local `/export-<repo>/assets/framer/images/…` (le CDN est bloqué en preview local).

## ⭐ K. Résolution de la route INITIALE — `data-framer-hydrate-v2` (DÉCOUVERTE CLÉ)

**Au chargement d'une page, Framer NE devine PAS la route depuis l'URL en priorité.** Le
`script_main` lit, dans l'ordre :
1. `document.querySelector('#main').dataset.framerHydrateV2` — un JSON
   `{routeId, localeId, breakpoints}` **injecté dans le SSR**. Si présent → `routeId` est pris
   **directement**, AUCUN matching d'URL. La page s'hydrate comme SA route, quel que soit le
   chemin de déploiement. (`s=JSON.parse(t.dataset.framerHydrateV2); r=s.routeId; …`)
2. sinon, l'en-tête `Server-Timing: route;desc=…` (absent sur GitHub Pages).
3. sinon **seulement** (`if(!r||!i)`), le fallback : `me(routes, decodeURIComponent(location.pathname))`
   → matche l'URL contre la table de routes.

**Conséquence — LA cause racine du bug « toutes les sous-pages retombent sur l'accueil » :**
certains exports n'ont le dataset `framer-hydrate-v2` QUE sur `index.html` ; les sous-pages
(`about/`, `service/`…) ont un `<div id="main">` **nu**. Elles tombent donc dans le fallback (3),
qui compare `location.pathname = /export-<repo>/about` à une route `/about` → **aucun match →
rendu de l'accueil par-dessus le SSR** (« la page apparaît 1 s puis disparaît »).
D'autres exports (kader1, kadaaaaa-ms1twfho) ont le dataset sur **toutes** les pages → immunisés.

**⭐ CORRECTIF DÉFINITIF (robuste, indépendant du chemin de déploiement) :** injecter le dataset
sur CHAQUE sous-page. Pour la page du dossier `X` :
`<div id="main" data-framer-hydrate-v2="{&quot;routeId&quot;:&quot;<ID>&quot;,&quot;localeId&quot;:&quot;default&quot;,&quot;breakpoints&quot;:[…]}">`.
- `<ID>` = le routeId de la route dont le `path` finit par `/X` (extrait de la table de routes).
- `breakpoints` = **copier** le tableau du dataset de `index.html` (identique pour tout le site).
- **Extraction path→routeId** : la table est `…<id>:{elements:{…},page:P(()=>import(…)),path:`/X`}`.
  Les objets ont des accolades imbriquées (`{…trigger}`) → un regex simple casse : partir de
  chaque `path:` et **remonter en comptant les accolades** jusqu'à l'accolade ouvrante de l'objet,
  l'`<id>` est le token juste avant `:{`.
- **Vérifier** : le routeId injecté doit égaler celui de la table pour ce path ; et le dataset de
  `index.html` doit pointer la route d'accueil (`elements` contient `hero-banner`), pas `/blogs`
  (bug réel vu sur med12 : dataset d'accueil corrompu en `nQCqzkvcq`=/blogs → corriger).

Le préfixe des `path:` (point 1 ci-dessous) reste utile **en plus** : il garde des URLs correctes
lors des navigations client-side (sinon `pushState('/about')` perd le préfixe `/export-<repo>`).

## J. Récapitulatif des CORRECTIFS fiables (à appliquer)
0. **⭐ Sous-pages qui retombent sur l'accueil (sous-dossier)** : le VRAI correctif est
   d'**injecter `data-framer-hydrate-v2`** sur chaque sous-page (voir section K). Le préfixe des
   routes (1) est complémentaire, pas suffisant seul si le matcher ne normalise pas le `/` final.
1. **Sous-dossier** : préfixer les `path:` de la table de routes par `/export-<repo>` (garde les
   URLs correctes en nav client-side). Home `/` → `/export-<repo>`.
2. **Nav custom** : `<div role="link">` + `window.location.assign('/export-<repo>/route/')`
   (jamais `<a>` : href réécrit en void(0)). Une fois (1) fait, `/route/` matche.
3. **Traduction** : SSR + `.mjs` + filet runtime `clinic-fix` (MutationObserver, remplacement
   du nœud texte). Vider `searchIndex-*.json`.
4. **Masquer sections** (avis/FAQ/blog/offres) : CSS `!important` sur `[data-framer-name*="…"]`.
5. **Cache** : `clinic-fix.js?v=N` (les `.mjs` gardent leur nom → cache navigateur/CDN ~10 min).
