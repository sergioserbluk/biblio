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

if ($multa['estado'] === 'Pagada') {
    die("La multa ya está pagada.");
}

$stmt->close();

// Actualizar el estado a "Pagada"
$stmt = $conn->prepare("UPDATE multas SET estado = 'Pagada', fecha_pago = CURDATE() WHERE id_multa = ?");
$stmt->bind_param("i", $id_multa);
$stmt->execute();
$stmt->close();

// Crear PDF con FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Encabezado del comprobante
$pdf->SetFont('Helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Comprobante de Pago de Multa', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(0, 10, "DNI del socio: " . $multa['dni'], 0, 1);
$pdf->Cell(0, 10, "Monto pagado: $" . $multa['monto'], 0, 1);
$pdf->Cell(0, 10, "Fecha de pago: " . date('d/m/Y'), 0, 1);

$pdf->Output('I', 'Comprobante_Pago_Multa.pdf');
?>
