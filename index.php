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
    <meta name="description" content="Programa de la Subsecretaría de Turismo y la Subsecretaría de Producción de Esquel para la estructuración comercial de experiencias turísticas urbanas y rurales.">
    <link rel="stylesheet" href="assets/css/style.css?v=1.1">
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
                    <li><a href="#comunidad" class="nav-link">Información Comunitaria</a></li>
                    <li><a href="media-kit.php" class="nav-link">Sala de Prensa</a></li>
                    <li><a href="inscribirse.php" class="btn btn-primary btn-sm">Inscribirse</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="section" style="padding-top: 200px; min-height: 100vh; display: flex; align-items: center;">
        <div class="container">
            <div class="hero-grid" style="display: grid; grid-template-columns: 1.25fr 0.75fr; gap: 48px; align-items: center; width: 100%;">
                <div class="hero-content">
                    <span class="hero-subtitle text-mono">Programa de Fomento de la Oferta Turística</span>
                    <h1 class="hero-title" style="font-size: 3.5rem; line-height: 1.1; margin-bottom: 1.5rem;">Laboratorio de<br>Destino Esquel.</h1>
                    <p class="hero-description" style="font-size: 1.15rem; line-height: 1.6; margin-bottom: 2rem;">
                        Un programa práctico diseñado para transformar tus ideas, emprendimiento o saberes tradicionales en una propuesta turística lista para vender. Te brindamos acompañamiento técnico personalizado en tu propio lugar de trabajo para definir tarifas comerciales, abrir canales de reserva digital y conectar tu actividad con los productores locales.
                    </p>
                    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                        <a href="inscribirse.php" class="btn btn-primary">Formulario de Postulación</a>
                        <a href="#metodo" class="btn btn-secondary">Especificaciones Técnicas</a>
                    </div>
                </div>
                <div class="hero-logo-container" style="display: flex; justify-content: center; align-items: center;">
                    <img src="assets/images/logo-lab-white.png" alt="Esquel LAB Logo" style="width: 100%; max-width: 320px; height: auto; opacity: 0.9; filter: drop-shadow(0 0 45px rgba(255,255,255,0.06));">
                </div>
            </div>
        </div>
    </section>

    <!-- Metodología Section -->
    <section id="metodo" class="section" style="border-top: 1px solid var(--glass-border);">
        <div class="container">
            <div class="grid-2" style="align-items: center;">
                <div>
                    <span class="text-mono" style="color: var(--color-text-secondary); font-size: 0.85rem; font-weight: 600;">Metodología de Trabajo</span>
                    <h2 style="font-size: 2.2rem; margin-top: 10px;">Estructuración y Economía de los Recuerdos</h2>
                    <p>
                        El programa asume que las experiencias turísticas crean vínculos emocionales y que los objetos locales con identidad territorial (artesanías, lana, alimentos elaborados) actúan como el canal físico que mantiene vivo el recuerdo del destino en el tiempo.
                    </p>
                    <p>
                        La metodología consiste en un acompañamiento técnico individual en territorio. El equipo de facilitadores asiste a cada postulante seleccionado en el diseño de su guión interpretativo, el establecimiento de precios neta para la venta a operadores receptivos, y la habilitación de un canal digital de reservas.
                    </p>
                    <div class="scarcity-box" style="border-color: var(--color-wild-berry);">
                        <h4 style="margin-bottom: 8px;">Acompañamiento Técnico Individual</h4>
                        <p style="margin-bottom: 0; font-size: 0.95rem; color: #a1a5ab;">
                            Se priorizan proyectos en funcionamiento o negocios no turísticos con saberes configurables como experiencia. El proceso evalúa la factibilidad de incorporar al menos un producto físico asociado con el Sello "Hecho en Esquel" para extender el impacto comercial del destino.
                        </p>
                    </div>
                </div>
                <div class="card">
                    <h3 style="margin-bottom: 24px; font-size: 1.5rem;">Entregables del Proceso</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 16px; display: flex; gap: 12px;">
                            <span style="color: var(--color-wild-berry); font-weight: bold;">✓</span>
                            <div>
                                <strong>Ficha técnica comercial:</strong> Definición de tarifas netas para operadores comerciales receptivos, duración, capacidad operativa y requisitos.
                            </div>
                        </li>
                        <li style="margin-bottom: 16px; display: flex; gap: 12px;">
                            <span style="color: var(--color-wild-berry); font-weight: bold;">✓</span>
                            <div>
                                <strong>Canal digital de reservas:</strong> Habilitación y configuración de un canal digital básico y funcional para contacto y recepción de reservas.
                            </div>
                        </li>
                        <li style="margin-bottom: 16px; display: flex; gap: 12px;">
                            <span style="color: var(--color-wild-berry); font-weight: bold;">✓</span>
                            <div>
                                <strong>Registro promocional básico:</strong> Relevamiento fotográfico inicial de la experiencia turística para su difusión.
                            </div>
                        </li>
                        <li style="margin-bottom: 0; display: flex; gap: 12px;">
                            <span style="color: var(--color-wild-berry); font-weight: bold;">✓</span>
                            <div>
                                <strong>Informe de viabilidad de producto físico:</strong> Análisis técnico para asociar o comercializar un objeto físico representativo de la identidad de Esquel.
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
                <span class="text-mono" style="color: var(--color-text-secondary); font-size: 0.85rem; font-weight: 600;">Estructura comparada</span>
                <h2 style="font-size: 2.2rem; margin-top: 10px;">Líneas de Aceleración y Desarrollo</h2>
                <p>
                    El programa coordina dos líneas complementarias bajo el mismo horizonte temporal de ejecución y metodología aplicada en territorio.
                </p>
            </div>

            <div class="grid-2">
                <!-- Esquel Acelera -->
                <div class="card program-card">
                    <div class="program-card-header">
                        <img src="assets/images/logo-acelera.png" alt="Esquel Acelera" style="height: 64px; margin-bottom: 16px;">
                        <span class="program-badge badge-acelera text-mono">Esquel Acelera</span>
                        <p style="font-size: 0.95rem; margin-bottom: 0;">
                            Línea orientada a la consolidación de emprendimientos, organizaciones de la sociedad civil y prestadores de servicios turísticos dentro del ejido urbano de Esquel.
                        </p>
                    </div>
                    <ul class="program-features">
                        <li><strong>Destinatarios:</strong> Casas de té, talleres artesanales, circuitos históricos y propuestas gastronómicas locales.</li>
                        <li><strong>Resultado esperado:</strong> 8 a 10 experiencias estructuradas y preparadas para su comercialización por edición.</li>
                        <li><strong>Plazo de ejecución:</strong> 8 semanas de trabajo técnico en territorio.</li>
                        <li><strong>Requisito productivo:</strong> Evaluación de integración con el Sello Municipal "Hecho en Esquel".</li>
                    </ul>
                    <a href="inscribirse.php?linea=Acelera" class="btn btn-primary" style="margin-top: 24px;">Postularse a Acelera</a>
                </div>

                <!-- Esquel Raíz -->
                <div class="card program-card" style="border-color: rgba(35, 111, 76, 0.25);">
                    <div class="program-card-header">
                        <img src="assets/images/logo-raiz.png" alt="Esquel Raíz" style="height: 64px; margin-bottom: 16px;">
                        <span class="program-badge badge-raiz text-mono">Raíz</span>
                        <p style="font-size: 0.95rem; margin-bottom: 0;">
                            Línea orientada a la estructuración de la oferta turística en el ámbito rural, asociando saberes tradicionales a la comercialización del producto de campo.
                        </p>
                    </div>
                    <ul class="program-features" style="border-top-color: rgba(35, 111, 76, 0.1);">
                        <li><strong>Destinatarios:</strong> Establecimientos rurales, chacras, viñedos, productores de lana y productores de fruta fina.</li>
                        <li><strong>Resultado esperado:</strong> 5 a 8 experiencias rurales estructuradas y geolocalizadas en el Mapa de Turismo Rural.</li>
                        <li><strong>Plazo de ejecución:</strong> 8 semanas de relevamiento y desarrollo técnico.</li>
                        <li><strong>Requisito productivo:</strong> Mejora de packaging, relato y exhibición de productos agrícolas y textiles.</li>
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
                    <span class="text-mono" style="color: var(--color-text-secondary); font-size: 0.85rem; font-weight: 600;">Detalles de la Convocatoria</span>
                    <h2 style="font-size: 2.2rem; margin-top: 10px;">Cronograma de Trabajo y Compromisos</h2>
                    <p>
                        Para garantizar un acompañamiento técnico personalizado de alta dedicación individual, el programa cuenta con cupos limitados por edición (8 a 10 proyectos urbanos y 5 a 8 proyectos rurales).
                    </p>
                    <div class="scarcity-box" style="border-color: #236f4c;">
                        <h4 style="margin-bottom: 8px;">Dedicación Requerida</h4>
                        <p style="font-size: 0.95rem; color: #cbd5e1; margin-bottom: 0;">
                            Las propuestas seleccionadas asumen el compromiso de prever y destinar un mínimo de **12 horas semanales** al proceso de co-creación y desarrollo técnico.
                        </p>
                    </div>
                    <p style="font-size: 0.9rem; color: var(--color-text-secondary);">
                        Las postulaciones que no resulten seleccionadas para esta primera cohorte formarán parte de una base de datos para la definición de futuros programas y clusters complementarios.
                    </p>
                </div>
                <div>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">Del 23 de Julio al 9 de Agosto</div>
                            <h4 style="margin-bottom: 8px;">Recepción de Formularios de Postulación</h4>
                            <p style="font-size: 0.9rem; color: #a1a5ab;">
                                Apertura de la convocatoria pública. Los postulantes deben completar el formulario detallando sus recursos y su motivación para participar.
                            </p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">Del 23 de Julio al 9 de Agosto</div>
                            <h4 style="margin-bottom: 8px;">Evaluación y Entrevistas Técnicas</h4>
                            <p style="font-size: 0.9rem; color: #a1a5ab;">
                                Ponderación de los proyectos a cargo del Cuadro Técnico integrado por representantes del sector privado y los facilitadores del programa.
                            </p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">10 de Agosto</div>
                            <h4 style="margin-bottom: 8px;">Notificación e Inicio de Trabajos</h4>
                            <p style="font-size: 0.9rem; color: #a1a5ab;">
                                Comunicación oficial de las propuestas seleccionadas e inicio inmediato del proceso de acompañamiento técnico en territorio.
                            </p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">2 de Octubre</div>
                            <h4 style="margin-bottom: 8px;">Cierre y Presentación de Experiencias</h4>
                            <p style="font-size: 0.9rem; color: #a1a5ab; margin-bottom: 0;">
                                Evento formal de lanzamiento de las experiencias estructuradas y sus productos físicos asociados ante operadores receptivos y prensa.
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
                <span class="text-mono" style="color: #4ed392; font-size: 0.85rem; font-weight: 600;">Desarrollo Productivo Local</span>
                <h2 style="font-size: 2.2rem; margin-top: 10px; margin-bottom: 24px;">Información sobre el Impacto del Programa en la Comunidad</h2>
                <p style="font-size: 1.1rem; line-height: 1.7; color: #e2e8f0; margin-bottom: 24px;">
                    El programa busca diversificar la oferta del destino apoyando directamente a pequeños productores y emprendimientos locales que constituyen la base de la identidad de Esquel.
                </p>
                <p style="color: #cbd5e1; margin-bottom: 32px;">
                    A diferencia del régimen general de grandes inversiones turísticas de capital externo, este programa se enfoca en dotar de herramientas comerciales y de diseño a los productores locales (chacras, artesanos y elaboradores urbanos). La estructuración de estas experiencias busca generar un circuito complementario continuo en el territorio, abriendo canales de venta directa que permiten que el derrame del gasto del visitante beneficie de manera equitativa a los prestadores y productores locales.
                </p>
                <a href="inscribirse.php" class="btn btn-primary" style="background-color: var(--color-lichen-green); box-shadow: 0 4px 20px var(--color-lichen-green-glow);">Iniciar Formulario de Postulación</a>
            </div>
        </div>
    </section>

    <!-- Co-creadores y Gobernabilidad -->
    <section class="section" style="border-bottom: 1px solid var(--glass-border);">
        <div class="container" style="text-align: center;">
            <span class="text-mono" style="color: var(--color-text-secondary); font-size: 0.8rem; letter-spacing: 0.1em; display: block; margin-bottom: 32px;">SISTEMA DE GOBERNANZA MIXTA · CUADRO TÉCNICO DE EVALUACIÓN</span>
            <div style="display: flex; justify-content: center; align-items: center; gap: 64px; flex-wrap: wrap; opacity: 0.75;">
                <div style="font-family: var(--font-display); font-weight: 600; color: #cbd5e1; font-size: 1.1rem;">CAMOCH</div>
                <div style="font-family: var(--font-display); font-weight: 600; color: #cbd5e1; font-size: 1.1rem;">Cámara de Prestadores Turísticos de Esquel</div>
                <div style="font-family: var(--font-display); font-weight: 600; color: #cbd5e1; font-size: 1.1rem;">FEHGRA Filial Esquel</div>
            </div>
            <p style="font-size: 0.85rem; color: var(--color-text-secondary); max-width: 600px; margin: 24px auto 0 auto; line-height: 1.5;">
                La evaluación y selección técnica de los postulantes se realiza mediante una matriz multidimensional co-diseñada junto a las cámaras sectoriales locales para asegurar la transparencia e imparcialidad del proceso.
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
