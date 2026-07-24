// ============================================================
// Contenu global du site : navigation, coordonnées, horaires…
// ⚠️  Les coordonnées ci-dessous sont des EXEMPLES (placeholders).
//     Chaque cabinet remplace ces valeurs par ses informations réelles.
// ============================================================

export const site = {
  name: 'Sérène',
  legalName: 'Sérène — Cabinet dentaire',
  brandline: 'CABINET DENTAIRE',
  tagline: 'Des soins sereins, un sourire apaisé',
  // --- Coordonnées FICTIVES (version démo — aucune donnée réelle) ---
  contact: {
    phone: '+33 4 78 52 09 44',
    phoneHref: 'tel:+33478520944',
    email: 'contact@serene-dentaire.fr',
    address: '18 Rue des Tilleuls, 69006 Lyon, France',
    // Carte de démonstration (emplacement fictif, Lyon).
    mapsEmbed:
      'https://www.google.com/maps?q=Rue+des+Tilleuls+Lyon&output=embed',
    mapsLink:
      'https://www.google.com/maps/search/?api=1&query=S%C3%A9r%C3%A8ne+Cabinet+dentaire+Lyon',
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
  { value: '1200+', label: 'Patients suivis' },
  { value: '15+', label: "Années d'expérience" },
  { value: '21', label: 'Soins proposés' },
  { value: '94%', label: 'Patients satisfaits' },
];

// Équipe médicale — noms FICTIFS (version démo)
export const equipe = [
  { name: 'Dr. Léa Fontaine', role: 'Dentisterie esthétique — Fondatrice', phone: '+33 4 78 52 09 45', image: 'about-doctor' },
  { name: 'Dr. Nadia Benali', role: 'Spécialiste en orthodontie', phone: '+33 4 78 52 09 46', image: 'doctor-2' },
  { name: 'Dr. Thomas Ferrand', role: 'Chirurgien oral', phone: '+33 4 78 52 09 47', image: 'doctor-3' },
];

// Étapes du parcours de soin (« Working Process » du modèle original)
export const process = [
  {
    step: '01',
    title: 'Prise de rendez-vous',
    text: "Réservez votre visite en quelques clics et recevez une confirmation immédiate.",
  },
  {
    step: '02',
    title: 'Consultation & bilan',
    text: "Nous évaluons votre santé bucco-dentaire et écoutons vos attentes.",
  },
  {
    step: '03',
    title: 'Plan de soin personnalisé',
    text: "Un plan de traitement sur mesure, clair et adapté à vos besoins.",
  },
  {
    step: '04',
    title: 'Traitement & suivi',
    text: "Des soins doux et précis, avec un accompagnement jusqu'au contrôle final.",
  },
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
