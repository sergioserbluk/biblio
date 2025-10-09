<?php
include("../scripts/conexion.php");

// Consulta de prueba
$sql = "SELECT * FROM socios";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    while($fila = $resultado->fetch_assoc()) {
        echo "DNI: " . $fila["dni"]. " - Nombre: " . $fila["nombre"]. " " . $fila["apellido"]. "<br>";
    }
} else {
    echo "No hay registros.";
}

$conn->close();
?>
