import { motion } from 'framer-motion';
import Img from '../ui/Img.jsx';
import SectionHeader from '../ui/SectionHeader.jsx';
import { temoignages } from '../../data/temoignages.js';
import { staggerParent, fadeUp } from '../../lib/motion.js';
import './Testimonials.css';

// Section témoignages patients (grille animée en cascade).
export default function Testimonials() {
  return (
    <section className="section testimonials">
      <div className="container">
        <SectionHeader
          eyebrow="Témoignages"
          title="Ils nous ont fait confiance"
          intro="Parce que chaque sourire compte, nous sommes fiers de la confiance et du bien-être que nous construisons avec nos patients."
          align="center"
        />
        <motion.div
          className="testimonials__grid"
          variants={staggerParent(0.1)}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, amount: 0.15 }}
        >
          {temoignages.map((t, i) => (
            <motion.figure key={t.id} className="testimonial" variants={fadeUp}>
              <div className="testimonial__stars" aria-label="5 étoiles sur 5">
                {'★★★★★'}
              </div>
              <blockquote className="testimonial__quote">{t.text}</blockquote>
              <figcaption className="testimonial__author">
                <Img
                  imageKey={t.image}
                  alt={t.author}
                  variant={i % 4}
                  className="testimonial__avatar"
                />
                <div>
                  <span className="testimonial__name">{t.author}</span>
                  <span className="testimonial__role">{t.title}</span>
                </div>
              </figcaption>
            </motion.figure>
          ))}
        </motion.div>
      </div>
    </section>
  );
}
