<?php
/**
 * Base de prueba para revisar el panel sin tocar datos reales.
 *
 *     php tools/sembrar-prueba.php
 *
 * Crea jurados, postulaciones y votos repartidos a propósito: alguna sin
 * ningún voto, alguna con el jurado completo, alguna con disenso fuerte y una
 * abstención. Es lo que hace falta para ver si los indicadores dicen la verdad.
 *
 * Nunca corre contra la base de producción: escribe en data/database.sqlite del
 * repositorio, que está en .gitignore y no se sube.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/jurado.php';

$pdo = db();

foreach (['evaluaciones', 'stage_history', 'application_details', 'applications', 'visitas',
          'sesiones_panel', 'login_attempts'] as $t) {
    $pdo->exec("DELETE FROM $t");
}
$pdo->exec("DELETE FROM users WHERE username <> 'admin'");

$jurados = [
    ['ana', 'admin'],
    ['bruno', 'editor'],
    ['carla', 'editor'],
    ['diego', 'viewer'],           // mira pero no vota
];
$ins = $pdo->prepare("INSERT INTO users (username, password, role, must_change_password, created_at) VALUES (?, ?, ?, 0, datetime('now'))");
foreach ($jurados as [$nombre, $rol]) {
    $ins->execute([$nombre, password_hash('prueba1234', PASSWORD_DEFAULT), $rol]);
}
$ids = [];
foreach ($pdo->query("SELECT id, username FROM users")->fetchAll() as $u) {
    $ids[$u['username']] = (int) $u['id'];
}

$proyectos = [
    ['Cabalgatas Nahuelpan', 'Raiz', 'Pendiente', -12],
    ['Cervecería del Cordón', 'Acelera', 'En revisión', -10],
    ['Tejidos de la Meseta', 'Raiz', 'Preseleccionado', -9],
    ['Bicis Trevelin Tour', 'Acelera', 'Entrevista', -7],
    ['Dulces La Hoya', 'Raiz', 'Pendiente', -5],
    ['Taller de Ahumados', 'Acelera', 'Pendiente', -3],
    ['Refugio Los Alerces', 'Raiz', 'Aprobado', -2],
    ['Café de Especialidad 42', 'Acelera', 'Pendiente', 0],
];
// Las fechas se calculan en PHP y se guardan en UTC, que es lo que escribe
// CURRENT_TIMESTAMP en producción. SQLite acepta un modificador por argumento,
// no varios pegados en una cadena: datetime('now', '-12 days -3 hours') no
// devuelve una fecha corrida, devuelve NULL.
$insApp = $pdo->prepare(
    "INSERT INTO applications (name, contact_name, email, phone, program, stage, notes, submitted_at)
     VALUES (?, ?, ?, ?, ?, ?, '', ?)"
);
foreach ($proyectos as $i => [$nombre, $programa, $estado, $dias]) {
    $insApp->execute([
        $nombre,
        'Responsable ' . ($i + 1),
        'proyecto' . ($i + 1) . '@ejemplo.com',
        '2945 40' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
        $programa,
        $estado,
        gmdate('Y-m-d H:i:s', strtotime($dias . ' days -' . (3 + $i * 2) . ' hours')),
    ]);
}
$apps = $pdo->query('SELECT id, name FROM applications ORDER BY id')->fetchAll();

$insDet = $pdo->prepare('INSERT INTO application_details (application_id, field_key, field_value) VALUES (?, ?, ?)');
foreach ($apps as $a) {
    foreach (array_keys(ETIQUETAS_DETALLE) as $k) {
        $insDet->execute([$a['id'], $k, 'Respuesta de prueba para «' . $a['name'] . '» en el campo ' . $k . '.']);
    }
}

/** Carga el voto de un jurado como lo haría el panel. */
function votar(PDO $pdo, int $app, int $user, string $username, array $puntajes, string $comentario, bool $abstencion = false): void
{
    $campos = array_keys(CRITERIOS);
    $lista = implode(', ', $campos);
    $marcas = implode(', ', array_fill(0, count($campos), '?'));
    $stmt = $pdo->prepare(
        "INSERT INTO evaluaciones (application_id, user_id, username, $lista, comentario, abstencion, created_at, updated_at)
         VALUES (?, ?, ?, $marcas, ?, ?, datetime('now'), datetime('now'))"
    );
    $valores = [$app, $user, $username];
    foreach ($campos as $i => $c) {
        $valores[] = $abstencion ? 0 : ($puntajes[$i] ?? 0);
    }
    $valores[] = $comentario;
    $valores[] = $abstencion ? 1 : 0;
    $stmt->execute($valores);
}

// El reparto está armado a mano para que cada caso del panel tenga un ejemplo.
$a = array_column($apps, 'id');
votar($pdo, $a[0], $ids['ana'], 'ana', [5, 4, 4, 3, 2], 'Producto listo. La familia ya recibe visitas, falta precio y ficha.');
votar($pdo, $a[0], $ids['bruno'], 'bruno', [4, 4, 5, 3, 2], 'De acuerdo. El diferencial es real y no lo tiene nadie más.');
votar($pdo, $a[0], $ids['carla'], 'carla', [5, 5, 4, 4, 3], 'La más sólida que vi.');

// Disenso fuerte: mismo proyecto, lecturas opuestas.
votar($pdo, $a[1], $ids['ana'], 'ana', [5, 4, 4, 5, 3], 'Está a un paso de vender. Le falta muy poco.');
votar($pdo, $a[1], $ids['bruno'], 'bruno', [1, 2, 1, 2, 1], 'No lo veo. Ya tiene canal propio y no necesita el programa.');

// Abstención por conflicto de interés.
votar($pdo, $a[2], $ids['carla'], 'carla', [], 'Es prima mía. Me abstengo para no condicionar al resto.', true);
votar($pdo, $a[2], $ids['ana'], 'ana', [4, 5, 3, 3, 5], 'El producto físico acá es lo más fuerte.');

// Un solo voto, para probar que no se muestre como si estuviera cerrado.
votar($pdo, $a[3], $ids['bruno'], 'bruno', [3, 3, 3, 2, 1], 'Correcto pero sin nada propio todavía.');

// Jurado completo con puntajes bajos.
votar($pdo, $a[6], $ids['ana'], 'ana', [2, 2, 2, 1, 1], 'Muy verde.');
votar($pdo, $a[6], $ids['bruno'], 'bruno', [2, 1, 2, 2, 1], 'Coincido.');
votar($pdo, $a[6], $ids['carla'], 'carla', [3, 2, 2, 2, 1], 'Le daría otra vuelta el año que viene.');

// Historial de etapas.
$insH = $pdo->prepare("INSERT INTO stage_history (application_id, user_id, username, stage_from, stage_to, created_at) VALUES (?, ?, ?, ?, ?, ?)");
$insH->execute([$a[1], $ids['ana'], 'ana', 'Pendiente', 'En revisión', gmdate('Y-m-d H:i:s', strtotime('-4 days'))]);
$insH->execute([$a[2], $ids['ana'], 'ana', 'Pendiente', 'En revisión', gmdate('Y-m-d H:i:s', strtotime('-6 days'))]);
$insH->execute([$a[2], $ids['ana'], 'ana', 'En revisión', 'Preseleccionado', gmdate('Y-m-d H:i:s', strtotime('-2 days'))]);

// Visitas: 45 días hacia atrás, con más tráfico cerca del cierre y una curva
// horaria realista para el día de hoy.
$rutas = ['/index.php' => 60, '/inscribirse.php' => 25, '/media-kit.php' => 8, '/terminos.php' => 4, '/index.php#preguntas' => 3];
$origenes = ['' => 40, 'instagram.com' => 25, 'google' => 15, 'facebook.com' => 10, 'whatsapp' => 10];
$insV = $pdo->prepare(
    "INSERT INTO visitas (ruta, origen, dispositivo, pais, visitante, segundos, profundidad, paso_form, creada_at)
     VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?)"
);

function elegir(array $pesos): string
{
    $total = array_sum($pesos);
    $r = random_int(1, $total);
    foreach ($pesos as $k => $p) {
        $r -= $p;
        if ($r <= 0) return (string) $k;
    }
    return (string) array_key_first($pesos);
}

// Reparto por hora: nadie mira una convocatoria municipal a las 4 de la mañana.
$curva = [0=>2,1=>1,2=>1,3=>0,4=>0,5=>0,6=>1,7=>3,8=>6,9=>9,10=>11,11=>10,12=>7,
          13=>5,14=>6,15=>8,16=>9,17=>10,18=>11,19=>12,20=>13,21=>12,22=>8,23=>4];

$pdo->beginTransaction();
for ($dia = 45; $dia >= 0; $dia--) {
    $base = (int) round(18 + (45 - $dia) * 1.1);          // sube hacia el cierre
    $visitas = max(3, $base + random_int(-6, 8));
    for ($v = 0; $v < $visitas; $v++) {
        $hora = (int) elegir($curva);
        $minuto = random_int(0, 59);
        $ruta = elegir($rutas);
        $paso = $ruta === '/inscribirse.php' ? random_int(1, 6) : null;
        $insV->execute([
            $ruta,
            elegir($origenes),
            elegir(['celular' => 68, 'escritorio' => 27, 'tablet' => 5]),
            substr(hash('sha256', "visitante-$dia-" . random_int(1, max(4, (int) ($visitas * 0.7)))), 0, 32),
            random_int(8, 260),
            random_int(15, 100),
            $paso,
            // La hora se elige en hora de Esquel y se guarda en UTC: la curva
            // horaria del panel hace el camino inverso.
            gmdate('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime("-{$dia} days")) . " {$hora}:{$minuto}:00")),
        ]);
    }
}
$pdo->commit();
// Las visitas se escribieron como "hace N días a tal hora": para el día de hoy
// eso puede caer en el futuro, y una visita con fecha de mañana no existe.
$pdo->exec("DELETE FROM visitas WHERE creada_at > datetime('now')");

echo "Base de prueba lista.\n";
echo "  usuarios: admin/admin123 (cambio obligatorio) · ana(admin) · bruno(editor) · carla(editor) · diego(viewer), todos con prueba1234\n";
echo "  postulaciones: " . count($apps) . "\n";
echo "  votos: " . $pdo->query('SELECT COUNT(*) FROM evaluaciones')->fetchColumn() . "\n";
echo "  visitas: " . $pdo->query('SELECT COUNT(*) FROM visitas')->fetchColumn() . "\n";

// Ingresos al panel: cada quien con su horario, para que el gráfico de momentos
// muestre un patrón y no una nube uniforme.
$habitos = [
    'ana'   => ['dias' => 22, 'horas' => [9, 10, 11, 15, 16, 17], 'min' => 900,  'max' => 3400],
    'bruno' => ['dias' => 14, 'horas' => [20, 21, 22, 23],        'min' => 400,  'max' => 1800],
    'carla' => ['dias' => 10, 'horas' => [7, 8, 13, 14],          'min' => 600,  'max' => 2200],
    'diego' => ['dias' => 4,  'horas' => [11, 18],                'min' => 120,  'max' => 700],
];
$insL = $pdo->prepare('INSERT INTO login_attempts (username, ip, ok, created_at) VALUES (?, ?, ?, ?)');
$insS = $pdo->prepare(
    'INSERT INTO sesiones_panel (user_id, username, inicio, ultima_actividad, pantallas, cerrada) VALUES (?, ?, ?, ?, ?, 1)'
);

$pdo->beginTransaction();
foreach ($habitos as $nombre => $h) {
    for ($i = 0; $i < $h['dias']; $i++) {
        $dia = random_int(0, 44);
        $hora = $h['horas'][array_rand($h['horas'])];
        $inicioLocal = date('Y-m-d', strtotime("-{$dia} days")) . sprintf(' %02d:%02d:00', $hora, random_int(0, 59));
        if (strtotime($inicioLocal) > time()) {
            continue;                       // un ingreso con fecha futura no existe
        }
        $inicioUtc = gmdate('Y-m-d H:i:s', strtotime($inicioLocal));
        $dur = random_int($h['min'], $h['max']);

        $insL->execute([$nombre, '181.0.0.' . random_int(2, 250), 1, $inicioUtc]);
        $insS->execute([
            $ids[$nombre], $nombre, $inicioUtc,
            gmdate('Y-m-d H:i:s', strtotime($inicioUtc) + $dur),
            max(2, (int) round($dur / 90)),
        ]);
    }
}
// Un par de intentos fallidos, que es lo que se quiere poder detectar.
foreach ([['bruno', 3], ['carla', 1]] as [$quien, $n]) {
    for ($i = 0; $i < $n; $i++) {
        $insL->execute([$quien, '181.0.0.' . random_int(2, 250), 0,
                        gmdate('Y-m-d H:i:s', strtotime('-' . random_int(1, 20) . ' days'))]);
    }
}
$pdo->commit();

echo "  ingresos al panel: " . $pdo->query('SELECT COUNT(*) FROM login_attempts WHERE ok = 1')->fetchColumn() . "\n";
