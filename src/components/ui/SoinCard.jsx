import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import Img from './Img.jsx';
import { CATEGORIES } from '../../data/soins.js';
import { fadeUp } from '../../lib/motion.js';
import './SoinCard.css';

/**
 * Carte de soin : image (zoom au survol) + catégorie + titre + description.
 * Mène au formulaire de rendez-vous pré-rempli avec le soin choisi.
 */
export default function SoinCard({ soin, index = 0 }) {
  return (
    <motion.article className="soin-card" variants={fadeUp}>
      <Link
        to={`/rendez-vous?soin=${soin.slug}`}
        className="soin-card__link"
        aria-label={`Prendre rendez-vous — ${soin.title}`}
      >
        <div className="soin-card__media">
          <Img
            imageKey={soin.image}
            alt={soin.title}
            variant={index % 4}
            className="soin-card__img"
          />
          <span className="soin-card__cat">{CATEGORIES[soin.category]}</span>
        </div>
        <div className="soin-card__body">
          <h3 className="soin-card__title">{soin.title}</h3>
          <p className="soin-card__desc">{soin.short}</p>
          <span className="soin-card__more" aria-hidden="true">
            Prendre rendez-vous
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <path
                d="M3.75 9h10.5M9.75 4.5 14.25 9l-4.5 4.5"
                stroke="currentColor"
                strokeWidth="1.6"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
          </span>
        </div>
      </Link>
    </motion.article>
  );
}
