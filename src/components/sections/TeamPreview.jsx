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
                <div className="team__row">
                  <a href={`tel:${m.phone.replace(/\s/g, '')}`} className="team__phone">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M6.5 3.5 9 4l1 3-1.5 1.5a12 12 0 0 0 6 6L15 14l3 1 .5 2.5a2 2 0 0 1-2.2 2.4A16 16 0 0 1 2.1 5.7 2 2 0 0 1 4.5 3.5Z" fill="currentColor" />
                    </svg>
                    {m.phone}
                  </a>
                  <div className="team__socials" aria-label="Réseaux sociaux">
                    <a href="#" aria-label="X">𝕏</a>
                    <a href="#" aria-label="LinkedIn">in</a>
                    <a href="#" aria-label="Instagram">◎</a>
                  </div>
                </div>
                <Button to="/rendez-vous" className="team__btn">
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
