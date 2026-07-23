// assets/js/crm.js
// Script de administración para el CRM de Laboratorio de Destino Esquel

document.addEventListener('DOMContentLoaded', () => {
    initViewToggles();
    initCRMDrawer();
});

/**
 * Alternar entre Vista de Tabla y Vista de Tarjetas (Kanban)
 */
function initViewToggles() {
    const listBtn = document.getElementById('btnListView');
    const cardBtn = document.getElementById('btnCardView');
    const listContainer = document.getElementById('listViewContainer');
    const cardContainer = document.getElementById('cardViewContainer');

    if (!listBtn || !cardBtn) return;

    listBtn.addEventListener('click', () => {
        listBtn.classList.add('active');
        cardBtn.classList.remove('active');
        if (listContainer) listContainer.style.display = 'block';
        if (cardContainer) cardContainer.style.display = 'none';
        localStorage.setItem('crm_view', 'list');
    });

    cardBtn.addEventListener('click', () => {
        cardBtn.classList.add('active');
        listBtn.classList.remove('active');
        if (cardContainer) cardContainer.style.display = 'grid';
        if (listContainer) listContainer.style.display = 'none';
        localStorage.setItem('crm_view', 'card');
    });

    // Cargar preferencia guardada
    const savedView = localStorage.getItem('crm_view');
    if (savedView === 'card') {
        cardBtn.click();
    } else {
        listBtn.click();
    }
}

/**
 * Cajón Lateral (Drawer) de Detalles e Interacción
 */
function initCRMDrawer() {
    const backdrop = document.getElementById('drawerBackdrop');
    const drawer = document.getElementById('crmDrawer');
    const closeBtn = document.getElementById('drawerClose');

    if (!drawer || !backdrop) return;

    // Elementos del Drawer
    const nameEl = document.getElementById('drawerAppName');
    const contactEl = document.getElementById('drawerAppContact');
    const emailEl = document.getElementById('drawerAppEmail');
    const phoneEl = document.getElementById('drawerAppPhone');
    const programEl = document.getElementById('drawerAppProgram');
    const dateEl = document.getElementById('drawerAppDate');
    const answersContainer = document.getElementById('drawerAnswersContainer');
    
    // Controles de evaluación
    const appStatusSelect = document.getElementById('appStatusSelect');
    const appNotesTextarea = document.getElementById('appNotesTextarea');
    const sliders = document.querySelectorAll('.rating-slider');
    const avgScoreEl = document.getElementById('avgScoreVal');
    const saveActionsBtn = document.getElementById('btnSaveActions');

    let activeAppId = null;

    // Función para abrir el cajón lateral con datos
    window.openAppDrawer = function(appId, appDataString) {
        try {
            const data = JSON.parse(decodeURIComponent(appDataString));
            activeAppId = appId;
            
            // Inyectar datos básicos
            nameEl.textContent = data.name;
            contactEl.textContent = data.contact_name;
            emailEl.textContent = data.email;
            emailEl.href = `mailto:${data.email}`;
            phoneEl.textContent = data.phone;
            phoneEl.href = `tel:${data.phone}`;
            programEl.textContent = data.program === 'Acelera' ? 'Esquel Acelera (Urbano)' : 'Raíz (Rural)';
            dateEl.textContent = data.submitted_at;
            
            // Inyectar respuestas dinámicas
            answersContainer.innerHTML = '';
            if (data.details && data.details.length > 0) {
                data.details.forEach(detail => {
                    const row = document.createElement('div');
                    row.className = 'detail-row';
                    
                    const label = document.createElement('div');
                    label.className = 'detail-label';
                    label.textContent = formatFieldLabel(detail.field_key);
                    
                    const val = document.createElement('div');
                    val.className = 'detail-val';
                    val.textContent = detail.field_value;
                    
                    row.appendChild(label);
                    row.appendChild(val);
                    answersContainer.appendChild(row);
                });
            } else {
                answersContainer.innerHTML = '<p class="detail-val" style="font-style:italic; color:var(--color-text-secondary);">No se registraron respuestas específicas.</p>';
            }

            // Inyectar notas y estados actuales
            appStatusSelect.value = data.stage;
            appNotesTextarea.value = data.notes || '';

            // Configurar Sliders
            document.getElementById('sliderDiferenciacion').value = data.rating_diferenciacion || 0;
            document.getElementById('sliderImpacto').value = data.rating_impacto || 0;
            document.getElementById('sliderPerfil').value = data.rating_perfil || 0;
            document.getElementById('sliderProductoFisico').value = data.rating_producto_fisico || 0;
            document.getElementById('sliderViabilidad').value = data.rating_viabilidad || 0;

            // Actualizar etiquetas de número de los sliders
            sliders.forEach(slider => {
                const badge = document.getElementById(slider.id + 'Val');
                if (badge) badge.textContent = slider.value;
            });

            calculateAverage();

            // Mostrar Drawer
            backdrop.style.display = 'block';
            setTimeout(() => {
                backdrop.style.opacity = '1';
                drawer.classList.add('active');
            }, 10);

        } catch (e) {
            console.error("Error al abrir detalles:", e);
        }
    };

    // Formatear etiquetas de campo del formulario dinámico
    function formatFieldLabel(key) {
        const labels = {
            'descripcion': 'Descripción del Servicio o Negocio Urbano',
            'etapa': 'Etapa de Desarrollo Actual',
            'producto_integracion': 'Integración de Producto Físico (Economía de Recuerdos)',
            'tipo_establecimiento': 'Tipo de Establecimiento y Ubicación Rural',
            'proceso_visitable': 'Procesos Productivos / Saberes Visibles',
            'producto_conector': 'Producto de Campo como Objeto Conector',
            'motivacion': 'Motivación para Acompañamiento Personalizado',
            'compromiso_tiempo': 'Compromiso de Dedicación Semanal'
        };
        return labels[key] || key;
    }

    // Cerrar cajón
    function closeDrawer() {
        backdrop.style.opacity = '0';
        drawer.classList.remove('active');
        setTimeout(() => {
            backdrop.style.display = 'none';
        }, 300);
    }

    closeBtn.addEventListener('click', closeDrawer);
    backdrop.addEventListener('click', closeDrawer);

    // Calcular promedio en tiempo real
    sliders.forEach(slider => {
        slider.addEventListener('input', () => {
            const badge = document.getElementById(slider.id + 'Val');
            if (badge) badge.textContent = slider.value;
            calculateAverage();
        });
    });

    function calculateAverage() {
        let total = 0;
        sliders.forEach(slider => {
            total += parseInt(slider.value);
        });
        const avg = (total / sliders.length).toFixed(1);
        avgScoreEl.textContent = avg;
    }

    // Guardar Acciones / Notas / Evaluaciones por AJAX
    saveActionsBtn.addEventListener('click', () => {
        if (!activeAppId) return;

        saveActionsBtn.setAttribute('disabled', 'disabled');
        const originalText = saveActionsBtn.textContent;
        saveActionsBtn.textContent = 'Guardando...';

        const payload = {
            action: 'update_application',
            id: activeAppId,
            stage: appStatusSelect.value,
            notes: appNotesTextarea.value,
            rating_diferenciacion: parseInt(document.getElementById('sliderDiferenciacion').value),
            rating_impacto: parseInt(document.getElementById('sliderImpacto').value),
            rating_perfil: parseInt(document.getElementById('sliderPerfil').value),
            rating_producto_fisico: parseInt(document.getElementById('sliderProductoFisico').value),
            rating_viabilidad: parseInt(document.getElementById('sliderViabilidad').value)
        };

        fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                closeDrawer();
                // Recargar página para reflejar cambios en la tabla/columnas kanban sin lógica compleja de dom
                window.location.reload();
            } else {
                alert('Ocurrió un error al guardar los cambios: ' + response.error);
                saveActionsBtn.removeAttribute('disabled');
                saveActionsBtn.textContent = originalText;
            }
        })
        .catch(err => {
            console.error("Error al actualizar:", err);
            alert('Error de conexión con el servidor.');
            saveActionsBtn.removeAttribute('disabled');
            saveActionsBtn.textContent = originalText;
        });
    });
}
