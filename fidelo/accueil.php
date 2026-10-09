<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="./support.js"></script>
</head>
<body>
<x-dc>
<helmet>
<title>Fidelo — La carte de fidélité digitale des commerces de proximité</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
<link href="https://fonts.googleapis.com/css2?family=Gloock&family=Geist:wght@400;500;600&family=Geist+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
html,body{margin:0;background:#0c0a08;color:#f4efe6;-webkit-font-smoothing:antialiased}
body{font-family:Geist,system-ui,sans-serif;overflow-x:hidden}
a{color:#f2a65a;text-decoration:none}a:hover{color:#ffcf99}
::selection{background:#f2a65a;color:#1a1007}
@media (max-width:760px){[data-hud]{align-items:flex-end!important;justify-content:stretch!important;padding:90px 18px 96px!important}[data-hud]>div{width:100%!important;max-width:100%!important}}
</style>
</helmet>
<div ref="{{ rootRef }}" style="position:relative;background:#0c0a08">
  <dc-import name="Nav" active="accueil" hint-size="100%,72px"></dc-import>

  <div ref="{{ stageRef }}" style="position:fixed;inset:0;z-index:0;background:radial-gradient(ellipse at 50% 18%,#2a1a0c 0%,#0c0a08 62%)"></div>

  <sc-if value="{{ debugOn }}" hint-placeholder-val="{{ false }}">
    <div style="position:fixed;top:80px;left:12px;right:12px;z-index:999;background:rgba(0,0,0,.92);color:#7dffb0;font:12px/1.6 'Geist Mono',monospace;padding:14px;border-radius:10px;white-space:pre-wrap;word-break:break-word;box-shadow:0 8px 30px rgba(0,0,0,.6)">{{ debugText }}</div>
  </sc-if>

  <sc-if value="{{ fallback }}" hint-placeholder-val="{{ false }}">
    <div style="position:fixed;inset:0;z-index:1;overflow:hidden;background:radial-gradient(ellipse at 50% 18%,#2a1a0c 0%,#0c0a08 62%)">
      <sc-if value="{{ hasVideo }}" hint-placeholder-val="{{ false }}">
        <video src="{{ fallbackVideo }}" autoplay="" muted="" loop="" playsinline="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover"></video>
      </sc-if>
      <sc-for list="{{ stills }}" as="im" hint-placeholder-count="0">
        <img data-fb="{{ $index }}" src="{{ im.src }}" alt="{{ im.alt }}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity .6s ease">
      </sc-for>
    </div>
  </sc-if>
  <div style="position:fixed;inset:0;z-index:2;pointer-events:none;background:linear-gradient(180deg,rgba(12,10,8,.55) 0%,rgba(12,10,8,0) 18%,rgba(12,10,8,0) 62%,rgba(12,10,8,.78) 100%)"></div>

  <div style="position:fixed;inset:0;z-index:4;pointer-events:none;text-shadow:0 2px 28px rgba(0,0,0,.55)">

    <section data-hud="0" data-screen-label="01 Ouverture" style="position:absolute;inset:0;display:flex;align-items:flex-end;justify-content:flex-start;padding:110px clamp(20px,6vw,88px) 128px;box-sizing:border-box">
      <div style="max-width:min(820px,100%);display:flex;flex-direction:column;gap:26px">
        <div style="display:flex;align-items:center;gap:12px;font:500 12px/1 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a"><span style="width:28px;height:1px;background:#f2a65a"></span><span>Programme de fidélité pour commerces de proximité</span></div>
        <h1 style="margin:0;font:400 clamp(3rem,7.4vw,7.2rem)/.94 Gloock,serif;letter-spacing:-.025em;text-wrap:balance">La carte de fidélité qui ne se perd plus.</h1>
        <p style="margin:0;max-width:540px;font:400 clamp(1rem,1.3vw,1.18rem)/1.6 Geist,sans-serif;color:#d6cdbf;text-wrap:pretty">Vos clients cumulent leurs points depuis leur téléphone. Pas d'application à installer, pas de carte plastique : vous scannez, le point s'ajoute.</p>
        <div style="display:flex;flex-wrap:wrap;gap:12px;pointer-events:auto">
          <a href="inscription.php" style="display:flex;align-items:center;padding:16px 26px;border-radius:999px;background:#f2a65a;color:#1a1007;font:600 15px Geist,sans-serif;text-shadow:none;box-shadow:0 14px 40px rgba(242,166,90,.3)" style-hover="background:#ffbe7d;color:#1a1007">Commencer gratuitement</a>
          <button onClick="{{ goDemo }}" style="display:flex;align-items:center;gap:10px;padding:16px 24px;border-radius:999px;background:rgba(244,239,230,.06);border:1px solid rgba(244,239,230,.22);color:#f4efe6;font:500 15px Geist,sans-serif;cursor:pointer;backdrop-filter:blur(10px)" style-hover="background:rgba(244,239,230,.12)">Essayer la démo</button>
        </div>
        <div style="font:400 12px 'Geist Mono',monospace;letter-spacing:.12em;text-transform:uppercase;color:#a39a8c">Cafés · Restaurants · Salons · Boulangeries · Bars à jus</div>
      </div>
    </section>

    <section data-hud="1" data-screen-label="02 Le constat" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:flex-end;padding:110px clamp(20px,6vw,88px) 128px;box-sizing:border-box;opacity:0">
      <div style="width:min(470px,100%);display:flex;flex-direction:column;gap:24px">
        <div style="display:flex;align-items:center;gap:12px;font:500 12px/1 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a"><span style="width:28px;height:1px;background:#f2a65a"></span><span>Scène 02 — Sans Fidelo</span></div>
        <h2 style="margin:0;font:400 clamp(2.4rem,4.6vw,4.4rem)/.98 Gloock,serif;letter-spacing:-.02em;text-wrap:balance">Une carte papier, ça s'oublie.</h2>
        <div style="display:flex;flex-direction:column;border-top:1px solid rgba(244,239,230,.16);font:400 1rem/1.5 Geist,sans-serif;color:#e4dccf">
          <div style="display:flex;gap:16px;padding:14px 0;border-bottom:1px solid rgba(244,239,230,.1)"><span style="font:500 12px/1.9 'Geist Mono',monospace;color:#a39a8c">01</span><span>Le client vient une fois, puis vous oublie.</span></div>
          <div style="display:flex;gap:16px;padding:14px 0;border-bottom:1px solid rgba(244,239,230,.1)"><span style="font:500 12px/1.9 'Geist Mono',monospace;color:#a39a8c">02</span><span>La carte à tampons se perd au fond d'un sac.</span></div>
          <div style="display:flex;gap:16px;padding:14px 0;border-bottom:1px solid rgba(244,239,230,.1)"><span style="font:500 12px/1.9 'Geist Mono',monospace;color:#a39a8c">03</span><span>Aucune donnée : impossible de le recontacter.</span></div>
        </div>
        <div style="display:flex;align-items:flex-start;gap:18px">
          <span style="font:400 3.4rem/1 Gloock,serif;color:#f2a65a">30 %</span>
          <div style="display:flex;flex-direction:column;gap:6px;padding-top:4px">
            <span style="font:400 .95rem/1.45 Geist,sans-serif;color:#e4dccf">des Français oublient régulièrement d'utiliser leurs cartes de fidélité.</span>
            <a href="https://www.sumup.com/fr-fr/business-guide/sondage-sur-les-programmes-de-fidelite-en-france/" target="_blank" rel="noopener" style="pointer-events:auto;font:400 11px 'Geist Mono',monospace;letter-spacing:.06em;color:#a39a8c;text-decoration:underline;text-underline-offset:3px" style-hover="color:#f2a65a">Source : SumUp, sondage 1 500 Français, avril 2026</a>
          </div>
        </div>
      </div>
    </section>

    <section data-hud="2" data-screen-label="03 La bascule" style="position:absolute;inset:0;display:flex;align-items:flex-end;justify-content:flex-start;padding:110px clamp(20px,6vw,88px) 128px;box-sizing:border-box;opacity:0">
      <div style="width:min(520px,100%);display:flex;flex-direction:column;gap:22px">
        <div style="display:flex;align-items:center;gap:12px;font:500 12px/1 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a"><span style="width:28px;height:1px;background:#f2a65a"></span><span>Scène 03 — La bascule</span></div>
        <h2 style="margin:0;font:400 clamp(2.4rem,4.6vw,4.4rem)/.98 Gloock,serif;letter-spacing:-.02em;text-wrap:balance">Tout tient dans le téléphone du client.</h2>
        <p style="margin:0;font:400 1.05rem/1.6 Geist,sans-serif;color:#d6cdbf;text-wrap:pretty">La carte s'ouvre dans le navigateur. Votre client l'ajoute à Google Wallet ou à son écran d'accueil — rien à télécharger.</p>
        <div style="display:flex;align-items:flex-start;gap:18px">
          <span style="font:400 3.4rem/1 Gloock,serif;color:#f2a65a">91 %</span>
          <div style="display:flex;flex-direction:column;gap:6px;padding-top:4px">
            <span style="font:400 .95rem/1.45 Geist,sans-serif;color:#e4dccf">des personnes de 12 ans et plus en France sont équipées d'un smartphone.</span>
            <a href="https://www.arcep.fr/uploads/tx_gspublication/barometre-du-numerique-edition-2026_INFOGRAPHIE.pdf" target="_blank" rel="noopener" style="pointer-events:auto;font:400 11px 'Geist Mono',monospace;letter-spacing:.06em;color:#a39a8c;text-decoration:underline;text-underline-offset:3px" style-hover="color:#f2a65a">Source : Arcep / Crédoc, Baromètre du numérique 2026</a>
          </div>
        </div>
      </div>
    </section>

    <section data-hud="3" data-screen-label="04 En caisse" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:flex-start;padding:110px clamp(20px,6vw,88px) 128px;box-sizing:border-box;opacity:0">
      <div style="width:min(470px,100%);display:flex;flex-direction:column;gap:22px">
        <div style="display:flex;align-items:center;gap:12px;font:500 12px/1 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a"><span style="width:28px;height:1px;background:#f2a65a"></span><span>Scène 04 — En caisse</span></div>
        <h2 style="margin:0;font:400 clamp(2.4rem,4.6vw,4.4rem)/.98 Gloock,serif;letter-spacing:-.02em;text-wrap:balance">Trois gestes, pas un de plus.</h2>
        <div style="display:flex;flex-direction:column;gap:4px">
          <div data-step="0" style="display:flex;gap:18px;padding:14px 0;border-top:1px solid rgba(244,239,230,.14);transition:opacity .4s">
            <span style="font:400 1.6rem/1 Gloock,serif;color:#f2a65a">1</span>
            <div style="display:flex;flex-direction:column;gap:4px"><strong style="font:600 1.02rem Geist,sans-serif">Le client montre son code</strong><span style="font:400 .95rem/1.5 Geist,sans-serif;color:#d6cdbf">Reçu par SMS ou enregistré sur son téléphone. Aucune application.</span></div>
          </div>
          <div data-step="1" style="display:flex;gap:18px;padding:14px 0;border-top:1px solid rgba(244,239,230,.14);opacity:.35;transition:opacity .4s">
            <span style="font:400 1.6rem/1 Gloock,serif;color:#f2a65a">2</span>
            <div style="display:flex;flex-direction:column;gap:4px"><strong style="font:600 1.02rem Geist,sans-serif">Vous scannez depuis votre espace</strong><span style="font:400 .95rem/1.5 Geist,sans-serif;color:#d6cdbf">C'est toujours le commerçant qui scanne, jamais l'inverse : pas de triche possible.</span></div>
          </div>
          <div data-step="2" style="display:flex;gap:18px;padding:14px 0;border-top:1px solid rgba(244,239,230,.14);opacity:.35;transition:opacity .4s">
            <span style="font:400 1.6rem/1 Gloock,serif;color:#f2a65a">3</span>
            <div style="display:flex;flex-direction:column;gap:4px"><strong style="font:600 1.02rem Geist,sans-serif">Le point s'ajoute</strong><span style="font:400 .95rem/1.5 Geist,sans-serif;color:#d6cdbf">Au palier que vous avez fixé, la récompense se débloque.</span></div>
          </div>
        </div>
      </div>
    </section>

    <section data-hud="4" data-screen-label="05 Démo" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:flex-end;padding:100px clamp(20px,5vw,72px) 120px;box-sizing:border-box;opacity:0">
      <div data-panel="" style="width:min(430px,100%);display:flex;flex-direction:column;gap:18px;padding:26px;border-radius:24px;background:rgba(16,13,10,.66);border:1px solid rgba(244,239,230,.12);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);text-shadow:none;box-sizing:border-box">
        <div style="display:flex;align-items:center;gap:12px;font:500 12px/1 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a"><span style="width:28px;height:1px;background:#f2a65a"></span><span>Scène 05 — À vous de scanner</span></div>
        <h2 style="margin:0;font:400 clamp(2rem,3.2vw,2.9rem)/1 Gloock,serif;letter-spacing:-.02em">Votre échelle de récompenses.</h2>
        <div style="display:flex;flex-wrap:wrap;gap:6px">
          <sc-for list="{{ shops }}" as="sh" hint-placeholder-count="5">
            <button onClick="{{ sh.pick }}" style="padding:8px 13px;border-radius:999px;border:1px solid {{ sh.border }};background:{{ sh.bg }};color:{{ sh.color }};font:500 13px Geist,sans-serif;cursor:pointer">{{ sh.label }}</button>
          </sc-for>
        </div>
        <div style="display:flex;flex-direction:column;border-top:1px solid rgba(244,239,230,.12)">
          <sc-for list="{{ tierRows }}" as="t" hint-placeholder-count="4">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:11px 0;border-bottom:1px solid rgba(244,239,230,.08)">
              <div style="display:flex;align-items:center;gap:14px"><span style="width:52px;font:500 13px 'Geist Mono',monospace;color:{{ t.numColor }}">{{ t.n }} pts</span><span style="font:400 15px Geist,sans-serif;color:{{ t.labelColor }}">{{ t.label }}</span></div>
              <span style="font:500 11px 'Geist Mono',monospace;letter-spacing:.08em;text-transform:uppercase;color:{{ t.numColor }}">{{ t.status }}</span>
            </div>
          </sc-for>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
          <div style="display:flex;align-items:baseline;gap:8px"><span style="font:400 2.6rem/1 Gloock,serif;color:#f2a65a">{{ points }}</span><span style="font:500 12px 'Geist Mono',monospace;color:#a39a8c">/ 20 PTS</span></div>
          <div style="display:flex;gap:8px">
            <button onClick="{{ reset }}" aria-label="Remettre à zéro" style="width:48px;height:48px;border-radius:50%;border:1px solid rgba(244,239,230,.2);background:transparent;color:#f4efe6;font-size:17px;cursor:pointer" style-hover="background:rgba(244,239,230,.08)">↺</button>
            <button onClick="{{ scan }}" style="height:48px;padding:0 22px;border-radius:999px;border:0;background:#f2a65a;color:#1a1007;font:600 15px Geist,sans-serif;cursor:pointer;box-shadow:0 10px 30px rgba(242,166,90,.3)" style-hover="background:#ffbe7d">Scanner un passage</button>
          </div>
        </div>
        <div style="font:400 13px/1.45 Geist,sans-serif;color:{{ msgColor }};min-height:19px">{{ msg }}</div>
        <div style="display:flex;gap:8px;padding-top:14px;border-top:1px solid rgba(244,239,230,.1)">
          <input value="{{ aiText }}" onChange="{{ onAiText }}" onKeyDown="{{ onAiKey }}" placeholder="Votre commerce, ex. salon de coiffure à Lyon" style="flex:1;min-width:0;height:42px;padding:0 14px;border-radius:12px;border:1px solid rgba(244,239,230,.16);background:rgba(244,239,230,.04);color:#f4efe6;font:400 14px Geist,sans-serif;outline:none">
          <button onClick="{{ askAi }}" style="height:42px;padding:0 14px;border-radius:12px;border:1px solid rgba(242,166,90,.5);background:transparent;color:#f2a65a;font:500 13px Geist,sans-serif;cursor:pointer;white-space:nowrap" style-hover="background:rgba(242,166,90,.1)">{{ aiLabel }}</button>
        </div>
        <span style="font:400 12px/1.4 Geist,sans-serif;color:#8f877a">Paliers donnés en exemple : vous fixez librement les vôtres.</span>
      </div>
    </section>

    <section data-hud="5" data-screen-label="06 Pourquoi" style="position:absolute;inset:0;display:flex;align-items:flex-end;justify-content:flex-end;padding:110px clamp(20px,6vw,88px) 128px;box-sizing:border-box;opacity:0">
      <div style="width:min(560px,100%);display:flex;flex-direction:column;gap:22px">
        <div style="display:flex;align-items:center;gap:12px;font:500 12px/1 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a"><span style="width:28px;height:1px;background:#f2a65a"></span><span>Scène 06 — Avec Fidelo</span></div>
        <h2 style="margin:0;font:400 clamp(2.4rem,4.6vw,4.4rem)/.98 Gloock,serif;letter-spacing:-.02em;text-wrap:balance">Une récompense proche fait revenir.</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px 28px">
          <div style="display:flex;flex-direction:column;gap:8px;padding-top:14px;border-top:1px solid rgba(244,239,230,.16)"><span style="font:400 2.8rem/1 Gloock,serif;color:#f2a65a">67 %</span><span style="font:400 .95rem/1.45 Geist,sans-serif;color:#e4dccf">des Français disent que les programmes de fidélité les incitent à revenir dans les mêmes enseignes.</span></div>
          <div style="display:flex;flex-direction:column;gap:8px;padding-top:14px;border-top:1px solid rgba(244,239,230,.16)"><span style="font:400 2.8rem/1 Gloock,serif;color:#f2a65a">35 %</span><span style="font:400 .95rem/1.45 Geist,sans-serif;color:#e4dccf">ont abandonné un programme parce que la récompense était trop longue à obtenir.</span></div>
        </div>
        <a href="https://www.sumup.com/fr-fr/business-guide/sondage-sur-les-programmes-de-fidelite-en-france/" target="_blank" rel="noopener" style="pointer-events:auto;font:400 11px 'Geist Mono',monospace;letter-spacing:.06em;color:#a39a8c;text-decoration:underline;text-underline-offset:3px" style-hover="color:#f2a65a">Source : SumUp, sondage 1 500 Français, 18–22 avril 2026</a>
        <p style="margin:0;font:400 1rem/1.6 Geist,sans-serif;color:#d6cdbf;text-wrap:pretty">D'où un premier palier dès 3 points. Un scan, un point ; rien ne se perd, tout est dans le téléphone ; et comme c'est vous qui scannez, personne ne triche.</p>
      </div>
    </section>

    <section data-hud="6" data-screen-label="07 Tarifs" style="position:absolute;inset:0;display:flex;align-items:flex-end;justify-content:center;padding:110px clamp(20px,5vw,72px) 118px;box-sizing:border-box;opacity:0">
      <div style="width:min(1180px,100%);display:flex;flex-direction:column;gap:20px">
        <div style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px">
          <div style="display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;align-items:center;gap:12px;font:500 12px/1 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a"><span style="width:28px;height:1px;background:#f2a65a"></span><span>Scène 07 — Tarifs</span></div>
            <h2 style="margin:0;font:400 clamp(2.2rem,4vw,3.8rem)/.98 Gloock,serif;letter-spacing:-.02em">Dès 0 €. Sans commission.</h2>
          </div>
          <a href="tarifs.php" style="pointer-events:auto;font:500 14px Geist,sans-serif;color:#f4efe6;padding:12px 18px;border-radius:999px;border:1px solid rgba(244,239,230,.22);text-shadow:none" style-hover="background:rgba(244,239,230,.08);color:#f4efe6">Comparer les formules →</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1px;border-radius:18px;overflow:hidden;background:rgba(244,239,230,.1);border:1px solid rgba(244,239,230,.1);text-shadow:none">
          <div style="display:flex;flex-direction:column;gap:6px;padding:18px 20px;background:rgba(16,13,10,.72);backdrop-filter:blur(14px)"><span style="font:500 11px 'Geist Mono',monospace;letter-spacing:.14em;text-transform:uppercase;color:#a39a8c">Découverte</span><span style="font:400 2rem/1.1 Gloock,serif">0 €</span><span style="font:400 13px/1.45 Geist,sans-serif;color:#d6cdbf">Jusqu'à 30 clients, sans carte bancaire.</span></div>
          <div style="display:flex;flex-direction:column;gap:6px;padding:18px 20px;background:rgba(16,13,10,.72);backdrop-filter:blur(14px)"><span style="font:500 11px 'Geist Mono',monospace;letter-spacing:.14em;text-transform:uppercase;color:#a39a8c">Mensuel</span><span style="font:400 2rem/1.1 Gloock,serif">29 € <span style="font:400 14px Geist,sans-serif;color:#a39a8c">/ mois</span></span><span style="font:400 13px/1.45 Geist,sans-serif;color:#d6cdbf">Clients illimités, sans engagement.</span></div>
          <div style="display:flex;flex-direction:column;gap:6px;padding:18px 20px;background:rgba(242,166,90,.16);backdrop-filter:blur(14px)"><span style="font:500 11px 'Geist Mono',monospace;letter-spacing:.14em;text-transform:uppercase;color:#f2a65a">Annuel · le plus choisi</span><span style="font:400 2rem/1.1 Gloock,serif">250 € <span style="font:400 14px Geist,sans-serif;color:#a39a8c">/ an</span></span><span style="font:400 13px/1.45 Geist,sans-serif;color:#d6cdbf">Soit 20,83 € par mois, 98 € économisés.</span></div>
          <div style="display:flex;flex-direction:column;gap:6px;padding:18px 20px;background:rgba(16,13,10,.72);backdrop-filter:blur(14px)"><span style="font:500 11px 'Geist Mono',monospace;letter-spacing:.14em;text-transform:uppercase;color:#a39a8c">À vie + site web</span><span style="font:400 2rem/1.1 Gloock,serif">525 € <span style="font:400 14px Geist,sans-serif;color:#a39a8c">une fois</span></span><span style="font:400 13px/1.45 Geist,sans-serif;color:#d6cdbf">Site pro offert, puis 50 € / an (domaine + hébergement).</span></div>
        </div>
      </div>
    </section>

    <section data-hud="7" data-screen-label="08 Questions" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:flex-end;padding:100px clamp(20px,5vw,72px) 120px;box-sizing:border-box;opacity:0">
      <div style="width:min(500px,100%);display:flex;flex-direction:column;gap:16px;padding:26px;border-radius:24px;background:rgba(16,13,10,.66);border:1px solid rgba(244,239,230,.12);backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);text-shadow:none;box-sizing:border-box">
        <div style="display:flex;align-items:center;gap:12px;font:500 12px/1 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a"><span style="width:28px;height:1px;background:#f2a65a"></span><span>Scène 08 — Questions</span></div>
        <h2 style="margin:0;font:400 clamp(2rem,3.2vw,2.9rem)/1 Gloock,serif;letter-spacing:-.02em">Questions fréquentes</h2>
        <div style="display:flex;flex-direction:column">
          <sc-for list="{{ faq }}" as="q" hint-placeholder-count="5">
            <div style="border-top:1px solid rgba(244,239,230,.1)">
              <button onClick="{{ q.toggle }}" style="width:100%;display:flex;justify-content:space-between;align-items:center;gap:14px;padding:14px 0;background:none;border:0;color:#f4efe6;font:500 15px/1.35 Geist,sans-serif;text-align:left;cursor:pointer"><span>{{ q.q }}</span><span style="font:400 18px Geist,sans-serif;color:#f2a65a;transform:rotate({{ q.rot }});transition:transform .25s">+</span></button>
              <sc-if value="{{ q.open }}" hint-placeholder-val="{{ false }}"><p style="margin:0 0 14px;font:400 14px/1.55 Geist,sans-serif;color:#cfc6b8;text-wrap:pretty">{{ q.a }}</p></sc-if>
            </div>
          </sc-for>
        </div>
        <a href="tarifs.php" style="font:500 13px Geist,sans-serif;color:#f2a65a">Toutes les questions sur les tarifs →</a>
      </div>
    </section>

    <section data-hud="8" data-screen-label="09 Générique" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:110px clamp(20px,6vw,88px) 128px;box-sizing:border-box;opacity:0;text-align:center">
      <div style="max-width:min(900px,100%);display:flex;flex-direction:column;align-items:center;gap:26px">
        <div style="font:500 12px/1 'Geist Mono',monospace;letter-spacing:.2em;text-transform:uppercase;color:#f2a65a">Scène 09 — À vous</div>
        <h2 style="margin:0;font:400 clamp(2.8rem,6.4vw,6.2rem)/.95 Gloock,serif;letter-spacing:-.025em;text-wrap:balance">Donnez à vos clients une raison de revenir.</h2>
        <p style="margin:0;font:400 1.08rem/1.6 Geist,sans-serif;color:#d6cdbf">0 € · jusqu'à 30 clients · sans carte bancaire · 10 jours en illimité offerts à l'inscription</p>
        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:12px;pointer-events:auto">
          <a href="inscription.php" style="padding:17px 28px;border-radius:999px;background:#f2a65a;color:#1a1007;font:600 15px Geist,sans-serif;text-shadow:none;box-shadow:0 14px 40px rgba(242,166,90,.3)" style-hover="background:#ffbe7d;color:#1a1007">Créer mon programme</a>
          <a href="contact.php" style="padding:17px 26px;border-radius:999px;border:1px solid rgba(244,239,230,.22);color:#f4efe6;font:500 15px Geist,sans-serif;backdrop-filter:blur(10px)" style-hover="background:rgba(244,239,230,.1);color:#f4efe6">Poser une question</a>
        </div>
      </div>
    </section>
  </div>

  <div style="position:fixed;left:0;right:0;bottom:0;z-index:6;padding:0 clamp(16px,4vw,40px) 18px;box-sizing:border-box;pointer-events:none">
    <div style="display:flex;align-items:center;gap:18px;font:500 11px 'Geist Mono',monospace;letter-spacing:.12em;text-transform:uppercase;color:#a39a8c">
      <span style="display:flex;gap:8px;white-space:nowrap"><span data-counter="" style="color:#f4efe6">SC 01</span><span>/ 09</span></span>
      <div style="flex:1;display:flex;gap:4px;pointer-events:auto">
        <sc-for list="{{ ticks }}" as="tk" hint-placeholder-count="9">
          <button onClick="{{ tk.go }}" aria-label="{{ tk.label }}" title="{{ tk.label }}" style="flex:1;height:18px;padding:0;border:0;background:none;cursor:pointer;display:flex;align-items:center"><span style="position:relative;display:block;width:100%;height:2px;background:rgba(244,239,230,.16);overflow:hidden"><span data-tick="{{ $index }}" style="position:absolute;left:0;top:0;bottom:0;width:0;background:#f2a65a"></span></span></button>
        </sc-for>
      </div>
      <span data-scene-name="" style="min-width:110px;color:#f4efe6;display:{{ nameDisplay }}">Ouverture</span>
      <span data-tc="" style="font-variant-numeric:tabular-nums;white-space:nowrap">00:00:00:00</span>
    </div>
  </div>

  <div ref="{{ spacerRef }}" style="height:{{ spacerH }}"></div>
  <dc-import name="Footer" hint-size="100%,260px"></dc-import>
</div>
</x-dc>
<script type="text/x-dc" data-dc-script data-props="{&quot;forceFallback&quot;:{&quot;editor&quot;:&quot;boolean&quot;,&quot;default&quot;:false,&quot;tsType&quot;:&quot;boolean&quot;,&quot;section&quot;:&quot;Film&quot;},&quot;fallbackStills&quot;:{&quot;editor&quot;:&quot;boolean&quot;,&quot;default&quot;:false,&quot;tsType&quot;:&quot;boolean&quot;,&quot;section&quot;:&quot;Film&quot;},&quot;fallbackVideo&quot;:{&quot;editor&quot;:&quot;text&quot;,&quot;default&quot;:&quot;&quot;,&quot;tsType&quot;:&quot;string&quot;,&quot;section&quot;:&quot;Film&quot;}}">
class Component extends DCLogic {
  state = { fallback: false, points: 4, shop: 0, open: 0, msg: '', aiText: '', aiBusy: false, tiers: null, narrow: false, debugOn: false, debugText: '' };
  N = 9;
  NAMES = ['Ouverture', 'Le constat', 'La bascule', 'En caisse', 'Démo', 'Pourquoi', 'Tarifs', 'Questions', 'Générique'];
  SHOPS = ['Café Lumière', 'Boulangerie Soleil', 'Salon Élégance', 'Bar à jus Zeste', 'Bistrot du Coin'];
  SHOP_LABELS = ['Café', 'Boulangerie', 'Salon', 'Bar à jus', 'Restaurant'];
  TIERS = [{ n: 3, label: 'Boisson offerte' }, { n: 6, label: 'Pâtisserie maison' }, { n: 10, label: '−15 % sur l’addition' }, { n: 20, label: 'Repas offert · VIP' }];
  FAQ = [
    { q: 'La formule Découverte est-elle vraiment gratuite ?', a: 'Oui : 0 €, sans limite de durée et sans carte bancaire, jusqu’à 30 clients. À l’inscription, vous profitez aussi de 10 jours en illimité.' },
    { q: 'Mes clients doivent-ils installer une application ?', a: 'Non. Leur carte s’ouvre dans le navigateur et peut être ajoutée à Google Wallet ou à l’écran d’accueil du téléphone.' },
    { q: 'Faut-il acheter du matériel ?', a: 'Non. Vous scannez les codes depuis votre espace Fidelo, sur le téléphone ou la tablette que vous avez déjà.' },
    { q: 'Y a-t-il une commission sur mes ventes ?', a: 'Aucune, quelle que soit la formule.' },
    { q: 'Puis-je changer de formule plus tard ?', a: 'Oui, vous pouvez passer d’une formule à l’autre selon l’évolution de votre commerce.' },
  ];
  rootRef = React.createRef(); stageRef = React.createRef(); spacerRef = React.createRef();

  componentDidMount() {
    const mq = matchMedia('(prefers-reduced-motion: reduce)');
    const nav = navigator, mobile = /Mobi|Android/i.test(nav.userAgent);
    const low = (nav.deviceMemory && nav.deviceMemory <= 2) || (mobile && (nav.hardwareConcurrency || 8) <= 4) || (nav.connection && nav.connection.saveData);
    let gl = false, glErr = ''; try { gl = !!document.createElement('canvas').getContext('webgl2'); } catch (e) { glErr = String(e && e.message || e); }
    const debugOn = /[?&]debug=1\b/.test(location.search);
    if (debugOn) {
      const reasons = [];
      if (this.props.forceFallback) reasons.push('forceFallback=true (prop)');
      if (mq.matches) reasons.push('prefers-reduced-motion: reduce');
      if (low) reasons.push('appareil jugé "low-end"');
      if (!gl) reasons.push('pas de WebGL2' + (glErr ? ' (' + glErr + ')' : ''));
      const info = [
        'mode: ' + (reasons.length ? 'FALLBACK (image plate)' : 'FILM 3D'),
        reasons.length ? 'raison(s): ' + reasons.join(', ') : '',
        'reducedMotion=' + mq.matches + '  webgl2=' + gl,
        'deviceMemory=' + nav.deviceMemory + '  hardwareConcurrency=' + nav.hardwareConcurrency,
        'saveData=' + !!(nav.connection && nav.connection.saveData) + '  mobile=' + mobile,
        'UA: ' + nav.userAgent,
      ].filter(Boolean).join('\n');
      this.setState({ debugOn: true, debugText: info });
      try { console.log('[fidelo debug]\n' + info); } catch (e) {}
    }
    this.onScroll = () => this.readScroll();
    this.onResize = () => { this.setState({ narrow: innerWidth < 760 }); this.readScroll(); };
    addEventListener('scroll', this.onScroll, { passive: true }); addEventListener('resize', this.onResize);
    this.setState({ narrow: innerWidth < 760 });
    window.__fideloRestart = () => { window.__fideloNoWatchdog = true; this.setState({ fallback: false }, () => this.startFilm()); };
    if (this.props.forceFallback || mq.matches || low || !gl) this.goFallback();
    else this.startFilm();
    this.readScroll();
  }
  componentDidUpdate(pp) {
    if (pp.forceFallback !== this.props.forceFallback) { if (this.props.forceFallback) this.goFallback(); else if (this.state.fallback) { this.setState({ fallback: false }); this.startFilm(); } }
    if (this.film) this.film.setDemo({ points: this.state.points, shop: this.SHOPS[this.state.shop], tiers: this.state.tiers || this.TIERS });
    if (this.state.fallback) this.paint(this.s || 0);
  }
  componentWillUnmount() { removeEventListener('scroll', this.onScroll); removeEventListener('resize', this.onResize); this.film && this.film.destroy(); }

  async startFilm() {
    try {
      const mod = await import(new URL('fidelo-scene.js', location.href).href);
      if (this.state.fallback || this.film) return;
      this.film = await mod.createFilm(this.stageRef.current, { onSlow: () => { if (!window.__fideloNoWatchdog) this.goFallback(); }, onFrame: c => this.paint(c) });
      this.film.setDemo({ points: this.state.points, shop: this.SHOPS[this.state.shop], tiers: this.TIERS });
      this.film.jump(this.s || 0);
      window.__film = this.film;
    } catch (e) { console.warn('3D indisponible, bascule en images fixes', e); this.goFallback(); }
  }
  goFallback() { if (this.film) { this.film.destroy(); this.film = null; } this.setState({ fallback: true }, () => this.paint(this.s || 0)); }

  readScroll() {
    const sp = this.spacerRef.current; if (!sp) return;
    const max = Math.max(1, sp.offsetHeight - innerHeight);
    this.s = Math.min(1, Math.max(0, scrollY / max)) * (this.N - 1);
    if (this.film) this.film.setTarget(this.s); else this.paint(this.s);
  }
  paint(s) {
    const root = this.rootRef.current; if (!root) return;
    root.querySelectorAll('[data-hud]').forEach(el => {
      const i = +el.dataset.hud, d = s - i, a = Math.abs(d), op = Math.max(0, Math.min(1, 1.25 - a * 2.6));
      el.style.opacity = op; el.style.transform = `translate3d(0,${(-d * 46).toFixed(1)}px,0)`;
      el.style.filter = op > .98 ? 'none' : `blur(${(a * 10).toFixed(1)}px)`;
      el.style.visibility = op < .01 ? 'hidden' : 'visible';
      el.querySelectorAll('a,button,input').forEach(b => b.style.pointerEvents = op > .6 ? 'auto' : 'none');
    });
    const f = s - 3, st = f < -.2 ? 0 : f < .4 ? 0 : f < .7 ? 1 : 2;
    root.querySelectorAll('[data-step]').forEach(el => el.style.opacity = +el.dataset.step <= st ? 1 : .35);
    const idx = Math.round(s);
    root.querySelectorAll('[data-tick]').forEach(el => { const i = +el.dataset.tick; el.style.width = (Math.max(0, Math.min(1, s - i + .5)) * 100) + '%'; });
    root.querySelectorAll('[data-fb]').forEach(el => el.style.opacity = +el.dataset.fb === idx ? 1 : 0);
    const c = root.querySelector('[data-counter]'); if (c) c.textContent = 'SC ' + String(idx + 1).padStart(2, '0');
    const n = root.querySelector('[data-scene-name]'); if (n) n.textContent = this.NAMES[idx];
    const tc = root.querySelector('[data-tc]'); if (tc) { const fr = Math.round(s / (this.N - 1) * 108 * 24), p = v => String(v).padStart(2, '0'); tc.textContent = `00:${p(Math.floor(fr / 1440))}:${p(Math.floor(fr / 24) % 60)}:${p(fr % 24)}`; }
  }
  goTo(i) { const sp = this.spacerRef.current; if (!sp) return; const max = sp.offsetHeight - innerHeight; window.scrollTo({ top: i / (this.N - 1) * max, behavior: this.state.fallback ? 'auto' : 'smooth' }); }

  addPoint() {
    const tiers = this.state.tiers || this.TIERS;
    this.setState(s => {
      const p = s.points >= 20 ? 20 : s.points + 1, hit = tiers.find(t => t.n === p);
      return { points: p, msg: s.points >= 20 ? 'Carte complète : toutes les récompenses sont débloquées.' : hit ? `Récompense débloquée : ${hit.label}.` : `+1 point · ${p} / 20` };
    });
  }
  async askAi() {
    const txt = this.state.aiText.trim(); if (!txt || this.state.aiBusy) return;
    if (!window.claude || !window.claude.complete) { this.setState({ msg: 'Suggestion indisponible ici.' }); return; }
    this.setState({ aiBusy: true, msg: 'Composition de vos paliers…' });
    try {
      const out = await window.claude.complete(`Tu conseilles un commerce de proximité français : « ${txt} ». Propose 4 récompenses de fidélité réalistes et peu coûteuses pour les paliers 3, 6, 10 et 20 points, de valeur croissante. Réponds uniquement avec un tableau JSON de 4 chaînes en français, 28 caractères maximum chacune, sans emoji.`);
      const arr = JSON.parse(out.slice(out.indexOf('['), out.lastIndexOf(']') + 1));
      const tiers = [3, 6, 10, 20].map((n, i) => ({ n, label: String(arr[i] || this.TIERS[i].label).slice(0, 30) }));
      const name = txt.charAt(0).toUpperCase() + txt.slice(1);
      this.SHOPS = this.SHOPS.map((s, i) => i === this.state.shop ? name.slice(0, 22) : s);
      this.setState({ tiers, aiBusy: false, msg: 'Paliers proposés pour votre commerce.' });
    } catch (e) { this.setState({ aiBusy: false, msg: 'Impossible de proposer des paliers pour le moment.' }); }
  }

  renderVals() {
    const st = this.state, tiers = st.tiers || this.TIERS;
    return {
      rootRef: this.rootRef, stageRef: this.stageRef, spacerRef: this.spacerRef,
      debugOn: st.debugOn, debugText: st.debugText,
      fallback: st.fallback, hasVideo: !!this.props.fallbackVideo, fallbackVideo: this.props.fallbackVideo || '',
      stills: !this.props.fallbackStills ? [] : this.NAMES.map((n, i) => ({ src: `img/scene-${String(i + 1).padStart(2, '0')}.jpg`, alt: `Scène ${i + 1} — ${n}` })),
      spacerH: `${this.N * 115}vh`, nameDisplay: st.narrow ? 'none' : 'inline',
      ticks: this.NAMES.map((n, i) => ({ label: `Scène ${i + 1} — ${n}`, go: () => this.goTo(i) })),
      goDemo: () => this.goTo(4),
      shops: this.SHOP_LABELS.map((l, i) => ({ label: l, pick: () => this.setState({ shop: i }), bg: i === st.shop ? '#f4efe6' : 'transparent', color: i === st.shop ? '#1a1007' : '#d6cdbf', border: i === st.shop ? '#f4efe6' : 'rgba(244,239,230,.2)' })),
      tierRows: tiers.map(t => { const on = st.points >= t.n; return { n: t.n, label: t.label, status: on ? 'Débloqué' : `encore ${t.n - st.points}`, numColor: on ? '#f2a65a' : '#8f877a', labelColor: on ? '#f4efe6' : '#b3aa9c' }; }),
      points: st.points, msg: st.msg, msgColor: /débloqu/i.test(st.msg) ? '#f2a65a' : '#a39a8c',
      scan: () => { if (this.film) this.film.scan(() => this.addPoint()); else this.addPoint(); },
      reset: () => this.setState({ points: 0, msg: 'Carte remise à zéro.' }),
      aiText: st.aiText, aiLabel: st.aiBusy ? '…' : 'Suggérer', onAiText: e => this.setState({ aiText: e.target.value }), onAiKey: e => { if (e.key === 'Enter') this.askAi(); }, askAi: () => this.askAi(),
      faq: this.FAQ.map((f, i) => ({ ...f, open: st.open === i, rot: st.open === i ? '45deg' : '0deg', toggle: () => this.setState({ open: st.open === i ? -1 : i }) })),
    };
  }
}
</script>
</body>
</html>
