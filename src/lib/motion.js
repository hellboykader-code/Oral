// ============================================================
// Variants Framer Motion réutilisables (héritage + orchestration).
// Centralise la « hiérarchie du mouvement » du site.
// ============================================================

export const easeOut = [0.16, 1, 0.3, 1];

// Apparition au scroll — fondu + montée
export const fadeUp = {
  hidden: { opacity: 0, y: 32 },
  show: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.7, ease: easeOut },
  },
};

export const fadeIn = {
  hidden: { opacity: 0 },
  show: { opacity: 1, transition: { duration: 0.8, ease: easeOut } },
};

export const scaleIn = {
  hidden: { opacity: 0, scale: 0.92 },
  show: { opacity: 1, scale: 1, transition: { duration: 0.7, ease: easeOut } },
};

// Conteneur qui décale l'apparition de ses enfants (stagger)
export const staggerParent = (stagger = 0.1, delay = 0) => ({
  hidden: {},
  show: {
    transition: { staggerChildren: stagger, delayChildren: delay },
  },
});

// Transitions de page (AnimatePresence)
export const pageTransition = {
  initial: { opacity: 0, y: 12 },
  animate: { opacity: 1, y: 0, transition: { duration: 0.5, ease: easeOut } },
  exit: { opacity: 0, y: -8, transition: { duration: 0.3, ease: easeOut } },
};

// Réglage ressort partagé (physique du mouvement)
export const spring = { type: 'spring', stiffness: 260, damping: 24 };
