import { motion } from 'framer-motion';
import PageWrapper from '../components/ui/PageWrapper.jsx';
import Hero from '../components/sections/Hero.jsx';
import AboutIntro from '../components/sections/AboutIntro.jsx';
import WorkingProcess from '../components/sections/WorkingProcess.jsx';
import BeforeAfter from '../components/sections/BeforeAfter.jsx';
import TeamPreview from '../components/sections/TeamPreview.jsx';
import BookingSection from '../components/sections/BookingSection.jsx';
import MapHours from '../components/sections/MapHours.jsx';
import SmileGallery from '../components/sections/SmileGallery.jsx';
import CTA from '../components/sections/CTA.jsx';
import SectionHeader from '../components/ui/SectionHeader.jsx';
import SoinCard from '../components/ui/SoinCard.jsx';
import Reveal from '../components/ui/Reveal.jsx';
import Button from '../components/ui/Button.jsx';
import { soins } from '../data/soins.js';
import { staggerParent } from '../lib/motion.js';
import './Home.css';

export default function Home() {
  const featured = soins.slice(0, 6);

  return (
    <PageWrapper title="Cabinet dentaire — Votre sourire entre de bonnes mains">
      <Hero />
      <AboutIntro />

      {/* Nos soins — aperçu (6 cartes) */}
      <section className="section home-soins">
        <div className="container">
          <div className="home-soins__head">
            <SectionHeader
              eyebrow="Nos soins"
              title="Du contrôle aux traitements, un sourire sain et éclatant."
              intro="Découvrez une sélection de nos soins. Sélectionnez-en un pour prendre rendez-vous."
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

      <WorkingProcess />
      <BeforeAfter />
      <TeamPreview />
      <BookingSection />
      <MapHours />
      <SmileGallery />
      <CTA />
    </PageWrapper>
  );
}
