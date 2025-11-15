<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.html");
    exit();
}

// Agregar nueva cuenta
if (isset($_POST['agregar'])) {
    $id_cliente = $_POST['id_cliente'];
    $numero_cuenta = $_POST['numero_cuenta'];
    $tipo_cuenta = $_POST['tipo_cuenta'];
    $saldo = $_POST['saldo'];

    $sql = "INSERT INTO cuentas (id_cliente, numero_cuenta, tipo_cuenta, saldo) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("issd", $id_cliente, $numero_cuenta, $tipo_cuenta, $saldo);
    $stmt->execute();
    $stmt->close();

    $_SESSION['mensaje'] = "Cuenta creada exitosamente 💳";
    header("Location: cuentas.php");
    exit();
}

// Eliminar cuenta
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $conexion->query("DELETE FROM cuentas WHERE id=$id");
    $_SESSION['mensaje'] = "Cuenta eliminada 🗑️";
    header("Location: cuentas.php");
    exit();
}

// Obtener clientes y cuentas
$clientes = $conexion->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
$cuentas = $conexion->query("SELECT c.*, cl.nombre AS cliente FROM cuentas c INNER JOIN clientes cl ON c.id_cliente = cl.id ORDER BY c.id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Cuentas</title>
  <link rel="stylesheet" href="crud.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <div class="container">
    <h1>💳 Gestión de Cuentas</h1>

    <form method="POST" class="formulario">
      <h3>Agregar Nueva Cuenta</h3>
      <label>Cliente:</label>
      <select name="id_cliente" required>
        <option value="">Seleccione un cliente</option>
        <?php while ($c = $clientes->fetch_assoc()) { ?>
          <option value="<?= $c['id'] ?>"><?= $c['nombre'] ?></option>
        <?php } ?>
      </select>

      <input type="text" name="numero_cuenta" placeholder="Número de cuenta" required>
      <select name="tipo_cuenta" required>
        <option value="Ahorros">Ahorros</option>
        <option value="Corriente">Corriente</option>
      </select>
      <input type="number" step="0.01" name="saldo" placeholder="Saldo inicial" required>
      <button type="submit" name="agregar">➕ Crear Cuenta</button>
    </form>

    <h3>Lista de Cuentas</h3>
    <table>
      <tr>
        <th>ID</th>
        <th>Cliente</th>
        <th>Número</th>
        <th>Tipo</th>
        <th>Saldo</th>
        <th>Estado</th>
        <th>Fecha Apertura</th>
        <th>Acciones</th>
      </tr>
      <?php while ($fila = $cuentas->fetch_assoc()) { ?>
      <tr>
        <td><?= $fila['id'] ?></td>
        <td><?= $fila['cliente'] ?></td>
        <td><?= $fila['numero_cuenta'] ?></td>
        <td><?= $fila['tipo_cuenta'] ?></td>
        <td><?= number_format($fila['saldo'], 2) ?></td>
        <td><?= $fila['estado'] ?></td>
        <td><?= $fila['fecha_apertura'] ?></td>
        <td>
          <a href="cuentas.php?eliminar=<?= $fila['id'] ?>" onclick="return confirm('¿Eliminar cuenta?')">🗑️ Eliminar</a>
        </td>
      </tr>
      <?php } ?>
    </table>

    <a href="panel_admin.php" class="volver">⬅️ Volver al Panel</a>
  </div>

  <?php if (isset($_SESSION['mensaje'])): ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Éxito',
      text: '<?= $_SESSION['mensaje'] ?>',
      confirmButtonColor: '#00e0ff',
      background: '#1a1a1a',
      color: '#fff'
    });
  </script>
  <?php unset($_SESSION['mensaje']); endif; ?>
</body>
</html>
