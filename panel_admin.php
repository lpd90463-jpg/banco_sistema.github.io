<?php
session_start();
include("conexion.php");

// Verificar sesión
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.html");
    exit();
}

// ============================
// 📊 CONSULTAS DINÁMICAS
// ============================

// Total de clientes
$total_clientes = 0;
$result_clientes = $conexion->query("SELECT COUNT(*) AS total FROM clientes");
if ($result_clientes) {
    $total_clientes = $result_clientes->fetch_assoc()['total'];
}

// Total de cuentas activas
$total_cuentas = 0;
$result_cuentas = $conexion->query("SELECT COUNT(*) AS total FROM cuentas WHERE estado = 'Activa'");
if ($result_cuentas) {
    $total_cuentas = $result_cuentas->fetch_assoc()['total'];
}

// Total de transacciones de hoy
$total_transacciones = 0;
$hoy = date("Y-m-d");
$result_trans = $conexion->query("SELECT COUNT(*) AS total FROM transacciones WHERE DATE(fecha) = '$hoy'");
if ($result_trans) {
    $total_transacciones = $result_trans->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Administrador - Banco Fassil S.A.</title>
  <link rel="stylesheet" href="panel.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <aside class="sidebar">
    <div class="logo">
      <img src="logo.jpg" alt="Logo Banco Fassil">
      <h2>Admin Fassil</h2>
    </div>
    <nav>
      <a href="cuentas.php">🏦 Cuentas</a>
      <a href="clientes.php">👥 Clientes</a>
      <a href="transacciones.php">💸 Transacciones</a>
      <a href="reportes.php">📄 Reportes</a>
      <a href="logout.php">🚪 Cerrar sesión</a>
    </nav>
  </aside>

  <main class="contenido">
    <h1>Panel de Control del Administrador</h1>
    <p>Bienvenido al sistema de gestión bancaria del <strong>Banco Fassil S.A.</strong></p>
    
    <div class="tarjetas">
      <div class="card">
        <h3>Total de Clientes</h3>
        <p><?= $total_clientes ?></p>
      </div>
      <div class="card">
        <h3>Cuentas Activas</h3>
        <p><?= $total_cuentas ?></p>
      </div>
      <div class="card">
        <h3>Transacciones Hoy</h3>
        <p><?= $total_transacciones ?></p>
      </div>
    </div>
  </main>
</body>
</html>
