<?php
session_start();
include("conexion.php");

// Verificar sesión
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'cliente') {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id'];

// Obtener datos del cliente
$cliente = $conexion->query("SELECT * FROM usuarios WHERE id = $id_usuario")->fetch_assoc();
$cuentas = $conexion->query("SELECT * FROM cuentas WHERE id_cliente = $id_usuario");

// Movimientos del cliente
$transacciones = $conexion->query("
    SELECT t.*, c.numero_cuenta AS cuenta_origen, cd.numero_cuenta AS cuenta_destino
    FROM transacciones t
    LEFT JOIN cuentas c ON t.id_cuenta_origen = c.id
    LEFT JOIN cuentas cd ON t.id_cuenta_destino = cd.id
    WHERE c.id_cliente = $id_usuario
    ORDER BY t.fecha DESC
");

// Procesar nuevas transacciones
if(isset($_POST['transaccion'])){
    $id_origen = $_POST['id_cuenta_origen'];
    $tipo = $_POST['tipo'];
    $monto = floatval($_POST['monto']);
    $id_destino = $_POST['id_cuenta_destino'] ?? null;
    $descripcion = trim($_POST['descripcion']);

    $saldo_actual = $conexion->query("SELECT saldo FROM cuentas WHERE id=$id_origen")->fetch_assoc()['saldo'];

    if($tipo=='Retiro' && $monto > $saldo_actual){
        echo "<script>alert('❌ Fondos insuficientes'); window.location='panel_cliente.php';</script>";
        exit();
    }
    if($tipo=='Transferencia' && $monto > $saldo_actual){
        echo "<script>alert('❌ Fondos insuficientes'); window.location='panel_cliente.php';</script>";
        exit();
    }

    if($tipo=='Retiro'){
        $conexion->query("UPDATE cuentas SET saldo = saldo - $monto WHERE id=$id_origen");
    } elseif($tipo=='Deposito'){
        $conexion->query("UPDATE cuentas SET saldo = saldo + $monto WHERE id=$id_origen");
    } elseif($tipo=='Transferencia'){
        $conexion->query("UPDATE cuentas SET saldo = saldo - $monto WHERE id=$id_origen");
        $conexion->query("UPDATE cuentas SET saldo = saldo + $monto WHERE id=$id_destino");
    }

    $stmt = $conexion->prepare("INSERT INTO transacciones (id_cuenta_origen, id_cuenta_destino, tipo, monto, descripcion) VALUES (?,?,?,?,?)");
    $stmt->bind_param("iisss", $id_origen, $id_destino, $tipo, $monto, $descripcion);
    $stmt->execute();

    echo "<script>alert('✅ Transacción realizada'); window.location='panel_cliente.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel Cliente - Banco Fassil</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="panel.css">
<style>
body { font-family: Arial, sans-serif; background: #0a0a0a; color: #fff; margin:0; padding:0; display:flex; }
aside.sidebar { width: 250px; background: #001f2d; display:flex; flex-direction:column; }
aside.sidebar .logo { text-align:center; padding:20px; }
aside.sidebar .logo img { width:80px; border-radius:50%; margin-bottom:10px; }
aside.sidebar nav { display:flex; flex-direction:column; padding:10px; }
aside.sidebar nav button, aside.sidebar nav a { margin:10px 0; padding:15px; font-size:16px; color:#00ffff; background:#003b55; border:none; border-radius:10px; cursor:pointer; transition:all 0.3s; }
aside.sidebar nav button:hover, aside.sidebar nav a:hover { background:#00ffff; color:#001f2d; transform:translateX(5px); }
main.contenido { flex:1; padding:20px; overflow-y:auto; background:#0f1419; }
.dashboard-buttons { display:flex; flex-wrap:wrap; gap:20px; margin-bottom:30px; }
.dashboard-buttons button { flex:1 1 200px; padding:40px 20px; font-size:18px; border:none; border-radius:15px; background:#001f2d; color:#00ffff; cursor:pointer; box-shadow:0 0 10px #00ffff44; transition:all 0.3s; display:flex; flex-direction:column; align-items:center; }
.dashboard-buttons button:hover { background:#00ffff; color:#001f2d; transform:translateY(-5px); box-shadow:0 0 20px #00ffff88; }
.dashboard-buttons button i { font-size:40px; margin-bottom:10px; }
section { display:none; }
section.active { display:block; }
table { width:100%; border-collapse:collapse; margin-top:10px; }
th, td { padding:10px; text-align:center; border-bottom:1px solid #00ffff44; }
th { color:#00ffff; }
.deposito { color:#00ff00; font-weight:bold; }
.retiro { color:#ff3300; font-weight:bold; }
.transferencia { color:#ffff00; font-weight:bold; }
form { background:#001f2d; padding:20px; border-radius:10px; box-shadow:0 0 10px #00ffff44; margin-top:10px; }
form label { display:block; margin-top:10px; }
form input, form select { width:100%; padding:10px; margin-top:5px; border-radius:5px; border:none; }
form button { margin-top:15px; width:100%; padding:12px; background:#00ffff; color:#001f2d; font-weight:bold; border:none; border-radius:5px; cursor:pointer; transition:0.3s; }
form button:hover { background:#00ffcc; }
@media(max-width:768px){ .dashboard-buttons { flex-direction:column; } aside.sidebar { width:100%; flex-direction:row; overflow-x:auto; } aside.sidebar nav { flex-direction:row; } aside.sidebar nav button, aside.sidebar nav a { flex:1; text-align:center; } }
.tarjeta { background:#001f2d; padding:20px; border-radius:15px; margin-bottom:15px; box-shadow:0 0 10px #00ffff44; }
</style>
</head>
<body>
<aside class="sidebar">
<div class="logo">
    <img src="logo.jpg" alt="Logo Banco Fassil">
    <h2>Cliente Fassil</h2>
</div>
<nav>
    <button onclick="mostrarSeccion('mi-cuenta')">💰 Mi Cuenta</button>
    <button onclick="mostrarSeccion('movimientos')">📊 Movimientos</button>
    <button onclick="mostrarSeccion('transacciones')">💸 Transferencias</button>
    <button onclick="mostrarSeccion('configuracion')">⚙️ Configuración</button>
    <a href="logout.php">🚪 Cerrar sesión</a>
</nav>
</aside>

<main class="contenido">
<div class="dashboard-buttons">
    <button onclick="mostrarSeccion('mi-cuenta')"><i>💰</i>Mi Cuenta</button>
    <button onclick="mostrarSeccion('movimientos')"><i>📊</i>Movimientos</button>
    <button onclick="mostrarSeccion('transacciones')"><i>💸</i>Transferencias</button>
    <button onclick="mostrarSeccion('configuracion')"><i>⚙️</i>Configuración</button>
</div>

<!-- SECCIONES -->
<section id="mi-cuenta" class="active">
<h1>💰 Mis Cuentas</h1>
<?php
$cuentas2 = $conexion->query("SELECT * FROM cuentas WHERE id_cliente = $id_usuario");
while($cuenta = $cuentas2->fetch_assoc()):
?>
<div class="tarjeta">
    <h2><?= isset($cuenta['numero_cuenta']) ? $cuenta['numero_cuenta'] : '' ?></h2>
    <p>Saldo: Bs <?= isset($cuenta['saldo']) ? number_format($cuenta['saldo'],2) : '0.00' ?></p>
</div>
<?php endwhile; ?>
</section>

<section id="movimientos">
<h1>📊 Historial de Movimientos</h1>
<table>
<tr>
<th>Cuenta Origen</th>
<th>Cuenta Destino</th>
<th>Tipo</th>
<th>Monto</th>
<th>Descripción</th>
<th>Fecha</th>
</tr>
<?php while($t = $transacciones->fetch_assoc()): ?>
<tr>
    <td><?= isset($t['cuenta_origen']) ? $t['cuenta_origen'] : '-' ?></td>
    <td><?= isset($t['cuenta_destino']) ? $t['cuenta_destino'] : '-' ?></td>
    <td class="<?= isset($t['tipo']) ? strtolower($t['tipo']) : '' ?>"><?= isset($t['tipo']) ? $t['tipo'] : '-' ?></td>
    <td>Bs <?= isset($t['monto']) ? number_format($t['monto'],2) : '0.00' ?></td>
    <td><?= isset($t['descripcion']) ? $t['descripcion'] : '-' ?></td>
    <td><?= isset($t['fecha']) ? $t['fecha'] : '-' ?></td>
</tr>
<?php endwhile; ?>
</table>
</section>

<section id="transacciones">
<h1>💸 Nueva Transacción</h1>
<form method="POST">
<label>Cuenta de Origen:</label>
<select name="id_cuenta_origen" required>
<?php
$cuentas3 = $conexion->query("SELECT * FROM cuentas WHERE id_cliente = $id_usuario");
while($c = $cuentas3->fetch_assoc()):
?>
<option value="<?= isset($c['id']) ? $c['id'] : '' ?>"><?= isset($c['numero_cuenta']) ? $c['numero_cuenta'] : '' ?> - Saldo: Bs <?= isset($c['saldo']) ? number_format($c['saldo'],2) : '0.00' ?></option>
<?php endwhile; ?>
</select>

<label>Tipo de Transacción:</label>
<select name="tipo" required onchange="toggleDestino(this.value)">
<option value="Deposito">Depósito</option>
<option value="Retiro">Retiro</option>
<option value="Transferencia">Transferencia</option>
</select>

<div id="destino-container" style="display:none;">
<label>Cuenta Destino:</label>
<select name="id_cuenta_destino">
<?php
$cuentas_all = $conexion->query("SELECT * FROM cuentas WHERE id_cliente != $id_usuario");
while($c = $cuentas_all->fetch_assoc()):
?>
<option value="<?= isset($c['id']) ? $c['id'] : '' ?>"><?= isset($c['numero_cuenta']) ? $c['numero_cuenta'] : '' ?></option>
<?php endwhile; ?>
</select>
</div>

<label>Monto:</label>
<input type="number" name="monto" step="0.01" required>
<label>Descripción:</label>
<input type="text" name="descripcion">
<button type="submit" name="transaccion">💾 Realizar</button>
</form>
</section>

<section id="configuracion">
<h1>⚙️ Configuración</h1>
<form method="POST" action="actualizar_cliente.php">
<label>Nombre:</label>
<input type="text" name="nombre" value="<?= isset($cliente['nombre']) ? $cliente['nombre'] : '' ?>" required>

<label>Teléfono:</label>
<input type="text" name="telefono" value="<?= isset($cliente['telefono']) ? $cliente['telefono'] : '' ?>">

<label>Dirección:</label>
<input type="text" name="direccion" value="<?= isset($cliente['direccion']) ? $cliente['direccion'] : '' ?>">

<button type="submit" name="actualizar">💾 Actualizar Datos</button>
</form>
</section>

</main>

<script>
function mostrarSeccion(id) {
const secciones = document.querySelectorAll('main section');
secciones.forEach(s => s.classList.remove('active'));
document.getElementById(id).classList.add('active');
}
function toggleDestino(tipo) {
const container = document.getElementById('destino-container');
if(tipo==='Transferencia') container.style.display='block';
else container.style.display='none';
}
</script>
</body>
</html>
