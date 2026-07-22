import { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { AnimatePresence, motion } from 'framer-motion';
import PageWrapper from '../components/ui/PageWrapper.jsx';
import Reveal from '../components/ui/Reveal.jsx';
import Button from '../components/ui/Button.jsx';
import { site, horaires } from '../data/site.js';
import { soins, CATEGORIES, soinBySlug } from '../data/soins.js';
import { staggerParent, fadeUp } from '../lib/motion.js';
import '../styles/forms.css';
import './RendezVous.css';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export default function RendezVous() {
  const [params] = useSearchParams();
  const [form, setForm] = useState({
    nom: '',
    prenom: '',
    telephone: '',
    email: '',
    date: '',
    heure: '',
    message: '',
  });
  const [selected, setSelected] = useState([]);
  const [showSoins, setShowSoins] = useState(false);
  const [errors, setErrors] = useState({});
  const [sent, setSent] = useState(false);

  // Pré-sélection depuis ?soin=slug (clic sur une carte de soin)
  useEffect(() => {
    const slug = params.get('soin');
    if (slug && soinBySlug(slug)) {
      setSelected([slug]);
      setShowSoins(true);
    }
  }, [params]);

  const set = (k) => (e) => setForm((f) => ({ ...f, [k]: e.target.value }));

  const toggleSoin = (slug) =>
    setSelected((s) =>
      s.includes(slug) ? s.filter((x) => x !== slug) : [...s, slug]
    );

  const selectedTitles = useMemo(
    () => selected.map((slug) => soinBySlug(slug)?.title).filter(Boolean),
    [selected]
  );

  const validate = () => {
    const err = {};
    if (!form.nom.trim()) err.nom = 'Champ requis.';
    if (!form.prenom.trim()) err.prenom = 'Champ requis.';
    if (!/[0-9]{6,}/.test(form.telephone.replace(/\s/g, '')))
      err.telephone = 'Numéro de téléphone invalide.';
    if (!EMAIL_RE.test(form.email)) err.email = 'Adresse email invalide.';
    setErrors(err);
    return Object.keys(err).length === 0;
  };

  const onSubmit = (e) => {
    e.preventDefault();
    if (validate()) setSent(true);
  };

  return (
    <PageWrapper title="Prendre rendez-vous — Cabinet dentaire">
      <header className="rdv-hero">
        <div className="container">
          <Reveal>
            <span className="eyebrow">Rendez-vous</span>
            <h1 className="rdv-hero__title">Prenez rendez-vous en ligne</h1>
            <p className="rdv-hero__lead">
              Réservez votre visite ou posez-nous vos questions : nous sommes là
              pour vous accompagner et vous guider à chaque étape.
            </p>
          </Reveal>
        </div>
      </header>

      <section className="section rdv-main">
        <div className="container rdv-main__grid">
          {/* Formulaire */}
          <Reveal className="rdv-card">
            {sent ? (
              <div className="form-success" role="status">
                <div className="form-success__icon">
                  <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
                    <path d="m5 13 4 4L19 7" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                </div>
                <h2>Demande envoyée !</h2>
                <p className="form-note">
                  Merci {form.prenom}. Nous confirmerons rapidement votre
                  rendez-vous{selectedTitles.length ? ` pour : ${selectedTitles.join(', ')}` : ''}.
                </p>
                <Button variant="ghost" onClick={() => setSent(false)}>
                  Nouvelle demande
                </Button>
              </div>
            ) : (
              <form onSubmit={onSubmit} noValidate>
                <div className="form-row">
                  <div className={`field ${errors.prenom ? 'field--error' : ''}`}>
                    <label htmlFor="prenom">Prénom <span className="req">*</span></label>
                    <input id="prenom" value={form.prenom} onChange={set('prenom')} autoComplete="given-name" />
                    {errors.prenom && <span className="field__err">{errors.prenom}</span>}
                  </div>
                  <div className={`field ${errors.nom ? 'field--error' : ''}`}>
                    <label htmlFor="nom">Nom <span className="req">*</span></label>
                    <input id="nom" value={form.nom} onChange={set('nom')} autoComplete="family-name" />
                    {errors.nom && <span className="field__err">{errors.nom}</span>}
                  </div>
                </div>
                <div className="form-row">
                  <div className={`field ${errors.telephone ? 'field--error' : ''}`}>
                    <label htmlFor="tel">Téléphone <span className="req">*</span></label>
                    <input id="tel" type="tel" value={form.telephone} onChange={set('telephone')} autoComplete="tel" placeholder="+33 6 12 34 56 78" />
                    {errors.telephone && <span className="field__err">{errors.telephone}</span>}
                  </div>
                  <div className={`field ${errors.email ? 'field--error' : ''}`}>
                    <label htmlFor="mail">Email <span className="req">*</span></label>
                    <input id="mail" type="email" value={form.email} onChange={set('email')} autoComplete="email" />
                    {errors.email && <span className="field__err">{errors.email}</span>}
                  </div>
                </div>

                {/* Jour et heure souhaités */}
                <div className="form-row">
                  <div className="field">
                    <label htmlFor="rdv-date">Jour souhaité</label>
                    <input id="rdv-date" type="date" value={form.date} onChange={set('date')} />
                  </div>
                  <div className="field">
                    <label htmlFor="rdv-heure">Heure souhaitée</label>
                    <input id="rdv-heure" type="time" value={form.heure} onChange={set('heure')} />
                  </div>
                </div>

                {/* Pourquoi ? -> 21 soins sélectionnables */}
                <div className="rdv-why">
                  <button
                    type="button"
                    className={`rdv-why__toggle ${showSoins ? 'is-open' : ''}`}
                    aria-expanded={showSoins}
                    onClick={() => setShowSoins((v) => !v)}
                  >
                    <span>
                      Pourquoi prenez-vous rendez-vous ?
                      {selected.length > 0 && (
                        <span className="rdv-why__count">{selected.length} sélectionné{selected.length > 1 ? 's' : ''}</span>
                      )}
                    </span>
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                      <path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                  </button>

                  <AnimatePresence initial={false}>
                    {showSoins && (
                      <motion.div
                        className="rdv-why__panel"
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: 'auto', opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.35, ease: [0.16, 1, 0.3, 1] }}
                      >
                        <p className="rdv-why__hint">
                          Sélectionnez un ou plusieurs soins parmi nos 21 soins :
                        </p>
                        <motion.ul
                          className="rdv-soins"
                          variants={staggerParent(0.02)}
                          initial="hidden"
                          animate="show"
                        >
                          {soins.map((s) => {
                            const on = selected.includes(s.slug);
                            return (
                              <motion.li key={s.slug} variants={fadeUp}>
                                <button
                                  type="button"
                                  className={`rdv-soin ${on ? 'is-on' : ''}`}
                                  aria-pressed={on}
                                  onClick={() => toggleSoin(s.slug)}
                                >
                                  <span className="rdv-soin__check" aria-hidden="true" />
                                  <span className="rdv-soin__text">
                                    <span className="rdv-soin__title">{s.title}</span>
                                    <span className="rdv-soin__cat">{CATEGORIES[s.category]}</span>
                                  </span>
                                </button>
                              </motion.li>
                            );
                          })}
                        </motion.ul>
                      </motion.div>
                    )}
                  </AnimatePresence>
                </div>

                <div className="field">
                  <label htmlFor="msg">Message / description</label>
                  <textarea id="msg" value={form.message} onChange={set('message')} placeholder="Décrivez brièvement votre besoin (facultatif)…" />
                </div>

                <Button type="submit" size="lg" className="rdv-submit">
                  Confirmer la demande de rendez-vous
                </Button>
                <p className="form-note">
                  Ce formulaire nécessite une configuration côté serveur pour
                  l'envoi (Netlify Forms, Formspree, e-mail…).
                </p>
              </form>
            )}
          </Reveal>

          {/* Aside : infos pratiques */}
          <Reveal className="rdv-aside" data-surface="dark" variants={fadeUp}>
            <h2>Informations pratiques</h2>
            <ul className="rdv-aside__coords">
              <li>
                <span className="rdv-aside__label">Téléphone</span>
                <a href={site.contact.phoneHref}>{site.contact.phone}</a>
              </li>
              <li>
                <span className="rdv-aside__label">Email</span>
                <a href={`mailto:${site.contact.email}`}>{site.contact.email}</a>
              </li>
              <li>
                <span className="rdv-aside__label">Adresse</span>
                <span>{site.contact.address}</span>
              </li>
            </ul>
            <div className="rdv-aside__hours">
              <span className="rdv-aside__label">Horaires</span>
              <ul>
                {horaires.map((h) => (
                  <li key={h.jour}>
                    <span>{h.jour}</span>
                    <span className={h.closed ? 'is-closed' : ''}>{h.heures}</span>
                  </li>
                ))}
              </ul>
            </div>
            <p className="rdv-aside__note">
              Nous confirmons votre rendez-vous une fois la demande traitée, avec
              l'horaire et les options disponibles.
            </p>
          </Reveal>
        </div>
      </section>
    </PageWrapper>
  );
}
