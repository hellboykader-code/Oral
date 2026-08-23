import './Loader.css';

// Indicateur de chargement (Suspense fallback).
export default function Loader() {
  return (
    <div className="loader" role="status" aria-live="polite">
      <span className="loader__dot" />
      <span className="loader__dot" />
      <span className="loader__dot" />
      <span className="visually-hidden">Chargement…</span>
    </div>
  );
}
