/**
 * Módulo de Gestión LAB · Lógica interactiva y reactividad del cliente.
 * 
 * Cumple con las preferencias de Leandro:
 * - Persistencia asíncrona real en SQLite sin recargas bruscas
 * - Preservación de componentes y estados
 * - Tablas compactas y alto contraste
 * - Altura de drawer con dvh y padding seguro
 */

(function () {
  'use strict';

  // Cargar datos inyectados por el servidor
  const rawData = document.getElementById('labData');
  if (!rawData) return;
  const DATA = JSON.parse(rawData.textContent);

  const { meta, celulas, consultores, proyectos, reuniones, compromisos, csrf, yo } = DATA;

  // Helpers de fecha y formato
  const DOWL = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
  const DOWS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
  const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

  const parseD = s => {
    if (!s) return new Date();
    const [y, m, dd] = s.split('-').map(Number);
    return new Date(y, m - 1, dd);
  };
  const keyD = dt => dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(dt.getDate()).padStart(2, '0');
  const addDays = (dt, n) => {
    const x = new Date(dt);
    x.setDate(x.getDate() + n);
    return x;
  };

  const fCorta = f => {
    const dt = parseD(f);
    return `${dt.getDate()}/${dt.getMonth() + 1}`;
  };

  const fLarga = f => {
    const dt = parseD(f);
    return `${DOWL[dt.getDay()]} ${dt.getDate()} de ${MESES[dt.getMonth()]}`;
  };

  const esc = s => String(s || '').replace(/[&<>"']/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  }[c]));

  const enFIT = f => f >= meta.fit[0] && f <= meta.fit[1];
  const esFeriado = f => meta.feriados.includes(f);

  const COLOR_TIPO = {
    ind: 'var(--ind, #4A7FA8)',
    gru: 'var(--gru, #E8A33D)',
    ter: 'var(--ter, #2F7D5D)',
    cie: 'var(--cie, #6B3FA0)'
  };
  const LABEL_TIPO = {
    ind: 'Individual',
    gru: 'Célula / Grupal',
    ter: 'Terreno',
    cie: 'Cierre'
  };

  // Generar lista continua de días de la cohorte
  const DIAS = [];
  for (let x = parseD(meta.inicio); x <= parseD(meta.fin); x = addDays(x, 1)) {
    DIAS.push(keyD(x));
  }

  // Índice de reuniones por fecha y por proyecto
  const getIdxFecha = () => {
    const idx = {};
    reuniones.forEach(r => {
      (idx[r.fecha] = idx[r.fecha] || []).push(r);
    });
    return idx;
  };

  const getReunionesProyecto = pid => {
    return reuniones.filter(r => r.proyecto_id === pid).sort((a, b) => a.fecha < b.fecha ? -1 : 1);
  };

  const getProgresoCheck = r => {
    const list = r.checklist || [];
    if (!list.length) return null;
    const hechos = list.filter(it => it.done).length;
    return { hechos, total: list.length, pct: Math.round((hechos / list.length) * 100) };
  };

  // -------------------------------------------------------------------------
  // 1. TIRA DE COLISIONES Y METRICAS SUPERIORES
  // -------------------------------------------------------------------------
  function renderStrip() {
    const idx = getIdxFecha();
    const maxDia = Math.max(...DIAS.map(f => (idx[f] || []).length), 1);
    const strip = document.getElementById('stripContainer');
    if (!strip) return;

    strip.innerHTML = DIAS.map(f => {
      const evs = idx[f] || [];
      const n = evs.length;
      const dt = parseD(f);
      const wd = dt.getDay();
      const fin = wd === 0 || wd === 6;

      let bg = '#E3EAF0';
      if (n === 0) {
        bg = fin ? 'repeating-linear-gradient(45deg,#E3EAF0,#E3EAF0 2px,#EDF2F6 2px,#EDF2F6 4px)' : '#E3EAF0';
      } else if (n >= 3) {
        bg = 'var(--d4, #C4442E)';
      } else if (n === 2) {
        bg = 'var(--d3, #E8A33D)';
      } else {
        bg = 'var(--d2, #8FB8D4)';
      }

      if (enFIT(f) && n > 0) {
        bg = 'repeating-linear-gradient(45deg, #C4442E, #C4442E 3px, #E8A33D 3px, #E8A33D 6px)';
      }

      const h = n ? Math.max(10, Math.round((n / maxDia) * 68)) : 4;
      return `<div class="sbar" style="height:${h}px;background:${bg}" title="${fCorta(f)} — ${n} ${n === 1 ? 'encuentro' : 'encuentros'}" data-fecha="${f}"></div>`;
    }).join('');

    const picos = DIAS.filter(f => (idx[f] || []).length >= 3).length;
    const stripLead = document.getElementById('stripLead');
    if (stripLead) {
      stripLead.innerHTML = `Densidad diaria de reuniones: <strong>${picos}</strong> día${picos !== 1 ? 's' : ''} con 3 o más encuentros en paralelo.`;
    }

    // Click en la barra de un día para ir a la vista diaria
    strip.querySelectorAll('.sbar').forEach(bar => {
      bar.addEventListener('click', () => {
        const f = bar.dataset.fecha;
        document.querySelector('.gnav-btn[data-tab="agenda"]').click();
        document.querySelector('.submode-btn[data-mode="dxd"]').click();
        setTimeout(() => {
          const el = document.querySelector(`.dxd-row[data-fecha="${f}"]`);
          if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 80);
      });
    });
  }

  function renderStats() {
    const grid = document.getElementById('statsGrid');
    if (!grid) return;

    const totalProy = Object.keys(proyectos).length;
    const totalReu = reuniones.length;

    let totalTareas = 0;
    let tareasHechas = 0;
    let asistencias = 0;
    let reunionesPasadas = 0;
    const hoyStr = keyD(new Date());

    reuniones.forEach(r => {
      (r.checklist || []).forEach(it => {
        totalTareas++;
        if (it.done) tareasHechas++;
      });
      if (r.fecha < hoyStr) {
        reunionesPasadas++;
        if (r.asistencia_estado === 1) asistencias++;
      }
    });

    const compPend = compromisos.filter(c => c.estado === 'pendiente').length;

    grid.innerHTML = `
      <div class="stat-card">
        <span class="stat-num">${totalProy} / 18</span>
        <span class="stat-lbl">Proyectos de la cohorte</span>
      </div>
      <div class="stat-card">
        <span class="stat-num">${totalReu}</span>
        <span class="stat-lbl">Encuentros agendados</span>
      </div>
      <div class="stat-card">
        <span class="stat-num">${tareasHechas} / ${totalTareas}</span>
        <span class="stat-lbl">Tareas checklist cumplidas</span>
      </div>
      <div class="stat-card">
        <span class="stat-num">${asistencias} / ${reunionesPasadas || 1}</span>
        <span class="stat-lbl">Asistencias efectivas</span>
      </div>
      <div class="stat-card ${compPend > 0 ? 'is-alert' : ''}">
        <span class="stat-num">${compPend}</span>
        <span class="stat-lbl">Compromisos pendientes</span>
      </div>
    `;
  }

  // -------------------------------------------------------------------------
  // 2. SUBVISTA CALENDARIO
  // -------------------------------------------------------------------------
  function renderCalendario() {
    const container = document.getElementById('mesesGrid');
    if (!container) return;

    const celFiltro = document.getElementById('filtroCelula')?.value;
    const consFiltro = document.getElementById('filtroConsultor')?.value;
    const tipoFiltro = document.getElementById('filtroTipo')?.value;

    const filtradas = reuniones.filter(r => {
      const p = proyectos[r.proyecto_id];
      if (celFiltro && p && String(p.celula) !== celFiltro) return false;
      if (consFiltro && !r.asistentes.includes(consFiltro)) return false;
      if (tipoFiltro && r.tipo !== tipoFiltro) return false;
      return true;
    });

    const idx = {};
    filtradas.forEach(r => {
      (idx[r.fecha] = idx[r.fecha] || []).push(r);
    });

    const mesesDefs = [
      { y: 2026, m: 8, nombre: 'Septiembre 2026' },
      { y: 2026, m: 9, nombre: 'Octubre 2026' },
      { y: 2026, m: 10, nombre: 'Noviembre 2026' }
    ];

    container.innerHTML = mesesDefs.map(({ y, m, nombre }) => {
      const prim = new Date(y, m, 1);
      const ult = new Date(y, m + 1, 0);
      const skip = (prim.getDay() === 0 ? 6 : prim.getDay() - 1);

      let cells = '';
      for (let i = 0; i < skip; i++) {
        cells += `<div class="cal-cell is-empty"></div>`;
      }

      for (let day = 1; day <= ult.getDate(); day++) {
        const dt = new Date(y, m, day);
        const f = keyD(dt);
        const wd = dt.getDay();
        const evs = idx[f] || [];
        const dentro = f >= meta.inicio && f <= meta.fin;

        let cls = 'cal-cell';
        if (!dentro || wd === 0 || wd === 6) cls += ' is-off';
        if (evs.length) cls += ' has-events';
        if (enFIT(f)) cls += ' is-fit';
        if (esFeriado(f)) cls += ' is-feriado';

        const dots = evs.slice(0, 6).map(e => `
          <span class="cal-dot" style="background:${COLOR_TIPO[e.tipo] || '#888'}" title="${esc(e.titulo)}"></span>
        `).join('');

        cells += `
          <div class="${cls}" data-fecha="${f}">
            <div class="cal-day-num">${day}</div>
            <div class="cal-dots">${dots}</div>
          </div>
        `;
      }

      return `
        <div class="cal-mes-box">
          <h4 class="cal-mes-title">${nombre}</h4>
          <div class="cal-dow-row">
            <div>L</div><div>M</div><div>M</div><div>J</div><div>V</div><div>S</div><div>D</div>
          </div>
          <div class="cal-days-grid">${cells}</div>
        </div>
      `;
    }).join('');

    container.querySelectorAll('.cal-cell.has-events').forEach(cell => {
      cell.addEventListener('click', () => {
        const f = cell.dataset.fecha;
        const evs = idx[f] || [];
        if (evs.length === 1) {
          abrirDrawer(evs[0].id);
        } else {
          document.querySelector('.submode-btn[data-mode="dxd"]').click();
          setTimeout(() => {
            const el = document.querySelector(`.dxd-row[data-fecha="${f}"]`);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }, 80);
        }
      });
    });
  }

  // -------------------------------------------------------------------------
  // 3. SUBVISTA DIA POR DIA
  // -------------------------------------------------------------------------
  function renderDiaPorDia() {
    const container = document.getElementById('dxdContainer');
    if (!container) return;

    const celFiltro = document.getElementById('filtroCelula')?.value;
    const consFiltro = document.getElementById('filtroConsultor')?.value;
    const tipoFiltro = document.getElementById('filtroTipo')?.value;

    const filtradas = reuniones.filter(r => {
      const p = proyectos[r.proyecto_id];
      if (celFiltro && p && String(p.celula) !== celFiltro) return false;
      if (consFiltro && !r.asistentes.includes(consFiltro)) return false;
      if (tipoFiltro && r.tipo !== tipoFiltro) return false;
      return true;
    });

    const idx = {};
    filtradas.forEach(r => {
      (idx[r.fecha] = idx[r.fecha] || []).push(r);
    });

    const diasConEventos = DIAS.filter(f => (idx[f] || []).length > 0);

    if (!diasConEventos.length) {
      container.innerHTML = `
        <div class="empty-state-notice">
          <p>No se encontraron encuentros con los filtros seleccionados.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = diasConEventos.map(f => {
      const evs = idx[f];
      const n = evs.length;
      const dt = parseD(f);

      let badge = '';
      if (enFIT(f)) badge = `<span class="badge badge-fit">Período FIT</span>`;
      else if (esFeriado(f)) badge = `<span class="badge badge-feriado">Feriado</span>`;
      else if (n >= 3) badge = `<span class="badge badge-crit">${n} reuniones</span>`;

      return `
        <div class="dxd-row ${n >= 3 ? 'is-crit' : n === 2 ? 'is-hot' : ''}" data-fecha="${f}">
          <div class="dxd-date-col">
            <span class="dxd-day">${dt.getDate()} ${MESES[dt.getMonth()].slice(0, 3)}</span>
            <span class="dxd-dow">${DOWS[dt.getDay()]}</span>
            ${badge}
          </div>
          <div class="dxd-events-col">
            ${evs.map(e => {
              const p = proyectos[e.proyecto_id] || {};
              const pr = getProgresoCheck(e);
              const asistNombres = e.asistentes.map(aid => consultores[aid]?.nombre || aid).join(', ');
              const hora = e.hora_inicio ? (e.hora_fin ? `${e.hora_inicio} a ${e.hora_fin}` : e.hora_inicio) : '';

              return `
                <div class="ev-card" data-rid="${esc(e.id)}">
                  <span class="ev-strip" style="background:${COLOR_TIPO[e.tipo] || '#888'}"></span>
                  <div class="ev-card-content">
                    <div class="ev-card-head">
                      <strong>${esc(p.nombre || e.proyecto_id)}</strong>
                      <span class="ev-card-num">#${e.numero_reunion}</span>
                      <span class="ev-tipo-pill">${LABEL_TIPO[e.tipo] || e.tipo}</span>
                      ${e.asistencia_estado === 1 ? '<span class="status-pill ok">Asistió</span>' : ''}
                      ${e.asistencia_estado === -1 ? '<span class="status-pill fail">Ausente</span>' : ''}
                    </div>
                    <div class="ev-card-title">${esc(e.titulo)}</div>
                    <div class="ev-card-meta">
                      <span>📍 ${esc(e.lugar)}</span>
                      ${hora ? `<span>⏰ ${esc(hora)}</span>` : ''}
                      <span>👥 ${esc(asistNombres || 'Sin asignar')}</span>
                    </div>
                  </div>
                  ${pr ? `
                    <div class="ev-card-progress" title="${pr.hechos} de ${pr.total} tareas completadas">
                      <span>${pr.hechos}/${pr.total}</span>
                      <div class="mini-prog-bar"><div class="mini-prog-fill" style="width:${pr.pct}%"></div></div>
                    </div>
                  ` : ''}
                </div>
              `;
            }).join('')}
          </div>
        </div>
      `;
    }).join('');

    container.querySelectorAll('.ev-card').forEach(card => {
      card.addEventListener('click', () => {
        abrirDrawer(card.dataset.rid);
      });
    });
  }

  // -------------------------------------------------------------------------
  // 4. SUBVISTA GANTT / TIMELINE
  // -------------------------------------------------------------------------
  function renderGantt() {
    const container = document.getElementById('ganttInner');
    if (!container) return;

    const t0 = parseD(meta.inicio).getTime();
    const tSpan = parseD(meta.fin).getTime() - t0;
    const calcPct = f => Math.max(0, Math.min(100, ((parseD(f).getTime() - t0) / tSpan) * 100));

    const fitLeft = calcPct(meta.fit[0]);
    const fitWidth = calcPct(meta.fit[1]) - fitLeft;

    const rows = Object.values(proyectos).map(p => {
      const evs = getReunionesProyecto(p.id);
      const srNom = consultores[p.consultor_sr_id]?.nombre || p.consultor_sr_id;
      const jrNom = consultores[p.consultor_jr_id]?.nombre || p.consultor_jr_id;

      return `
        <div class="gantt-row">
          <div class="gantt-label">
            <span class="gantt-cel-dot" style="background:${celulas[p.celula]?.color || '#888'}"></span>
            <div class="gantt-pname">
              <strong>${esc(p.nombre)}</strong>
              <span>${esc(srNom)} / ${esc(jrNom)} · Célula ${p.celula}</span>
            </div>
          </div>
          <div class="gantt-track">
            <div class="gantt-fit-band" style="left:${fitLeft}%;width:${fitWidth}%"></div>
            ${evs.map(e => `
              <button type="button" class="gantt-point" data-rid="${esc(e.id)}"
                style="left:${calcPct(e.fecha)}%;background:${COLOR_TIPO[e.tipo] || '#888'}"
                title="${fCorta(e.fecha)}: ${esc(e.titulo)}"></button>
            `).join('')}
          </div>
        </div>
      `;
    }).join('');

    container.innerHTML = `
      <div class="gantt-header-row">
        <div class="gantt-header-label">Emprendimiento / Equipo</div>
        <div class="gantt-header-timeline">
          <div style="flex:22">Septiembre</div>
          <div style="flex:31">Octubre</div>
          <div style="flex:10">Noviembre</div>
        </div>
      </div>
      <div class="gantt-body-rows">${rows}</div>
    `;

    container.querySelectorAll('.gantt-point').forEach(pt => {
      pt.addEventListener('click', () => {
        abrirDrawer(pt.dataset.rid);
      });
    });
  }

  // -------------------------------------------------------------------------
  // 5. VISTA 2: EXPEDIENTE 360° POR EMPRENDIMIENTO
  // -------------------------------------------------------------------------
  let proyectoActivoId = Object.keys(proyectos)[0] || null;

  function renderExpedienteSidebar() {
    const list = document.getElementById('proyectosNavList');
    if (!list) return;

    const q = (document.getElementById('searchProyecto')?.value || '').toLowerCase().trim();

    list.innerHTML = Object.values(proyectos).filter(p => {
      if (!q) return true;
      return p.nombre.toLowerCase().includes(q) || p.titular.toLowerCase().includes(q) || p.linea.toLowerCase().includes(q);
    }).map(p => {
      const isSel = p.id === proyectoActivoId;
      const celColor = celulas[p.celula]?.color || '#888';
      return `
        <button type="button" class="pnav-item ${isSel ? 'is-active' : ''}" data-pid="${esc(p.id)}">
          <span class="pnav-cel-tag" style="background:${celColor}"></span>
          <div class="pnav-info">
            <strong>${esc(p.nombre)}</strong>
            <span class="pnav-meta">${esc(p.titular)} · ${esc(p.linea)} (${Number(p.puntaje).toFixed(2)})</span>
          </div>
        </button>
      `;
    }).join('');

    list.querySelectorAll('.pnav-item').forEach(btn => {
      btn.addEventListener('click', () => {
        proyectoActivoId = btn.dataset.pid;
        renderExpedienteSidebar();
        renderExpedienteDetalle();
      });
    });
  }

  function renderExpedienteDetalle() {
    const body = document.getElementById('expedienteBody');
    if (!body) return;

    const p = proyectos[proyectoActivoId];
    if (!p) {
      body.innerHTML = `<div class="empty-state-notice"><p>Seleccioná un proyecto.</p></div>`;
      return;
    }

    const evs = getReunionesProyecto(p.id);
    const comps = compromisos.filter(c => c.proyecto_id === p.id);
    const srNom = consultores[p.consultor_sr_id]?.nombre || p.consultor_sr_id;
    const jrNom = consultores[p.consultor_jr_id]?.nombre || p.consultor_jr_id;
    const celColor = celulas[p.celula]?.color || '#888';

    body.innerHTML = `
      <div class="p360-header">
        <div class="p360-header-top">
          <div>
            <span class="badge" style="background:${celColor};color:#fff">Célula ${p.celula}: ${esc(celulas[p.celula]?.nombre)}</span>
            <span class="badge">${esc(p.linea)}</span>
            <span class="badge ok">Puntaje ${Number(p.puntaje).toFixed(2)}</span>
            <h2 class="p360-title">${esc(p.nombre)}</h2>
            <div class="p360-titular">👤 <strong>${esc(p.titular)}</strong> · ${p.email ? `<a href="mailto:${esc(p.email)}">${esc(p.email)}</a>` : ''} · ${p.phone ? `<span>📞 ${esc(p.phone)}</span>` : ''}</div>
          </div>
          <div class="p360-equipo-box">
            <span class="sub">Equipo consultor asignado:</span>
            <div><strong>Senior (Conduce):</strong> ${esc(srNom)}</div>
            <div><strong>Junior (Acompaña):</strong> ${esc(jrNom)}</div>
            <div class="p360-status-select">
              <label>Estado del acompañamiento:</label>
              <select id="p360EstadoSelect" data-pid="${esc(p.id)}">
                <option value="En curso" ${p.estado_acompanamiento === 'En curso' ? 'selected' : ''}>En curso</option>
                <option value="Avanzado" ${p.estado_acompanamiento === 'Avanzado' ? 'selected' : ''}>Avanzado</option>
                <option value="En riesgo" ${p.estado_acompanamiento === 'En riesgo' ? 'selected' : ''}>En riesgo</option>
                <option value="Finalizado" ${p.estado_acompanamiento === 'Finalizado' ? 'selected' : ''}>Finalizado</option>
              </select>
            </div>
          </div>
        </div>

        <nav class="p360-tabs" role="tablist">
          <button type="button" class="p360-tab-btn is-active" data-ptab="plan">📋 Plan Estratégico (8 Semanas)</button>
          <button type="button" class="p360-tab-btn" data-ptab="postulacion">📝 Postulación Original</button>
          <button type="button" class="p360-tab-btn" data-ptab="jurado">⚖️ Votos del Jurado</button>
          <button type="button" class="p360-tab-btn" data-ptab="encuentros">📅 Encuentros (${evs.length})</button>
          <button type="button" class="p360-tab-btn" data-ptab="compromisos">✅ Compromisos (${comps.length})</button>
        </nav>
      </div>

      <!-- Sub-Pestaña: Plan Estratégico -->
      <div id="ptab-plan" class="p360-tab-content">
        <div class="grid-2col">
          <div class="panel-box">
            <h4>Diagnóstico de situación</h4>
            <ul class="bullet-list">
              ${(p.diagnostico || []).map(d => `<li>${esc(d)}</li>`).join('') || '<li>Sin diagnóstico cargado.</li>'}
            </ul>

            <h4 style="margin-top:20px">Trabas y cuellos de botella</h4>
            <ul class="bullet-list is-warning">
              ${(p.trabas || []).map(t => `<li>${esc(t)}</li>`).join('') || '<li>Sin trabas registradas.</li>'}
            </ul>
          </div>

          <div class="panel-box">
            <h4>Ejes estratégicos de trabajo</h4>
            ${(p.ejes || []).map(eje => `
              <div class="eje-item">
                <strong>${esc(eje.t)}</strong>
                <ul>${(eje.items || []).map(it => `<li>${esc(it)}</li>`).join('')}</ul>
              </div>
            `).join('') || '<p class="sub">Sin ejes cargados.</p>'}

            <h4 style="margin-top:20px">Entregables finales comprometidos</h4>
            <ul class="bullet-list is-ok">
              ${(p.entregables || []).map(ent => `<li>${esc(ent)}</li>`).join('') || '<li>Sin entregables definidos.</li>'}
            </ul>
          </div>
        </div>

        <div class="panel-box" style="margin-top:20px">
          <h4>Bitácora y Notas Generales del Proyecto</h4>
          <textarea id="p360NotasGenerales" class="form-textarea" rows="4" placeholder="Notas de evolución, acuerdos con la Subsecretaría, derivaciones...">${esc(p.notas_generales || '')}</textarea>
          <div style="text-align:right;margin-top:8px">
            <button type="button" class="btn btn-secondary btn-sm" id="btnGuardarNotasProy" data-pid="${esc(p.id)}">Guardar notas del proyecto</button>
          </div>
        </div>
      </div>

      <!-- Sub-Pestaña: Postulación Original -->
      <div id="ptab-postulacion" class="p360-tab-content" style="display:none">
        <div class="postulacion-box">
          ${renderPostulacionOriginal(p)}
        </div>
      </div>

      <!-- Sub-Pestaña: Votos del Jurado -->
      <div id="ptab-jurado" class="p360-tab-content" style="display:none">
        <div class="jurado-box">
          ${renderVotosJurado(p)}
        </div>
      </div>

      <!-- Sub-Pestaña: Encuentros -->
      <div id="ptab-encuentros" class="p360-tab-content" style="display:none">
        <div class="pencuentros-list">
          ${evs.map(e => {
            const pr = getProgresoCheck(e);
            return `
              <div class="pencuentro-card">
                <div class="penc-left">
                  <span class="badge">${fCorta(e.fecha)}</span>
                  <span class="ev-tipo-pill">${LABEL_TIPO[e.tipo]}</span>
                  <strong>#${e.numero_reunion} · ${esc(e.titulo)}</strong>
                  <div class="sub">📍 ${esc(e.lugar)} ${e.hora_inicio ? `· ⏰ ${esc(e.hora_inicio)}` : ''} · 👥 ${esc(e.asistentes.map(aid => consultores[aid]?.nombre || aid).join(', '))}</div>
                </div>
                <div class="penc-right">
                  ${pr ? `<span class="prog-pill">${pr.hechos}/${pr.total} tareas</span>` : ''}
                  <button type="button" class="btn btn-secondary btn-sm btn-open-meeting" data-rid="${esc(e.id)}">Abrir Playbook</button>
                </div>
              </div>
            `;
          }).join('')}
        </div>
      </div>

      <!-- Sub-Pestaña: Compromisos -->
      <div id="ptab-compromisos" class="p360-tab-content" style="display:none">
        <div class="compromisos-manager">
          <div class="panel-box">
            <h4>Nuevo compromiso / entregable</h4>
            <form id="formNuevoCompromiso" class="form-grid-inline">
              <input type="hidden" name="proyecto_id" value="${esc(p.id)}">
              <input type="text" name="descripcion" placeholder="Descripción de la tarea o entregable..." required>
              <select name="responsable">
                <option value="Emprendedor">Responsable: Emprendedor</option>
                <option value="Equipo LAB">Responsable: Equipo LAB</option>
                <option value="Compartido">Responsable: Compartido</option>
              </select>
              <input type="date" name="fecha_limite" value="${keyD(addDays(new Date(), 7))}">
              <button type="submit" class="btn btn-primary btn-sm">+ Agregar</button>
            </form>
          </div>

          <div class="compromisos-list" id="compromisosList">
            ${renderCompromisosList(p.id)}
          </div>
        </div>
      </div>
    `;

    // Eventos de tabs internos
    body.querySelectorAll('.p360-tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        body.querySelectorAll('.p360-tab-btn').forEach(b => b.classList.remove('is-active'));
        body.querySelectorAll('.p360-tab-content').forEach(c => c.style.display = 'none');
        btn.classList.add('is-active');
        const target = document.getElementById('ptab-' + btn.dataset.ptab);
        if (target) target.style.display = 'block';
      });
    });

    // Guardar estado de acompañamiento
    const estadoSel = document.getElementById('p360EstadoSelect');
    if (estadoSel) {
      estadoSel.addEventListener('change', () => {
        guardarPlanProyecto(p.id, { estado_acompanamiento: estadoSel.value });
      });
    }

    // Guardar notas generales
    const btnNotas = document.getElementById('btnGuardarNotasProy');
    if (btnNotas) {
      btnNotas.addEventListener('click', () => {
        const txt = document.getElementById('p360NotasGenerales')?.value || '';
        guardarPlanProyecto(p.id, { notas_generales: txt }, () => {
          showToast('Notas del proyecto guardadas.');
        });
      });
    }

    // Abrir meetings desde el listado
    body.querySelectorAll('.btn-open-meeting').forEach(btn => {
      btn.addEventListener('click', () => abrirDrawer(btn.dataset.rid));
    });

    // Formulario de nuevo compromiso
    const formComp = document.getElementById('formNuevoCompromiso');
    if (formComp) {
      formComp.addEventListener('submit', ev => {
        ev.preventDefault();
        const fd = new FormData(formComp);
        apiPost({
          accion: 'agregar_compromiso',
          proyecto_id: fd.get('proyecto_id'),
          descripcion: fd.get('descripcion'),
          responsable: fd.get('responsable'),
          fecha_limite: fd.get('fecha_limite')
        }, res => {
          if (res.ok) {
            compromisos.unshift(res.compromiso);
            document.getElementById('compromisosList').innerHTML = renderCompromisosList(p.id);
            attachCompromisosEvents();
            formComp.reset();
            showToast('Compromiso agregado.');
          }
        });
      });
    }

    attachCompromisosEvents();
  }

  function renderPostulacionOriginal(p) {
    const det = p.detalles_postulacion || {};
    const keysMap = {
      descripcion: '1. ¿De qué se trata tu proyecto?',
      diferencial: '2. ¿Cuál es el valor diferencial o único?',
      visitable: '3. ¿Qué puede ver, hacer o probar un visitante?',
      conexiones: '4. ¿Con qué otros actores o lugares se conecta?',
      producto_fisico: '5. ¿Tiene un producto físico o recuerdo asociado?',
      producto_fisico_cual: 'Detalle del producto físico',
      recursos: '6. ¿Con qué recursos físicos y humanos contás hoy?',
      falta: '7. ¿Qué te falta concretamente para vender o crecer?',
      motivacion: '8. ¿Por qué querés participar en Esquel LAB?',
      equipo: '9. ¿Quiénes integran el equipo de trabajo?',
      compromiso: '10. Disponibilidad de 12 horas semanales'
    };

    let html = `
      <div class="detail-grid">
        <div class="detail-item"><strong>Ubicación declarada:</strong> ${esc(det.ubicacion || '—')}</div>
        <div class="detail-item"><strong>Barrio / Paraje:</strong> ${esc(det.barrio || '—')}</div>
        <div class="detail-item"><strong>Antigüedad:</strong> ${esc(det.antiguedad || '—')}</div>
        <div class="detail-item"><strong>Situación actual:</strong> ${esc(det.situacion || '—')}</div>
      </div>
      <hr style="margin:16px 0;border:none;border-top:1px solid var(--linea)">
    `;

    for (const [k, label] of Object.entries(keysMap)) {
      if (det[k]) {
        html += `
          <div class="form-answer-block">
            <h5>${esc(label)}</h5>
            <p>${esc(det[k])}</p>
          </div>
        `;
      }
    }

    return html || '<p class="sub">Sin datos detallados de formulario.</p>';
  }

  function renderVotosJurado(p) {
    const votos = p.votos_jurado || [];
    if (!votos.length) {
      return '<p class="sub">Sin votos registrados para este proyecto.</p>';
    }

    return votos.map(v => {
      const score = (v.rating_perfil * 1.5 + v.rating_diferenciacion + v.rating_impacto + v.rating_viabilidad + v.rating_producto_fisico * 0.5) / 5;
      return `
        <div class="voto-card">
          <div class="voto-head">
            <strong>Juez: ${esc(v.username)}</strong>
            <span class="voto-score-badge">${score.toFixed(2)} / 5.00</span>
          </div>
          <div class="voto-breakdown">
            <span>Perfil: ${v.rating_perfil}/5</span>
            <span>Diferenciación: ${v.rating_diferenciacion}/5</span>
            <span>Impacto: ${v.rating_impacto}/5</span>
            <span>Viabilidad: ${v.rating_viabilidad}/5</span>
            <span>Prod. Físico: ${v.rating_producto_fisico}/5</span>
          </div>
          <p class="voto-comment">${esc(v.comentario)}</p>
        </div>
      `;
    }).join('');
  }

  function renderCompromisosList(pid) {
    const comps = compromisos.filter(c => c.proyecto_id === pid);
    if (!comps.length) {
      return '<p class="sub">No hay compromisos registrados todavía.</p>';
    }

    return comps.map(c => {
      const isDone = c.estado === 'cumplido';
      return `
        <div class="comp-row ${isDone ? 'is-done' : ''}" data-cid="${c.id}">
          <label class="comp-label">
            <input type="checkbox" class="chk-comp" data-cid="${c.id}" ${isDone ? 'checked' : ''}>
            <span class="comp-desc">${esc(c.descripcion)}</span>
          </label>
          <div class="comp-meta">
            <span class="badge ${c.responsable === 'Equipo LAB' ? 'w' : ''}">${esc(c.responsable)}</span>
            ${c.fecha_limite ? `<span class="sub">Vence: ${fCorta(c.fecha_limite)}</span>` : ''}
          </div>
        </div>
      `;
    }).join('');
  }

  function attachCompromisosEvents() {
    document.querySelectorAll('.chk-comp').forEach(chk => {
      chk.addEventListener('change', () => {
        const cid = chk.dataset.cid;
        const estado = chk.checked ? 'cumplido' : 'pendiente';
        apiPost({
          accion: 'toggle_compromiso',
          id: cid,
          estado: estado
        }, res => {
          if (res.ok) {
            const item = compromisos.find(x => String(x.id) === String(cid));
            if (item) item.estado = estado;
            const row = chk.closest('.comp-row');
            if (row) row.classList.toggle('is-done', chk.checked);
          }
        });
      });
    });
  }

  function guardarPlanProyecto(pid, patch, cb) {
    const p = proyectos[pid];
    if (!p) return;
    Object.assign(p, patch);
    apiPost({
      accion: 'guardar_plan_proyecto',
      proyecto_id: pid,
      estado_acompanamiento: p.estado_acompanamiento,
      notas_generales: p.notas_generales
    }, res => {
      if (res.ok && cb) cb();
    });
  }

  // -------------------------------------------------------------------------
  // 6. VISTA 3: CONSULTORES & CARGA DE TRABAJO
  // -------------------------------------------------------------------------
  function renderConsultores() {
    const grid = document.getElementById('consultoresGrid');
    if (!grid) return;

    grid.innerHTML = Object.values(consultores).map(c => {
      const mios = reuniones.filter(r => r.asistentes.includes(c.id));
      const comoSr = Object.values(proyectos).filter(p => p.consultor_sr_id === c.id);
      const comoJr = Object.values(proyectos).filter(p => p.consultor_jr_id === c.id);

      // Calcular posibles choques FIT
      const choquesFIT = mios.filter(r => enFIT(r.fecha) && ['francisco', 'agustina'].includes(c.id)).length;

      return `
        <div class="consultor-card" data-cid="${esc(c.id)}">
          <div class="cons-head">
            <div class="cons-avatar" style="background:${c.color}">${c.nombre.slice(0, 2).toUpperCase()}</div>
            <div>
              <h3 class="cons-name">${esc(c.nombre)}</h3>
              <span class="cons-rol-badge">${esc(c.rol)}</span>
            </div>
            <div class="cons-kpi">
              <strong>${mios.length}</strong>
              <span class="sub">encuentros</span>
            </div>
          </div>

          <div class="cons-proyectos-bar">
            <span><strong>${comoSr.length}</strong> conduce</span> · 
            <span><strong>${comoJr.length}</strong> acompaña</span>
          </div>

          ${choquesFIT ? `<div class="alert-strip-danger">⚠️ ${choquesFIT} reunión(es) agendada(s) durante el FIT</div>` : ''}

          <div class="cons-dispo-table">
            ${(c.disponibilidad || []).map((disp, idx) => `
              <div class="cons-dispo-col">
                <span class="dname">${DOWS[idx + 1]}</span>
                <span class="dval ${disp === 'libre' ? 'is-free' : ''}">${esc(disp)}</span>
              </div>
            `).join('')}
          </div>

          <p class="cons-restriccion-text">${esc(c.restricciones)}</p>

          <div class="cons-footer">
            <button type="button" class="btn btn-secondary btn-sm btn-ver-agenda-cons" data-cid="${esc(c.id)}">Ver agenda (${mios.length})</button>
          </div>
        </div>
      `;
    }).join('');

    grid.querySelectorAll('.btn-ver-agenda-cons').forEach(btn => {
      btn.addEventListener('click', () => {
        const cid = btn.dataset.cid;
        document.getElementById('filtroConsultor').value = cid;
        document.querySelector('.gnav-btn[data-tab="agenda"]').click();
        document.querySelector('.submode-btn[data-mode="dxd"]').click();
        renderDiaPorDia();
      });
    });
  }

  // -------------------------------------------------------------------------
  // 7. VISTA 4: ALERTAS & CONFLICTOS
  // -------------------------------------------------------------------------
  function renderAlertas() {
    const container = document.getElementById('alertasContainer');
    if (!container) return;

    const AL = [];
    const idx = getIdxFecha();

    // 1. Días sobrecargados
    DIAS.filter(f => (idx[f] || []).length >= 3).forEach(f => {
      AL.push({
        nivel: 'crit',
        titulo: `${idx[f].length} reuniones el mismo día`,
        momento: fLarga(f),
        proyectos: idx[f].map(r => proyectos[r.proyecto_id]?.nombre || r.proyecto_id).join(' · '),
        solucion: 'Considerar reprogramar una reunión a otro día del mismo ciclo para descomprimir al equipo.'
      });
    });

    // 2. Encuentros durante el FIT con personal ausente
    const evFIT = DIAS.filter(f => enFIT(f) && (idx[f] || []).length).flatMap(f => idx[f] || []);
    if (evFIT.length) {
      const afectados = evFIT.filter(r => r.asistentes.some(a => ['francisco', 'agustina'].includes(a)));
      if (afectados.length) {
        AL.push({
          nivel: 'crit',
          titulo: `${afectados.length} encuentro(s) durante el FIT con Francisco o Agustina asignados`,
          momento: '24 al 30 de septiembre de 2026',
          proyectos: afectados.map(r => `${fCorta(r.fecha)}: ${proyectos[r.proyecto_id]?.nombre}`).join(' · '),
          solucion: 'Reasignar el acompañamiento a Cesia o Noelia desde el editor del encuentro, o mover la fecha.'
        });
      }
    }

    // 3. Feriado del 12 de octubre
    (idx['2026-10-12'] || []).forEach(r => {
      AL.push({
        nivel: 'warn',
        titulo: 'Reunión agendada en feriado (12 de octubre)',
        momento: 'Lunes 12 de octubre',
        proyectos: proyectos[r.proyecto_id]?.nombre || r.proyecto_id,
        solucion: 'Verificar si el emprendedor y el consultor tienen disponibilidad ese día.'
      });
    });

    // 4. Reuniones sin asistentes asignados
    const sinAsist = reuniones.filter(r => !r.asistentes || !r.asistentes.length);
    if (sinAsist.length) {
      AL.push({
        nivel: 'warn',
        titulo: `${sinAsist.length} encuentro(s) sin consultor asignado`,
        momento: 'Pendiente',
        proyectos: sinAsist.map(r => `${fCorta(r.fecha)}: ${proyectos[r.proyecto_id]?.nombre}`).join(' · '),
        solucion: 'Abrir el encuentro y asignar al Senior y Junior correspondientes.'
      });
    }

    // 5. Brecha de más de 14 días sin encuentros
    Object.values(proyectos).forEach(p => {
      const evs = getReunionesProyecto(p.id);
      for (let i = 1; i < evs.length; i++) {
        const dd = Math.round((parseD(evs[i].fecha) - parseD(evs[i - 1].fecha)) / (1000 * 60 * 60 * 24));
        if (dd > 14) {
          AL.push({
            nivel: 'info',
            titulo: `${p.nombre}: ${dd} días sin encuentro`,
            momento: `Del ${fCorta(evs[i - 1].fecha)} al ${fCorta(evs[i].fecha)}`,
            proyectos: p.nombre,
            solucion: `Coordinar un toque intermedio de seguimiento con ${consultores[p.consultor_jr_id]?.nombre || 'el Junior'}.`
          });
        }
      }
    });

    const ordenNivel = { crit: 0, warn: 1, info: 2 };
    AL.sort((a, b) => ordenNivel[a.nivel] - ordenNivel[b.nivel]);

    container.innerHTML = AL.map(al => `
      <div class="alerta-card is-${al.nivel}">
        <div class="al-head">
          <strong>${esc(al.titulo)}</strong>
          <span class="sub">${esc(al.momento)}</span>
        </div>
        <div class="al-p">${esc(al.proyectos)}</div>
        <div class="al-fix">💡 ${esc(al.solucion)}</div>
      </div>
    `).join('');
  }

  // -------------------------------------------------------------------------
  // 8. DRAWER / PLAYBOOK DEL ENCUENTRO
  // -------------------------------------------------------------------------
  const scrim = document.getElementById('drawerScrim');
  const drawer = document.getElementById('reunionDrawer');
  let reunionActiva = null;

  function abrirDrawer(rid) {
    const r = reuniones.find(x => x.id === rid);
    if (!r) return;
    reunionActiva = r;
    const p = proyectos[r.proyecto_id] || {};

    document.getElementById('drawerKick').innerHTML = `
      <span class="dot" style="background:${COLOR_TIPO[r.tipo] || '#888'}"></span>
      ${LABEL_TIPO[r.tipo] || r.tipo} · <strong>${esc(p.nombre || r.proyecto_id)}</strong>
    `;
    document.getElementById('drawerTitle').textContent = `#${r.numero_reunion} · ${r.titulo}`;
    document.getElementById('drawerMeta').textContent = `${fLarga(r.fecha)} ${r.hora_inicio ? `· ${r.hora_inicio} a ${r.hora_fin}` : ''} · ${r.lugar}`;

    renderDrawerBody();

    scrim.classList.add('is-open');
    drawer.classList.add('is-open');
  }

  function cerrarDrawer() {
    scrim.classList.remove('is-open');
    drawer.classList.remove('is-open');
    reunionActiva = null;
    refrescarTodo();
  }

  function renderDrawerBody() {
    const body = document.getElementById('drawerBody');
    const r = reunionActiva;
    if (!body || !r) return;

    const p = proyectos[r.proyecto_id] || {};
    const pr = getProgresoCheck(r);

    // Alertas de conflicto dentro del modal
    let warningHtml = '';
    if (enFIT(r.fecha)) {
      const ausentes = r.asistentes.filter(a => ['francisco', 'agustina'].includes(a)).map(a => consultores[a]?.nombre || a);
      warningHtml += `<div class="alert-strip-danger">⚠️ Encuentro durante el FIT (24 al 30 sep).${ausentes.length ? ` ${ausentes.join(' y ')} no estarán en la ciudad.` : ''}</div>`;
    }
    if (esFeriado(r.fecha)) {
      warningHtml += `<div class="alert-strip-warning">⚠️ Feriado probable sin verificar (12 de octubre).</div>`;
    }

    body.innerHTML = `
      ${warningHtml}

      <!-- PROGRAMACION Y HORARIOS -->
      <div class="rsec">
        <h5>Programación del encuentro</h5>
        <div class="form-grid-3">
          <div>
            <label class="lbl-mini">Fecha:</label>
            <input type="date" id="drwFecha" value="${esc(r.fecha)}">
          </div>
          <div>
            <label class="lbl-mini">Hora inicio:</label>
            <input type="time" id="drwHoraInicio" value="${esc(r.hora_inicio)}">
          </div>
          <div>
            <label class="lbl-mini">Hora fin:</label>
            <input type="time" id="drwHoraFin" value="${esc(r.hora_fin)}">
          </div>
        </div>

        <div class="form-grid-2" style="margin-top:10px">
          <div>
            <label class="lbl-mini">Lugar / Sede:</label>
            <input type="text" id="drwLugar" value="${esc(r.lugar)}">
          </div>
          <div>
            <label class="lbl-mini">Tipo de encuentro:</label>
            <select id="drwTipo">
              <option value="ind" ${r.tipo === 'ind' ? 'selected' : ''}>Individual</option>
              <option value="gru" ${r.tipo === 'gru' ? 'selected' : ''}>Célula / Grupal</option>
              <option value="ter" ${r.tipo === 'ter' ? 'selected' : ''}>Terreno</option>
              <option value="cie" ${r.tipo === 'cie' ? 'selected' : ''}>Cierre</option>
            </select>
          </div>
        </div>
      </div>

      <!-- ASISTENTES / CONSULTORES -->
      <div class="rsec">
        <h5>Equipo consultor asignado</h5>
        <div class="chips-selector">
          ${Object.values(consultores).map(c => {
            const on = r.asistentes.includes(c.id);
            const fitConflicto = on && enFIT(r.fecha) && ['francisco', 'agustina'].includes(c.id);
            return `
              <button type="button" class="chip-btn ${on ? 'is-on' : ''} ${fitConflicto ? 'is-warn' : ''}" data-cid="${esc(c.id)}">
                ${esc(c.nombre)}
              </button>
            `;
          }).join('')}
        </div>
      </div>

      <!-- PLAYBOOK PEDAGOGICO: GUIA DEL CONSULTOR -->
      <div class="rsec">
        <h5>📖 Guía para el consultor (Contexto y Enfoque)</h5>
        <div class="playbook-guide-box">
          <p>${esc(r.guia_consultor || 'Revisar la postulación y plan estratégico antes de ingresar.')}</p>
        </div>
      </div>

      <!-- PREGUNTAS CLAVE PUNZANTES -->
      <div class="rsec">
        <h5>🎯 Preguntas clave a realizar</h5>
        <ul class="bullet-list">
          ${(r.preguntas_clave || []).map(q => `<li><strong>«${esc(q)}»</strong></li>`).join('') || '<li>Sin preguntas clave cargadas.</li>'}
        </ul>
      </div>

      <!-- CHECKLIST INTERACTIVO DE TAREAS -->
      <div class="rsec">
        <div class="rsec-head">
          <h5>Checklist de objetivos</h5>
          ${pr ? `<span class="sub" id="drwCheckStats">${pr.hechos} de ${pr.total}</span>` : ''}
        </div>
        <div class="checklist-box">
          ${(r.checklist || []).map(it => `
            <label class="chk-item">
              <input type="checkbox" class="drw-chk" data-cid="${it.id}" ${it.done ? 'checked' : ''}>
              <span class="chk-text">${esc(it.texto)}</span>
            </label>
          `).join('')}
        </div>
      </div>

      <!-- REGISTRO DE ASISTENCIA Y HORARIO REAL -->
      <div class="rsec">
        <h5>Registro de Asistencia Efectiva</h5>
        <div class="form-grid-3">
          <div>
            <label class="lbl-mini">¿Asistió?</label>
            <select id="drwAsistencia">
              <option value="0" ${r.asistencia_estado === 0 ? 'selected' : ''}>Pendiente</option>
              <option value="1" ${r.asistencia_estado === 1 ? 'selected' : ''}>✓ Sí asistió</option>
              <option value="-1" ${r.asistencia_estado === -1 ? 'selected' : ''}>✗ No asistió / Ausente</option>
            </select>
          </div>
          <div>
            <label class="lbl-mini">Hora real inicio:</label>
            <input type="time" id="drwHoraRealInicio" value="${esc(r.hora_real_inicio || '')}">
          </div>
          <div>
            <label class="lbl-mini">Hora real fin:</label>
            <input type="time" id="drwHoraRealFin" value="${esc(r.hora_real_fin || '')}">
          </div>
        </div>
      </div>

      <!-- BITACORA Y MINUTA DEL ENCUENTRO -->
      <div class="rsec">
        <h5>Minuta y notas del encuentro</h5>
        <textarea id="drwMinutaNotas" rows="4" class="form-textarea" placeholder="Qué se acordó, qué dificultades se detectaron, qué tareas quedan para el próximo encuentro...">${esc(r.minuta_notas || '')}</textarea>
      </div>
    `;

    // Toggle checklist asíncrono
    body.querySelectorAll('.drw-chk').forEach(cb => {
      cb.addEventListener('change', () => {
        const checkId = Number(cb.dataset.cid);
        const done = cb.checked;
        apiPost({
          accion: 'toggle_checklist',
          reunion_id: r.id,
          check_id: checkId,
          done: done
        }, res => {
          if (res.ok) {
            r.checklist = res.checklist;
            const nuevoPr = getProgresoCheck(r);
            const statsSpan = document.getElementById('drwCheckStats');
            if (statsSpan && nuevoPr) {
              statsSpan.textContent = `${nuevoPr.hechos} de ${nuevoPr.total}`;
            }
          }
        });
      });
    });

    // Toggle asistentes
    body.querySelectorAll('.chip-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const cid = btn.dataset.cid;
        const idx = r.asistentes.indexOf(cid);
        if (idx >= 0) {
          r.asistentes.splice(idx, 1);
          btn.classList.remove('is-on');
        } else {
          r.asistentes.push(cid);
          btn.classList.add('is-on');
        }
      });
    });
  }

  // Guardar cambios globales del encuentro
  document.getElementById('btnDrawerSave')?.addEventListener('click', () => {
    if (!reunionActiva) return;
    const r = reunionActiva;

    r.fecha = document.getElementById('drwFecha')?.value || r.fecha;
    r.hora_inicio = document.getElementById('drwHoraInicio')?.value || '';
    r.hora_fin = document.getElementById('drwHoraFin')?.value || '';
    r.lugar = document.getElementById('drwLugar')?.value || r.lugar;
    r.tipo = document.getElementById('drwTipo')?.value || r.tipo;
    r.asistencia_estado = Number(document.getElementById('drwAsistencia')?.value || 0);
    r.hora_real_inicio = document.getElementById('drwHoraRealInicio')?.value || '';
    r.hora_real_fin = document.getElementById('drwHoraRealFin')?.value || '';
    r.minuta_notas = document.getElementById('drwMinutaNotas')?.value || '';

    apiPost({
      accion: 'guardar_reunion',
      id: r.id,
      fecha: r.fecha,
      hora_inicio: r.hora_inicio,
      hora_fin: r.hora_fin,
      lugar: r.lugar,
      tipo: r.tipo,
      estado: r.estado,
      asistencia_estado: r.asistencia_estado,
      hora_real_inicio: r.hora_real_inicio,
      hora_real_fin: r.hora_real_fin,
      minuta_notas: r.minuta_notas,
      asistentes: r.asistentes
    }, res => {
      if (res.ok) {
        showToast('Encuentro guardado correctamente.');
        cerrarDrawer();
      }
    });
  });

  document.getElementById('drawerClose')?.addEventListener('click', cerrarDrawer);
  document.getElementById('btnDrawerCancel')?.addEventListener('click', cerrarDrawer);
  scrim?.addEventListener('click', cerrarDrawer);
  document.addEventListener('keydown', ev => {
    if (ev.key === 'Escape' && reunionActiva) cerrarDrawer();
  });

  // -------------------------------------------------------------------------
  // 9. EXPORTACION Y HERRAMIENTAS
  // -------------------------------------------------------------------------
  function descargarArchivo(texto, nombre, mime) {
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([texto], { type: mime }));
    a.download = nombre;
    document.body.appendChild(a);
    a.click();
    setTimeout(() => {
      document.body.removeChild(a);
      URL.revokeObjectURL(a.href);
    }, 100);
  }

  document.getElementById('btnExportJSON')?.addEventListener('click', () => {
    const payload = { meta, celulas, consultores, proyectos, reuniones, compromisos };
    descargarArchivo(JSON.stringify(payload, null, 2), 'esquel-lab-gestion-2026.json', 'application/json');
  });

  document.getElementById('btnExportTXT')?.addEventListener('click', () => {
    let txt = `ESQUEL LAB · AGENDA Y GESTIÓN DE LA 1ª COHORTE\n9 de septiembre al 10 de noviembre de 2026\n=======================================================\n\n`;
    const idx = getIdxFecha();
    DIAS.filter(f => (idx[f] || []).length).forEach(f => {
      txt += `${fLarga(f).toUpperCase()}${enFIT(f) ? ' [PERÍODO FIT]' : ''}\n-------------------------------------------------------\n`;
      (idx[f] || []).forEach(r => {
        const p = proyectos[r.proyecto_id] || {};
        const asist = r.asistentes.map(aid => consultores[aid]?.nombre || aid).join(', ');
        txt += `• ${p.nombre || r.proyecto_id} (#${r.numero_reunion}) — ${r.titulo}\n`;
        txt += `  Lugar: ${r.lugar} | Horario: ${r.hora_inicio || 'A conf.'} a ${r.hora_fin || 'A conf.'}\n`;
        txt += `  Asistentes: ${asist || 'Sin asignar'}\n`;
        if (r.checklist && r.checklist.length) {
          txt += `  Checklist:\n`;
          r.checklist.forEach(it => {
            txt += `    [${it.done ? 'X' : ' '}] ${it.texto}\n`;
          });
        }
        if (r.minuta_notas) {
          txt += `  Notas: ${r.minuta_notas}\n`;
        }
        txt += `\n`;
      });
    });
    descargarArchivo(txt, 'agenda-esquel-lab-2026.txt', 'text/plain');
  });

  // -------------------------------------------------------------------------
  // 10. REFRESCAR TODO Y CAMBIO DE TABS
  // -------------------------------------------------------------------------
  function refrescarTodo() {
    renderStrip();
    renderStats();
    renderCalendario();
    renderDiaPorDia();
    renderGantt();
    renderExpedienteSidebar();
    renderExpedienteDetalle();
    renderConsultores();
    renderAlertas();
  }

  // Cambio de pestaña principal
  document.querySelectorAll('.gnav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.gnav-btn').forEach(b => b.classList.remove('is-active'));
      document.querySelectorAll('.gtab-content').forEach(c => c.style.display = 'none');
      btn.classList.add('is-active');
      const target = document.getElementById('tab-' + btn.dataset.tab);
      if (target) target.style.display = 'block';

      if (btn.dataset.tab === 'proyectos') renderExpedienteSidebar();
      if (btn.dataset.tab === 'consultores') renderConsultores();
      if (btn.dataset.tab === 'alertas') renderAlertas();
    });
  });

  // Cambio de sub-modo en Agenda
  document.querySelectorAll('.submode-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.submode-btn').forEach(b => b.classList.remove('is-active'));
      document.querySelectorAll('.submode-view').forEach(v => v.style.display = 'none');
      btn.classList.add('is-active');
      const target = document.getElementById('sub-' + btn.dataset.mode);
      if (target) target.style.display = 'block';

      if (btn.dataset.mode === 'cal') renderCalendario();
      if (btn.dataset.mode === 'dxd') renderDiaPorDia();
      if (btn.dataset.mode === 'gantt') renderGantt();
    });
  });

  // Filtros de agenda
  ['filtroCelula', 'filtroConsultor', 'filtroTipo'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', () => {
      renderCalendario();
      renderDiaPorDia();
    });
  });

  // Búsqueda en sidebar de proyectos
  document.getElementById('searchProyecto')?.addEventListener('input', () => {
    renderExpedienteSidebar();
  });

  // Helpers de red
  function apiPost(payload, cb) {
    payload.csrf_token = csrf;
    fetch('gestion_api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
      .then(res => res.json())
      .then(res => {
        if (!res.ok) {
          alert('Error: ' + (res.error || 'No se pudo completar la acción.'));
        } else {
          cb(res);
        }
      })
      .catch(err => {
        alert('Error de conexión con el servidor.');
      });
  }

  function showToast(msg) {
    let t = document.getElementById('gestionToast');
    if (!t) {
      t = document.createElement('div');
      t.id = 'gestionToast';
      t.className = 'gestion-toast';
      document.body.appendChild(t);
    }
    t.textContent = msg;
    t.classList.add('is-show');
    setTimeout(() => t.classList.remove('is-show'), 2500);
  }

  // Inicialización
  refrescarTodo();

})();
