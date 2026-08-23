import { motion } from 'framer-motion';
import Button from '../ui/Button.jsx';
import { fadeUp, staggerParent } from '../../lib/motion.js';
import './CTA.css';

// Bandeau d'appel à l'action « Prendre rendez-vous ».
export default function CTA() {
  return (
    <section className="section cta">
      <div className="container">
        <motion.div
          className="cta__card"
          data-surface="dark"
          variants={staggerParent(0.12)}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, amount: 0.3 }}
        >
          <motion.span className="eyebrow" variants={fadeUp}>
            Prêt à sourire ?
          </motion.span>
          <motion.h2 className="cta__title" variants={fadeUp}>
            N'attendez plus pour offrir à votre sourire les soins qu'il mérite.
          </motion.h2>
          <motion.p className="cta__text" variants={fadeUp}>
            Contrôle de routine, soin esthétique ou urgence : réservez votre
            visite en quelques clics et profitez de soins personnalisés dans un
            cadre moderne et confortable.
          </motion.p>
          <motion.div variants={fadeUp}>
            <Button to="/rendez-vous" size="lg">
              Prendre rendez-vous
            </Button>
          </motion.div>
        </motion.div>
      </div>
    </section>
  );
}
