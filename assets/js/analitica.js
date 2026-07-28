/**
 * El cuadro flotante de los gráficos del panel.
 *
 * Reemplaza al <title> de SVG, que tarda casi un segundo en aparecer, no se
 * puede maquetar y en una pantalla táctil directamente no existe.
 *
 * Funciona por delegación: cualquier elemento con data-tt participa, sin
 * registrar nada. Así el PHP que dibuja un gráfico nuevo no tiene que avisarle
 * a este archivo, le alcanza con emitir el atributo.
 *
 *   data-tt         encabezado
 *   data-tt-lineas  el dato y su lectura, separados por |
 */
(function () {
  'use strict';

  var conDatos = document.querySelector('[data-tt]');
  if (!conDatos) return;

  var caja = document.createElement('div');
  caja.className = 'tt';
  caja.setAttribute('role', 'tooltip');
  caja.hidden = true;
  document.body.appendChild(caja);

  var actual = null;

  function pintar(el) {
    var titulo = el.getAttribute('data-tt') || '';
    var lineas = (el.getAttribute('data-tt-lineas') || '').split('|').filter(Boolean);
    var html = '<span class="tt-t"></span>';
    caja.innerHTML = html + lineas.map(function () { return '<span class="tt-l"></span>'; }).join('');
    // textContent y no innerHTML: el contenido sale de la base y de nombres que
    // carga el equipo, así que no se interpreta como marcado.
    caja.querySelector('.tt-t').textContent = titulo;
    caja.querySelectorAll('.tt-l').forEach(function (n, i) { n.textContent = lineas[i]; });
    caja.hidden = false;
  }

  /** Junto al cursor, pero siempre dentro de la pantalla. */
  function ubicar(x, y) {
    var m = 14;
    var r = caja.getBoundingClientRect();
    var iz = x + m;
    var ar = y + m;
    if (iz + r.width > window.innerWidth - 8) iz = x - r.width - m;
    if (iz < 8) iz = 8;
    if (ar + r.height > window.innerHeight - 8) ar = y - r.height - m;
    if (ar < 8) ar = 8;
    caja.style.transform = 'translate(' + Math.round(iz) + 'px,' + Math.round(ar) + 'px)';
  }

  function esconder() {
    caja.hidden = true;
    if (actual) {
      actual.classList.remove('tt-activo');
      cruz(actual, false);
      actual = null;
    }
  }

  /**
   * La línea vertical de la curva y el punto agrandado.
   * Sólo aplica a las bandas de contacto de svg_curva, que llevan data-i.
   */
  function cruz(el, mostrar) {
    var i = el.getAttribute('data-i');
    if (i === null) return;
    var svg = el.ownerSVGElement;
    if (!svg) return;
    var linea = svg.querySelector('.g-cruz');
    var punto = svg.querySelector('.g-punto[data-i="' + i + '"]');
    if (linea) {
      if (mostrar) {
        var cx = parseFloat(el.getAttribute('x')) + parseFloat(el.getAttribute('width')) / 2;
        linea.setAttribute('x1', cx);
        linea.setAttribute('x2', cx);
        linea.removeAttribute('hidden');
      } else {
        linea.setAttribute('hidden', '');
      }
    }
    if (punto) punto.classList.toggle('es-foco', mostrar);
  }

  function entrar(el, x, y) {
    if (actual !== el) {
      if (actual) { actual.classList.remove('tt-activo'); cruz(actual, false); }
      actual = el;
      el.classList.add('tt-activo');
      cruz(el, true);
      pintar(el);
    }
    ubicar(x, y);
  }

  var ultimoX = 0;
  var ultimoY = 0;
  var enTacto = false;

  document.addEventListener('mousemove', function (ev) {
    if (enTacto) return;              // eco del toque: lo maneja touchstart
    ultimoX = ev.clientX;
    ultimoY = ev.clientY;
    var el = ev.target.closest ? ev.target.closest('[data-tt]') : null;
    if (!el) { if (actual) esconder(); return; }
    entrar(el, ev.clientX, ev.clientY);
  }, { passive: true });

  // Sin capture y sobre el <html>: así se esconde sólo cuando el puntero se va
  // de la página. Con capture:true saltaba en cada mouseleave de cualquier
  // elemento del árbol, y en el escritorio no se notaba porque el mousemove
  // siguiente lo volvía a mostrar — pero en una pantalla táctil, donde no hay
  // mousemove después, el cuadro se cerraba apenas se abría.
  document.documentElement.addEventListener('mouseleave', esconder);

  // Al scrollear no se esconde a ciegas: se vuelve a mirar qué quedó abajo del
  // cursor. Esconder de una hacía que el cuadro desapareciera apenas aparecía
  // cada vez que la página se acomodaba sola para mostrar el gráfico.
  window.addEventListener('scroll', function () {
    if (!actual) return;
    var abajo = document.elementFromPoint(ultimoX, ultimoY);
    var el = abajo && abajo.closest ? abajo.closest('[data-tt]') : null;
    if (el) {
      entrar(el, ultimoX, ultimoY);
    } else {
      esconder();
    }
  }, { passive: true });

  window.addEventListener('resize', esconder);
  document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') esconder(); });

  // En el celular no hay mouse: se toca y el cuadro queda hasta que se toque
  // otra cosa. Sin esto, la mitad de la información del panel no existe en el
  // dispositivo con el que más se lo va a mirar.
  //
  // Se ubica arriba del elemento tocado y no junto al dedo, que es lo que hace
  // el mouse: al lado del dedo el cuadro queda tapado por la propia mano.
  document.addEventListener('touchstart', function (ev) {
    enTacto = true;
    var el = ev.target.closest ? ev.target.closest('[data-tt]') : null;
    if (!el) { esconder(); return; }
    if (actual) { actual.classList.remove('tt-activo'); cruz(actual, false); }
    actual = el;
    el.classList.add('tt-activo');
    cruz(el, true);
    pintar(el);
    var r = el.getBoundingClientRect();
    ubicar(r.left + r.width / 2, r.top - caja.getBoundingClientRect().height - 6);
  }, { passive: true });

  // Los eventos de mouse que el navegador simula después de un toque no tienen
  // que pisar lo que acaba de mostrar el dedo.
  document.addEventListener('touchend', function () {
    setTimeout(function () { enTacto = false; }, 600);
  }, { passive: true });

  // Con el teclado: los elementos enfocables muestran su cuadro al recibir foco.
  document.addEventListener('focusin', function (ev) {
    var el = ev.target.closest ? ev.target.closest('[data-tt]') : null;
    if (!el) return;
    var r = el.getBoundingClientRect();
    entrar(el, r.left + r.width / 2, r.bottom);
  });
  document.addEventListener('focusout', esconder);
})();
