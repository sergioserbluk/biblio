<?php
require_once __DIR__ . "/../scripts/conexion.php";

$multas = [];
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $dni = trim($_POST['dni'] ?? '');

    if ($dni !== '') {
        $stmt = $conn->prepare("SELECT id_multa, monto, fecha_generada, estado FROM multas WHERE dni = ? AND estado = 'Pendiente'");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $multas[] = $row;
        $stmt->close();

        if (count($multas) === 0) $mensaje = "No hay multas pendientes para el DNI $dni.";
    } else {
        $mensaje = "Debe ingresar un DNI.";
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Pagar Multas</title></head>
<body>
<h1>Pago de Multas</h1>

<form method="post" action="pagar_multa_form.php">
    <label>DNI del socio: <input type="text" name="dni" required></label>
    <button type="submit" name="buscar">Buscar</button>
</form>

<?php if ($mensaje): ?>
<p style="color: red;"><?php echo htmlspecialchars($mensaje); ?></p>
<?php endif; ?>

<?php if (count($multas) > 0): ?>
<form method="post" action="procesar_pago_multa.php">
    <h3>Multas pendientes</h3>
    <table border="1" cellpadding="5">
        <tr><th>Seleccionar</th><th>ID Multa</th><th>Monto</th><th>Fecha Generada</th><th>Estado</th></tr>
        <?php foreach ($multas as $m): ?>
        <tr>
            <td><input type="radio" name="id_multa" value="<?php echo $m['id_multa']; ?>" required></td>
            <td><?php echo $m['id_multa']; ?></td>
            <td>$<?php echo number_format($m['monto'], 2); ?></td>
            <td><?php echo $m['fecha_generada']; ?></td>
            <td><?php echo $m['estado']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><button type="submit">Registrar Pago</button></p>
</form>
<?php endif; ?>

</body>
</html>
