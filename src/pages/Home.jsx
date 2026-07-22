import { motion } from 'framer-motion';
import PageWrapper from '../components/ui/PageWrapper.jsx';
import Hero from '../components/sections/Hero.jsx';
import Testimonials from '../components/sections/Testimonials.jsx';
import MapHours from '../components/sections/MapHours.jsx';
import CTA from '../components/sections/CTA.jsx';
import SectionHeader from '../components/ui/SectionHeader.jsx';
import SoinCard from '../components/ui/SoinCard.jsx';
import Reveal from '../components/ui/Reveal.jsx';
import Img from '../components/ui/Img.jsx';
import Button from '../components/ui/Button.jsx';
import { soins } from '../data/soins.js';
import { avantages, technologies } from '../data/site.js';
import { staggerParent, fadeUp } from '../lib/motion.js';
import './Home.css';

export default function Home() {
  const featured = soins.slice(0, 6);

  return (
    <PageWrapper title="Cabinet dentaire — Votre sourire entre de bonnes mains">
      <Hero />

      {/* Intro */}
      <section className="section home-intro">
        <div className="container home-intro__grid">
          <Reveal>
            <span className="eyebrow">Bienvenue</span>
            <h2 className="home-intro__title">
              Des soins dentaires simples, confortables et dignes de confiance.
            </h2>
          </Reveal>
          <Reveal className="home-intro__text">
            <p>
              Nous mettons l'accent sur des traitements personnalisés, une
              technologie moderne et une prise en charge tout en douceur. Du
              contrôle de routine aux procédures avancées, nous protégeons votre
              sourire et renforçons votre confiance.
            </p>
            <p>Parce qu'une bonne santé bucco-dentaire commence par des soins auxquels vous pouvez vous fier.</p>
            <Button to="/a-propos" variant="ghost">
              En savoir plus sur nous
            </Button>
          </Reveal>
        </div>
      </section>

      {/* 6 soins + Voir plus */}
      <section className="section home-soins">
        <div className="container">
          <div className="home-soins__head">
            <SectionHeader
              eyebrow="Nos soins"
              title="Du contrôle aux traitements, un sourire sain et éclatant."
              intro="Découvrez une sélection de nos soins. Chaque traitement est pensé pour sublimer votre sourire et votre santé bucco-dentaire."
            />
            <Button to="/nos-soins" className="home-soins__more-btn">
              Voir plus
            </Button>
          </div>

          <motion.div
            className="home-soins__grid"
            variants={staggerParent(0.08)}
            initial="hidden"
            whileInView="show"
            viewport={{ once: true, amount: 0.1 }}
          >
            {featured.map((s, i) => (
              <SoinCard key={s.slug} soin={s} index={i} />
            ))}
          </motion.div>

          <Reveal className="home-soins__foot">
            <Button to="/nos-soins" size="lg">
              Voir les 21 soins
            </Button>
          </Reveal>
        </div>
      </section>

      {/* Pourquoi nous choisir */}
      <section className="section home-why" data-surface="dark">
        <div className="container home-why__grid">
          <Reveal className="home-why__media">
            <Img
              imageKey="why-us"
              alt="Notre équipe soignante au cabinet"
              variant={1}
              className="home-why__img"
            />
          </Reveal>
          <div className="home-why__content">
            <SectionHeader
              eyebrow="Pourquoi nous choisir ?"
              title="Chez nous, votre sourire est entre de bonnes mains."
              invert
            />
            <motion.ul
              className="home-why__list"
              variants={staggerParent(0.1)}
              initial="hidden"
              whileInView="show"
              viewport={{ once: true, amount: 0.2 }}
            >
              {avantages.map((a, i) => (
                <motion.li key={a.title} variants={fadeUp}>
                  <span className="home-why__num">0{i + 1}</span>
                  <div>
                    <h3>{a.title}</h3>
                    <p>{a.text}</p>
                  </div>
                </motion.li>
              ))}
            </motion.ul>
          </div>
        </div>
      </section>

      {/* Nos technologies */}
      <section className="section home-tech">
        <div className="container">
          <SectionHeader
            eyebrow="Nos technologies"
            title="Une technologie de pointe au service de votre confort."
            align="center"
          />
          <motion.div
            className="home-tech__grid"
            variants={staggerParent(0.08)}
            initial="hidden"
            whileInView="show"
            viewport={{ once: true, amount: 0.15 }}
          >
            {technologies.map((t) => (
              <motion.div key={t.title} className="home-tech__card" variants={fadeUp}>
                <h3>{t.title}</h3>
                <p>{t.text}</p>
              </motion.div>
            ))}
          </motion.div>
        </div>
      </section>

      {/* Avant / Après */}
      <section className="section home-ba">
        <div className="container home-ba__grid">
          <Reveal className="home-ba__content">
            <SectionHeader
              eyebrow="Avant / Après"
              title="Des transformations qui redonnent le sourire."
              intro="Découvrez notre galerie avant / après : des sourires transformés qui renforcent la confiance et témoignent de la qualité de nos soins."
            />
            <Button to="/rendez-vous">Commencer ma transformation</Button>
          </Reveal>
          <Reveal className="home-ba__media" variants={fadeUp}>
            <div className="home-ba__pair">
              <div className="home-ba__shot">
                <Img imageKey="before-after" alt="Sourire avant traitement" variant={3} />
                <span>Avant</span>
              </div>
              <div className="home-ba__shot">
                <Img imageKey="gallery-1" alt="Sourire après traitement" variant={2} />
                <span className="is-after">Après</span>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      {/* Cabinet en photos */}
      <section className="section home-gallery">
        <div className="container">
          <SectionHeader
            eyebrow="Le cabinet en photos"
            title="Un environnement moderne et apaisant."
            align="center"
          />
          <motion.div
            className="home-gallery__grid"
            variants={staggerParent(0.08)}
            initial="hidden"
            whileInView="show"
            viewport={{ once: true, amount: 0.1 }}
          >
            {['gallery-1', 'gallery-2', 'gallery-3', 'gallery-4'].map((g, i) => (
              <motion.figure
                key={g}
                className={`home-gallery__item home-gallery__item--${i}`}
                variants={fadeUp}
              >
                <Img imageKey={g} alt={`Cabinet dentaire — photo ${i + 1}`} variant={i % 4} />
              </motion.figure>
            ))}
          </motion.div>
        </div>
      </section>

      <Testimonials />
      <MapHours />
      <CTA />
    </PageWrapper>
  );
}
