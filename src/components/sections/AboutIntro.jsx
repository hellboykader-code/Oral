import Reveal from '../ui/Reveal.jsx';
import Img from '../ui/Img.jsx';
import ScrollRevealText from '../ui/ScrollRevealText.jsx';
import { stats } from '../../data/site.js';
import { motion } from 'framer-motion';
import { staggerParent, fadeUp } from '../../lib/motion.js';
import './AboutIntro.css';

/**
 * Section « À propos » de l'accueil : grande phrase révélée mot par mot
 * au défilement + image + paragraphe + chiffres clés.
 */
export default function AboutIntro() {
  return (
    <section className="section about-intro">
      <div className="container">
        <span className="eyebrow">À propos</span>
        <ScrollRevealText
          className="about-intro__statement"
          text="Nous pensons que chacun mérite un sourire éclatant. Notre équipe d'experts offre des soins sur mesure dans un cadre chaleureux, avec une technologie de pointe pour les meilleurs résultats."
        />

        <div className="about-intro__grid">
          <Reveal className="about-intro__media">
            <Img imageKey="intro-1" alt="Patient souriant au cabinet" variant={1} />
          </Reveal>
          <Reveal className="about-intro__text">
            <p>
              Nous rendons les soins dentaires simples, confortables et dignes de
              confiance. L'accent est mis sur des traitements personnalisés, une
              technologie moderne et une prise en charge tout en douceur — pour
              que chaque visite soit sereine et efficace.
            </p>
            <p>
              Du contrôle de routine aux procédures avancées, nous protégeons
              votre sourire et renforçons votre confiance.
            </p>
          </Reveal>
        </div>

        <motion.dl
          className="about-intro__stats"
          variants={staggerParent(0.1)}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, amount: 0.3 }}
        >
          {stats.map((s) => (
            <motion.div key={s.label} variants={fadeUp} className="about-intro__stat">
              <dt>{s.value}</dt>
              <dd>{s.label}</dd>
            </motion.div>
          ))}
        </motion.dl>
      </div>
    </section>
  );
}
