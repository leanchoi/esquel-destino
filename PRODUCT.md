# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users
- **Postulantes**: Emprendedores urbanos, productores rurales, organizaciones del tercer sector y empresas de servicios turísticos de Esquel que desean estructurar comercialmente su oferta turística.
- **Cuadro Técnico de Acompañamiento (CAMOCH, Cámara de Prestadores, AEHGCLA / FEHGRA)**: Evaluadores del sector privado que revisan las postulaciones de manera transparente y realizan el acompañamiento.
- **Equipo Técnico Municipal (Turismo y Producción)**: Administradores municipales encargados de gestionar las convocatorias y dar seguimiento técnico a las propuestas.
- **Periodistas y Medios**: Profesionales de comunicación que acceden a los recursos de prensa para difundir las novedades del programa.
- **Ciudadanía en General**: Vecinos de Esquel que buscan visibilizar y comprobar el apoyo municipal al entramado productivo y emprendedor local.

## Product Purpose
El portal tiene un doble propósito:
1. **Público (Landing Page & Formulario)**: Funcionar como una landing page de altísimo impacto visual y narrativo que incentive a los emprendedores locales a postularse en la primera cohorte del Laboratorio de Destino Esquel (Acelera o Raíz), guiándolos a través de un formulario interactivo por etapas que estimule respuestas a conciencia y demuestre transparencia.
2. **Privado (CRM / Panel de Control)**: Actuar como un CRM interno para el Cuadro Técnico y el municipio, permitiendo evaluar las postulaciones mediante una matriz de puntuación multidimensional, añadir notas operativas, gestionar estados (pendiente, preseleccionado, aprobado, rechazado) y exportar los datos a formato Excel/CSV.

## Positioning
Un programa de desarrollo de experiencias que se diferencia por el "hacer juntos" (co-creación) y "dejar cosas andando" (lanzamiento y canales digitales activos), en lugar de capacitaciones teóricas abstractas. Su sustento metodológico es la "Economía de los Recuerdos" (experiencia + producto físico identitario) y su legitimidad proviene de un modelo de gobernanza mixto y transparente.

## Operating Context
- **Servidor Final**: Alojamientos web compartidos (slots personalizados de Hostinger PHP/HTML) del usuario.
- **Base de Datos**: SQLite 3 integrada en un archivo local resguardado mediante reglas `.htaccess` (evita dependencias de configuración manual de MySQL en Hostinger y garantiza portabilidad inmediata).
- **Acceso Administrativo**: Acceso mediante login con credenciales iniciales `admin` / `admin123`, con capacidad posterior de modificación de contraseña y creación de usuarios adicionales con roles específicos (Administrador/Editor/Lector).

## Capabilities and Constraints
- **Multi-step Form**: Formulario interactivo condicional por etapas. El usuario selecciona inicialmente su línea de postulación (ESQUEL ACELERA o RAÍZ) y el formulario despliega dinámicamente las preguntas específicas para cada perfil.
- **CRM Dinámico**: Panel de administración accesible mediante un botón sutil (candado) con vistas en formato lista (tabla interactiva con ordenamiento y filtros) y formato tarjetas (Kanban/tablero visual de seguimiento).
- **Evaluación Integrada**: Modal "Tomar Acción" en cada postulación para calificar cuantitativamente las 5 dimensiones ponderadas (Diferenciación, Impacto, Perfil, Viabilidad del Producto Físico, Viabilidad Operativa) y escribir notas de revisión.
- **Exportación XLS**: Script PHP para la generación y descarga inmediata de un archivo compatible con Excel (CSV UTF-8 con separador de tabuladores o comas).
- **Media Kit Práctico**: Descarga estructurada de logos, fichas técnicas genéricas del programa y tres textos prediseñados ("Notas Editoriales" interactivas) para facilitar la labor de prensa sin tecnicismos complejos.
- **Sección Ciudadana Integradora**: Narrativa que demuestra que el turismo en Esquel beneficia directamente al ciudadano común y al pequeño productor (chacras, artesanos), desmitificando que los beneficios se limitan a grandes inversiones externas.

## Brand Commitments
- **Identidad Gráfica**: Utilización de los logotipos oficiales de Esquel LAB, Esquel Acelera y Esquel Raíz.
- **Líneas de Trabajo**:
  - *ESQUEL ACELERA*: Enfoque urbano, consolidación de comercios, oficios y experiencias locales.
  - *RAÍZ*: Enfoque rural, origen, saberes de campo, chacras, fruta fina, destilerías, lana y viñedos.
- **Cronograma Vinculante**: Convocatoria del 23 de julio al 9 de agosto. Comunicación de seleccionados e inicio de trabajos el 10 de agosto. Cierre y lanzamiento de resultados el 2 de octubre.
- **Compromiso Temporal**: Dedicación mínima requerida de 12 horas semanales al proceso por parte de los seleccionados.

## Evidence on Hand
- Perfil de Proyecto oficial: "Laboratorio de Destino Esquel - Programa de Fomento de la Oferta Turística" (Julio 2026).
- Criterios de gobernanza y ponderación definidos por el Cuadro Técnico (CAMOCH, Cámara de Prestadores, AEHGCLA).
- Imágenes de logos y referencias visuales de la geografía, paisaje y producción local.

## Product Principles
1. **Hacer y Dejar Andando**: Toda interacción del usuario debe conducir a una acción clara, evitando la jerga académica y enfocándose en lo práctico y tangible.
2. **La Economía de los Recuerdos**: Los recuerdos son el producto final del destino. El portal debe celebrar la tangibilidad de las experiencias locales y la producción física de Esquel (Sello Hecho en Esquel).
3. **Legitimidad por Transparencia**: La gobernanza mixta y los criterios objetivos de selección son públicos y auditables en el Media Kit y la sección de evaluación.
4. **Sentido de Pertenencia Local**: El vecino de Esquel debe sentirse reflejado y valorado en las historias de los pequeños productores de campo y los emprendedores urbanos.

## Accessibility & Inclusion
- Diseño responsive enfocado en dispositivos móviles (muchos emprendedores y productores rurales acceden al sitio desde celulares).
- Tipografías legibles con óptimo contraste de colores (WCAG AA mínimo).
- Formularios interactivos con indicadores visuales claros de progreso y guardado local del borrador para evitar pérdida de datos ante caídas de conexión rural.
