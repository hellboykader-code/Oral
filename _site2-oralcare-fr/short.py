import re, glob
# Safe: replace only text-node occurrences >Word<  (never attributes/code)
short = {
  'Home':'Accueil', 'About':'À propos', 'Services':'Nos soins', 'Contact':'Contact',
  'Name':'Nom', 'Phone':'Téléphone', 'Email':'Email', 'Time':'Heure',
  'Message':'Message', 'Submit':'Envoyer', 'Features':'Points forts',
  'Get Started':'Commencer', 'Learn More':'En savoir plus',
}
total=0
for f in glob.glob('/home/user/oral3/site/**/*.html', recursive=True):
    t=open(f,encoding='utf-8',errors='ignore').read(); orig=t
    for en,fr in short.items():
        t=t.replace(f'>{en}<', f'>{fr}<')
        # also inside anchor: >Word</a
    if t!=orig:
        open(f,'w',encoding='utf-8').write(t); total+=1
print(f"short-word nav/form translated in {total} files")
