<?php include("conexion.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Banco Fassil S.A. - Login</title>
  <link rel="stylesheet" href="stilos.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
    /* === ESTILOS DEL INPUT CON OJO === */
    .input-group {
      position: relative;
      display: flex;
      align-items: center;
    }

    .input-group input {
      flex: 1;
      padding-right: 25px; /* espacio para el botón */
    }

    .toggle-email {
      position: absolute;
      right: 5px;
      background: none;
      border: none;
      color: #00ffff;
      cursor: pointer;
      font-size: 0.9em;
      width: 20px;
      height: 20px;
      line-height: 20px;
      padding: 0;
      text-align: center;
      transition: 0.3s;
    }

    .toggle-email:hover {
      color: #fff;
      text-shadow: 0 0 5px #00ffff;
    }
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
    
    <form method="POST" action="login.php" class="formulario">
      <label for="email">Correo:</label>
      <div class="input-group">
        <input type="email" id="email" name="email" required>
        <button type="button" class="toggle-email" onclick="toggleEmail()">👁️</button>
      </div>
      
      <label for="password">Contraseña:</label>
      <input type="password" name="password" required>

      <button type="submit">Ingresar</button>
    </form>
  </div>

  <script>
    // === EFECTO DE FONDO ===
    document.addEventListener('mousemove', e => {
      const circles = document.querySelectorAll('.circulo');
      circles.forEach((c, i) => {
        const x = (e.clientX / window.innerWidth) * 100;
        const y = (e.clientY / window.innerHeight) * 100;
        c.style.transform = `translate(${x / (i + 1)}px, ${y / (i + 1)}px)`;
      });
    });

    // === FUNCIÓN PARA MOSTRAR / OCULTAR CORREO ===
    function toggleEmail() {
      const emailInput = document.getElementById('email');
      const toggleButton = document.querySelector('.toggle-email');

      if (emailInput.type === 'email') {
        emailInput.type = 'text';
        toggleButton.textContent = '🙈';
      } else {
        emailInput.type = 'email';
        toggleButton.textContent = '👁️';
      }
    }
  </script>
</body>
</html>
