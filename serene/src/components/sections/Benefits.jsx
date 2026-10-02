import { motion } from 'framer-motion';
import SectionHeader from '../ui/SectionHeader.jsx';
import Img from '../ui/Img.jsx';
import Button from '../ui/Button.jsx';
import { fadeUp, staggerParent } from '../../lib/motion.js';
import './Benefits.css';

// Section « Precision Dentistry for Lasting Results » du modèle Dentiva :
// trois atouts illustrés + appel à l'action.
const items = [
  {
    image: 'treat-1',
    title: 'Sourire en toute confiance',
    text: "Retrouvez un sourire sain, éclatant et soigné — du blanchiment aux soins les plus délicats, votre confort reste notre priorité.",
  },
  {
    image: 'treat-2',
    title: 'Un cabinet accueillant',
    text: "Petits et grands sont accueillis dans un cadre apaisant. Notre équipe vous accompagne pour un sourire dont vous serez fier.",
  },
  {
    image: 'clinic-1',
    title: 'Des soins personnalisés',
    text: "Nous concevons des plans de soin sur mesure, pensés pour votre santé bucco-dentaire comme pour votre bien-être.",
  },
];

export default function Benefits() {
  return (
    <section className="section benefits">
      <div className="container">
        <SectionHeader
          eyebrow="Pourquoi Sérène"
          title="Une dentisterie de précision pour des résultats durables."
          intro="Une équipe experte, des techniques éprouvées et une attention sincère à chaque étape de votre parcours de soin."
          align="center"
        />
        <motion.div
          className="benefits__grid"
          variants={staggerParent(0.1)}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, amount: 0.15 }}
        >
          {items.map((it) => (
            <motion.article key={it.title} className="benefit-card" variants={fadeUp}>
              <div className="benefit-card__media">
                <Img imageKey={it.image} alt={it.title} />
              </div>
              <h3 className="benefit-card__title">{it.title}</h3>
              <p className="benefit-card__text">{it.text}</p>
            </motion.article>
          ))}
        </motion.div>
        <div className="benefits__cta">
          <Button to="/rendez-vous" size="lg">Réservez votre créneau</Button>
        </div>
      </div>
    </section>
  );
}
