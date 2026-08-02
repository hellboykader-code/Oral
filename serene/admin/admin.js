/* ============================================================
   Espace Praticien — Sérène · logique applicative
   Mode démo : données dans localStorage (clé "serene-espace").
   Mode hébergé : si api.php répond, la même app lit/écrit côté
   serveur (voir api.php). Aucune dépendance externe hors qrcode.js.
   ============================================================ */
'use strict';

/* ---------- utilitaires ---------- */
const $ = (s, r) => (r || document).querySelector(s);
const $$ = (s, r) => [...(r || document).querySelectorAll(s)];
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const uid = () => Math.random().toString(36).slice(2, 9) + Date.now().toString(36).slice(-4);
const todayISO = () => new Date().toISOString().slice(0, 10);
const normTel = (t) => String(t || '').replace(/\D/g, '').replace(/^33/, '0');
const JOURS = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
const JOURS_C = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
const MOIS = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
const fmtDate = (iso) => { if (!iso) return '—'; const d = new Date(iso + 'T12:00'); return `${JOURS[d.getDay()]} ${d.getDate()} ${MOIS[d.getMonth()]}`; };
const fmtShort = (iso) => { const d = new Date(iso + 'T12:00'); return `${d.getDate()} ${MOIS[d.getMonth()].slice(0, 4)}.`; };
const addDays = (iso, n) => { const d = new Date(iso + 'T12:00'); d.setDate(d.getDate() + n); return d.toISOString().slice(0, 10); };
const sha = async (txt) => { const b = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(txt)); return [...new Uint8Array(b)].map((x) => x.toString(16).padStart(2, '0')).join(''); };
function toast(msg) { const t = $('#toast'); t.textContent = msg; t.classList.add('on'); clearTimeout(toast._t); toast._t = setTimeout(() => t.classList.remove('on'), 2600); }
function dl(name, content, mime) { const a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([content], { type: mime || 'text/plain' })); a.download = name; a.click(); setTimeout(() => URL.revokeObjectURL(a.href), 5000); }

/* ---------- catalogue des 21 soins (hérité du site) ---------- */
const SOINS_INIT = [
  ['detartrage', 'Détartrage & Polissage', 'generale'], ['bilan-bucco-dentaire', 'Examen & Bilan bucco-dentaire', 'generale'],
  ['traitement-caries', 'Traitement des caries', 'generale'], ['blanchiment', 'Blanchiment dentaire', 'esthetique'],
  ['facettes', 'Facettes dentaires', 'esthetique'], ['couronnes', 'Couronnes céramiques', 'esthetique'],
  ['bridge', 'Bridges (ponts dentaires)', 'esthetique'], ['implants', 'Implants dentaires', 'implant'],
  ['protheses', 'Prothèses amovibles', 'implant'], ['devitalisation', 'Dévitalisation (traitement de canal)', 'endo'],
  ['extraction', 'Extraction dentaire', 'chirurgie'], ['dents-de-sagesse', 'Extraction des dents de sagesse', 'chirurgie'],
  ['greffe-osseuse', 'Greffe osseuse', 'chirurgie'], ['orthodontie-enfant', 'Orthodontie enfant', 'ortho'],
  ['aligneurs', 'Aligneurs transparents (adulte)', 'ortho'], ['traitement-gencives', 'Traitement des gencives', 'paro'],
  ['surfacage', 'Surfaçage radiculaire', 'paro'], ['soins-enfants', 'Soins des enfants', 'enfant'],
  ['urgences', 'Urgences dentaires', 'generale'], ['prevention-scellement', 'Prévention & scellement des sillons', 'enfant'],
  ['bruxisme', 'Bruxisme & gouttière occlusale', 'generale'],
];
const CATS = { generale: 'Dentisterie générale', esthetique: 'Esthétique', ortho: 'Orthodontie', endo: 'Endodontie', chirurgie: 'Chirurgie orale', implant: 'Implantologie', paro: 'Parodontologie', enfant: 'Pédodontie' };
const SOURCES = ['Google', 'Instagram', 'Facebook', 'Recommandation', 'Passage devant le cabinet', 'Autre'];

/* ---------- fiches conseils pré-rédigées ---------- */
const FICHES_INIT = [
  { id: 'f-extraction', titre: 'Après une extraction dentaire', texte: "• Mordez la compresse 30 à 45 min sans la changer.\n• Pas de bain de bouche ni de crachat pendant 24 h.\n• Mangez tiède et mou aujourd'hui (purée, compote, yaourt).\n• Évitez tabac et alcool 48 h : ils retardent la cicatrisation.\n• Douleur ? Prenez l'antalgique prescrit, jamais d'aspirine.\n• Appliquez une poche de froid 10 min par heure si gonflement.\n• Saignement persistant ou fièvre : appelez le cabinet." },
  { id: 'f-blanchiment', titre: 'Après un blanchiment', texte: "• Pendant 48 h, évitez tout ce qui tache : café, thé, vin rouge, curry, sodas colorés, tabac.\n• Privilégiez les aliments clairs (riz, poulet, laitages).\n• Une sensibilité passagère est normale : dentifrice pour dents sensibles.\n• Brossage doux 2×/jour, brosse souple.\n• Le résultat se stabilise en quelques jours." },
  { id: 'f-implant', titre: 'Après la pose d\'un implant', texte: "• Ne touchez pas la zone avec la langue ou les doigts.\n• Froid 10 min/heure le premier jour contre l'œdème.\n• Alimentation molle et tiède pendant 3 jours.\n• Brossage doux autour de la zone, bains de bouche prescrits à partir de demain.\n• Pas de sport intense pendant 48 h, pas de tabac.\n• Douleur qui augmente après 3 jours : contactez-nous." },
  { id: 'f-detartrage', titre: 'Après un détartrage', texte: "• Une sensibilité au froid peut durer quelques jours : c'est normal.\n• Évitez thé/café/vin rouge pendant 24 h (les dents sont poreuses juste après).\n• Brossage 2×/jour + brossettes ou fil dentaire chaque soir.\n• Prochain détartrage conseillé dans 6 à 12 mois." },
  { id: 'f-devitalisation', titre: 'Après une dévitalisation', texte: "• Ne mâchez pas du côté traité tant que l'anesthésie n'est pas dissipée.\n• Une gêne à la mastication est possible quelques jours.\n• La dent devra être couronnée rapidement pour éviter la fracture : pensez à prendre ce rendez-vous.\n• Douleur intense ou gonflement : appelez le cabinet." },
  { id: 'f-enfant', titre: 'Conseils enfants & prévention', texte: "• Brossage 2×/jour dès la première dent, supervisé jusqu'à 8 ans.\n• Dentifrice fluoré adapté à l'âge (quantité : un petit pois).\n• Limitez sodas et jus, surtout au coucher — l'eau est la meilleure boisson.\n• Première visite conseillée dès 1 an, puis 1 à 2 fois par an.\n• Le scellement des sillons protège les molaires définitives." },
];

/* ---------- contenu du site (éditable) ---------- */
const CONTENT_INIT = {
  bandeau: { actif: false, texte: 'Le cabinet sera fermé du 5 au 20 août — urgences : appelez le 04 78 52 09 44.' },
  coord: { tel: '+33 4 78 52 09 44', email: 'contact@serene-dentaire.fr', adresse: 'Votre adresse — Votre ville', maps: '' },
  horaires: [
    { jour: 'Lundi', h: '09h00 – 19h00', ferme: false }, { jour: 'Mardi', h: '09h00 – 19h00', ferme: false },
    { jour: 'Mercredi', h: '09h00 – 19h00', ferme: false }, { jour: 'Jeudi', h: '09h00 – 19h00', ferme: false },
    { jour: 'Vendredi', h: '09h00 – 18h00', ferme: false }, { jour: 'Samedi', h: '09h00 – 13h00', ferme: false },
    { jour: 'Dimanche', h: '', ferme: true },
  ],
  textes: { heroTitre: 'Des soins sereins, un sourire apaisé', heroSous: 'Votre cabinet dentaire de confiance', apropos: "Depuis plus de 15 ans, notre équipe vous accueille dans un cadre apaisant et moderne pour prendre soin de votre sourire." },
  chiffres: [ { v: '1200+', l: 'Patients suivis' }, { v: '15+', l: "Années d'expérience" }, { v: '21', l: 'Soins proposés' }, { v: '94%', l: 'Patients satisfaits' } ],
  equipe: [
    { id: 'e1', nom: 'Dr. Julien Fontaine', role: 'Chirurgien-dentiste — Fondateur', tel: '+33 4 78 52 09 45', photo: '' },
    { id: 'e2', nom: 'Dr. Marc Lavigne', role: 'Spécialiste en orthodontie', tel: '+33 4 78 52 09 46', photo: '' },
    { id: 'e3', nom: 'Dr. Thomas Ferrand', role: 'Chirurgien oral', tel: '+33 4 78 52 09 47', photo: '' },
  ],
  reseaux: { instagram: '', facebook: '', linkedin: '' },
  avisGoogle: '',
};

/* ---------- données de démonstration ---------- */
function demoData() {
  const T = todayISO();
  const soins = SOINS_INIT.map(([slug, titre, cat]) => ({ slug, titre, cat, actif: true, prix: '', photo: '' }));
  const rdv = [
    { prenom: 'Camille', nom: 'Roussel', tel: '06 12 45 78 90', email: 'camille.r@mail.fr', soin: 'detartrage', date: T, heure: '09:30', statut: 'confirme', source: 'Google', message: '' },
    { prenom: 'Karim', nom: 'Benali', tel: '07 61 23 88 41', email: 'k.benali@mail.fr', soin: 'urgences', date: T, heure: '11:00', statut: 'nouveau', source: 'Google', message: 'Douleur forte depuis hier soir.' },
    { prenom: 'Marie', nom: 'Dupont', tel: '06 44 09 21 77', email: 'marie.dup@mail.fr', soin: 'blanchiment', date: T, heure: '14:30', statut: 'confirme', source: 'Instagram', message: '' },
    { prenom: 'Lucas', nom: 'Marchand', tel: '06 98 32 10 54', email: 'lucas.m@mail.fr', soin: 'bilan-bucco-dentaire', date: T, heure: '16:00', statut: 'confirme', source: 'Recommandation', message: '' },
    { prenom: 'Sofia', nom: 'Haddad', tel: '07 33 71 26 09', email: 'sofia.h@mail.fr', soin: 'aligneurs', date: addDays(T, 1), heure: '10:00', statut: 'nouveau', source: 'Instagram', message: 'Je voudrais un devis pour des aligneurs.' },
    { prenom: 'Paul', nom: 'Girard', tel: '06 05 66 43 20', email: 'p.girard@mail.fr', soin: 'couronnes', date: addDays(T, 1), heure: '15:30', statut: 'confirme', source: 'Google', message: '' },
    { prenom: 'Emma', nom: 'Lefort', tel: '06 87 54 39 12', email: 'emma.l@mail.fr', soin: 'soins-enfants', date: addDays(T, 2), heure: '09:00', statut: 'nouveau', source: 'Passage devant le cabinet', message: 'Pour ma fille de 7 ans.' },
    { prenom: 'Nadia', nom: 'Cherif', tel: '07 12 90 45 67', email: 'nadia.c@mail.fr', soin: 'implants', date: addDays(T, 3), heure: '11:30', statut: 'confirme', source: 'Recommandation', message: '' },
    { prenom: 'Hugo', nom: 'Blanc', tel: '06 71 20 83 46', email: 'hugo.b@mail.fr', soin: 'extraction', date: addDays(T, -1), heure: '17:00', statut: 'termine', source: 'Google', message: '' },
    { prenom: 'Léa', nom: 'Moreau', tel: '06 29 47 15 83', email: 'lea.m@mail.fr', soin: 'detartrage', date: addDays(T, -2), heure: '10:30', statut: 'termine', source: 'Google', message: '' },
    { prenom: 'Camille', nom: 'Roussel', tel: '06 12 45 78 90', email: 'camille.r@mail.fr', soin: 'traitement-caries', date: addDays(T, -30), heure: '14:00', statut: 'termine', source: 'Google', message: '' },
    { prenom: 'Yanis', nom: 'Meziane', tel: '07 55 08 31 92', email: 'yanis.m@mail.fr', soin: 'devitalisation', date: addDays(T, -3), heure: '16:30', statut: 'annule', source: 'Facebook', message: '' },
    { prenom: 'Chloé', nom: 'Perrin', tel: '06 40 77 58 26', email: 'chloe.p@mail.fr', soin: 'facettes', date: addDays(T, 4), heure: '14:00', statut: 'nouveau', source: 'Instagram', message: '' },
    { prenom: 'Hugo', nom: 'Blanc', tel: '06 71 20 83 46', email: 'hugo.b@mail.fr', soin: 'bilan-bucco-dentaire', date: addDays(T, 6), heure: '09:30', statut: 'confirme', source: 'Google', message: 'Contrôle après extraction.' },
  ].map((r, i) => ({ id: 'r' + i, notes: '', rappel: false, naissance: i === 0 ? T.slice(0, 4) - 34 + '-' + T.slice(5) : '', createdAt: addDays(T, -((i * 13) % 165)), ...r }));
  const messages = [
    { id: 'm1', nom: 'Julie Aubert', email: 'julie.a@mail.fr', tel: '06 51 42 87 30', texte: 'Bonjour, acceptez-vous les nouveaux patients CMU ? Merci.', date: T, lu: false },
    { id: 'm2', nom: 'Marc Roy', email: 'marc.roy@mail.fr', tel: '', texte: 'Est-il possible d\'avoir un devis pour une couronne céramique ?', date: addDays(T, -1), lu: false },
    { id: 'm3', nom: 'Sarah Klein', email: 's.klein@mail.fr', tel: '07 80 14 62 95', texte: 'Merci pour votre accueil hier, très bonne équipe !', date: addDays(T, -2), lu: true },
  ];
  return {
    v: 1, rdv, messages, soins,
    waitlist: [ { id: 'w1', nom: 'Inès Fabre', tel: '06 62 18 47 93', soin: 'detartrage', note: 'Disponible en matinée', date: addDays(T, -1) } ],
    blocked: [], notesPatients: {}, fiches: FICHES_INIT.map((f) => ({ ...f })),
    avantApres: [], content: JSON.parse(JSON.stringify(CONTENT_INIT)),
    settings: { emailNotif: 'contact@serene-dentaire.fr', rappel24: true, bulleWA: true, rgpdMois: 24, siteUrl: 'https://serene-dentaire.fr', derniereSauvegarde: '', publie: '' },
    visits: { mois: 428, prev: 361 },
  };
}

/* ---------- persistance ---------- */
const KEY = 'serene-espace';
let DB = null;
function load() { try { DB = JSON.parse(localStorage.getItem(KEY)); } catch (e) { DB = null; } if (!DB || !DB.v) { DB = demoData(); save(); } }
function save() { localStorage.setItem(KEY, JSON.stringify(DB)); }
const soinTitre = (slug) => (DB.soins.find((s) => s.slug === slug) || {}).titre || slug;

/* ---------- authentification (démo : mdp "serene2026") ---------- */
const PASS_KEY = 'serene-espace-pass';
async function ensurePass() { if (!localStorage.getItem(PASS_KEY)) localStorage.setItem(PASS_KEY, await sha('serene2026')); }
async function tryLogin(p) { return (await sha(p)) === localStorage.getItem(PASS_KEY); }

/* ============================================================
   NAVIGATION / COQUILLE
   ============================================================ */
const VIEWS = [
  ['dashboard', '🏠', 'Tableau de bord'], ['rdv', '📅', 'Rendez-vous'], ['calendrier', '🗓️', 'Calendrier'],
  ['attente', '⏳', "Liste d'attente"], ['messages', '✉️', 'Messages'], ['patients', '👤', 'Patients'],
  ['SEP', '', 'MON SITE'],
  ['contenu', '✏️', 'Contenu du site'], ['fiches', '📄', 'Fiches conseils'], ['avap', '📸', 'Avant / Après'],
  ['comm', '📣', 'Communication'], ['stats', '📊', 'Statistiques'],
  ['SEP2', '', 'RÉGLAGES'],
  ['parametres', '⚙️', 'Paramètres'],
];
let VIEW = 'dashboard';
function goto(v) { VIEW = v; $$('.nav-btn').forEach((b) => b.classList.toggle('on', b.dataset.v === v)); $('#sidebar').classList.remove('open'); $('#scrim').classList.remove('on'); render(); window.scrollTo(0, 0); }

function buildNav() {
  $('#nav').innerHTML = VIEWS.map(([v, ic, l]) => v.startsWith('SEP')
    ? `<div class="nav-sep">${l}</div>`
    : `<button class="nav-btn${v === VIEW ? ' on' : ''}" data-v="${v}"><span class="ic">${ic}</span>${l}<span class="badge" data-badge="${v}" hidden></span></button>`).join('');
  $$('.nav-btn').forEach((b) => (b.onclick = () => goto(b.dataset.v)));
}
function refreshBadges() {
  const nb = DB.rdv.filter((r) => r.statut === 'nouveau').length;
  const nm = DB.messages.filter((m) => !m.lu).length;
  const set = (v, n) => { const el = $(`[data-badge="${v}"]`); if (el) { el.textContent = n; el.hidden = !n; } };
  set('rdv', nb); set('messages', nm);
}

/* ============================================================
   VUES
   ============================================================ */
const TITLES = { dashboard: 'Tableau de bord', rdv: 'Rendez-vous', calendrier: 'Calendrier', attente: "Liste d'attente", messages: 'Messages', patients: 'Patients', contenu: 'Contenu du site', fiches: 'Fiches conseils', avap: 'Avant / Après', comm: 'Communication', stats: 'Statistiques', parametres: 'Paramètres' };
function render() { $('#title').textContent = TITLES[VIEW]; refreshBadges(); RENDER[VIEW](); }

/* ---------- helpers RDV ---------- */
function waLink(tel, txt) { return `https://wa.me/${normTel(tel).replace(/^0/, '33')}?text=${encodeURIComponent(txt)}`; }
function confirmMsg(r) { return `Bonjour ${r.prenom}, votre rendez-vous au cabinet Sérène est confirmé : ${fmtDate(r.date)} à ${r.heure} (${soinTitre(r.soin)}). En cas d'empêchement, merci de nous prévenir au ${DB.content.coord.tel}. À bientôt !`; }
function isDup(r) { return DB.rdv.some((o) => o.id !== r.id && normTel(o.tel) === normTel(r.tel) && o.date === r.date && o.statut !== 'annule'); }
function rdvRow(r, opts = {}) {
  const dup = isDup(r) && r.statut === 'nouveau';
  const notes = r.notes ? `<div class="rdv-notes">📝 ${esc(r.notes)}</div>` : '';
  return `<div class="rdv-row${dup ? ' dup' : ''}" data-id="${r.id}">
    <div class="rdv-when"><span class="d">${esc(fmtShort(r.date))}</span><span class="h">${esc(r.heure)}</span></div>
    <div class="rdv-body">
      <div class="n">${esc(r.prenom)} ${esc(r.nom)} <span class="tag ${r.statut}">${{ nouveau: 'Nouveau', confirme: 'Confirmé', termine: 'Terminé', annule: 'Annulé' }[r.statut]}</span>${r.rappel ? ' <span class="chip">⏰ À rappeler</span>' : ''}${dup ? ' <span class="chip" title="Même téléphone, même date">⚠️ doublon ?</span>' : ''}</div>
      <div class="s">${esc(soinTitre(r.soin))} · ${esc(r.tel)}${r.message ? ` · « ${esc(r.message)} »` : ''}</div>
    </div>
    <div class="rdv-acts">
      <a class="iconbtn" title="Appeler" href="tel:${esc(r.tel)}">📞</a>
      <a class="iconbtn" title="WhatsApp" target="_blank" href="${waLink(r.tel, confirmMsg(r))}">💬</a>
      <select class="btn-sm" data-act="statut" style="border:1px solid var(--line);border-radius:9px;background:#fff;padding:6px">
        ${['nouveau', 'confirme', 'termine', 'annule'].map((s) => `<option value="${s}"${r.statut === s ? ' selected' : ''}>${{ nouveau: 'Nouveau', confirme: 'Confirmé', termine: 'Terminé', annule: 'Annulé' }[s]}</option>`).join('')}
      </select>
      <button class="iconbtn" title="À rappeler" data-act="rappel">${r.rappel ? '🔕' : '⏰'}</button>
      <button class="iconbtn" title="Note interne" data-act="note">📝</button>
      <button class="iconbtn" title="Bloquer ce numéro" data-act="block">🚫</button>
    </div>${notes}</div>`;
}
function bindRdvRows(container) {
  $$('.rdv-row', container).forEach((row) => {
    const r = DB.rdv.find((x) => x.id === row.dataset.id); if (!r) return;
    $('[data-act="statut"]', row).onchange = (e) => {
      const old = r.statut; r.statut = e.target.value; save();
      if (r.statut === 'annule' && DB.waitlist.length) toast(`Créneau libéré ! ${DB.waitlist.length} patient(s) en liste d'attente — pensez à les appeler.`);
      else toast('Statut mis à jour');
      if (old !== r.statut) render();
    };
    $('[data-act="rappel"]', row).onclick = () => { r.rappel = !r.rappel; save(); render(); };
    $('[data-act="note"]', row).onclick = () => { const n = prompt('Note interne (visible uniquement par le cabinet) :', r.notes || ''); if (n !== null) { r.notes = n; save(); render(); } };
    $('[data-act="block"]', row).onclick = () => { if (confirm(`Bloquer le numéro ${r.tel} ? Ses futures demandes seront marquées comme indésirables.`)) { DB.blocked.push(normTel(r.tel)); save(); toast('Numéro bloqué'); } };
  });
}

/* ---------- TABLEAU DE BORD ---------- */
const RENDER = {};
RENDER.dashboard = () => {
  const T = todayISO();
  const week = addDays(T, 7);
  const auj = DB.rdv.filter((r) => r.date === T && r.statut !== 'annule').sort((a, b) => a.heure.localeCompare(b.heure));
  const sem = DB.rdv.filter((r) => r.date >= T && r.date < week && r.statut !== 'annule');
  const nouveaux = DB.rdv.filter((r) => r.statut === 'nouveau');
  const nonLus = DB.messages.filter((m) => !m.lu);
  const rappels = DB.rdv.filter((r) => r.rappel);
  const annivs = DB.rdv.filter((r) => r.naissance && r.naissance.slice(5) === T.slice(5));
  const b = DB.content.bandeau;
  $('#view').innerHTML = `
    ${b.actif ? `<div class="alertline">📣 Bandeau actif sur le site : « ${esc(b.texte)} » <button class="btn btn-sm btn-s" onclick="goto('contenu')">Gérer</button></div>` : ''}
    ${annivs.map((r) => `<div class="alertline">🎂 Aujourd'hui : anniversaire de <b>${esc(r.prenom)} ${esc(r.nom)}</b> <a class="btn btn-sm btn-w" target="_blank" href="${waLink(r.tel, `Joyeux anniversaire ${r.prenom} ! 🎂 Toute l'équipe du cabinet Sérène vous souhaite une excellente journée.`)}">Souhaiter 💬</a></div>`).join('')}
    <div class="grid g4" style="margin-bottom:16px">
      <div class="card kpi hot"><span class="v">${auj.length}</span><span class="l">RDV aujourd'hui</span></div>
      <div class="card kpi"><span class="v">${sem.length}</span><span class="l">RDV cette semaine</span></div>
      <div class="card kpi"><span class="v">${nouveaux.length}</span><span class="l">Demandes à traiter</span></div>
      <div class="card kpi"><span class="v">${nonLus.length}</span><span class="l">Messages non lus</span></div>
    </div>
    <div class="grid g2">
      <div class="card"><h3 style="margin-bottom:12px">📅 Aujourd'hui — ${esc(fmtDate(T))}</h3>
        ${auj.length ? auj.map((r) => rdvRow(r)).join('') : '<div class="empty">Aucun rendez-vous aujourd\'hui.</div>'}
      </div>
      <div>
        <div class="card" style="margin-bottom:14px"><h3 style="margin-bottom:12px">🟡 Nouvelles demandes</h3>
          ${nouveaux.length ? nouveaux.slice(0, 4).map((r) => rdvRow(r)).join('') : '<div class="empty">Rien à traiter, tout est à jour ✅</div>'}
        </div>
        ${rappels.length ? `<div class="card"><h3 style="margin-bottom:12px">⏰ À rappeler</h3>${rappels.map((r) => rdvRow(r)).join('')}</div>` : ''}
      </div>
    </div>`;
  bindRdvRows($('#view'));
};

/* ---------- RENDEZ-VOUS ---------- */
let FILT = { q: '', statut: '', soin: '', date: '' };
RENDER.rdv = () => {
  const list = DB.rdv
    .filter((r) => !FILT.statut || r.statut === FILT.statut)
    .filter((r) => !FILT.soin || r.soin === FILT.soin)
    .filter((r) => !FILT.date || r.date === FILT.date)
    .filter((r) => !FILT.q || `${r.prenom} ${r.nom} ${r.tel}`.toLowerCase().includes(FILT.q.toLowerCase()))
    .sort((a, b) => (a.date + a.heure).localeCompare(b.date + b.heure));
  $('#view').innerHTML = `
    <div class="filters">
      <input placeholder="🔍 Rechercher (nom, téléphone…)" id="f-q" value="${esc(FILT.q)}">
      <select id="f-statut"><option value="">Tous les statuts</option>${['nouveau', 'confirme', 'termine', 'annule'].map((s) => `<option value="${s}"${FILT.statut === s ? ' selected' : ''}>${{ nouveau: 'Nouveau', confirme: 'Confirmé', termine: 'Terminé', annule: 'Annulé' }[s]}</option>`).join('')}</select>
      <select id="f-soin"><option value="">Tous les soins</option>${DB.soins.map((s) => `<option value="${s.slug}"${FILT.soin === s.slug ? ' selected' : ''}>${esc(s.titre)}</option>`).join('')}</select>
      <input type="date" id="f-date" value="${FILT.date}">
      <span class="spacer" style="flex:1"></span>
      <button class="btn btn-s btn-sm" id="b-add">➕ Ajouter</button>
      <button class="btn btn-s btn-sm" id="b-csv">⬇️ Export CSV</button>
      <button class="btn btn-s btn-sm" id="b-print">🖨️ Planning du jour</button>
      <button class="btn btn-s btn-sm" id="b-link">🔗 Lien pré-rempli</button>
    </div>
    ${list.length ? list.map((r) => rdvRow(r)).join('') : '<div class="card empty">Aucun rendez-vous ne correspond à ces filtres.</div>'}
    <div id="printzone" hidden></div>`;
  $('#f-q').oninput = (e) => { FILT.q = e.target.value; render(); };
  $('#f-statut').onchange = (e) => { FILT.statut = e.target.value; render(); };
  $('#f-soin').onchange = (e) => { FILT.soin = e.target.value; render(); };
  $('#f-date').onchange = (e) => { FILT.date = e.target.value; render(); };
  $('#b-csv').onclick = () => {
    const rows = [['Prénom', 'Nom', 'Téléphone', 'E-mail', 'Soin', 'Date', 'Heure', 'Statut', 'Source', 'Message', 'Notes']]
      .concat(list.map((r) => [r.prenom, r.nom, r.tel, r.email, soinTitre(r.soin), r.date, r.heure, r.statut, r.source || '', r.message || '', r.notes || '']));
    dl('rendez-vous-serene.csv', '﻿' + rows.map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(';')).join('\n'), 'text/csv');
    toast('Export CSV téléchargé');
  };
  $('#b-print').onclick = () => {
    const T = todayISO();
    const auj = DB.rdv.filter((r) => r.date === T && r.statut !== 'annule').sort((a, b) => a.heure.localeCompare(b.heure));
    $('#printzone').hidden = false;
    $('#printzone').innerHTML = `<h1 style="font-size:22px">Cabinet Sérène — Planning du ${esc(fmtDate(T))}</h1><hr>
      <table style="width:100%;border-collapse:collapse;font-size:14px">${auj.map((r) => `<tr style="border-bottom:1px solid #ddd"><td style="padding:8px;font-weight:700">${esc(r.heure)}</td><td style="padding:8px">${esc(r.prenom)} ${esc(r.nom)}</td><td style="padding:8px">${esc(soinTitre(r.soin))}</td><td style="padding:8px">${esc(r.tel)}</td><td style="padding:8px">${esc(r.notes || '')}</td></tr>`).join('')}</table>
      <p style="margin-top:14px;font-size:12px;color:#777">Imprimé le ${new Date().toLocaleString('fr-FR')} — Espace Praticien Sérène</p>`;
    window.print(); setTimeout(() => ($('#printzone').hidden = true), 500);
  };
  $('#b-link').onclick = () => {
    const soin = FILT.soin || 'detartrage';
    const url = `${DB.settings.siteUrl.replace(/\/$/, '')}/rendez-vous?soin=${soin}`;
    navigator.clipboard?.writeText(url);
    toast(`Lien copié : ${url}`);
  };
  $('#b-add').onclick = () => {
    const html = `<div class="card" id="addbox" style="margin-bottom:14px"><h3 style="margin-bottom:10px">Nouveau rendez-vous</h3>
      <div class="grid g4">
        <div class="field"><label>Prénom</label><input id="a-prenom"></div>
        <div class="field"><label>Nom</label><input id="a-nom"></div>
        <div class="field"><label>Téléphone</label><input id="a-tel"></div>
        <div class="field"><label>E-mail</label><input id="a-email"></div>
        <div class="field"><label>Soin</label><select id="a-soin">${DB.soins.filter((s) => s.actif).map((s) => `<option value="${s.slug}">${esc(s.titre)}</option>`).join('')}</select></div>
        <div class="field"><label>Date</label><input type="date" id="a-date" value="${todayISO()}"></div>
        <div class="field"><label>Heure</label><input type="time" id="a-heure" value="10:00"></div>
        <div class="field"><label>Source</label><select id="a-source">${SOURCES.map((s) => `<option>${s}</option>`).join('')}</select></div>
      </div>
      <div style="margin-top:12px;display:flex;gap:8px"><button class="btn btn-p btn-sm" id="a-ok">Enregistrer</button><button class="btn btn-s btn-sm" id="a-no">Annuler</button></div></div>`;
    $('#view').insertAdjacentHTML('afterbegin', html);
    $('#a-no').onclick = () => $('#addbox').remove();
    $('#a-ok').onclick = () => {
      const tel = $('#a-tel').value.trim();
      if (!$('#a-prenom').value.trim() || !tel) return toast('Prénom et téléphone obligatoires');
      if (DB.blocked.includes(normTel(tel))) return toast('⚠️ Ce numéro est bloqué (indésirable)');
      DB.rdv.push({ id: uid(), prenom: $('#a-prenom').value.trim(), nom: $('#a-nom').value.trim(), tel, email: $('#a-email').value.trim(), soin: $('#a-soin').value, date: $('#a-date').value, heure: $('#a-heure').value, statut: 'confirme', source: $('#a-source').value, message: '', notes: '', rappel: false, naissance: '', createdAt: todayISO() });
      save(); toast('Rendez-vous ajouté'); render();
    };
  };
  bindRdvRows($('#view'));
};

/* ---------- CALENDRIER ---------- */
let CAL = { y: null, m: null, sel: null };
RENDER.calendrier = () => {
  const now = new Date();
  if (CAL.y === null) { CAL.y = now.getFullYear(); CAL.m = now.getMonth(); }
  const first = new Date(CAL.y, CAL.m, 1);
  const start = (first.getDay() + 6) % 7; // lundi = 0
  const days = new Date(CAL.y, CAL.m + 1, 0).getDate();
  const cells = [];
  for (let i = 0; i < start; i++) cells.push('<div class="cal-cell off"></div>');
  for (let d = 1; d <= days; d++) {
    const iso = `${CAL.y}-${String(CAL.m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const n = DB.rdv.filter((r) => r.date === iso && r.statut !== 'annule').length;
    cells.push(`<div class="cal-cell${iso === todayISO() ? ' today' : ''}${CAL.sel === iso ? ' sel' : ''}" data-d="${iso}">${d}${n ? `<span class="cnt">${n}</span>` : ''}</div>`);
  }
  const selList = CAL.sel ? DB.rdv.filter((r) => r.date === CAL.sel).sort((a, b) => a.heure.localeCompare(b.heure)) : [];
  $('#view').innerHTML = `
    <div class="card">
      <div class="cal-head">
        <button class="iconbtn" id="c-prev">←</button>
        <h3 style="flex:1;text-align:center">${MOIS[CAL.m][0].toUpperCase() + MOIS[CAL.m].slice(1)} ${CAL.y}</h3>
        <button class="iconbtn" id="c-next">→</button>
      </div>
      <div class="cal-grid">${JOURS_C.map((j) => `<div class="cal-dow">${j}</div>`).join('')}${cells.join('')}</div>
    </div>
    ${CAL.sel ? `<div class="card" style="margin-top:14px"><h3 style="margin-bottom:12px">${esc(fmtDate(CAL.sel))}</h3>${selList.length ? selList.map((r) => rdvRow(r)).join('') : '<div class="empty">Aucun rendez-vous ce jour.</div>'}</div>` : '<p class="muted sm" style="margin-top:12px">Cliquez sur un jour pour voir ses rendez-vous.</p>'}`;
  $('#c-prev').onclick = () => { CAL.m--; if (CAL.m < 0) { CAL.m = 11; CAL.y--; } render(); };
  $('#c-next').onclick = () => { CAL.m++; if (CAL.m > 11) { CAL.m = 0; CAL.y++; } render(); };
  $$('.cal-cell[data-d]').forEach((c) => (c.onclick = () => { CAL.sel = c.dataset.d; render(); }));
  bindRdvRows($('#view'));
};

/* ---------- LISTE D'ATTENTE ---------- */
RENDER.attente = () => {
  $('#view').innerHTML = `
    <div class="card" style="margin-bottom:14px"><h3 style="margin-bottom:10px">Ajouter un patient en attente</h3>
      <div class="grid g4">
        <div class="field"><label>Nom complet</label><input id="w-nom"></div>
        <div class="field"><label>Téléphone</label><input id="w-tel"></div>
        <div class="field"><label>Soin souhaité</label><select id="w-soin">${DB.soins.filter((s) => s.actif).map((s) => `<option value="${s.slug}">${esc(s.titre)}</option>`).join('')}</select></div>
        <div class="field"><label>Disponibilités</label><input id="w-note" placeholder="ex : plutôt le matin"></div>
      </div>
      <button class="btn btn-p btn-sm" style="margin-top:12px" id="w-add">Ajouter à la liste</button>
    </div>
    <div class="card"><h3 style="margin-bottom:12px">⏳ En attente d'un créneau (${DB.waitlist.length})</h3>
      ${DB.waitlist.length ? DB.waitlist.map((w) => `<div class="list-row" data-id="${w.id}">
        <div class="avatar">${esc((w.nom || '?')[0])}</div>
        <div class="grow"><b>${esc(w.nom)}</b><div class="sm muted">${esc(soinTitre(w.soin))} · ${esc(w.tel)}${w.note ? ' · ' + esc(w.note) : ''}</div></div>
        <a class="iconbtn" title="Appeler" href="tel:${esc(w.tel)}">📞</a>
        <a class="iconbtn" title="WhatsApp" target="_blank" href="${waLink(w.tel, `Bonjour ${w.nom.split(' ')[0]}, un créneau vient de se libérer au cabinet Sérène pour votre ${soinTitre(w.soin)}. Êtes-vous disponible ? Répondez-nous ou appelez le ${DB.content.coord.tel}.`)}">💬</a>
        <button class="iconbtn" title="Retirer" data-del>✖️</button>
      </div>`).join('') : '<div class="empty">Liste vide. Quand un créneau se libère, contactez ici vos patients en attente.</div>'}
    </div>`;
  $('#w-add').onclick = () => {
    if (!$('#w-nom').value.trim() || !$('#w-tel').value.trim()) return toast('Nom et téléphone obligatoires');
    DB.waitlist.push({ id: uid(), nom: $('#w-nom').value.trim(), tel: $('#w-tel').value.trim(), soin: $('#w-soin').value, note: $('#w-note').value.trim(), date: todayISO() });
    save(); toast('Ajouté à la liste d\'attente'); render();
  };
  $$('[data-del]').forEach((b) => (b.onclick = () => { const id = b.closest('.list-row').dataset.id; DB.waitlist = DB.waitlist.filter((w) => w.id !== id); save(); render(); }));
};

/* ---------- MESSAGES ---------- */
RENDER.messages = () => {
  const list = [...DB.messages].sort((a, b) => b.date.localeCompare(a.date));
  $('#view').innerHTML = list.length ? list.map((m) => `
    <div class="card" style="margin-bottom:10px;${m.lu ? '' : 'border-left:4px solid var(--acc)'}" data-id="${m.id}">
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <b>${esc(m.nom)}</b><span class="muted sm">${esc(fmtDate(m.date))}</span>
        ${m.lu ? '' : '<span class="tag nouveau">Non lu</span>'}
        <span style="flex:1"></span>
        ${m.email ? `<a class="btn btn-s btn-sm" href="mailto:${esc(m.email)}?subject=${encodeURIComponent('Re : votre message au cabinet Sérène')}">↩️ Répondre</a>` : ''}
        ${m.tel ? `<a class="btn btn-w btn-sm" target="_blank" href="${waLink(m.tel, `Bonjour ${m.nom.split(' ')[0]}, merci pour votre message au cabinet Sérène. `)}">💬</a>` : ''}
        <button class="iconbtn" data-lu title="${m.lu ? 'Marquer non lu' : 'Marquer lu'}">${m.lu ? '📩' : '✅'}</button>
        <button class="iconbtn" data-del title="Supprimer">🗑️</button>
      </div>
      <p style="margin-top:8px">${esc(m.texte)}</p>
    </div>`).join('') : '<div class="card empty">Aucun message pour le moment.</div>';
  $$('[data-lu]').forEach((b) => (b.onclick = () => { const m = DB.messages.find((x) => x.id === b.closest('[data-id]').dataset.id); m.lu = !m.lu; save(); render(); }));
  $$('[data-del]').forEach((b) => (b.onclick = () => { if (!confirm('Supprimer ce message ?')) return; DB.messages = DB.messages.filter((x) => x.id !== b.closest('[data-id]').dataset.id); save(); render(); }));
};

/* ---------- PATIENTS ---------- */
RENDER.patients = () => {
  const map = {};
  DB.rdv.forEach((r) => { const k = normTel(r.tel); if (!k) return; (map[k] = map[k] || { tel: r.tel, nom: `${r.prenom} ${r.nom}`, email: r.email, naissance: r.naissance, rdv: [] }).rdv.push(r); if (r.naissance) map[k].naissance = r.naissance; });
  const pats = Object.entries(map).map(([k, p]) => ({ k, ...p, last: p.rdv.sort((a, b) => b.date.localeCompare(a.date))[0] }));
  $('#view').innerHTML = `<p class="muted sm" style="margin-bottom:12px">Fiches générées automatiquement à partir des rendez-vous (regroupées par téléphone). Les notes sont internes au cabinet.</p>` +
    pats.map((p) => `<div class="card" style="margin-bottom:10px" data-k="${p.k}">
      <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <div class="avatar">${esc(p.nom[0] || '?')}</div>
        <div style="flex:1"><b>${esc(p.nom)}</b>
          <div class="sm muted">${esc(p.tel)}${p.email ? ' · ' + esc(p.email) : ''}${p.naissance ? ' · 🎂 ' + esc(p.naissance) : ''}</div>
          <div class="sm">${p.rdv.length} rendez-vous · dernier : <b>${esc(soinTitre(p.last.soin))}</b> (${esc(fmtShort(p.last.date))})</div>
        </div>
        <a class="iconbtn" href="tel:${esc(p.tel)}">📞</a>
        <a class="iconbtn" target="_blank" href="${waLink(p.tel, `Bonjour ${p.nom.split(' ')[0]}, `)}">💬</a>
        <button class="btn btn-s btn-sm" data-note>📝 Note</button>
      </div>
      ${DB.notesPatients[p.k] ? `<div class="rdv-notes" style="margin-top:10px">📝 ${esc(DB.notesPatients[p.k])}</div>` : ''}
    </div>`).join('');
  $$('[data-note]').forEach((b) => (b.onclick = () => { const k = b.closest('[data-k]').dataset.k; const n = prompt('Note interne sur ce patient :', DB.notesPatients[k] || ''); if (n !== null) { DB.notesPatients[k] = n; save(); render(); } }));
};

/* ---------- CONTENU DU SITE ---------- */
let CTAB = 'bandeau';
RENDER.contenu = () => {
  const C = DB.content;
  const tabs = [['bandeau', '📣 Bandeau'], ['coord', '📍 Coordonnées'], ['horaires', '🕘 Horaires'], ['textes', '✏️ Textes'], ['chiffres', '🔢 Chiffres clés'], ['equipe', '👥 Équipe'], ['soins', '🦷 Soins'], ['reseaux', '🌐 Réseaux']];
  let body = '';
  if (CTAB === 'bandeau') body = `
    <div class="field" style="max-width:640px"><label>Message affiché en haut du site (fermeture, congés, urgence…)</label>
      <textarea id="c-bandeau" rows="2">${esc(C.bandeau.texte)}</textarea></div>
    <label class="chip" style="margin-top:12px;cursor:pointer"><span class="switch"><input type="checkbox" id="c-bandeau-on"${C.bandeau.actif ? ' checked' : ''}><span class="tr"></span></span> Bandeau ${C.bandeau.actif ? 'ACTIF sur le site' : 'désactivé'}</label>`;
  if (CTAB === 'coord') body = `<div class="grid g2" style="max-width:760px">
    <div class="field"><label>Téléphone</label><input id="c-tel" value="${esc(C.coord.tel)}"></div>
    <div class="field"><label>E-mail</label><input id="c-email" value="${esc(C.coord.email)}"></div>
    <div class="field" style="grid-column:1/-1"><label>Adresse</label><input id="c-adresse" value="${esc(C.coord.adresse)}"></div>
    <div class="field" style="grid-column:1/-1"><label>Lien Google Maps</label><input id="c-maps" value="${esc(C.coord.maps)}" placeholder="https://maps.google.com/…"></div></div>`;
  if (CTAB === 'horaires') body = C.horaires.map((h, i) => `<div class="h-row" style="max-width:560px">
      <b>${esc(h.jour)}</b>
      <input data-h="${i}" value="${esc(h.h)}" ${h.ferme ? 'disabled placeholder="Fermé"' : ''} style="border:1px solid var(--line);border-radius:10px;padding:8px 12px">
      <label class="chip" style="cursor:pointer"><span class="switch"><input type="checkbox" data-f="${i}"${h.ferme ? ' checked' : ''}><span class="tr"></span></span> Fermé</label>
    </div>`).join('');
  if (CTAB === 'textes') body = `<div class="grid" style="max-width:640px">
    <div class="field"><label>Titre principal (hero)</label><input id="c-hero" value="${esc(C.textes.heroTitre)}"></div>
    <div class="field"><label>Sous-titre</label><input id="c-sous" value="${esc(C.textes.heroSous)}"></div>
    <div class="field"><label>Texte « À propos »</label><textarea id="c-apropos" rows="4">${esc(C.textes.apropos)}</textarea></div></div>`;
  if (CTAB === 'chiffres') body = `<div class="grid g4">` + C.chiffres.map((c, i) => `
    <div class="card"><div class="field"><label>Valeur</label><input data-cv="${i}" value="${esc(c.v)}"></div>
    <div class="field" style="margin-top:8px"><label>Libellé</label><input data-cl="${i}" value="${esc(c.l)}"></div></div>`).join('') + '</div>';
  if (CTAB === 'equipe') body = C.equipe.map((e) => `<div class="list-row" data-id="${e.id}">
      <div class="avatar">${e.photo ? `<img src="${e.photo}" alt="">` : esc(e.nom.replace('Dr. ', '')[0])}</div>
      <div class="grow"><input data-en value="${esc(e.nom)}" style="border:1px solid var(--line);border-radius:8px;padding:6px 10px;width:100%;margin-bottom:4px">
        <input data-er value="${esc(e.role)}" style="border:1px solid var(--line);border-radius:8px;padding:6px 10px;width:100%"></div>
      <label class="btn btn-s btn-sm">📷<input type="file" accept="image/*" data-ep hidden></label>
      <button class="iconbtn" data-edel title="Retirer">✖️</button>
    </div>`).join('') + `<button class="btn btn-s btn-sm" id="e-add">➕ Ajouter un praticien</button>`;
  if (CTAB === 'soins') body = `<p class="muted sm" style="margin-bottom:12px">Activez/désactivez un soin (il disparaît du site et du formulaire), modifiez son intitulé ou ajoutez un tarif indicatif.</p>` +
    DB.soins.map((s, i) => `<div class="list-row${s.actif ? '' : ' soin-off'}">
      <span class="switch"><input type="checkbox" data-sa="${i}"${s.actif ? ' checked' : ''}><span class="tr"></span></span>
      <div class="grow"><input data-st="${i}" value="${esc(s.titre)}" style="border:1px solid var(--line);border-radius:8px;padding:6px 10px;width:100%"><span class="xs muted">${CATS[s.cat]}</span></div>
      <input data-sp="${i}" value="${esc(s.prix)}" placeholder="Tarif (opt.)" style="border:1px solid var(--line);border-radius:8px;padding:6px 10px;width:110px">
    </div>`).join('');
  if (CTAB === 'reseaux') body = `<div class="grid" style="max-width:560px">
    ${['instagram', 'facebook', 'linkedin'].map((k) => `<div class="field"><label>${k[0].toUpperCase() + k.slice(1)}</label><input data-rs="${k}" value="${esc(C.reseaux[k])}" placeholder="https://…"></div>`).join('')}
    <div class="field"><label>Lien « Avis Google » du cabinet</label><input id="c-avis" value="${esc(C.avisGoogle)}" placeholder="https://g.page/r/…/review"></div></div>`;
  $('#view').innerHTML = `
    <div class="tabs">${tabs.map(([k, l]) => `<button class="tab${CTAB === k ? ' on' : ''}" data-t="${k}">${l}</button>`).join('')}</div>
    <div class="card">${body}</div>
    <div style="margin-top:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <button class="btn btn-p" id="c-save">💾 Enregistrer</button>
      <button class="btn btn-d" id="c-pub">🚀 Publier sur le site</button>
      <span class="muted sm">${DB.settings.publie ? 'Dernière publication : ' + esc(DB.settings.publie) : 'Jamais publié'}</span>
    </div>`;
  $$('.tab').forEach((t) => (t.onclick = () => { CTAB = t.dataset.t; render(); }));
  const collect = () => {
    if (CTAB === 'bandeau') { C.bandeau.texte = $('#c-bandeau').value; C.bandeau.actif = $('#c-bandeau-on').checked; }
    if (CTAB === 'coord') { C.coord.tel = $('#c-tel').value; C.coord.email = $('#c-email').value; C.coord.adresse = $('#c-adresse').value; C.coord.maps = $('#c-maps').value; }
    if (CTAB === 'horaires') { $$('[data-h]').forEach((el) => (C.horaires[+el.dataset.h].h = el.value)); $$('[data-f]').forEach((el) => (C.horaires[+el.dataset.f].ferme = el.checked)); }
    if (CTAB === 'textes') { C.textes.heroTitre = $('#c-hero').value; C.textes.heroSous = $('#c-sous').value; C.textes.apropos = $('#c-apropos').value; }
    if (CTAB === 'chiffres') { $$('[data-cv]').forEach((el) => (C.chiffres[+el.dataset.cv].v = el.value)); $$('[data-cl]').forEach((el) => (C.chiffres[+el.dataset.cl].l = el.value)); }
    if (CTAB === 'equipe') $$('.list-row[data-id]').forEach((row) => { const e = C.equipe.find((x) => x.id === row.dataset.id); if (e) { e.nom = $('[data-en]', row).value; e.role = $('[data-er]', row).value; } });
    if (CTAB === 'soins') { $$('[data-sa]').forEach((el) => (DB.soins[+el.dataset.sa].actif = el.checked)); $$('[data-st]').forEach((el) => (DB.soins[+el.dataset.st].titre = el.value)); $$('[data-sp]').forEach((el) => (DB.soins[+el.dataset.sp].prix = el.value)); }
    if (CTAB === 'reseaux') { $$('[data-rs]').forEach((el) => (C.reseaux[el.dataset.rs] = el.value)); C.avisGoogle = $('#c-avis').value; }
  };
  $('#c-save').onclick = () => { collect(); save(); toast('Modifications enregistrées ✅'); render(); };
  $('#c-pub').onclick = () => { collect(); DB.settings.publie = new Date().toLocaleString('fr-FR'); save(); toast('Contenu publié — le site sera mis à jour 🚀'); render(); };
  if (CTAB === 'horaires') $$('[data-f]').forEach((el) => (el.onchange = () => { collect(); save(); render(); }));
  if (CTAB === 'soins') $$('[data-sa]').forEach((el) => (el.onchange = () => { collect(); save(); render(); }));
  if (CTAB === 'bandeau') $('#c-bandeau-on').onchange = () => { collect(); save(); render(); };
  if (CTAB === 'equipe') {
    $('#e-add').onclick = () => { C.equipe.push({ id: uid(), nom: 'Dr. Nouveau Praticien', role: 'Chirurgien-dentiste', tel: '', photo: '' }); save(); render(); };
    $$('[data-edel]').forEach((b) => (b.onclick = () => { const id = b.closest('[data-id]').dataset.id; C.equipe = C.equipe.filter((e) => e.id !== id); save(); render(); }));
    $$('[data-ep]').forEach((inp) => (inp.onchange = () => { const f = inp.files[0]; if (!f) return; const rd = new FileReader(); rd.onload = () => { const e = C.equipe.find((x) => x.id === inp.closest('[data-id]').dataset.id); e.photo = rd.result; save(); render(); }; rd.readAsDataURL(f); }));
  }
};

/* ---------- FICHES CONSEILS ---------- */
RENDER.fiches = () => {
  $('#view').innerHTML = `<p class="muted sm" style="margin-bottom:12px">Fiches prêtes à envoyer au patient après son soin (WhatsApp ou impression). Vous pouvez adapter chaque texte.</p>` +
    DB.fiches.map((f) => `<div class="card fiche" style="margin-bottom:12px" data-id="${f.id}">
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:8px">
        <h3 style="flex:1">${esc(f.titre)}</h3>
        <button class="btn btn-w btn-sm" data-wa>💬 Envoyer (WhatsApp)</button>
        <button class="btn btn-s btn-sm" data-copy>📋 Copier</button>
        <button class="btn btn-s btn-sm" data-print>🖨️ Imprimer</button>
      </div>
      <textarea data-txt rows="6" style="width:100%;border:1px solid var(--line);border-radius:11px;padding:10px">${esc(f.texte)}</textarea>
    </div>`).join('');
  $$('.fiche').forEach((card) => {
    const f = DB.fiches.find((x) => x.id === card.dataset.id);
    $('[data-txt]', card).onchange = (e) => { f.texte = e.target.value; save(); toast('Fiche mise à jour'); };
    $('[data-copy]', card).onclick = () => { navigator.clipboard?.writeText(`${f.titre} — Cabinet Sérène\n\n${f.texte}`); toast('Texte copié'); };
    $('[data-wa]', card).onclick = () => { const tel = prompt('Numéro WhatsApp du patient (ex : 06 12 34 56 78) :'); if (tel) window.open(waLink(tel, `🦷 ${f.titre} — Cabinet Sérène\n\n${f.texte}\n\nUne question ? Appelez-nous : ${DB.content.coord.tel}`)); };
    $('[data-print]', card).onclick = () => {
      $('#printzone2').hidden = false;
      $('#printzone2').innerHTML = `<div id="printzone"><h1 style="font-size:22px">🦷 ${esc(f.titre)}</h1><p class="muted">Cabinet Sérène — ${esc(DB.content.coord.tel)}</p><hr><pre style="font-family:inherit;white-space:pre-wrap;font-size:15px;line-height:1.8">${esc(f.texte)}</pre></div>`;
      window.print(); setTimeout(() => ($('#printzone2').hidden = true), 500);
    };
  });
  $('#view').insertAdjacentHTML('beforeend', '<div id="printzone2" hidden></div>');
};

/* ---------- AVANT / APRÈS ---------- */
RENDER.avap = () => {
  $('#view').innerHTML = `
    <p class="muted sm" style="margin-bottom:12px">Ajoutez vos propres cas cliniques (avec l'accord écrit du patient) : ils alimentent le comparateur interactif du site.</p>
    <div class="card" style="margin-bottom:14px"><h3 style="margin-bottom:10px">Nouveau cas</h3>
      <div class="grid g3">
        <div class="field"><label>Titre (ex : Blanchiment)</label><input id="ba-titre"></div>
        <div class="field"><label>Photo AVANT</label><input type="file" id="ba-av" accept="image/*"></div>
        <div class="field"><label>Photo APRÈS</label><input type="file" id="ba-ap" accept="image/*"></div>
      </div>
      <button class="btn btn-p btn-sm" style="margin-top:12px" id="ba-add">Ajouter le cas</button>
    </div>
    <div class="grid g2">${DB.avantApres.map((c) => `
      <div class="card" data-id="${c.id}"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px"><b>${esc(c.titre)}</b><button class="iconbtn" data-del>🗑️</button></div>
        <div class="ba-slider" data-ba>
          <img src="${c.apres}" alt="Après">
          <div class="ba-clip" style="width:50%"><img src="${c.avant}" alt="Avant" style="width:${'100%'};max-width:none"></div>
          <div class="ba-bar" style="left:50%"></div>
          <input type="range" class="ba-range" min="0" max="100" value="50">
        </div><p class="xs muted" style="margin-top:6px">Glissez pour comparer — c'est exactement l'effet affiché sur le site.</p></div>`).join('') || '<div class="card empty">Aucun cas pour le moment. Ajoutez votre premier avant/après ci-dessus.</div>'}
    </div>`;
  const rd = (f) => new Promise((res) => { const r = new FileReader(); r.onload = () => res(r.result); r.readAsDataURL(f); });
  $('#ba-add').onclick = async () => {
    const t = $('#ba-titre').value.trim(), a = $('#ba-av').files[0], p = $('#ba-ap').files[0];
    if (!t || !a || !p) return toast('Titre + les 2 photos sont obligatoires');
    DB.avantApres.push({ id: uid(), titre: t, avant: await rd(a), apres: await rd(p) });
    save(); toast('Cas ajouté ✅'); render();
  };
  $$('[data-del]').forEach((b) => (b.onclick = () => { const id = b.closest('[data-id]').dataset.id; DB.avantApres = DB.avantApres.filter((c) => c.id !== id); save(); render(); }));
  $$('[data-ba]').forEach((sl) => {
    const range = $('.ba-range', sl), clip = $('.ba-clip', sl), bar = $('.ba-bar', sl);
    const imgA = $('img', clip);
    const fit = () => { imgA.style.width = sl.clientWidth + 'px'; };
    fit(); new ResizeObserver(fit).observe(sl);
    range.oninput = () => { clip.style.width = range.value + '%'; bar.style.left = range.value + '%'; };
  });
};

/* ---------- COMMUNICATION (posts, QR, avis) ---------- */
RENDER.comm = () => {
  $('#view').innerHTML = `
    <div class="grid g2">
      <div class="card"><h3 style="margin-bottom:4px">📣 Générateur de publication</h3>
        <p class="muted sm" style="margin-bottom:12px">Créez une image aux couleurs du cabinet pour Instagram/Facebook.</p>
        <div class="field"><label>Titre</label><input id="p-titre" value="Nouveau : blanchiment dentaire au cabinet"></div>
        <div class="field" style="margin-top:8px"><label>Sous-texte</label><input id="p-sous" value="Prenez rendez-vous en ligne — serene-dentaire.fr"></div>
        <div style="display:flex;gap:8px;margin:12px 0">
          <button class="btn btn-p btn-sm" data-fmt="carre">Carré (feed)</button>
          <button class="btn btn-s btn-sm" data-fmt="story">Story (9:16)</button>
        </div>
        <canvas id="p-canvas" class="post-prev"></canvas>
        <div style="margin-top:10px"><button class="btn btn-d btn-sm" id="p-dl">⬇️ Télécharger l'image</button></div>
      </div>
      <div style="display:grid;gap:14px;align-content:start">
        <div class="card"><h3 style="margin-bottom:4px">🪧 Affiche QR — prise de rendez-vous</h3>
          <p class="muted sm" style="margin-bottom:12px">À imprimer et poser à l'accueil : le patient scanne et réserve.</p>
          <div class="qr-poster" id="qr-poster">
            <div style="font-size:11px;letter-spacing:.2em;color:var(--muted)">CABINET DENTAIRE</div>
            <h3>Sérène</h3>
            <p class="sm muted">Prenez votre prochain rendez-vous en ligne</p>
            <div class="qr-box" id="qr-box"></div>
            <p class="sm"><b>Scannez-moi</b> 📱</p>
          </div>
          <div style="text-align:center;margin-top:12px"><button class="btn btn-s btn-sm" id="qr-print">🖨️ Imprimer l'affiche</button></div>
        </div>
        <div class="card"><h3 style="margin-bottom:4px">⭐ Collecte d'avis Google</h3>
          <p class="muted sm" style="margin-bottom:10px">Envoyez le lien d'avis à un patient satisfait après son soin.</p>
          ${DB.content.avisGoogle ? `<button class="btn btn-w btn-sm" id="avis-wa">💬 Envoyer le lien par WhatsApp</button>` : `<p class="sm">⚠️ Renseignez d'abord votre lien d'avis dans <a href="#" onclick="CTAB='reseaux';goto('contenu');return false">Contenu → Réseaux</a>.</p>`}
        </div>
      </div>
    </div><div id="printzone3" hidden></div>`;
  /* --- posts canvas --- */
  const cv = $('#p-canvas'); let FMT = 'carre';
  function drawPost() {
    const W = 1080, H = FMT === 'carre' ? 1080 : 1920;
    cv.width = W; cv.height = H;
    const g = cv.getContext('2d');
    g.fillStyle = '#faf7f5'; g.fillRect(0, 0, W, H);
    g.fillStyle = '#963f36'; g.beginPath(); g.arc(W - 60, -80, 340, 0, 7); g.fill();
    g.fillStyle = '#6e8568'; g.globalAlpha = .16; g.beginPath(); g.arc(-40, H + 60, 380, 0, 7); g.fill(); g.globalAlpha = 1;
    g.fillStyle = '#030303'; g.font = '700 34px system-ui'; g.fillText('CABINET DENTAIRE', 70, FMT === 'carre' ? 150 : 260);
    g.fillStyle = '#963f36'; g.font = '800 92px system-ui'; g.fillText('Sérène', 70, FMT === 'carre' ? 250 : 380);
    const titre = $('#p-titre').value, sous = $('#p-sous').value;
    g.fillStyle = '#030303'; g.font = '700 58px system-ui';
    const words = titre.split(' '); let line = '', y = FMT === 'carre' ? 520 : 860; const lines = [];
    words.forEach((w) => { if (g.measureText(line + ' ' + w).width > W - 160) { lines.push(line); line = w; } else line = line ? line + ' ' + w : w; });
    lines.push(line); lines.forEach((l) => { g.fillText(l, 70, y); y += 76; });
    g.fillStyle = '#6b6560'; g.font = '400 40px system-ui'; g.fillText(sous, 70, y + 30);
    g.fillStyle = '#963f36'; g.beginPath(); g.roundRect(70, H - 190, 640, 96, 48); g.fill();
    g.fillStyle = '#faf7f5'; g.font = '700 40px system-ui'; g.fillText('📅  Prendre rendez-vous', 116, H - 128);
  }
  drawPost();
  $$('[data-fmt]').forEach((b) => (b.onclick = () => { FMT = b.dataset.fmt; drawPost(); }));
  $('#p-titre').oninput = drawPost; $('#p-sous').oninput = drawPost;
  $('#p-dl').onclick = () => { const a = document.createElement('a'); a.download = `post-serene-${FMT}.png`; a.href = cv.toDataURL('image/png'); a.click(); toast('Image téléchargée'); };
  /* --- QR --- */
  try {
    const url = `${DB.settings.siteUrl.replace(/\/$/, '')}/rendez-vous`;
    const qr = qrcode(0, 'M'); qr.addData(url); qr.make();
    $('#qr-box').innerHTML = qr.createImgTag(5, 8);
  } catch (e) { $('#qr-box').innerHTML = '<span class="muted sm">QR indisponible</span>'; }
  $('#qr-print').onclick = () => {
    $('#printzone3').hidden = false;
    $('#printzone3').innerHTML = `<div id="printzone" style="text-align:center;padding-top:80px">${$('#qr-poster').outerHTML}</div>`;
    window.print(); setTimeout(() => ($('#printzone3').hidden = true), 500);
  };
  const avisBtn = $('#avis-wa');
  if (avisBtn) avisBtn.onclick = () => { const tel = prompt('Numéro WhatsApp du patient :'); if (tel) window.open(waLink(tel, `Merci pour votre visite au cabinet Sérène ! Si vous avez 30 secondes, votre avis nous aide beaucoup 🙏 ${DB.content.avisGoogle}`)); };
};

/* ---------- STATISTIQUES ---------- */
RENDER.stats = () => {
  const T = todayISO();
  /* rdv par mois (6 derniers mois) */
  const months = []; const now = new Date();
  for (let i = 5; i >= 0; i--) { const d = new Date(now.getFullYear(), now.getMonth() - i, 1); months.push({ k: d.toISOString().slice(0, 7), l: MOIS[d.getMonth()].slice(0, 4) + '.' }); }
  months.forEach((m) => (m.n = DB.rdv.filter((r) => (r.createdAt || r.date).slice(0, 7) === m.k).length));
  /* top soins */
  const bySoin = {}; DB.rdv.forEach((r) => (bySoin[r.soin] = (bySoin[r.soin] || 0) + 1));
  const top = Object.entries(bySoin).sort((a, b) => b[1] - a[1]).slice(0, 6);
  const maxT = Math.max(...top.map(([, n]) => n), 1);
  /* sources */
  const bySrc = {}; DB.rdv.forEach((r) => { const s = r.source || 'Autre'; bySrc[s] = (bySrc[s] || 0) + 1; });
  const srcs = Object.entries(bySrc).sort((a, b) => b[1] - a[1]);
  const maxS = Math.max(...srcs.map(([, n]) => n), 1);
  /* heatmap jour × heure */
  const heat = Array.from({ length: 7 }, () => Array(12).fill(0));
  DB.rdv.forEach((r) => { if (r.statut === 'annule') return; const d = (new Date(r.date + 'T12:00').getDay() + 6) % 7; const h = parseInt(r.heure) - 8; if (h >= 0 && h < 12) heat[d][h]++; });
  const maxH = Math.max(...heat.flat(), 1);
  /* taux de présence */
  const done = DB.rdv.filter((r) => r.statut === 'termine').length;
  const cancel = DB.rdv.filter((r) => r.statut === 'annule').length;
  const taux = done + cancel ? Math.round((done / (done + cancel)) * 100) : 100;
  $('#view').innerHTML = `
    <div class="grid g4" style="margin-bottom:16px">
      <div class="card kpi"><span class="v">${DB.rdv.length}</span><span class="l">Demandes reçues (total)</span></div>
      <div class="card kpi"><span class="v">${taux}%</span><span class="l">Taux de présence</span></div>
      <div class="card kpi"><span class="v">${DB.visits.mois}</span><span class="l">Visites du site ce mois</span></div>
      <div class="card kpi"><span class="v">+${Math.max(0, Math.round(((DB.visits.mois - DB.visits.prev) / Math.max(DB.visits.prev, 1)) * 100))}%</span><span class="l">vs mois précédent</span></div>
    </div>
    <div class="grid g2">
      <div class="card"><h3 style="margin-bottom:14px">📈 Demandes de RDV par mois</h3><canvas id="s-chart" class="chart" width="640" height="300"></canvas></div>
      <div class="card"><h3 style="margin-bottom:14px">🦷 Soins les plus demandés</h3>
        ${top.map(([slug, n]) => `<div class="bar-row"><span>${esc(soinTitre(slug))}</span><div class="bar-tr"><div class="bar-fl" style="width:${(n / maxT) * 100}%"></div></div><b>${n}</b></div>`).join('')}
      </div>
      <div class="card"><h3 style="margin-bottom:14px">🔥 Heures les plus demandées</h3>
        <div class="heat">
          <div></div>${Array.from({ length: 12 }, (_, i) => `<div class="hl" style="justify-content:center">${8 + i}h</div>`).join('')}
          ${heat.map((row, d) => `<div class="hl">${JOURS_C[d]}</div>` + row.map((n) => `<div class="hc" style="background:${n ? `rgba(150,63,54,${0.15 + 0.85 * (n / maxH)})` : 'var(--cream2)'}" title="${n} RDV"></div>`).join('')).join('')}
        </div>
      </div>
      <div class="card"><h3 style="margin-bottom:14px">🧭 Comment les patients nous trouvent</h3>
        ${srcs.map(([s, n]) => `<div class="bar-row"><span>${esc(s)}</span><div class="bar-tr"><div class="bar-fl sage" style="width:${(n / maxS) * 100}%"></div></div><b>${n}</b></div>`).join('')}
      </div>
    </div>`;
  /* graphe canvas */
  const cv = $('#s-chart'), g = cv.getContext('2d');
  const maxM = Math.max(...months.map((m) => m.n), 1);
  g.clearRect(0, 0, 640, 300);
  months.forEach((m, i) => {
    const h = (m.n / maxM) * 210, x = 40 + i * 100;
    g.fillStyle = '#f2eeec'; g.beginPath(); g.roundRect(x, 30, 56, 220, 10); g.fill();
    g.fillStyle = i === months.length - 1 ? '#963f36' : '#6e8568';
    g.beginPath(); g.roundRect(x, 250 - h, 56, h, 10); g.fill();
    g.fillStyle = '#030303'; g.font = '700 15px system-ui'; g.textAlign = 'center';
    g.fillText(m.n, x + 28, 240 - h);
    g.fillStyle = '#6b6560'; g.font = '13px system-ui'; g.fillText(m.l, x + 28, 285);
  });
};

/* ---------- PARAMÈTRES ---------- */
RENDER.parametres = () => {
  const S = DB.settings;
  $('#view').innerHTML = `
    <div class="grid g2">
      <div class="card"><h3 style="margin-bottom:12px">🔐 Compte du cabinet</h3>
        <p class="muted sm" style="margin-bottom:12px">Un seul compte partagé praticien + secrétaire.</p>
        <div class="field"><label>Nouveau mot de passe</label><input type="password" id="s-p1" placeholder="••••••••"></div>
        <div class="field" style="margin-top:8px"><label>Confirmer</label><input type="password" id="s-p2" placeholder="••••••••"></div>
        <button class="btn btn-p btn-sm" style="margin-top:12px" id="s-pass">Changer le mot de passe</button>
      </div>
      <div class="card"><h3 style="margin-bottom:12px">🔔 Notifications & rappels</h3>
        <div class="field"><label>E-mail de réception (RDV & messages)</label><input id="s-email" value="${esc(S.emailNotif)}"></div>
        <label class="chip" style="margin-top:12px;cursor:pointer"><span class="switch"><input type="checkbox" id="s-r24"${S.rappel24 ? ' checked' : ''}><span class="tr"></span></span> Rappel automatique au patient 24 h avant *</label>
        <label class="chip" style="margin-top:8px;cursor:pointer"><span class="switch"><input type="checkbox" id="s-wa"${S.bulleWA ? ' checked' : ''}><span class="tr"></span></span> Bulle WhatsApp sur le site *</label>
        <p class="xs muted" style="margin-top:10px">* Actifs lorsque le site est sur son hébergement définitif.</p>
      </div>
      <div class="card"><h3 style="margin-bottom:12px">🛡️ RGPD</h3>
        <div class="field"><label>Suppression automatique des demandes après</label>
          <select id="s-rgpd">${[12, 24, 36].map((m) => `<option value="${m}"${S.rgpdMois == m ? ' selected' : ''}>${m} mois</option>`).join('')}</select></div>
        <button class="btn btn-s btn-sm" style="margin-top:12px" id="s-purge">🧹 Purger maintenant les données anciennes</button>
        <p class="xs muted" style="margin-top:10px">Conformité : les données patients ne sont conservées que le temps nécessaire.</p>
      </div>
      <div class="card"><h3 style="margin-bottom:12px">💾 Sauvegarde & agenda</h3>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button class="btn btn-s btn-sm" id="s-backup">⬇️ Télécharger une sauvegarde</button>
          <label class="btn btn-s btn-sm">📤 Restaurer<input type="file" id="s-restore" accept=".json" hidden></label>
          <button class="btn btn-s btn-sm" id="s-ical">📅 Exporter vers mon agenda (.ics)</button>
        </div>
        <p class="xs muted" style="margin-top:10px">Le fichier .ics ajoute vos RDV confirmés dans l'agenda de votre téléphone (Apple/Google).</p>
        ${S.derniereSauvegarde ? `<p class="xs muted">Dernière sauvegarde : ${esc(S.derniereSauvegarde)}</p>` : ''}
      </div>
      <div class="card"><h3 style="margin-bottom:12px">📟 Salle d'attente</h3>
        <p class="muted sm" style="margin-bottom:10px">Affichage plein écran pour une tablette à l'accueil : bienvenue, soins et QR de réservation.</p>
        <button class="btn btn-d btn-sm" id="s-tablet">▶️ Lancer le mode salle d'attente</button>
      </div>
      <div class="card"><h3 style="margin-bottom:12px">📱 Application</h3>
        <p class="muted sm" style="margin-bottom:10px">Installez cet espace comme application sur votre téléphone (icône + notifications).</p>
        <button class="btn btn-p btn-sm" id="s-pwa">📲 Installer l'application</button>
        <p class="xs muted" style="margin-top:10px">Disponible une fois le site hébergé (Android/iOS via « Ajouter à l'écran d'accueil »).</p>
      </div>
      <div class="card" style="grid-column:1/-1"><h3 style="margin-bottom:8px">🧪 Données de démonstration</h3>
        <button class="btn btn-danger btn-sm" id="s-reset">♻️ Réinitialiser la démo</button>
      </div>
    </div>`;
  $('#s-pass').onclick = async () => {
    const a = $('#s-p1').value, b = $('#s-p2').value;
    if (a.length < 8) return toast('8 caractères minimum');
    if (a !== b) return toast('Les deux mots de passe ne correspondent pas');
    localStorage.setItem(PASS_KEY, await sha(a)); toast('Mot de passe modifié ✅');
  };
  $('#s-email').onchange = (e) => { DB.settings.emailNotif = e.target.value; save(); toast('E-mail mis à jour'); };
  $('#s-r24').onchange = (e) => { DB.settings.rappel24 = e.target.checked; save(); };
  $('#s-wa').onchange = (e) => { DB.settings.bulleWA = e.target.checked; save(); };
  $('#s-rgpd').onchange = (e) => { DB.settings.rgpdMois = +e.target.value; save(); };
  $('#s-purge').onclick = () => {
    const lim = addDays(todayISO(), -DB.settings.rgpdMois * 30);
    const n = DB.rdv.length;
    DB.rdv = DB.rdv.filter((r) => r.date >= lim);
    save(); toast(`${n - DB.rdv.length} ancienne(s) demande(s) supprimée(s)`); render();
  };
  $('#s-backup').onclick = () => { DB.settings.derniereSauvegarde = new Date().toLocaleString('fr-FR'); save(); dl('sauvegarde-serene.json', JSON.stringify(DB, null, 2), 'application/json'); toast('Sauvegarde téléchargée'); };
  $('#s-restore').onchange = (e) => { const f = e.target.files[0]; if (!f) return; const r = new FileReader(); r.onload = () => { try { DB = JSON.parse(r.result); save(); toast('Données restaurées ✅'); render(); } catch { toast('Fichier invalide'); } }; r.readAsText(f); };
  $('#s-ical').onclick = () => {
    const ev = DB.rdv.filter((r) => r.statut === 'confirme').map((r) => {
      const dt = r.date.replace(/-/g, '') + 'T' + r.heure.replace(':', '') + '00';
      return `BEGIN:VEVENT\nUID:${r.id}@serene\nDTSTART:${dt}\nSUMMARY:${r.prenom} ${r.nom} — ${soinTitre(r.soin)}\nDESCRIPTION:Tél ${r.tel}\nEND:VEVENT`;
    }).join('\n');
    dl('agenda-serene.ics', `BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//Serene//Espace Praticien//FR\n${ev}\nEND:VCALENDAR`, 'text/calendar');
    toast('Agenda exporté (.ics)');
  };
  $('#s-tablet').onclick = openTablet;
  $('#s-pwa').onclick = () => {
    if (window._pwaPrompt) { window._pwaPrompt.prompt(); }
    else toast('Sur votre téléphone : menu du navigateur → « Ajouter à l\'écran d\'accueil »');
  };
  $('#s-reset').onclick = () => { if (confirm('Remettre les données de démonstration ? (les modifications seront perdues)')) { DB = demoData(); save(); toast('Démo réinitialisée'); render(); } };
};

/* ---------- MODE TABLETTE ---------- */
function openTablet() {
  const el = document.createElement('div');
  el.className = 'tablet';
  const soins = DB.soins.filter((s) => s.actif);
  el.innerHTML = `
    <button class="close-t">✖ Quitter</button>
    <div style="font-size:13px;letter-spacing:.25em;color:#bdb5ae">CABINET DENTAIRE</div>
    <h1>Bienvenue chez <span style="color:#b9564c">Sérène</span></h1>
    <div class="soin-cyc" id="t-cyc">${esc(soins[0]?.titre || '')}</div>
    <div class="qr-white" id="t-qr"></div>
    <p style="color:#bdb5ae">Scannez pour prendre votre prochain rendez-vous</p>`;
  document.body.appendChild(el);
  try { const qr = qrcode(0, 'M'); qr.addData(`${DB.settings.siteUrl.replace(/\/$/, '')}/rendez-vous`); qr.make(); $('#t-qr').innerHTML = qr.createImgTag(5, 4); } catch (e) {}
  let i = 0; const cyc = setInterval(() => { i = (i + 1) % soins.length; const c = $('#t-cyc'); if (!c) return clearInterval(cyc); c.style.opacity = 0; setTimeout(() => { c.textContent = soins[i].titre; c.style.opacity = 1; }, 400); }, 3200);
  el.querySelector('.close-t').onclick = () => { clearInterval(cyc); el.remove(); };
  el.requestFullscreen?.().catch(() => {});
}

/* ============================================================
   DÉMARRAGE
   ============================================================ */
window.goto = goto;
window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); window._pwaPrompt = e; });
(async function init() {
  await ensurePass(); load();
  $('#login-form').onsubmit = async (e) => {
    e.preventDefault();
    if (await tryLogin($('#login-pass').value)) {
      sessionStorage.setItem('serene-auth', '1');
      $('#login').hidden = true; $('#app').classList.add('on'); buildNav(); render();
    } else { toast('Mot de passe incorrect'); }
  };
  $('#hamb').onclick = () => { $('#sidebar').classList.add('open'); $('#scrim').classList.add('on'); };
  $('#scrim').onclick = () => { $('#sidebar').classList.remove('open'); $('#scrim').classList.remove('on'); };
  $('#logout').onclick = () => { sessionStorage.removeItem('serene-auth'); location.reload(); };
  if (sessionStorage.getItem('serene-auth')) { $('#login').hidden = true; $('#app').classList.add('on'); buildNav(); render(); }
  if ('serviceWorker' in navigator && location.protocol.startsWith('http')) navigator.serviceWorker.register('sw.js').catch(() => {});
})();
