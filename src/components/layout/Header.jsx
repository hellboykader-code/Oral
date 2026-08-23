import { useState } from 'react';
import { NavLink, Link } from 'react-router-dom';
import { motion, useMotionValueEvent, useScroll } from 'framer-motion';
import { navLinks, site } from '../../data/site.js';
import Button from '../ui/Button.jsx';
import MobileMenu from './MobileMenu.jsx';
import Logo from '../ui/Logo.jsx';
import './Header.css';

/**
 * En-tête « capsule » flottante posée par-dessus le Hero (comme le modèle
 * original). Se masque au défilement vers le bas, réapparaît vers le haut.
 */
export default function Header() {
  const [hidden, setHidden] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const { scrollY } = useScroll();

  useMotionValueEvent(scrollY, 'change', (y) => {
    const prev = scrollY.getPrevious() ?? 0;
    setScrolled(y > 30);
    if (menuOpen) return;
    setHidden(y > prev && y > 300);
  });

  return (
    <>
      <motion.div
        className="site-header"
        initial={{ y: 0 }}
        animate={{ y: hidden ? '-140%' : '0%' }}
        transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
      >
        <div className={`site-header__pill ${scrolled ? 'is-scrolled' : ''}`}>
          <Link to="/" className="site-header__brand" aria-label={site.legalName}>
            <Logo size={34} />
            <span className="site-header__wordmark">
              <span className="site-header__name">{site.name}</span>
              <span className="site-header__brandline">{site.brandline}</span>
            </span>
          </Link>

          <nav className="site-header__nav" aria-label="Navigation principale">
            {navLinks.map((l) => (
              <NavLink
                key={l.to}
                to={l.to}
                end={l.to === '/'}
                className={({ isActive }) =>
                  `site-header__link ${isActive ? 'is-active' : ''}`
                }
              >
                {l.label}
              </NavLink>
            ))}
          </nav>

          <div className="site-header__cta">
            <Button to="/rendez-vous" size="sm" variant="dark">
              Prendre rendez-vous
            </Button>
          </div>

          <button
            className={`burger ${menuOpen ? 'is-open' : ''}`}
            aria-label={menuOpen ? 'Fermer le menu' : 'Ouvrir le menu'}
            aria-expanded={menuOpen}
            onClick={() => setMenuOpen((v) => !v)}
          >
            <span />
            <span />
          </button>
        </div>
      </motion.div>

      <MobileMenu open={menuOpen} onClose={() => setMenuOpen(false)} />
    </>
  );
}
