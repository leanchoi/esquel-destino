/**
 * Panel de evaluación — Esquel LAB.
 *
 * La evaluación es de a varios: cada jurado guarda su propio voto y su propio
 * comentario, todos ven los de todos, y el puntaje que se muestra en las listas
 * es el promedio de los votos emitidos. Mover una postulación de columna es
 * otra cosa —una decisión del proceso— y queda reservado al admin.
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
  var JURADO = CFG.jurado || [];
  var YO = CFG.yo || { id: 0, username: '' };
  var PUEDE_VOTAR = !!CFG.puedeVotar;
  var PUEDE_ESTADO = !!CFG.puedeEstado;
  var PUEDE_BORRAR = !!CFG.puedeBorrar;

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }
  function num(v, dec) {
    return v == null ? '—' : Number(v).toFixed(dec == null ? 2 : dec);
  }
  function buscar(id) {
    for (var i = 0; i < APPS.length; i++) {
      if (Number(APPS[i].id) === Number(id)) return APPS[i];
    }
    return null;
  }

  // --------------------------------------------------- alternancia de vistas
  var VISTAS = [
    { btn: 'viewTable', vista: 'tableView', clave: 'tabla' },
    { btn: 'viewRank', vista: 'rankView', clave: 'ranking' },
    { btn: 'viewCards', vista: 'cardsView', clave: 'tablero' }
  ];

  function verVista(clave) {
    var alguna = false;
    VISTAS.forEach(function (v) {
      var btn = document.getElementById(v.btn);
      var vista = document.getElementById(v.vista);
      if (!btn || !vista) return;
      alguna = true;
      var activa = v.clave === clave;
      vista.hidden = !activa;
      btn.classList.toggle('is-active', activa);
      btn.setAttribute('aria-selected', activa ? 'true' : 'false');
    });
    // La ayuda de arrastrar sólo tiene sentido con el tablero a la vista.
    var ayuda = document.getElementById('kanbanAyuda');
    if (ayuda) ayuda.hidden = clave !== 'tablero';
    if (alguna) {
      try { localStorage.setItem('esquellab_vista', clave); } catch (e) {}
    }
  }

  VISTAS.forEach(function (v) {
    var btn = document.getElementById(v.btn);
    if (btn) btn.addEventListener('click', function () { verVista(v.clave); });
  });
  try {
    var guardada = localStorage.getItem('esquellab_vista');
    if (guardada && VISTAS.some(function (v) { return v.clave === guardada; })) verVista(guardada);
  } catch (e) {}

  // ------------------------------------------------------- sello del jurado
  // Mismo cuadro que arma sello_jurado() en PHP, para poder repintarlo después
  // de un voto sin recargar toda la página.
  function selloHTML(c) {
    var clase = c.completo ? 'is-completo' : (c.emitidos > 0 ? 'is-parcial' : 'is-vacio');
    var titulo = c.faltan && c.faltan.length
      ? 'Falta votar: ' + c.faltan.join(', ')
      : (c.jurados === 0 ? 'No hay jurados cargados' : 'Votó todo el jurado');
    var html = '<span class="jurado-sello ' + clase + '" title="' + esc(titulo) + '">' +
      '<span class="js-n">' + c.emitidos + '/' + Math.max(c.jurados, c.emitidos) + '</span></span>';
    if (c.disenso) {
      html += ' <span class="tag-disenso" title="Entre el voto más alto y el más bajo hay ' +
        num(c.dispersion) + ' puntos">disenso</span>';
    }
    return html;
  }

  /** Repinta el sello y el puntaje de una postulación en todas las vistas. */
  function repintar(id) {
    var a = buscar(id);
    if (!a) return;
    var c = a.consolidado;
    document.querySelectorAll('[data-sello="' + id + '"]').forEach(function (el) {
      el.innerHTML = selloHTML(c);
    });
    document.querySelectorAll('[data-puntaje="' + id + '"]').forEach(function (el) {
      el.textContent = c.puntaje == null
        ? (el.classList.contains('kcard-pts') ? 'sin puntaje' : '—')
        : num(c.puntaje);
    });
    var enTabla = document.querySelector('#tableView [data-abrir="' + id + '"]');
    var tr = enTabla && enTabla.closest ? enTabla.closest('tr') : null;
    if (tr) tr.classList.toggle('sin-mi-voto', PUEDE_VOTAR && !a.miVoto);
    if (enTabla) enTabla.textContent = PUEDE_VOTAR ? (a.miVoto ? 'Ver / editar' : 'Evaluar') : 'Ver';
    renderRanking();
  }

  /**
   * Rehace la lista del ranking desde memoria.
   *
   * Después de votar el orden cambia, y dejar los números nuevos sobre el orden
   * viejo sería mentir con precisión decimal.
   */
  function renderRanking() {
    var ol = document.querySelector('#rankView .ranking');
    if (!ol) return;
    var lista = APPS.slice().sort(function (x, y) {
      var px = x.consolidado.puntaje, py = y.consolidado.puntaje;
      if (px == null && py == null) return String(x.submitted_at).localeCompare(String(y.submitted_at));
      if (px == null) return 1;
      if (py == null) return -1;
      return py - px;
    });
    ol.innerHTML = lista.map(function (a, i) {
      var c = a.consolidado;
      var ancho = c.puntaje == null ? 0 : Math.round(c.puntaje * 20);
      return '<li class="rank-row' + (c.puntaje == null ? ' is-sin-votos' : '') + '">' +
        '<span class="rank-pos">' + (c.puntaje == null ? '—' : i + 1) + '</span>' +
        '<button type="button" class="rank-main" data-abrir="' + a.id + '">' +
          '<span class="rank-nombre">' + esc(a.name) + '</span>' +
          '<span class="rank-meta"><span class="sello-wrap" data-sello="' + a.id + '">' +
            selloHTML(c) + '</span></span>' +
        '</button>' +
        '<span class="rank-barra"><span style="width:' + ancho + '%"></span></span>' +
        '<span class="rank-num" data-puntaje="' + a.id + '">' + (c.puntaje == null ? '—' : num(c.puntaje)) + '</span>' +
      '</li>';
    }).join('');
    ol.querySelectorAll('[data-abrir]').forEach(function (el) {
      el.addEventListener('click', function () { abrir(el.getAttribute('data-abrir')); });
    });
  }

  // ------------------------------------- el rango a medida de la analítica
  // Vive acá porque crm.js es el único script del panel. Sin JS el formulario
  // igual funciona: se muestra siempre y se envía por GET.
  var btnCustom = document.getElementById('btnCustom');
  var formCustom = document.getElementById('formCustom');
  if (btnCustom && formCustom) {
    btnCustom.addEventListener('click', function () {
      var abierto = !formCustom.hidden;
      formCustom.hidden = abierto;
      btnCustom.setAttribute('aria-expanded', abierto ? 'false' : 'true');
      if (!abierto) formCustom.querySelector('input[type=date]').focus();
    });
  }

  // ------------------------------------------------------------- el drawer
  var drawer = document.getElementById('drawer');
  var backdrop = document.getElementById('drawerBackdrop');
  var cerrar = document.getElementById('drawerClose');
  var cuerpo = document.getElementById('drawerBody');
  var titulo = document.getElementById('dTitulo');
  var sub = document.getElementById('dSub');
  var tabs = document.getElementById('drawerTabs');
  if (!drawer) return;

  var actual = null;
  var pestania = 'jurado';
  var ultimoFoco = null;

  function abrir(id) {
    actual = buscar(id);
    if (!actual) return;
    ultimoFoco = document.activeElement;
    titulo.textContent = actual.name;
    sub.textContent = actual.contact_name + ' · ' + actual.email + (actual.phone ? ' · ' + actual.phone : '');
    pestania = 'jurado';
    pintarTabs();
    render();
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

  function pintarTabs() {
    if (!tabs) return;
    tabs.querySelectorAll('[data-tab]').forEach(function (b) {
      var activa = b.getAttribute('data-tab') === pestania;
      b.classList.toggle('is-active', activa);
      b.setAttribute('aria-selected', activa ? 'true' : 'false');
    });
  }

  function render() {
    if (!actual) return;
    cuerpo.scrollTop = 0;
    if (pestania === 'respuestas') cuerpo.innerHTML = tabRespuestas(actual);
    else if (pestania === 'proceso') cuerpo.innerHTML = tabProceso(actual);
    else cuerpo.innerHTML = tabJurado(actual);
    enlazar();
  }

  if (tabs) {
    tabs.addEventListener('click', function (ev) {
      var b = ev.target.closest('[data-tab]');
      if (!b) return;
      pestania = b.getAttribute('data-tab');
      pintarTabs();
      render();
    });
  }

  // ------------------------------------------------------- pestaña: jurado
  function tabJurado(a) {
    var c = a.consolidado;
    var html = '';

    // Consolidado. El número grande es el promedio de los votos emitidos, y al
    // lado va siempre sobre cuántos: un 4,20 de un solo voto y un 4,20 de cuatro
    // no valen lo mismo, y el panel no puede hacer como que sí.
    //
    // Sin ningún voto no se muestra un número tachado, que se lee como error:
    // se dice lo que pasa, que es que la postulación todavía no la miró nadie.
    if (c.emitidos === 0) {
      html += '<div class="consolidado is-sin-votos">' +
        '<div class="cons-estado"><span class="je je-falta">Todavía no la evaluó nadie</span>' +
        '<span class="cons-sub">' + (c.jurados === 0
          ? 'No hay jurados cargados en el panel.'
          : 'El jurado son ' + c.jurados + ' personas: ' + esc(c.faltan.join(', ')) + '.') +
        '</span></div></div>';
    } else {
      html += '<div class="consolidado' + (c.completo ? ' is-completo' : '') + '">' +
        '<div class="cons-num"><span class="n">' + (c.puntaje == null ? '—' : num(c.puntaje)) + '</span>' +
        '<span class="cons-de">' + (c.votos === 1 ? '1 voto' : c.votos + ' votos') +
        (c.abstenciones ? ' · ' + c.abstenciones + (c.abstenciones === 1 ? ' abstención' : ' abstenciones') : '') +
        '</span></div>' +
        '<div class="cons-estado">' + estadoJurado(c) + '</div>' +
      '</div>';
    }

    // Promedio por criterio: dónde el jurado coincide y dónde no.
    if (c.votos > 0) {
      html += '<div class="d-section"><h3>Promedio por criterio</h3><ul class="crit-prom">';
      Object.keys(CRITERIOS).forEach(function (campo) {
        var v = c.promedios[campo];
        html += '<li>' +
          '<span class="cp-n">' + esc(CRITERIOS[campo].label) + ' <span class="peso">×' + CRITERIOS[campo].peso + '</span></span>' +
          '<span class="cp-barra"><span style="width:' + (v == null ? 0 : Math.round(v * 20)) + '%"></span></span>' +
          '<span class="cp-v">' + num(v, 1) + '</span>' +
        '</li>';
      });
      html += '</ul></div>';
    }

    // Mi voto, o por qué no lo tengo.
    if (PUEDE_VOTAR) {
      html += miVotoHTML(a);
    } else {
      html += '<div class="d-section sin-voto"><h3>Tu rol no vota</h3><p class="ayuda">' +
        (PUEDE_ESTADO
          ? 'Como administrador coordinás el proceso: movés las postulaciones de etapa. ' +
            'El voto queda en los evaluadores, para que quien decide no sea además quien puntúa.'
          : 'Entrás como observador: ves todo lo que ve el jurado y podés bajar el CSV, pero no emitís voto.') +
        '</p></div>';
    }

    // Los votos del resto, uno por uno.
    html += '<div class="d-section"><h3>Lo que votó cada jurado</h3>';
    var otros = (a.votos || []).filter(function (v) { return v.user_id !== YO.id; });
    var pendientes = JURADO.filter(function (j) {
      return j.id !== YO.id && !(a.votos || []).some(function (v) { return v.user_id === j.id; });
    });

    if (!otros.length && !pendientes.length) {
      html += '<p class="hint">Sos el único jurado cargado en el panel.</p>';
    }
    otros.forEach(function (v) { html += votoHTML(v); });
    pendientes.forEach(function (j) {
      html += '<div class="voto voto-pendiente">' +
        '<div class="voto-head"><span class="voto-quien">' + esc(j.username) + '</span>' +
        '<span class="voto-falta">todavía no votó</span></div></div>';
    });
    html += '</div>';

    return html;
  }

  function estadoJurado(c) {
    if (c.jurados === 0) {
      return '<span class="je je-vacio">No hay jurados cargados en el panel</span>';
    }
    if (c.completo) {
      var t = '<span class="je je-ok">Votó todo el jurado (' + c.jurados + ')</span>';
      if (c.disenso) {
        t += '<span class="je je-disenso">Disenso de ' + num(c.dispersion) + ' puntos entre el voto más alto y el más bajo</span>';
      }
      return t;
    }
    var falta = c.faltan.length === 1
      ? 'Falta el voto de ' + esc(c.faltan[0])
      : 'Faltan ' + c.faltan.length + ' votos: ' + esc(c.faltan.join(', '));
    return '<span class="je je-falta">' + falta + '</span>';
  }

  function votoHTML(v) {
    var etiqueta = esc(v.username) + (v.baja ? ' <span class="voto-baja">ya no está en el panel</span>' : '');
    if (v.user_id < 0) etiqueta = esc(v.username) + ' <span class="voto-baja">sin autor</span>';

    var html = '<div class="voto' + (v.abstencion ? ' voto-abstencion' : '') + '">' +
      '<div class="voto-head"><span class="voto-quien">' + etiqueta + '</span>' +
      (v.abstencion
        ? '<span class="voto-num voto-abst">se abstuvo</span>'
        : '<span class="voto-num">' + num(v.puntaje) + '</span>') +
      '</div>';

    if (!v.abstencion) {
      html += '<ul class="voto-crit">';
      Object.keys(CRITERIOS).forEach(function (campo) {
        html += '<li><span>' + esc(CRITERIOS[campo].label) + '</span><b>' + Number(v[campo] || 0) + '</b></li>';
      });
      html += '</ul>';
    }
    if (v.comentario) {
      html += '<p class="voto-com">' + esc(v.comentario) + '</p>';
    } else if (!v.abstencion) {
      html += '<p class="voto-com voto-sin">Sin comentario.</p>';
    }
    html += '<p class="voto-fecha">' + esc(fechaCorta(v.updated_at)) + '</p></div>';
    return html;
  }

  function fechaCorta(s) {
    if (!s) return '';
    var m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
    return m ? m[3] + '/' + m[2] + '/' + m[1] + ' ' + m[4] + ':' + m[5] : String(s);
  }

  function miVotoHTML(a) {
    var mio = a.miVoto;
    var abst = mio ? !!mio.abstencion : false;

    var html = '<div class="d-section mi-voto' + (mio ? ' ya-vote' : '') + '">' +
      '<h3>Tu evaluación' + (mio ? '' : ' <span class="pend">pendiente</span>') + '</h3>';

    html += '<div class="mi-voto-cuerpo"' + (abst ? ' data-apagado="1"' : '') + '>';
    Object.keys(CRITERIOS).forEach(function (campo) {
      var c = CRITERIOS[campo];
      var val = mio ? Number(mio[campo] || 0) : 0;
      html += '<div class="crit">' +
        '<div class="crit-head"><span class="name">' + esc(c.label) + ' <span class="peso">×' + c.peso + '</span></span>' +
        '<span class="val" data-val="' + campo + '">' + val + '</span></div>' +
        '<p class="ayuda">' + esc(c.ayuda) + '</p>' +
        '<input type="range" min="0" max="5" step="1" value="' + val + '" data-crit="' + campo + '"' +
        (abst ? ' disabled' : '') + ' aria-label="' + esc(c.label) + '"></div>';
    });
    html += '<div class="score-box"><span class="lbl">Tu puntaje</span>' +
      '<span class="num" id="miPuntaje">' + (mio && !abst ? num(mio.puntaje) : '—') + '</span></div>';
    html += '</div>';

    html += '<div class="field"><label class="lbl" for="miComentario">Tu comentario</label>' +
      '<textarea id="miComentario" rows="4" maxlength="4000" placeholder="Qué viste, qué te preocupa, qué preguntarías en la entrevista.">' +
      esc(mio ? mio.comentario : '') + '</textarea>' +
      '<p class="ayuda">Lo lee el resto del jurado. No lo ve quien se postuló.</p></div>';

    html += '<label class="check-abst"><input type="checkbox" id="miAbstencion"' + (abst ? ' checked' : '') + '> ' +
      '<span>Me abstengo en esta postulación<em>Por conocer a quien se postula o cualquier otro conflicto de interés. ' +
      'No entra en el promedio, pero cuenta como resuelta: el jurado deja de figurar incompleto por tu voto.</em></span></label>';

    html += '<div class="drawer-actions">' +
      '<button type="button" class="btn btn-primary" id="dGuardarVoto">' + (mio ? 'Actualizar mi voto' : 'Guardar mi voto') + '</button>' +
      (mio ? '<button type="button" class="btn btn-secondary btn-sm" id="dRetirar">Retirar mi voto</button>' : '') +
      '<span class="drawer-msg" id="dMsg"></span></div>';

    html += '</div>';
    return html;
  }

  // --------------------------------------------------- pestaña: respuestas
  function tabRespuestas(a) {
    var d = a.detalles || {};
    var html = '<div class="d-section"><h3>Respuestas del formulario</h3>';
    Object.keys(ETIQUETAS).forEach(function (k) {
      var val = d[k];
      html += '<div class="d-answer"><div class="q">' + esc(ETIQUETAS[k]) + '</div>' +
        '<div class="a' + (val ? '' : ' empty') + '">' + (val ? esc(val) : 'Sin responder') + '</div></div>';
    });
    return html + '</div>';
  }

  // ------------------------------------------------------ pestaña: proceso
  function tabProceso(a) {
    var html = '';

    html += '<div class="d-section"><h3>Estado en el proceso</h3>';
    if (PUEDE_ESTADO) {
      html += '<div class="field"><label class="lbl" for="dStage">Etapa</label><select id="dStage">';
      Object.keys(ESTADOS).forEach(function (k) {
        html += '<option value="' + esc(k) + '"' + (a.stage === k ? ' selected' : '') + '>' + esc(ESTADOS[k].label) + '</option>';
      });
      html += '</select><p class="ayuda">El cambio queda firmado con tu usuario y la fecha.</p></div>' +
        '<span class="drawer-msg" id="dMsgEstado"></span>';
    } else {
      var s = ESTADOS[a.stage] || { label: a.stage, color: '#8a8178' };
      html += '<p class="estado-fijo"><span class="chip" style="--c:' + esc(s.color) + '">' + esc(s.label) + '</span></p>' +
        '<p class="ayuda">La etapa la mueve un administrador. Tu evaluación sí queda en tus manos, en la pestaña Jurado.</p>';
    }
    html += '</div>';

    if (PUEDE_VOTAR) {
      html += '<div class="d-section"><h3>Nota del equipo</h3>' +
        '<div class="field"><textarea id="dNotes" rows="4" placeholder="Datos del caso que sirven a todos: llamadas, documentación, lo que se habló.">' +
        esc(a.notes || '') + '</textarea>' +
        '<p class="ayuda">Es compartida y la puede editar cualquier evaluador. Tu opinión sobre el proyecto va en tu comentario del jurado.</p></div>' +
        '<button type="button" class="btn btn-secondary btn-sm" id="dGuardarNotas">Guardar la nota</button>' +
        '<span class="drawer-msg" id="dMsgNotas"></span></div>';
    } else if (a.notes) {
      html += '<div class="d-section"><h3>Nota del equipo</h3><p class="d-answer"><span class="a">' + esc(a.notes) + '</span></p></div>';
    }

    html += '<div class="d-section"><h3>Historial de etapas</h3>';
    var h = a.historial || [];
    if (!h.length) {
      html += '<p class="hint">Todavía no cambió de etapa.</p>';
    } else {
      html += '<ul class="hist">';
      h.forEach(function (x) {
        html += '<li class="hist-row"><span>' + esc(fechaCorta(x.created_at)) + '</span>' +
          '<span><strong>' + esc(x.username || '—') + '</strong> pasó de ' +
          esc((ESTADOS[x.stage_from] || {}).label || x.stage_from || '—') + ' a ' +
          esc((ESTADOS[x.stage_to] || {}).label || x.stage_to) + '</span></li>';
      });
      html += '</ul>';
    }
    html += '</div>';

    if (PUEDE_BORRAR) {
      html += '<div class="d-section d-peligro"><h3>Eliminar postulación</h3>' +
        '<p class="ayuda">Se borra la postulación con todas sus respuestas, los votos del jurado y su historial. No se puede deshacer.</p>' +
        '<div class="field"><label class="lbl" for="dConfirmar">Escribí <strong>' + esc(a.name) + '</strong> para habilitar el borrado</label>' +
        '<input type="text" id="dConfirmar" autocomplete="off" placeholder="Nombre del proyecto"></div>' +
        '<button type="button" class="btn btn-peligro" id="dEliminar" disabled>Eliminar definitivamente</button>' +
        '<span class="drawer-msg" id="dMsgDel"></span></div>';
    }

    return html;
  }

  // ------------------------------------------------------------- listeners
  function enlazar() {
    cuerpo.querySelectorAll('[data-crit]').forEach(function (slider) {
      slider.addEventListener('input', function () {
        var destino = cuerpo.querySelector('[data-val="' + slider.getAttribute('data-crit') + '"]');
        if (destino) destino.textContent = slider.value;
        previaPuntaje();
      });
    });

    var abst = document.getElementById('miAbstencion');
    if (abst) {
      abst.addEventListener('change', function () {
        var cuerpoVoto = cuerpo.querySelector('.mi-voto-cuerpo');
        if (cuerpoVoto) cuerpoVoto.toggleAttribute('data-apagado', abst.checked);
        cuerpo.querySelectorAll('[data-crit]').forEach(function (s) { s.disabled = abst.checked; });
        previaPuntaje();
      });
    }

    var gv = document.getElementById('dGuardarVoto');
    if (gv) gv.addEventListener('click', guardarVoto);

    var rv = document.getElementById('dRetirar');
    if (rv) rv.addEventListener('click', retirarVoto);

    var gn = document.getElementById('dGuardarNotas');
    if (gn) gn.addEventListener('click', guardarNotas);

    var st = document.getElementById('dStage');
    if (st) st.addEventListener('change', function () { cambiarEstado(actual.id, st.value, 'dMsgEstado'); });

    var confirmar = document.getElementById('dConfirmar');
    var eliminar = document.getElementById('dEliminar');
    if (confirmar && eliminar) {
      confirmar.addEventListener('input', function () {
        eliminar.disabled = confirmar.value.trim() !== String(actual.name).trim();
      });
      eliminar.addEventListener('click', function () { borrar(confirmar.value); });
    }
  }

  /** Vista previa del puntaje propio mientras se mueven los deslizadores. */
  function previaPuntaje() {
    var el = document.getElementById('miPuntaje');
    if (!el) return;
    var abst = document.getElementById('miAbstencion');
    if (abst && abst.checked) { el.textContent = '—'; return; }
    var suma = 0, pesos = 0;
    Object.keys(CRITERIOS).forEach(function (campo) {
      var s = cuerpo.querySelector('[data-crit="' + campo + '"]');
      suma += (s ? Number(s.value) : 0) * CRITERIOS[campo].peso;
      pesos += CRITERIOS[campo].peso;
    });
    el.textContent = pesos ? (suma / pesos).toFixed(2) : '—';
  }

  // ------------------------------------------------------------ peticiones
  function pedir(payload) {
    return fetch('api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(Object.assign({ csrf: CFG.csrf }, payload))
    }).then(function (r) {
      return r.json().then(function (j) { return { ok: r.ok && j.ok, body: j }; });
    });
  }

  function decir(idMsg, texto, clase) {
    var m = document.getElementById(idMsg);
    if (m) {
      m.textContent = texto;
      m.className = 'drawer-msg' + (clase ? ' ' + clase : '');
    }
  }

  /** Mete la respuesta del servidor en memoria y repinta lo que corresponda. */
  function aplicar(res) {
    var a = buscar(res.id);
    if (!a) return;
    a.votos = res.votos;
    a.miVoto = res.miVoto;
    a.consolidado = res.consolidado;
    a.stage = res.stage;
    a.notes = res.notes;
    a.historial = res.historial;
    repintar(a.id);
  }

  function guardarVoto() {
    if (!actual) return;
    var btn = document.getElementById('dGuardarVoto');
    var abst = document.getElementById('miAbstencion');
    var payload = {
      accion: 'voto',
      id: actual.id,
      comentario: (document.getElementById('miComentario') || {}).value || '',
      abstencion: abst ? abst.checked : false
    };
    Object.keys(CRITERIOS).forEach(function (campo) {
      var s = cuerpo.querySelector('[data-crit="' + campo + '"]');
      payload[campo] = s ? Number(s.value) : 0;
    });

    btn.disabled = true;
    decir('dMsg', 'Guardando…');
    pedir(payload).then(function (res) {
      btn.disabled = false;
      if (!res.ok) { decir('dMsg', res.body.error || 'No se pudo guardar.', 'error'); return; }
      aplicar(res.body);
      render();
      decir('dMsg', 'Tu voto quedó guardado', 'ok');
    }).catch(function () {
      btn.disabled = false;
      decir('dMsg', 'Error de conexión.', 'error');
    });
  }

  function retirarVoto() {
    if (!actual) return;
    if (!window.confirm('¿Retirás tu evaluación de «' + actual.name + '»? Vas a volver a figurar como pendiente.')) return;
    decir('dMsg', 'Retirando…');
    pedir({ accion: 'retirar-voto', id: actual.id }).then(function (res) {
      if (!res.ok) { decir('dMsg', res.body.error || 'No se pudo retirar.', 'error'); return; }
      aplicar(res.body);
      render();
      decir('dMsg', 'Voto retirado', 'ok');
    }).catch(function () { decir('dMsg', 'Error de conexión.', 'error'); });
  }

  function guardarNotas() {
    if (!actual) return;
    var btn = document.getElementById('dGuardarNotas');
    btn.disabled = true;
    decir('dMsgNotas', 'Guardando…');
    pedir({ accion: 'notas', id: actual.id, notes: document.getElementById('dNotes').value })
      .then(function (res) {
        btn.disabled = false;
        if (!res.ok) { decir('dMsgNotas', res.body.error || 'No se pudo guardar.', 'error'); return; }
        aplicar(res.body);
        decir('dMsgNotas', 'Nota guardada', 'ok');
      })
      .catch(function () { btn.disabled = false; decir('dMsgNotas', 'Error de conexión.', 'error'); });
  }

  /** Cambio de etapa. Devuelve la promesa para que el tablero sepa si revertir. */
  function cambiarEstado(id, stage, idMsg) {
    if (idMsg) decir(idMsg, 'Guardando…');
    return pedir({ accion: 'estado', id: id, stage: stage }).then(function (res) {
      if (!res.ok) {
        if (idMsg) decir(idMsg, res.body.error || 'No se pudo cambiar el estado.', 'error');
        throw new Error(res.body.error || 'error');
      }
      aplicar(res.body);
      if (idMsg) decir(idMsg, 'Estado actualizado', 'ok');
      return res.body;
    });
  }

  function borrar(confirmacion) {
    if (!actual) return;
    var btn = document.getElementById('dEliminar');
    btn.disabled = true;
    decir('dMsgDel', 'Eliminando…');
    pedir({ id: actual.id, accion: 'eliminar', confirmar: confirmacion }).then(function (res) {
      if (!res.ok) {
        btn.disabled = false;
        decir('dMsgDel', res.body.error || 'No se pudo eliminar.', 'error');
        return;
      }
      decir('dMsgDel', 'Eliminada', 'ok');
      setTimeout(function () { window.location.reload(); }, 500);
    }).catch(function () {
      btn.disabled = false;
      decir('dMsgDel', 'Error de conexión.', 'error');
    });
  }

  // ------------------------------------------------------------- el tablero
  // Arrastrar sólo lo puede el admin. El selector de cada tarjeta hace lo mismo
  // y es el camino real en el celular y con el teclado: el drag nativo de HTML
  // no existe en pantallas táctiles.
  function moverTarjeta(card, columna, estado) {
    var vacio = columna.querySelector('.kempty');
    if (vacio) vacio.remove();
    columna.appendChild(card);
    var sel = card.querySelector('[data-mover]');
    if (sel) sel.value = estado;
    contarColumnas();
  }

  function contarColumnas() {
    document.querySelectorAll('#cardsView .kcol').forEach(function (col) {
      var n = col.querySelectorAll('.kcard').length;
      var badge = col.querySelector('.kcol-head .n');
      if (badge) badge.textContent = n;
      var body = col.querySelector('.kcol-body');
      var vacio = body.querySelector('.kempty');
      if (n === 0 && !vacio) {
        var d = document.createElement('div');
        d.className = 'kempty';
        d.textContent = '—';
        body.appendChild(d);
      } else if (n > 0 && vacio) {
        vacio.remove();
      }
    });
  }

  if (PUEDE_ESTADO) {
    var arrastrada = null;

    document.querySelectorAll('#cardsView .kcard[draggable]').forEach(function (card) {
      card.addEventListener('dragstart', function (ev) {
        arrastrada = card;
        card.classList.add('arrastrando');
        ev.dataTransfer.effectAllowed = 'move';
        ev.dataTransfer.setData('text/plain', card.getAttribute('data-id'));
      });
      card.addEventListener('dragend', function () {
        card.classList.remove('arrastrando');
        arrastrada = null;
        document.querySelectorAll('.kcol-body.encima').forEach(function (c) { c.classList.remove('encima'); });
      });
    });

    document.querySelectorAll('#cardsView .kcol-body').forEach(function (columna) {
      columna.addEventListener('dragover', function (ev) {
        if (!arrastrada) return;
        ev.preventDefault();
        ev.dataTransfer.dropEffect = 'move';
        columna.classList.add('encima');
      });
      columna.addEventListener('dragleave', function (ev) {
        if (!columna.contains(ev.relatedTarget)) columna.classList.remove('encima');
      });
      columna.addEventListener('drop', function (ev) {
        ev.preventDefault();
        columna.classList.remove('encima');
        if (!arrastrada) return;

        var card = arrastrada;
        var origen = card.parentElement;
        var estado = columna.getAttribute('data-drop');
        var app = buscar(card.getAttribute('data-id'));
        if (!app || app.stage === estado || origen === columna) return;

        var estadoPrevio = app.stage;
        moverTarjeta(card, columna, estado);      // optimista: la tarjeta ya se ve donde la soltaron
        card.classList.add('guardando');

        cambiarEstado(app.id, estado, null)
          .then(function () { card.classList.remove('guardando'); })
          .catch(function () {
            // Si el servidor dijo que no, la tarjeta vuelve sola: dejarla en la
            // columna nueva sería mostrar un estado que no existe en la base.
            card.classList.remove('guardando');
            var vuelta = document.querySelector('#cardsView .kcol-body[data-drop="' + estadoPrevio + '"]');
            if (vuelta) moverTarjeta(card, vuelta, estadoPrevio);
            alert('No se pudo mover la postulación. Volvió a su columna.');
          });
      });
    });

    document.querySelectorAll('#cardsView [data-mover]').forEach(function (sel) {
      sel.addEventListener('change', function () {
        var card = sel.closest('.kcard');
        var app = buscar(sel.getAttribute('data-mover'));
        var estadoPrevio = app ? app.stage : null;
        var destino = document.querySelector('#cardsView .kcol-body[data-drop="' + sel.value + '"]');
        if (!destino || !app) return;

        var estado = sel.value;
        moverTarjeta(card, destino, estado);
        card.classList.add('guardando');
        cambiarEstado(app.id, estado, null)
          .then(function () { card.classList.remove('guardando'); })
          .catch(function () {
            card.classList.remove('guardando');
            var vuelta = document.querySelector('#cardsView .kcol-body[data-drop="' + estadoPrevio + '"]');
            if (vuelta) moverTarjeta(card, vuelta, estadoPrevio);
            alert('No se pudo mover la postulación. Volvió a su columna.');
          });
      });
    });
  }

  // ----------------------------------------------------------------- arranque
  document.querySelectorAll('[data-abrir]').forEach(function (el) {
    el.addEventListener('click', function () { abrir(el.getAttribute('data-abrir')); });
  });
  cerrar.addEventListener('click', cerrarDrawer);
  backdrop.addEventListener('click', cerrarDrawer);
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && !drawer.hidden) cerrarDrawer();
  });
})();
