import { motion, useScroll, useTransform } from 'framer-motion';
import { useRef } from 'react';
import Button from '../ui/Button.jsx';
import Img from '../ui/Img.jsx';
import { stats } from '../../data/site.js';
import { staggerParent, fadeUp } from '../../lib/motion.js';
import './Hero.css';

/**
 * Section Hero de la page d'accueil.
 * Parallaxe lié au scroll sur le visuel + apparition en cascade du texte.
 */
export default function Hero() {
  const ref = useRef(null);
  const { scrollYProgress } = useScroll({
    target: ref,
    offset: ['start start', 'end start'],
  });
  const y = useTransform(scrollYProgress, [0, 1], ['0%', '18%']);
  const scale = useTransform(scrollYProgress, [0, 1], [1, 1.12]);

  return (
    <section className="hero" ref={ref}>
      <div className="container hero__inner">
        <motion.div
          className="hero__content"
          variants={staggerParent(0.12, 0.1)}
          initial="hidden"
          animate="show"
        >
          <motion.span className="eyebrow" variants={fadeUp}>
            Cabinet dentaire · France
          </motion.span>
          <motion.h1 className="hero__title" variants={fadeUp}>
            Sublimez votre sourire avec des soins qui vous redonnent confiance.
          </motion.h1>
          <motion.p className="hero__lead" variants={fadeUp}>
            Des soins dentaires modernes, doux et personnalisés — du contrôle de
            routine aux traitements les plus avancés, pour protéger votre sourire
            au quotidien.
          </motion.p>
          <motion.div className="hero__actions" variants={fadeUp}>
            <Button to="/rendez-vous" size="lg">
              Prendre rendez-vous
            </Button>
            <Button to="/nos-soins" variant="ghost" size="lg">
              Découvrir nos soins
            </Button>
          </motion.div>

          <motion.dl className="hero__stats" variants={fadeUp}>
            {stats.map((s) => (
              <div key={s.label} className="hero__stat">
                <dt>{s.value}</dt>
                <dd>{s.label}</dd>
              </div>
            ))}
          </motion.dl>
        </motion.div>

        <motion.div
          className="hero__media"
          initial={{ opacity: 0, scale: 0.96 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 0.9, ease: [0.16, 1, 0.3, 1], delay: 0.2 }}
        >
          <motion.div className="hero__media-inner" style={{ y, scale }}>
            <Img
              imageKey="hero"
              alt="Personne souriant chaleureusement à la lumière du soleil"
              eager
              variant={0}
              className="hero__img"
            />
          </motion.div>
          <div className="hero__badge">
            <span className="hero__badge-dot" />
            Nouveaux patients bienvenus
          </div>
        </motion.div>
      </div>
    </section>
  );
}
