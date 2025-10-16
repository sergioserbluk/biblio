<?php
require_once __DIR__ . '/../scripts/fpdf.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Courier', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('¡FPDF funcionando correctamente!'), 0, 1, 'C');
$pdf->Output('I', 'prueba.pdf');
?>
