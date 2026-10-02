#!/usr/bin/env bash
# Site 2 : export Framer OralCare TRADUIT EN FRANÇAIS (édition en place).
# Télécharge les assets d'origine depuis le Drive public, applique par-dessus
# tous les fichiers traduits (HTML/MJS/JSON + données CMS .framercms + modules
# corrigés), retire les pages exclues, réécrit les chemins pour /Oral/comparaison.
set -e
FID="1y3NBXWCQr5br4JyoMgVk4XbASBaVoT9r"
PREFIX="/Oral/comparaison"
cd "$(dirname "$0")"
python3 enumerate.py "$FID"
python3 download.py
# Re-télécharger proprement les fichiers Drive corrompus (avertissement antivirus)
python3 - <<'PY'
import json,subprocess,os
man=json.load(open('manifest.json'))
idp={m['path'].lstrip('/'):m['id'] for m in man if m['type']=='file'}
import glob
for f in glob.glob('site/**/*',recursive=True):
    if os.path.isfile(f) and os.path.getsize(f)<12000:
        head=open(f,'rb').read(400)
        if b'Virus scan warning' in head or b"can't scan this file" in head:
            rel=os.path.relpath(f,'site'); fid=idp.get(rel)
            if fid:
                subprocess.run(['curl','-sSL','-o',f,f'https://drive.usercontent.google.com/download?id={fid}&export=download&confirm=t'],check=False)
PY
# Appliquer TOUTES les traductions + données CMS FR + modules
tar xzf overlay.tgz -C site
# Retirer pages exclues (Blog / Legal / interne / doublon) — Team conservée (FR)
rm -rf site/blog site/legal-pages site/seo-report site/index.dup1.html
# Règles produit : masquer avis patients / blog / FAQ (sections rendues côté client).
# On cible les data-framer-name (présents dans le DOM même après hydratation).
python3 - <<'PY'
import glob, re
NAMES = ["Reviews","Review","Testimonials","Testimonial","Our Client's Words",
         "Client's Words","Blog","FAQ","Faq","Innovation",
         "User 1","User 2","User 3","User 4","User 5","User 6",
         "Legal Page","Privacy Policy","Terms and Conditions","Utility Page"]
sel = ",".join('[data-framer-name="%s"]' % n for n in NAMES)
CSS = '<style id="regles-produit">' + sel + '{display:none !important;}</style>'
for f in glob.glob('site/**/*.html', recursive=True):
    t = open(f, encoding='utf-8', errors='ignore').read()
    t = re.sub(r'<style id="regles-produit">.*?</style>', '', t, flags=re.S)
    if '</head>' not in t:
        continue
    open(f, 'w', encoding='utf-8').write(t.replace('</head>', CSS + '</head>', 1))
print("CSS règles produit injecté")
PY
# Réécriture des chemins absolus -> /Oral/comparaison
python3 - "$PREFIX" <<'PY'
import sys,glob,re
pfx=sys.argv[1]
def fix(s):
    for a,b in [('="/assets/',f'="{pfx}/assets/'),('="/images/',f'="{pfx}/images/'),
                ("='/assets/",f"='{pfx}/assets/"),('url(/assets/',f'url({pfx}/assets/'),
                ('"/assets/',f'"{pfx}/assets/'),('href="/assets/',f'href="{pfx}/assets/')]:
        s=s.replace(a,b)
    return s
for ext in ('html','mjs','css','json'):
    for f in glob.glob(f'site/**/*.{ext}',recursive=True):
        try:t=open(f,encoding='utf-8',errors='ignore').read()
        except:continue
        n=fix(t)
        if n!=t: open(f,'w',encoding='utf-8').write(n)
for f in glob.glob('site/**/*.html',recursive=True):
    t=open(f,encoding='utf-8',errors='ignore').read()
    open(f,'w',encoding='utf-8').write(re.sub(r'href="/(?!/|Oral/|assets|images)',f'href="{pfx}/',t))
print("paths rewritten")
PY
echo "site2 FR build done: $(find site -type f | wc -l) files, $(du -sh site|cut -f1)"
