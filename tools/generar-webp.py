#!/usr/bin/env python3
"""
Genera las versiones web de las fotos: al tamaño real de uso y en dos formatos.

Los originales de 1800 px se quedan en assets/images/fotos/ como master. El
problema era servirlos tal cual: una tarjeta de perfil mide unos 365 px en
pantalla, así que el navegador se bajaba cuatro veces los píxeles que iba a
mostrar. Acá se escribe, en assets/images/fotos/web/, cada foto al doble de su
tamaño de uso (que es lo que necesita una pantalla retina) en JPEG y en WebP.

El helper foto() de includes/helpers.php arma el <picture> que los ofrece.

    python3 tools/generar-webp.py
"""
from PIL import Image
import glob
import os

ORIGEN = 'assets/images/fotos'
DESTINO = f'{ORIGEN}/web'

# ancho de uso en pantalla -> se exporta al doble
ANCHOS_CSS = {
    'hero-esquel': 560,          # columna del hero
    'linea-acelera': 560,        # tarjetas de línea, 2 columnas
    'linea-raiz': 560,
    'economia-recuerdos': 460,   # columna de la lista de trabajo
}
ANCHO_PERFIL = 380               # tarjetas de perfil, 3 columnas

os.makedirs(DESTINO, exist_ok=True)
antes = despues = 0

for ruta in sorted(glob.glob(f'{ORIGEN}/*.jpg')):
    nombre = os.path.basename(ruta).rsplit('.', 1)[0]
    ancho_final = ANCHOS_CSS.get(nombre, ANCHO_PERFIL) * 2

    with Image.open(ruta) as im:
        if im.width > ancho_final:
            alto = round(im.height * ancho_final / im.width)
            im = im.resize((ancho_final, alto), Image.LANCZOS)
        im = im.convert('RGB')
        im.save(f'{DESTINO}/{nombre}.jpg', 'JPEG', quality=82, optimize=True, progressive=True)
        im.save(f'{DESTINO}/{nombre}.webp', 'WEBP', quality=80, method=6)

    kb_o = os.path.getsize(ruta) / 1024
    kb_j = os.path.getsize(f'{DESTINO}/{nombre}.jpg') / 1024
    kb_w = os.path.getsize(f'{DESTINO}/{nombre}.webp') / 1024
    antes += kb_o
    despues += kb_w
    print(f'  {nombre:<22} {kb_o:6.0f} KB  ->  jpg {kb_j:5.0f}  webp {kb_w:5.0f}   ({im.width}px)')

print(f'\n  lo que baja el visitante (webp): {antes:.0f} KB -> {despues:.0f} KB '
      f'({100 - despues / antes * 100:.0f}% menos)')
