<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.html");
    exit();
}

// Insertar nuevo cliente
if (isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];

    $sql = "INSERT INTO clientes (nombre, email, telefono, direccion) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssss", $nombre, $email, $telefono, $direccion);
    $stmt->execute();
    $stmt->close();

    $_SESSION['mensaje'] = "Cliente agregado exitosamente ✅";
    header("Location: clientes.php");
    exit();
}

// Eliminar cliente
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $conexion->query("DELETE FROM clientes WHERE id=$id");
    $_SESSION['mensaje'] = "Cliente eliminado correctamente 🗑️";
    header("Location: clientes.php");
    exit();
}

// Editar cliente
if (isset($_POST['editar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];

    $sql = "UPDATE clientes SET nombre=?, email=?, telefono=?, direccion=? WHERE id=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssi", $nombre, $email, $telefono, $direccion, $id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['mensaje'] = "Cliente actualizado correctamente 💾";
    header("Location: clientes.php");
    exit();
}

// Obtener todos los clientes
$result = $conexion->query("SELECT * FROM clientes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Clientes - Banco Fassil</title>
  <link rel="stylesheet" href="crud.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <div class="container">
    <h1>👥 Gestión de Clientes</h1>

    <form method="POST" class="formulario">
      <h3>Agregar Nuevo Cliente</h3>
      <input type="text" name="nombre" placeholder="Nombre completo" required>
      <input type="email" name="email" placeholder="Correo electrónico" required>
      <input type="text" name="telefono" placeholder="Teléfono">
      <input type="text" name="direccion" placeholder="Dirección">
      <button type="submit" name="agregar">➕ Agregar</button>
    </form>

    <h3>Lista de Clientes</h3>
    <table>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Email</th>
        <th>Teléfono</th>
        <th>Dirección</th>
        <th>Fecha Registro</th>
        <th>Acciones</th>
      </tr>
      <?php while ($fila = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $fila['id'] ?></td>
        <td><?= $fila['nombre'] ?></td>
        <td><?= $fila['email'] ?></td>
        <td><?= $fila['telefono'] ?></td>
        <td><?= $fila['direccion'] ?></td>
        <td><?= $fila['fecha_registro'] ?></td>
        <td>
          <a href="editar_cliente.php?id=<?= $fila['id'] ?>">✏️ Editar</a> |
          <a href="clientes.php?eliminar=<?= $fila['id'] ?>" onclick="return confirm('¿Seguro que deseas eliminar este cliente?')">🗑️ Eliminar</a>
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
