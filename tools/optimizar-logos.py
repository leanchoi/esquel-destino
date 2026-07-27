#!/usr/bin/env python3
"""
Genera las versiones web de los logos.

Los archivos de assets/images/ son los originales y ahí se quedan: el media kit
los ofrece para descarga y la prensa los necesita en alta. El problema es que
la página los estaba usando tal cual —un PNG de 418 KB para mostrarlo a 104 px
de alto— y entre los cuatro logos se llevaban más de un mega de la primera
carga, más que todas las fotos juntas.

Esto escribe copias en assets/images/web/ al doble del tamaño de uso, que es lo
que necesita una pantalla retina y nada más.

    python3 tools/optimizar-logos.py
"""
from PIL import Image
import os

ORIGEN = 'assets/images'
DESTINO = 'assets/images/web'

# archivo -> alto de uso en CSS, en px. Se exporta al doble para retina.
ALTOS = {
    'logo-esquel-lab-horizontal.png': 48,        # header 42, pie 46
    'logo-esquel-lab-horizontal-blanco.png': 48,
    'logo-esquel-acelera.png': 104,              # .line-logo
    'logo-esquel-raiz.png': 104,
    'logo-municipio-esquel.png': 46,             # franja institucional del hero
    'logo-esquel-lab.png': 62,                   # apilado, por si se usa
}

os.makedirs(DESTINO, exist_ok=True)
total_antes = total_despues = 0

for archivo, alto_css in ALTOS.items():
    ruta = os.path.join(ORIGEN, archivo)
    if not os.path.isfile(ruta):
        print(f'  falta {archivo}, se saltea')
        continue

    im = Image.open(ruta).convert('RGBA')
    alto_final = alto_css * 2
    ancho_final = round(im.width * alto_final / im.height)
    im = im.resize((ancho_final, alto_final), Image.LANCZOS)

    salida = os.path.join(DESTINO, archivo)
    # quantize conserva el canal alfa y baja mucho el peso en logos planos.
    im.quantize(colors=192, method=Image.FASTOCTREE).save(salida, optimize=True)

    antes = os.path.getsize(ruta) / 1024
    despues = os.path.getsize(salida) / 1024
    total_antes += antes
    total_despues += despues
    print(f'  {archivo:<40} {antes:6.0f} KB -> {despues:5.0f} KB  ({ancho_final}x{alto_final})')

print(f'\n  total: {total_antes:.0f} KB -> {total_despues:.0f} KB '
      f'({100 - total_despues / total_antes * 100:.0f}% menos)')
