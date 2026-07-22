import { useState } from 'react';
import { motion } from 'framer-motion';
import Button from '../ui/Button.jsx';
import Reveal from '../ui/Reveal.jsx';
import { CATEGORIES } from '../../data/soins.js';
import '../../styles/forms.css';
import './BookingSection.css';

// Section de prise de rendez-vous rapide sur l'accueil (Nom / Téléphone /
// Soin / Date / Heure) — reprend le bloc « Booking » du modèle original.
export default function BookingSection() {
  const [form, setForm] = useState({ nom: '', tel: '', soin: '', date: '', heure: '' });
  const [sent, setSent] = useState(false);
  const set = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }));

  const onSubmit = (e) => {
    e.preventDefault();
    if (form.nom.trim() && /[0-9]{6,}/.test(form.tel.replace(/\s/g, ''))) setSent(true);
  };

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
          {sent ? (
            <div className="form-success" role="status">
              <div className="form-success__icon">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
                  <path d="m5 13 4 4L19 7" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
              </div>
              <h3>Demande envoyée !</h3>
              <p className="form-note">Merci {form.nom.split(' ')[0]}, nous confirmons votre rendez-vous rapidement.</p>
            </div>
          ) : (
            <form onSubmit={onSubmit} noValidate>
              <div className="form-row">
                <div className="field">
                  <label htmlFor="b-nom">Nom <span className="req">*</span></label>
                  <input id="b-nom" value={form.nom} onChange={set('nom')} autoComplete="name" />
                </div>
                <div className="field">
                  <label htmlFor="b-tel">Téléphone <span className="req">*</span></label>
                  <input id="b-tel" type="tel" value={form.tel} onChange={set('tel')} placeholder="+33 6 12 34 56 78" autoComplete="tel" />
                </div>
              </div>
              <div className="field">
                <label htmlFor="b-soin">Soin</label>
                <select id="b-soin" value={form.soin} onChange={set('soin')}>
                  <option value="">Sélectionnez…</option>
                  {Object.values(CATEGORIES).map((c) => (
                    <option key={c} value={c}>{c}</option>
                  ))}
                </select>
              </div>
              <div className="form-row">
                <div className="field">
                  <label htmlFor="b-date">Date</label>
                  <input id="b-date" type="date" value={form.date} onChange={set('date')} />
                </div>
                <div className="field">
                  <label htmlFor="b-heure">Heure</label>
                  <input id="b-heure" type="time" value={form.heure} onChange={set('heure')} />
                </div>
              </div>
              <Button type="submit" size="lg" className="booking__submit">
                Réserver
              </Button>
            </form>
          )}
        </motion.div>
      </div>
    </section>
  );
}
