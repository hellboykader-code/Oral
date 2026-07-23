import json, re, glob, html as htmllib

hm = json.load(open('/home/user/oral3/work/headings.json'))
# normalize keys (unescaped)
def norm(s): return htmllib.unescape(s).strip()
hm = {norm(k): v for k,v in hm.items()}

def esc(c):
    return c.replace('&','&amp;').replace('<','&lt;').replace('>','&gt;')

hding = re.compile(r'(<(h[1-4])\b[^>]*>)(.*?)(</\2>)', re.S)
charspan = re.compile(r'<span style="(display: inline-block[^"]*)">')

def rebuild(inner):
    m = charspan.search(inner)
    if not m: return None
    style = m.group(1)
    words = None
    # plain text
    plain = norm(re.sub(r'<[^>]+>','',inner))
    if plain not in hm: return None
    fr = hm[plain]
    parts=[]
    for wi, word in enumerate(fr.split(' ')):
        chars=''.join(f'<span style="{style}">{esc(c)}</span>' for c in word)
        parts.append(f'<span style="white-space:nowrap">{chars}</span>')
    return ' '.join(parts)

total=0; changed_files=0
for f in glob.glob('/home/user/oral3/site/**/*.html', recursive=True):
    t=open(f,encoding='utf-8',errors='ignore').read()
    out=[]; last=0; n=0
    for m in hding.finditer(t):
        open_tag, tag, inner, close_tag = m.group(1), m.group(2), m.group(3), m.group(4)
        if 'display: inline-block' in inner and 'white-space:nowrap' in inner:
            rb = rebuild(inner)
            if rb is not None:
                out.append(t[last:m.start()]); out.append(open_tag+rb+close_tag)
                last=m.end(); n+=1
    if n:
        out.append(t[last:])
        open(f,'w',encoding='utf-8').write(''.join(out))
        total+=n; changed_files+=1

print(f"rebuilt {total} animated headings in {changed_files} files")
