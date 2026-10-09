import { motion } from 'framer-motion';
import { fadeUp } from '../../lib/motion.js';

/**
 * Enveloppe « apparition au scroll » (animation liée au viewport).
 * Se déclenche une fois quand l'élément entre dans la fenêtre.
 */
export default function Reveal({
  children,
  variants = fadeUp,
  as = 'div',
  className,
  amount = 0.25,
  ...rest
}) {
  const M = motion[as] || motion.div;
  return (
    <M
      className={className}
      variants={variants}
      initial="hidden"
      whileInView="show"
      viewport={{ once: true, amount }}
      {...rest}
    >
      {children}
    </M>
  );
}
