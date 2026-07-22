// ============================================================
// Contenu global du site : navigation, coordonnées, horaires…
// ⚠️  Les coordonnées ci-dessous sont des EXEMPLES (placeholders).
//     Chaque cabinet remplace ces valeurs par ses informations réelles.
// ============================================================

export const site = {
  name: 'Cabinet Dentaire',
  tagline: 'Votre sourire entre de bonnes mains',
  // --- Coordonnées (À REMPLACER par le praticien) ---
  contact: {
    phone: '+33 1 23 45 67 89',
    phoneHref: 'tel:+33123456789',
    email: 'contact@cabinet-dentaire.fr',
    address: '12 Rue de l’Exemple, 75000 Paris, France',
    // Emplacement Google Maps — remplacez par l'URL d'intégration de votre cabinet.
    // (Google Maps → Partager → Intégrer une carte → copier l'URL du src)
    mapsEmbed:
      'https://www.google.com/maps?q=12+Rue+de+Rivoli,+75004+Paris&output=embed',
    mapsLink: 'https://www.google.com/maps/search/?api=1&query=Cabinet+Dentaire+Paris',
  },
  social: [
    { label: 'Instagram', href: '#' },
    { label: 'Facebook', href: '#' },
    { label: 'LinkedIn', href: '#' },
  ],
};

// Horaires d'ouverture (À REMPLACER)
export const horaires = [
  { jour: 'Lundi', heures: '09h00 – 19h00' },
  { jour: 'Mardi', heures: '09h00 – 19h00' },
  { jour: 'Mercredi', heures: '09h00 – 19h00' },
  { jour: 'Jeudi', heures: '09h00 – 19h00' },
  { jour: 'Vendredi', heures: '09h00 – 18h00' },
  { jour: 'Samedi', heures: '09h00 – 13h00' },
  { jour: 'Dimanche', heures: 'Fermé', closed: true },
];

// Navigation principale (menu + footer)
export const navLinks = [
  { to: '/', label: 'Accueil' },
  { to: '/nos-soins', label: 'Nos soins' },
  { to: '/a-propos', label: 'À propos' },
  { to: '/contact', label: 'Contact' },
];

// Chiffres clés de la page d'accueil (adaptés du modèle original)
export const stats = [
  { value: '15+', label: "Années d'expérience" },
  { value: '1M', label: 'Patients satisfaits' },
  { value: '98%', label: 'Taux de satisfaction' },
  { value: '21', label: 'Soins proposés' },
];

// Arguments « Pourquoi nous choisir ? »
export const avantages = [
  {
    title: 'Une équipe à votre écoute',
    text: "Nos praticiens prennent le temps de comprendre vos attentes et vos besoins avec les techniques les plus récentes.",
  },
  {
    title: 'Sans stress, sans douleur',
    text: "Anxieux à l'idée de consulter ? Nos dentistes rendent chaque rendez-vous serein et confortable.",
  },
  {
    title: 'Des soins personnalisés',
    text: "De la première visite au contrôle final, nous simplifions vos soins pour le sourire que vous méritez.",
  },
];

// Nos technologies (section accueil)
export const technologies = [
  {
    title: 'Planification 3D',
    text: 'Une imagerie 3D précise pour planifier chaque traitement en toute sécurité.',
  },
  {
    title: 'Aperçu numérique du sourire',
    text: 'Visualisez votre futur sourire avant même de commencer le traitement.',
  },
  {
    title: 'Outils de précision',
    text: 'Notre équipe utilise des instruments de pointe pour des soins précis et doux.',
  },
  {
    title: 'Confirmation instantanée',
    text: 'Réservez en quelques clics et recevez une confirmation immédiate.',
  },
];
