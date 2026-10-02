// Fidelo — scroll-driven cinematic scene. One set, one card, one camera.
import * as THREE from './vendor/three.module.js';
import * as TX from './fidelo-textures.js';

const PI = Math.PI, TAU = PI * 2;
const cl = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
const ss = (a, b, x) => { x = cl((x - a) / (b - a)); return x * x * (3 - 2 * x); };
const L = (a, b, t) => a + (b - a) * t;
const LA = (a, b, t) => a.map((v, i) => L(v, b[i], t));

// One keyframe per scene: camera, card pose, scanner pose and object states.
const REST_SCAN = { sp: [1.5, 0.36, -0.45], sr: [-0.32, -0.55, 0] };
const K = [
  { cam: [3.3, 2.5, 4.9], tgt: [0.1, 0.3, 0], ap: .55, cp: [0, .014, .25], cr: [-PI / 2, 0, .18], morph: 0, beam: 0, coins: 0, fan: 0, ex: 1, ...REST_SCAN },
  { cam: [1.55, .42, 1.55], tgt: [.7, .03, .45], ap: 1.5, cp: [.85, .014, .5], cr: [-PI / 2, 0, -.55], morph: 0, beam: 0, coins: 0, fan: 0, ex: .9, ...REST_SCAN },
  { cam: [0, 1.25, 3.0], tgt: [0, 1.15, 0], ap: .9, cp: [0, 1.15, 0], cr: [.04, TAU, 0], morph: 1, beam: 0, coins: 0, fan: 0, ex: 1.05, ...REST_SCAN },
  { cam: [2.35, 1.55, 2.2], tgt: [-.2, 1.1, 0], ap: 1, cp: [-.35, 1.12, .05], cr: [0, TAU + .45, .02], morph: 1, beam: 1, coins: 0, fan: 0, ex: 1, sp: [.95, 1.12, .7], sr: [0, 1.12, 0] },
  { cam: [0, 1.95, 3.5], tgt: [0, .7, -.45], ap: .55, cp: [0, 1.55, -.05], cr: [-.12, TAU, 0], morph: 1, beam: 0, coins: 1, fan: 0, ex: 1, ...REST_SCAN },
  { cam: [-3.0, .85, 2.3], tgt: [0, .95, 0], ap: 1.6, cp: [0, 1.0, 0], cr: [.08, TAU - .55, -.05], morph: 1, beam: 0, coins: 0, fan: 0, ex: .95, ...REST_SCAN },
  { cam: [0, 1.3, 3.25], tgt: [0, 1.2, 0], ap: .45, cp: [0, 1.22, 0], cr: [0, TAU, 0], morph: 1, beam: 0, coins: 0, fan: 1, ex: 1, ...REST_SCAN },
  { cam: [.25, 4.1, 1.35], tgt: [0, 0, .1], ap: .7, cp: [0, .014, .2], cr: [-PI / 2, TAU, .22], morph: 1, beam: 0, coins: 0, fan: 0, ex: .95, ...REST_SCAN },
  { cam: [0, 2.3, 8.2], tgt: [0, .7, 0], ap: .3, cp: [0, .014, .2], cr: [-PI / 2, TAU, .22], morph: 1, beam: 0, coins: 0, fan: 0, ex: .8, ...REST_SCAN },
];
const N = K.length;
const PLANS = [
  { name: 'Découverte', price: '0 €', unit: '', line: 'Jusqu’à 30 clients', line2: 'Sans carte bancaire' },
  { name: 'Mensuel', price: '29 €', unit: '/ mois', line: 'Clients illimités', line2: 'Sans engagement' },
  { name: 'Annuel', price: '250 €', unit: '/ an', line: 'Soit 20,83 € / mois', line2: '98 € économisés', badge: 'Le plus choisi', hi: true },
  { name: 'À vie + site', price: '525 €', unit: 'une fois', line: 'Site web pro offert', line2: 'puis 50 € / an (domaine)' },
];
const STACK_X = [-1.05, -.35, .35, 1.05], STACK_N = [3, 6, 10, 20];

function rrShape(w, h, r) { const s = new THREE.Shape(), x = -w / 2, y = -h / 2; s.moveTo(x + r, y); s.lineTo(x + w - r, y); s.quadraticCurveTo(x + w, y, x + w, y + r); s.lineTo(x + w, y + h - r); s.quadraticCurveTo(x + w, y + h, x + w - r, y + h); s.lineTo(x + r, y + h); s.quadraticCurveTo(x, y + h, x, y + h - r); s.lineTo(x, y + r); s.quadraticCurveTo(x, y, x + r, y); return s; }
function faceGeo(w, h, r) { const g = new THREE.ShapeGeometry(rrShape(w, h, r), 10), p = g.attributes.position, uv = g.attributes.uv; for (let i = 0; i < p.count; i++) uv.setXY(i, (p.getX(i) + w / 2) / w, (p.getY(i) + h / 2) / h); return g; }
function canvasTex(w, h, draw) { const cv = document.createElement('canvas'); cv.width = w; cv.height = h; draw(cv); const t = new THREE.CanvasTexture(cv); t.colorSpace = THREE.SRGBColorSpace; t.anisotropy = 8; t.userData.cv = cv; return t; }

export async function createFilm(el, opts = {}) {
  try { await Promise.all(['64px Gloock', '400 30px Geist', '500 24px "Geist Mono"'].map(f => document.fonts.load(f))); } catch (e) {}
  const renderer = new THREE.WebGLRenderer({ antialias: false, preserveDrawingBuffer: true, powerPreference: 'high-performance' });
  const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
  renderer.setPixelRatio(dpr); renderer.shadowMap.enabled = true; renderer.shadowMap.type = THREE.PCFSoftShadowMap;
  renderer.toneMapping = THREE.NoToneMapping;
  el.appendChild(renderer.domElement); Object.assign(renderer.domElement.style, { position: 'absolute', inset: 0, width: '100%', height: '100%', display: 'block' });

  const scene = new THREE.Scene(); scene.background = new THREE.Color('#090705'); scene.fog = new THREE.FogExp2('#0b0806', .055);
  const camera = new THREE.PerspectiveCamera(34, 1, .05, 60);

  // Environment for reflections: dark room, one warm softbox, one cool strip.
  const pm = new THREE.PMREMGenerator(renderer), envS = new THREE.Scene();
  envS.add(new THREE.Mesh(new THREE.SphereGeometry(10, 16, 8), new THREE.MeshBasicMaterial({ color: '#070504', side: THREE.BackSide })));
  const panel = (c, i, p, s) => { const m = new THREE.Mesh(new THREE.PlaneGeometry(...s), new THREE.MeshBasicMaterial({ color: new THREE.Color(c).multiplyScalar(i), side: THREE.DoubleSide })); m.position.set(...p); m.lookAt(0, 0, 0); envS.add(m); };
  panel('#ffb36b', 7, [0, 6, 1], [5, 3]); panel('#6f9fd6', .7, [-7, 2, -3], [1, 5]); panel('#ff9a50', 1.4, [6, 1, 4], [3, 2]);
  scene.environment = pm.fromScene(envS, .03).texture; scene.environmentIntensity = .3;

  // Light: one dominant warm pendant, cool rim, faint fill.
  const spot = new THREE.SpotLight('#ffb574', 34, 12, .62, .75, 1.35); spot.position.set(0, 3.12, .15); spot.target.position.set(0, 0, .15);
  spot.castShadow = true; spot.shadow.mapSize.set(2048, 2048); spot.shadow.bias = -.0003; spot.shadow.radius = 5; spot.shadow.camera.near = .5; spot.shadow.camera.far = 8;
  scene.add(spot, spot.target);
  const rim = new THREE.DirectionalLight('#7fa6d8', .32); rim.position.set(-4, 3, -5); scene.add(rim);
  scene.add(new THREE.HemisphereLight('#4a3220', '#050403', .22));

  // Counter: terrazzo top, fluted walnut front.
  const stone = canvasTex(1024, 1024, TX.drawStone); stone.wrapS = stone.wrapT = THREE.RepeatWrapping; stone.repeat.set(3, 1);
  const top = new THREE.Mesh(new THREE.BoxGeometry(8, .12, 2.6), new THREE.MeshStandardMaterial({ name: 'terrazzo', map: stone, roughness: .32, metalness: 0 }));
  top.position.set(0, -.06, 0); top.receiveShadow = true; scene.add(top);
  const walnut = new THREE.MeshStandardMaterial({ name: 'walnut', color: '#3a2416', roughness: .55 });
  const flute = new THREE.InstancedMesh(new THREE.CylinderGeometry(.045, .045, 1.05, 12, 1, false, 0, PI), walnut, 88);
  const d = new THREE.Object3D(); for (let i = 0; i < 88; i++) { d.position.set(-3.96 + i * .091, -.64, 1.26); d.rotation.set(0, -PI / 2, 0); d.updateMatrix(); flute.setMatrixAt(i, d.matrix); } flute.receiveShadow = true; scene.add(flute);
  const floor = new THREE.Mesh(new THREE.PlaneGeometry(40, 40), new THREE.MeshStandardMaterial({ color: '#0e0a07', roughness: .8 })); floor.rotation.x = -PI / 2; floor.position.y = -1.18; floor.receiveShadow = true; scene.add(floor);

  // Back shelves, jars and out-of-focus shop lights.
  const wall = new THREE.Mesh(new THREE.PlaneGeometry(18, 8), new THREE.MeshStandardMaterial({ color: '#15100b', roughness: .9 })); wall.position.set(0, 2, -4.4); scene.add(wall);
  const oak = new THREE.MeshStandardMaterial({ color: '#4a3020', roughness: .6 });
  const glass = new THREE.MeshPhysicalMaterial({ color: '#c9b79a', roughness: .08, metalness: 0, transmission: 0, transparent: true, opacity: .5, envMapIntensity: 1.6 });
  for (let k = 0; k < 3; k++) { const sh = new THREE.Mesh(new THREE.BoxGeometry(7, .06, .5), oak); sh.position.set(0, .75 + k * .85, -4.1); scene.add(sh);
    for (let j = 0; j < 9; j++) { const h = .22 + ((j * 7 + k * 3) % 5) * .06; const jar = new THREE.Mesh(new THREE.CylinderGeometry(.09, .09, h, 24), glass); jar.position.set(-3 + j * .74 + (k % 2) * .3, .78 + k * .85 + h / 2, -4.05); scene.add(jar); } }
  const bok = new THREE.Group(); for (let i = 0; i < 46; i++) { const warm = i % 5 !== 0; const m = new THREE.Mesh(new THREE.SphereGeometry(.035 + (i % 4) * .012, 10, 8), new THREE.MeshBasicMaterial({ color: new THREE.Color(warm ? '#ffb46a' : '#8fb4e0').multiplyScalar(warm ? 5 : 3), fog: false })); m.position.set(-6 + (i * 2.71) % 12, .4 + (i * 1.37) % 3.4, -3.3 - (i * .53) % 3); bok.add(m); } scene.add(bok);

  // Pendant lamp + volumetric cone.
  const lamp = new THREE.Group(); lamp.position.set(0, 3.3, .15);
  const shade = new THREE.Mesh(new THREE.CylinderGeometry(.1, .42, .36, 48, 1, true), new THREE.MeshStandardMaterial({ name: 'brass', color: '#8a6a45', metalness: 1, roughness: .3, side: THREE.DoubleSide }));
  const bulb = new THREE.Mesh(new THREE.SphereGeometry(.09, 24, 16), new THREE.MeshBasicMaterial({ color: new THREE.Color('#ffd09a').multiplyScalar(14) }));
  bulb.position.y = -.13; const cord = new THREE.Mesh(new THREE.CylinderGeometry(.006, .006, 6, 6), new THREE.MeshBasicMaterial({ color: '#111' })); cord.position.y = 3.18;
  lamp.add(shade, bulb, cord); scene.add(lamp);
  const coneMat = new THREE.ShaderMaterial({ transparent: true, depthWrite: false, blending: THREE.AdditiveBlending, side: THREE.DoubleSide, uniforms: { uOp: { value: .16 }, uCol: { value: new THREE.Color('#ffae66') } },
    vertexShader: 'varying float vH;varying vec3 vN;varying vec3 vV;void main(){vH=uv.y;vec4 mv=modelViewMatrix*vec4(position,1.);vN=normalize(normalMatrix*normal);vV=normalize(-mv.xyz);gl_Position=projectionMatrix*mv;}',
    fragmentShader: 'uniform float uOp;uniform vec3 uCol;varying float vH;varying vec3 vN;varying vec3 vV;void main(){float f=pow(abs(dot(vN,vV)),2.2);float a=uOp*f*smoothstep(0.,.9,vH)*(.35+.65*vH);gl_FragColor=vec4(uCol*a,1.);}' });
  const cone = new THREE.Mesh(new THREE.CylinderGeometry(.1, 1.9, 3.2, 64, 1, true), coneMat); cone.position.set(0, 3.2 - 1.6, .15); scene.add(cone);

  // Dust in the light.
  const DN = window.innerWidth < 700 ? 350 : 800, dp = new Float32Array(DN * 3), dseed = new Float32Array(DN);
  for (let i = 0; i < DN; i++) { const a = Math.random() * TAU, r = Math.sqrt(Math.random()) * 1.7; dp[i * 3] = Math.cos(a) * r; dp[i * 3 + 1] = Math.random() * 3; dp[i * 3 + 2] = Math.sin(a) * r + .15; dseed[i] = Math.random() * 100; }
  const dg = new THREE.BufferGeometry(); dg.setAttribute('position', new THREE.BufferAttribute(dp, 3));
  const soft = canvasTex(64, 64, TX.drawSoft); soft.colorSpace = THREE.NoColorSpace;
  const dust = new THREE.Points(dg, new THREE.PointsMaterial({ color: new THREE.Color('#ffc890').multiplyScalar(1.6), size: .022, map: soft, transparent: true, depthWrite: false, blending: THREE.AdditiveBlending, opacity: .75 }));
  scene.add(dust); const dBase = dp.slice();

  // The card: paper → digital morph on one face via shader sweep.
  const CW = 1.7, CH = 1.07, CD = .018, CR = .09;
  const paper = canvasTex(1024, 644, TX.drawPaper);
  const demo = { points: 4, shop: 'Café Lumière', tiers: TX.TIERS };
  const digital = canvasTex(1024, 644, cv => TX.drawDigital(cv, demo));
  const backT = canvasTex(1024, 644, TX.drawBack);
  const U = { uMapB: { value: digital }, uMorph: { value: 0 }, uGlow: { value: .55 }, uFlash: { value: 0 } };
  const faceMat = new THREE.MeshStandardMaterial({ name: 'card_face', map: paper, roughness: .85, metalness: 0 });
  faceMat.onBeforeCompile = sh => {
    Object.assign(sh.uniforms, U);
    sh.fragmentShader = 'uniform sampler2D uMapB;uniform float uMorph;uniform float uGlow;uniform float uFlash;\n' + sh.fragmentShader
      .replace('#include <map_fragment>', `vec4 tA=texture2D(map,vMapUv);vec4 tB=texture2D(uMapB,vMapUv);
        float nz=fract(sin(dot(floor(vMapUv*48.),vec2(12.9898,78.233)))*43758.5453);
        float e=vMapUv.x*.8+(1.-vMapUv.y)*.2+(nz-.5)*.05; float th=uMorph*1.3-.15;
        float mm=smoothstep(e-.012,e+.012,th); float edge=(1.-smoothstep(0.,.035,abs(e-th)))*step(.001,uMorph)*step(uMorph,.999);
        diffuseColor*=mix(tA,tB,mm);`)
      .replace('#include <emissivemap_fragment>', 'totalEmissiveRadiance+=tB.rgb*mm*(uGlow+uFlash)+vec3(1.,.6,.28)*edge*4.;');
  };
  const bodyMat = new THREE.MeshStandardMaterial({ name: 'card_body', color: '#a07c56', roughness: .8, metalness: 0 });
  const backMat = new THREE.MeshStandardMaterial({ name: 'card_back', map: backT, roughness: .35, metalness: .2 });
  const bodyGeo = new THREE.ExtrudeGeometry(rrShape(CW, CH, CR), { depth: CD, bevelEnabled: false, curveSegments: 10 }); bodyGeo.translate(0, 0, -CD / 2);
  const fGeo = faceGeo(CW, CH, CR);
  function makeCard(fm, bm) { const g = new THREE.Group(), b = new THREE.Mesh(bodyGeo, bodyMat); b.castShadow = true; const f = new THREE.Mesh(fGeo, fm); f.position.z = CD / 2 + .0012; const k = new THREE.Mesh(fGeo, bm); k.rotation.y = PI; k.position.z = -CD / 2 - .0012; g.add(b, f, k); return g; }
  const card = makeCard(faceMat, backMat); scene.add(card);
  const kraft = new THREE.Color('#b8946a'), obs = new THREE.Color('#17120e');

  // Plan cards for the tariff scene.
  const fan = new THREE.Group(); scene.add(fan);
  const planCards = PLANS.map(p => { const t = canvasTex(1024, 644, cv => TX.drawPlan(cv, p)); const c = makeCard(new THREE.MeshStandardMaterial({ map: t, roughness: .4, emissiveMap: t, emissive: new THREE.Color('#ffffff'), emissiveIntensity: .35 }), backMat); c.scale.setScalar(.62); fan.add(c); return c; });

  // Merchant scanner phone + beam.
  const scanner = new THREE.Group(); scene.add(scanner);
  const SW = .44, SH = .9, sGeo = new THREE.ExtrudeGeometry(rrShape(SW, SH, .06), { depth: .04, bevelEnabled: false, curveSegments: 10 }); sGeo.translate(0, 0, -.02);
  const sBody = new THREE.Mesh(sGeo, new THREE.MeshStandardMaterial({ name: 'phone', color: '#1a1714', metalness: .6, roughness: .28 })); sBody.castShadow = true;
  const sTex = canvasTex(512, 1024, TX.drawScanner);
  const sScreen = new THREE.Mesh(faceGeo(SW - .03, SH - .03, .05), new THREE.MeshStandardMaterial({ map: sTex, emissiveMap: sTex, emissive: new THREE.Color('#fff'), emissiveIntensity: .9, roughness: .2 })); sScreen.position.z = .0215;
  const beamMat = new THREE.ShaderMaterial({ transparent: true, depthWrite: false, blending: THREE.AdditiveBlending, side: THREE.DoubleSide, uniforms: { uOp: { value: 0 }, uT: { value: 0 } },
    vertexShader: 'varying vec2 vU;void main(){vU=uv;gl_Position=projectionMatrix*modelViewMatrix*vec4(position,1.);}',
    fragmentShader: 'uniform float uOp;uniform float uT;varying vec2 vU;void main(){float line=smoothstep(.06,0.,abs(fract(vU.y*1.-uT)-.5)*.2);float a=uOp*(.25+line*1.2)*(1.-vU.y*.3);gl_FragColor=vec4(vec3(1.,.72,.42)*a,1.);}' });
  const beam = new THREE.Mesh(new THREE.CylinderGeometry(.03, .5, 1.3, 4, 1, true, PI / 4), beamMat); beam.rotation.x = PI / 2; beam.position.z = -.68;
  scanner.add(sBody, sScreen, beam);

  // Reward coins (instanced) + one flying coin for the demo.
  const coinGeo = new THREE.CylinderGeometry(.115, .115, .022, 40), coinMat = new THREE.MeshStandardMaterial({ name: 'gold', color: '#ffffff', metalness: 1, roughness: .26 });
  const CN = STACK_N.reduce((a, b) => a + b, 0), coins = new THREE.InstancedMesh(coinGeo, coinMat, CN); coins.castShadow = true; scene.add(coins);
  const slots = []; STACK_N.forEach((n, s) => { for (let i = 0; i < n; i++) slots.push({ s, p: new THREE.Vector3(STACK_X[s], .011 + i * .023, -.55) }); });
  const gOn = new THREE.Color('#f5c47f'), gOff = new THREE.Color('#6b4f36');
  function tintCoins() { slots.forEach((sl, i) => coins.setColorAt(i, demo.points >= STACK_N[sl.s] ? gOn : gOff)); coins.instanceColor.needsUpdate = true; }
  tintCoins();
  const fly = new THREE.Mesh(coinGeo, new THREE.MeshStandardMaterial({ color: '#f5c47f', metalness: 1, roughness: .2, emissive: '#ff9a40', emissiveIntensity: .6 })); fly.visible = false; scene.add(fly);
  let flyT = -1, flyFrom = new THREE.Vector3(), flyCb = null, flash = 0;

  // Post: render to HDR target, then DOF + ACES + vignette + grain.
  const rt = new THREE.WebGLRenderTarget(2, 2, { type: THREE.HalfFloatType, depthTexture: new THREE.DepthTexture(2, 2) });
  const post = new THREE.ShaderMaterial({ uniforms: { tC: { value: rt.texture }, tD: { value: rt.depthTexture }, res: { value: new THREE.Vector2() }, focus: { value: 4 }, ap: { value: .5 }, nearF: { value: camera.near }, farF: { value: camera.far }, time: { value: 0 }, ex: { value: 1 } },
    vertexShader: 'varying vec2 vUv;void main(){vUv=uv;gl_Position=vec4(position.xy,0.,1.);}',
    fragmentShader: `uniform sampler2D tC;uniform sampler2D tD;uniform vec2 res;uniform float focus,ap,nearF,farF,time,ex;varying vec2 vUv;
      float lin(float d){float z=d*2.-1.;return 2.*nearF*farF/(farF+nearF-z*(farF-nearF));}
      float coc(float d){return clamp(abs(d-focus)/max(d,.001)*ap,0.,1.);}
      vec3 aces(vec3 x){return clamp((x*(2.51*x+.03))/(x*(2.43*x+.59)+.14),0.,1.);}
      void main(){float d=lin(texture2D(tD,vUv).r);float R=res.y*.014;float r=coc(d)*R;vec3 acc=vec3(0.);float w=0.;
        for(int i=0;i<32;i++){float fi=float(i);float a=fi*2.39996;float rr=sqrt((fi+.5)/32.)*r;vec2 o=vec2(cos(a),sin(a))*rr/res;
          vec3 c=texture2D(tC,vUv+o).rgb;float sc=coc(lin(texture2D(tD,vUv+o).r))*R;float ww=(i==0)?1.:smoothstep(rr-1.,rr+.5,sc+.5);
          ww*=1.+min(dot(c,vec3(.3,.59,.11)),4.)*.9;acc+=c*ww;w+=ww;}
        vec3 col=aces(acc/max(w,.0001)*ex);col=pow(col,vec3(1./2.2));vec2 cc=vUv-.5;
        col*=mix(.28,1.,smoothstep(.95,.2,length(cc*vec2(1.15,1.))));
        float g=fract(sin(dot(vUv*res+fract(time)*97.,vec2(12.9898,78.233)))*43758.5453);col+=(g-.5)*.028;
        gl_FragColor=vec4(col,1.);}` });
  const pScene = new THREE.Scene(), pCam = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1); pScene.add(new THREE.Mesh(new THREE.PlaneGeometry(2, 2), post));

  function resize() { const w = el.clientWidth || innerWidth, h = el.clientHeight || innerHeight; renderer.setSize(w, h, false); camera.aspect = w / h; camera.fov = w / h < .8 ? 48 : 34; camera.updateProjectionMatrix(); const pw = Math.floor(w * dpr), ph = Math.floor(h * dpr); rt.setSize(pw, ph); rt.depthTexture.image.width = pw; rt.depthTexture.image.height = ph; post.uniforms.res.value.set(pw, ph); }
  resize(); addEventListener('resize', resize);
  const mouse = { x: 0, y: 0, tx: 0, ty: 0 }; const onMove = e => { mouse.tx = e.clientX / innerWidth - .5; mouse.ty = e.clientY / innerHeight - .5; }; addEventListener('pointermove', onMove);

  let target = 0, cur = 0, last = performance.now(), raf = 0, frames = 0, slowAcc = 0, alive = true;
  const cam = new THREE.Vector3(), tg = new THREE.Vector3(), cw = new THREE.Vector3();
  function sample(s) { const i = Math.min(N - 2, Math.floor(s)), f = ss(.12, .88, s - i), a = K[i], b = K[i + 1], o = {}; for (const k in a) o[k] = Array.isArray(a[k]) ? LA(a[k], b[k], f) : L(a[k], b[k], f); o.i = i; o.f = s - i; return o; }

  function frame(now) {
    if (!alive) return; raf = requestAnimationFrame(frame);
    const dt = Math.min(.1, (now - last) / 1000); last = now; const t = now / 1000;
    if (frames < 120) { frames++; if (frames > 30) slowAcc += dt; if (frames === 120 && slowAcc / 90 > .05 && opts.onSlow) opts.onSlow(); }
    cur += (target - cur) * Math.min(1, dt * 3.2); mouse.x += (mouse.tx - mouse.x) * dt * 2; mouse.y += (mouse.ty - mouse.y) * dt * 2;
    const k = sample(cl(cur, 0, N - 1));
    cam.set(...k.cam); tg.set(...k.tgt);
    cam.x += Math.sin(t * .31) * .04 + mouse.x * .35; cam.y += Math.sin(t * .23) * .03 - mouse.y * .2;
    camera.position.copy(cam); camera.lookAt(tg);
    const bob = ss(.013, .3, k.cp[1]) * .025;
    card.position.set(k.cp[0], k.cp[1] + Math.sin(t * 1.1) * bob, k.cp[2]); card.rotation.set(k.cr[0] + Math.sin(t * .7) * bob, k.cr[1], k.cr[2]);
    U.uMorph.value = k.morph; bodyMat.color.copy(kraft).lerp(obs, ss(.3, .7, k.morph)); bodyMat.roughness = L(.8, .3, k.morph); bodyMat.metalness = L(0, .5, k.morph); faceMat.roughness = L(.85, .3, k.morph);
    flash = Math.max(0, flash - dt * 1.6); U.uFlash.value = flash;
    scanner.position.set(...k.sp); scanner.rotation.set(...k.sr);
    const beamLocal = k.i === 3 ? ss(0, .35, k.f) * (1 - ss(.75, 1, k.f)) : 0;
    beamMat.uniforms.uOp.value = Math.max(beamLocal, k.beam * .5) * (.8 + .2 * Math.sin(t * 18)); beamMat.uniforms.uT.value = t * .9;
    // scroll-driven point in the checkout scene
    const scrollCoin = k.i === 3 ? ss(.45, .8, k.f) : (k.i === 2 && k.f > .99 ? 0 : 0);
    if (flyT < 0) { if (scrollCoin > 0 && scrollCoin < 1) { fly.visible = true; scanner.getWorldPosition(flyFrom); card.getWorldPosition(cw); fly.position.lerpVectors(flyFrom, cw, scrollCoin); fly.position.y += Math.sin(scrollCoin * PI) * .35; fly.rotation.set(PI / 2 + t * 6, 0, 0); } else fly.visible = false; }
    else { flyT += dt / .9; card.getWorldPosition(cw); const e = ss(0, 1, flyT); fly.visible = true; fly.position.lerpVectors(flyFrom, cw, e); fly.position.y += Math.sin(e * PI) * .5; fly.rotation.set(PI / 2 + t * 8, t * 3, 0); if (flyT >= 1) { flyT = -1; fly.visible = false; flash = 1.2; flyCb && flyCb(); } }
    // coins rise from the card to the stacks
    card.getWorldPosition(cw); const ct = k.coins;
    slots.forEach((sl, i) => { const ti = cl(ct * 1.7 - (i / CN) * .7); const e = ss(0, 1, ti); d.position.lerpVectors(cw, sl.p, e); d.position.y += Math.sin(e * PI) * .6; d.rotation.set(e < 1 ? t * 4 + i : 0, 0, 0); d.scale.setScalar(ti <= 0 ? .0001 : 1); d.updateMatrix(); coins.setMatrixAt(i, d.matrix); });
    coins.instanceMatrix.needsUpdate = true;
    // fan of plan cards
    const fnv = k.fan; fan.visible = fnv > .04; card.visible = !fan.visible;
    planCards.forEach((c, j) => { const o = j - 1.5; c.position.set(k.cp[0] + o * .64 * fnv, k.cp[1] - Math.abs(o) * .07 * fnv + (j === 2 ? .06 * fnv : 0), k.cp[2] + (j === 2 ? .05 : -Math.abs(o) * .03) * fnv); c.rotation.set(0, -o * .12 * fnv, -o * .09 * fnv); c.scale.setScalar(L(1, .62, fnv)); });
    // dust drift
    for (let i = 0; i < DN; i++) { const s = dseed[i]; dp[i * 3] = dBase[i * 3] + Math.sin(t * .2 + s) * .08; dp[i * 3 + 1] = (dBase[i * 3 + 1] + t * .03 + Math.sin(t * .3 + s) * .05) % 3; dp[i * 3 + 2] = dBase[i * 3 + 2] + Math.cos(t * .17 + s) * .08; }
    dg.attributes.position.needsUpdate = true;
    // light flicker + focus
    spot.intensity = 52 * k.ex * (1 + Math.sin(t * 13) * .006); coneMat.uniforms.uOp.value = .15 * k.ex;
    const fp = (fan.visible ? planCards[2] : card).getWorldPosition(cw); post.uniforms.focus.value = camera.position.distanceTo(fp); post.uniforms.ap.value = k.ap; post.uniforms.time.value = t; post.uniforms.ex.value = k.ex;
    renderer.setRenderTarget(rt); renderer.render(scene, camera); renderer.setRenderTarget(null); renderer.render(pScene, pCam);
    if (opts.onFrame) opts.onFrame(cur);
  }
  raf = requestAnimationFrame(frame);

  return {
    N,
    setTarget(s) { target = cl(s, 0, N - 1); },
    jump(s) { target = cur = cl(s, 0, N - 1); },
    setDemo(p) { Object.assign(demo, p); TX.drawDigital(digital.userData.cv, demo); digital.needsUpdate = true; tintCoins(); },
    scan(cb) { if (flyT >= 0) return; flyFrom.set(camera.position.x * .4 + .9, 2.6, 1.6); flyT = 0; flyCb = cb; },
    destroy() { alive = false; cancelAnimationFrame(raf); removeEventListener('resize', resize); removeEventListener('pointermove', onMove); renderer.dispose(); el.contains(renderer.domElement) && el.removeChild(renderer.domElement); },
  };
}
