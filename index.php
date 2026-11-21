<?php
// index.php
declare(strict_types=1);
require_once __DIR__ . '/Repositorio.php';

$pdo  = Conexion::obtener();
$repo = new ClubDeportivoRepositorio($pdo);

$errores = [];
$mensajeExito = '';

$e = fn($valor) => htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');

// Procesar envíos por método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        // --- CASO 1: REGISTRAR CLUB ---
        if ($accion === 'registrar_club') {
            $nombre    = trim($_POST['nombre'] ?? '');
            $deporte   = trim($_POST['deporte'] ?? '');
            $ubicacion = trim($_POST['ubicacion'] ?? '');
            $fecha     = trim($_POST['fecha_fundacion'] ?? '');

            if ($nombre === '' || $deporte === '' || $ubicacion === '' || $fecha === '') {
                $errores[] = 'Todos los campos del club son obligatorios.';
            }

            if (!$errores) {
                $nuevoClub = new Club(null, $nombre, $deporte, $ubicacion, $fecha);
                $repo->registrarClub($nuevoClub);
                header('Location: index.php?ok=club'); // Patrón Post/Redirect/Get
                exit;
            }
        }

        // --- CASO 2: REGISTRAR MIEMBRO ---
        if ($accion === 'registrar_miembro') {
            $nombre   = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $telefono = trim($_POST['telefono'] ?? '');
            $clubId   = !empty($_POST['club_id']) ? (int) $_POST['club_id'] : null;

            if ($nombre === '' || $apellido === '') {
                $errores[] = 'El nombre y el apellido son obligatorios.';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El formato del correo electrónico no es válido.';
            }
            if (!preg_match('/^[0-9+\-\s]{7,15}$/', $telefono)) {
                $errores[] = 'El teléfono debe contener entre 7 y 15 dígitos.';
            }

            if (!$errores) {
                $nuevoMiembro = new Miembro(null, $nombre, $apellido, $email, $telefono, $clubId);
                $repo->registrarMiembro($nuevoMiembro);
                header('Location: index.php?ok=miembro'); // Patrón Post/Redirect/Get
                exit;
            }
        }
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        $errores[] = ($ex->getCode() === '23000')
            ? 'El correo electrónico ya está registrado en el sistema.'
            : 'Ocurrió un error al guardar en la base de datos.';
    }
}

// Mensajes tras redirección GET
if (isset($_GET['ok'])) {
    $mensajeExito = ($_GET['ok'] === 'club')
        ? 'Club deportivo registrado correctamente.'
        : 'Miembro registrado correctamente.';
}

// Consultar listas actualizadas para mostrar en las tablas
$listaClubes   = $repo->listarClubes();
$listaMiembros = $repo->listarMiembros();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Gestión de Clubes Deportivos</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #ecfdf5; margin: 25px; color: #1f2937; }
        .contenedor { max-width: 1050px; margin: auto; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .card { background: #ffffff; padding: 24px; border-radius: 14px; box-shadow: 0 4px 15px rgba(5, 150, 105, 0.08); margin-bottom: 24px; border-left: 6px solid #059669; }
        h1 { color: #065f46; margin-bottom: 20px; }
        h2 { margin-top: 0; color: #047857; font-size: 1.35em; }
        label { display: block; margin-top: 10px; font-weight: 600; font-size: 0.9em; color: #374151; }
        input, select { width: 100%; padding: 9px 12px; margin-top: 4px; box-sizing: border-box; border: 1px solid #d1fae5; background: #f9fafb; border-radius: 6px; }
        input:focus, select:focus { outline: none; border-color: #059669; background: #fff; }
        button { margin-top: 16px; background: #059669; color: #fff; border: none; padding: 11px 15px; border-radius: 6px; cursor: pointer; width: 100%; font-weight: bold; }
        button:hover { background: #047857; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #059669; color: #ffffff; padding: 12px; text-align: left; font-size: 0.88em; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 0.92em; }
        tr:nth-child(even) { background: #f0fdf4; }
        tr:hover { background: #d1fae5; }
        .alerta-error { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 15px; }
        .alerta-exito { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="contenedor">
    
    <h1>
    <img src="logo.jpg" alt="Logo" style="height: 40px; vertical-align: middle; margin-right: 10px;">
    Sistema de Gestión de Clubes Deportivos
    </h1>

    <?php if ($errores): ?>
        <div class="alerta-error">
            <?= implode('<br>', array_map($e, $errores)) ?>
        </div>
    <?php endif; ?>

    <?php if ($mensajeExito): ?>
        <div class="alerta-exito"><?= $e($mensajeExito) ?></div>
    <?php endif; ?>

    <!-- FORMULARIOS DE REGISTRO -->
    <div class="grid">
        <!-- Formulario 1: Registrar Club -->
        <div class="card">
            <h2>Registrar un Nuevo Club</h2>
            <form method="post" action="index.php">
                <input type="hidden" name="accion" value="registrar_club">
                
                <label>Nombre del Club:</label>
                <input type="text" name="nombre" required maxlength="100" placeholder="Ej. Club Deportivo Quito">

                <label>Deporte:</label>
                <input type="text" name="deporte" required maxlength="50" placeholder="Ej. Fútbol, Natación, Tenis">

                <label>Ubicación:</label>
                <input type="text" name="ubicacion" required maxlength="120" placeholder="Ej. Av. Olímpica y Central">

                <label>Fecha de Fundación:</label>
                <input type="date" name="fecha_fundacion" required>

                <button type="submit">Guardar Club</button>
            </form>
        </div>

        <!-- Formulario 2: Registrar Miembro -->
        <div class="card">
            <h2>Registrar un Nuevo Miembro</h2>
            <form method="post" action="index.php">
                <input type="hidden" name="accion" value="registrar_miembro">

                <label>Nombre:</label>
                <input type="text" name="nombre" required maxlength="80">

                <label>Apellido:</label>
                <input type="text" name="apellido" required maxlength="80">

                <label>Email:</label>
                <input type="email" name="email" required maxlength="120" placeholder="ejemplo@correo.com">

                <label>Teléfono:</label>
                <input type="text" name="telefono" required maxlength="20" placeholder="0991234567">

                <label>Club Deportivo (Opcional):</label>
                <select name="club_id">
                    <option value="">-- Seleccionar Club --</option>
                    <?php foreach ($listaClubes as $c): ?>
                        <option value="<?= (int) $c['club_id'] ?>">
                            <?= $e($c['nombre']) ?> (<?= $e($c['deporte']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Guardar Miembro</button>
            </form>
        </div>
    </div>

    <!-- TABLA 1: LISTADO DE CLUBES -->
    <div class="card">
        <h2>Listado de Clubes Registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Deporte</th>
                    <th>Ubicación</th>
                    <th>Fecha de Fundación</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaClubes as $c): ?>
                    <tr>
                        <td><?= (int) $c['club_id'] ?></td>
                        <td><?= $e($c['nombre']) ?></td>
                        <td><?= $e($c['deporte']) ?></td>
                        <td><?= $e($c['ubicacion']) ?></td>
                        <td><?= date('d/m/Y', strtotime($c['fecha_fundacion'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$listaClubes): ?>
                    <tr><td colspan="5">No hay clubes registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- TABLA 2: LISTADO DE MIEMBROS -->
    <div class="card">
        <h2>Listado de Miembros Registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Club Asignado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listaMiembros as $m): ?>
                    <tr>
                        <td><?= (int) $m['miembro_id'] ?></td>
                        <td><?= $e($m['nombre'] . ' ' . $m['apellido']) ?></td>
                        <td><?= $e($m['email']) ?></td>
                        <td><?= $e($m['telefono']) ?></td>
                        <td><?= $e($m['nombre_club'] ?? 'Sin club asignado') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$listaMiembros): ?>
                    <tr><td colspan="5">No hay miembros registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>