<?php
// Creado para el Laboratorio de Destino Esquel
// media-kit.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recursos Editoriales & Media Kit · Esquel LAB</title>
    <meta name="description" content="Sala de recursos interactivos para medios de prensa. Notas editoriales, logos y material gráfico oficial de Esquel LAB.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg-vignette"></div>

    <!-- Header Navigation -->
    <header class="header">
        <div class="container header-container">
            <a href="index.php" class="logo-link">
                <img src="assets/images/logo-lab-white.png" alt="Esquel LAB" class="logo-img">
            </a>
            <nav class="nav">
                <ul class="nav-list">
                    <li><a href="index.php" class="nav-link">Inicio</a></li>
                    <li><a href="index.php#metodo" class="nav-link">Metodología</a></li>
                    <li><a href="index.php#programas" class="nav-link">Programas</a></li>
                    <li><a href="index.php#convocatoria" class="nav-link">Convocatoria</a></li>
                    <li><a href="inscribirse.php" class="btn btn-primary btn-sm">Inscribirse</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="section" style="padding-top: 140px; min-height: 90vh;">
        <div class="container">
            
            <div style="text-align: center; max-width: 800px; margin: 0 auto 60px auto;">
                <span class="text-mono" style="color: var(--color-wild-berry); font-size: 0.85rem; font-weight: 600;">Sala de Prensa Interactiva</span>
                <h2 style="font-size: 2.8rem; margin-top: 10px; margin-bottom: 16px;">Recursos Editoriales & Media Kit</h2>
                <p style="font-size: 1.05rem;">
                    Facilitamos el trabajo de los comunicadores. Ponemos a disposición recursos listos para copiar, adaptar y publicar, junto con las descargas de materiales visuales oficiales.
                </p>
            </div>

            <div class="grid-3" style="margin-bottom: 64px;">
                <!-- Card Descarga Logos -->
                <div class="card" style="text-align: center; display: flex; flex-direction: column; justify-content: space-between; align-items: center;">
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 2.5rem; margin-bottom: 12px;">📁</div>
                        <h4 style="margin-bottom: 8px;">Logotipos Oficiales</h4>
                        <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin-bottom: 0;">
                            Isologotipos de Esquel LAB, Acelera y Raíz en formato transparente de alta definición.
                        </p>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px; width: 100%;">
                        <a href="assets/images/logo-lab-white.png" download class="btn btn-secondary btn-sm" style="width: 100%;">Descargar Logo LAB (Blanco)</a>
                        <a href="assets/images/logo-acelera.png" download class="btn btn-secondary btn-sm" style="width: 100%;">Descargar Logo Acelera</a>
                        <a href="assets/images/logo-raiz.png" download class="btn btn-secondary btn-sm" style="width: 100%;">Descargar Logo Raíz</a>
                    </div>
                </div>

                <!-- Card Documentación Técnica -->
                <div class="card" style="text-align: center; display: flex; flex-direction: column; justify-content: space-between; align-items: center;">
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 2.5rem; margin-bottom: 12px;">📄</div>
                        <h4 style="margin-bottom: 8px;">Dossier del Programa</h4>
                        <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin-bottom: 0;">
                            Documentación ejecutiva detallada en formato PDF sobre el Laboratorio de Destino Esquel.
                        </p>
                    </div>
                    <a href="PRODUCT.md" target="_blank" class="btn btn-secondary btn-sm" style="width: 100%;">Ver Ficha del Proyecto (Markdown)</a>
                </div>

                <!-- Contacto de Prensa -->
                <div class="card" style="text-align: center; display: flex; flex-direction: column; justify-content: space-between; align-items: center;">
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 2.5rem; margin-bottom: 12px;">✉️</div>
                        <h4 style="margin-bottom: 8px;">Contacto de Prensa</h4>
                        <p style="font-size: 0.85rem; color: var(--color-text-secondary); margin-bottom: 0;">
                            ¿Necesitás coordinar una entrevista con el equipo técnico municipal o las Cámaras asociadas?
                        </p>
                    </div>
                    <a href="mailto:comunicacionesquel25@gmail.com" class="btn btn-primary btn-sm" style="width: 100%;">Escribir a Prensa</a>
                </div>
            </div>

            <!-- Notas de prensa interactivas -->
            <h3 style="font-size: 1.8rem; margin-bottom: 32px; text-align: center;">Notas Editoriales Disponibles</h3>

            <div class="grid-2">
                <!-- Nota 1: Gobernanza y selección transparente -->
                <div class="card release-card">
                    <span class="text-mono" style="color: var(--color-wild-berry); font-size: 0.75rem; font-weight: 600;">Eje: Transparencia y Gobernanza</span>
                    <h4 style="font-size: 1.25rem; margin: 12px 0 8px 0;">El sector privado de Esquel asume un rol activo en la selección de emprendimientos turísticos</h4>
                    
                    <div id="releaseText1" class="release-content">
                        ESQUEL, CHUBUT · Con el lanzamiento de la primera cohorte del Laboratorio de Destino Esquel, el municipio y el sector privado local presentaron un modelo de gobernanza mixta inédito en la región. Las instituciones representativas del comercio, la hotelería y la prestación turística —CAMOCH, la Cámara de Prestadores Turísticos de Esquel y FEHGRA Filial Esquel— no solo avalan el programa, sino que conforman el Cuadro Técnico a cargo del sistema de ponderación de postulantes.

                        Este sistema busca garantizar equidad, transparencia y un análisis técnico preciso de cada propuesta. La evaluación se realiza bajo cinco dimensiones ponderadas: diferenciación de la propuesta, impacto en la matriz turística local, motivación de los emprendedores, viabilidad del producto físico y capacidad operativa mínima. De esta manera, se asegura un buen mix de diversidad de proyectos en distintos estadios de desarrollo, evitando beneficiar únicamente a los actores ya consolidados y brindando oportunidades reales a nuevos emprendedores locales.
                    </div>

                    <button class="btn btn-secondary btn-sm btn-copy" data-target="releaseText1" style="width: 100%;">
                        Copiar nota de prensa
                    </button>
                </div>

                <!-- Nota 2: Economía de los Recuerdos y Producción -->
                <div class="card release-card" style="border-left-color: #236f4c;">
                    <span class="text-mono" style="color: #4ed392; font-size: 0.75rem; font-weight: 600;">Eje: Economía de los Recuerdos</span>
                    <h4 style="font-size: 1.25rem; margin: 12px 0 8px 0;">Esquel impulsa la "Economía de los Recuerdos" integrando el turismo con los productores locales</h4>
                    
                    <div id="releaseText2" class="release-content">
                        ESQUEL, CHUBUT · La Subsecretaría de Turismo en conjunto con la Subsecretaría de Producción de Esquel anunciaron la implementación metodológica de la "Economía de los Recuerdos" dentro del nuevo Laboratorio de Destino. Bajo este enfoque estratégico, los recuerdos generados por el turista son considerados el bien más valioso que produce un destino.

                        A través de las líneas de trabajo "Esquel Acelera" y "Raíz", el programa vincula directamente el diseño de experiencias turísticas con la adquisición de productos físicos tangibles con identidad local. Se buscará que cada experiencia rural (chacras, lanas, fruta fina) y urbana (gastronomía, artesanías) asocie y mejore la presentación de un producto conector representativo. Esto no solo incrementará el ticket de gasto del visitante, sino que impulsará de forma directa las ventas de los pequeños artesanos y productores locales nucleados en el Sello Municipal "Hecho en Esquel".
                    </div>

                    <button class="btn btn-secondary btn-sm btn-copy" data-target="releaseText2" style="width: 100%;">
                        Copiar nota de prensa
                    </button>
                </div>
            </div>

            <!-- Continuidad Nota Informativa -->
            <div class="card" style="margin-top: 48px; border-color: rgba(255, 255, 255, 0.05); background-color: rgba(0,0,0,0.15);">
                <span class="text-mono" style="color: var(--color-text-secondary); font-size: 0.75rem;">Nota Adicional sobre la Convocatoria</span>
                <h4 style="margin: 12px 0 8px 0;">Un programa municipal continuo y con foco territorial</h4>
                <div id="releaseText3" class="release-content" style="max-height: 150px;">
                    El Laboratorio de Destino Esquel prevé abrir de manera secuencial y continua futuras cohortes a lo largo del año. La selección de un cupo restringido en esta primera etapa (8 a 10 urbanos y 5 a 8 rurales) responde a la necesidad de concentrar recursos y asesoramiento personalizado 1:1, asegurando que las experiencias seleccionadas abran camino comercial estable y sirvan de modelo para futuros programas y clusters complementarios.
                </div>
                <button class="btn btn-secondary btn-sm btn-copy" data-target="releaseText3" style="max-width: 250px;">
                    Copiar nota corta
                </button>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Laboratorio de Destino Esquel. Subsecretaría de Turismo y Subsecretaría de Producción.</p>
            <p style="font-size: 0.75rem; color: var(--color-text-secondary); margin-top: 4px;">Municipalidad de Esquel, Chubut, Patagonia Argentina.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
