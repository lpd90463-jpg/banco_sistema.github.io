<?php
$host = "localhost";
$user = "root";      // tu usuario MySQL
$pass = "";          // tu contraseña MySQL (si usas XAMPP, déjalo vacío)
$dbname = "banco_sistema";

$conexion = new mysqli($host, $user, $pass, $dbname);

if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}
?>
