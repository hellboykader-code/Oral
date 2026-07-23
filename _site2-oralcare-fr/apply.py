import json, re, glob, os
tr = json.load(open('/home/user/oral3/work/translations.json'))
# Only SAFE distinctive phrases (multi-word) go into .mjs/.html/.json globally.
long_pairs = {k:v for k,v in tr.items() if ' ' in k and len(k) >= 9}
# sort longest-first to avoid partial overlaps
ordered = sorted(long_pairs.items(), key=lambda kv: -len(kv[0]))

targets = []
for ext in ('html','mjs','json'):
    targets += glob.glob(f'/home/user/oral3/site/**/*.{ext}', recursive=True)

total = 0
per_string = {}
for path in targets:
    try:
        s = open(path, encoding='utf-8', errors='ignore').read()
    except: continue
    orig = s
    for en, fr in ordered:
        if en in s:
            c = s.count(en)
            s = s.replace(en, fr)
            per_string[en] = per_string.get(en,0)+c
            total += c
    if s != orig:
        open(path,'w',encoding='utf-8').write(s)

print(f"applied {total} replacements across {len(targets)} files")
print("top replaced strings:")
for k,v in sorted(per_string.items(), key=lambda x:-x[1])[:15]:
    print(f"  {v:4d}  {k[:50]}")
missing = [k for k in long_pairs if k not in per_string]
print(f"phrases never found ({len(missing)}):", [m[:30] for m in missing][:10])
