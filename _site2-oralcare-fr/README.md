# Site 2 — OralCare (export Framer) traduit en français

Travail réalisé de nuit (autonome). **On édite le site original, on ne le reconstruit pas.**

## Source
- Dossier Google Drive de l'utilisateur : `oral 3`
  (ID: `1y3NBXWCQr5br4JyoMgVk4XbASBaVoT9r`) — export Framer statique « Oralcare »,
  ~451 Mo, 37 dossiers / 314 fichiers. Public (Anyone with the link).

## Ce qui a été fait
1. **Énumération** du dossier public via `embeddedfolderview` → `manifest.json`
   (`enumerate.py`).
2. **Téléchargement** complet des 314 fichiers via `download.py` (curl, sans MCP).
3. **Traduction EN→FR** appliquée aux fichiers `.html`, `.mjs` et `.json` :
   - `translations.json` (87 phrases) + `apply.py` : 776 remplacements des phrases
     multi-mots (sûres — pas de collision avec le code).
   - `headings.json` + `split_headings.py` : 41 titres animés (spans par caractère)
     reconstruits en français en préservant la structure du « text reveal ».
   - `short.py` : libellés courts de navigation / formulaire (nœuds de texte `>Mot<`).
4. **Suppression** (règles permanentes) : dossiers `blog/`, `team/`, `legal-pages/`
   + liens de menu Team/Blog. (⚠️ sections « avis clients » et FAQ de l'accueil
   encore présentes — à masquer/retirer, voir TODO.)

## Contenu de ce dossier
- `oralcare-fr-edits.tgz` — tous les fichiers texte (.html/.mjs/.json) TRADUITS.
- `translations.json`, `headings.json`, `*.py` — la « recette » de traduction.
- `manifest.json` — la liste complète des fichiers Drive (id → chemin).

## Reconstruire le site complet (451 Mo)
```bash
# 1) re-télécharger tous les assets depuis le Drive
python3 enumerate.py 1y3NBXWCQr5br4JyoMgVk4XbASBaVoT9r   # -> manifest.json
python3 download.py                                       # -> site/ (451 Mo)
# 2) appliquer les fichiers texte traduits par-dessus
tar xzf oralcare-fr-edits.tgz         # écrase site/*.html /*.mjs /*.json en FR
# 3) servir en HTTP
cd site && python3 -m http.server 8000
```

## Constat technique important
L'export Framer « Maximum Fidelity » déclenche une **erreur d'hydratation React
(#405)** en auto-hébergement — présente AUSSI sur l'original NON modifié.
Le contenu texte s'affiche bien en français, mais certaines animations/révélations
peuvent ne pas se déclencher hors du domaine Framer d'origine. À valider dans un
vrai navigateur / après déploiement.

## TODO restant
- Masquer/retirer les sections « avis patients » et FAQ de la page d'accueil.
- Traduire d'éventuelles chaînes résiduelles (pages services détaillées).
- Déployer (GitHub Pages / autre) pour comparer dans un vrai navigateur.
