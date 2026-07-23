#!/usr/bin/env bash
# Reconstruit le site 2 (OralCare FR) : télécharge les assets depuis le Drive
# public, applique les fichiers texte traduits, retire les pages exclues,
# puis réécrit les chemins absolus pour l'hébergement sous /Oral/comparaison/.
set -e
FID="1y3NBXWCQr5br4JyoMgVk4XbASBaVoT9r"
PREFIX="/Oral/comparaison"
cd "$(dirname "$0")"
python3 enumerate.py "$FID"            # -> manifest.json
python3 download.py                    # -> site/ (assets EN)
tar xzf edits.tgz                      # overlay des fichiers texte FR (+ hide-CSS)
rm -rf site/blog site/team site/legal-pages site/seo-report site/index.dup1.html

# Réécriture des chemins absolus -> préfixés par /Oral/comparaison
python3 - "$PREFIX" <<'PY'
import sys, glob, re, os
prefix = sys.argv[1]
def fix(s):
    # chemins d'assets et d'images absolus
    s = s.replace('="/assets/', f'="{prefix}/assets/')
    s = s.replace('="/images/', f'="{prefix}/images/')
    s = s.replace("='/assets/", f"='{prefix}/assets/")
    s = s.replace('url(/assets/', f'url({prefix}/assets/')
    s = s.replace('"/assets/', f'"{prefix}/assets/')   # imports mjs / json
    s = s.replace('href="/assets/', f'href="{prefix}/assets/')
    return s
exts=('html','mjs','css','json')
for ext in exts:
    for f in glob.glob(f'site/**/*.{ext}', recursive=True):
        try: t=open(f,encoding='utf-8',errors='ignore').read()
        except: continue
        n=fix(t)
        if n!=t: open(f,'w',encoding='utf-8').write(n)
# Liens de navigation internes dans les .html (href="/xxx" -> préfixe), en évitant
# les URLs déjà préfixées, externes, ancres, mailto, tel.
for f in glob.glob('site/**/*.html', recursive=True):
    t=open(f,encoding='utf-8',errors='ignore').read()
    t=re.sub(r'href="/(?!/|Oral/|assets|images)', f'href="{prefix}/', t)
    open(f,'w',encoding='utf-8').write(t)
print("path rewrite done for", prefix)
PY
echo "site2 build done: $(find site -type f | wc -l) files, $(du -sh site | cut -f1)"
