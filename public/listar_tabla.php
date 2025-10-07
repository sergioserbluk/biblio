<?php
include("../scripts/conexion.php");

// Obtener el nombre de la tabla desde la URL
$tabla = isset($_GET['tabla']) ? $_GET['tabla'] : '';

if ($tabla == '') {
    echo "⚠️ Debes indicar una tabla en la URL. Ejemplo: listar_tabla.php?tabla=socios";
    exit;
}

// Armar consulta dinámica
$sql = "SELECT * FROM " . $tabla;
$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $conn->error);
}

if ($resultado->num_rows > 0) {
    echo "<h2>Registros en la tabla <u>$tabla</u></h2>";
    echo "<table border='1' cellpadding='5'>";
    
    // Encabezados de columna
    $campos = $resultado->fetch_fields();
    echo "<tr>";
    foreach ($campos as $campo) {
        echo "<th>" . $campo->name . "</th>";
    }
    echo "</tr>";
    
    // Filas de datos
    while($fila = $resultado->fetch_assoc()) {
        echo "<tr>";
        foreach ($fila as $valor) {
            echo "<td>" . $valor . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No hay registros en la tabla $tabla.";
}

$conn->close();
?>
