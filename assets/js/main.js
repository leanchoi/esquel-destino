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

  // ------------------------------------------------- disuasión de copiado
  //
  // Bloquea clic derecho, copiar y arrastrar en el contenido general. Vale
  // aclarar qué es y qué no: el texto viaja en el HTML, así que con ver el
  // código fuente se saca igual. Esto frena el copiado de ocasión, nada más.
  //
  // Las zonas exceptuadas están elegidas a propósito: el kit de difusión
  // existe para que la gente copie esos textos, en el formulario hace falta
  // pegar y corregir, y de los términos uno tiene derecho a guardarse copia.
  var ZONAS_COPIABLES = '.release-body, .facts-table, .legal, .footer-col, input, textarea, select, [data-copiable]';

  function sePuedeCopiar(nodo) {
    while (nodo && nodo.nodeType !== 1) nodo = nodo.parentNode;   // de texto a elemento
    return !!(nodo && nodo.closest && nodo.closest(ZONAS_COPIABLES));
  }

  document.addEventListener('contextmenu', function (ev) {
    if (!sePuedeCopiar(ev.target)) ev.preventDefault();
  });

  document.addEventListener('copy', function (ev) {
    var sel = window.getSelection ? window.getSelection() : null;
    var desde = (sel && sel.anchorNode) || ev.target;
    if (!sePuedeCopiar(desde)) ev.preventDefault();
  });

  document.addEventListener('dragstart', function (ev) {
    if (ev.target && ev.target.tagName === 'IMG') ev.preventDefault();
  });

  // ------------------------------------------------------------- analítica
  // Al cerrar la pestaña le avisamos al servidor cuánto duró la visita, hasta
  // dónde bajó la persona y, en el formulario, hasta qué paso llegó. Sirve
  // para ver dónde se traba la gente. No manda nada que identifique a nadie.
  var visita = (function () {
    var el = document.getElementById('datosVisita');
    if (!el) return null;
    try { return JSON.parse(el.textContent); } catch (e) { return null; }
  })();

  window.esquelPasoForm = 0;   // lo actualiza el controlador del formulario

  if (visita && navigator.sendBeacon) {
    var arranque = Date.now();
    var hondo = 0;

    var medirScroll = function () {
      var alto = document.documentElement.scrollHeight - window.innerHeight;
      var pct = alto > 0 ? Math.round((window.scrollY / alto) * 100) : 100;
      if (pct > hondo) hondo = Math.min(100, pct);
    };
    window.addEventListener('scroll', medirScroll, { passive: true });
    medirScroll();

    var yaAvisado = false;
    var avisar = function () {
      if (yaAvisado) return;
      yaAvisado = true;
      var carga = {
        id: visita.id,
        t: visita.t,
        s: Math.round((Date.now() - arranque) / 1000),
        p: hondo
      };
      if (window.esquelPasoForm > 0) carga.f = window.esquelPasoForm;
      try {
        navigator.sendBeacon(visita.url, new Blob([JSON.stringify(carga)], { type: 'application/json' }));
      } catch (e) { /* si no se puede avisar, no pasa nada */ }
    };

    // pagehide cubre el cierre y la navegación; visibilitychange cubre el
    // caso del celular que manda la pestaña al fondo y nunca la cierra.
    window.addEventListener('pagehide', avisar);
    document.addEventListener('visibilitychange', function () {
      if (document.visibilityState === 'hidden') avisar();
    });
  }

  // ------------------------------------------------- movimiento (home)
  // Todo lo de acá abajo es decorativo: si el sistema pide menos movimiento
  // no se engancha nada y la página queda igual de usable.
  var menosMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Parallax. Cada pieza declara cuánto se puede mover en data-parallax: el
  // hero 36px, porque su foto es 72px más alta que el marco, y el fondo del
  // cierre 52px. Nunca más que el sobrante, o asoman los bordes.
  var conParallax = [].slice.call(document.querySelectorAll('[data-parallax]'));
  if (conParallax.length && !menosMovimiento) {
    var pendiente = false;

    var mover = function () {
      pendiente = false;
      for (var i = 0; i < conParallax.length; i++) {
        var pieza = conParallax[i];
        var recorrido = parseFloat(pieza.getAttribute('data-parallax')) || 36;
        var marco = pieza.parentElement.getBoundingClientRect();
        // Fuera de pantalla no hay nada que recalcular.
        if (marco.bottom < -80 || marco.top > window.innerHeight + 80) continue;
        // 0 cuando el marco entra por abajo, 1 cuando terminó de salir por arriba.
        var avance = (window.innerHeight - marco.top) / (window.innerHeight + marco.height);
        avance = Math.min(1, Math.max(0, avance));
        pieza.style.transform = 'translate3d(0,' + ((avance - 0.5) * 2 * recorrido).toFixed(1) + 'px,0)';
      }
    };

    var pedirMovimiento = function () {
      if (pendiente) return;
      pendiente = true;
      window.requestAnimationFrame(mover);
    };

    window.addEventListener('scroll', pedirMovimiento, { passive: true });
    window.addEventListener('resize', pedirMovimiento);
    mover();
  }

  // ------------------------------------------------- video de fondo (cierre)
  // El iframe de YouTube pesa, así que se arma recién cuando el bloque está
  // por entrar en pantalla. Y no se arma nunca si el sistema pide menos
  // movimiento o si el navegador avisa que el usuario está ahorrando datos:
  // en esos casos queda la foto, que es la base del bloque.
  var bloqueVideo = document.querySelector('[data-video]');
  var conexion = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
  var ahorrando = !!(conexion && (conexion.saveData || /^(slow-)?2g$/.test(conexion.effectiveType || '')));

  if (bloqueVideo && !menosMovimiento && !ahorrando && 'IntersectionObserver' in window) {
    // Se usa la API oficial de YouTube y no un iframe armado a mano. La
    // primera versión le mandaba a YouTube un saludo postMessage para que
    // avisara cuándo arrancaba, y lo mandaba al cargar el iframe: o sea, antes
    // de que el reproductor estuviera listo para escuchar. El saludo se perdía,
    // el aviso no llegaba nunca y el video se quedaba reproduciendo con
    // opacidad 0. Se veía sólo la foto.
    var pedidosAPI = [];
    var cargandoAPI = false;

    var conAPI = function (cb) {
      if (window.YT && window.YT.Player) { cb(); return; }
      pedidosAPI.push(cb);
      if (cargandoAPI) return;
      cargandoAPI = true;
      // La API avisa por una función global; se respeta la que ya hubiera.
      var previa = window.onYouTubeIframeAPIReady;
      window.onYouTubeIframeAPIReady = function () {
        if (typeof previa === 'function') previa();
        for (var i = 0; i < pedidosAPI.length; i++) pedidosAPI[i]();
        pedidosAPI = [];
      };
      var script = document.createElement('script');
      script.src = 'https://www.youtube.com/iframe_api';
      document.head.appendChild(script);
    };

    var armado = false;
    var armarVideo = function () {
      if (armado) return;
      armado = true;

      var id = bloqueVideo.getAttribute('data-video');
      var fondo = bloqueVideo.querySelector('.cierre-fondo');
      if (!id || !fondo) return;
      var desde = parseInt(bloqueVideo.getAttribute('data-video-desde'), 10) || 0;

      // El video va adentro del fondo para que el parallax lo mueva a él
      // también, y no sólo a la foto.
      var marco = document.createElement('div');
      marco.className = 'cierre-frame';
      var hueco = document.createElement('div');
      marco.appendChild(hueco);
      fondo.appendChild(marco);

      conAPI(function () {
        new window.YT.Player(hueco, {
          host: 'https://www.youtube-nocookie.com',
          videoId: id,
          playerVars: {
            autoplay: 1, mute: 1, controls: 0, loop: 1, playlist: id,
            playsinline: 1, modestbranding: 1, rel: 0, disablekb: 1, fs: 0,
            iv_load_policy: 3, start: desde, origin: window.location.origin
          },
          events: {
            onReady: function (ev) {
              var f = ev.target.getIframe();
              f.setAttribute('tabindex', '-1');
              f.setAttribute('aria-hidden', 'true');
              f.setAttribute('title', 'Esquel en video');
              // Sin silenciar, el navegador no lo deja arrancar solo.
              ev.target.mute();
              ev.target.playVideo();
              // Si el reproductor quedó listo pero nunca llega a reproducir,
              // el autoplay está bloqueado. Queda la foto, pero conviene que
              // en la consola diga por qué y no que parezca que falló el sitio.
              setTimeout(function () {
                if (!marco.classList.contains('is-visible')) {
                  console.info('[cierre] El video cargó pero el navegador no lo dejó arrancar solo. Queda la foto.');
                }
              }, 6000);
            },
            onStateChange: function (ev) {
              var E = window.YT.PlayerState;
              // Aparece recién cuando reproduce de verdad: si el navegador
              // bloqueó el autoplay queda la foto, que es mejor que un cartel
              // de play gigante encima del texto.
              if (ev.data === E.PLAYING) marco.classList.add('is-visible');
              // loop+playlist no siempre repite cuando el reproductor lo maneja
              // la API, así que al terminar se rebobina a mano.
              if (ev.data === E.ENDED) { ev.target.seekTo(desde); ev.target.playVideo(); }
            },
            // Sin video no hay nada que mostrar: se saca el marco y queda la
            // foto. El código dice por qué, que es lo único que después
            // permite arreglarlo sin adivinar.
            onError: function (ev) {
              var motivo = { 2: 'el ID del video es inválido',
                5: 'el reproductor HTML5 no puede con este video',
                100: 'el video no existe o es privado (privado no es lo mismo que oculto)',
                101: 'el dueño no permite incrustarlo en otros sitios',
                150: 'el dueño no permite incrustarlo en otros sitios' };
              console.warn('[cierre] YouTube rechazó el video (código ' + (ev && ev.data) + '): ' +
                (motivo[ev && ev.data] || 'motivo desconocido') + '. Queda la foto.');
              if (marco.parentNode) marco.parentNode.removeChild(marco);
            }
          }
        });
      });
    };

    var vigia = new IntersectionObserver(function (entradas) {
      for (var i = 0; i < entradas.length; i++) {
        if (entradas[i].isIntersecting) { armarVideo(); vigia.disconnect(); return; }
      }
    }, { rootMargin: '300px 0px' });
    vigia.observe(bloqueVideo);
  }

  // Aparición al scroll.
  //
  // Esto es puro adorno, así que está armado para que nunca pueda esconder
  // contenido de verdad. Tres seguros, en orden:
  //   1. sin JS no se agrega la clase y todo se ve;
  //   2. lo que ya está en pantalla al cargar no se oculta nunca;
  //   3. además del observer hay un barrido en el scroll, por si el observer
  //      se queda atrás con un scroll muy rápido.
  var revelables = document.querySelectorAll('.section-head, .profile-card, .line-card, .date-card, .camino-paso, .ayuda-card, .ba-wrap');
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
  // Las reglas vienen del servidor, de REQUERIDOS_POR_PASO en config.php.
  //
  // Antes esta lista estaba escrita acá a mano y era una copia parcial de la
  // que valida el envío: la pantalla dejaba pasar campos que el servidor
  // después rechazaba, y el visitante volvía al principio del formulario sin
  // entender por qué. Con una sola lista eso no puede volver a pasar.
  var REQUERIDOS = {};
  try {
    var crudo = JSON.parse((document.getElementById('camposRequeridos') || {}).textContent || '{}');
    Object.keys(crudo).forEach(function (paso) {
      REQUERIDOS[paso] = Object.keys(crudo[paso]).map(function (nombre) {
        return { name: nombre, msg: crudo[paso][nombre], tipo: nombre === 'email' ? 'email' : '' };
      });
    });
  } catch (e) {}

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

    // Paso más alto alcanzado: es el dato que dice dónde abandona la gente.
    if ((indice + 1) > window.esquelPasoForm) window.esquelPasoForm = indice + 1;

    // scaleX en lugar de width: la barra ocupa el 100% y se escala, así el
    // navegador la resuelve en el compositor y no rehace layout en cada cuadro.
    if (barra) barra.style.transform = 'scaleX(' + ((indice + 1) / pasos.length) + ')';
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
