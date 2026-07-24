# Esquel LAB — auditoría del sitio actual y plan de mejora

Revisión del repo `leanchoi/esquel-destino` (commit `52d5a0a`). Foco: que un emprendedor turístico —o alguien que tiene un negocio y no logra arrancar / necesita readecuarse— entienda el programa en 30 segundos y quiera postularse.

---

## 1. Diagnóstico en una frase

El sitio pasó de "vendedor" a "expediente municipal". Está bien construido y la información es correcta, pero **está escrito desde adentro del programa hacia afuera**: describe la metodología, no el resultado para quien lee. Un emprendedor que entra no encuentra respuesta a las cinco preguntas que realmente se hace.

### Lo que está bien y hay que conservar

- La matriz de evaluación 1-5 sobre los cinco criterios en el CRM. Es mejor que un estado suelto: permite ordenar por puntaje y justificar la selección ante el Cuadro Técnico.
- Vista tabla + kanban con drawer de detalle.
- Las notas de prensa con botón "copiar" en el media kit — eso sí le sirve a un periodista.
- Los logos reales integrados (color de marca real: `#ab2759`).
- Uso de SQLite con `.htaccess` bloqueando `/data` — decisión sensata para Hostinger, cero configuración de base.

---

## 2. Bugs concretos (arreglar sí o sí antes de publicar)

| # | Dónde | Qué pasa |
|---|---|---|
| 1 | `index.php:193`, `inscribirse.php:130`, `inscribirse.php:273`, `admin/dashboard.php:434,488` | **Markdown `**` dentro del HTML.** Se ve literal en pantalla: "un mínimo de \*\*12 horas semanales\*\*". Está visible en el home publicado. Reemplazar por `<strong>`. |
| 2 | `inscribirse.php:222,227,232,240,245,250` | Los campos del Paso 2 tienen asterisco de "obligatorio" en el label pero **no tienen el atributo `required`**. Se puede enviar la postulación con toda la propuesta vacía. Es el núcleo evaluable del formulario y hoy es opcional en la práctica. |
| 3 | `inscribirse.php:194` | El radio de programa viene **pre-marcado en "Acelera"** cuando se entra sin parámetro. Todo el que no preste atención queda registrado como urbano. Ensucia los datos de la convocatoria. |
| 4 | `inscribirse.php:81`, `admin/login.php:40`, `db_config.php:76` | Los errores de base de datos se imprimen al visitante (`$e->getMessage()`). Filtra rutas y estructura interna. Loguear en servidor, mostrar mensaje genérico. |
| 5 | `inscribirse.php` (todo) | **Sin CSRF y sin honeypot.** Un formulario público de un municipio se llena de spam en días. |
| 6 | `inscribirse.php` | **No cierra el 9 de agosto.** El sitio dice "únicamente hasta el 9 de agosto" y sigue aceptando envíos el 10, el 20 y en noviembre. Si la escasez se anuncia y no se cumple, deja de funcionar como escasez. |
| 7 | `admin/api.php:36-74` | **El CSV no exporta las respuestas del formulario.** Baja contacto + puntajes, pero no `application_details`. O sea: no se pueden leer las postulaciones fuera del panel, ni mandárselas por mail al Cuadro Técnico. Es justo lo que se necesita para evaluar en grupo. |
| 8 | `media-kit.php:72` | "Dossier del Programa" apunta a `PRODUCT.md`, un archivo interno de desarrollo. Un periodista se baja notas de trabajo. |
| 9 | `admin/dashboard.php:557` | El botón dice "Descargar Excel (XLS)" pero entrega un CSV. Además usa `;` como separador: abre bien en Excel español, se rompe en Google Sheets. Decir "Descargar CSV" y ofrecer el separador correcto. |
| 10 | `index.php:203,210` | La línea de tiempo repite **"Del 23 de Julio al 9 de Agosto"** en dos hitos seguidos. Parece error de tipeo aunque sea intencional. Unificar en un solo bloque. |

---

## 3. El problema de fondo del contenido

Un emprendedor entra al sitio con cinco preguntas en la cabeza. **Hoy el sitio no responde ninguna:**

| La pregunta que se hace | Qué encuentra hoy |
|---|---|
| **¿Esto me cuesta plata?** | Nada. La palabra "gratuito" no aparece **ni una vez** en todo el sitio. Es la primera duda de cualquiera y la que más gente hace rebotar. |
| **¿Puedo participar si no estoy formalizado?** | Nada sobre habilitación, monotributo, RNTC o inscripción. El que no está en regla asume que no puede y se va. |
| **¿Esto es para mí? Yo no soy "turístico"** | La página lo dice de costado ("negocios no turísticos con saberes configurables como experiencia") en lenguaje que nadie se auto-aplica. |
| **¿Qué me llevo concretamente el 2 de octubre?** | "Ficha técnica comercial", "canal digital de reservas". Correcto pero abstracto: no se ve el antes/después. |
| **¿Quién me va a acompañar?** | "El equipo de facilitadores". Sin nombres, sin caras, sin qué hacen. |

Y hay una sexta que el sitio contesta mal: **¿cuánto trabajo real es?** Dice "12 horas semanales" como una exigencia seca, sin desglosar. Suena a mucho. Desglosado —2 h de taller + 1 visita + el resto trabajando en lo tuyo— suena razonable.

### Además: el registro de lenguaje está corrido

Frases del sitio actual y cómo las lee el destinatario:

| Sitio actual | Cómo suena | Alternativa |
|---|---|---|
| "Especificaciones Técnicas" (botón del hero) | manual de electrodoméstico | "Cómo funciona" |
| "Información sobre el Impacto del Programa en la Comunidad" | boletín oficial | "¿Y si yo no tengo un emprendimiento turístico?" |
| "Recepción de Formularios de Postulación" | mesa de entradas | "Te postulás" |
| "Ponderación de los proyectos a cargo del Cuadro Técnico" | resolución administrativa | "Un jurado con las cámaras de Esquel lee tu postulación" |
| "Línea orientada a la consolidación de emprendimientos, organizaciones de la sociedad civil y prestadores de servicios turísticos dentro del ejido urbano" | 24 palabras para decir "negocios de la ciudad" | "Si tu proyecto está en la ciudad" |

No hay que volver al tono vendedor. Hay que **bajar el registro sin perder precisión**: el municipio informa, pero le habla a una persona, no a un expediente.

---

## 4. Estructura propuesta del home

Orden actual: Hero → Metodología → Programas → Convocatoria → Comunidad → Gobernanza.
El problema: "Metodología" es la segunda sección. Nadie quiere saber la metodología antes de saber si le sirve.

**Orden propuesto:**

1. **Hero** — qué es, para quién, cuándo cierra, y que es gratis. Todo visible sin scrollear.
2. **¿Esto es para vos?** *(nueva)* — 6 ejemplos concretos en tarjetas: la casa de té, el que hace cerveza, la chacra de frutas finas, el guía sin canal de venta, el taller de telar, el hotel que necesita reinventar su producto. Que cada uno se reconozca en una.
3. **Qué te llevás el 2 de octubre** *(reformulada desde "Entregables")* — antes/después, con ejemplo real.
4. **Elegí tu línea** — Acelera / Raíz. Se queda como está, con copy más corto.
5. **Cómo es el trabajo** — las 8 semanas + el desglose honesto de las 12 horas.
6. **Preguntas frecuentes** *(nueva, la sección más importante que falta)* — cuesta plata, hace falta habilitación, y si no quedo, quién me acompaña, tengo que tener local, y si no sé nada de turismo.
7. **Fechas y cupos** — cronograma corregido.
8. **Quién decide** — gobernanza, con las cámaras.
9. **Para la comunidad** — el mensaje de que no es solo para grandes inversores.

Las secciones 2 y 6 son las que hoy no existen y son las que convierten.

---

## 5. Copy concreto propuesto

No es una descripción de qué escribir: es el texto listo para pegar.

### Hero

> **Convocatoria abierta · Cierra el 9 de agosto**
>
> # Tenés algo. Te falta poder venderlo.
>
> Ocho semanas de trabajo con un equipo técnico que va a tu lugar para que lo que ya hacés —tu servicio, tu campo, tu oficio— tenga precio, canal de venta y esté listo para que un operador lo comercialice.
>
> **Gratuito · 13 a 18 proyectos · Del 10 de agosto al 2 de octubre**
>
> [Postularme] [Ver si es para mí]

Por qué funciona: nombra el problema real del destinatario en cinco palabras, dice qué obtiene, y responde "cuánto cuesta" antes de que lo pregunte.

### Sección "¿Esto es para vos?"

> ## Probablemente sí, aunque no te consideres "turístico"
>
> El programa no busca solo empresas de turismo. Busca gente que ya hace algo que un visitante pagaría por vivir.
>
> - **Tenés un negocio que funciona pero no vende a turistas.** Una casa de té, una panadería, un taller. La gente pasa, compra y se va. Nunca lo armaste como experiencia.
> - **Sos guía o prestador y vendés solo por WhatsApp.** Tenés el servicio, no tenés precio para agencias ni forma de que te reserven online.
> - **Tenés una chacra o un campo y no sabés si "eso" se puede visitar.** La esquila, la cosecha, el proceso de dulces. Para vos es trabajo. Para un visitante es algo que nunca vio.
> - **Hacés algo con las manos y lo vendés suelto.** Lana, cerámica, conservas. Sin relato, sin packaging, sin conexión con quien visita Esquel.
> - **Tenés un emprendimiento turístico que se quedó.** Funcionaba y hoy no. Necesitás rearmar el producto, no empezar de cero.
> - **Tenés una idea y algo con qué arrancar.** No hace falta que ya esté funcionando: hace falta que puedas dedicarle tiempo.

### Sección "Qué te llevás"

> ## El 2 de octubre te vas con esto en la mano
>
> No con un diagnóstico ni un PDF de recomendaciones. Con la experiencia armada y andando.
>
> | | Antes | Después |
> |---|---|---|
> | **Precio** | "Y… depende, hacemé una oferta" | Precio al público y precio neto para agencias, con tus costos calculados |
> | **Reservas** | WhatsApp cuando te acordás de contestar | Un canal digital donde te reservan y te llega el aviso |
> | **Cómo lo contás** | Improvisado cada vez | Un guión de la experiencia: qué pasa, en qué orden, cuánto dura |
> | **Fotos** | Las del celular | Registro fotográfico para promoción |
> | **Quién lo vende** | Vos | Tu ficha en manos de las agencias receptivas de Esquel |
> | **Qué se lleva el visitante** | El recuerdo | El recuerdo y un producto físico tuyo, con identidad de Esquel |

### Las 12 horas, desglosadas

> ## 12 horas por semana. Esto es lo que son.
>
> - **2 h** — Taller grupal con el resto de la cohorte.
> - **1 a 2 h** — Reunión individual con el equipo técnico, en tu lugar de trabajo.
> - **8 a 9 h** — Trabajo tuyo sobre tu propio proyecto: costos, fotos, textos, probar la experiencia.
>
> La mayor parte del tiempo es trabajo sobre lo tuyo, no reuniones. Pero es tiempo real y por eso lo pedimos por escrito: un cupo ocupado por alguien que no puede sostenerlo es un cupo que le sacamos a otro.

### Preguntas frecuentes (la sección que falta)

> **¿Cuánto cuesta?**
> Nada. El programa es gratuito. Está financiado por el municipio a través de las Subsecretarías de Turismo y de Producción.
>
> **¿Necesito tener habilitación, monotributo o estar en el Registro de Prestadores?**
> Para postularte, no. Si tu propuesta necesita alguna habilitación para poder venderse, parte del acompañamiento es ayudarte a ordenar eso. No te quedes afuera por esto.
>
> **No tengo un emprendimiento turístico. ¿Igual puedo?**
> Sí, y es parte del objetivo. Varios de los proyectos que buscamos hoy no se dedican al turismo: son oficios, producciones o negocios locales que pueden convertirse en una experiencia.
>
> **¿Y si no quedo seleccionado?**
> Tu postulación queda en el registro del programa. Esta es la primera cohorte de un proceso continuo, y de lo que veamos acá van a salir las próximas convocatorias y programas complementarios. Si tu propuesta no entra ahora, sabemos que existe.
>
> **¿Quién me va a acompañar?**
> Un equipo técnico de facilitadores del programa, más el respaldo de las Subsecretarías de Turismo y Producción. El acompañamiento es individual y presencial: van a tu lugar de trabajo.
>
> **¿Tengo que tener un local o un lugar físico?**
> No necesariamente. Hay experiencias que ocurren en un recorrido, en un campo o en la casa de quien la ofrece.
>
> **¿Puedo postularme con más de un proyecto?**
> Sí, pero postulá cada uno por separado y tené en cuenta que el compromiso de horas es por proyecto.
>
> **¿Qué pasa si me seleccionan y no puedo sostener las 12 horas?**
> Avisanos apenas lo sepas. Preferimos liberar el cupo a mitad de camino que perderlo entero.

*(Las respuestas sobre habilitación y sobre quién forma el equipo hay que confirmarlas con vos antes de publicarlas — las dejo redactadas con el criterio más razonable, pero son definiciones tuyas, no mías.)*

---

## 6. Look & feel

El dark mode con glassmorphism está bien ejecutado, pero **es el registro equivocado para este público**. Se lee como una startup de software o un producto cripto. La persona que tiene que postularse acá puede ser una señora de 60 años con una casa de té o un criancero rural: para ellos, negro + neón + tipografía condensada no dice "el municipio me acompaña", dice "esto no es para mí".

Encima, el "dark tech + acento neón + mucho glass" es hoy uno de los looks más reconociblemente generados por IA que hay. Buscando alejarse de lo genérico, se cayó en otro genérico.

**Propuesta: no volver al beige claro que hice yo, sino a un registro con identidad de destino.**

- **Fondo claro y cálido**, con textura sutil de papel o grano. Institucional pero no frío.
- **Fotografía real de Esquel como protagonista** — lo que más falta. Hoy el sitio no tiene una sola foto. Un programa sobre experiencias turísticas sin una imagen del lugar ni de gente trabajando es un contrasentido. Con 6-8 fotos reales (el municipio las tiene) el sitio cambia por completo.
- **Magenta `#ab2759` como acento único**, el del logo real. Verde solo para Raíz.
- **Tipografía con más carácter en títulos** y una sans muy legible en cuerpo, con tamaño base 17-18px (el público no es todo joven).
- **Los logos, más grandes.** Hoy están a 64px de alto en las tarjetas de programa y el wordmark queda ilegible: son lockups cuadrados con texto, necesitan 120px+ o usar una versión horizontal.
- **Modo oscuro como opción**, no como única identidad.

---

## 7. Formulario

Hoy: 3 pasos, ~8 campos. Es un formulario de contacto, no un instrumento de evaluación. Con 8 campos no se puede ponderar seriamente sobre cinco criterios, y el CRM tiene cinco sliders de puntaje que no tienen de dónde sacar la información.

**Propuesta: 6 pasos, con cada paso alimentando un criterio.**

| Paso | Qué pide | Alimenta |
|---|---|---|
| 1 | Elegí tu línea (sin default) + auto-diagnóstico: "¿en qué situación estás?" (funcionando / parado / idea / necesito rearmar) | Segmentación |
| 2 | Datos de contacto y del proyecto | Base |
| 3 | Qué hacés hoy y qué tiene de único | Diferenciación |
| 4 | Con quién te conectás en Esquel + qué producto físico podés asociar | Impacto en la matriz + Economía de los Recuerdos |
| 5 | Con qué contás hoy y qué te falta para vender sin gran inversión | Viabilidad operativa |
| 6 | Por qué vos + compromiso de 12 h + revisión antes de enviar | Perfil y motivación |

Cambios de UX imprescindibles:
- **Guardado automático del borrador** en el navegador. Un formulario de 20 minutos sin autosave pierde postulantes.
- **Que funcione sin JavaScript** (hoy si falla el JS aparecen los campos de las dos líneas juntos y no se puede navegar entre pasos).
- **Barra de progreso real** y pantalla de revisión antes de enviar.
- **Ejemplos de respuesta** en cada campo abierto, no solo un placeholder genérico.
- **Email automático de confirmación** al postulante. Hoy manda el formulario y no recibe nada: no tiene comprobante de que se envió.

---

## 8. Backend

Lo que hay funciona. Estas son las mejoras por orden de importancia:

1. **Exportar las respuestas completas en el CSV** (hoy se pierden). Es lo que necesita el Cuadro Técnico para evaluar sin entrar al panel.
2. **Vista de evaluación comparada**: las postulaciones de una línea, una al lado de otra, con los cinco puntajes. Hoy hay que abrir de a una y no se puede comparar.
3. **Puntaje ponderado, no promedio simple.** Vos marcaste que "perfil y motivación" es el criterio más importante; hoy los cinco pesan igual. Con pesos configurables, el ranking refleja el criterio real del programa.
4. **Forzar cambio de la contraseña `admin123` en el primer ingreso.** Hoy queda activa para siempre; en un sitio público eso es una puerta abierta.
5. **CSRF en el panel** (crear usuario, borrar usuario y actualizar postulación aceptan POST sin token).
6. **Registro de quién cambió qué estado y cuándo.** Si el proceso se presenta como transparente ante las cámaras, tiene que poder mostrarse la trazabilidad.
7. **Backup del `.sqlite`**: un botón de descarga de la base. El archivo vive en el hosting y no está en Git — si se pierde el hosting, se pierden las postulaciones.
8. Marcar cada postulación como **leída/no leída** por usuario, para repartir la lectura entre el equipo.

---

## 9. Orden de trabajo sugerido

**Ahora (antes de difundir el link):**
1. Los 10 bugs de la sección 2 — sobre todo los `**`, los campos sin `required` y el cierre por fecha.
2. Sección de preguntas frecuentes + "gratuito" en el hero. Es el cambio de una tarde que más postulaciones agrega.
3. Contraseña de admin y CSRF.

**Después (esta semana):**
4. Reescritura del copy del home con la estructura de la sección 4.
5. Formulario de 6 pasos con autosave.
6. Export completo del CSV.

**Cuando haya fotos:**
7. Cambio de look & feel a registro claro con fotografía real.

---

## 10. Lo que necesito de vos para avanzar

1. **¿El programa es gratuito?** Doy por hecho que sí, pero no puedo publicarlo sin tu confirmación.
2. **¿Hace falta habilitación / monotributo / RNTC para participar?** Define la respuesta de la FAQ más importante.
3. **¿Quién forma el equipo técnico?** Nombres y roles, aunque sea breve — le da cara al programa.
4. **Fotos**: ¿tenés acceso al banco de imágenes de Turismo/Producción?
5. **¿El 9 de agosto es cierre duro?** Si sí, implemento el bloqueo automático.
6. **Contacto de prensa**: hoy figura `comunicacionesquel25@gmail.com`. ¿Es el definitivo?
