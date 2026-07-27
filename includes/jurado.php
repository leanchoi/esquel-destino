<?php
/**
 * El jurado: quién puede votar, quién votó y qué sale de juntar todos los votos.
 *
 * La composición del jurado no está escrita en ningún lado a mano. Son los
 * usuarios del panel con rol editor o admin, leídos en el momento. Si mañana se
 * suman dos evaluadores más, aparecen solos como pendientes en todas las
 * postulaciones, incluidas las que ya tenían votos: el proceso es dinámico y el
 * indicador de "falta alguien" tiene que reflejar eso sin que nadie toque nada.
 */

require_once __DIR__ . '/db.php';

/**
 * Los roles que votan, sacados de ROLES_INFO.
 *
 * Hoy es sólo el evaluador. El observador entra a mirar, y el admin coordina:
 * mueve las postulaciones de etapa, así que sumarle además un voto sería darle
 * dos veces la misma decisión.
 *
 * @return string[]
 */
function roles_jurado(): array
{
    return array_keys(array_filter(ROLES_INFO, fn($r) => !empty($r['vota'])));
}

/**
 * Los jurados habilitados hoy.
 *
 * @return array<int, array{id:int, username:string, role:string}>
 */
function jurado(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $roles = roles_jurado();
    if (!$roles) {
        return $cache = [];
    }
    $marcas = implode(',', array_fill(0, count($roles), '?'));
    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE role IN ($marcas) ORDER BY username COLLATE NOCASE");
    $stmt->execute($roles);
    return $cache = array_map(
        fn($u) => ['id' => (int) $u['id'], 'username' => $u['username'], 'role' => $u['role']],
        $stmt->fetchAll()
    );
}

/**
 * Todas las evaluaciones de un conjunto de postulaciones, en una sola consulta.
 *
 * @param  int[] $ids
 * @return array<int, array<int, array>>  application_id => lista de votos
 */
function evaluaciones_de(PDO $pdo, array $ids): array
{
    if (!$ids) {
        return [];
    }
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        "SELECT e.*, u.username AS username_actual, u.role AS rol_actual
           FROM evaluaciones e
      LEFT JOIN users u ON u.id = e.user_id
          WHERE e.application_id IN ($marcas)
       ORDER BY e.updated_at"
    );
    $stmt->execute($ids);

    $out = [];
    foreach ($stmt->fetchAll() as $ev) {
        // El username se guarda al votar para que el voto sobreviva al borrado
        // del usuario, pero si la cuenta sigue viva manda el nombre de hoy:
        // renombrar a alguien no tiene por qué dejar votos con el nombre viejo.
        $ev['username'] = $ev['username_actual'] ?? $ev['username'];
        $ev['user_id'] = (int) $ev['user_id'];
        $ev['abstencion'] = (bool) $ev['abstencion'];
        $ev['puntaje'] = $ev['abstencion'] ? null : puntaje_voto($ev);
        $ev['baja'] = $ev['user_id'] > 0 && $ev['rol_actual'] === null;
        unset($ev['username_actual'], $ev['rol_actual']);
        $out[(int) $ev['application_id']][] = $ev;
    }
    return $out;
}

/**
 * Puntaje de un voto individual, de 0 a 5.
 *
 * A diferencia de puntaje_ponderado(), acá el cero es un puntaje y no un
 * "sin evaluar": la fila existe solamente porque un jurado apretó guardar.
 */
function puntaje_voto(array $ev): float
{
    $suma = 0.0;
    $pesos = 0.0;
    foreach (CRITERIOS as $campo => $def) {
        $suma  += (int) ($ev[$campo] ?? 0) * $def['peso'];
        $pesos += $def['peso'];
    }
    return $pesos > 0 ? round($suma / $pesos, 2) : 0.0;
}

/**
 * Junta los votos de una postulación en un solo cuadro.
 *
 * El puntaje global se calcula promediando cada criterio entre los jurados y
 * recién después ponderando. Da lo mismo que promediar los puntajes finales
 * —los pesos son constantes— pero de paso deja el promedio por criterio, que es
 * lo que muestra en qué está de acuerdo el jurado y en qué no.
 *
 * @param array $votos    las evaluaciones de esa postulación
 * @param array $jurado   los jurados habilitados hoy
 */
function consolidar(array $votos, array $jurado): array
{
    $cuentan = array_values(array_filter($votos, fn($v) => !$v['abstencion']));
    $abstenciones = array_values(array_filter($votos, fn($v) => $v['abstencion']));

    $promedios = [];
    $puntaje = null;
    if ($cuentan) {
        $suma = 0.0;
        $pesos = 0.0;
        foreach (CRITERIOS as $campo => $def) {
            $prom = array_sum(array_map(fn($v) => (int) ($v[$campo] ?? 0), $cuentan)) / count($cuentan);
            $promedios[$campo] = round($prom, 2);
            $suma  += $prom * $def['peso'];
            $pesos += $def['peso'];
        }
        $puntaje = $pesos > 0 ? round($suma / $pesos, 2) : null;
    }

    // Quién falta. Sólo se le reclama el voto a los jurados de hoy: el evaluador
    // heredado y los que se dieron de baja no cuentan como pendientes.
    $votaron = array_column($votos, 'user_id');
    $faltan = array_values(array_filter($jurado, fn($j) => !in_array($j['id'], $votaron, true)));

    // Dispersión: cuánto separa al jurado más duro del más generoso. Con un solo
    // voto no hay nada que comparar.
    $dispersion = null;
    if (count($cuentan) >= 2) {
        $ps = array_map(fn($v) => $v['puntaje'], $cuentan);
        $dispersion = round(max($ps) - min($ps), 2);
    }

    return [
        'puntaje'      => $puntaje,
        'promedios'    => $promedios,
        'votos'        => count($cuentan),
        'abstenciones' => count($abstenciones),
        'emitidos'     => count($votos),
        'jurados'      => count($jurado),
        'faltan'       => array_column($faltan, 'username'),
        'completo'     => $faltan === [] && $jurado !== [],
        'dispersion'   => $dispersion,
        // Un jurado que puso 3.4 y otro que puso 1.2 sobre lo mismo es una
        // conversación pendiente, no un promedio.
        'disenso'      => $dispersion !== null && $dispersion >= 1.5,
    ];
}

/** El voto de un jurado dentro de la lista de votos de una postulación. */
function voto_de(array $votos, int $userId): ?array
{
    foreach ($votos as $v) {
        if ($v['user_id'] === $userId) {
            return $v;
        }
    }
    return null;
}
