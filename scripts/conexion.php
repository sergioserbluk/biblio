<?php
// Datos de conexión a la base de datos
$host = "localhost";
$usuario = "root";     // Usuario por defecto en XAMPP
$clave = "";           // Contraseña vacía en XAMPP por defecto
$base_datos = "biblioteca";

// Crear conexión
$conn = new mysqli($host, $usuario, $clave, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>


