import { motion } from 'framer-motion';
import Reveal from '../ui/Reveal.jsx';
import BookingForm from './BookingForm.jsx';
import './BookingSection.css';

// Section de prise de rendez-vous sur l'accueil : reprend EXACTEMENT le
// formulaire complet de la page « Rendez-vous » (mêmes champs, sélection des
// 21 soins), présenté dans un bandeau sombre.
export default function BookingSection() {
  return (
    <section className="section booking" data-surface="dark" id="reservation">
      <div className="container booking__inner">
        <Reveal className="booking__intro">
          <span className="eyebrow">Réservation</span>
          <h2 className="booking__title">Réservez votre rendez-vous dès maintenant</h2>
          <p className="booking__text">
            Contrôle de routine, soin esthétique ou urgence : réservez en quelques
            clics. Nous confirmons rapidement votre créneau.
          </p>
        </Reveal>

        <motion.div
          className="booking__card"
          initial={{ opacity: 0, y: 24 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, amount: 0.2 }}
          transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
        >
          <BookingForm idPrefix="home" />
        </motion.div>
      </div>
    </section>
  );
}
