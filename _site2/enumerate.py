import re, json, urllib.request, sys, time

def fetch(url):
    req = urllib.request.Request(url, headers={'User-Agent':'Mozilla/5.0'})
    for _ in range(3):
        try:
            return urllib.request.urlopen(req, timeout=30).read().decode('utf-8','ignore')
        except Exception as e:
            time.sleep(2)
    return ''

def list_folder(fid):
    """Return list of (id, name, is_folder) for a public folder via embeddedfolderview."""
    html = fetch(f"https://drive.google.com/embeddedfolderview?id={fid}#list")
    out = []
    # Each entry: <div class="flip-entry" id="entry-<ID>"> ... href="...(/file/d/<ID>|/drive/folders/<ID>)..." ... <div class="flip-entry-title">NAME</div>
    for m in re.finditer(r'id="entry-([0-9A-Za-z_-]{20,44})".*?href="([^"]+)".*?flip-entry-title">([^<]+)</div>', html, re.S):
        eid, href, name = m.group(1), m.group(2), m.group(3)
        is_folder = '/folders/' in href
        out.append((eid, name, is_folder))
    return out

root = sys.argv[1]
manifest = []
def walk(fid, path, depth=0):
    if depth > 6: return
    for eid, name, is_folder in list_folder(fid):
        p = f"{path}/{name}"
        if is_folder:
            manifest.append({'id':eid,'path':p,'type':'folder'})
            walk(eid, p, depth+1)
        else:
            manifest.append({'id':eid,'path':p,'type':'file'})

walk(root, '')
json.dump(manifest, open('manifest.json','w'), indent=0)
folders = [m for m in manifest if m['type']=='folder']
files = [m for m in manifest if m['type']=='file']
print(f"folders: {len(folders)}, files: {len(files)}")
# show non-asset files (html/json/md/txt/xml)
for m in files:
    if re.search(r'\.(html|json|md|txt|xml|css|js|mjs)$', m['path']):
        print("TXT", m['path'])
