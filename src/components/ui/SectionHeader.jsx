import Reveal from './Reveal.jsx';
import './SectionHeader.css';

// En-tête de section : sur-titre (eyebrow) + titre + intro.
export default function SectionHeader({
  eyebrow,
  title,
  intro,
  align = 'left',
  invert = false,
}) {
  return (
    <Reveal
      className={`section-header section-header--${align} ${
        invert ? 'section-header--invert' : ''
      }`}
    >
      {eyebrow && <span className="eyebrow">{eyebrow}</span>}
      {title && <h2 className="section-header__title">{title}</h2>}
      {intro && <p className="section-header__intro">{intro}</p>}
    </Reveal>
  );
}
