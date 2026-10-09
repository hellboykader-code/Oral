import { motion } from 'framer-motion';
import Button from '../ui/Button.jsx';
import Img from '../ui/Img.jsx';
import SplitText from '../ui/SplitText.jsx';
import './Hero.css';

const stats = [
  { value: '98%', label: 'Patients satisfaits de nos soins' },
  { value: '15k+', label: 'Traitements réalisés avec succès' },
];

/**
 * Hero « Dentiva » : mise en page en deux colonnes sur fond crème —
 * texte à gauche (titre animé + accroche + CTA + chiffres), image en carte
 * arrondie à droite. (Le modèle d'origine n'a PAS de fond plein écran.)
 */
export default function Hero() {
  return (
    <section className="hero">
      <div className="hero__grid">
        <div className="hero__content">
          <motion.span
            className="hero__eyebrow"
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
          >
            Cabinet dentaire · Lyon
          </motion.span>
          <SplitText
            text={"Corrigez votre sourire,\nretrouvez confiance"}
            as="h1"
            className="hero__title"
            delay={0.1}
          />
          <motion.p
            className="hero__lead"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7, ease: [0.16, 1, 0.3, 1], delay: 0.7 }}
          >
            De la visite de contrôle aux soins les plus avancés, notre équipe vous
            accueille dans un cadre apaisant et prend soin de votre sourire avec
            précision et douceur.
          </motion.p>
          <motion.div
            className="hero__actions"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7, ease: [0.16, 1, 0.3, 1], delay: 0.85 }}
          >
            <Button to="/rendez-vous" size="lg">Prendre rendez-vous</Button>
            <Button href="#soins" variant="ghost" size="lg">Découvrir nos soins</Button>
          </motion.div>
          <motion.dl
            className="hero__stats"
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7, ease: [0.16, 1, 0.3, 1], delay: 1 }}
          >
            {stats.map((s) => (
              <div key={s.label} className="hero__stat">
                <dt>{s.value}</dt>
                <dd>{s.label}</dd>
              </div>
            ))}
          </motion.dl>
        </div>

        <motion.div
          className="hero__media"
          initial={{ opacity: 0, scale: 0.96 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 0.8, ease: [0.16, 1, 0.3, 1], delay: 0.2 }}
        >
          <Img imageKey="hero" alt="Patiente souriante au cabinet Sérène" eager className="hero__img" />
          <div className="hero__badge">
            <span className="hero__badge-icon" aria-hidden="true">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M12 2 4 5v6c0 5 3.4 8.3 8 11 4.6-2.7 8-6 8-11V5l-8-3Z" fill="#faf7f5" />
                <path d="m9 12 2 2 4-4" stroke="#963f36" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
            </span>
            <div>
              <strong>Soins garantis</strong>
              <span>Équipe experte &amp; matériel de pointe</span>
            </div>
          </div>
        </motion.div>
      </div>
    </section>
  );
}
