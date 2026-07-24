import Reveal from './Reveal.jsx';
import WordReveal from './WordReveal.jsx';
import './SectionHeader.css';

// En-tête de section : sur-titre (eyebrow) + titre + intro.
// Le titre et l'intro se révèlent MOT PAR MOT au défilement (comme l'original).
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
      {title && (
        <WordReveal as="h2" text={title} className="section-header__title" />
      )}
      {intro && (
        <WordReveal as="p" text={intro} className="section-header__intro" stagger={0.03} />
      )}
    </Reveal>
  );
}
