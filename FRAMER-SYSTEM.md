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
