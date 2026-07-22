import { Link } from 'react-router-dom';
import { navLinks, site, horaires } from '../../data/site.js';
import Logo from '../ui/Logo.jsx';
import './Footer.css';

export default function Footer() {
  const year = new Date().getFullYear();
  return (
    <footer className="site-footer" data-surface="dark">
      <div className="container site-footer__grid">
        <div className="site-footer__brand">
          <Link to="/" className="site-footer__logo">
            <Logo />
            <span>{site.name}</span>
          </Link>
          <p className="site-footer__tagline">{site.tagline}</p>
          <div className="site-footer__social">
            {site.social.map((s) => (
              <a key={s.label} href={s.href} aria-label={s.label}>
                {s.label}
              </a>
            ))}
          </div>
        </div>

        <nav className="site-footer__col" aria-label="Navigation pied de page">
          <h4>Navigation</h4>
          {navLinks.map((l) => (
            <Link key={l.to} to={l.to}>
              {l.label}
            </Link>
          ))}
          <Link to="/rendez-vous">Rendez-vous</Link>
        </nav>

        <div className="site-footer__col">
          <h4>Contact</h4>
          <a href={site.contact.phoneHref}>{site.contact.phone}</a>
          <a href={`mailto:${site.contact.email}`}>{site.contact.email}</a>
          <address>{site.contact.address}</address>
        </div>

        <div className="site-footer__col">
          <h4>Horaires</h4>
          <ul className="site-footer__hours">
            {horaires.map((h) => (
              <li key={h.jour}>
                <span>{h.jour}</span>
                <span className={h.closed ? 'is-closed' : ''}>{h.heures}</span>
              </li>
            ))}
          </ul>
        </div>
      </div>

      <div className="container site-footer__bottom">
        <p>© {year} {site.name}. Tous droits réservés.</p>
        <p className="site-footer__note">Site conçu pour les cabinets dentaires en France.</p>
      </div>
    </footer>
  );
}
