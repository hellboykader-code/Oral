import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';

// Remet le scroll en haut à chaque changement de route.
export default function ScrollToTop() {
  const { pathname } = useLocation();
  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'instant' in window ? 'instant' : 'auto' });
  }, [pathname]);
  return null;
}
