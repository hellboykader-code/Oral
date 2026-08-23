import { motion } from 'framer-motion';
import { useEffect } from 'react';
import { pageTransition } from '../../lib/motion.js';

/**
 * Enveloppe de page : transition d'entrée/sortie + gestion du <title>.
 */
export default function PageWrapper({ title, children }) {
  useEffect(() => {
    if (title) document.title = title;
  }, [title]);

  return (
    <motion.div
      initial={pageTransition.initial}
      animate={pageTransition.animate}
      exit={pageTransition.exit}
    >
      {children}
    </motion.div>
  );
}
