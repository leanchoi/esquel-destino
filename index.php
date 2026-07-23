<?php
// Creado para el Laboratorio de Destino Esquel
// index.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorio de Destino Esquel · Convocatoria Cohorte 2026</title>
    <meta name="description" content="Aceleramos y estructuramos comercialmente experiencias turísticas urbanas y rurales en Esquel. Co-creación práctica con el sector público-privado.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg-vignette"></div>
    
    <!-- Hero Background SVG (Vectorial, Patagonian Mountain Lines) -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100vh; overflow: hidden; z-index: -2; background: linear-gradient(180deg, #161a1e 0%, #111315 100%);">
        <svg viewBox="0 0 1440 800" fill="none" xmlns="http://www.w3.org/2000/svg" style="position: absolute; bottom: 0; width: 100%; height: auto; opacity: 0.15;">
            <path d="M0 800L250 500L500 680L800 380L1100 620L1440 300V800H0Z" fill="url(#mountainGrad)"/>
            <path d="M150 800L450 550L750 710L1050 480L1350 690L1440 620V800H150Z" fill="url(#mountainGrad2)" opacity="0.5"/>
            <defs>
                <linearGradient id="mountainGrad" x1="720" y1="300" x2="720" y2="800" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#b02a53" stop-opacity="0.3"/>
                    <stop offset="1" stop-color="#111315"/>
                </linearGradient>
                <linearGradient id="mountainGrad2" x1="795" y1="480" x2="795" y2="800" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#236f4c" stop-opacity="0.2"/>
                    <stop offset="1" stop-color="#111315"/>
                </linearGradient>
            </defs>
        </svg>
    </div>

    <!-- Header Navigation -->
    <header class="header">
        <div class="container header-container">
            <a href="index.php" class="logo-link">
                <img src="assets/images/logo-lab-white.png" alt="Esquel LAB" class="logo-img">
            </a>
            <nav class="nav">
                <ul class="nav-list">
                    <li><a href="#metodo" class="nav-link">Metodología</a></li>
                    <li><a href="#programas" class="nav-link">Programas</a></li>
                    <li><a href="#convocatoria" class="nav-link">Convocatoria</a></li>
                    <li><a href="#comunidad" class="nav-link">Comunidad</a></li>
                    <li><a href="media-kit.php" class="nav-link">Sala de Prensa</a></li>
                    <li><a href="inscribirse.php" class="btn btn-primary btn-sm">Inscribirse</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="section" style="padding-top: 180px; min-height: 100vh; display: flex; align-items: center;">
        <div class="container">
            <div class="hero-content">
                <span class="hero-subtitle text-mono">Esquel LAB · Laboratorio de Destino</span>
                <h1 class="hero-title">Hagamos juntos.<br>Dejemos cosas andando.</h1>
                <p class="hero-description">
                    Una propuesta municipal disruptiva de fomento turístico. No te capacitamos en teoría; nos ponemos a tu disposición con un equipo de idóneos para estructurar y acelerar comercialmente tus propuestas turísticas y rurales en ciclos cortos, ágiles y reales.
                </p>
                <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                    <a href="inscribirse.php" class="btn btn-primary">Postularse a la Cohorte 2026</a>
                    <a href="#metodo" class="btn btn-secondary">Conocer la Metodología</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Metodología Section -->
    <section id="metodo" class="section" style="border-top: 1px solid var(--glass-border);">
        <div class="container">
            <div class="grid-2" style="align-items: center;">
                <div>
                    <span class="text-mono" style="color: var(--color-wild-berry); font-size: 0.85rem; font-weight: 600;">Metodología Práctica</span>
                    <h2 style="font-size: 2.5rem; margin-top: 10px;">La Economía de los Recuerdos</h2>
                    <p>
                        Los destinos turísticos no compiten únicamente por la espectacularidad de sus paisajes; compiten por el lugar emocional que logran ocupar en la memoria de quienes los visitan.
                    </p>
                    <p>
                        Bajo esta premisa, nuestra metodología integra la dimensión de las <strong>experiencias</strong> (el viaje memorable) y los <strong>objetos locales con identidad</strong> (el Sello Hecho en Esquel) como vehículos que mantienen vivos esos recuerdos en el tiempo, elevando el ticket promedio de consumo y fortaleciendo a los productores locales.
                    </p>
                    <div class="scarcity-box">
                        <h4 style="margin-bottom: 8px;">Acompañamiento 1:1 Personalizado</h4>
                        <p style="margin-bottom: 0; font-size: 0.95rem; color: #a1a5ab;">
                            El proceso es corto e intensivo. Trabajamos codo a codo en el territorio para definir itinerarios, costear precios de venta para operadores turísticos, habilitar canales de reserva digital y dotar a cada propuesta de fichas comerciales listas para vender.
                        </p>
                    </div>
                </div>
                <div class="card" style="background: linear-gradient(135deg, var(--glass-bg) 0%, rgba(176, 42, 83, 0.05) 100%);">
                    <h3 style="margin-bottom: 24px; color: var(--color-wild-berry);">¿Qué entregamos al finalizar?</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 16px; display: flex; gap: 12px;">
                            <span style="color: var(--color-wild-berry); font-weight: bold;">✓</span>
                            <div>
                                <strong>Ficha Técnica Comercial:</strong> Estructura de tarifas neta y detalles de operación para comercialización directa e indirecta.
                            </div>
                        </li>
                        <li style="margin-bottom: 16px; display: flex; gap: 12px;">
                            <span style="color: var(--color-wild-berry); font-weight: bold;">✓</span>
                            <div>
                                <strong>Canal Digital Mínimo:</strong> Habilitación funcional de reservas y contacto digital para operar de inmediato.
                            </div>
                        </li>
                        <li style="margin-bottom: 16px; display: flex; gap: 12px;">
                            <span style="color: var(--color-wild-berry); font-weight: bold;">✓</span>
                            <div>
                                <strong>Registro Promocional:</strong> Registro fotográfico básico de la experiencia y diseño de su guión interpretativo.
                            </div>
                        </li>
                        <li style="margin-bottom: 0; display: flex; gap: 12px;">
                            <span style="color: var(--color-wild-berry); font-weight: bold;">✓</span>
                            <div>
                                <strong>Anclaje Productivo:</strong> Evaluación de viabilidad de su producto físico de identidad y postulación directa al sello "Hecho en Esquel".
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Programas Section -->
    <section id="programas" class="section" style="background-color: rgba(255, 255, 255, 0.01); border-top: 1px solid var(--glass-border);">
        <div class="container">
            <div style="text-align: center; max-width: 700px; margin: 0 auto 60px auto;">
                <span class="text-mono" style="color: var(--color-wild-berry); font-size: 0.85rem; font-weight: 600;">Dos líneas complementarias</span>
                <h2 style="font-size: 2.5rem; margin-top: 10px;">Programas de Aceleración 2026</h2>
                <p>
                    Dividimos las postulaciones según el ámbito y madurez de la propuesta. Ambas líneas trabajan con metodologías ágiles coordinadas de 8 semanas.
                </p>
            </div>

            <div class="grid-2">
                <!-- Esquel Acelera -->
                <div class="card program-card">
                    <div class="program-card-header">
                        <img src="assets/images/logo-acelera.png" alt="Esquel Acelera" style="height: 64px; margin-bottom: 16px;">
                        <span class="program-badge badge-acelera text-mono">Aceleración Urbana</span>
                        <p style="font-size: 0.95rem; margin-bottom: 0;">
                            Orientado a consolidar experiencias en el ejido urbano: gastronomía temática, talleres de artesanos, circuitos culturales y paseos activos únicos.
                        </p>
                    </div>
                    <ul class="program-features">
                        <li><strong>Destinatarios:</strong> Emprendedores urbanos con servicios activos o saberes transformables en experiencia.</li>
                        <li><strong>Economía de recuerdos:</strong> Incorporación obligatoria de productos físicos identitarios asociados.</li>
                        <li><strong>Cupos Limitados:</strong> De 8 a 10 experiencias estructuradas por cohorte.</li>
                        <li><strong>Duración:</strong> 8 semanas de trabajo intensivo 1:1 y grupal.</li>
                    </ul>
                    <a href="inscribirse.php?linea=Acelera" class="btn btn-primary" style="margin-top: 24px;">Postularse a Acelera</a>
                </div>

                <!-- Esquel Raíz -->
                <div class="card program-card" style="border-color: rgba(35, 111, 76, 0.25);">
                    <div class="program-card-header">
                        <img src="assets/images/logo-raiz.png" alt="Esquel Raíz" style="height: 64px; margin-bottom: 16px;">
                        <span class="program-badge badge-raiz text-mono">Desarrollo Rural</span>
                        <p style="font-size: 0.95rem; margin-bottom: 0;">
                            Dedicado a estructurar el turismo rural andino y de estepa. Diseñamos guiones interpretativos y protocolos seguros sobre la labor tradicional del campo patagónico.
                        </p>
                    </div>
                    <ul class="program-features" style="border-top-color: rgba(35, 111, 76, 0.1);">
                        <li><strong>Destinatarios:</strong> Chacras, estancias, productores de fruta fina, viñedos y artesanos textiles.</li>
                        <li><strong>Objetos Conectores:</strong> Vinculación directa con packaging, dulces, lanas y licores de origen rural.</li>
                        <li><strong>Cupos Limitados:</strong> De 5 a 8 establecimientos seleccionados por cohorte.</li>
                        <li><strong>Duración:</strong> 8 semanas de relevamiento, costeo y mapa interactivo.</li>
                    </ul>
                    <a href="inscribirse.php?linea=Raiz" class="btn btn-secondary" style="margin-top: 24px; border-color: rgba(35, 111, 76, 0.4); color: #cbd5e1;">Postularse a Raíz</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Convocatoria y Cronograma -->
    <section id="convocatoria" class="section" style="border-top: 1px solid var(--glass-border);">
        <div class="container">
            <div class="grid-2">
                <div>
                    <span class="text-mono" style="color: var(--color-wild-berry); font-size: 0.85rem; font-weight: 600;">Fechas Importantes</span>
                    <h2 style="font-size: 2.5rem; margin-top: 10px;">Línea de Tiempo Convocatoria</h2>
                    <p>
                        Para asegurar la máxima calidad del acompañamiento personalizado y el desarrollo real de productos digitales y físicos, establecemos un riguroso sistema de cupos. 
                    </p>
                    <div class="scarcity-box" style="border-color: #236f4c;">
                        <h4 style="margin-bottom: 8px;">Requisito de Compromiso Temporal</h4>
                        <p style="font-size: 0.95rem; color: #cbd5e1; margin-bottom: 0;">
                            Las propuestas seleccionadas firmarán una carta de compromiso asumiendo una **dedicación mínima de 12 horas semanales** al trabajo de co-creación junto al Cuadro Técnico.
                        </p>
                    </div>
                    <p style="font-size: 0.9rem; color: var(--color-text-secondary);">
                        *Si no resultas seleccionado en esta cohorte, la postulación quedará registrada para futuros clusters y programas complementarios sectoriales que habilitaremos a partir del relevamiento general.
                    </p>
                </div>
                <div>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">23 de Julio al 9 de Agosto</div>
                            <h4 style="margin-bottom: 8px;">Período de Postulación Abierto</h4>
                            <p style="font-size: 0.9rem; color: #a1a5ab;">
                                Recepción de formularios interactivos. Los aplicantes deben explayarse a conciencia sobre sus recursos, motivación e integración comercial.
                            </p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">30 de Julio al 9 de Agosto</div>
                            <h4 style="margin-bottom: 8px;">Evaluaciones y Entrevistas Técnicas</h4>
                            <p style="font-size: 0.9rem; color: #a1a5ab;">
                                El Cuadro Técnico (CAMOCH, Prestadores y FEHGRA) pondera las propuestas y realiza visitas de diagnóstico en el territorio.
                            </p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">10 de Agosto</div>
                            <h4 style="margin-bottom: 8px;">Inicio de Trabajos Técnicos</h4>
                            <p style="font-size: 0.9rem; color: #a1a5ab;">
                                Comunicación oficial de seleccionados e inicio de los sprints semanales (Diagnóstico, Desarrollo de Precios, Digitalización e Integración Productiva).
                            </p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">2 de Octubre</div>
                            <h4 style="margin-bottom: 8px;">Lanzamiento y Evento de Resultados</h4>
                            <p style="font-size: 0.9rem; color: #a1a5ab; margin-bottom: 0;">
                                Evento formal de lanzamiento comercial de las experiencias y sus objetos de identidad ante la prensa regional y operadores receptivos nacionales.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comunidad y Desmitificación Turística -->
    <section id="comunidad" class="section civic-card" style="border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border);">
        <div class="container">
            <div style="max-width: 800px; margin: 0 auto; text-align: center;">
                <span class="text-mono" style="color: #4ed392; font-size: 0.85rem; font-weight: 600;">Destino Esquel · Turismo para todos</span>
                <h2 style="font-size: 2.5rem; margin-top: 10px; margin-bottom: 24px;">Una Matriz Turística Real e Integradora</h2>
                <p style="font-size: 1.1rem; line-height: 1.7; color: #e2e8f0; margin-bottom: 24px;">
                    Existe la noción histórica de que el turismo beneficia exclusivamente a grandes capitales de inversión hotelera o gastronómica externa, dejando al ciudadano local fuera del juego. El Laboratorio de Destino Esquel nace para romper ese paradigma.
                </p>
                <p style="color: #cbd5e1; margin-bottom: 32px;">
                    Creemos firmemente que Esquel posee un potencial turístico inmenso y no tradicional que reside en sus vecinos: en el productor de fruta fina de las afueras, en el artesano textil que preserva saberes, en el guía local de naturaleza, y en las pequeñas cocinas de tradición patagónica. Al priorizar el acompañamiento a estos micro-emprendedores pioneros, allanamos el camino para diversificar los recursos económicos de la ciudad de forma permanente, equitativa y continua.
                </p>
                <a href="inscribirse.php" class="btn btn-primary" style="background-color: var(--color-lichen-green); box-shadow: 0 4px 20px var(--color-lichen-green-glow);">Sumate al Cambio Local</a>
            </div>
        </div>
    </section>

    <!-- Co-creadores y Gobernabilidad -->
    <section class="section" style="border-bottom: 1px solid var(--glass-border);">
        <div class="container" style="text-align: center;">
            <span class="text-mono" style="color: var(--color-text-secondary); font-size: 0.8rem; letter-spacing: 0.1em; display: block; margin-bottom: 32px;">SISTEMA DE GOBERNANZA MIXTA · EVALUACIÓN TRANSPARENTE</span>
            <div style="display: flex; justify-content: center; align-items: center; gap: 64px; flex-wrap: wrap; opacity: 0.75;">
                <div style="font-family: var(--font-display); font-weight: 600; color: #cbd5e1; font-size: 1.1rem;">CAMOCH</div>
                <div style="font-family: var(--font-display); font-weight: 600; color: #cbd5e1; font-size: 1.1rem;">Cámara Prestadores Esquel</div>
                <div style="font-family: var(--font-display); font-weight: 600; color: #cbd5e1; font-size: 1.1rem;">FEHGRA Filial Esquel</div>
            </div>
            <p style="font-size: 0.85rem; color: var(--color-text-secondary); max-width: 600px; margin: 24px auto 0 auto; line-height: 1.5;">
                Estas organizaciones conforman el Cuadro Técnico que pondera con criterios públicos y objetivos (innovación, perfil, derrame y viabilidad) cada propuesta para garantizar equidad, transparencia y un buen mix de diversidad de estadios comerciales.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p style="margin-bottom: 8px;">&copy; 2026 Laboratorio de Destino Esquel. Subsecretaría de Turismo y Subsecretaría de Producción.</p>
            <p style="font-size: 0.75rem; color: var(--color-text-secondary);">Municipalidad de Esquel, Chubut, Patagonia Argentina.</p>
            <a href="admin/login.php" class="footer-lock" title="Acceso de Gestión Interna">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </a>
        </div>
    </footer>
</body>
</html>
