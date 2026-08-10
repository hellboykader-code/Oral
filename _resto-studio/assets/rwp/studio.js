/* ===== RestoWebPro — studio JS (surcouche sur Foodee/Framer) =====
   - injecte la galerie « Nos réalisations » après le Hero
   - se ré-injecte si React reconstruit la page (MutationObserver + interval)
   - clics pilotés en JS (Framer réécrit tous les <a href> en void(0)) → on utilise des <div> + window.open
   ⚙️ Pour mettre à jour la liste des sites, éditez le tableau SITES ci-dessous
      (remplacez `url` par l'URL en ligne réelle quand le site sera déployé). */
(function () {
  'use strict';

  // ---- Liste des sites vendables (à compléter à chaque nouveau site) ----
  var SITES = [
    {
      name: 'Braise', tag: 'Grill & feu de bois',
      desc: 'Ambiance charbon, viandes braisées, réservation en ligne et galerie appétissante.',
      img: 'assets/rwp/braise.jpg', accent: '#ff6b1a',
      url: 'https://claude.ai/code/artifact/3cb205a4-42a4-46cb-914f-7753d0a62ff0'
    },
    {
      name: 'Oliva', tag: 'Trattoria italienne',
      desc: 'Trattoria chaleureuse, carte à jour, photos gourmandes et prise de réservation.',
      img: 'assets/rwp/oliva.jpg', accent: '#c73a24',
      url: 'https://claude.ai/code/artifact/4a4a0e70-8b10-401b-8a05-157c9257c700'
    },
    {
      name: 'SAFRAN', tag: 'Bistrot moderne',
      desc: 'Bistrot élégant avec Espace Restaurateur : carte, horaires et réservations en autonomie.',
      img: 'assets/rwp/safran.jpg', accent: '#e0a428',
      url: 'https://claude.ai/code/artifact/d8f7a00e-7a11-47dc-9f56-0cfbb5d2dfa4'
    }
  ];

  var WHATSAPP = 'https://wa.me/33189480971';

  // Libellés de nav renvoyant vers des sections masquées → à cacher
  var DEAD_NAV = ['restaurant', 'menu', 'témoignages', 'temoignages', 'pourquoi nous',
                  'nos chefs', 'à propos', 'a propos'];

  function h(tag, cls, html) {
    var e = document.createElement(tag);
    if (cls) e.className = cls;
    if (html != null) e.innerHTML = html;
    return e;
  }
  function esc(s) { return String(s).replace(/[&<>"]/g, function (c) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

  function buildGallery() {
    var sec = h('section', 'rwp-real');
    sec.id = 'rwp-real';
    var cards = SITES.map(function (s) {
      return '<div class="rwp-card" data-url="' + esc(s.url) + '" style="--a:' + esc(s.accent) + '">' +
        '<div class="rwp-media"><img src="' + esc(s.img) + '" alt="Site ' + esc(s.name) + '" loading="lazy">' +
        '<span class="rwp-badge">Voir la démo</span></div>' +
        '<div class="rwp-body"><h3>' + esc(s.name) + '</h3>' +
        '<span class="rwp-tag">' + esc(s.tag) + '</span>' +
        '<p>' + esc(s.desc) + '</p>' +
        '<span class="rwp-link">Voir le site &rarr;</span></div></div>';
    }).join('');
    sec.innerHTML =
      '<div class="rwp-wrap">' +
      '<div class="rwp-head">' +
      '<span class="rwp-kicker">Nos réalisations</span>' +
      '<h2>Des sites qui donnent faim</h2>' +
      '<p>Chaque restaurant a son site sur mesure : réservation en ligne, carte toujours à jour et galerie soignée. Cliquez pour visiter.</p>' +
      '</div>' +
      '<div class="rwp-grid">' + cards + '</div>' +
      '<div class="rwp-cta">' +
      '<div><strong>À partir de 390 €</strong><span>Livraison en 3 jours · hébergement inclus · sans commission</span></div>' +
      '<div class="rwp-btns">' +
      '<div class="rwp-btn rwp-wa" data-wa>💬 Discuter sur WhatsApp</div>' +
      '<div class="rwp-btn rwp-ghost" data-contact>Nous contacter</div>' +
      '</div></div>' +
      '</div>';
    return sec;
  }

  function wire(g) {
    g.querySelectorAll('.rwp-card').forEach(function (c) {
      c.addEventListener('click', function () {
        var u = c.getAttribute('data-url');
        if (u) window.open(u, '_blank', 'noopener');
      });
    });
    var wa = g.querySelector('[data-wa]');
    if (wa) wa.addEventListener('click', function () { window.open(WHATSAPP, '_blank', 'noopener'); });
    var ct = g.querySelector('[data-contact]');
    if (ct) ct.addEventListener('click', function () {
      var s = document.querySelector('[data-framer-name="Section - Contact"]');
      if (s) s.scrollIntoView({ behavior: 'smooth' });
    });
  }

  function findHero() {
    return document.querySelector('[data-framer-name="Section - Hero"]') ||
           document.querySelector('[data-framer-name^="Section - Hero"]') ||
           document.querySelector('[data-framer-name^="Section"]');
  }

  function place() {
    if (document.getElementById('rwp-real')) return;
    var hero = findHero();
    var g = buildGallery();
    if (hero && hero.parentNode) hero.parentNode.insertBefore(g, hero.nextSibling);
    else (document.querySelector('#main') || document.body).appendChild(g);
    wire(g);
  }

  // Nettoyage de la nav : cacher les onglets vers des sections masquées,
  // et faire défiler « Populaire/Réalisations » vers notre galerie.
  function cleanNav() {
    var links = document.querySelectorAll('nav a, [data-framer-name="Nav"] a, #main a');
    links.forEach(function (a) {
      // libellé propre (retire le CSS injecté par les effets Framer + doublons)
      var t = (a.textContent || '').split('{')[0].trim().toLowerCase();
      if (!t || t.length > 24) return;
      // dé-duplication (Framer double parfois le libellé)
      var half = t.slice(0, t.length / 2);
      if (t.length % 2 === 0 && half === t.slice(t.length / 2)) t = half.trim();
      if (t === 'populaire' || t === 'réalisations' || t === 'realisations') {
        if (!a.dataset.rwpWired) {
          a.dataset.rwpWired = '1';
          a.addEventListener('click', function (e) {
            var g = document.getElementById('rwp-real');
            if (g) { e.preventDefault(); e.stopPropagation(); g.scrollIntoView({ behavior: 'smooth' }); }
          }, true);
        }
      } else if (DEAD_NAV.indexOf(t) !== -1) {
        // masquer l'onglet vers une section supprimée (sauf s'il est dans le footer « Liens »)
        var li = a.closest('li') || a;
        li.classList.add('rwp-nav-hidden');
      }
    });
  }

  function apply() { place(); cleanNav(); }

  function boot() {
    apply();
    // Ré-appliquer tant que React reconstruit la page
    var mo = new MutationObserver(function () {
      if (!document.getElementById('rwp-real')) place();
      cleanNav();
    });
    mo.observe(document.body, { childList: true, subtree: true });
    var n = 0, iv = setInterval(function () { apply(); if (++n > 40) clearInterval(iv); }, 500);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
