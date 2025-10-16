<?php
require_once __DIR__ . "/../scripts/conexion.php";
require_once __DIR__ . "/../scripts/fpdf.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_multa'])) {
    exit("Acceso inválido");
}

$id_multa = (int)$_POST['id_multa'];

// Consultar multa
$stmt = $conn->prepare("SELECT dni, monto, fecha_generada, estado FROM multas WHERE id_multa = ?");
$stmt->bind_param("i", $id_multa);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    exit("Multa no encontrada.");
}

$multa = $res->fetch_assoc();
$dni = $multa['dni'];
$monto = $multa['monto'];
$fecha_generada = $multa['fecha_generada'];
$estado = $multa['estado'];

if ($estado === 'Pagada') {
    // Mostrar mensaje dentro del PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Courier', 'B', 16);
    $pdf->Cell(0, 10, utf8_decode('Comprobante no generado'), 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->SetFont('Courier', '', 12);
    $pdf->MultiCell(0, 10, utf8_decode("La multa ya fue pagada anteriormente."), 0, 'C');
    header('Content-Type: application/pdf');
    $pdf->Output('I', 'Multa_Pagada.pdf');
    exit;
}

$stmt->close();

// Actualizar estado a "Pagada"
$stmt = $conn->prepare("UPDATE multas SET estado = 'Pagada', fecha_pago = CURDATE() WHERE id_multa = ?");
$stmt->bind_param("i", $id_multa);
$stmt->execute();
$stmt->close();

// Crear comprobante PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Courier', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Comprobante de Pago de Multa'), 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Courier', '', 12);
$pdf->Cell(0, 10, "DNI del socio: $dni", 0, 1);
$pdf->Cell(0, 10, "Monto pagado: $" . number_format($monto, 2, ',', '.'), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Fecha de generación: $fecha_generada"), 0, 1);
$pdf->Cell(0, 10, "Fecha de pago: " . date('d/m/Y'), 0, 1);
$pdf->Ln(10);

$pdf->SetFont('Courier', 'I', 10);
$pdf->MultiCell(0, 10, utf8_decode("Gracias por regularizar su situación.\nBiblioteca Pública"), 0, 'C');

// Encabezado correcto antes del PDF
header('Content-Type: application/pdf');
$pdf->Output('I', 'Comprobante_Pago_Multa.pdf');
exit;
?>
