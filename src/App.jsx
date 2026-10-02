import { Suspense, lazy, useEffect } from 'react';
import { Routes, Route, useLocation } from 'react-router-dom';
import { AnimatePresence } from 'framer-motion';
import Layout from './components/layout/Layout.jsx';
import ScrollToTop from './components/layout/ScrollToTop.jsx';
import Loader from './components/ui/Loader.jsx';

// Code-splitting : chaque page est chargée à la demande (dynamic import).
const Home = lazy(() => import('./pages/Home.jsx'));
const Soins = lazy(() => import('./pages/Soins.jsx'));
const About = lazy(() => import('./pages/About.jsx'));
const Contact = lazy(() => import('./pages/Contact.jsx'));
const RendezVous = lazy(() => import('./pages/RendezVous.jsx'));
const NotFound = lazy(() => import('./pages/NotFound.jsx'));

export default function App() {
  const location = useLocation();

  useEffect(() => {
    document.documentElement.lang = 'fr';
  }, []);

  return (
    <Layout>
      <ScrollToTop />
      <Suspense fallback={<Loader />}>
        <AnimatePresence mode="wait">
          <Routes location={location} key={location.pathname}>
            <Route path="/" element={<Home />} />
            <Route path="/nos-soins" element={<Soins />} />
            <Route path="/a-propos" element={<About />} />
            <Route path="/contact" element={<Contact />} />
            <Route path="/rendez-vous" element={<RendezVous />} />
            <Route path="*" element={<NotFound />} />
          </Routes>
        </AnimatePresence>
      </Suspense>
    </Layout>
  );
}
