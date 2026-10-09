import { useRef } from 'react';
import { motion, useScroll, useTransform } from 'framer-motion';

/**
 * Grand texte qui se révèle mot par mot (gris → foncé) au défilement,
 * comme la section « intro » du modèle original.
 */
function Word({ children, progress, range }) {
  const opacity = useTransform(progress, range, [0.18, 1]);
  return (
    <span style={{ position: 'relative', marginRight: '0.28em' }}>
      <span style={{ position: 'absolute', opacity: 0.18 }}>{children}</span>
      <motion.span style={{ opacity }}>{children}</motion.span>
    </span>
  );
}

export default function ScrollRevealText({ text, className = '' }) {
  const ref = useRef(null);
  const { scrollYProgress } = useScroll({
    target: ref,
    offset: ['start 0.85', 'start 0.28'],
  });
  const words = String(text).split(' ');

  return (
    <p ref={ref} className={className} style={{ display: 'flex', flexWrap: 'wrap' }}>
      {words.map((w, i) => {
        const start = i / words.length;
        const end = (i + 1) / words.length;
        return (
          <Word key={i} progress={scrollYProgress} range={[start, end]}>
            {w}
          </Word>
        );
      })}
    </p>
  );
}
