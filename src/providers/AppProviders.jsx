import { MotionConfig } from 'framer-motion';
import { createContext, useContext, useMemo } from 'react';

// Contexte global léger (Context API) — extensible (thème, préférences…).
const AppContext = createContext(null);
export const useApp = () => useContext(AppContext);

/**
 * Fournisseurs globaux de l'application.
 * MotionConfig applique un `transition` par défaut à toutes les animations et
 * respecte automatiquement « prefers-reduced-motion » (reducedMotion="user").
 */
export default function AppProviders({ children }) {
  const value = useMemo(() => ({ brand: 'Cabinet Dentaire' }), []);
  return (
    <AppContext.Provider value={value}>
      <MotionConfig
        reducedMotion="user"
        transition={{ type: 'spring', stiffness: 120, damping: 20, mass: 0.9 }}
      >
        {children}
      </MotionConfig>
    </AppContext.Provider>
  );
}
