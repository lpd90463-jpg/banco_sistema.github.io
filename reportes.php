<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.html");
    exit();
}

// Variables del filtro
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$id_cuenta = $_POST['id_cuenta'] ?? '';

// Construir filtro base
$where = "1=1";
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $where .= " AND fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
}
if (!empty($id_cuenta)) {
    $where .= " AND (id_cuenta_origen = $id_cuenta OR id_cuenta_destino = $id_cuenta)";
}

// Consultar cuentas para el filtro
$cuentas = $conexion->query("SELECT id, numero_cuenta FROM cuentas");

// Consulta principal (usa alias)
$query = "
    SELECT 
        t.*, 
        co.numero_cuenta AS cuenta_origen,
        cd.numero_cuenta AS cuenta_destino
    FROM transacciones t
    LEFT JOIN cuentas co ON t.id_cuenta_origen = co.id
    LEFT JOIN cuentas cd ON t.id_cuenta_destino = cd.id
    WHERE 1=1
";

if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $query .= " AND t.fecha BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
}
if (!empty($id_cuenta)) {
    $query .= " AND (t.id_cuenta_origen = $id_cuenta OR t.id_cuenta_destino = $id_cuenta)";
}

$query .= " ORDER BY t.fecha DESC";
$transacciones = $conexion->query($query);

// Calcular totales (sin alias)
$total_depositos = 0;
$total_retiros = 0;
$total_transferencias = 0;

$result = $conexion->query("SELECT tipo, SUM(monto) AS total FROM transacciones WHERE $where GROUP BY tipo");
while ($r = $result->fetch_assoc()) {
    if ($r['tipo'] == 'Depósito') $total_depositos = $r['total'];
    if ($r['tipo'] == 'Retiro') $total_retiros = $r['total'];
    if ($r['tipo'] == 'Transferencia') $total_transferencias = $r['total'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>📊 Reportes - Banco Fassil S.A.</title>
    <link rel="stylesheet" href="crud.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="container">
    <h1>📊 Reportes de Transacciones</h1>

    <form method="POST" class="filtros">
        <label>Desde:</label>
        <input type="date" name="fecha_inicio" value="<?= $fecha_inicio ?>">
        <label>Hasta:</label>
        <input type="date" name="fecha_fin" value="<?= $fecha_fin ?>">

        <select name="id_cuenta">
            <option value="">-- Todas las cuentas --</option>
            <?php while ($c = $cuentas->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>" <?= ($id_cuenta == $c['id']) ? 'selected' : '' ?>>
                    <?= $c['numero_cuenta'] ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">🔍 Filtrar</button>
    </form>

    <h2>📄 Resultados</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Cuenta Origen</th>
            <th>Cuenta Destino</th>
            <th>Tipo</th>
            <th>Monto</th>
            <th>Fecha</th>
            <th>Descripción</th>
        </tr>
        <?php if ($transacciones->num_rows > 0): ?>
            <?php while ($t = $transacciones->fetch_assoc()): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><?= $t['cuenta_origen'] ?? '-' ?></td>
                    <td><?= $t['cuenta_destino'] ?? '-' ?></td>
                    <td><?= $t['tipo'] ?></td>
                    <td><?= number_format($t['monto'], 2) ?></td>
                    <td><?= $t['fecha'] ?></td>
                    <td><?= $t['descripcion'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No se encontraron transacciones.</td></tr>
        <?php endif; ?>
    </table>

    <h2>💰 Totales</h2>
    <div class="tarjetas">
        <div class="card">
            <h3>Depósitos</h3>
            <p>Bs <?= number_format($total_depositos, 2) ?></p>
        </div>
        <div class="card">
            <h3>Retiros</h3>
            <p>Bs <?= number_format($total_retiros, 2) ?></p>
        </div>
        <div class="card">
            <h3>Transferencias</h3>
            <p>Bs <?= number_format($total_transferencias, 2) ?></p>
        </div>
    </div>

    <a href="panel_admin.php" class="volver">⬅️ Volver al panel</a>
</div>
</body>
</html>
