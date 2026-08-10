<?php
/**
 * Los que dejaron los datos para la próxima cohorte.
 *
 * Es la lista con la que se abre la segunda convocatoria: gente que ya levantó
 * la mano. Vale más que cualquier campaña, así que tiene que poder bajarse
 * entera y sin vueltas el día que haya fecha.
 *
 * Solo el admin: son datos de contacto de personas que todavía no se
 * postularon a nada.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$u = requiere_rol('admin');
$pdo = db();

// --- exportación ----------------------------------------------------------
if (($_GET['export'] ?? '') === 'csv') {
    $filas = $pdo->query('SELECT * FROM interesados ORDER BY updated_at DESC')->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="interesados-esquel-lab-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");   // BOM, para que Excel lea bien los acentos
    fputcsv($out, ['Nombre', 'Correo', 'WhatsApp', 'Instagram', 'Línea', 'Qué contó', 'Por dónde llegó', 'Veces', 'Primera vez', 'Última vez']);
    foreach ($filas as $f) {
        fputcsv($out, [
            $f['nombre'], $f['email'], $f['telefono'],
            $f['instagram'] === '' ? '' : '@' . $f['instagram'],
            $f['linea'] === '' ? 'Todavía no sabe' : programa_info($f['linea'])['nombre'],
            $f['cuenta'], $f['origen'], $f['veces'],
            fecha_corta($f['created_at'], true), fecha_corta($f['updated_at'], true),
        ]);
    }
    fclose($out);
    exit;
}

$q = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT * FROM interesados';
$bind = [];
if ($q !== '') {
    $sql .= ' WHERE nombre LIKE ? OR email LIKE ? OR cuenta LIKE ? OR instagram LIKE ?';
    $like = '%' . $q . '%';
    $bind = [$like, $like, $like, $like];
}
$sql .= ' ORDER BY updated_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($bind);
$filas = $stmt->fetchAll();

$total = (int) $pdo->query('SELECT COUNT(*) FROM interesados')->fetchColumn();
$semana = (int) $pdo->query("SELECT COUNT(*) FROM interesados WHERE created_at > datetime('now','-7 days')")->fetchColumn();
$conTelefono = (int) $pdo->query("SELECT COUNT(*) FROM interesados WHERE telefono <> ''")->fetchColumn();

$porLinea = [];
foreach ($pdo->query('SELECT linea, COUNT(*) n FROM interesados GROUP BY linea') as $r) {
    $porLinea[$r['linea']] = (int) $r['n'];
}

$pageTitle = 'Interesados';
$nav = 'interesados';
require __DIR__ . '/_header.php';
?>

<div class="admin-topbar">
  <h1>Interesados en la próxima cohorte</h1>
  <span class="admin-count"><?= $total ?> <?= $total === 1 ? 'persona' : 'personas' ?></span>
</div>

<div class="admin-content">
  <div class="stats">
    <div class="stat"><span class="k">Total</span><span class="v"><?= $total ?></span></div>
    <div class="stat"><span class="k">Últimos 7 días</span><span class="v"><?= $semana ?></span></div>
    <div class="stat"><span class="k">Dejaron WhatsApp</span><span class="v"><?= $conTelefono ?></span></div>
    <?php foreach (PROGRAMAS as $slug => $p): ?>
      <div class="stat <?= $slug === 'Acelera' ? 'acelera' : 'raiz' ?>"><span class="k"><?= e($p['nombre']) ?></span><span class="v"><?= (int) ($porLinea[$slug] ?? 0) ?></span></div>
    <?php endforeach; ?>
    <div class="stat"><span class="k">Todavía no sabe</span><span class="v"><?= (int) ($porLinea[''] ?? 0) ?></span></div>
  </div>

  <form method="get" class="filters">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por nombre, correo, Instagram o lo que contó…">
    <button type="submit" class="btn btn-secondary btn-sm">Buscar</button>
    <?php if ($q !== ''): ?><a href="interesados.php" class="clear">Limpiar</a><?php endif; ?>
    <a href="interesados.php?export=csv" class="btn btn-secondary btn-sm">Descargar CSV</a>
  </form>

  <?php if (!$filas): ?>
    <div class="empty-state">
      <?= $q !== ''
          ? 'No hay nadie que coincida con esa búsqueda.'
          : 'Todavía no se anotó nadie. Cuando alguien deje sus datos desde la home o desde la página avisame, aparece acá.' ?>
    </div>
  <?php else: ?>
    <div class="panel" style="padding:0;overflow:hidden">
      <div class="table-scroll">
        <table class="crm-table">
          <thead>
            <tr>
              <th>Persona</th><th>Contacto</th><th>Línea</th><th>Qué contó</th><th class="nowrap">Se anotó</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filas as $f): ?>
              <tr>
                <td data-col="Persona">
                  <div><strong><?= e($f['nombre']) ?></strong></div>
                  <?php if ((int) $f['veces'] > 1): ?>
                    <div class="sub" title="Dejó los datos más de una vez">volvió <?= (int) $f['veces'] ?> veces</div>
                  <?php endif; ?>
                </td>
                <td data-col="Contacto">
                  <div><a href="mailto:<?= e($f['email']) ?>"><?= e($f['email']) ?></a></div>
                  <?php if ($f['telefono'] !== ''): ?>
                    <div class="sub"><?= e($f['telefono']) ?></div>
                  <?php endif; ?>
                  <?php if ($f['instagram'] !== ''): ?>
                    <div class="sub"><a href="https://instagram.com/<?= e($f['instagram']) ?>" target="_blank" rel="noopener noreferrer">@<?= e($f['instagram']) ?></a></div>
                  <?php endif; ?>
                </td>
                <td data-col="Línea">
                  <?php if ($f['linea'] === ''): ?>
                    <span class="sub">Todavía no sabe</span>
                  <?php else: $p = programa_info($f['linea']); ?>
                    <span class="chip" style="--c:<?= e($p['color']) ?>"><?= e($p['nombre']) ?></span>
                  <?php endif; ?>
                </td>
                <td data-col="Qué contó" class="celda-cuenta"><?= $f['cuenta'] === '' ? '<span class="sub">—</span>' : e($f['cuenta']) ?></td>
                <td class="nowrap sub" data-col="Se anotó"><?= e(fecha_corta($f['updated_at'], true)) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_footer.php'; ?>
