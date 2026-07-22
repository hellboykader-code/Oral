// ============================================================
// Les 21 soins dentaires (français) — regroupés par pôle de soin.
// `image` référence une clé du manifeste d'images (src/data/images.js).
// Chaque soin est sélectionnable dans le formulaire de rendez-vous.
// ============================================================

export const CATEGORIES = {
  generale: 'Dentisterie générale',
  esthetique: 'Dentisterie esthétique',
  ortho: 'Orthodontie',
  endo: 'Endodontie',
  chirurgie: 'Chirurgie orale',
  implant: 'Implantologie',
  paro: 'Parodontologie',
  enfant: 'Pédodontie',
};

export const soins = [
  {
    slug: 'detartrage',
    title: 'Détartrage & Polissage',
    category: 'generale',
    image: 'soin-detartrage',
    short:
      "Élimination du tartre et de la plaque pour des gencives saines et un sourire éclatant.",
    long:
      "Le détartrage retire le tartre accumulé au-dessus et sous la gencive, suivi d'un polissage doux qui lisse l'émail et ravive la blancheur naturelle. Un geste préventif recommandé une à deux fois par an.",
  },
  {
    slug: 'bilan-bucco-dentaire',
    title: 'Examen & Bilan bucco-dentaire',
    category: 'generale',
    image: 'soin-bilan',
    short:
      "Un contrôle complet et des radiographies pour détecter tôt et prévenir.",
    long:
      "Nous examinons dents, gencives et articulations, avec radiographies si nécessaire, afin d'établir un plan de traitement personnalisé et de dépister précocement caries et pathologies.",
  },
  {
    slug: 'traitement-caries',
    title: 'Traitement des caries',
    category: 'generale',
    image: 'soin-caries',
    short:
      "Soins conservateurs et composites esthétiques qui préservent la dent naturelle.",
    long:
      "La carie est nettoyée puis la dent restaurée avec un composite de la teinte exacte de votre émail. Résultat discret, solide et durable, dans le respect maximal du tissu sain.",
  },
  {
    slug: 'blanchiment',
    title: 'Blanchiment dentaire',
    category: 'esthetique',
    image: 'soin-blanchiment',
    short:
      "Un sourire plus lumineux en une séance, avec des produits sûrs et contrôlés.",
    long:
      "Blanchiment professionnel au fauteuil ou à domicile sur gouttières sur mesure. Les taches liées au café, au thé ou au tabac s'estompent pour un éclat naturel, sans agresser l'émail.",
  },
  {
    slug: 'facettes',
    title: 'Facettes dentaires',
    category: 'esthetique',
    image: 'soin-facettes',
    short:
      "De fines coques céramiques pour corriger forme, teinte et alignement.",
    long:
      "Les facettes en céramique recouvrent la face visible de la dent pour masquer fêlures, espaces et colorations. Un rendu très naturel et une transformation harmonieuse du sourire.",
  },
  {
    slug: 'couronnes',
    title: 'Couronnes céramiques',
    category: 'esthetique',
    image: 'soin-couronnes',
    short:
      "Reconstituer une dent abîmée avec une couronne solide et esthétique.",
    long:
      "Lorsque la dent est trop délabrée pour un simple composite, la couronne céramique la reconstruit intégralement, restaurant fonction et esthétique avec une résistance optimale.",
  },
  {
    slug: 'bridge',
    title: 'Bridges (ponts dentaires)',
    category: 'esthetique',
    image: 'soin-bridge',
    short: "Remplacer une ou plusieurs dents manquantes sans chirurgie.",
    long:
      "Le bridge prend appui sur les dents voisines pour combler l'espace laissé par une dent absente, rétablissant mastication, élocution et sourire de façon fixe et confortable.",
  },
  {
    slug: 'implants',
    title: 'Implants dentaires',
    category: 'implant',
    image: 'soin-implants',
    short:
      "La solution durable et fixe pour remplacer une dent, racine comprise.",
    long:
      "Une racine en titane est intégrée à l'os puis surmontée d'une couronne sur mesure. L'implant offre stabilité, confort et durabilité, sans toucher aux dents adjacentes.",
  },
  {
    slug: 'protheses',
    title: 'Prothèses amovibles',
    category: 'implant',
    image: 'soin-protheses',
    short:
      "Des appareils confortables et esthétiques pour retrouver le sourire.",
    long:
      "Partielles ou complètes, nos prothèses amovibles sont conçues pour un ajustement précis et un rendu naturel, afin de manger et sourire de nouveau en toute confiance.",
  },
  {
    slug: 'devitalisation',
    title: 'Dévitalisation (traitement de canal)',
    category: 'endo',
    image: 'soin-endo',
    short:
      "Traiter l'infection en profondeur et conserver la dent naturelle.",
    long:
      "Le traitement endodontique nettoie et scelle les canaux d'une dent infectée sous anesthésie, éliminant la douleur et évitant l'extraction. La dent est ensuite restaurée durablement.",
  },
  {
    slug: 'extraction',
    title: 'Extraction dentaire',
    category: 'chirurgie',
    image: 'soin-extraction',
    short: "Une extraction douce et maîtrisée lorsque la dent est irrécupérable.",
    long:
      "Réalisée sous anesthésie locale avec des gestes atraumatiques, l'extraction reste confortable. Nous vous accompagnons ensuite vers la meilleure solution de remplacement.",
  },
  {
    slug: 'dents-de-sagesse',
    title: 'Extraction des dents de sagesse',
    category: 'chirurgie',
    image: 'soin-sagesse',
    short:
      "Prévenir douleurs et complications par une chirurgie précise et sereine.",
    long:
      "Les dents de sagesse incluses ou mal positionnées sont retirées avec précision et confort, pour prévenir infections, douleurs et déplacements des autres dents.",
  },
  {
    slug: 'greffe-osseuse',
    title: 'Greffe osseuse',
    category: 'chirurgie',
    image: 'soin-greffe',
    short: "Reconstruire le volume osseux pour accueillir un implant.",
    long:
      "Lorsque l'os est insuffisant, la greffe restaure le volume nécessaire à la pose d'un implant stable. Une étape clé pour un traitement implantaire fiable et durable.",
  },
  {
    slug: 'orthodontie-enfant',
    title: 'Orthodontie enfant',
    category: 'ortho',
    image: 'soin-ortho-enfant',
    short:
      "Guider la croissance et aligner les dents dès le plus jeune âge.",
    long:
      "Un suivi orthodontique précoce corrige les malpositions et guide le développement des mâchoires, pour un sourire harmonieux et une meilleure santé bucco-dentaire à l'âge adulte.",
  },
  {
    slug: 'aligneurs',
    title: 'Aligneurs transparents (adulte)',
    category: 'ortho',
    image: 'soin-aligneurs',
    short:
      "Aligner les dents discrètement grâce à des gouttières sur mesure.",
    long:
      "Les aligneurs transparents redressent progressivement les dents sans bagues visibles. Amovibles et confortables, ils s'intègrent à votre quotidien pour un résultat en toute discrétion.",
  },
  {
    slug: 'traitement-gencives',
    title: 'Traitement des gencives',
    category: 'paro',
    image: 'soin-gencives',
    short: "Soigner gingivite et parodontite pour préserver vos dents.",
    long:
      "Le traitement parodontal assainit les gencives enflammées et stoppe la progression du déchaussement, protégeant l'os et la stabilité de vos dents sur le long terme.",
  },
  {
    slug: 'surfacage',
    title: 'Surfaçage radiculaire',
    category: 'paro',
    image: 'soin-surfacage',
    short:
      "Un nettoyage en profondeur des racines pour des gencives assainies.",
    long:
      "Le surfaçage élimine le tartre et les bactéries logés sous la gencive, le long des racines, favorisant la cicatrisation des tissus et l'arrêt de la parodontite.",
  },
  {
    slug: 'soins-enfants',
    title: 'Soins des enfants',
    category: 'enfant',
    image: 'soin-enfants',
    short:
      "Un accueil bienveillant pour apprivoiser le dentiste en douceur.",
    long:
      "Dans un cadre rassurant et ludique, nous prenons soin des dents de lait, prévenons les caries et instaurons de bonnes habitudes pour toute la vie.",
  },
  {
    slug: 'urgences',
    title: 'Urgences dentaires',
    category: 'generale',
    image: 'soin-urgence',
    short:
      "Douleur, dent cassée, abcès : une prise en charge rapide et prioritaire.",
    long:
      "Face à une urgence, nous vous recevons dans les meilleurs délais pour soulager la douleur et traiter le problème. Contactez le cabinet, nous vous orientons immédiatement.",
  },
  {
    slug: 'prevention-scellement',
    title: 'Prévention & scellement des sillons',
    category: 'enfant',
    image: 'soin-prevention',
    short: "Protéger durablement les dents des caries dès l'enfance.",
    long:
      "Le scellement des sillons applique un vernis protecteur sur les faces mastiquantes des molaires, réduisant fortement le risque de carie chez l'enfant et l'adolescent.",
  },
  {
    slug: 'bruxisme',
    title: 'Bruxisme & gouttière occlusale',
    category: 'generale',
    image: 'soin-bruxisme',
    short:
      "Protéger vos dents du grincement nocturne avec une gouttière sur mesure.",
    long:
      "Le bruxisme use l'émail et fatigue les mâchoires. Une gouttière occlusale sur mesure protège vos dents la nuit, soulage les tensions et préserve vos articulations.",
  },
];

export const soinBySlug = (slug) => soins.find((s) => s.slug === slug);
