import { useState } from 'react';
import { NavLink, Link } from 'react-router-dom';
import { motion, useMotionValueEvent, useScroll } from 'framer-motion';
import { navLinks, site } from '../../data/site.js';
import Button from '../ui/Button.jsx';
import MobileMenu from './MobileMenu.jsx';
import Logo from '../ui/Logo.jsx';
import './Header.css';

/**
 * En-tête collant qui se masque au défilement vers le bas et réapparaît
 * au défilement vers le haut (reveal-on-scroll header).
 */
export default function Header() {
  const [hidden, setHidden] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const { scrollY } = useScroll();

  useMotionValueEvent(scrollY, 'change', (y) => {
    const prev = scrollY.getPrevious() ?? 0;
    setScrolled(y > 12);
    if (menuOpen) return;
    setHidden(y > prev && y > 240);
  });

  return (
    <>
      <motion.header
        className={`site-header ${scrolled ? 'is-scrolled' : ''}`}
        initial={{ y: 0 }}
        animate={{ y: hidden ? '-110%' : '0%' }}
        transition={{ duration: 0.4, ease: [0.16, 1, 0.3, 1] }}
      >
        <div className="container site-header__inner">
          <Link to="/" className="site-header__brand" aria-label={site.name}>
            <Logo />
            <span>{site.name}</span>
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
            <Button to="/rendez-vous" size="sm">
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
      </motion.header>

      <MobileMenu open={menuOpen} onClose={() => setMenuOpen(false)} />
    </>
  );
}
