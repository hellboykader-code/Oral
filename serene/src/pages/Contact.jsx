import { useState } from 'react';
import { motion } from 'framer-motion';
import PageWrapper from '../components/ui/PageWrapper.jsx';
import Reveal from '../components/ui/Reveal.jsx';
import Button from '../components/ui/Button.jsx';
import MapHours from '../components/sections/MapHours.jsx';
import { site } from '../data/site.js';
import '../styles/forms.css';
import './Contact.css';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export default function Contact() {
  const [form, setForm] = useState({ nom: '', email: '', message: '' });
  const [errors, setErrors] = useState({});
  const [sent, setSent] = useState(false);

  const set = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }));

  const validate = () => {
    const err = {};
    if (!form.nom.trim()) err.nom = 'Veuillez indiquer votre nom.';
    if (!EMAIL_RE.test(form.email)) err.email = 'Adresse email invalide.';
    if (!form.message.trim()) err.message = 'Veuillez écrire votre message.';
    setErrors(err);
    return Object.keys(err).length === 0;
  };

  const onSubmit = (e) => {
    e.preventDefault();
    if (validate()) setSent(true);
  };

  return (
    <PageWrapper title="Contact — Cabinet dentaire">
      <header className="contact-hero">
        <div className="container">
          <Reveal>
            <span className="eyebrow">Contact</span>
            <h1 className="contact-hero__title">Contactez-nous</h1>
            <p className="contact-hero__lead">
              Une question ou besoin d'un rendez-vous ? Notre équipe est toujours
              là pour vous aider. Écrivez-nous et gardons ensemble votre sourire
              en pleine santé.
            </p>
          </Reveal>
        </div>
      </header>

      <section className="section contact-main">
        <div className="container contact-main__grid">
          <Reveal className="contact-form-card">
            {sent ? (
              <div className="form-success" role="status">
                <div className="form-success__icon">
                  <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
                    <path d="m5 13 4 4L19 7" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                </div>
                <h2>Message envoyé !</h2>
                <p className="form-note">
                  Merci {form.nom.split(' ')[0]}. Nous vous répondrons dans les
                  plus brefs délais.
                </p>
                <Button onClick={() => { setSent(false); setForm({ nom: '', email: '', message: '' }); }} variant="ghost">
                  Envoyer un autre message
                </Button>
              </div>
            ) : (
              <form onSubmit={onSubmit} noValidate>
                <h2 className="contact-form-card__title">Écrivez-nous</h2>
                <div className={`field ${errors.nom ? 'field--error' : ''}`}>
                  <label htmlFor="nom">Nom <span className="req">*</span></label>
                  <input id="nom" value={form.nom} onChange={set('nom')} autoComplete="name" />
                  {errors.nom && <span className="field__err">{errors.nom}</span>}
                </div>
                <div className={`field ${errors.email ? 'field--error' : ''}`}>
                  <label htmlFor="email">Email <span className="req">*</span></label>
                  <input id="email" type="email" value={form.email} onChange={set('email')} autoComplete="email" />
                  {errors.email && <span className="field__err">{errors.email}</span>}
                </div>
                <div className={`field ${errors.message ? 'field--error' : ''}`}>
                  <label htmlFor="message">Message <span className="req">*</span></label>
                  <textarea id="message" value={form.message} onChange={set('message')} />
                  {errors.message && <span className="field__err">{errors.message}</span>}
                </div>
                <motion.div whileTap={{ scale: 0.99 }}>
                  <Button type="submit" size="lg" className="contact-submit">
                    Envoyer le message
                  </Button>
                </motion.div>
                <p className="form-note">
                  Ce formulaire nécessite une configuration côté serveur pour
                  l'envoi (Netlify Forms, Formspree, e-mail…).
                </p>
              </form>
            )}
          </Reveal>

          <Reveal className="contact-info">
            <h2>Coordonnées</h2>
            <ul>
              <li>
                <span className="contact-info__label">Téléphone</span>
                <a href={site.contact.phoneHref}>{site.contact.phone}</a>
              </li>
              <li>
                <span className="contact-info__label">Email</span>
                <a href={`mailto:${site.contact.email}`}>{site.contact.email}</a>
              </li>
              <li>
                <span className="contact-info__label">Adresse</span>
                <address>{site.contact.address}</address>
              </li>
            </ul>
            <Button to="/rendez-vous" size="lg" className="contact-info__cta">
              Prendre rendez-vous
            </Button>
          </Reveal>
        </div>
      </section>

      <MapHours />
    </PageWrapper>
  );
}
