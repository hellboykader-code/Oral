import Reveal from '../ui/Reveal.jsx';
import { site, horaires } from '../../data/site.js';
import './MapHours.css';

/**
 * Localisation Google Maps + horaires d'ouverture + coordonnées.
 * Réutilisé sur l'accueil et la page Contact.
 */
export default function MapHours() {
  return (
    <section className="section maphours">
      <div className="container maphours__grid">
        <Reveal className="maphours__map">
          <iframe
            title="Emplacement du cabinet"
            src={site.contact.mapsEmbed}
            loading="lazy"
            referrerPolicy="no-referrer-when-downgrade"
            allowFullScreen
          />
        </Reveal>

        <Reveal className="maphours__panel" data-surface="dark">
          <span className="eyebrow">Nous trouver</span>
          <h2 className="maphours__title">Venir au cabinet</h2>

          <ul className="maphours__coords">
            <li>
              <span className="maphours__label">Adresse</span>
              <a href={site.contact.mapsLink} target="_blank" rel="noreferrer">
                {site.contact.address}
              </a>
            </li>
            <li>
              <span className="maphours__label">Téléphone</span>
              <a href={site.contact.phoneHref}>{site.contact.phone}</a>
            </li>
            <li>
              <span className="maphours__label">Email</span>
              <a href={`mailto:${site.contact.email}`}>{site.contact.email}</a>
            </li>
          </ul>

          <div className="maphours__hours">
            <span className="maphours__label">Horaires d'ouverture</span>
            <ul>
              {horaires.map((h) => (
                <li key={h.jour}>
                  <span>{h.jour}</span>
                  <span className={h.closed ? 'is-closed' : ''}>{h.heures}</span>
                </li>
              ))}
            </ul>
          </div>
        </Reveal>
      </div>
    </section>
  );
}
