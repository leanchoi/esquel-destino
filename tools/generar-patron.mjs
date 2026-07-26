/**
 * Genera el patrón topográfico de fondo (curvas de nivel).
 *
 * Se usa como textura de fondo en varias secciones para que la página deje de
 * ser un plano de color parejo. Es un mosaico que se repite: las curvas son
 * sumas de senos con período entero sobre el ancho del tile, así el borde
 * izquierdo y el derecho coinciden. En vertical las líneas arrancan a media
 * separación del borde y la amplitud se mantiene por debajo de esa media, de
 * modo que ninguna curva cruza el borde superior o inferior y no hay costura.
 *
 *   node tools/generar-patron.mjs
 */
import { writeFileSync, mkdirSync } from 'node:fs';

const W = 800;
const H = 800;
const LINEAS = 8;
const SEP = H / LINEAS;

/** Ondas: [período sobre W, amplitud, giro de fase por línea]. */
const ONDAS = [
  { k: 1, amp: 26, giro: 1 },
  { k: 2, amp: 13, giro: -2 },
  { k: 3, amp: 7,  giro: 3 },
];

const AMP_TOTAL = ONDAS.reduce((s, o) => s + o.amp, 0);
if (AMP_TOTAL >= SEP / 2) {
  throw new Error(`La amplitud (${AMP_TOTAL}) cruzaría el borde del tile (max ${SEP / 2}).`);
}

function curva(i) {
  const base = (i + 0.5) * SEP;
  const pasos = 96;
  const pts = [];
  for (let s = 0; s <= pasos; s++) {
    const x = (s / pasos) * W;
    let y = base;
    for (const o of ONDAS) {
      // La fase avanza en múltiplos de 2π/LINEAS: se repite cada LINEAS
      // líneas, que es exactamente la altura del tile.
      const fase = (2 * Math.PI * o.giro * i) / LINEAS;
      y += o.amp * Math.sin((2 * Math.PI * o.k * x) / W + fase);
    }
    pts.push(`${x.toFixed(1)},${y.toFixed(1)}`);
  }
  return `<polyline points="${pts.join(' ')}"/>`;
}

function svg(color, opacidad) {
  const lineas = Array.from({ length: LINEAS }, (_, i) => curva(i)).join('\n    ');
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <g fill="none" stroke="${color}" stroke-opacity="${opacidad}" stroke-width="1.4" stroke-linecap="round">
    ${lineas}
  </g>
</svg>
`;
}

/* --------------------------------------------------------------------------
   Divisor de cordillera
   Silueta que corona las secciones oscuras: los picos suben hacia la sección
   clara de arriba. Se repite en horizontal, así que el primer y el último
   punto tienen que estar a la misma altura o se ve el corte.
   -------------------------------------------------------------------------- */
const RW = 1200;
const RH = 64;

function azar(semilla) {
  let s = semilla;
  return () => {
    s = (s * 1664525 + 1013904223) % 4294967296;
    return s / 4294967296;
  };
}

function cordillera(semilla) {
  const rnd = azar(semilla);
  const picos = 9;
  const borde = RH - 10;              // altura del valle, medida desde arriba
  const pts = [[0, borde]];
  for (let i = 1; i < picos; i++) {
    const x = (i / picos) * RW + (rnd() - 0.5) * 40;
    // Alterna cumbre y valle para que la silueta tenga dientes marcados.
    const cumbre = i % 2 === 1;
    const y = cumbre ? 6 + rnd() * 18 : borde - rnd() * 16;
    pts.push([x, y]);
  }
  pts.push([RW, borde]);              // cierra a la misma altura que el inicio

  const d = pts.map(([x, y], i) => `${i === 0 ? 'M' : 'L'}${x.toFixed(1)},${y.toFixed(1)}`).join(' ');
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${RW}" height="${RH}" viewBox="0 0 ${RW} ${RH}" preserveAspectRatio="none">
  <path d="${d} L${RW},${RH} L0,${RH} Z" fill="#1F1D1B"/>
</svg>
`;
}

mkdirSync('assets/images/patrones', { recursive: true });
writeFileSync('assets/images/patrones/topo.svg', svg('#1F1D1B', '0.055'));
writeFileSync('assets/images/patrones/topo-claro.svg', svg('#FFFFFF', '0.05'));
writeFileSync('assets/images/patrones/cordillera.svg', cordillera(20260809));
console.log('Patrón topográfico y cordillera generados en assets/images/patrones/');
