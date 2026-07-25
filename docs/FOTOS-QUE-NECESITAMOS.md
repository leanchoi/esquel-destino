# Qué foto va en cada lugar

Las ilustraciones que están hoy en el sitio (`assets/images/ilustraciones/`) son **propias y provisorias**: sirven para que el sitio no se vea vacío y para dejar en claro qué tipo de imagen va en cada slot. Se generan con `node tools/generar-imagenes.mjs`.

**Reemplazalas por fotos reales apenas las tengas.** Cada slot está marcado en el código con un comentario `<!-- FOTO REAL PENDIENTE -->` y la ilustración respeta el encuadre exacto que va a ocupar la foto.

Al reemplazar: dejá el mismo nombre de archivo con extensión `.jpg` y actualizá el `src` en el HTML, o simplemente guardá el `.jpg` y cambiá la extensión en la etiqueta `<img>`.

Si en vez de salir a sacar fotos vas a generarlas con Gemini (o cualquier otro generador de imágenes), usá los prompts de este documento — están armados para que las diez imágenes salgan de **la misma familia estética** y no parezca que cada una vino de un lugar distinto.

---

## Cómo usar los prompts para que la página quede pareja

1. **Pegá siempre el bloque "Estilo base" antes de la descripción de la escena.** Es lo que hace que las diez fotos compartan luz, paleta y textura. Sin eso, cada imagen sale con su propio estilo y la página se ve desprolija aunque cada foto individual esté buena.
2. **No cambies la condición de luz entre fotos.** Todos los prompts de acá abajo ya están escritos con la misma luz (dorada, de atardecer) a propósito. Si a alguna no le queda bien esa luz, mejor ajustá la escena antes que cambiar la hora del día — es lo primero que rompe la sensación de conjunto.
3. **Generá cada una 2 o 3 veces** y elegí la variante que más se parezca a las demás en tono de color, no solo la que más te guste sola. Gemini varía bastante de una corrida a otra.
4. **Mirá las diez juntas antes de subirlas.** Ponelas una al lado de la otra (aunque sea en una carpeta) y fijate que la temperatura de color no salte de una foto cálida a una fría.
5. Si Gemini te devuelve texto, logos o marcas de agua inventados dentro de la imagen (pasa seguido), volvé a generar — no los edites a mano, casi nunca queda bien.

---

## Estilo base — pegar antes de cada prompt

```
Fotografía documental realista, no ilustración ni render 3D ni pintura digital.
Locación: Esquel, Patagonia argentina, con la cordillera andina visible o
insinuada de fondo. Luz natural cálida de atardecer (golden hour), sombras
largas y suaves. Paleta de color en tonos tierra, piedra y crema, con un
acento discreto de color vino/magenta (similar a #AB2759) en algún objeto
puntual de la escena —una prenda, un cartel, una etiqueta— y verde bosque
apagado en la vegetación, nunca saturado. Grano fino tipo película de 35mm,
sin look HDR ni sobreprocesado. Composición documental: personas reales
haciendo una tarea concreta, en movimiento o a mitad de una acción, nunca
mirando a cámara ni posando con sonrisa de foto de stock. Profundidad de
campo moderada. Sin texto superpuesto, sin logos, sin marcas de agua, sin
marcos ni bordes.
```

---

## Regla general (además del estilo base)

- **Personas haciendo cosas, no personas posando.** Manos trabajando, alguien en movimiento, el proceso a mitad de camino.
- **Pedir autorización de uso de imagen** a las personas reales que aparezcan en fotos de verdad. Es un sitio municipal.
- Formato horizontal, mínimo 1600 px de ancho, JPG optimizado (menos de 300 KB cada una).

---

## 1. Hero — `hero-valle.svg` → `hero-esquel.jpg`

**Dónde:** lo primero que se ve al entrar. La foto más importante del sitio.
**Formato:** horizontal, aprox. 4:3.

```
[ESTILO BASE]

Vista panorámica del valle de Esquel al atardecer, con la cordillera de los
Andes patagónicos de fondo y un bosque de coihues cubriendo la ladera. En
primer plano, dos personas de espaldas —una con una mochila de trekking—
mirando el paisaje, dando escala a la montaña y sin mostrar el rostro. Cielo
con nubes bajas iluminadas por la última luz del día.

Formato horizontal, relación de aspecto 4:3.
```

**Lo que NO funciona:** una postal vacía sin personas. Es lo que hace que se vea genérica.

---

## 2 a 7. Perfiles de "¿Es para vos?"

Seis fotos, formato **16:10 horizontal**. Cada una tiene que hacer que alguien se reconozca en tres segundos.

### `perfil-gastronomia.svg`

```
[ESTILO BASE]

Interior cálido de una casa de té patagónica: una mesa de madera con una
tetera de metal humeante, dos tazas de cerámica y una torta casera recién
cortada. Al fondo, una ventana con marco de madera que deja ver las
montañas nevadas. Manos de una persona sirviendo el té, sin mostrar el
rostro. Luz cálida de atardecer entrando de costado por la ventana.

Formato horizontal, relación de aspecto 16:10.
```

### `perfil-guia.svg`

```
[ESTILO BASE]

Un guía de montaña, visto de espaldas o de perfil, señalando un pico
nevado a dos visitantes en un sendero de trekking rodeado de coihues.
Mochilas, bastones de trekking, ropa de montaña. Luz rasante de atardecer
entre los troncos de los árboles.

Formato horizontal, relación de aspecto 16:10.
```

### `perfil-chacra.svg`

```
[ESTILO BASE]

Una persona agachada cosechando frambuesas entre hileras de plantas en
una chacra patagónica, con un invernadero de estructura simple al fondo
y la cordillera detrás. Un balde con fruta recién cosechada en primer
plano. Manos con tierra, ropa de trabajo de campo. Luz dorada de
atardecer rasante entre las hileras.

Formato horizontal, relación de aspecto 16:10.
```

### `perfil-lana.svg`

```
[ESTILO BASE]

Interior de un taller textil rústico: una persona tejiendo en un telar
de pie de madera, con madejas de lana teñida a mano —tonos vino, mostaza
y verde apagado— colgando de una barra al costado. Luz cálida de
atardecer entrando por una ventana lateral, textura de la lana marcada
en primer plano.

Formato horizontal, relación de aspecto 16:10.
```

### `perfil-reabrir.svg`

```
[ESTILO BASE]

Fachada de un pequeño local comercial en una calle de Esquel, con la
cordillera asomando entre los techos. Una persona levantando la persiana
metálica para abrir el negocio, vista de espaldas o de perfil. Luz
rasante de atardecer, reflejo tenue de la montaña en la vidriera.

Formato horizontal, relación de aspecto 16:10.
```

### `perfil-idea.svg`

```
[ESTILO BASE]

Vista cenital de un escritorio de madera con un cuaderno abierto con
anotaciones y bocetos escritos a mano (garabatos y líneas, sin palabras
legibles), un lápiz, un mate con bombilla y una taza. Luz cálida entrando
de costado, como al final de la tarde. Sin personas en cuadro.

Formato horizontal, relación de aspecto 16:10.
```

---

## 8. Economía de los Recuerdos — `economia-recuerdos.svg`

**Formato:** horizontal, aprox. 8:5. El punto de esta imagen es el packaging y el relato, así que la etiqueta tiene que ser protagonista.

```
[ESTILO BASE]

Composición de productos artesanales patagónicos sobre una mesa de
madera rústica: un frasco de dulce casero con una etiqueta de papel
escrita a mano bien visible, una botella de cerveza o licor artesanal
con etiqueta simple, y una madeja de lana teñida natural. Luz lateral
cálida de atardecer que resalta las texturas y las etiquetas.

Formato horizontal, relación de aspecto 8:5 aproximada.
```

---

## 9 y 10. Las dos líneas

Formato **21:9, panorámico** (una banda ancha arriba de cada tarjeta).

### `linea-acelera.svg`

```
[ESTILO BASE]

Calle comercial de una ciudad patagónica pequeña, con fachadas bajas de
colores tierra y la cordillera nevada asomando al final de la calle.
Dos o tres personas caminando por la vereda a media distancia, sin mirar
a cámara. Luz cálida de atardecer, sombras largas sobre el pavimento.

Formato panorámico, relación de aspecto 21:9.
```

### `linea-raiz.svg`

```
[ESTILO BASE]

Campo rural en las afueras de Esquel: un alambrado de postes de madera,
un grupo de ovejas pastando, un galpón de chapa a lo lejos y la
cordillera de fondo. Una persona con un perro de trabajo caminando entre
los animales, vista de espaldas o de perfil. Luz dorada de atardecer.

Formato panorámico, relación de aspecto 21:9.
```

---

## Fotos que van a servir después (no urgentes, y estas sí tienen que ser reales)

Estas cuatro no se pueden generar con IA de forma honesta — son evidencia de que el programa funcionó, no ambientación. Cuando la cohorte esté en marcha:

1. **El equipo trabajando en el lugar de un participante** — la escena del acompañamiento, que es lo que el programa vende y hoy no se puede mostrar.
2. **Un taller grupal** con la cohorte completa.
3. **Retratos de los participantes seleccionados** en su lugar de trabajo.
4. **El evento de cierre del 2 de octubre.**

Conviene tener a alguien sacando fotos desde la primera visita de diagnóstico. Cuando las tengas, van a reemplazar el argumento por la prueba en la próxima convocatoria — son más valiosas que cualquier imagen generada, real o no.

---

## Logos

Los cuatro logos están en `assets/images/`, ya recortados y con los nombres corregidos:

| Archivo | Contenido |
|---|---|
| `logo-esquel-lab.png` | Esquel LAB, color |
| `logo-esquel-lab-blanco.png` | Esquel LAB, blanco (para fondos oscuros) |
| `logo-esquel-acelera.png` | Esquel Acelera, color |
| `logo-esquel-raiz.png` | Raíz, color |

Falta, si existen: las versiones en blanco de Acelera y Raíz, y los archivos vectoriales (SVG o AI) de los cuatro.
