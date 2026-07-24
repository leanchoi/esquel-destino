# Qué foto va en cada lugar

Las ilustraciones que están hoy en el sitio (`assets/images/ilustraciones/`) son **propias y provisorias**: sirven para que el sitio no se vea vacío y para dejar en claro qué tipo de imagen va en cada slot. Se generan con `node tools/generar-imagenes.mjs`.

**Reemplazalas por fotos reales apenas las tengas.** Cada slot está marcado en el código con un comentario `<!-- FOTO REAL PENDIENTE -->` y la ilustración respeta el encuadre exacto que va a ocupar la foto.

Al reemplazar: dejá el mismo nombre de archivo con extensión `.jpg` y actualizá el `src` en el HTML, o simplemente guardá el `.jpg` y cambiá la extensión en la etiqueta `<img>`.

---

## Regla general para todas

- **Personas haciendo cosas, no personas posando.** Manos trabajando, alguien en movimiento, el proceso a mitad de camino. Nada de gente mirando a cámara y sonriendo.
- **Luz natural.** Temprano o al atardecer si es exterior. Nada de flash directo.
- **Contexto visible.** Que se vea dónde ocurre: el taller, la chacra, la calle de Esquel.
- **Pedir autorización de uso de imagen** a las personas que aparezcan. Es un sitio municipal.
- Formato horizontal, mínimo 1600 px de ancho, JPG optimizado (menos de 300 KB cada una).

---

## 1. Hero — `hero-valle.svg` → `hero-esquel.jpg`

**Dónde:** lo primero que se ve al entrar. Es la foto más importante del sitio.
**Encuadre:** horizontal, aprox. 4:3.
**Qué tiene que mostrar:** el paisaje de Esquel con la cordillera de fondo, y **una o dos personas de espaldas o de perfil** mirando el paisaje. Las figuras dan escala y hacen que la foto sea sobre gente, no sobre postal.
**Alternativas que también funcionan:** vista del valle desde La Hoya, el paisaje desde la ruta de acceso, la ciudad con la montaña detrás.
**Lo que NO funciona:** una postal vacía sin personas. Es lo que hace que se vea genérica.

---

## 2 a 7. Perfiles de "¿Es para vos?"

Seis fotos, formato **16:10 horizontal**. Cada una tiene que hacer que alguien se reconozca en tres segundos. Son las más importantes después del hero.

| Archivo actual | Foto que necesitamos |
|---|---|
| `perfil-gastronomia.svg` | Una mesa servida en una casa de té o confitería local: tetera, tazas, torta. Mejor si hay manos sirviendo. Si la ventana da a la montaña, mucho mejor. |
| `perfil-guia.svg` | Un guía en el sendero **señalando o explicando** algo a dos o tres visitantes. De espaldas o de perfil. El paisaje detrás. |
| `perfil-chacra.svg` | Alguien **cosechando** fruta fina, o entre las hileras de la chacra. El balde, las manos, la planta. |
| `perfil-lana.svg` | Manos en el telar, o madejas de lana teñida colgando en el taller. Si se ve la persona tejiendo, mejor. |
| `perfil-reabrir.svg` | La fachada de un local con alguien **abriendo la persiana o el negocio**. Transmite "volver a arrancar". |
| `perfil-idea.svg` | Un escritorio o mesa de trabajo real: cuaderno con anotaciones a mano, mate, lápiz. Cenital. Sin personas. |

---

## 8. Economía de los Recuerdos — `economia-recuerdos.svg`

**Encuadre:** horizontal, aprox. 8:5.
**Qué mostrar:** productos locales sobre madera — un frasco de dulce con etiqueta, una botella artesanal, una madeja de lana, una pieza de cerámica. **Con etiqueta visible**, porque el punto de la sección es el packaging y el relato.
**Ideal:** productos que ya tengan el sello "Hecho en Esquel".

---

## 9 y 10. Las dos líneas

Formato **21:9, panorámico** (una banda ancha arriba de cada tarjeta).

- **`linea-acelera.svg`** → una calle comercial de Esquel, con fachadas y la cordillera al fondo. Con gente caminando. Es la foto que dice "urbano".
- **`linea-raiz.svg`** → un campo en el ejido de Esquel: ovejas, alambrado, un galpón, la montaña detrás. Es la foto que dice "rural".

---

## Fotos que van a servir después (no urgentes)

Cuando la cohorte esté en marcha, estas son las que más valor van a tener y hoy no se pueden conseguir:

1. **El equipo trabajando en el lugar de un participante** — la escena del acompañamiento, que es lo que el programa vende y hoy no se puede mostrar.
2. **Un taller grupal** con la cohorte completa.
3. **Retratos de los participantes seleccionados** en su lugar de trabajo.
4. **El evento de cierre del 2 de octubre.**

Estas cuatro son las que van a permitir reemplazar el argumento por la prueba en la próxima convocatoria. Conviene tener a alguien sacando fotos desde la primera visita de diagnóstico.

---

## Logos

Los cuatro logos están en `assets/images/`, ya recortados y con los nombres corregidos:

| Archivo | Contenido |
|---|---|
| `logo-esquel-lab.png` | Esquel LAB, color |
| `logo-esquel-lab-blanco.png` | Esquel LAB, blanco (para fondos oscuros) |
| `logo-esquel-acelera.png` | Esquel Acelera, color |
| `logo-esquel-raiz.png` | Raíz, color |

> **Nota:** los archivos venían cruzados en el repositorio original — `logo-esquel-acelera.png` contenía el logo de LAB, `logo-lab-color.png` contenía el de Raíz, y el logo real de Acelera estaba guardado como `logo-lab-white-2.png`. Ya está corregido, pero si aparece una copia vieja de esos archivos en algún lado, revisá el contenido antes de usarla.

Falta, si existen: las versiones en blanco de Acelera y Raíz, y los archivos vectoriales (SVG o AI) de los cuatro.
