# Run Locally — Maximum Fidelity Mode

This export embeds Framer's actual runtime (`react`, `motion`, `framer`, 331 chunks total, 12902 KB) so the page renders byte-for-byte identical to your live Framer site.

> Generated for **studiooo1.framer.website** on 2026-08-15.

## ⚠️ Important: Don't open `index.html` directly

Modern JavaScript modules (the `.mjs` files Framer ships) require an HTTP origin to load. Opening the file directly in a browser (`file://...`) will show a blank page with a "Failed to load module" error in the console.

You must serve the folder over HTTP. Pick the option for your operating system:

---

# 🖥️ macOS / Linux

## Option 1 — `npx serve` (zero install, fastest)

Requires Node.js. Open Terminal, navigate to the folder, run:

```bash
cd /path/to/this/folder
npx serve
```

Press `Enter` if it asks to install. It prints a URL like `http://localhost:3000`. Open it in your browser. Done.

To disable caching while iterating:

```bash
npx serve -c-1
```

## Option 2 — Python (already installed on macOS)

```bash
cd /path/to/this/folder
python3 -m http.server 8000
```

Open http://localhost:8000 in your browser. Press `Ctrl+C` in the terminal to stop.

> Python's http.server sends the right MIME types — `.mjs` files are served as `text/javascript` so the browser executes them as modules.

## Option 3 — `http-server` (better for active dev)

Better cache discipline if you're iterating on the files:

```bash
cd /path/to/this/folder
npx http-server -c-1 -p 8000
```

`-c-1` disables HTTP caching. `-p 8000` sets the port.

## Option 4 — Caddy (with correct headers automatically)

If you have [Caddy](https://caddyserver.com) installed (`brew install caddy`):

```bash
cd /path/to/this/folder
caddy file-server --listen :8000
```

Caddy auto-configures correct MIME types and CORS headers — useful if you'll deploy behind a proxy and want parity locally.

---

# 🪟 Windows

## Option 1 — `npx serve` (zero install, fastest)

Requires [Node.js](https://nodejs.org/) installed. Open **Command Prompt** or **PowerShell**:

```powershell
cd C:\path\to\this\folder
npx serve
```

Press `Enter` if it asks to install. Open the URL it prints (`http://localhost:3000`).

To disable caching:

```powershell
npx serve -c-1
```

## Option 2 — Python (must be installed)

Install Python from [python.org](https://www.python.org/downloads/) if you haven't (check the **"Add Python to PATH"** option during install).

In **Command Prompt** or **PowerShell**:

```powershell
cd C:\path\to\this\folder
python -m http.server 8000
```

> Note: on Windows the command is `python` (no `3`), unless you have the Python launcher `py` (`py -m http.server 8000`).

Open http://localhost:8000 in your browser. Press `Ctrl+C` to stop.

## Option 3 — `http-server` (better for active dev)

```powershell
cd C:\path\to\this\folder
npx http-server -c-1 -p 8000
```

## Option 4 — IIS Express / WAMP / XAMPP

If you already have a Windows web server installed, just point its document root at this folder. Default ports are 80 (IIS) or 8080 (WAMP/XAMPP).

---

# 🔧 Cross-platform: VS Code Live Server

Works on all OSes — easiest if you already use VS Code:

1. Install the **Live Server** extension by Ritwick Dey
2. Open this folder in VS Code (`File → Open Folder…`)
3. Right-click `index.html` in the file explorer → **"Open with Live Server"**
4. Your default browser opens automatically with auto-reload on file changes

---

# 🐳 Docker (any OS)

If you have Docker:

```bash
docker run --rm -it -v "$(pwd)":/usr/share/nginx/html:ro -p 8000:80 nginx:alpine
```

Open http://localhost:8000. `Ctrl+C` to stop.

> Windows PowerShell users: replace `$(pwd)` with `${PWD}`.

---

## Quickest answer per OS

| Your OS | Just run |
|---|---|
| macOS | `python3 -m http.server 8000` |
| Linux | `python3 -m http.server 8000` |
| Windows + Node.js | `npx serve` |
| Windows without Node.js | `python -m http.server 8000` (after [installing Python](https://python.org)) |

Then open the URL it prints (typically `http://localhost:8000` or `:3000`).

---

## What works offline vs needs internet

- ✅ **Works offline** — All page structure, the React/motion/framer runtime, scroll animations, hover effects, layout, your text content
- ⚠️ **Still touches `framerusercontent.com`** — Some font loaders, dynamic CMS data (if your site uses Framer CMS collections), and image-resize variants
- ❌ **Doesn't work without your form backend** — Forms still POST to wherever they were configured (Formspree, your own endpoint, etc.) — that's external by nature

If `framerusercontent.com` is unreachable from where you serve the page (corporate VPN, air-gapped network, CDN incident), you'll see partial degradation: missing fonts, no CMS items, fallback layouts. The structural rendering still works.

---

## Deploying to production

Once you've verified locally, deploy to any static host:

### Netlify (drag & drop)
1. Visit https://app.netlify.com/drop
2. Drag this entire folder onto the page
3. Done — Netlify gives you a URL

### Vercel CLI
```bash
npm i -g vercel
vercel
```

### Cloudflare Pages, GitHub Pages, S3+CloudFront, your own server
All work. The only requirement is **serve over HTTP/HTTPS**, not `file://`.

### Custom server checklist

If you're hosting on your own infrastructure:

- ✅ Serve `.mjs` files with `Content-Type: text/javascript` (most servers do this by default; verify with `curl -I https://yoursite/assets/framer/sites/.../*.mjs`)
- ✅ Allow same-origin requests for `/assets/framer/...` paths (no special CORS needed if everything is on the same domain)
- ✅ Allow connections to `framerusercontent.com` from your CSP if you have one (`script-src` and `font-src` directives)
- ⚠️ Don't set aggressive `Cache-Control` for HTML; `.mjs` chunks are content-hashed and safe to cache for a long time

---

## ⚠️ Re-export when you republish in Framer

Every time you click "Publish" in Framer Studio, Framer rotates the internal hashes on its asset chunks. The `script_main.X.mjs` URL becomes `script_main.Y.mjs`. Your snapshot here is frozen in time pointing to the old hash.

**The old chunks usually stay live on Framer's CDN for ~2 weeks**, but eventually they get cleaned up and your export starts 404'ing for some chunks. The page degrades to partial rendering.

**The fix is simple**: re-export through NoCodeExport. Each new export captures the current runtime hashes. Schedule it as part of your "republish in Framer" routine.

---

## Browser compatibility

Modern browsers only — Framer's runtime uses ES modules and modern Web APIs:

- Chrome 87+
- Safari 14+
- Firefox 89+
- Edge 87+

IE11 and older browsers will show a blank page.

---

## Troubleshooting

**Blank page when I open index.html**
You're using `file://`. Use one of the server options above.

**Page loads but no animations / hover effects**
Open the browser console. Look for "Failed to load module" errors. Most likely a chunk failed to fetch — check your network tab to see which URL is 404'ing. If it's a `framerusercontent.com` URL, your local network is blocking that domain.

**"This export feels static — no scroll effects working"**
Check the `<script type="module" data-framer-bundle="main">` in `index.html` — its `src` should point to `/assets/framer/sites/.../script_main.*.mjs`. If you opened the file from disk and the path doesn't resolve, you're not running over HTTP.

**Fonts look wrong**
Some fonts load from `framerusercontent.com` at runtime. If your network is blocking that domain, you'll get fallback fonts. Either open the network or accept the fallback.

**Forms don't submit**
Forms are configured per their original action — Formspree, custom endpoint, etc. The runtime mirror doesn't change form behavior. Check the form's `action` attribute and verify the backend is reachable.

---

Generated by NoCodeExport ([nocodeexport.com](https://www.nocodeexport.com)) — questions? Hit reply on your export-complete email.
