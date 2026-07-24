# Esquel LAB — sitio de la primera cohorte

Sitio del Laboratorio de Destino Esquel: convocatoria y postulación a **Esquel Acelera** (urbano) y **Raíz** (rural), sala de prensa, y panel interno de evaluación de postulaciones.

- Auditoría del estado anterior y criterios de las decisiones: [`docs/AUDITORIA-Y-MEJORAS.md`](docs/AUDITORIA-Y-MEJORAS.md)
- Qué foto real va en cada lugar: [`docs/FOTOS-QUE-NECESITAMOS.md`](docs/FOTOS-QUE-NECESITAMOS.md)

## Stack

PHP 8 + SQLite, HTML/CSS/JS sin build step. Pensado para un slot de hosting compartido de Hostinger con despliegue por Git: el repositorio *es* el sitio, no hay que compilar nada.

```
index.php            Home
inscribirse.php      Formulario de postulación (6 pasos)
media-kit.php        Sala de prensa
includes/            Config, base, helpers, auth y layout compartido
admin/               Panel de evaluación
assets/              CSS, JS, logos e ilustraciones
data/                Base SQLite (bloqueada por .htaccess, fuera de Git)
tools/               Generador de las ilustraciones
docs/                Documentación del proyecto
```

## Levantarlo local

```bash
php -S localhost:8000
```

La base se crea sola en `data/database.sqlite` en el primer acceso, con el usuario semilla:

- **usuario:** `admin`
- **contraseña:** `admin123`

El sistema **obliga a cambiar esa contraseña en el primer ingreso** y no deja usar el panel hasta que se haya cambiado.

## Deploy en Hostinger

1. hPanel → **Websites → Add website → PHP/HTML personalizado**.
2. En el sitio: **Avanzado → Git → Continuar con GitHub**, elegí este repositorio y la rama. Directorio de despliegue: `public_html`.
3. Activá **Auto Deployment** para que cada push actualice el sitio.
4. Verificá que la carpeta `data/` tenga permiso de escritura para PHP (755 alcanza en la mayoría de los casos).
5. Entrá a `/admin/login.php`, cambiá la contraseña y cargá al resto del equipo desde **Usuarios**.

> **Importante:** `data/database.sqlite` está en `.gitignore`. Las postulaciones viven solo en el servidor, no en Git — hacé backup del archivo periódicamente durante la convocatoria.

## Fechas del programa

Están todas en `includes/config.php`. Si cambian, se editan ahí y el sitio entero se actualiza: el countdown, los textos, la sala de prensa y el cierre automático del formulario.

```php
const FECHA_APERTURA = '2026-07-23 00:00:00';
const FECHA_CIERRE   = '2026-08-09 23:59:59';  // cierre duro
const FECHA_INICIO   = '2026-08-10 00:00:00';
const FECHA_FIN      = '2026-10-02 00:00:00';
const CIERRE_DURO    = true;
```

Con `CIERRE_DURO = true`, pasada la fecha de cierre el formulario deja de aceptar envíos y muestra un mensaje explicando que la convocatoria cerró.

## Roles del panel

| Rol | Puede |
|---|---|
| `viewer` | Ver postulaciones y descargar el CSV |
| `editor` | Todo lo anterior + puntuar, cambiar estados y dejar notas |
| `admin` | Todo lo anterior + gestionar usuarios |

## Criterios de evaluación

Los cinco criterios y **sus pesos** están en `includes/config.php` (`CRITERIOS`). El puntaje que muestra el panel es un promedio ponderado, no simple: hoy *perfil y motivación* pesa 1.5× y *producto físico* 0.5×, según la definición del programa. Cambiar un peso ahí reordena automáticamente el ranking en el panel y en el CSV.

## Regenerar las ilustraciones

```bash
node tools/generar-imagenes.mjs
```

Escribe los SVG en `assets/images/ilustraciones/`. Son provisorias: ver el documento de fotos.
