import Reveal from '../ui/Reveal.jsx';
import Button from '../ui/Button.jsx';
import BeforeAfterSlider from '../ui/BeforeAfterSlider.jsx';
import './BeforeAfter.css';

const items = [
  {
    title: 'Consultation offerte',
    text: "Notre équipe prend le temps de comprendre vos attentes et vos besoins avec les techniques les plus récentes.",
  },
  {
    title: 'Le sourire, en toute simplicité',
    text: "Anxieux à l'idée de consulter ? Nos dentistes rendent chaque rendez-vous serein et sans stress.",
  },
];

// Section Avant / Après avec comparateur interactif (glisser pour révéler).
export default function BeforeAfter() {
  return (
    <section className="section beforeafter">
      <div className="container beforeafter__grid">
        <div className="beforeafter__content">
          <span className="eyebrow">Pourquoi nous choisir ?</span>
          <h2 className="beforeafter__title">Galerie Avant / Après</h2>
          <p className="beforeafter__lead">
            Découvrez nos transformations de sourires : des résultats qui
            renforcent la confiance et témoignent de la qualité de nos soins.
            Glissez le curseur pour révéler l'avant / après.
          </p>

          <ul className="beforeafter__list">
            {items.map((it, i) => (
              <li key={it.title}>
                <span className="beforeafter__icon" aria-hidden="true">
                  {i === 0 ? '✚' : '☺'}
                </span>
                <div>
                  <h3>{it.title}</h3>
                  <p>{it.text}</p>
                </div>
              </li>
            ))}
          </ul>

          <Button to="/rendez-vous" size="lg">
            Prendre rendez-vous
          </Button>
        </div>

        <Reveal className="beforeafter__slider">
          <BeforeAfterSlider
            beforeKey="before-smile"
            afterKey="after-smile"
            beforeAlt="Sourire avant traitement"
            afterAlt="Sourire après traitement"
          />
        </Reveal>
      </div>
    </section>
  );
}
