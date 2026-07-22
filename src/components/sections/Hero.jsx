import { motion } from 'framer-motion';
import Button from '../ui/Button.jsx';
import Img from '../ui/Img.jsx';
import SplitText from '../ui/SplitText.jsx';
import './Hero.css';

/**
 * Hero plein écran : grande image de fond + dégradé + texte blanc superposé.
 * Titre animé lettre par lettre, badge « Top Dentists & Tech » en bas.
 */
export default function Hero() {
  return (
    <section className="hero">
      <div className="hero__bg">
        <Img
          imageKey="hero"
          alt="Personne souriant chaleureusement à la lumière du soleil"
          eager
          className="hero__img"
        />
        <div className="hero__overlay" />
      </div>

      <div className="container hero__inner">
        <div className="hero__content">
          <SplitText
            text="Des soins dentaires d'exception"
            as="h1"
            className="hero__title"
            delay={0.15}
          />
          <motion.p
            className="hero__lead"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7, ease: [0.16, 1, 0.3, 1], delay: 0.9 }}
          >
            Préparez-vous à sublimer votre sourire avec des soins qui renforcent
            votre confiance et illuminent votre journée !
          </motion.p>
          <motion.div
            className="hero__actions"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7, ease: [0.16, 1, 0.3, 1], delay: 1.05 }}
          >
            <Button to="/rendez-vous" size="lg">
              Prendre rendez-vous
            </Button>
            <Button to="/nos-soins" variant="light" size="lg">
              Nos soins
            </Button>
          </motion.div>
        </div>

        <motion.div
          className="hero__badge"
          initial={{ opacity: 0, y: 24 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7, ease: [0.16, 1, 0.3, 1], delay: 1.25 }}
        >
          <span className="hero__badge-icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M12 2 4 5v6c0 5 3.4 8.3 8 11 4.6-2.7 8-6 8-11V5l-8-3Z" fill="#0d1b15" />
              <path d="m9 12 2 2 4-4" stroke="#d1fc71" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
          </span>
          <div>
            <strong>Praticiens & technologies de pointe</strong>
            <span>Notre équipe utilise des outils avancés pour des soins précis.</span>
          </div>
        </motion.div>
      </div>
    </section>
  );
}
