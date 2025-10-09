
<?php
require_once __DIR__ . "/../scripts/conexion.php";
require_once __DIR__ . "/../scripts/fpdf.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_multa'])) {
    die("Acceso inválido");
}

$id_multa = (int)$_POST['id_multa'];

// Consultar la multa
$stmt = $conn->prepare("SELECT dni, monto, fecha_generada, estado FROM multas WHERE id_multa = ?");
$stmt->bind_param("i", $id_multa);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Multa no encontrada.");
}

$multa = $res->fetch_assoc();
$dni = $multa['dni'];
$monto = $multa['monto'];
$fecha_generada = $multa['fecha_generada'];
$estado = $multa['estado'];

if ($estado === 'Pagada') {
    die("La multa ya está pagada.");
}

$stmt->close();

// Actualizar el estado a "Pagada"
$stmt = $conn->prepare("UPDATE multas SET estado = 'Pagada', fecha_pago = CURDATE() WHERE id_multa = ?");
$stmt->bind_param("i", $id_multa);
$stmt->execute();
$stmt->close();

// Generar comprobante PDF
$pdf = new FPDF();
$pdf->AddPage();

// ✅ Cambiamos helvetica por Arial (fuente incluida por defecto)
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Comprobante de Pago de Multa', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "DNI del socio: $dni", 0, 1);
$pdf->Cell(0, 10, "Monto pagado: $" . number_format($monto, 2, ',', '.'), 0, 1);
$pdf->Cell(0, 10, "Fecha de generación: $fecha_generada", 0, 1);
$pdf->Cell(0, 10, "Fecha de pago: " . date('d/m/Y'), 0, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Gracias por regularizar su situación. Biblioteca Pública', 0, 1, 'C');

// Mostrar el PDF en el navegador
$pdf->Output('I', 'Comprobante_Pago_Multa.pdf');
?>