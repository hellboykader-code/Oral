import { motion } from 'framer-motion';
import Img from '../ui/Img.jsx';
import Button from '../ui/Button.jsx';
import SectionHeader from '../ui/SectionHeader.jsx';
import { equipe } from '../../data/site.js';
import { staggerParent, fadeUp } from '../../lib/motion.js';
import './TeamPreview.css';

// Aperçu de l'équipe médicale (« Team » du modèle original).
export default function TeamPreview() {
  return (
    <section className="section team">
      <div className="container">
        <div className="team__head">
          <SectionHeader eyebrow="Équipe" title="Notre équipe médicale" />
          <Button to="/a-propos" variant="ghost" className="team__all">
            En savoir plus
          </Button>
        </div>

        <motion.div
          className="team__grid"
          variants={staggerParent(0.1)}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, amount: 0.15 }}
        >
          {equipe.map((m, i) => (
            <motion.figure key={i} className="team__card" variants={fadeUp}>
              <div className="team__media">
                <Img imageKey={m.image} alt={m.name} variant={i % 4} />
              </div>
              <figcaption>
                <span className="team__name">{m.name}</span>
                <span className="team__role">{m.role}</span>
                <a href={`tel:${m.phone.replace(/\s/g, '')}`} className="team__phone">
                  {m.phone}
                </a>
                <Button to="/rendez-vous" size="sm" className="team__btn">
                  Prendre rendez-vous
                </Button>
              </figcaption>
            </motion.figure>
          ))}
        </motion.div>
      </div>
    </section>
  );
}
