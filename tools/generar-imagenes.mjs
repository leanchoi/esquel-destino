#!/usr/bin/env node
/**
 * Genera el set de ilustraciones del sitio (SVG).
 *
 * Estilo: afiche de parque nacional — color plano, siluetas, paleta acotada.
 * Son ilustraciones propias, pensadas para ocupar los mismos encuadres que
 * van a ocupar las fotos reales cuando estén. Ver docs/FOTOS-QUE-NECESITAMOS.md
 * para el brief de cada una.
 *
 *   node tools/generar-imagenes.mjs
 */
import fs from 'node:fs';
import path from 'node:path';

const OUT = path.join(process.cwd(), 'assets/images/ilustraciones');
fs.mkdirSync(OUT, { recursive: true });

// ---------------------------------------------------------------- paleta ---
const P = {
  sky1: '#F3EAE0',
  sky2: '#E6DDD3',
  skyDusk1: '#F6E3E4',
  skyDusk2: '#E8D6CF',
  far: '#CBC3BA',
  mid: '#A49A90',
  near: '#71675D',
  deep: '#2E2A26',
  ink: '#221F1C',
  berry: '#AB2759',
  berryLt: '#C95480',
  green: '#236F4C',
  greenLt: '#3E9169',
  greenPale: '#7FA98D',
  wheat: '#C9A24B',
  cream: '#F7F3EC',
  wood: '#8A6242',
  woodLt: '#A97C53',
};

// --------------------------------------------------------------- helpers ---
const rad = (d) => (d * Math.PI) / 180;

/**
 * Cadena montañosa determinista.
 * Pocos picos, grandes y asimétricos: una sierra de dientes iguales se lee
 * como patrón, no como cordillera.
 */
function ridge(w, baseY, height, seed, wobble = 1) {
  let s = seed;
  const rnd = () => {
    s = (s * 1103515245 + 12345) & 0x7fffffff;
    return s / 0x7fffffff;
  };
  const pts = [[-20, baseY]];
  let x = -20;
  while (x < w) {
    // Ancho de la ladera: variación amplia para que ningún pico repita al otro.
    const span = w * (0.16 + rnd() * 0.22);
    // Cumbre desplazada del centro: una ladera corta y otra larga.
    const skew = 0.28 + rnd() * 0.44;
    const peak = baseY - height * (0.4 + rnd() * 0.6) * wobble;
    pts.push([x + span * skew, peak]);
    // Silla entre picos, nunca vuelve del todo a la base.
    x += span;
    pts.push([x, baseY - height * (0.1 + rnd() * 0.3)]);
  }
  pts.push([w + 20, baseY]);
  return pts;
}

function ridgePath(pts, w, h) {
  const d = pts.map(([x, y], i) => `${i === 0 ? 'M' : 'L'}${x.toFixed(1)},${y.toFixed(1)}`).join(' ');
  return `${d} L${w + 20},${h} L-20,${h} Z`;
}

/** Nieve en la cumbre: triangulitos sobre los picos más altos. */
function snowCaps(pts, threshold) {
  return pts
    .filter(([, y], i) => i % 2 === 1 && y < threshold)
    .map(([x, y]) => `M${x - 13},${y + 17} L${x},${y} L${x + 13},${y + 17} L${x + 5},${y + 13} L${x - 2},${y + 18} L${x - 7},${y + 13} Z`)
    .join(' ');
}

/**
 * Figura humana estilizada de pie.
 * Sin rasgos faciales: silueta, como en la cartelería de parques.
 */
function person(x, y, scale = 1, color = P.deep, opts = {}) {
  const s = scale;
  const { armAngle = -20, lean = 0 } = opts;
  const headR = 9 * s;
  const bodyTop = y - 62 * s;
  const armY = y - 50 * s;
  const armLen = 26 * s;
  const ax = x + Math.cos(rad(armAngle)) * armLen;
  const ay = armY + Math.sin(rad(armAngle)) * armLen;
  return `
    <g transform="rotate(${lean} ${x} ${y})">
      <circle cx="${x}" cy="${bodyTop - headR - 2 * s}" r="${headR}" fill="${color}"/>
      <path d="M${x - 12 * s},${y} L${x - 10 * s},${bodyTop} Q${x},${bodyTop - 8 * s} ${x + 10 * s},${bodyTop} L${x + 12 * s},${y} Z" fill="${color}"/>
      <path d="M${x + 4 * s},${armY} L${ax},${ay}" stroke="${color}" stroke-width="${6 * s}" stroke-linecap="round" fill="none"/>
      <rect x="${x - 11 * s}" y="${y}" width="${8 * s}" height="${26 * s}" rx="${3 * s}" fill="${color}"/>
      <rect x="${x + 3 * s}" y="${y}" width="${8 * s}" height="${26 * s}" rx="${3 * s}" fill="${color}"/>
    </g>`;
}

/** Figura agachada (cosecha, esquila, trabajo a ras del suelo). */
function personCrouch(x, y, scale = 1, color = P.deep) {
  const s = scale;
  return `
    <g>
      <circle cx="${x + 6 * s}" cy="${y - 52 * s}" r="${9 * s}" fill="${color}"/>
      <path d="M${x - 6 * s},${y - 16 * s} Q${x - 2 * s},${y - 44 * s} ${x + 12 * s},${y - 42 * s} Q${x + 18 * s},${y - 26 * s} ${x + 12 * s},${y - 14 * s} Z" fill="${color}"/>
      <path d="M${x + 10 * s},${y - 34 * s} Q${x + 20 * s},${y - 22 * s} ${x + 15 * s},${y - 8 * s}" stroke="${color}" stroke-width="${5.5 * s}" stroke-linecap="round" fill="none"/>
      <path d="M${x - 6 * s},${y - 16 * s} L${x - 10 * s},${y} L${x + 16 * s},${y} L${x + 12 * s},${y - 14 * s} Z" fill="${color}"/>
    </g>`;
}

/** Grano sutil: evita el look de vector plano perfecto. */
const grain = `
  <filter id="grain">
    <feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="3" stitchTiles="stitch"/>
    <feColorMatrix type="saturate" values="0"/>
  </filter>`;

function grainOverlay(w, h, opacity = 0.055) {
  return `<rect width="${w}" height="${h}" filter="url(#grain)" opacity="${opacity}" style="mix-blend-mode:multiply"/>`;
}

function svg(w, h, body, defs = '') {
  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${w} ${h}" width="${w}" height="${h}" role="img">
  <defs>${grain}${defs}</defs>
  ${body}
  ${grainOverlay(w, h)}
</svg>\n`;
}

function write(name, content) {
  fs.writeFileSync(path.join(OUT, name), content);
  console.log('  ✓', name);
}

// ----------------------------------------------------------------- fondos ---
/** Cielo + cordillera en capas. Base de casi todas las escenas. */
function mountainBase(w, h, { horizon = h * 0.72, dusk = false, seed = 7 } = {}) {
  const c1 = dusk ? P.skyDusk1 : P.sky1;
  const c2 = dusk ? P.skyDusk2 : P.sky2;
  const far = ridge(w, horizon - h * 0.02, h * 0.42, seed, 1);
  const mid = ridge(w, horizon + h * 0.03, h * 0.3, seed * 3 + 11, 0.85);
  const near = ridge(w, horizon + h * 0.12, h * 0.2, seed * 7 + 29, 0.7);
  return `
  <rect width="${w}" height="${h}" fill="url(#sky${seed})"/>
  <defs>
    <linearGradient id="sky${seed}" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="${c1}"/><stop offset="1" stop-color="${c2}"/>
    </linearGradient>
  </defs>
  <path d="${ridgePath(far, w, h)}" fill="${P.far}"/>
  <path d="${snowCaps(far, horizon - h * 0.2)}" fill="${P.cream}" opacity="0.85"/>
  <path d="${ridgePath(mid, w, h)}" fill="${P.mid}"/>
  <path d="${ridgePath(near, w, h)}" fill="${P.near}"/>`;
}

// ================================================================ escenas ===

// 1 — HERO: panorámica de valle con dos figuras mirando la cordillera.
{
  const w = 1200, h = 900;
  const horizon = h * 0.6;
  const body = `
  ${mountainBase(w, h, { horizon, seed: 5 })}
  <!-- valle -->
  <path d="M0,${h * 0.76} Q${w * 0.35},${h * 0.7} ${w},${h * 0.78} L${w},${h} L0,${h} Z" fill="${P.green}" opacity="0.9"/>
  <!-- coníferas al pie de la ladera -->
  ${Array.from({ length: 26 }, (_, i) => {
    const x = 30 + i * 46 + ((i * 37) % 19);
    const th = 44 + ((i * 53) % 32);
    const y = h * 0.795 + ((i * 29) % 12);
    return `<path d="M${x},${y} L${x - th * 0.3},${y} L${x},${y - th} L${x + th * 0.3},${y} Z" fill="${P.ink}" opacity="0.45"/>`;
  }).join('')}
  <!-- pradera en primer plano, más clara: las figuras tienen que leerse -->
  <path d="M0,${h * 0.85} Q${w * 0.5},${h * 0.79} ${w},${h * 0.87} L${w},${h} L0,${h} Z" fill="#4E6B4F"/>
  <!-- dos personas grandes en primer plano mirando la cordillera -->
  ${person(w * 0.2, h * 0.985, 2.5, P.ink, { armAngle: -58 })}
  ${person(w * 0.31, h * 0.995, 2.2, P.berry, { armAngle: 18 })}`;
  write('hero-valle.svg', svg(w, h, body));
}

// 2 — CASA DE TÉ / gastronomía: interior, mesa servida, ventana a la montaña.
{
  const w = 800, h = 500;
  const body = `
  <rect width="${w}" height="${h}" fill="${P.cream}"/>
  <!-- pared -->
  <rect width="${w}" height="${h}" fill="#EFE4D8"/>
  <!-- ventana con montaña -->
  <rect x="${w * 0.52}" y="${h * 0.1}" width="${w * 0.4}" height="${h * 0.46}" rx="6" fill="${P.sky1}"/>
  <path d="M${w * 0.52},${h * 0.5} L${w * 0.63},${h * 0.26} L${w * 0.71},${h * 0.42} L${w * 0.8},${h * 0.2} L${w * 0.92},${h * 0.5} Z" fill="${P.mid}"/>
  <path d="M${w * 0.75},${h * 0.31} L${w * 0.8},${h * 0.2} L${w * 0.85},${h * 0.31} L${w * 0.81},${h * 0.29} L${w * 0.78},${h * 0.33} Z" fill="${P.cream}"/>
  <rect x="${w * 0.52}" y="${h * 0.1}" width="${w * 0.4}" height="${h * 0.46}" rx="6" fill="none" stroke="${P.wood}" stroke-width="7"/>
  <line x1="${w * 0.72}" y1="${h * 0.1}" x2="${w * 0.72}" y2="${h * 0.56}" stroke="${P.wood}" stroke-width="5"/>
  <!-- mesa -->
  <rect x="0" y="${h * 0.68}" width="${w}" height="${h * 0.32}" fill="${P.wood}"/>
  <rect x="0" y="${h * 0.68}" width="${w}" height="10" fill="${P.woodLt}"/>
  <!-- tetera -->
  <ellipse cx="${w * 0.24}" cy="${h * 0.6}" rx="52" ry="42" fill="${P.berry}"/>
  <path d="M${w * 0.24 - 50},${h * 0.58} q-34,-6 -30,18 q2,16 22,10" fill="none" stroke="${P.berry}" stroke-width="9" stroke-linecap="round"/>
  <path d="M${w * 0.24 + 44},${h * 0.55} q26,-16 30,4" fill="none" stroke="${P.berry}" stroke-width="9" stroke-linecap="round"/>
  <rect x="${w * 0.24 - 14}" y="${h * 0.6 - 50}" width="28" height="12" rx="5" fill="${P.berryLt}"/>
  <!-- tazas -->
  <path d="M${w * 0.37},${h * 0.62} h44 a4,4 0 0 1 4,4 v14 a22,22 0 0 1 -22,22 h-8 a22,22 0 0 1 -22,-22 v-14 a4,4 0 0 1 4,-4 Z" fill="${P.cream}" stroke="${P.near}" stroke-width="3"/>
  <path d="M${w * 0.44},${h * 0.63} h34 a4,4 0 0 1 4,4 v10 a18,18 0 0 1 -18,18 h-6 a18,18 0 0 1 -18,-18 v-10 a4,4 0 0 1 4,-4 Z" fill="${P.cream}" stroke="${P.near}" stroke-width="3"/>
  <!-- torta -->
  <path d="M${w * 0.08},${h * 0.66} l70,0 l-10,-34 l-50,0 Z" fill="${P.woodLt}"/>
  <path d="M${w * 0.08 + 10},${h * 0.66 - 34} l50,0 l-4,-12 l-42,0 Z" fill="${P.berryLt}"/>
  <!-- vapor -->
  <path d="M${w * 0.24},${h * 0.42} q10,-16 0,-30 q-10,-14 0,-26" stroke="${P.near}" stroke-width="3.5" fill="none" opacity="0.4" stroke-linecap="round"/>`;
  write('perfil-gastronomia.svg', svg(w, h, body));
}

// 3 — GUÍA / prestador: silueta señalando la montaña en un sendero.
{
  const w = 800, h = 500;
  const body = `
  ${mountainBase(w, h, { horizon: h * 0.62, seed: 13 })}
  <path d="M0,${h * 0.84} Q${w * 0.4},${h * 0.78} ${w},${h * 0.86} L${w},${h} L0,${h} Z" fill="${P.green}" opacity="0.85"/>
  <!-- sendero: ancho abajo, angosto al fondo (perspectiva real, no una barra) -->
  <path d="M${w * 0.16},${h}
           Q${w * 0.34},${h * 0.93} ${w * 0.6},${h * 0.845}
           L${w * 0.64},${h * 0.85}
           Q${w * 0.42},${h * 0.95} ${w * 0.42},${h} Z"
        fill="#D9CDBB" opacity="0.95"/>
  <!-- guía señalando la cumbre + dos visitantes atrás -->
  ${person(w * 0.28, h * 0.95, 1.25, P.berry, { armAngle: -62 })}
  <rect x="${w * 0.28 - 27}" y="${h * 0.95 - 76}" width="19" height="30" rx="7" fill="${P.berryLt}"/>
  ${person(w * 0.41, h * 0.965, 1.05, P.ink, { armAngle: 22 })}
  ${person(w * 0.51, h * 0.945, 0.95, P.ink, { armAngle: 32 })}`;
  write('perfil-guia.svg', svg(w, h, body));
}

// 4 — CHACRA / fruta fina: hileras de cultivo, invernadero, cosecha.
{
  const w = 800, h = 500;
  const body = `
  ${mountainBase(w, h, { horizon: h * 0.5, seed: 23 })}
  <rect y="${h * 0.56}" width="${w}" height="${h * 0.44}" fill="#8AA06B"/>
  <!-- hileras en perspectiva -->
  ${Array.from({ length: 7 }, (_, i) => {
    const y = h * 0.6 + i * (h * 0.062);
    const sw = 5 + i * 2.2;
    return `<path d="M${-40 + i * 6},${y} Q${w * 0.5},${y - 8} ${w + 40 - i * 6},${y}" stroke="${P.green}" stroke-width="${sw}" fill="none" opacity="${0.55 + i * 0.05}"/>`;
  }).join('')}
  <!-- arbustos de berries -->
  ${Array.from({ length: 16 }, (_, i) => {
    const x = 40 + i * 50 + ((i * 31) % 22);
    const y = h * 0.72 + ((i * 47) % 60);
    return `<g><ellipse cx="${x}" cy="${y}" rx="17" ry="14" fill="${P.greenLt}"/>
      <circle cx="${x - 5}" cy="${y - 3}" r="3.4" fill="${P.berry}"/>
      <circle cx="${x + 6}" cy="${y + 2}" r="3.4" fill="${P.berry}"/>
      <circle cx="${x + 1}" cy="${y + 7}" r="3" fill="${P.berryLt}"/></g>`;
  }).join('')}
  <!-- invernadero -->
  <g>
    <path d="M${w * 0.63},${h * 0.58} l0,-34 q46,-34 92,0 l0,34 Z" fill="${P.cream}" opacity="0.94" stroke="${P.near}" stroke-width="3"/>
    <path d="M${w * 0.709},${h * 0.42} l0,${h * 0.16}" stroke="${P.near}" stroke-width="2.5" opacity="0.6"/>
    <path d="M${w * 0.66},${h * 0.44} l0,${h * 0.14}" stroke="${P.near}" stroke-width="2" opacity="0.4"/>
    <path d="M${w * 0.76},${h * 0.44} l0,${h * 0.14}" stroke="${P.near}" stroke-width="2" opacity="0.4"/>
  </g>
  <!-- persona cosechando entre las hileras -->
  <g>
    <circle cx="${w * 0.24}" cy="${h * 0.72}" r="19" fill="${P.ink}"/>
    <path d="M${w * 0.24 - 3},${h * 0.72 + 17} q-26,4 -28,34 l0,26 l58,0 l0,-30 q-4,-26 -27,-30 Z" fill="${P.ink}"/>
    <!-- brazo bajando al arbusto -->
    <path d="M${w * 0.265},${h * 0.83} Q${w * 0.31},${h * 0.87} ${w * 0.325},${h * 0.9}"
          stroke="${P.ink}" stroke-width="12" stroke-linecap="round" fill="none"/>
  </g>
  <!-- balde de cosecha -->
  <path d="M${w * 0.35},${h * 0.95} l7,-30 l38,0 l7,30 Z" fill="${P.berry}"/>
  <ellipse cx="${w * 0.386}" cy="${h * 0.888}" rx="26" ry="7" fill="${P.berryLt}"/>`;
  write('perfil-chacra.svg', svg(w, h, body));
}

// 5 — LANA / textil: telar con madejas colgando.
{
  const w = 800, h = 500;
  const body = `
  <rect width="${w}" height="${h}" fill="#EDE3D6"/>
  <!-- pared de madera -->
  ${Array.from({ length: 9 }, (_, i) =>
    `<rect y="${i * (h / 9)}" width="${w}" height="${h / 9 - 3}" fill="${i % 2 ? '#E7DACB' : '#EFE5D8'}"/>`).join('')}
  <!-- telar: marco -->
  <rect x="${w * 0.12}" y="${h * 0.16}" width="${w * 0.46}" height="${h * 0.62}" fill="none" stroke="${P.wood}" stroke-width="14" rx="4"/>
  <!-- urdimbre -->
  ${Array.from({ length: 18 }, (_, i) => {
    const x = w * 0.14 + i * ((w * 0.42) / 17);
    return `<line x1="${x}" y1="${h * 0.19}" x2="${x}" y2="${h * 0.75}" stroke="${P.cream}" stroke-width="2.5" opacity="0.9"/>`;
  }).join('')}
  <!-- trama tejida -->
  ${Array.from({ length: 7 }, (_, i) => {
    const y = h * 0.5 + i * 16;
    const col = [P.berry, P.wheat, P.deep, P.green, P.berryLt, P.wheat, P.deep][i];
    return `<rect x="${w * 0.14}" y="${y}" width="${w * 0.42}" height="12" fill="${col}" opacity="0.92"/>`;
  }).join('')}
  <!-- tejedora sentada frente al telar, de perfil -->
  <g>
    <circle cx="${w * 0.66}" cy="${h * 0.4}" r="21" fill="${P.deep}"/>
    <path d="M${w * 0.66 - 4},${h * 0.4 + 18} q-26,4 -30,34 l0,44 l58,0 l0,-52 q-4,-24 -28,-26 Z" fill="${P.deep}"/>
    <!-- brazo que llega a la trama -->
    <path d="M${w * 0.645},${h * 0.58} Q${w * 0.58},${h * 0.56} ${w * 0.5},${h * 0.53}"
          stroke="${P.deep}" stroke-width="13" stroke-linecap="round" fill="none"/>
  </g>
  <!-- cesto de vellón al pie del telar -->
  <path d="M${w * 0.6},${h * 0.92} l9,-34 l52,0 l9,34 Z" fill="${P.woodLt}"/>
  <ellipse cx="${w * 0.644}" cy="${h * 0.855}" rx="36" ry="12" fill="${P.cream}"/>
  <!-- madejas colgadas de una barra alta -->
  ${[[0.7, P.berry], [0.79, P.wheat], [0.88, P.green]].map(([xr, col]) =>
    `<g><line x1="${w * xr}" y1="${h * 0.09}" x2="${w * xr}" y2="${h * 0.15}" stroke="${P.near}" stroke-width="3"/>
     <ellipse cx="${w * xr}" cy="${h * 0.23}" rx="26" ry="38" fill="${col}"/>
     <ellipse cx="${w * xr}" cy="${h * 0.23}" rx="10" ry="38" fill="${P.cream}" opacity="0.18"/></g>`).join('')}
  <rect x="${w * 0.62}" y="${h * 0.07}" width="${w * 0.34}" height="9" rx="4" fill="${P.wood}"/>`;
  write('perfil-lana.svg', svg(w, h, body));
}

// 6 — OFICIO URBANO / taller: banco de trabajo, herramientas, manos.
{
  const w = 800, h = 500;
  const body = `
  <rect width="${w}" height="${h}" fill="#E9E0D5"/>
  <!-- tablero de herramientas -->
  <rect x="${w * 0.06}" y="${h * 0.08}" width="${w * 0.88}" height="${h * 0.44}" fill="#DCD0C0" rx="5"/>
  ${Array.from({ length: 60 }, (_, i) => {
    const cx = w * 0.09 + (i % 15) * (w * 0.058);
    const cy = h * 0.13 + Math.floor(i / 15) * (h * 0.1);
    return `<circle cx="${cx}" cy="${cy}" r="2.6" fill="${P.near}" opacity="0.3"/>`;
  }).join('')}
  <!-- herramientas colgadas -->
  <g stroke-linecap="round">
    <line x1="${w * 0.18}" y1="${h * 0.14}" x2="${w * 0.18}" y2="${h * 0.34}" stroke="${P.deep}" stroke-width="8"/>
    <rect x="${w * 0.155}" y="${h * 0.33}" width="34" height="16" rx="3" fill="${P.berry}"/>
    <path d="M${w * 0.3},${h * 0.14} l0,22 l-16,0 l0,10 l40,0 l0,-10 l-16,0 l0,-22 Z" fill="${P.deep}"/>
    <circle cx="${w * 0.44}" cy="${h * 0.22}" r="20" fill="none" stroke="${P.deep}" stroke-width="7"/>
    <line x1="${w * 0.44}" y1="${h * 0.26}" x2="${w * 0.44}" y2="${h * 0.42}" stroke="${P.deep}" stroke-width="7"/>
    <path d="M${w * 0.58},${h * 0.14} q26,10 0,26 q-26,-16 0,-26 Z" fill="${P.wheat}"/>
    <line x1="${w * 0.7}" y1="${h * 0.14}" x2="${w * 0.7}" y2="${h * 0.4}" stroke="${P.woodLt}" stroke-width="9"/>
    <path d="M${w * 0.7 - 16},${h * 0.4} l32,0 l-6,14 l-20,0 Z" fill="${P.deep}"/>
    <circle cx="${w * 0.84}" cy="${h * 0.24}" r="26" fill="none" stroke="${P.green}" stroke-width="7"/>
  </g>
  <!-- banco de trabajo -->
  <rect x="0" y="${h * 0.6}" width="${w}" height="${h * 0.4}" fill="${P.wood}"/>
  <rect x="0" y="${h * 0.6}" width="${w}" height="9" fill="${P.woodLt}"/>
  <!-- artesano de perfil trabajando una pieza de cerámica en el torno -->
  <g>
    <circle cx="${w * 0.24}" cy="${h * 0.36}" r="24" fill="${P.deep}"/>
    <path d="M${w * 0.24 - 4},${h * 0.36 + 21} q-30,6 -34,40 l0,${h * 0.09} l72,0 l0,-${h * 0.1} q-4,-28 -34,-31 Z" fill="${P.deep}"/>
    <!-- brazos hacia la pieza -->
    <path d="M${w * 0.27},${h * 0.55} Q${w * 0.35},${h * 0.56} ${w * 0.42},${h * 0.55}"
          stroke="${P.deep}" stroke-width="14" stroke-linecap="round" fill="none"/>
  </g>
  <!-- torno + vasija en proceso -->
  <ellipse cx="${w * 0.47}" cy="${h * 0.6}" rx="66" ry="13" fill="${P.deep}" opacity="0.35"/>
  <path d="M${w * 0.47 - 30},${h * 0.6}
           q-10,-32 6,-48 q-14,-16 24,-16 q38,0 24,16 q16,16 6,48 Z" fill="${P.berry}"/>
  <ellipse cx="${w * 0.47}" cy="${h * 0.6 - 64}" rx="24" ry="7" fill="${P.berryLt}"/>`;
  write('perfil-oficio.svg', svg(w, h, body));
}

// 7 — NEGOCIO QUE SE QUEDÓ: local con cartel, persiana a medio subir.
{
  const w = 800, h = 500;
  const body = `
  ${mountainBase(w, h, { horizon: h * 0.34, seed: 41 })}
  <rect y="${h * 0.42}" width="${w}" height="${h * 0.58}" fill="#D9CFC2"/>
  <!-- fachada -->
  <rect x="${w * 0.12}" y="${h * 0.2}" width="${w * 0.76}" height="${h * 0.68}" fill="${P.cream}"/>
  <rect x="${w * 0.12}" y="${h * 0.2}" width="${w * 0.76}" height="${h * 0.13}" fill="${P.berry}"/>
  <!-- líneas del cartel (nombre sin escribir: es un local cualquiera) -->
  <rect x="${w * 0.2}" y="${h * 0.245}" width="${w * 0.32}" height="10" rx="5" fill="${P.cream}" opacity="0.85"/>
  <rect x="${w * 0.2}" y="${h * 0.278}" width="${w * 0.19}" height="7" rx="3.5" fill="${P.cream}" opacity="0.55"/>
  <!-- vidriera -->
  <rect x="${w * 0.17}" y="${h * 0.38}" width="${w * 0.36}" height="${h * 0.34}" fill="${P.sky2}" stroke="${P.near}" stroke-width="4"/>
  <path d="M${w * 0.17},${h * 0.38} l${w * 0.36},${h * 0.34}" stroke="${P.cream}" stroke-width="10" opacity="0.35"/>
  <!-- puerta -->
  <rect x="${w * 0.6}" y="${h * 0.42}" width="${w * 0.16}" height="${h * 0.46}" fill="${P.wood}"/>
  <circle cx="${w * 0.735}" cy="${h * 0.65}" r="4.5" fill="${P.wheat}"/>
  <!-- persiana a medio subir sobre la vidriera -->
  ${Array.from({ length: 5 }, (_, i) =>
    `<rect x="${w * 0.17}" y="${h * 0.38 + i * 11}" width="${w * 0.36}" height="8" fill="${P.mid}" opacity="0.9"/>`).join('')}
  <!-- persona abriendo -->
  ${person(w * 0.83, h * 0.88, 1.15, P.ink, { armAngle: -70 })}
  <!-- maceta -->
  <path d="${`M${w * 0.55},${h * 0.88} l6,-22 l26,0 l6,22 Z`}" fill="${P.berryLt}"/>
  <ellipse cx="${w * 0.585}" cy="${h * 0.85}" rx="20" ry="14" fill="${P.green}"/>`;
  write('perfil-reabrir.svg', svg(w, h, body));
}

// 8 — IDEA / recién arranca: cuaderno, mate, manos planificando.
{
  const w = 800, h = 500;
  const body = `
  <rect width="${w}" height="${h}" fill="${P.wood}"/>
  ${Array.from({ length: 7 }, (_, i) =>
    `<rect y="${i * (h / 7)}" width="${w}" height="${h / 7 - 4}" fill="${i % 2 ? '#8A6242' : '#94694A'}"/>`).join('')}
  <!-- cuaderno -->
  <rect x="${w * 0.1}" y="${h * 0.18}" width="${w * 0.46}" height="${h * 0.64}" rx="6" fill="${P.cream}" transform="rotate(-4 ${w * 0.33} ${h * 0.5})"/>
  <g transform="rotate(-4 ${w * 0.33} ${h * 0.5})">
    ${Array.from({ length: 8 }, (_, i) =>
      `<rect x="${w * 0.14}" y="${h * 0.28 + i * 26}" width="${w * (i % 3 === 2 ? 0.22 : 0.36)}" height="6" rx="3" fill="${P.near}" opacity="0.3"/>`).join('')}
    <rect x="${w * 0.14}" y="${h * 0.23}" width="${w * 0.18}" height="9" rx="4" fill="${P.berry}"/>
  </g>
  <!-- lápiz -->
  <g transform="rotate(38 ${w * 0.62} ${h * 0.55})">
    <rect x="${w * 0.6}" y="${h * 0.3}" width="16" height="${h * 0.4}" fill="${P.wheat}"/>
    <path d="M${w * 0.6},${h * 0.7} l16,0 l-8,22 Z" fill="${P.deep}"/>
    <rect x="${w * 0.6}" y="${h * 0.3}" width="16" height="16" fill="${P.berry}"/>
  </g>
  <!-- mate -->
  <ellipse cx="${w * 0.82}" cy="${h * 0.62}" rx="46" ry="50" fill="${P.green}"/>
  <ellipse cx="${w * 0.82}" cy="${h * 0.62 - 44}" rx="30" ry="12" fill="${P.greenLt}"/>
  <line x1="${w * 0.86}" y1="${h * 0.6 - 46}" x2="${w * 0.95}" y2="${h * 0.28}" stroke="#C8CBD0" stroke-width="8" stroke-linecap="round"/>
  <!-- notas adhesivas -->
  <rect x="${w * 0.63}" y="${h * 0.14}" width="72" height="66" rx="3" fill="${P.wheat}" transform="rotate(7 ${w * 0.66} ${h * 0.18})"/>
  <rect x="${w * 0.72}" y="${h * 0.2}" width="66" height="60" rx="3" fill="${P.berryLt}" transform="rotate(-9 ${w * 0.75} ${h * 0.24})"/>`;
  write('perfil-idea.svg', svg(w, h, body));
}

// 9 — LÍNEA ACELERA (21:9): calle de Esquel con la montaña al fondo.
{
  const w = 1260, h = 540;
  const body = `
  ${mountainBase(w, h, { horizon: h * 0.42, seed: 61 })}
  <rect y="${h * 0.52}" width="${w}" height="${h * 0.48}" fill="#D6CCBE"/>
  <!-- fila de fachadas -->
  ${[
    [0.02, 0.3, P.cream, P.berry],
    [0.16, 0.36, '#E4D8C6', P.green],
    [0.3, 0.28, P.cream, P.wheat],
    [0.42, 0.34, '#EADFCE', P.berry],
    [0.56, 0.3, P.cream, P.green],
    [0.68, 0.38, '#E4D8C6', P.berry],
    [0.82, 0.32, P.cream, P.wheat],
  ].map(([x, hh, fill, band]) => {
    const bx = w * x, bw = w * 0.13, by = h * (0.86 - hh), bh = h * hh;
    return `<g>
      <rect x="${bx}" y="${by}" width="${bw}" height="${bh}" fill="${fill}"/>
      <rect x="${bx}" y="${by}" width="${bw}" height="16" fill="${band}"/>
      <rect x="${bx + bw * 0.12}" y="${by + bh * 0.32}" width="${bw * 0.3}" height="${bh * 0.26}" fill="${P.sky2}"/>
      <rect x="${bx + bw * 0.56}" y="${by + bh * 0.32}" width="${bw * 0.3}" height="${bh * 0.26}" fill="${P.sky2}"/>
      <rect x="${bx + bw * 0.34}" y="${by + bh * 0.68}" width="${bw * 0.32}" height="${bh * 0.32}" fill="${P.wood}"/>
    </g>`;
  }).join('')}
  <!-- vereda -->
  <rect y="${h * 0.86}" width="${w}" height="${h * 0.14}" fill="#C3B8A8"/>
  <!-- gente caminando -->
  ${person(w * 0.12, h * 0.96, 1.05, P.ink, { armAngle: 25 })}
  ${person(w * 0.2, h * 0.97, 0.95, P.berry, { armAngle: -15 })}
  ${person(w * 0.62, h * 0.965, 1.0, P.ink, { armAngle: 30 })}
  ${person(w * 0.9, h * 0.955, 1.1, P.ink, { armAngle: -25 })}
  <!-- árboles de vereda -->
  ${[0.36, 0.76].map((x) =>
    `<g><rect x="${w * x}" y="${h * 0.78}" width="10" height="${h * 0.16}" fill="${P.wood}"/>
     <circle cx="${w * x + 5}" cy="${h * 0.74}" r="34" fill="${P.green}" opacity="0.85"/></g>`).join('')}`;
  write('linea-acelera.svg', svg(w, h, body));
}

// 10 — LÍNEA RAÍZ (21:9): campo con ovejas, alambrado y galpón.
{
  const w = 1260, h = 540;
  const body = `
  ${mountainBase(w, h, { horizon: h * 0.4, seed: 83 })}
  <rect y="${h * 0.5}" width="${w}" height="${h * 0.5}" fill="#9FAE78"/>
  <path d="M0,${h * 0.62} Q${w * 0.4},${h * 0.54} ${w},${h * 0.64} L${w},${h} L0,${h} Z" fill="#8CA067"/>
  <path d="M0,${h * 0.78} Q${w * 0.5},${h * 0.7} ${w},${h * 0.8} L${w},${h} L0,${h} Z" fill="#7B9159"/>
  <!-- alambrado -->
  ${Array.from({ length: 9 }, (_, i) => {
    const x = 40 + i * (w / 8.5);
    return `<rect x="${x}" y="${h * 0.62}" width="7" height="${h * 0.2}" fill="${P.wood}"/>`;
  }).join('')}
  ${[0.66, 0.7, 0.74].map((y) =>
    `<line x1="0" y1="${h * y}" x2="${w}" y2="${h * y}" stroke="${P.near}" stroke-width="2" opacity="0.55"/>`).join('')}
  <!-- galpón -->
  <g>
    <rect x="${w * 0.68}" y="${h * 0.42}" width="${w * 0.2}" height="${h * 0.2}" fill="#C4553F"/>
    <path d="M${w * 0.66},${h * 0.42} L${w * 0.78},${h * 0.32} L${w * 0.9},${h * 0.42} Z" fill="${P.deep}"/>
    <rect x="${w * 0.75}" y="${h * 0.5}" width="${w * 0.06}" height="${h * 0.12}" fill="${P.deep}" opacity="0.75"/>
  </g>
  <!-- ovejas -->
  ${[[0.1, 0.86], [0.19, 0.9], [0.29, 0.85], [0.4, 0.91], [0.5, 0.87]].map(([x, y]) =>
    `<g><ellipse cx="${w * x}" cy="${h * y}" rx="34" ry="24" fill="${P.cream}"/>
     <circle cx="${w * x + 30}" cy="${h * y - 12}" r="13" fill="${P.deep}"/>
     <rect x="${w * x - 20}" y="${h * y + 18}" width="7" height="16" fill="${P.deep}"/>
     <rect x="${w * x + 14}" y="${h * y + 18}" width="7" height="16" fill="${P.deep}"/></g>`).join('')}
  <!-- productor con perro -->
  ${person(w * 0.6, h * 0.96, 1.3, P.ink, { armAngle: -30 })}
  <g><ellipse cx="${w * 0.66}" cy="${h * 0.955}" rx="22" ry="13" fill="${P.deep}"/>
   <circle cx="${w * 0.68}" cy="${h * 0.93}" r="9" fill="${P.deep}"/>
   <rect x="${w * 0.645}" y="${h * 0.96}" width="5" height="12" fill="${P.deep}"/>
   <rect x="${w * 0.672}" y="${h * 0.96}" width="5" height="12" fill="${P.deep}"/></g>`;
  write('linea-raiz.svg', svg(w, h, body));
}

// 11 — ECONOMÍA DE LOS RECUERDOS: frascos, madeja y etiqueta.
{
  const w = 800, h = 500;
  const body = `
  <rect width="${w}" height="${h}" fill="#EFE6D9"/>
  <rect y="${h * 0.7}" width="${w}" height="${h * 0.3}" fill="${P.wood}"/>
  <rect y="${h * 0.7}" width="${w}" height="8" fill="${P.woodLt}"/>
  <!-- frasco de dulce -->
  <g>
    <rect x="${w * 0.12}" y="${h * 0.36}" width="110" height="${h * 0.34}" rx="12" fill="${P.berry}" opacity="0.9"/>
    <rect x="${w * 0.12}" y="${h * 0.32}" width="110" height="26" rx="6" fill="${P.deep}"/>
    <rect x="${w * 0.13}" y="${h * 0.48}" width="88" height="42" rx="4" fill="${P.cream}"/>
    <rect x="${w * 0.145}" y="${h * 0.51}" width="58" height="6" rx="3" fill="${P.near}" opacity="0.5"/>
    <rect x="${w * 0.145}" y="${h * 0.55}" width="40" height="5" rx="2.5" fill="${P.near}" opacity="0.3"/>
  </g>
  <!-- botella -->
  <g>
    <path d="M${w * 0.38},${h * 0.7} l0,-${h * 0.24} q0,-20 18,-30 l0,-24 l24,0 l0,24 q18,10 18,30 l0,${h * 0.24} Z" fill="${P.green}" opacity="0.92"/>
    <rect x="${w * 0.395}" y="${h * 0.5}" width="76" height="46" rx="4" fill="${P.cream}"/>
    <rect x="${w * 0.41}" y="${h * 0.53}" width="48" height="6" rx="3" fill="${P.near}" opacity="0.5"/>
    <rect x="${w * 0.41}" y="${h * 0.57}" width="32" height="5" rx="2.5" fill="${P.near}" opacity="0.3"/>
  </g>
  <!-- madeja de lana -->
  <ellipse cx="${w * 0.72}" cy="${h * 0.56}" rx="62" ry="52" fill="${P.wheat}"/>
  ${Array.from({ length: 5 }, (_, i) =>
    `<path d="M${w * 0.72 - 58 + i * 6},${h * 0.56 - 40 + i * 4} q58,${20 + i * 6} 110,-${10 - i * 4}" stroke="${P.cream}" stroke-width="3" fill="none" opacity="0.35"/>`).join('')}
  <!-- etiqueta colgante de identidad local: silueta de montaña, sin texto -->
  <g transform="rotate(-10 ${w * 0.86} ${h * 0.4})">
    <path d="M${w * 0.815},${h * 0.3} l84,0 l0,74 l-42,20 l-42,-20 Z" fill="${P.cream}" stroke="${P.near}" stroke-width="2.5"/>
    <circle cx="${w * 0.857}" cy="${h * 0.325}" r="5" fill="none" stroke="${P.near}" stroke-width="2.5"/>
    <path d="M${w * 0.828},${h * 0.44} l20,-30 l13,19 l11,-14 l18,25 Z" fill="${P.berry}"/>
    <rect x="${w * 0.828}" y="${h * 0.455}" width="66" height="5" rx="2.5" fill="${P.near}" opacity="0.35"/>
  </g>
  <!-- hilo de la etiqueta -->
  <path d="M${w * 0.79},${h * 0.5} Q${w * 0.82},${h * 0.42} ${w * 0.845},${h * 0.34}"
        stroke="${P.near}" stroke-width="2.5" fill="none" opacity="0.7"/>`;
  write('economia-recuerdos.svg', svg(w, h, body));
}

// 12 — favicon
{
  const s = 64;
  const body = `
  <rect width="${s}" height="${s}" rx="14" fill="${P.paper || '#F4F2EE'}"/>
  <path d="M8,46 L22,20 L30,34 L40,14 L56,46 Z" fill="${P.deep}"/>
  <rect x="8" y="49" width="48" height="8" rx="4" fill="${P.berry}"/>`;
  fs.writeFileSync(path.join(process.cwd(), 'assets/images/favicon.svg'),
    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${s} ${s}" width="${s}" height="${s}">${body}</svg>\n`);
  console.log('  ✓ favicon.svg');
}

console.log('\nListo. Ilustraciones en assets/images/ilustraciones/');
