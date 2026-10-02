import { useMemo, useState } from 'react';
import { motion } from 'framer-motion';
import PageWrapper from '../components/ui/PageWrapper.jsx';
import SoinCard from '../components/ui/SoinCard.jsx';
import CTA from '../components/sections/CTA.jsx';
import Reveal from '../components/ui/Reveal.jsx';
import { soins, CATEGORIES } from '../data/soins.js';
import { staggerParent } from '../lib/motion.js';
import './Soins.css';

export default function Soins() {
  const [filter, setFilter] = useState('all');

  const filtered = useMemo(
    () => (filter === 'all' ? soins : soins.filter((s) => s.category === filter)),
    [filter]
  );

  const filters = [['all', 'Tous les soins'], ...Object.entries(CATEGORIES)];

  return (
    <PageWrapper title="Nos soins — Cabinet dentaire">
      <header className="soins-hero">
        <div className="container">
          <Reveal>
            <span className="eyebrow">Nos soins</span>
            <h1 className="soins-hero__title">
              21 soins pour un sourire sain, solide et éclatant.
            </h1>
            <p className="soins-hero__lead">
              Du contrôle de routine aux traitements les plus avancés, nous
              prenons soin de votre sourire à chaque étape. Sélectionnez un soin
              pour prendre rendez-vous.
            </p>
          </Reveal>
        </div>
      </header>

      <section className="section soins-list">
        <div className="container">
          <div className="soins-filters" role="tablist" aria-label="Filtrer les soins">
            {filters.map(([key, label]) => (
              <button
                key={key}
                role="tab"
                aria-selected={filter === key}
                className={`soins-filter ${filter === key ? 'is-active' : ''}`}
                onClick={() => setFilter(key)}
              >
                {label}
              </button>
            ))}
          </div>

          {/* La clé sur le filtre force un remontage propre + réanimation
              à chaque changement (robuste, sans transition de layout fragile). */}
          <motion.div
            key={filter}
            className="soins-grid"
            variants={staggerParent(0.05)}
            initial="hidden"
            animate="show"
          >
            {filtered.map((s, i) => (
              <SoinCard key={s.slug} soin={s} index={i} />
            ))}
          </motion.div>

          {filtered.length === 0 && (
            <p className="soins-empty">Aucun soin dans cette catégorie.</p>
          )}
        </div>
      </section>

      <CTA />
    </PageWrapper>
  );
}
