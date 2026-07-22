import { motion } from 'framer-motion';

/**
 * Titre qui apparaît caractère par caractère (flou → net), comme le modèle original.
 * Découpe le texte en mots (insécables) puis en lettres animées en cascade.
 */
export default function SplitText({
  text,
  as = 'h1',
  className = '',
  delay = 0,
  stagger = 0.028,
}) {
  const Tag = motion[as] || motion.h1;
  const words = String(text).split(' ');

  const container = {
    hidden: {},
    show: {
      transition: { staggerChildren: stagger, delayChildren: delay },
    },
  };
  const letter = {
    hidden: { opacity: 0, y: '0.4em', filter: 'blur(8px)' },
    show: {
      opacity: 1,
      y: '0em',
      filter: 'blur(0px)',
      transition: { duration: 0.5, ease: [0.16, 1, 0.3, 1] },
    },
  };

  return (
    <Tag className={className} variants={container} initial="hidden" animate="show" aria-label={text}>
      {words.map((word, wi) => (
        <span key={wi} style={{ display: 'inline-block', whiteSpace: 'nowrap' }}>
          {word.split('').map((ch, ci) => (
            <motion.span
              key={ci}
              variants={letter}
              style={{ display: 'inline-block', willChange: 'transform, filter' }}
            >
              {ch}
            </motion.span>
          ))}
          {wi < words.length - 1 && ' '}
        </span>
      ))}
    </Tag>
  );
}
