// ============================================================
// Manifeste d'images.
// Les visuels réels (issus du dossier assets original) sont placés dans
// /public/images/<clé>.webp. Tant qu'une image n'est pas présente, le
// composant <Img> affiche un placeholder de marque (dégradé + libellé).
//
// Pour brancher une image réelle : déposez le fichier
//   public/images/hero.webp   →   clé "hero"
//   public/images/soin-blanchiment.webp → clé "soin-blanchiment"
// ============================================================

// Respecte le chemin de base (ex. '/Oral/' sur GitHub Pages, '/' en local).
const base = import.meta.env.BASE_URL + 'images/';

// Clés attendues → chemin public. Ajoutez/renommez selon vos fichiers.
export const IMAGES = {
  // === Tous les visuels proviennent du modèle Dentiva d'origine ===
  // Accueil / génériques
  hero: base + 'hero.webp',
  'intro-1': base + 'treat-1.webp',
  'why-us': base + 'clinic-3.webp',
  'before-after': base + 'ba1-before.webp',
  'after': base + 'ba1-after.webp',
  'cta': base + 'clinic-2.webp',
  // Comparateur avant/après (vraies images dents Dentiva)
  'before-smile': base + 'ba1-before.webp',
  'after-smile': base + 'ba1-after.webp',
  // Visuels Dentiva
  'treat-1': base + 'treat-1.webp',
  'treat-2': base + 'treat-2.webp',
  'clinic-1': base + 'clinic-1.webp',
  'clinic-2': base + 'clinic-2.webp',
  'clinic-3': base + 'clinic-3.webp',
  'consult': base + 'consult.webp',
  'mascot': base + 'mascot.webp',
  'patient-smile': base + 'patient-smile.webp',
  'doctor': base + 'doctor.webp',
  'ba1-before': base + 'ba1-before.webp',
  'ba1-after': base + 'ba1-after.webp',
  'ba2-before': base + 'ba2-before.webp',
  'ba2-after': base + 'ba2-after.webp',
  'ba3-before': base + 'ba3-before.webp',
  'ba3-after': base + 'ba3-after.webp',
  'gallery-1': base + 'ba1-after.webp',
  'gallery-2': base + 'ba2-after.webp',
  'gallery-3': base + 'ba3-after.webp',
  'gallery-4': base + 'patient-smile.webp',
  // À propos / équipe (portraits médecins Dentiva)
  'about-clinic': base + 'clinic-1.webp',
  'about-doctor': base + 'doc-3.webp',
  'about-team': base + 'consult.webp',
  'doctor-2': base + 'doc-1.webp',
  'doctor-3': base + 'doc-2.webp',
  'staff-1': base + 'doc-1.webp',
  // Soins — images de procédure Dentiva réparties par type
  'soin-detartrage': base + 'proc-clean.webp',
  'soin-bilan': base + 'consult.webp',
  'soin-caries': base + 'proc-treat.webp',
  'soin-blanchiment': base + 'ba1-after.webp',
  'soin-facettes': base + 'ba2-after.webp',
  'soin-couronnes': base + 'proc-model.webp',
  'soin-bridge': base + 'proc-model.webp',
  'soin-implants': base + 'proc-aligner.webp',
  'soin-protheses': base + 'proc-model.webp',
  'soin-endo': base + 'proc-treat.webp',
  'soin-extraction': base + 'proc-chair.webp',
  'soin-sagesse': base + 'proc-treat.webp',
  'soin-greffe': base + 'clinic-3.webp',
  'soin-ortho-enfant': base + 'proc-child.webp',
  'soin-aligneurs': base + 'proc-aligner.webp',
  'soin-gencives': base + 'proc-clean.webp',
  'soin-surfacage': base + 'proc-treat.webp',
  'soin-enfants': base + 'proc-child.webp',
  'soin-urgence': base + 'proc-chair.webp',
  'soin-prevention': base + 'ba3-after.webp',
  'soin-bruxisme': base + 'proc-model.webp',
};

export function imageSrc(key) {
  return IMAGES[key] || '';
}

// Placeholder de marque (dégradé vert + libellé) encodé en data-URI SVG.
export function placeholder(label = '', variant = 0) {
  const palettes = [
    ['#963f36', '#b9564c'],
    ['#6e8568', '#4f5f4b'],
    ['#dbf6e9', '#c4ead6'],
    ['#faf7f5', '#efe7e2'],
  ];
  const [a, b] = palettes[variant % palettes.length];
  const light = variant === 2 || variant === 3;
  const fg = light ? '#963f36' : '#faf7f5';
  const txt = (label || '').replace(/&/g, '&amp;').slice(0, 40);
  const svg = `<svg xmlns='http://www.w3.org/2000/svg' width='800' height='600'>
    <defs><linearGradient id='g' x1='0' y1='0' x2='1' y2='1'>
      <stop offset='0' stop-color='${a}'/><stop offset='1' stop-color='${b}'/>
    </linearGradient></defs>
    <rect width='800' height='600' fill='url(#g)'/>
    <text x='50%' y='50%' fill='${fg}' font-family='Figtree, sans-serif'
      font-size='34' font-weight='600' text-anchor='middle'
      dominant-baseline='middle' opacity='0.85'>${txt}</text>
  </svg>`;
  return `data:image/svg+xml,${encodeURIComponent(svg)}`;
}
