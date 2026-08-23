import PageWrapper from '../components/ui/PageWrapper.jsx';
import Hero from '../components/sections/Hero.jsx';
import Benefits from '../components/sections/Benefits.jsx';
import BeforeAfter from '../components/sections/BeforeAfter.jsx';
import BookingSection from '../components/sections/BookingSection.jsx';
import MapHours from '../components/sections/MapHours.jsx';
import CTA from '../components/sections/CTA.jsx';

// Accueil « Sérène » — structure fidèle au modèle Dentiva, visuels d'origine.
export default function Home() {
  return (
    <PageWrapper title="Sérène — Cabinet dentaire à Lyon">
      <Hero />
      <Benefits />
      <BeforeAfter />
      <BookingSection />
      <MapHours />
      <CTA />
    </PageWrapper>
  );
}
