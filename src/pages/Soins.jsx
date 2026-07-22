import { useMemo, useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
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

          <motion.div
            className="soins-grid"
            variants={staggerParent(0.05)}
            initial="hidden"
            animate="show"
          >
            <AnimatePresence mode="popLayout">
              {filtered.map((s, i) => (
                <motion.div key={s.slug} layout exit={{ opacity: 0, scale: 0.95 }}>
                  <SoinCard soin={s} index={i} />
                </motion.div>
              ))}
            </AnimatePresence>
          </motion.div>
        </div>
      </section>

      <CTA />
    </PageWrapper>
  );
}
