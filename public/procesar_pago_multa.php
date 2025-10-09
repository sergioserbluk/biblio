<?php
require('../scripts/fpdf.php');
include('../scripts/conexion.php');

// Verificar si el acceso viene desde un formulario (POST)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "⚠️ Acceso inválido. Este archivo debe ejecutarse desde el formulario de pago.";
    exit;
}

// Obtener datos del formulario
$id_multa = $_POST['id_multa'] ?? 0;
$fecha_pago = date('Y-m-d');

// Verificar que se haya recibido el ID de multa
if ($id_multa == 0) {
    echo "❌ Error: falta el ID de la multa.";
    exit;
}

// Registrar el pago en la base de datos
$sql = "UPDATE multas SET estado = 'Pagada', fecha_pago = '$fecha_pago' WHERE id_multa = $id_multa";

if ($conn->query($sql) === TRUE) {

    // Obtener datos de la multa, el socio y el libro
    $sql_detalle = "
        SELECT 
            m.id_multa, 
            m.monto, 
            m.fecha_generada, 
            s.nombre AS nombre_socio, 
            s.apellido AS apellido_socio, 
            t.nombre AS libro
        FROM multas m
        INNER JOIN prestamos p ON m.id_prestamo = p.id_prestamo
        INNER JOIN socios s ON m.dni = s.dni
        INNER JOIN ejemplares e ON p.id_ejemplar = e.id_ejemplar
        INNER JOIN titulos t ON e.isbn = t.isbn
        WHERE m.id_multa = $id_multa
    ";

    $resultado = $conn->query($sql_detalle);

    if ($resultado && $resultado->num_rows > 0) {
        $datos = $resultado->fetch_assoc();

        // Crear PDF de comprobante
        $pdf = new FPDF();
        $pdf->AddPage();

        // Título del comprobante
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Comprobante de Pago de Multa', 0, 1, 'C');
        $pdf->Ln(10);

        // Datos del socio y multa
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, 'Socio:', 0, 0);
        $pdf->Cell(0, 10, utf8_decode($datos['nombre_socio'] . ' ' . $datos['apellido_socio']), 0, 1);

        $pdf->Cell(50, 10, 'Libro:', 0, 0);
        $pdf->Cell(0, 10, utf8_decode($datos['libro']), 0, 1);

        $pdf->Cell(50, 10, 'Fecha de Multa:', 0, 0);
        $pdf->Cell(0, 10, $datos['fecha_generada'], 0, 1);

        $pdf->Cell(50, 10, 'Monto Pagado:', 0, 0);
        $pdf->Cell(0, 10, '$' . number_format($datos['monto'], 2, ',', '.'), 0, 1);

        $pdf->Cell(50, 10, 'Fecha de Pago:', 0, 0);
        $pdf->Cell(0, 10, $fecha_pago, 0, 1);

        $pdf->Ln(10);
        $pdf->Cell(0, 10, 'Gracias por mantener su cuenta al día.', 0, 1, 'C');

        // Mostrar el PDF
        $pdf->Output('I', 'comprobante_pago_' . $id_multa . '.pdf');

    } else {
        echo "⚠️ No se encontraron datos de la multa o del socio.";
    }

} else {
    echo "❌ Error al registrar pago: " . $conn->error;
}

$conn->close();
?>
