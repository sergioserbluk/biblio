<?php
// Datos de conexión a la base de datos
$host = "localhost";   // Servidor local (XAMPP usa localhost)
$usuario = "root";     // Usuario por defecto en XAMPP
$clave = "";           // Contraseña (vacía por defecto en XAMPP)
$base_datos = "biblioteca"; // Nombre de la base que creaste

// Crear conexión
$conexion = new mysqli($host, $usuario, $clave, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("❌ Error en la conexión: " . $conexion->connect_error);
}

// Si llega hasta aquí, la conexión fue exitosa
// Podés usar esta variable $conexion en tus consultas SQL
?>
