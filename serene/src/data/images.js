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
  // Accueil
  hero: base + 'hero.webp',
  'intro-1': base + 'intro-1.webp',
  'why-us': base + 'why-us.webp',
  'before-after': base + 'before-after.webp',
  'after': base + 'after.webp',
  // Comparateur avant/après — vraies images dents du modèle (noms versionnés
  // pour forcer le rafraîchissement du cache navigateur/CDN)
  'before-smile': base + 'ba1-before.webp',
  'after-smile': base + 'ba1-after.webp',
  // --- Visuels du modèle Dentiva (utilisés par « Sérène ») ---
  'treat-1': base + 'treat-1.webp',
  'treat-2': base + 'treat-2.webp',
  'clinic-1': base + 'clinic-1.webp',
  'clinic-2': base + 'clinic-2.webp',
  'doctor': base + 'doctor.webp',
  'ba1-before': base + 'ba1-before.webp',
  'ba1-after': base + 'ba1-after.webp',
  'ba2-before': base + 'ba2-before.webp',
  'ba2-after': base + 'ba2-after.webp',
  'ba3-before': base + 'ba3-before.webp',
  'ba3-after': base + 'ba3-after.webp',
  'gallery-1': base + 'gallery-1.webp',
  'gallery-2': base + 'gallery-2.webp',
  'gallery-3': base + 'gallery-3.webp',
  'gallery-4': base + 'gallery-4.webp',
  'cta': base + 'cta.webp',
  // À propos
  'about-clinic': base + 'about-clinic.webp',
  'about-doctor': base + 'about-doctor.webp',
  'about-team': base + 'about-team.webp',
  'doctor-2': base + 'doctor-2.webp',
  'doctor-3': base + 'doctor-3.webp',
  'staff-1': base + 'staff-1.webp',
  // Soins (une clé par soin)
  'soin-detartrage': base + 'soin-detartrage.webp',
  'soin-bilan': base + 'soin-bilan.webp',
  'soin-caries': base + 'soin-caries.webp',
  'soin-blanchiment': base + 'soin-blanchiment.webp',
  'soin-facettes': base + 'soin-facettes.webp',
  'soin-couronnes': base + 'soin-couronnes.webp',
  'soin-bridge': base + 'soin-bridge-v2.webp',
  'soin-implants': base + 'soin-implants.webp',
  'soin-protheses': base + 'soin-protheses.webp',
  'soin-endo': base + 'soin-endo-v2.webp',
  'soin-extraction': base + 'soin-extraction.webp',
  'soin-sagesse': base + 'soin-sagesse-v2.webp',
  'soin-greffe': base + 'soin-greffe.webp',
  'soin-ortho-enfant': base + 'soin-ortho-enfant.webp',
  'soin-aligneurs': base + 'soin-aligneurs.webp',
  'soin-gencives': base + 'soin-gencives.webp',
  'soin-surfacage': base + 'soin-surfacage.webp',
  'soin-enfants': base + 'soin-enfants-v2.webp',
  'soin-urgence': base + 'soin-urgence.webp',
  'soin-prevention': base + 'soin-prevention.webp',
  'soin-bruxisme': base + 'soin-bruxisme.webp',
  // Témoignages
  'temoin-1': base + 'temoin-1.webp',
  'temoin-2': base + 'temoin-2.webp',
  'temoin-3': base + 'temoin-3.webp',
  'temoin-4': base + 'temoin-4.webp',
};

export function imageSrc(key) {
  return IMAGES[key] || '';
}

// Placeholder de marque (dégradé vert + libellé) encodé en data-URI SVG.
export function placeholder(label = '', variant = 0) {
  const palettes = [
    ['#0d1b15', '#23302a'],
    ['#23302a', '#0d1b15'],
    ['#d1fc71', '#a9d94f'],
    ['#f2f2ef', '#c9cbc5'],
  ];
  const [a, b] = palettes[variant % palettes.length];
  const light = variant === 2 || variant === 3;
  const fg = light ? '#0d1b15' : '#d1fc71';
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
