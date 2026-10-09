import { motion } from 'framer-motion';

/**
 * Titre qui apparaît caractère par caractère (flou → net), comme le modèle original.
 * Un "\n" dans `text` force un vrai saut de ligne (contrôle précis de la mise en page).
 */
export default function SplitText({
  text,
  as = 'h1',
  className = '',
  delay = 0,
  stagger = 0.028,
}) {
  const Tag = motion[as] || motion.h1;
  const lines = String(text).split('\n');

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
    <Tag
      className={className}
      variants={container}
      initial="hidden"
      animate="show"
      aria-label={text.replace(/\n/g, ' ')}
    >
      {lines.map((line, li) => (
        <span key={li} style={{ display: 'block' }}>
          {line.split(' ').map((word, wi, arr) => (
            <span
              key={wi}
              style={{
                display: 'inline-block',
                whiteSpace: 'nowrap',
                marginRight: wi < arr.length - 1 ? '0.28em' : 0,
              }}
            >
              {word.split('').map((ch, ci) => (
                <motion.span
                  key={ci}
                  variants={letter}
                  style={{ display: 'inline-block', willChange: 'transform, filter' }}
                >
                  {ch}
                </motion.span>
              ))}
            </span>
          ))}
        </span>
      ))}
    </Tag>
  );
}
