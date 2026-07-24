import { motion } from 'framer-motion';

/**
 * Texte qui se révèle MOT PAR MOT au défilement (flou → net, léger montée),
 * fidèle à l'effet « texte mot par mot » du modèle original.
 * Rendu sémantique via `as` (h2, p, span…) ; respecte prefers-reduced-motion
 * grâce au MotionConfig global.
 */
export default function WordReveal({
  text,
  as = 'p',
  className = '',
  stagger = 0.045,
  delay = 0,
  amount = 0.5,
}) {
  const Tag = motion[as] || motion.p;
  const words = String(text).split(' ');

  const container = {
    hidden: {},
    show: { transition: { staggerChildren: stagger, delayChildren: delay } },
  };
  const word = {
    hidden: { opacity: 0, y: '0.5em', filter: 'blur(6px)' },
    show: {
      opacity: 1,
      y: '0em',
      filter: 'blur(0px)',
      transition: { duration: 0.5, ease: [0.16, 1, 0.3, 1] },
    },
  };

  return (
    <Tag
      className={className}
      variants={container}
      initial="hidden"
      whileInView="show"
      viewport={{ once: true, amount }}
      aria-label={text}
    >
      {words.map((w, i) => (
        <motion.span
          key={`${w}-${i}`}
          variants={word}
          aria-hidden="true"
          style={{ display: 'inline-block', whiteSpace: 'pre' }}
        >
          {w}
          {i < words.length - 1 ? ' ' : ''}
        </motion.span>
      ))}
    </Tag>
  );
}
