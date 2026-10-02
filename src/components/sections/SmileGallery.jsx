import SectionHeader from '../ui/SectionHeader.jsx';
import Button from '../ui/Button.jsx';
import Img from '../ui/Img.jsx';
import Reveal from '../ui/Reveal.jsx';
import './SmileGallery.css';

// Défilement horizontal continu de sourires (« Smile Gallery » du modèle).
const row1 = ['gallery-1', 'soin-blanchiment', 'gallery-2', 'soin-facettes', 'gallery-3', 'soin-couronnes'];
const row2 = ['gallery-4', 'soin-aligneurs', 'after', 'soin-detartrage', 'intro-1', 'before-after'];

function Marquee({ keys, reverse }) {
  const items = [...keys, ...keys];
  return (
    <div className={`smile-marquee ${reverse ? 'smile-marquee--rev' : ''}`}>
      <div className="smile-marquee__track">
        {items.map((k, i) => (
          <div className="smile-marquee__item" key={i}>
            <Img imageKey={k} alt="Sourire de patient" variant={i % 4} />
          </div>
        ))}
      </div>
    </div>
  );
}

export default function SmileGallery() {
  return (
    <section className="section smile">
      <div className="container">
        <div className="smile__head">
          <SectionHeader
            eyebrow="Notre galerie sourires"
            title="Les beaux sourires que nous créons depuis des années."
            intro="De vraies transformations de patients : du blanchiment aux relookings complets du sourire."
          />
          <Button to="/rendez-vous" className="smile__btn">
            Prendre rendez-vous
          </Button>
        </div>
      </div>
      <Reveal className="smile__rows" amount={0.05}>
        <Marquee keys={row1} />
        <Marquee keys={row2} reverse />
      </Reveal>
    </section>
  );
}
