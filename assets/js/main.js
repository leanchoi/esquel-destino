/**
 * Esquel LAB — comportamiento del sitio público.
 *
 * El formulario funciona sin JavaScript: sin JS es una página larga con todos
 * los pasos visibles y un botón de envío. Con JS se muestra un paso por vez,
 * con progreso, guardado automático y revisión final.
 */
(function () {
  'use strict';

  // ------------------------------------------------------------ navegación
  var navToggle = document.getElementById('navToggle');
  var siteNav = document.getElementById('siteNav');
  if (navToggle && siteNav) {
    navToggle.addEventListener('click', function () {
      var abierto = siteNav.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    });
    siteNav.addEventListener('click', function (ev) {
      if (ev.target.tagName === 'A') {
        siteNav.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // -------------------------------------------------------- copiar (prensa)
  document.querySelectorAll('.copy-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var destino = document.querySelector(btn.getAttribute('data-copy'));
      if (!destino || !navigator.clipboard) return;
      navigator.clipboard.writeText(destino.innerText.trim()).then(function () {
        var original = btn.textContent;
        btn.textContent = 'Copiado';
        btn.classList.add('ok');
        setTimeout(function () {
          btn.textContent = original;
          btn.classList.remove('ok');
        }, 1900);
      });
    });
  });

  // ------------------------------------------------- movimiento (home)
  // Todo lo de acá abajo es decorativo: si el sistema pide menos movimiento
  // no se engancha nada y la página queda igual de usable.
  var menosMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Parallax del hero. La foto es 72px más alta que su marco, así que puede
  // desplazarse ±36px sin descubrir los bordes.
  var fotoHero = document.querySelector('[data-parallax]');
  if (fotoHero && !menosMovimiento) {
    var RECORRIDO = 36;
    var pendiente = false;

    var moverHero = function () {
      pendiente = false;
      var marco = fotoHero.parentElement.getBoundingClientRect();
      // 0 cuando el marco entra por abajo, 1 cuando terminó de salir por arriba.
      var avance = (window.innerHeight - marco.top) / (window.innerHeight + marco.height);
      avance = Math.min(1, Math.max(0, avance));
      fotoHero.style.transform = 'translate3d(0,' + ((avance - 0.5) * 2 * RECORRIDO).toFixed(1) + 'px,0)';
    };

    var pedirMovimiento = function () {
      if (pendiente) return;
      pendiente = true;
      window.requestAnimationFrame(moverHero);
    };

    window.addEventListener('scroll', pedirMovimiento, { passive: true });
    window.addEventListener('resize', pedirMovimiento);
    moverHero();
  }

  // Aparición al scroll.
  //
  // Esto es puro adorno, así que está armado para que nunca pueda esconder
  // contenido de verdad. Tres seguros, en orden:
  //   1. sin JS no se agrega la clase y todo se ve;
  //   2. lo que ya está en pantalla al cargar no se oculta nunca;
  //   3. además del observer hay un barrido en el scroll, por si el observer
  //      se queda atrás con un scroll muy rápido.
  var revelables = document.querySelectorAll('.section-head, .profile-card, .line-card, .date-card, .step-row, .hours-list li, .ba-wrap');
  if (revelables.length && !menosMovimiento && 'IntersectionObserver' in window) {
    var ocultos = [];

    revelables.forEach(function (el, i) {
      if (el.getBoundingClientRect().top < window.innerHeight) return;
      el.classList.add('js-reveal');
      // Escalón corto entre hermanos: da sensación de cascada sin demorar.
      el.style.transitionDelay = (Math.min(i % 6, 5) * 55) + 'ms';
      ocultos.push(el);
    });

    var mostrar = function (el) { el.classList.add('is-in'); };

    var observador = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (entrada) {
        if (!entrada.isIntersecting) return;
        mostrar(entrada.target);
        observador.unobserve(entrada.target);
      });
    }, { rootMargin: '0px 0px -6% 0px' });
    ocultos.forEach(function (el) { observador.observe(el); });

    // Barrido de respaldo: cualquier cosa que ya entró en pantalla se muestra,
    // haya avisado el observer o no.
    var barriendo = false;
    var barrer = function () {
      barriendo = false;
      ocultos = ocultos.filter(function (el) {
        if (el.getBoundingClientRect().top > window.innerHeight) return true;
        mostrar(el);
        return false;
      });
      if (!ocultos.length) window.removeEventListener('scroll', pedirBarrido);
    };
    var pedirBarrido = function () {
      if (barriendo) return;
      barriendo = true;
      window.requestAnimationFrame(barrer);
    };
    window.addEventListener('scroll', pedirBarrido, { passive: true });
    window.addEventListener('resize', pedirBarrido);
  }

  // Barra fija de postulación.
  //
  // Depende de una sola cosa: cuánto falta para que termine el hero. Una
  // versión anterior también se escondía cuando había otro botón de
  // postulación en pantalla, y como esos botones entran y salen todo el
  // tiempo al scrollear, la barra parpadeaba. Ahora hay una banda muerta de
  // 200px entre el punto donde aparece y el punto donde se va, así que no
  // puede oscilar por más despacio que se scrollee.
  var barraCta = document.getElementById('stickyCta');
  var hero = document.querySelector('.hero');
  if (barraCta && hero) {
    var visible = false;
    var descartada = false;
    var pedidoBarra = false;

    var pintarBarra = function (mostrar) {
      visible = mostrar;
      barraCta.classList.toggle('is-visible', mostrar);
      barraCta.setAttribute('aria-hidden', mostrar ? 'false' : 'true');
    };

    var revisarBarra = function () {
      pedidoBarra = false;
      if (descartada) return;
      var finHero = hero.getBoundingClientRect().bottom;
      if (!visible && finHero < -80) pintarBarra(true);
      else if (visible && finHero > 120) pintarBarra(false);
    };

    var pedirRevision = function () {
      if (pedidoBarra) return;
      pedidoBarra = true;
      window.requestAnimationFrame(revisarBarra);
    };

    window.addEventListener('scroll', pedirRevision, { passive: true });
    window.addEventListener('resize', pedirRevision);
    revisarBarra();

    var cerrarBarra = document.getElementById('stickyCtaClose');
    if (cerrarBarra) {
      cerrarBarra.addEventListener('click', function () {
        descartada = true;
        pintarBarra(false);
      });
    }
  }

  // ------------------------------------------------------------- formulario
  var form = document.getElementById('formPostulacion');
  if (!form) return;

  var CLAVE = 'esquellab_borrador_v2';
  var pasos = Array.prototype.slice.call(form.querySelectorAll('.fstep'));
  var btnPrev = document.getElementById('btnPrev');
  var btnNext = document.getElementById('btnNext');
  var btnSubmit = document.getElementById('btnSubmit');
  var progreso = document.getElementById('progress');
  var barra = document.getElementById('progressFill');
  var etiqueta = document.getElementById('progressLabel');
  var revision = document.getElementById('revision');
  var actual = 0;

  // Obligatorios por paso. En el HTML no usamos el atributo `required` para
  // que sin JS el formulario se pueda enviar igual y lo valide el servidor,
  // que es la validación que realmente manda.
  var REQUERIDOS = {
    1: [{ name: 'program', msg: 'Elegí una de las dos líneas.' }],
    2: [
      { name: 'name', msg: 'Poné el nombre de tu proyecto.' },
      { name: 'contact_name', msg: 'Necesitamos saber con quién hablamos.' },
      { name: 'email', tipo: 'email', msg: 'Revisá el correo: es por donde te vamos a contactar.' },
      { name: 'phone', msg: 'Dejanos un teléfono de contacto.' }
    ],
    3: [
      { name: 'descripcion', msg: 'Contanos qué hacés hoy.' },
      { name: 'diferencial', msg: 'Este campo es de los que más pesan en la evaluación.' }
    ],
    6: [
      { name: 'motivacion', msg: 'Contanos por qué querés participar.' },
      { name: 'compromiso', msg: 'Confirmá la disponibilidad de 12 horas semanales.' }
    ]
  };

  function campo(nombre) {
    return form.querySelector('[name="' + nombre + '"]');
  }

  function valor(nombre) {
    var el = campo(nombre);
    if (!el) return '';
    if (el.type === 'radio') {
      var marcado = form.querySelector('[name="' + nombre + '"]:checked');
      return marcado ? marcado.value : '';
    }
    if (el.type === 'checkbox') return el.checked ? 'on' : '';
    return el.value.trim();
  }

  function mostrarError(nombre, msg) {
    var p = form.querySelector('[data-err="' + nombre + '"]');
    if (p) p.textContent = msg || '';
    var el = campo(nombre);
    if (el && el.type !== 'radio' && el.type !== 'checkbox') {
      el.classList.toggle('is-invalid', !!msg);
    }
  }

  function validarPaso(indice) {
    var reglas = REQUERIDOS[indice + 1] || [];
    var ok = true;
    var primero = null;

    reglas.forEach(function (r) {
      var val = valor(r.name);
      var malo = !val;
      if (!malo && r.tipo === 'email') {
        malo = !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
      }
      if (malo) {
        ok = false;
        mostrarError(r.name, r.msg);
        if (!primero) primero = campo(r.name);
      } else {
        mostrarError(r.name, '');
      }
    });

    if (!ok && primero) {
      primero.focus({ preventScroll: true });
      primero.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return ok;
  }

  function ir(indice) {
    actual = indice;
    pasos.forEach(function (p, i) { p.hidden = i !== indice; });

    if (barra) barra.style.width = (((indice + 1) / pasos.length) * 100) + '%';
    if (etiqueta) etiqueta.textContent = 'Paso ' + (indice + 1) + ' de ' + pasos.length;

    btnPrev.hidden = indice === 0;
    var ultimo = indice === pasos.length - 1;
    btnNext.hidden = ultimo;
    btnSubmit.hidden = !ultimo;
    if (revision) revision.hidden = !ultimo;
    if (ultimo) construirRevision();

    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function recortar(txt, n) {
    if (!txt) return '';
    return txt.length > n ? txt.slice(0, n).trim() + '…' : txt;
  }

  function construirRevision() {
    var lista = document.getElementById('revisionLista');
    if (!lista) return;
    var linea = valor('program');
    var filas = [
      ['Línea', linea === 'Raiz' ? 'Raíz (rural)' : (linea === 'Acelera' ? 'Esquel Acelera (urbano)' : '')],
      ['Proyecto', valor('name')],
      ['Responsable', valor('contact_name')],
      ['Correo', valor('email')],
      ['Teléfono', valor('phone')],
      ['Qué hacés hoy', recortar(valor('descripcion'), 150)],
      ['Qué te hace distinto', recortar(valor('diferencial'), 150)],
      ['Por qué vos', recortar(valor('motivacion'), 150)],
      ['Compromiso de 12 hs', valor('compromiso') ? 'Confirmado' : '']
    ];

    lista.innerHTML = '';
    filas.forEach(function (f) {
      var dt = document.createElement('dt');
      dt.textContent = f[0];
      var dd = document.createElement('dd');
      if (f[1]) {
        dd.textContent = f[1];
      } else {
        dd.textContent = 'Sin completar';
        dd.className = 'empty';
      }
      lista.appendChild(dt);
      lista.appendChild(dd);
    });
  }

  // ---------------------------------------------------------- autoguardado
  var avisoBorrador = document.getElementById('avisoBorrador');
  var borrarAviso = null;

  /** El formulario es largo: si no se avisa, nadie sabe que se está guardando. */
  function marcarGuardado() {
    if (!avisoBorrador) return;
    avisoBorrador.textContent = 'Borrador guardado en este dispositivo';
    avisoBorrador.classList.add('is-on');
    clearTimeout(borrarAviso);
    borrarAviso = setTimeout(function () { avisoBorrador.classList.remove('is-on'); }, 2600);
  }

  function guardar() {
    try {
      var datos = {};
      new FormData(form).forEach(function (val, clave) {
        if (clave === 'csrf_token' || clave === 'sitio_web') return;
        datos[clave] = val;
      });
      datos.__paso = actual;
      localStorage.setItem(CLAVE, JSON.stringify(datos));
      marcarGuardado();
    } catch (e) { /* sin localStorage seguimos, solo sin autoguardado */ }
  }

  function restaurar() {
    try {
      var crudo = localStorage.getItem(CLAVE);
      if (!crudo) return;
      var datos = JSON.parse(crudo);
      Object.keys(datos).forEach(function (clave) {
        if (clave === '__paso') return;
        var els = form.querySelectorAll('[name="' + clave.replace(/"/g, '') + '"]');
        Array.prototype.forEach.call(els, function (el) {
          if (el.type === 'radio') el.checked = el.value === datos[clave];
          else if (el.type === 'checkbox') el.checked = datos[clave] === 'on';
          else el.value = datos[clave];
        });
      });
      if (typeof datos.__paso === 'number' && datos.__paso >= 0 && datos.__paso < pasos.length) {
        actual = datos.__paso;
      }
    } catch (e) { /* borrador ilegible: arrancamos limpio */ }
  }

  var timer;
  form.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(guardar, 500);
  });
  form.addEventListener('change', guardar);

  btnNext.addEventListener('click', function () {
    if (!validarPaso(actual)) return;
    guardar();
    ir(Math.min(actual + 1, pasos.length - 1));
  });

  btnPrev.addEventListener('click', function () {
    ir(Math.max(actual - 1, 0));
  });

  form.addEventListener('submit', function (ev) {
    // Validamos todos los pasos, no sólo el visible.
    for (var i = 0; i < pasos.length; i++) {
      if (!validarPaso(i)) {
        ev.preventDefault();
        ir(i);
        return;
      }
    }
    try { localStorage.removeItem(CLAVE); } catch (e) {}
  });

  // Si el servidor devolvió errores, los valores ya vienen repoblados desde
  // PHP: restaurar el borrador encima los pisaría con datos viejos.
  if (form.getAttribute('data-con-errores') !== '1') {
    restaurar();
  }

  // Recién acá activamos el modo por pasos. Si el JS falla antes de esta
  // línea, el formulario queda entero y usable.
  if (progreso) progreso.hidden = false;
  ir(actual);
})();
