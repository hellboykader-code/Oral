import json, os, subprocess, concurrent.futures, re

manifest = json.load(open('manifest.json'))
files = [m for m in manifest if m['type']=='file']
folders = [m for m in manifest if m['type']=='folder']

# Deduplicate paths (two root index.html — keep both by suffixing the 2nd)
os.makedirs('site', exist_ok=True)
for f in folders:
    os.makedirs('site'+f['path'], exist_ok=True)

seen = {}
def local_path(p):
    if p in seen:
        seen[p]+=1
        base,ext = os.path.splitext(p)
        return f"site{base}.dup{seen[p]}{ext}"
    seen[p]=0
    return 'site'+p

def dl(f):
    lp = local_path(f['path'])
    os.makedirs(os.path.dirname(lp), exist_ok=True)
    if os.path.exists(lp) and os.path.getsize(lp)>0:
        return ('skip', lp)
    url = f"https://drive.google.com/uc?export=download&id={f['id']}"
    r = subprocess.run(['curl','-sSL','-o',lp,url], capture_output=True)
    # detect virus-scan HTML for large files
    try:
        with open(lp,'rb') as fh: head=fh.read(200)
        if b'text/html' in head or b'<!DOCTYPE html' in head[:20]:
            if os.path.getsize(lp)<5000:
                # retry with confirm
                subprocess.run(['curl','-sSL','-o',lp,f"{url}&confirm=t"], capture_output=True)
    except: pass
    return ('ok', lp)

manifest_files = []
for f in files:
    manifest_files.append(f)

ok=0; fail=0
with concurrent.futures.ThreadPoolExecutor(max_workers=12) as ex:
    for status,lp in ex.map(dl, manifest_files):
        if status in ('ok','skip'): ok+=1
        else: fail+=1
print(f"downloaded/kept: {ok}, failed: {fail}")
