import PageWrapper from '../components/ui/PageWrapper.jsx';
import Reveal from '../components/ui/Reveal.jsx';
import BookingForm from '../components/sections/BookingForm.jsx';
import { site, horaires } from '../data/site.js';
import { fadeUp } from '../lib/motion.js';
import '../styles/forms.css';
import './RendezVous.css';

export default function RendezVous() {
  return (
    <PageWrapper title="Prendre rendez-vous — Cabinet dentaire">
      <header className="rdv-hero">
        <div className="container">
          <Reveal>
            <span className="eyebrow">Rendez-vous</span>
            <h1 className="rdv-hero__title">Prenez rendez-vous en ligne</h1>
            <p className="rdv-hero__lead">
              Réservez votre visite ou posez-nous vos questions : nous sommes là
              pour vous accompagner et vous guider à chaque étape.
            </p>
          </Reveal>
        </div>
      </header>

      <section className="section rdv-main">
        <div className="container rdv-main__grid">
          {/* Formulaire complet (partagé avec la section de l'accueil) */}
          <Reveal className="rdv-card">
            <BookingForm idPrefix="page" />
            <p className="form-note">
              Ce formulaire nécessite une configuration côté serveur pour l'envoi
              (Netlify Forms, Formspree, e-mail…).
            </p>
          </Reveal>

          {/* Aside : infos pratiques */}
          <Reveal className="rdv-aside" data-surface="dark" variants={fadeUp}>
            <h2>Informations pratiques</h2>
            <ul className="rdv-aside__coords">
              <li>
                <span className="rdv-aside__label">Téléphone</span>
                <a href={site.contact.phoneHref}>{site.contact.phone}</a>
              </li>
              <li>
                <span className="rdv-aside__label">Email</span>
                <a href={`mailto:${site.contact.email}`}>{site.contact.email}</a>
              </li>
              <li>
                <span className="rdv-aside__label">Adresse</span>
                <span>{site.contact.address}</span>
              </li>
            </ul>
            <div className="rdv-aside__hours">
              <span className="rdv-aside__label">Horaires</span>
              <ul>
                {horaires.map((h) => (
                  <li key={h.jour}>
                    <span>{h.jour}</span>
                    <span className={h.closed ? 'is-closed' : ''}>{h.heures}</span>
                  </li>
                ))}
              </ul>
            </div>
            <p className="rdv-aside__note">
              Nous confirmons votre rendez-vous une fois la demande traitée, avec
              l'horaire et les options disponibles.
            </p>
          </Reveal>
        </div>
      </section>
    </PageWrapper>
  );
}
