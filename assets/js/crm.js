/**
 * Panel de evaluación — Esquel LAB.
 * Alterna tabla/tarjetas y maneja el drawer de evaluación de cada postulación.
 */
(function () {
  'use strict';

  function leerJSON(id) {
    var el = document.getElementById(id);
    if (!el) return null;
    try { return JSON.parse(el.textContent); } catch (e) { return null; }
  }

  var APPS = leerJSON('datosApps') || [];
  var CFG = leerJSON('configCrm') || {};
  var CRITERIOS = CFG.criterios || {};
  var ESTADOS = CFG.estados || {};
  var ETIQUETAS = CFG.etiquetas || {};
  var PUEDE = !!CFG.puedeEditar;
  var PUEDE_BORRAR = !!CFG.puedeBorrar;

  // -------------------------------------------------- alternancia de vistas
  var btnTable = document.getElementById('viewTable');
  var btnCards = document.getElementById('viewCards');
  var vTable = document.getElementById('tableView');
  var vCards = document.getElementById('cardsView');

  function verTabla(esTabla) {
    if (!vTable || !vCards) return;
    vTable.hidden = !esTabla;
    vCards.hidden = esTabla;
    btnTable.classList.toggle('is-active', esTabla);
    btnCards.classList.toggle('is-active', !esTabla);
    try { localStorage.setItem('esquellab_vista', esTabla ? 'tabla' : 'tarjetas'); } catch (e) {}
  }
  if (btnTable && btnCards) {
    btnTable.addEventListener('click', function () { verTabla(true); });
    btnCards.addEventListener('click', function () { verTabla(false); });
    try {
      if (localStorage.getItem('esquellab_vista') === 'tarjetas') verTabla(false);
    } catch (e) {}
  }

  // ------------------------------------------------------------- el drawer
  var drawer = document.getElementById('drawer');
  var backdrop = document.getElementById('drawerBackdrop');
  var cerrar = document.getElementById('drawerClose');
  var cuerpo = document.getElementById('drawerBody');
  var titulo = document.getElementById('dTitulo');
  var sub = document.getElementById('dSub');
  if (!drawer) return;

  var actual = null;
  var ultimoFoco = null;

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function abrir(id) {
    actual = APPS.filter(function (a) { return Number(a.id) === Number(id); })[0];
    if (!actual) return;
    ultimoFoco = document.activeElement;
    titulo.textContent = actual.name;
    sub.textContent = actual.contact_name + ' · ' + actual.email + (actual.phone ? ' · ' + actual.phone : '');
    cuerpo.innerHTML = plantilla(actual);
    enlazar();
    drawer.hidden = false;
    backdrop.hidden = false;
    document.body.style.overflow = 'hidden';
    cerrar.focus();
  }

  function cerrarDrawer() {
    drawer.hidden = true;
    backdrop.hidden = true;
    document.body.style.overflow = '';
    actual = null;
    if (ultimoFoco) ultimoFoco.focus();
  }

  function plantilla(a) {
    var d = a.detalles || {};
    var html = '';

    // respuestas del formulario, en el orden de ETIQUETAS
    html += '<div class="d-section"><h3>Respuestas del formulario</h3>';
    Object.keys(ETIQUETAS).forEach(function (k) {
      var val = d[k];
      html += '<div class="d-answer"><div class="q">' + esc(ETIQUETAS[k]) + '</div>' +
              '<div class="a' + (val ? '' : ' empty') + '">' + (val ? esc(val) : 'Sin responder') + '</div></div>';
    });
    html += '</div>';

    // evaluación
    html += '<div class="d-section"><h3>Evaluación</h3>';
    html += '<div class="score-box"><span class="lbl">Puntaje ponderado</span>' +
            '<span class="num" id="puntajeVal">' + (a.puntaje == null ? '—' : Number(a.puntaje).toFixed(2)) + '</span></div>';

    Object.keys(CRITERIOS).forEach(function (campo) {
      var c = CRITERIOS[campo];
      var val = Number(a[campo] || 0);
      html += '<div class="crit">' +
        '<div class="crit-head"><span class="name">' + esc(c.label) + ' <span class="peso">×' + c.peso + '</span></span>' +
        '<span class="val" data-val="' + campo + '">' + val + '</span></div>' +
        '<p class="ayuda">' + esc(c.ayuda) + '</p>' +
        '<input type="range" min="0" max="5" step="1" value="' + val + '" data-crit="' + campo + '"' +
        (PUEDE ? '' : ' disabled') + '></div>';
    });
    html += '</div>';

    // estado y notas
    html += '<div class="d-section"><h3>Estado y notas</h3>';
    html += '<div class="field"><label class="lbl" for="dStage">Estado</label><select id="dStage"' + (PUEDE ? '' : ' disabled') + '>';
    Object.keys(ESTADOS).forEach(function (k) {
      html += '<option value="' + esc(k) + '"' + (a.stage === k ? ' selected' : '') + '>' + esc(ESTADOS[k].label) + '</option>';
    });
    html += '</select></div>';
    html += '<div class="field"><label class="lbl" for="dNotes">Notas del equipo</label>' +
            '<textarea id="dNotes" rows="5"' + (PUEDE ? '' : ' disabled') + '>' + esc(a.notes || '') + '</textarea></div>';
    html += '</div>';

    // Zona de borrado. Solo admin, y pidiendo el nombre del proyecto escrito:
    // no hay papelera, así que un click de más no puede borrar a nadie.
    if (PUEDE_BORRAR) {
      html += '<div class="d-section d-peligro"><h3>Eliminar postulación</h3>' +
        '<p class="ayuda">Se borra la postulación con todas sus respuestas, su evaluación y su historial. No se puede deshacer.</p>' +
        '<div class="field"><label class="lbl" for="dConfirmar">Escribí <strong>' + esc(a.name) + '</strong> para habilitar el borrado</label>' +
        '<input type="text" id="dConfirmar" autocomplete="off" placeholder="Nombre del proyecto"></div>' +
        '<button type="button" class="btn btn-peligro" id="dEliminar" disabled>Eliminar definitivamente</button>' +
        '<span class="drawer-msg" id="dMsgDel"></span></div>';
    }

    if (PUEDE) {
      html += '<div class="drawer-actions"><button type="button" class="btn btn-primary" id="dGuardar">Guardar</button>' +
              '<span class="drawer-msg" id="dMsg"></span></div>';
    } else {
      html += '<p class="hint">Tu rol es de solo lectura: podés consultar y descargar, pero no modificar la evaluación.</p>';
    }
    return html;
  }

  function enlazar() {
    cuerpo.querySelectorAll('[data-crit]').forEach(function (slider) {
      slider.addEventListener('input', function () {
        var destino = cuerpo.querySelector('[data-val="' + slider.getAttribute('data-crit') + '"]');
        if (destino) destino.textContent = slider.value;
        actualizarPuntajeLocal();
      });
    });

    var guardar = document.getElementById('dGuardar');
    if (guardar) guardar.addEventListener('click', enviar);

    var confirmar = document.getElementById('dConfirmar');
    var eliminar = document.getElementById('dEliminar');
    if (confirmar && eliminar) {
      confirmar.addEventListener('input', function () {
        eliminar.disabled = confirmar.value.trim() !== String(actual.name).trim();
      });
      eliminar.addEventListener('click', function () { borrar(confirmar.value); });
    }
  }

  function borrar(confirmacion) {
    if (!actual) return;
    var msg = document.getElementById('dMsgDel');
    var btn = document.getElementById('dEliminar');
    btn.disabled = true;
    msg.textContent = 'Eliminando…';
    msg.className = 'drawer-msg';

    fetch('api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ csrf: CFG.csrf, id: actual.id, accion: 'eliminar', confirmar: confirmacion })
    })
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
      .then(function (res) {
        if (!res.ok || !res.body.ok) {
          btn.disabled = false;
          msg.textContent = (res.body && res.body.error) || 'No se pudo eliminar.';
          msg.className = 'drawer-msg error';
          return;
        }
        msg.textContent = 'Eliminada';
        msg.className = 'drawer-msg ok';
        setTimeout(function () { window.location.reload(); }, 500);
      })
      .catch(function () {
        btn.disabled = false;
        msg.textContent = 'Error de conexión.';
        msg.className = 'drawer-msg error';
      });
  }

  /** Vista previa del puntaje mientras se mueven los sliders. */
  function actualizarPuntajeLocal() {
    var suma = 0, pesos = 0, evaluado = false;
    Object.keys(CRITERIOS).forEach(function (campo) {
      var s = cuerpo.querySelector('[data-crit="' + campo + '"]');
      var v = s ? Number(s.value) : 0;
      if (v > 0) evaluado = true;
      suma += v * CRITERIOS[campo].peso;
      pesos += CRITERIOS[campo].peso;
    });
    var el = document.getElementById('puntajeVal');
    if (el) el.textContent = (!evaluado || !pesos) ? '—' : (suma / pesos).toFixed(2);
  }

  function enviar() {
    if (!actual) return;
    var msg = document.getElementById('dMsg');
    var btn = document.getElementById('dGuardar');
    var payload = {
      csrf: CFG.csrf,
      id: actual.id,
      stage: document.getElementById('dStage').value,
      notes: document.getElementById('dNotes').value
    };
    Object.keys(CRITERIOS).forEach(function (campo) {
      var s = cuerpo.querySelector('[data-crit="' + campo + '"]');
      payload[campo] = s ? Number(s.value) : 0;
    });

    btn.disabled = true;
    msg.textContent = 'Guardando…';
    msg.className = 'drawer-msg';

    fetch('api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
      .then(function (res) {
        btn.disabled = false;
        if (!res.ok || !res.body.ok) {
          msg.textContent = (res.body && res.body.error) || 'No se pudo guardar.';
          msg.className = 'drawer-msg error';
          return;
        }
        msg.textContent = 'Guardado';
        msg.className = 'drawer-msg ok';

        // Reflejamos los cambios en memoria para no tener que recargar.
        actual.stage = res.body.stage;
        actual.notes = payload.notes;
        actual.puntaje = res.body.puntaje;
        Object.keys(CRITERIOS).forEach(function (c) { actual[c] = payload[c]; });

        setTimeout(function () { window.location.reload(); }, 700);
      })
      .catch(function () {
        btn.disabled = false;
        msg.textContent = 'Error de conexión.';
        msg.className = 'drawer-msg error';
      });
  }

  document.querySelectorAll('[data-abrir]').forEach(function (el) {
    el.addEventListener('click', function () { abrir(el.getAttribute('data-abrir')); });
  });
  cerrar.addEventListener('click', cerrarDrawer);
  backdrop.addEventListener('click', cerrarDrawer);
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && !drawer.hidden) cerrarDrawer();
  });
})();
