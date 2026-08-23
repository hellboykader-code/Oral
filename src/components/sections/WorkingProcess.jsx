import { motion } from 'framer-motion';
import SectionHeader from '../ui/SectionHeader.jsx';
import { process } from '../../data/site.js';
import { staggerParent, fadeUp } from '../../lib/motion.js';
import './WorkingProcess.css';

// Parcours de soin en 4 étapes (« Working Process » du modèle original).
export default function WorkingProcess() {
  return (
    <section className="section process" data-surface="dark">
      <div className="container">
        <SectionHeader
          eyebrow="Notre parcours"
          title="Des soins simples, du premier rendez-vous au dernier contrôle."
          intro="Notre équipe vous accompagne à chaque étape pour le sourire que vous méritez, sans stress."
          invert
        />
        <motion.ol
          className="process__grid"
          variants={staggerParent(0.1)}
          initial="hidden"
          whileInView="show"
          viewport={{ once: true, amount: 0.2 }}
        >
          {process.map((p) => (
            <motion.li key={p.step} className="process__card" variants={fadeUp}>
              <span className="process__num">{p.step}</span>
              <h3>{p.title}</h3>
              <p>{p.text}</p>
            </motion.li>
          ))}
        </motion.ol>
      </div>
    </section>
  );
}
