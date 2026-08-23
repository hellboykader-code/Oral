import { useRef, useState, useCallback } from 'react';
import Img from './Img.jsx';
import './BeforeAfterSlider.css';

/**
 * Comparateur avant/après interactif : on glisse la poignée (ou la souris)
 * pour révéler l'image « après ». Reproduit l'effet du modèle original.
 */
export default function BeforeAfterSlider({
  imageKey,
  beforeKey,
  afterKey,
  beforeAlt = 'Sourire avant traitement',
  afterAlt = 'Sourire après traitement',
}) {
  // Deux vraies photos : « avant » (dents naturelles) et « après » (éclaircies).
  const bKey = imageKey || beforeKey;
  const aKey = imageKey || afterKey;
  const ref = useRef(null);
  const [pos, setPos] = useState(50);
  const dragging = useRef(false);

  const move = useCallback((clientX) => {
    const el = ref.current;
    if (!el) return;
    const rect = el.getBoundingClientRect();
    const p = ((clientX - rect.left) / rect.width) * 100;
    setPos(Math.max(0, Math.min(100, p)));
  }, []);

  const onPointerDown = (e) => {
    dragging.current = true;
    move(e.clientX);
    e.currentTarget.setPointerCapture?.(e.pointerId);
  };
  const onPointerMove = (e) => {
    if (dragging.current) move(e.clientX);
  };
  const onPointerUp = () => {
    dragging.current = false;
  };
  // Survol : suit la souris sans clic (comme l'original)
  const onHoverMove = (e) => {
    if (!dragging.current) move(e.clientX);
  };

  return (
    <div
      className="ba-slider"
      ref={ref}
      onPointerDown={onPointerDown}
      onPointerMove={(e) => {
        onPointerMove(e);
        onHoverMove(e);
      }}
      onPointerUp={onPointerUp}
      onPointerLeave={onPointerUp}
      role="slider"
      aria-label="Comparateur avant / après"
      aria-valuenow={Math.round(pos)}
      aria-valuemin={0}
      aria-valuemax={100}
      tabIndex={0}
      onKeyDown={(e) => {
        if (e.key === 'ArrowLeft') setPos((p) => Math.max(0, p - 4));
        if (e.key === 'ArrowRight') setPos((p) => Math.min(100, p + 4));
      }}
    >
      {/* Après (dessous, pleine largeur) */}
      <div className="ba-slider__img">
        <Img imageKey={aKey} alt={afterAlt} variant={2} eager />
        <span className="ba-slider__tag ba-slider__tag--after">Après</span>
      </div>
      {/* Avant (dessus, découpé via clip-path pour ne pas déformer l'image) */}
      <div
        className="ba-slider__img ba-slider__img--before"
        style={{ clipPath: `inset(0 ${100 - pos}% 0 0)` }}
      >
        <Img imageKey={bKey} alt={beforeAlt} variant={3} eager />
        <span className="ba-slider__tag ba-slider__tag--before">Avant</span>
      </div>
      {/* Poignée */}
      <div className="ba-slider__handle" style={{ left: `${pos}%` }}>
        <span className="ba-slider__line" />
        <span className="ba-slider__knob" aria-hidden="true">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M9 6 3 12l6 6M15 6l6 6-6 6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
          </svg>
        </span>
      </div>
    </div>
  );
}
