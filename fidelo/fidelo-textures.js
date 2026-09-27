// Canvas textures for the Fidelo film (card faces, plans, scanner, stone).
const INK = '#f4efe6', AMBER = '#f2a65a', MUTED = '#a39a8c';
function rr(c, x, y, w, h, r) { c.beginPath(); c.moveTo(x + r, y); c.arcTo(x + w, y, x + w, y + h, r); c.arcTo(x + w, y + h, x, y + h, r); c.arcTo(x, y + h, x, y, r); c.arcTo(x, y, x + w, y, r); c.closePath(); }
function rnd(seed) { let s = seed; return () => (s = (s * 16807) % 2147483647) / 2147483647; }
export const TIERS = [
  { n: 3, label: 'Boisson offerte' },
  { n: 6, label: 'Pâtisserie maison' },
  { n: 10, label: '−15 % sur l’addition' },
  { n: 20, label: 'Repas offert · VIP' },
];

export function drawPaper(cv) {
  const c = cv.getContext('2d'), W = cv.width, H = cv.height, r = rnd(7);
  c.fillStyle = '#b8966c'; c.fillRect(0, 0, W, H);
  for (let i = 0; i < 9000; i++) { c.fillStyle = `rgba(${r() < .5 ? '90,60,30' : '255,240,210'},${r() * .12})`; c.fillRect(r() * W, r() * H, 1 + r() * 2, 1 + r() * 2); }
  const g = c.createRadialGradient(W * .8, H * .2, 10, W * .8, H * .2, W * .7); g.addColorStop(0, 'rgba(255,235,200,.18)'); g.addColorStop(1, 'rgba(80,50,20,.25)'); c.fillStyle = g; c.fillRect(0, 0, W, H);
  c.strokeStyle = 'rgba(70,40,20,.55)'; c.lineWidth = 3; c.setLineDash([14, 10]); rr(c, 34, 34, W - 68, H - 68, 26); c.stroke(); c.setLineDash([]);
  c.fillStyle = '#3b2415'; c.font = '64px Gloock, serif'; c.fillText('Café Lumière', 76, 142);
  c.font = '500 26px "Geist Mono", monospace'; c.fillStyle = 'rgba(59,36,21,.8)'; c.fillText('CARTE DE FIDÉLITÉ · 10 TAMPONS = 1 CAFÉ', 78, 190);
  for (let i = 0; i < 10; i++) {
    const x = 128 + (i % 5) * 190, y = 318 + Math.floor(i / 5) * 170;
    c.strokeStyle = 'rgba(59,36,21,.45)'; c.lineWidth = 3; c.beginPath(); c.arc(x, y, 58, 0, 7); c.stroke();
    if (i < 4) { c.save(); c.translate(x, y); c.rotate((r() - .5) * .8); c.globalAlpha = .55 + r() * .3; c.strokeStyle = '#9b2f22'; c.lineWidth = 7; c.beginPath(); c.arc(0, 0, 46, 0, 7); c.stroke(); c.fillStyle = '#9b2f22'; c.font = '44px Gloock, serif'; c.textAlign = 'center'; c.fillText('CL', 0, 15); c.restore(); }
  }
  c.strokeStyle = 'rgba(90,50,20,.16)'; c.lineWidth = 16; c.beginPath(); c.arc(W * .78, H * .74, 110, .3, 5.6); c.stroke();
  c.fillStyle = 'rgba(40,25,10,.2)'; c.beginPath(); c.moveTo(W, H - 120); c.lineTo(W, H); c.lineTo(W - 150, H); c.fill();
}

function qr(c, x, y, s, seed) {
  const n = 25, m = s / n, r = rnd(seed);
  c.fillStyle = '#f4efe6'; rr(c, x - 18, y - 18, s + 36, s + 36, 22); c.fill();
  c.fillStyle = '#14100c';
  for (let i = 0; i < n; i++) for (let j = 0; j < n; j++) {
    const f = (i < 8 && j < 8) || (i > n - 9 && j < 8) || (i < 8 && j > n - 9);
    if (!f && r() > .52) c.fillRect(x + i * m, y + j * m, m + .5, m + .5);
  }
  [[0, 0], [n - 7, 0], [0, n - 7]].forEach(([i, j]) => { c.fillRect(x + i * m, y + j * m, 7 * m, 7 * m); c.fillStyle = '#f4efe6'; c.fillRect(x + (i + 1) * m, y + (j + 1) * m, 5 * m, 5 * m); c.fillStyle = '#14100c'; c.fillRect(x + (i + 2) * m, y + (j + 2) * m, 3 * m, 3 * m); });
}

export function drawDigital(cv, st = {}) {
  const c = cv.getContext('2d'), W = cv.width, H = cv.height;
  const pts = st.points ?? 4, shop = st.shop || 'Café Lumière', tiers = st.tiers || TIERS, acc = st.accent || AMBER;
  const g = c.createLinearGradient(0, 0, W, H); g.addColorStop(0, '#221b14'); g.addColorStop(1, '#0b0907'); c.fillStyle = g; c.fillRect(0, 0, W, H);
  const rg = c.createRadialGradient(120, 60, 0, 120, 60, 620); rg.addColorStop(0, 'rgba(242,166,90,.28)'); rg.addColorStop(1, 'rgba(242,166,90,0)'); c.fillStyle = rg; c.fillRect(0, 0, W, H);
  c.strokeStyle = 'rgba(244,239,230,.14)'; c.lineWidth = 2; rr(c, 12, 12, W - 24, H - 24, 44); c.stroke();
  c.fillStyle = acc; c.beginPath(); c.arc(82, 88, 14, 0, 7); c.fill();
  c.fillStyle = MUTED; c.font = '500 24px "Geist Mono", monospace'; c.fillText('FIDELO · CARTE DE FIDÉLITÉ', 110, 97);
  c.fillStyle = INK; c.font = '70px Gloock, serif'; c.fillText(shop.length > 16 ? shop.slice(0, 15) + '…' : shop, 64, 196);
  qr(c, 742, 70, 214, 11);
  c.fillStyle = acc; c.font = '176px Gloock, serif'; c.fillText(String(pts), 60, 410);
  const pw = c.measureText(String(pts)).width;
  c.fillStyle = MUTED; c.font = '500 30px "Geist Mono", monospace'; c.fillText('/ 20 POINTS', 80 + pw, 404);
  const next = tiers.find(t => t.n > pts);
  c.fillStyle = INK; c.font = '400 30px Geist, sans-serif';
  c.fillText(next ? `Prochaine récompense : ${next.label} à ${next.n} pts` : 'Toutes les récompenses débloquées', 64, 470);
  const x0 = 64, x1 = W - 64, y = 548, seg = (x1 - x0) / 20;
  for (let i = 0; i < 20; i++) { c.fillStyle = i < pts ? acc : 'rgba(244,239,230,.13)'; rr(c, x0 + i * seg + 3, y, seg - 6, 18, 6); c.fill(); }
  c.font = '500 20px "Geist Mono", monospace'; c.textAlign = 'center';
  tiers.forEach(t => { const x = x0 + t.n * seg - seg / 2; c.fillStyle = pts >= t.n ? acc : MUTED; c.fillText(t.n, x, y + 50); });
  c.textAlign = 'left';
}

export function drawBack(cv) {
  const c = cv.getContext('2d'), W = cv.width, H = cv.height;
  const g = c.createLinearGradient(0, H, W, 0); g.addColorStop(0, '#0e0b09'); g.addColorStop(1, '#2a2017'); c.fillStyle = g; c.fillRect(0, 0, W, H);
  c.strokeStyle = 'rgba(242,166,90,.18)'; c.lineWidth = 1.5;
  for (let i = 0; i < 14; i++) { c.beginPath(); c.arc(W * .82, H * .2, 60 + i * 42, 0, 7); c.stroke(); }
  c.fillStyle = INK; c.font = '150px Gloock, serif'; c.fillText('Fidelo', 70, H / 2 + 50);
  c.fillStyle = MUTED; c.font = '500 24px "Geist Mono", monospace'; c.fillText('GOOGLE WALLET · ÉCRAN D’ACCUEIL · NAVIGATEUR', 74, H - 70);
}

export function drawPlan(cv, p) {
  const c = cv.getContext('2d'), W = cv.width, H = cv.height, hi = !!p.hi;
  if (hi) { const g = c.createLinearGradient(0, 0, W, H); g.addColorStop(0, '#ffc98a'); g.addColorStop(1, '#e8903f'); c.fillStyle = g; }
  else { const g = c.createLinearGradient(0, 0, W, H); g.addColorStop(0, '#231c15'); g.addColorStop(1, '#0d0b08'); c.fillStyle = g; }
  c.fillRect(0, 0, W, H);
  const ink = hi ? '#1a1007' : INK, sub = hi ? 'rgba(26,16,7,.72)' : MUTED;
  c.strokeStyle = hi ? 'rgba(26,16,7,.2)' : 'rgba(244,239,230,.14)'; c.lineWidth = 2; rr(c, 12, 12, W - 24, H - 24, 44); c.stroke();
  c.fillStyle = hi ? '#1a1007' : AMBER; c.font = '500 28px "Geist Mono", monospace'; c.fillText(p.name.toUpperCase(), 64, 100);
  if (p.badge) { c.font = '500 22px "Geist Mono", monospace'; const bw = c.measureText(p.badge).width + 40; c.fillStyle = hi ? '#1a1007' : AMBER; rr(c, W - 64 - bw, 66, bw, 48, 24); c.fill(); c.fillStyle = hi ? '#ffc98a' : '#1a1007'; c.fillText(p.badge, W - 44 - bw, 99); }
  c.fillStyle = ink; c.font = '190px Gloock, serif'; c.fillText(p.price, 56, 350);
  const pw = c.measureText(p.price).width; c.fillStyle = sub; c.font = '400 36px Geist, sans-serif'; c.fillText(p.unit, 76 + pw, 346);
  c.fillStyle = ink; c.font = '400 34px Geist, sans-serif'; c.fillText(p.line, 64, 500);
  c.fillStyle = sub; c.font = '400 28px Geist, sans-serif'; c.fillText(p.line2 || '', 64, 550);
}

export function drawScanner(cv) {
  const c = cv.getContext('2d'), W = cv.width, H = cv.height;
  c.fillStyle = '#0d0b09'; c.fillRect(0, 0, W, H);
  c.fillStyle = MUTED; c.font = '500 22px "Geist Mono", monospace'; c.fillText('ESPACE COMMERÇANT', 44, 90);
  c.fillStyle = INK; c.font = '52px Gloock, serif'; c.fillText('Scanner', 44, 160);
  const s = 360, x = (W - s) / 2, y = 290; c.strokeStyle = AMBER; c.lineWidth = 8; const k = 60;
  [[x, y, 1, 1], [x + s, y, -1, 1], [x, y + s, 1, -1], [x + s, y + s, -1, -1]].forEach(([a, b, dx, dy]) => { c.beginPath(); c.moveTo(a, b + dy * k); c.lineTo(a, b); c.lineTo(a + dx * k, b); c.stroke(); });
  c.fillStyle = 'rgba(242,166,90,.8)'; c.fillRect(x + 20, y + s / 2 - 2, s - 40, 4);
  c.fillStyle = MUTED; c.font = '400 26px Geist, sans-serif'; c.textAlign = 'center'; c.fillText('Visez le code du client', W / 2, y + s + 70);
  c.fillStyle = AMBER; rr(c, 44, H - 170, W - 88, 96, 48); c.fill(); c.fillStyle = '#1a1007'; c.font = '600 30px Geist, sans-serif'; c.fillText('+1 point', W / 2, H - 110); c.textAlign = 'left';
}

export function drawStone(cv) {
  const c = cv.getContext('2d'), W = cv.width, H = cv.height, r = rnd(3);
  c.fillStyle = '#1b1815'; c.fillRect(0, 0, W, H);
  for (let i = 0; i < 2600; i++) { const v = 30 + r() * 70; c.fillStyle = `rgba(${v + 20},${v + 10},${v},${.25 + r() * .5})`; const s = 1 + r() * (r() < .05 ? 9 : 3); c.beginPath(); c.ellipse(r() * W, r() * H, s, s * (.5 + r() * .5), r() * 3, 0, 7); c.fill(); }
  for (let i = 0; i < 60; i++) { c.fillStyle = `rgba(200,150,100,${.15 + r() * .2})`; c.beginPath(); c.arc(r() * W, r() * H, 2 + r() * 5, 0, 7); c.fill(); }
}

export function drawSoft(cv) {
  const c = cv.getContext('2d'), W = cv.width, g = c.createRadialGradient(W / 2, W / 2, 0, W / 2, W / 2, W / 2);
  g.addColorStop(0, 'rgba(255,255,255,1)'); g.addColorStop(.35, 'rgba(255,255,255,.45)'); g.addColorStop(1, 'rgba(255,255,255,0)'); c.fillStyle = g; c.fillRect(0, 0, W, W);
}
