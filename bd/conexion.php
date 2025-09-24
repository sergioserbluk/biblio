<?php
$host = "localhost";
$user = "root";   // usuario de MySQL (por defecto en XAMPP es root)
$pass = "";       // contraseña vacía si no la configuraste
$db   = "biblioteca";  // nombre de tu base de datos

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>

