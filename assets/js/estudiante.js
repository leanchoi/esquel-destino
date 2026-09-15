/**
 * Vista del estudiante ISET: contador, borradores y telemetría de escritura.
 *
 * Sobre la telemetría, que es lo delicado de este archivo.
 *
 * Mide tres cosas y ninguna es una acusación: cuántos caracteres entraron por
 * pegado, cuánto tiempo estuvo el campo enfocado y en cuántas sentadas se
 * escribió. Son hechos sobre cómo llegó el texto.
 *
 * NO dice si lo escribió una persona o un modelo, y el sistema en ningún lado
 * afirma eso. Alguien puede redactar en su cuaderno, pasarlo a un documento y
 * pegarlo: da 100% pegado y es trabajo propio. Por eso el profesor ve el dato
 * crudo junto con la valoración del consultor, y decide él.
 *
 * Tampoco se bloquea nada por esto. Una entrega se rechaza sólo por lo que le
 * falta —caracteres o específicos—, nunca por cómo se escribió.
 */
(function () {
  'use strict';

  var API = window.ESQUEL_TAREAS_API || 'estudiantes_api.php';
  var CSRF = (document.querySelector('meta[name="csrf"]') || {}).content || window.ESQUEL_CSRF || '';

  // --------------------------------------------------------- telemetría
  // Un registro por textarea. Se acumula mientras la persona escribe y se
  // manda con cada guardado.
  var medidas = new WeakMap();

  function medir(el) {
    if (medidas.has(el)) return medidas.get(el);
    var m = { pegados: 0, segundos: 0, desde: null };
    medidas.set(el, m);

    el.addEventListener('paste', function (ev) {
      var txt = (ev.clipboardData || window.clipboardData);
      m.pegados += txt ? String(txt.getData('text') || '').length : 0;
    });
    el.addEventListener('focus', function () { m.desde = Date.now(); });
    el.addEventListener('blur', function () {
      if (m.desde) { m.segundos += Math.round((Date.now() - m.desde) / 1000); m.desde = null; }
    });
    return m;
  }

  /** Cierra el reloj sin esperar al blur, para poder mandar el dato. */
  function cerrar(m) {
    if (m.desde) { m.segundos += Math.round((Date.now() - m.desde) / 1000); m.desde = Date.now(); }
    return m;
  }

  // ----------------------------------------------------------- contador
  function contar(form) {
    var campos = form.querySelectorAll('[data-telemetria]');
    var total = 0;
    campos.forEach(function (c) { total += c.value.trim().length; });

    var cont = form.querySelector('[data-contador]');
    if (!cont) return total;

    var min = parseInt(cont.getAttribute('data-min'), 10) || 0;
    if (total === 0) {
      cont.textContent = min ? 'Mínimo ' + min + ' caracteres' : '';
      cont.className = 'est-contador';
    } else if (total < min) {
      cont.textContent = total + ' de ' + min + ' caracteres · faltan ' + (min - total);
      cont.className = 'est-contador es-corto';
    } else {
      cont.textContent = total + ' caracteres';
      cont.className = 'est-contador es-ok';
    }
    return total;
  }

  function decir(form, texto, clase) {
    var m = form.querySelector('[data-msg]');
    if (m) { m.textContent = texto; m.className = 'drawer-msg' + (clase ? ' ' + clase : ''); }
  }

  function enviar(form, accion) {
    var id = parseInt(form.getAttribute('data-id'), 10);
    var carga = { csrf: CSRF, accion: accion, id: id, pegados: 0, segundos: 0 };

    var esp = {};
    var hayEsp = false;
    form.querySelectorAll('[data-telemetria]').forEach(function (c) {
      var m = cerrar(medir(c));
      carga.pegados += m.pegados;
      carga.segundos += m.segundos;
      m.segundos = 0;               // ya se mandó: no se cuenta dos veces
      m.pegados = 0;

      var name = c.getAttribute('name') || '';
      var mm = name.match(/^esp\[(\w+)\]$/);
      if (mm) { esp[mm[1]] = c.value; hayEsp = true; }
      else { carga.entrega = c.value; }
    });
    if (hayEsp) carga.esp = esp;

    var botones = form.querySelectorAll('button');
    botones.forEach(function (b) { b.disabled = true; });
    decir(form, accion === 'entregar' ? 'Entregando…' : 'Guardando…');

    fetch(API, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(carga)
    }).then(function (r) {
      return r.json().then(function (j) { return { ok: r.ok && j.ok, j: j }; });
    }).then(function (res) {
      botones.forEach(function (b) { b.disabled = false; });
      if (!res.ok) { decir(form, res.j.error || 'No se pudo guardar.', 'error'); return; }
      decir(form, res.j.mensaje, 'ok');
      if (res.j.estado === 'entregado') {
        // La tarjeta se marca como entregada sin recargar. Recargar en este
        // punto haría perder el mensaje justo cuando importa leerlo.
        var card = form.closest('.est-tarea');
        if (card) {
          card.classList.add('es-entregada');
          form.querySelectorAll('textarea, button').forEach(function (x) { x.disabled = true; });
        }
      }
    }).catch(function () {
      botones.forEach(function (b) { b.disabled = false; });
      decir(form, 'No pudimos conectar. Tu texto sigue acá: probá de nuevo.', 'error');
    });
  }

  // ------------------------------------------------------------ arranque
  document.querySelectorAll('.est-form').forEach(function (form) {
    form.querySelectorAll('[data-telemetria]').forEach(function (c) {
      medir(c);
      c.addEventListener('input', function () { contar(form); });
    });
    contar(form);

    form.addEventListener('submit', function (ev) { ev.preventDefault(); enviar(form, 'entregar'); });
    var borrador = form.querySelector('[data-accion="borrador"]');
    if (borrador) borrador.addEventListener('click', function () { enviar(form, 'borrador'); });
  });

  // Aviso al salir con texto sin guardar. Perder media hora de escritura por
  // cerrar una pestaña es la forma más rápida de que alguien no vuelva.
  var sucio = false;
  document.querySelectorAll('.est-form [data-telemetria]').forEach(function (c) {
    c.addEventListener('input', function () { sucio = true; });
  });
  document.addEventListener('click', function (ev) {
    if (ev.target.closest('.est-form button')) sucio = false;
  });
  window.addEventListener('beforeunload', function (ev) {
    if (!sucio) return;
    ev.preventDefault();
    ev.returnValue = '';
  });
})();
