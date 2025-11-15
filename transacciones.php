<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.html");
    exit();
}

// Registrar transacción
if (isset($_POST['registrar'])) {
    $tipo = $_POST['tipo'];
    $monto = floatval($_POST['monto']);
    $descripcion = $_POST['descripcion'];

    // Verificar que el monto sea válido
    if ($monto <= 0) {
        echo "<script>alert('⚠️ El monto debe ser mayor a 0.'); window.location='transacciones.php';</script>";
        exit();
    }

    // Cuentas origen y destino
    $id_origen = !empty($_POST['id_cuenta_origen']) ? intval($_POST['id_cuenta_origen']) : null;
    $id_destino = !empty($_POST['id_cuenta_destino']) ? intval($_POST['id_cuenta_destino']) : null;

    // Validaciones según el tipo
    if ($tipo == "Depósito" && $id_destino === null) {
        echo "<script>alert('Debe seleccionar una cuenta destino para el depósito.'); window.location='transacciones.php';</script>";
        exit();
    }

    if ($tipo == "Retiro" && $id_origen === null) {
        echo "<script>alert('Debe seleccionar una cuenta origen para el retiro.'); window.location='transacciones.php';</script>";
        exit();
    }

    // Manejo de saldos
    if ($tipo == "Depósito") {
        // Sumar al saldo de destino
        $conexion->query("UPDATE cuentas SET saldo = saldo + $monto WHERE id = $id_destino");
    } elseif ($tipo == "Retiro") {
        // Verificar saldo suficiente
        $saldo_origen = $conexion->query("SELECT saldo FROM cuentas WHERE id = $id_origen")->fetch_assoc()['saldo'];
        if ($saldo_origen < $monto) {
            echo "<script>alert('❌ Fondos insuficientes en la cuenta de origen.'); window.location='transacciones.php';</script>";
            exit();
        }
        $conexion->query("UPDATE cuentas SET saldo = saldo - $monto WHERE id = $id_origen");
    } elseif ($tipo == "Transferencia") {
        // Verificar saldo suficiente y actualizar ambas cuentas
        $saldo_origen = $conexion->query("SELECT saldo FROM cuentas WHERE id = $id_origen")->fetch_assoc()['saldo'];
        if ($saldo_origen < $monto) {
            echo "<script>alert('❌ Fondos insuficientes para transferir.'); window.location='transacciones.php';</script>";
            exit();
        }
        $conexion->query("UPDATE cuentas SET saldo = saldo - $monto WHERE id = $id_origen");
        $conexion->query("UPDATE cuentas SET saldo = saldo + $monto WHERE id = $id_destino");
    }

    // Insertar transacción (con manejo correcto de nulls)
    $stmt = $conexion->prepare("INSERT INTO transacciones (id_cuenta_origen, id_cuenta_destino, tipo, monto, descripcion) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $id_origen, $id_destino, $tipo, $monto, $descripcion);
    $stmt->execute();

    echo "<script>alert('✅ Transacción registrada correctamente.'); window.location='transacciones.php';</script>";
    exit();
}

// Consultar cuentas y transacciones
$cuentas = $conexion->query("SELECT id, numero_cuenta FROM cuentas");
$transacciones = $conexion->query("SELECT * FROM transacciones ORDER BY fecha DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Transacciones - Banco Fassil S.A.</title>
  <link rel="stylesheet" href="crud.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class="container">
  <h1>💰 Transacciones</h1>

  <form method="POST">
    <label>Tipo de Transacción:</label>
    <select name="tipo" id="tipo" onchange="toggleCuentas()" required>
      <option value="">-- Seleccione --</option>
      <option value="Depósito">Depósito</option>
      <option value="Retiro">Retiro</option>
      <option value="Transferencia">Transferencia</option>
    </select>

    <div id="cuenta_origen">
      <label>Cuenta Origen:</label>
      <select name="id_cuenta_origen">
        <option value="">-- Seleccione --</option>
        <?php while ($c = $cuentas->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>"><?= $c['numero_cuenta'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <?php 
      // Reiniciar el puntero para volver a listar cuentas
      $cuentas = $conexion->query("SELECT id, numero_cuenta FROM cuentas");
    ?>

    <div id="cuenta_destino">
      <label>Cuenta Destino:</label>
      <select name="id_cuenta_destino">
        <option value="">-- Seleccione --</option>
        <?php while ($c = $cuentas->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>"><?= $c['numero_cuenta'] ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <input type="number" name="monto" step="0.01" placeholder="Monto" required>
    <input type="text" name="descripcion" placeholder="Descripción (opcional)">
    <button type="submit" name="registrar">💾 Registrar</button>
  </form>

  <h2>📄 Historial de Transacciones</h2>
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
    <?php while ($t = $transacciones->fetch_assoc()): ?>
      <tr>
        <td><?= $t['id'] ?></td>
        <td><?= $t['id_cuenta_origen'] ?: '—' ?></td>
        <td><?= $t['id_cuenta_destino'] ?: '—' ?></td>
        <td><?= $t['tipo'] ?></td>
        <td><?= number_format($t['monto'], 2) ?></td>
        <td><?= $t['fecha'] ?></td>
        <td><?= htmlspecialchars($t['descripcion']) ?></td>
      </tr>
    <?php endwhile; ?>
  </table>

  <a href="panel_admin.php" class="volver">⬅️ Volver al panel</a>
</div>

<script>
function toggleCuentas() {
  const tipo = document.getElementById('tipo').value;
  document.getElementById('cuenta_origen').style.display =
      (tipo === 'Retiro' || tipo === 'Transferencia') ? 'block' : 'none';
  document.getElementById('cuenta_destino').style.display =
      (tipo === 'Depósito' || tipo === 'Transferencia') ? 'block' : 'none';
}
toggleCuentas();
</script>
</body>
</html>
