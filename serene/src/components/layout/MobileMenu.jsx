import { useEffect } from 'react';
import { NavLink } from 'react-router-dom';
import { AnimatePresence, motion } from 'framer-motion';
import { navLinks, site } from '../../data/site.js';
import Button from '../ui/Button.jsx';
import { staggerParent, fadeUp } from '../../lib/motion.js';
import './MobileMenu.css';

// Menu mobile plein écran avec animation d'entrée/sortie (AnimatePresence).
export default function MobileMenu({ open, onClose }) {
  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : '';
    return () => (document.body.style.overflow = '');
  }, [open]);

  return (
    <AnimatePresence>
      {open && (
        <motion.div
          className="mobile-menu"
          data-surface="dark"
          initial={{ opacity: 0, clipPath: 'circle(0% at 90% 6%)' }}
          animate={{ opacity: 1, clipPath: 'circle(150% at 90% 6%)' }}
          exit={{ opacity: 0, clipPath: 'circle(0% at 90% 6%)' }}
          transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
        >
          <motion.nav
            className="mobile-menu__nav"
            variants={staggerParent(0.08, 0.15)}
            initial="hidden"
            animate="show"
          >
            {navLinks.map((l) => (
              <motion.div key={l.to} variants={fadeUp}>
                <NavLink
                  to={l.to}
                  end={l.to === '/'}
                  onClick={onClose}
                  className={({ isActive }) =>
                    `mobile-menu__link ${isActive ? 'is-active' : ''}`
                  }
                >
                  {l.label}
                </NavLink>
              </motion.div>
            ))}
            <motion.div variants={fadeUp} className="mobile-menu__cta">
              <Button to="/rendez-vous" size="lg" onClick={onClose}>
                Prendre rendez-vous
              </Button>
            </motion.div>
            <motion.a
              variants={fadeUp}
              href={site.contact.phoneHref}
              className="mobile-menu__phone"
            >
              {site.contact.phone}
            </motion.a>
          </motion.nav>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
