<?php
include("conexion.php");
session_start();

// ======================
// REGISTRO DE CLIENTE
// ======================
if (isset($_POST['registrar'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $password = trim($_POST['password']); // sin hash para tarea

    // Verificar si el correo ya existe
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($resultado->num_rows > 0) {
        echo "<script>alert('⚠️ Este correo ya está registrado');</script>";
    } else {
        // Insertar nuevo usuario
        $rol = "cliente"; // Todos los que se registran son clientes
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $password, $rol);
        $stmt->execute();
        echo "<script>alert('✅ Registro exitoso, ahora inicia sesión');</script>";
    }
}

// ======================
// LOGIN
// ======================
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        if ($password === $usuario['password']) {
            $_SESSION['usuario'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['id'] = $usuario['id'];

            if ($usuario['rol'] === 'admin') {
                header("Location: panel_admin.php");
            } else {
                header("Location: panel_cliente.php");
            }
            exit();
        } else {
            echo "<script>alert('Contraseña incorrecta');</script>";
        }
    } else {
        echo "<script>alert('Correo no encontrado');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Banco Fassil S.A. - Registro / Login</title>
<link rel="stylesheet" href="stilos.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  .input-group { position: relative; display: flex; align-items: center; margin-bottom: 10px; }
  .input-group input { flex: 1; padding-right: 30px; }
  .toggle-pass { position: absolute; right: 5px; background: none; border: none; color: #00ffff; cursor: pointer; font-size: 0.9em; width: 25px; height: 25px; line-height: 25px; padding: 0; text-align: center; transition: 0.3s; }
  .toggle-pass:hover { color: #fff; text-shadow: 0 0 5px #00ffff; }
  .form-toggle { margin-top: 10px; text-align: center; cursor: pointer; color: #00ffff; }
  .form-toggle:hover { text-shadow: 0 0 5px #00ffff; }
</style>
</head>
<body>
<div class="background">
  <div class="circulo"></div>
  <div class="circulo"></div>
  <div class="circulo"></div>
</div>

<div class="login-container">
  <img src="logo.jpg" alt="Banco Fassil" class="logo">
  <h2 class="titulo">Banco Fassil S.A.</h2>

  <!-- FORM REGISTRO (visible al inicio) -->
  <form id="registro-form" method="POST" class="formulario" style="display:block;">
    <input type="text" name="nombre" placeholder="Nombre completo" required>
    <input type="email" name="email" placeholder="Correo" required>
    <input type="text" name="telefono" placeholder="Teléfono">
    <input type="text" name="direccion" placeholder="Dirección">
    <div class="input-group">
      <input type="password" name="password" placeholder="Contraseña" required>
      <button type="button" class="toggle-pass" onclick="togglePassword('registro-password', this)">👁️</button>
    </div>
    <button type="submit" name="registrar">Registrarse</button>
    <p class="form-toggle" onclick="switchForm()">¿Ya tienes cuenta? Inicia sesión</p>
  </form>

  <!-- FORM LOGIN (oculto inicialmente) -->
  <form id="login-form" method="POST" class="formulario" style="display:none;">
    <input type="email" name="email" placeholder="Correo" required>
    <div class="input-group">
      <input type="password" name="password" placeholder="Contraseña" required>
      <button type="button" class="toggle-pass" onclick="togglePassword('login-password', this)">👁️</button>
    </div>
    <button type="submit" name="login">Ingresar</button>
    <p class="form-toggle" onclick="switchForm()">¿No tienes cuenta? Regístrate</p>
  </form>
</div>

<script>
  // Efecto de fondo
  document.addEventListener('mousemove', e => {
    const circles = document.querySelectorAll('.circulo');
    circles.forEach((c, i) => {
      const x = (e.clientX / window.innerWidth) * 100;
      const y = (e.clientY / window.innerHeight) * 100;
      c.style.transform = `translate(${x / (i + 1)}px, ${y / (i + 1)}px)`;
    });
  });

  // Mostrar/ocultar contraseña
  function togglePassword(id, btn) {
    const input = btn.previousElementSibling;
    if (input.type === 'password') { input.type = 'text'; btn.textContent = '🙈'; }
    else { input.type = 'password'; btn.textContent = '👁️'; }
  }

  // Alternar entre login y registro
  function switchForm() {
    const loginForm = document.getElementById('login-form');
    const regForm = document.getElementById('registro-form');
    if (loginForm.style.display === 'none') {
      loginForm.style.display = 'block';
      regForm.style.display = 'none';
    } else {
      loginForm.style.display = 'none';
      regForm.style.display = 'block';
    }
  }
</script>
</body>
</html>
