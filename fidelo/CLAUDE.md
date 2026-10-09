# CLAUDE.md — Fidelo

Mémoire technique du projet **Fidelo** (carte de fidélité digitale pour commerces
indépendants — cafés, restaurants, boulangeries, salons, commerces). SaaS multi-
tenant en PHP pur, sans framework. Domaine live : **fidelo.site**. Propriétaire :
AK DEV / Hammou-Boutrig Abdelkader (même personne que le studio DentWebPro, voir
le CLAUDE.md racine — projets totalement indépendants, ne jamais mélanger).

## ⭐ Déploiement — RÈGLE CAPITALE

Le site live **n'est PAS auto-déployé depuis git**. Deux canaux distincts :
- **Fichiers applicatifs PHP** (`lib.php`, `api.php`, `index.php`, `carte.php`,
  `console.php`, `push.php`, etc.) : `git push` sur `claude/session-yk6nza` pour
  la traçabilité, **PUIS** toujours fournir un zip à l'utilisateur
  (`SendUserFile`) pour l'upload manuel via cPanel File Manager
  (`public_html/` = racine du site, PAS de sous-dossier `fidelo/` sur le
  serveur — `fidelo.site/index.php` sert directement depuis `public_html/`).
- **Design marketing v3-light** (`accueil.php`, `tarifs.php`, `contact.php`,
  `confidentialite.php`, `mentions-legales.php`, `accueil3.js`,
  `fidelo3-scene.js`, `fidelo3-textures.js`, `vendor/three.module.min.js`…) :
  **JAMAIS commité dans git** — uniquement livré en zip. Raison : évite tout
  risque de régression du rendu 3D en passant par un pipeline de build.
- Après chaque upload, rappeler : hard refresh (Cmd+Shift+R), le Service
  Worker (`sw.js`) est **network-first** (ne sert le cache que si le fetch
  échoue) donc ne devrait pas bloquer les mises à jour — si un bug de cache
  persiste malgré un hard refresh, suspecter d'abord un vrai bug de scroll/
  layout (vécu : `.sheet .card` sans `overflow-y`/`max-height` → contenu
  hors-vue, pas du cache) avant d'accuser le cache.

## ⭐ Secrets — RÈGLE CAPITALE

Le repo `hellboykader-code/Oral` est **PUBLIC**. Ne JAMAIS écrire une vraie
clé/secret dans un fichier commité :
- `ADMIN_KEY` (clé de secours console.php `?k=`) : le code ne définit QUE un
  fallback **vide** (`lib.php`, bloc `if (!defined('ADMIN_KEY'))`). La vraie
  valeur est donnée au propriétaire **en chat uniquement**, à coller à la main
  dans `lib.php` **sur le serveur seulement** (jamais dans un fichier livré ni
  dans git). Un garde-fou anti-exfiltration a bloqué une tentative de l'écrire
  automatiquement dans un zip de livraison — c'est le comportement voulu, ne
  pas contourner.
- Stripe (`sk_live_…`) : jamais dans un fichier. Configuré via l'UI
  (console.php → bouton Stripe → `stripe_set`), stocké dans
  `db.json → settings.stripe`, jamais exposé côté client.
- Google Wallet (service-account JSON) : même principe, via `gw_set`.

## Architecture des fichiers (`fidelo/`)

**Cœur applicatif**
- `lib.php` — tout le modèle de données + helpers partagés (session, DB, auth,
  sécurité). Voir détail des fonctions ci-dessous.
- `api.php` — API JSON du commerçant connecté (session `fidelo_sid`). Toutes
  les actions sont cloisonnées via `$ref = &shop_ref($db, current_shop_id())`
  — jamais un id de commerce venu de la requête (vérifié anti-IDOR).
- `console.php` — panneau du PROPRIÉTAIRE de la plateforme (toutes les
  commerces). Accès par mot de passe (porte normale) ou clé de secours `?k=`
  (dépannage). Session admin séparée (`$_SESSION['admin']`, CSRF `acsrf`).
- `push.php` — Web Push VAPID (PHP pur, sans payload chiffré — le SW relit le
  texte via `?a=peek`). Gratuit, pas de dépendance externe.
- `stripe.php` / `stripe-webhook.php` — Checkout Sessions + vérification
  webhook HMAC-SHA256 (fenêtre anti-rejeu 5 min, dédup par event id).
- `gwallet.php` — JWT Google Wallet (RS256, `openssl_sign`).

**Pages commerçant / client**
- `index.php` — espace commerçant (SPA légère, pas de framework — tout le JS
  est inline dans `<script>`, fonctions globales). Onglets : Accueil, Clients,
  Scanner, Cadeaux, Stats. Sheets modales (`sheet()`/`closeSheet()`) pour
  Réglages, ajout client, broadcast, historique, etc.
- `carte.php` — la carte client publique (vue par QR/NFC). Affiche points,
  récompenses, bannière de bienvenue, messagerie vers le commerçant.
- `inscription.php` — inscription commerçant (public, piège à robots,
  rate-limit, code promo/parrain).
- `rejoindre.php` — un client scanne le QR du commerce → rejoint le programme.
- `affiche.php`, `cartesvierges.php` — supports imprimables (affichette QR,
  lot de cartes vierges pré-imprimées).
- `brand.php` — sert le logo du commerce (upload ou généré).
- `manifest.php`, `sw.js` — PWA (installable, notif push).

**Marketing (v3-light, jamais en git — voir règle déploiement)**
`accueil.php`, `tarifs.php`, `contact.php`, `confidentialite.php`,
`mentions-legales.php`, `legal-ui.php`, `nav.php`, `Nav.dc.html`,
`Footer.dc.html`, `support.js`, `fidelo-scene.js`, `fidelo-textures.js`,
`vendor/` (React/Babel/Three.js self-hébergés).

## Modèle de données

Fichiers JSON sous `fidelo/data/` (jamais accessible directement :
`data/.htaccess` = `Require all denied`).

- `data/db.json` — index : `shops[]` (métadonnées commerce, SANS clients ni
  events — voir sharding), `settings` (promos, Stripe, Google Wallet,
  `announcements[]`), rate-limit (`_rl`).
- `data/shops/<shopId>.json` — shard par commerce : `clients[]`, `events[]`.
  Chargé/fusionné par `db_load($hydrate)` uniquement pour le commerce courant
  (isolation + perf — un gros commerce ne ralentit pas les autres).
- `data/cards.json` — index global token de carte → `{shopId, clientId}`
  (recherche O(1) depuis `carte.php` sans charger tous les shards).
- `data/backups/` — backups quotidiens auto, gz.

**Champ client notable** (dans `clients[]` d'un shard) :
`id, name, tel, points, visits, last, card, push[], created, bday (MM-DD
uniquement, jamais l'année), bdayLastYear, msg{title,body,at} (dernier message
affiché sur la carte), notif{...} (push en attente), blank (carte vierge pas
encore activée), corbeille (soft-delete 30j)`.

**Fonctions clés de `lib.php`** (non exhaustif, voir le fichier) :
`db_load/db_save/db_lock`, `shard_read/shard_write`, `shop_ref` (référence
modifiable), `client_ref`, `client_by_card`, `real_clients` (exclut les cartes
`blank`), `shop_public` (vue exposable au client JS), `auth_login/auth_pin
/auth_logout`, `device_known/device_valid`, `csrf_token/csrf_check`,
`rate_hit/rate_reset`, `client_segment($c,$t)` → `champions|fideles|nouveaux
|endormis` (règles : inactif >14j → endormis ; sinon ≥20pts champions, ≥10pts
fideles, sinon nouveaux — **seule source de vérité**, réutilisée par
`api.php` stats ET `push.php` broadcast ciblé, ne jamais dupliquer la
logique), `shop_broadcast_set/shop_broadcast_log`, `admin_announce`,
`bday_norm/bday_gifts_process`, `plan_of/plan_illimite/essai_actif
/quota_restant`, `promo_check/promo_apply`, `parrain_code/parrain_link`,
`vapid_ensure`, `brand_colors/monogram` (identité visuelle auto par commerce).

**Plans/tarifs** : `PLAN_FREE_MAX = 30` clients en formule Découverte.
`TARIFS = ['mensuel'=>29, 'annuel'=>250, 'avie'=>525, 'domaine'=>50]`. L'à-vie
est un paiement UNIQUE (jamais du MRR récurrent — piège corrigé cette
session, voir Registre).

## Authentification

- **Commerçant** : email+mot de passe (`auth_login`) → session `fidelo_sid`
  + cookie `fidelo_dev` (appareil de confiance, 120j) → ensuite code PIN 4
  chiffres (`auth_pin`) ou WebAuthn (empreinte/visage, `wa_*`) sur cet
  appareil. `fidelo_card` = cookie d'identification CLIENT (pas commerçant),
  posé à chaque vue de carte valide.
- **Propriétaire** : mot de passe (porte normale, `admin_login`) OU clé de
  secours `?k=ADMIN_KEY` (dépannage, voir règle Secrets). TTL session 8h.

## Actions API (référence rapide)

`api.php` (commerçant connecté, CSRF obligatoire sauf `login/pin/me/logout
/wa_aopts/wa_auth`) : `me, login, pin, logout, wa_aopts, wa_auth, wa_ropts,
wa_reg, home, clients, client_get, client_add, client_edit, client_del,
add_point, adjust, redeem, relance, rewards_set, settings_set, inbox,
broadcast_history, announcements, stats, parrain, export, trash,
trash_restore, dedup, devices, device_revoke, pass_set, pin_set, logo_set,
blank_batch, blank_activate, stripe_checkout`.

`console.php` (propriétaire) : `add, reset, status, plan, promos, promo_set,
promo_del, announce, parrainages, backups, backup_now, restore, audit,
demo_clean, signup_set, admin_set, shop_edit, shop_del, shop_clients,
impersonate, gw_set, gw_test, stripe_set, stripe_prices`.

`push.php` (public/mixte) : `peek` (SW client), `keygen`/`send` (admin ou
commerçant relançant SON client), `broadcast` (commerçant, session + CSRF,
segment optionnel).

`carte.php` (public, identifié par cookie/token carte) : `get, gwsave,
msgseen, subscribe, client_msg`, + endpoint GET `?nfc=<shopId>` (tap NFC →
+1 point, cooldown 120s via `rate_hit`).

## Sécurité — audit fait cette session (tout corrigé, voir Registre)

- **CRITIQUE** : `ADMIN_KEY` hardcodée en fallback → corrigée (vide par
  défaut).
- **CRITIQUE** : XSS stocké dans `console.php openShop()` (nom/email commerce
  non échappés) → corrigé (`escH()`).
- **MEDIUM** : XSS stocké dans `carte.php` (libellé de récompense) → corrigé
  (`esc()` JS).
- `client_ip()` ignore `X-Forwarded-For` sauf proxy de confiance défini
  (jamais défini) → anti-spoofing IP confirmé.
- `shop_by_join()` → `hash_equals()`.
- Connu mais non corrigé (communiqué au propriétaire, son choix) : pas de
  rate-limit GET sur lookup promo/join par shortcode, brute-force protection
  IP-only (pas de verrou par compte), WebAuthn sans compteur de signature.

### Scan OWASP ZAP (propriétaire, 9 oct 2026) — 10 alertes passées en revue

- **Corrigé** (`.htaccess`) : en-tête `X-Powered-By` (version PHP) retiré
  globalement (`Header unset`), y compris sur les 4 pages marketing qui
  n'incluent pas `lib.php`. `Cache-Control: no-store, private` ajouté sur
  `carte.php` et `index.php` (données personnelles/business, jamais en cache
  navigateur/proxy partagé) — même esprit que `console.php`, déjà protégée.
- **Déjà voulu, pas un bug** : CSP `unsafe-eval` — scopé **uniquement** aux 5
  pages marketing (`accueil/tarifs/contact/confidentialite/mentions-legales
  .php`, Babel standalone côté navigateur) ; le reste du site (app
  commerçant, carte, console, API) garde une CSP stricte sans `unsafe-eval`.
  `unsafe-inline` (script+style) reste nécessaire tant que l'architecture est
  en un fichier par page (pas de nonce/refactor prévu). Clé `?k=` de
  `console.php` : déjà redirigée instantanément vers une URL propre (ne reste
  jamais dans la barre d'adresse) — ZAP la voit forcément une fois dans sa
  propre requête de scan, c'est inhérent à tout lien magique de secours.
- **Non actionnable / faux positif probable** : SRI (Sub Resource Integrity)
  manquante sur les `<link>` Google Fonts — Google sert un CSS différent par
  User-Agent, un hash SRI casserait le chargement pour une partie des
  visiteurs ; Google déconseille officiellement le SRI sur son Fonts API.
  Aucun autre script/style tiers chargé (vérifié : seul Google Fonts CSS).
- **Informationnel, aucune action** : « Authentication Request Identified »,
  « Session Management Response Identified » (ZAP signale juste qu'il a vu un
  formulaire de connexion / des cookies de session — normal).
- **À vérifier avec le détail exact de l'alerte** (pas encore vu) :
  « User Controllable HTML Element Attribute (Potential XSS) ». Revue de code
  faite sur tous les `$_GET`/`$_POST` reflétés en HTML (`promo`, `p`, `c`,
  `s`, `j`, `ids`…) : tous filtrés par allow-list regex et/ou échappés via
  `e()` avant affichage — rien trouvé de concrètement exploitable, mais le
  scan passif de ZAP marque toute réflexion même échappée. Demander à
  l'export ZAP (Rapport → HTML/XML) l'URL + paramètre exact pour confirmer.

## Conventions de travail (établies, à respecter)

- **Toujours tester en isolation** avant de livrer : copier `fidelo/` dans
  `scratchpad/`, `data/` VIDE (`echo '{"shops":[]}' > data/db.json`), `php -S
  127.0.0.1:<port>`, créer un commerce de test via `inscription.php`, injecter
  des données synthétiques si besoin (jamais modifier de vraies données),
  vérifier par `curl`/Playwright, **puis seulement** nettoyer et livrer.
- `php -l` sur chaque fichier modifié avant tout.
- Une modification = un commit descriptif + push, PUIS zip des fichiers
  changés envoyé par `SendUserFile` (jamais l'inverse, jamais oublier l'un
  des deux).
- Toujours rappeler l'étape manuelle d'upload cPanel + hard refresh.
- Français partout (UI, commentaires, messages commit) — le propriétaire
  parle arabe algérien, communiquer avec lui dans sa langue en chat.
- Appliquer une correction à TOUTES les occurrences (ex. un bouton dupliqué
  entre `index.php` et une sheet) — leçon du studio DentWebPro (voir CLAUDE.md
  racine section rétrospective), vaut aussi ici.

## Registre des fonctionnalités livrées (session claude/session-yk6nza)

Dans l'ordre, chaque entrée = testée en isolation (curl + Playwright) avant
commit+push+zip :

1. **Quota/plan** : champ `plan` + sélecteur Formule dans console.php (le bug
   "quota ne se lève jamais" venait de l'absence de ce champ).
2. **Stripe** : Checkout (mensuel/annuel=subscription, à vie=payment) +
   webhook sécurisé. Testé signature/rejeu/dédup/3 types d'event.
3. **Design v3-light** : refonte marketing complète (palette crème/terracotta
   /vert, Young Serif/Figtree/IBM Plex Mono, métaphore de culture/croissance)
   — fallback Canvas-2D instantané + film Three.js en progressive enhancement
   (résout définitivement le bug "écran noir sur iPhone"). Livré en zip
   uniquement (jamais git).
4. **Audit sécurité complet** (2 sub-agents en parallèle) → voir section
   Sécurité ci-dessus.
5. **NFC tap-to-point + bannière de bienvenue + avis Google + messagerie
   client→commerçant** : `carte.php?nfc=<shopId>` (carte NFC statique, SANS
   protection cryptographique — **choix explicite et éclairé du
   propriétaire**, seul garde-fou = cooldown 120s anti-double-scan réutilisé).
   Bannière (`?bienvenue=1` nouveau client / `?pt=1` point NFC) avec lien
   avis Google configurable (`shop.googleReview`, Réglages commerçant) et
   formulaire message→commerçant (`client_msg` → `inbox`).
6. **Fix scroll Réglages** : `.sheet .card` sans `max-height`/`overflow-y` →
   contenu (ex. nouveau champ avis Google) hors-vue selon position de scroll
   héritée. Corrigé : `max-height:88vh;overflow-y:auto` + reset
   `scrollTop=0` à chaque ouverture de `sheet()`. **Symptôme pour diagnostic
   futur** : "un champ que je viens d'ajouter à une sheet n'apparaît pas" →
   suspecter CE bug avant le cache.
7. **Broadcast ciblé par segment** : `push.php?a=broadcast` accepte
   `segment` (all/champions/fideles/nouveaux/endormis), filtre clients via
   `client_segment()`. UI : `<select>` dans la sheet "Message groupé"
   (renommée depuis "Message à tous mes clients").
8. **Historique broadcast** : `shop_broadcast_log()` (30 derniers/commerce),
   action `broadcast_history`, écran dédié accessible depuis la sheet
   broadcast.
9. **Cadeau anniversaire auto** : `bday` (MM-DD seulement, jamais l'année) sur
   le client, `bday_gifts_process()` appelé une fois/jour (même garde que
   `today_reset`, dans l'action `home`) → +1 point + message, contrôlable par
   commerce (`ref['birthdayGift']`, Réglages, activé par défaut).
10. **Dashboard revenus console** (`shop_mrr()`) : MRR réel par formule
    (mensuel=plein tarif, annuel=tarif/12, à vie=0 car hors MRR + cumulé
    séparément). Panneau "Répartition par formule". Avant : comptait tout
    commerce actif au même tarif flat (faux dès qu'un commerce annuel/à vie
    passait actif) — corrigé.
11. **Alerte churn** : `shop_last_active()` = max(lastLogin, max(devices[*]
    .at)) — ne pas se fier à `lastLogin` seul (un commerçant qui n'utilise
    que le code PIN ne le met jamais à jour). Panneau "Commerces inactifs"
    (14j+, hors commerces déjà annulés).
12. **Annonce plateforme → tous les commerçants** : `admin_announce()`
    (global, `settings.announcements[]`, 20 dernières), action `announcements`
    côté commerçant (marque lu via `ref['annReadAt']`). Bouton enveloppe
    header (renommé "Annonces et messages") affiche annonces + messages
    clients dans un seul panneau à deux sections.

## ⭐⭐ Google Wallet — changement de compte + bug 403 résolu (saga complète, 4-6 oct 2026)

**État final : 100% fonctionnel, vérifié de bout en bout contre l'API Google
réelle.** `gw_test` → `{"ok":true,"name":"Fidelo"}`. Issuer : **Fidelo**
(`3388000000023209432`). Compte de service : `fidelo@fidelo-508713.iam
.gserviceaccount.com` (rôle **Développeur** dans pay.google.com/business/console,
à côté de `kaderhb33@gmail.com` en **Administrateur**). `settings.gw.live = true`
dans Fidelo (checké dans console.php → panneau Google Wallet).

**Demande initiale** : le propriétaire a changé de compte Google → "ajoute un
nouveau compte, supprime l'ancien". Instruction explicite : **"انت رح تفعل كل
شيء"** (fais tout toi-même) → tout ce qui suit a été fait en autonomie (curl
direct sur console.php en prod, avec confirmation régulière au propriétaire).

**Étapes traversées (dans l'ordre, pour ne pas revivre les mêmes fausses
pistes si un futur compte Wallet doit être reconfiguré)** :
1. Nouveau compte de service créé par le propriétaire sur Google Cloud Console
   (PAS pay.google.com — piège : il avait d'abord envoyé le JSON d'exemple
   officiel Google "Baconrista", reconnu immédiatement comme non-réel).
2. **Bloqué par l'Org Policy** `iam.disableServiceAccountKeyCreation` (héritée,
   bouton grisé) → la vraie cause était l'absence du rôle IAM **"Administration
   des règles de l'organisation"** (`orgpolicy.policyAdmin`) au niveau
   **Organisation** (pas Projet) sur le compte du propriétaire. Ajouté via
   console.cloud.google.com/cloud-resource-manager → clic sur l'organisation →
   IAM. Résolu.
3. Clé JSON générée, transmise à Fidelo via `gw_set` (curl direct, clé passée
   en `--data-urlencode sa@<fichier>` pour ne jamais la retaper en clair).
   `gw_set` → `ready:true`. Mais `gw_test` → `{"error":"access"}` pendant
   **~2 jours**, malgré un compte Développeur correctement ajouté (vérifié
   par capture : bon email, bon rôle, dès le début).
4. Découvert ensuite : le compte Wallet Business Console était en **"Mode
   démo"**, checklist 3 étapes ("Obtenir l'accès en publication") :
   Créer une classe / Finaliser la fiche d'établissement / Demander l'accès
   en publication. Complétées une à une (classe jetable `Fidelo_test_class`,
   type "Carte de fidélité", champs requis remplis avec des valeurs simples ;
   cas d'usage décrit en détail dans la demande d'accès en publication).
   Email de confirmation Google reçu : *"Fidelo is approved to access Google
   Wallet API"*. **`gw_test` toujours `error:access` après.**
5. **⭐⭐ VRAIE cause (celle qui comptait) — totalement différente de tout ce
   qui précède.** Diagnostic en contournant Fidelo : script PHP autonome qui
   rejoue `gw_token()` + `GET /issuer/<id>` avec la même clé, pour lire le
   **corps d'erreur complet** de Google (que `console.php` simplifie en un
   hint générique "ajoutez l'utilisateur Developer" pour TOUT code 403, quelle
   que soit la vraie raison). Le vrai message Google :
   > *"Google Wallet API has not been used in project 806302982027 before or
   > it is disabled. Enable it by visiting
   > console.developers.google.com/apis/api/walletobjects.googleapis.com/
   > overview?project=806302982027…"*

   Autrement dit : l'**API Google Wallet n'était simplement pas activée**
   (`Enable`) sur le projet Google Cloud — une étape GCP basique et
   **totalement indépendante** du Mode démo / Developer user / publish access
   (sur lesquels tout le monde, propriétaire et moi, avait passé 2 jours).
   Une fois l'API cliquée sur "Enable" (bouton simple dans Google Cloud
   Console → API et services), tout a marché **instantanément**.
6. **Test de bout en bout fait directement contre l'API Google réelle**
   (bypass complet de Fidelo, scripts PHP jetables avec la même clé de
   service) : jeton OAuth ✅ → `POST /loyaltyClass` (avec un vrai logo
   public `https://fidelo.site/apple-touch-icon.png`) ✅ `reviewStatus:
   approved` instantané ✅ → `POST /loyaltyObject` ✅ `state: active` ✅ →
   génération du JWT "Add to Google Wallet" ✅. Objet de test ensuite repassé
   en `state:inactive` (nettoyage, laisse une classe de test inoffensive
   `fidelo_TESTSHOP3` sous l'issuer réel — jamais vue par un vrai client).

**⭐ Leçon n°1 (diagnostic 403 Wallet futur)** : un code 403 de
`walletobjects.googleapis.com` peut vouloir dire AU MOINS 3 choses différentes
(API non activée sur le projet GCP / utilisateur Developer manquant dans
Wallet Business Console / restriction Mode démo), et `console.php` ne
distingue QUE le 2ᵉ cas dans son message d'erreur. **Ne jamais se fier au hint
simplifié** — en cas de blocage > quelques minutes, rejouer l'appel en brut
(JWT + curl direct avec la clé de service) pour lire le `message`/`reason`
exact renvoyé par Google (`SERVICE_DISABLED` vs autre) AVANT de creuser une
piste.

**⭐ Leçon n°2 (test local Wallet impossible)** : Google doit pouvoir
télécharger le logo/hero depuis une URL **publique** pour créer une
`loyaltyClass` — un test en isolation sur `127.0.0.1` échoue TOUJOURS
(`"Image cannot be loaded"`), ce n'est PAS un bug de code. Pour tester la
création de classe/objet Wallet, soit utiliser une image publique réelle
(ex. `https://fidelo.site/apple-touch-icon.png`) dans un script de diagnostic
à part, soit tester directement en production avec prudence (jamais sur de
vraies données client — utiliser un `classId`/`objectId` de test distinct,
et le repasser en `inactive` après).

**Si un jour un nouveau compte Google Wallet doit être reconfiguré**, suivre
dans l'ordre : (1) compte de service + clé JSON (Google Cloud Console, pas
pay.google.com) → `gw_set`, (2) ajouter l'email du compte de service comme
Développeur dans pay.google.com/business/console → Users, (3) compléter la
checklist Mode démo (3 étapes) si elle apparaît, (4) **vérifier que l'API
Google Wallet est "Enabled"** sur le projet GCP
(`console.developers.google.com/apis/api/walletobjects.googleapis.com/overview?project=<id>`)
— **étape la plus facile à oublier et la vraie cause la plus probable d'un
403 persistant**, (5) `gw_test`.

## Pistes explorées, non implémentées (discussion en cours)

Comparatif concurrents (Zerosix, Loyeo, Snapss, BonusQR, LittleBill) a
identifié des idées pas encore construites, par ordre de facilité :
cashback/cagnotte en euros, paliers VIP nommés visibles par le client (vs nos
segments internes), WhatsApp Business API pour le marketing, SMS, scan de
ticket par IA, intégration caisse/POS, facture dématérialisée (AGEC). Aucune
décision prise — à reprendre si le propriétaire relance le sujet.
