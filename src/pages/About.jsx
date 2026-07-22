import { motion } from 'framer-motion';
import PageWrapper from '../components/ui/PageWrapper.jsx';
import SectionHeader from '../components/ui/SectionHeader.jsx';
import Reveal from '../components/ui/Reveal.jsx';
import Img from '../components/ui/Img.jsx';
import CTA from '../components/sections/CTA.jsx';
import { stats } from '../data/site.js';
import { staggerParent, fadeUp } from '../lib/motion.js';
import './About.css';

// Équipe médicale (placeholders — à personnaliser par le praticien)
const equipe = [
  { name: 'Dr. [Nom du praticien]', role: 'Chirurgien-dentiste — Fondateur', image: 'about-doctor' },
  { name: 'Dr. [Nom]', role: 'Spécialiste en orthodontie', image: 'doctor-2' },
  { name: 'Dr. [Nom]', role: 'Chirurgien oral', image: 'doctor-3' },
  { name: '[Nom]', role: 'Assistant(e) dentaire', image: 'staff-1' },
];

const diplomes = [
  "Docteur en chirurgie dentaire — Université [à compléter]",
  'Diplôme universitaire d’implantologie',
  'Formation continue en dentisterie esthétique',
  'Membre de l’Ordre national des chirurgiens-dentistes',
];

export default function About() {
  return (
    <PageWrapper title="À propos — Cabinet dentaire">
      <header className="about-hero">
        <div className="container about-hero__grid">
          <Reveal>
            <span className="eyebrow">À propos</span>
            <h1 className="about-hero__title">
              Parce que chaque sourire compte.
            </h1>
            <p className="about-hero__lead">
              Depuis plus de 15 ans, nous rendons les soins dentaires simples,
              confortables et dignes de confiance. Notre priorité : des soins
              personnalisés, une technologie moderne et une prise en charge tout
              en douceur.
            </p>
          </Reveal>
          <Reveal className="about-hero__media" variants={fadeUp}>
            <Img imageKey="about-clinic" alt="Notre cabinet dentaire" variant={0} />
          </Reveal>
        </div>
      </header>

      <section className="section about-stats-sec">
        <div className="container">
          <motion.dl
            className="about-stats"
            variants={staggerParent(0.1)}
            initial="hidden"
            whileInView="show"
            viewport={{ once: true, amount: 0.3 }}
          >
            {stats.map((s) => (
              <motion.div key={s.label} variants={fadeUp} className="about-stat">
                <dt>{s.value}</dt>
                <dd>{s.label}</dd>
              </motion.div>
            ))}
          </motion.dl>
        </div>
      </section>

      {/* Notre histoire */}
      <section className="section about-story">
        <div className="container about-story__grid">
          <Reveal className="about-story__media">
            <Img imageKey="about-team" alt="L'équipe du cabinet" variant={1} />
          </Reveal>
          <div className="about-story__content">
            <SectionHeader
              eyebrow="Notre histoire"
              title="Un cabinet bâti sur la confiance et le soin."
            />
            <div className="about-story__text">
              <p>
                Notre cabinet est né d'une conviction simple : chacun mérite des
                soins dentaires de qualité, dans un cadre serein et bienveillant.
                Au fil des années, nous avons accompagné des milliers de patients
                vers un sourire plus sain et plus confiant.
              </p>
              <p>
                De la première visite au contrôle final, notre équipe simplifie
                votre parcours de soin et met tout en œuvre pour que vous
                atteigniez le sourire que vous méritez.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Le docteur */}
      <section className="section about-doctor" data-surface="dark">
        <div className="container about-doctor__grid">
          <Reveal className="about-doctor__media">
            <Img imageKey="about-doctor" alt="Portrait du praticien" variant={1} />
          </Reveal>
          <div className="about-doctor__content">
            <SectionHeader
              eyebrow="Votre praticien"
              title="Dr. [Nom du praticien]"
              intro="Chirurgien-dentiste passionné, le Dr. [Nom] met son expertise et son écoute au service de votre santé bucco-dentaire, avec une approche douce et personnalisée."
              invert
            />
            <div className="about-doctor__diplomas">
              <h3>Diplômes & certifications</h3>
              <ul>
                {diplomes.map((d) => (
                  <li key={d}>{d}</li>
                ))}
              </ul>
            </div>
          </div>
        </div>
      </section>

      {/* Équipe médicale */}
      <section className="section about-team">
        <div className="container">
          <SectionHeader
            eyebrow="Notre équipe médicale"
            title="Une équipe à votre écoute."
            align="center"
          />
          <motion.div
            className="about-team__grid"
            variants={staggerParent(0.08)}
            initial="hidden"
            whileInView="show"
            viewport={{ once: true, amount: 0.15 }}
          >
            {equipe.map((m, i) => (
              <motion.figure key={m.role} className="about-team__card" variants={fadeUp}>
                <div className="about-team__media">
                  <Img imageKey={m.image} alt={m.name} variant={i % 4} />
                </div>
                <figcaption>
                  <span className="about-team__name">{m.name}</span>
                  <span className="about-team__role">{m.role}</span>
                </figcaption>
              </motion.figure>
            ))}
          </motion.div>
        </div>
      </section>

      <CTA />
    </PageWrapper>
  );
}
