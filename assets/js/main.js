// assets/js/main.js
// Javascript para el Frontend del Laboratorio de Destino Esquel

document.addEventListener('DOMContentLoaded', () => {
    initMultiStepForm();
    initMediaKitCopy();
});

/**
 * Control del Formulario Multi-Etapa
 */
function initMultiStepForm() {
    const form = document.getElementById('postulacionForm');
    if (!form) return;

    const steps = Array.from(document.querySelectorAll('.form-step'));
    const dots = Array.from(document.querySelectorAll('.step-dot'));
    const nextButtons = document.querySelectorAll('.btn-next');
    const prevButtons = document.querySelectorAll('.btn-prev');
    let currentStep = 0;

    // Manejo de preguntas condicionales según el programa seleccionado
    const programRadios = document.querySelectorAll('input[name="program"]');
    const aceleraFields = document.getElementById('aceleraFields');
    const raizFields = document.getElementById('raizFields');

    function toggleProgramFields() {
        const selectedProgram = document.querySelector('input[name="program"]:checked')?.value;
        if (selectedProgram === 'Acelera') {
            if (aceleraFields) aceleraFields.style.display = 'block';
            if (raizFields) raizFields.style.display = 'none';
            // Configurar required para los campos de Acelera
            setRequiredState(aceleraFields, true);
            setRequiredState(raizFields, false);
        } else if (selectedProgram === 'Raiz') {
            if (aceleraFields) aceleraFields.style.display = 'none';
            if (raizFields) raizFields.style.display = 'block';
            // Configurar required para los campos de Raíz
            setRequiredState(aceleraFields, false);
            setRequiredState(raizFields, true);
        }
    }

    function setRequiredState(container, state) {
        if (!container) return;
        const inputs = container.querySelectorAll('.form-input, textarea');
        inputs.forEach(input => {
            if (state) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });
    }

    // Inicializar estado condicional
    programRadios.forEach(radio => {
        radio.addEventListener('change', toggleProgramFields);
    });
    toggleProgramFields();

    // Actualizar vista de etapas
    function updateFormSteps() {
        steps.forEach((step, idx) => {
            step.classList.toggle('active', idx === currentStep);
        });

        dots.forEach((dot, idx) => {
            dot.classList.toggle('active', idx === currentStep);
            dot.classList.toggle('completed', idx < currentStep);
        });
        
        // Auto scroll hacia el inicio del formulario
        const rect = form.getBoundingClientRect();
        if (rect.top < 0) {
            window.scrollTo({
                top: window.scrollY + rect.top - 100,
                behavior: 'smooth'
            });
        }
    }

    // Validación de la etapa actual
    function validateStep(stepIdx) {
        const activeStepEl = steps[stepIdx];
        const inputs = Array.from(activeStepEl.querySelectorAll('[required]'));
        
        let isValid = true;
        inputs.forEach(input => {
            if (input.type === 'checkbox') {
                if (!input.checked) {
                    isValid = false;
                    input.classList.add('invalid-input');
                } else {
                    input.classList.remove('invalid-input');
                }
            } else if (input.type === 'radio') {
                const name = input.name;
                const checked = activeStepEl.querySelector(`input[name="${name}"]:checked`);
                if (!checked) {
                    isValid = false;
                    // Resaltar visualmente las opciones de radio
                    const radioGroup = activeStepEl.querySelector('.form-radio-group');
                    if (radioGroup) radioGroup.style.borderColor = '#b02a53';
                } else {
                    const radioGroup = activeStepEl.querySelector('.form-radio-group');
                    if (radioGroup) radioGroup.style.borderColor = '';
                }
            } else {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#b02a53';
                } else {
                    input.style.borderColor = '';
                }
            }
        });

        return isValid;
    }

    // Listeners botones Siguiente
    nextButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                currentStep++;
                if (currentStep >= steps.length) currentStep = steps.length - 1;
                updateFormSteps();
            } else {
                // Alerta suave de validación
                alert('Por favor complete todos los campos obligatorios antes de continuar.');
            }
        });
    });

    // Listeners botones Anterior
    prevButtons.forEach(button => {
        button.addEventListener('click', () => {
            currentStep--;
            if (currentStep < 0) currentStep = 0;
            updateFormSteps();
        });
    });

    // Validar en el submit final por seguridad
    form.addEventListener('submit', (e) => {
        if (!validateStep(currentStep)) {
            e.preventDefault();
            alert('Por favor verifique las respuestas antes de enviar la postulación.');
        }
    });
}

/**
 * Copiar Notas de Prensa en el Media Kit
 */
function initMediaKitCopy() {
    const copyButtons = document.querySelectorAll('.btn-copy');
    copyButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.getAttribute('data-target');
            const textEl = document.getElementById(targetId);
            if (!textEl) return;

            // Seleccionar y copiar texto
            const textToCopy = textEl.textContent.trim();
            navigator.clipboard.writeText(textToCopy).then(() => {
                const originalText = button.innerHTML;
                button.innerHTML = '¡Copiado al portapapeles!';
                button.style.backgroundColor = '#236f4c';
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.style.backgroundColor = '';
                }, 2000);
            }).catch(err => {
                console.error('Error al copiar texto: ', err);
            });
        });
    });
}
