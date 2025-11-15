<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.html");
    exit();
}

$id = $_GET['id'];
$result = $conexion->query("SELECT * FROM clientes WHERE id=$id");
$cliente = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Cliente</title>
  <link rel="stylesheet" href="crud.css">
</head>
<body>
  <div class="container">
    <h1>✏️ Editar Cliente</h1>
    <form method="POST" action="clientes.php">
      <input type="hidden" name="id" value="<?= $cliente['id'] ?>">
      <input type="text" name="nombre" value="<?= $cliente['nombre'] ?>" required>
      <input type="email" name="email" value="<?= $cliente['email'] ?>" required>
      <input type="text" name="telefono" value="<?= $cliente['telefono'] ?>">
      <input type="text" name="direccion" value="<?= $cliente['direccion'] ?>">
      <button type="submit" name="editar">💾 Guardar Cambios</button>
    </form>
    <a href="clientes.php" class="volver">⬅️ Volver</a>
  </div>
</body>
</html>
