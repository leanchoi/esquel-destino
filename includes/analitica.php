<?php
/**
 * Analítica propia, sin servicios externos.
 *
 * Dos principios que valen más que cualquier métrica:
 *
 *  1. Nunca puede romper el sitio. Todo va en try/catch y, si algo falla,
 *     la página se sirve igual y el error queda en el log.
 *  2. No identifica personas. No guarda IPs en claro ni usa cookies de
 *     seguimiento. El "visitante" es un hash de IP + navegador + fecha + sal,
 *     así que cambia todos los días: alcanza para contar únicos por jornada
 *     y no sirve para seguir a nadie a lo largo del tiempo.
 */

require_once __DIR__ . '/db.php';

/** Sal del sitio. Se genera sola la primera vez y no se versiona. */
function analitica_sal(): string
{
    static $sal = null;
    if ($sal !== null) {
        return $sal;
    }
    $ruta = __DIR__ . '/../data/.sal';
    if (is_file($ruta)) {
        $sal = (string) file_get_contents($ruta);
    } else {
        $sal = bin2hex(random_bytes(32));
        @file_put_contents($ruta, $sal);
    }
    return $sal;
}

/** ¿Esto es un buscador o un robot? No los contamos como visitas. */
function analitica_es_robot(string $ua): bool
{
    if ($ua === '') {
        return true;
    }
    return (bool) preg_match('/bot|crawl|spider|slurp|bing|baidu|yandex|duckduck|facebookexternalhit|preview|monitor|curl|wget|headless|lighthouse/i', $ua);
}

function analitica_dispositivo(string $ua): string
{
    if (preg_match('/ipad|tablet|playbook|silk/i', $ua)) {
        return 'tablet';
    }
    if (preg_match('/mobi|android|iphone|ipod|phone/i', $ua)) {
        return 'movil';
    }
    return 'escritorio';
}

/** De dónde llegó. Vacío = entró directo o escribió la dirección. */
function analitica_origen(string $referer, string $hostPropio): string
{
    if ($referer === '') {
        return '';
    }
    $host = (string) parse_url($referer, PHP_URL_HOST);
    $host = strtolower(preg_replace('/^www\./', '', $host));
    if ($host === '' || $host === $hostPropio) {
        return '';                       // navegación interna: no es un origen
    }
    return substr($host, 0, 120);
}

/**
 * País, si el hosting lo expone en alguna cabecera. Hostinger sin Cloudflare
 * normalmente no lo hace, así que puede quedar vacío: es eso o mandarle la IP
 * de cada visitante a un servicio de terceros, que no vale la pena.
 */
function analitica_pais(): ?string
{
    foreach (['HTTP_CF_IPCOUNTRY', 'HTTP_X_GEO_COUNTRY', 'HTTP_X_APPENGINE_COUNTRY', 'GEOIP_COUNTRY_CODE'] as $c) {
        $v = $_SERVER[$c] ?? '';
        if (is_string($v) && preg_match('/^[A-Za-z]{2}$/', $v) && strtoupper($v) !== 'XX') {
            return strtoupper($v);
        }
    }
    return null;
}

/**
 * Registra la visita y devuelve [id, token] para que el navegador pueda
 * completar después cuánto se quedó y hasta dónde bajó. El token es un HMAC:
 * sin él no se puede tocar una fila desde afuera.
 */
function registrar_visita(string $ruta): ?array
{
    try {
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        if (analitica_es_robot($ua)) {
            return null;
        }

        $ip   = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $host = strtolower(preg_replace('/^www\.|:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));

        // El hash lleva la fecha adentro: mañana el mismo visitante es otro.
        $visitante = substr(hash('sha256', $ip . '|' . $ua . '|' . date('Y-m-d') . '|' . analitica_sal()), 0, 20);

        $pdo = db();
        $stmt = $pdo->prepare(
            'INSERT INTO visitas (ruta, origen, dispositivo, pais, visitante) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            substr($ruta, 0, 190),
            analitica_origen((string) ($_SERVER['HTTP_REFERER'] ?? ''), $host),
            analitica_dispositivo($ua),
            analitica_pais(),
            $visitante,
        ]);

        $id = (int) $pdo->lastInsertId();
        return [$id, analitica_token($id)];
    } catch (Throwable $e) {
        error_log('Esquel LAB analítica: ' . $e->getMessage());
        return null;                     // la página sigue como si nada
    }
}

function analitica_token(int $id): string
{
    return substr(hash_hmac('sha256', (string) $id, analitica_sal()), 0, 24);
}

/** Borra lo que ya no se mira. Mantiene la base chica en hosting compartido. */
function analitica_purgar(int $dias = 400): void
{
    try {
        db()->prepare("DELETE FROM visitas WHERE creada_at < datetime('now', ?)")
            ->execute(['-' . $dias . ' days']);
    } catch (Throwable $e) {
        error_log('Esquel LAB purga analítica: ' . $e->getMessage());
    }
}
