import { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { AnimatePresence, motion } from 'framer-motion';
import Button from '../ui/Button.jsx';
import { soins, CATEGORIES, soinBySlug } from '../../data/soins.js';
import { staggerParent, fadeUp } from '../../lib/motion.js';
import '../../styles/forms.css';
import '../../pages/RendezVous.css';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Les demandes sont envoyées au récepteur central DentWebPro (send.php), qui relaie
// vers l'e-mail du praticien depuis contact@dentwebpro.site (DKIM/SPF -> boîte de réception).

/**
 * Formulaire de rendez-vous complet (prénom/nom/téléphone/email, jour + heure,
 * sélection des 21 soins, message). Partagé entre la page « Rendez-vous » et la
 * section de réservation de l'accueil — un seul et même formulaire.
 * `idPrefix` évite les collisions d'identifiants si deux instances coexistent.
 */
export default function BookingForm({ idPrefix = 'rdv' }) {
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
    if (!validate()) return;
    // Envoi au praticien via FormSubmit (fonctionne une fois le site en ligne + e-mail activé).
    const payload = {
      site: 'eclat',
      _subject: 'Nouvelle demande de rendez-vous — Éclat',
      Prénom: form.prenom,
      Nom: form.nom,
      Téléphone: form.telephone,
      Email: form.email,
      Jour: form.date,
      Heure: form.heure,
      Soins: selectedTitles.join(', '),
      Message: form.message,
    };
    try {
      fetch('https://dentwebpro.site/send.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(payload),
      }).catch(() => {});
    } catch (_) {
      /* le message de succès reste affiché même si l'envoi échoue */
    }
    setSent(true);
  };

  const id = (name) => `${idPrefix}-${name}`;

  if (sent) {
    return (
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
    );
  }

  return (
    <form onSubmit={onSubmit} noValidate>
      <div className="form-row">
        <div className={`field ${errors.prenom ? 'field--error' : ''}`}>
          <label htmlFor={id('prenom')}>Prénom <span className="req">*</span></label>
          <input id={id('prenom')} value={form.prenom} onChange={set('prenom')} autoComplete="given-name" />
          {errors.prenom && <span className="field__err">{errors.prenom}</span>}
        </div>
        <div className={`field ${errors.nom ? 'field--error' : ''}`}>
          <label htmlFor={id('nom')}>Nom <span className="req">*</span></label>
          <input id={id('nom')} value={form.nom} onChange={set('nom')} autoComplete="family-name" />
          {errors.nom && <span className="field__err">{errors.nom}</span>}
        </div>
      </div>
      <div className="form-row">
        <div className={`field ${errors.telephone ? 'field--error' : ''}`}>
          <label htmlFor={id('tel')}>Téléphone <span className="req">*</span></label>
          <input id={id('tel')} type="tel" value={form.telephone} onChange={set('telephone')} autoComplete="tel" placeholder="+33 6 12 34 56 78" />
          {errors.telephone && <span className="field__err">{errors.telephone}</span>}
        </div>
        <div className={`field ${errors.email ? 'field--error' : ''}`}>
          <label htmlFor={id('mail')}>Email <span className="req">*</span></label>
          <input id={id('mail')} type="email" value={form.email} onChange={set('email')} autoComplete="email" />
          {errors.email && <span className="field__err">{errors.email}</span>}
        </div>
      </div>

      {/* Jour et heure souhaités */}
      <div className="form-row">
        <div className="field">
          <label htmlFor={id('date')}>Jour souhaité</label>
          <input id={id('date')} type="date" value={form.date} onChange={set('date')} />
        </div>
        <div className="field">
          <label htmlFor={id('heure')}>Heure souhaitée</label>
          <input id={id('heure')} type="time" value={form.heure} onChange={set('heure')} />
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
        <label htmlFor={id('msg')}>Message / description</label>
        <textarea id={id('msg')} value={form.message} onChange={set('message')} placeholder="Décrivez brièvement votre besoin (facultatif)…" />
      </div>

      <Button type="submit" size="lg" className="rdv-submit">
        Confirmer la demande de rendez-vous
      </Button>
    </form>
  );
}
