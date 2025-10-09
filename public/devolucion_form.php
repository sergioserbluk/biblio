<?php
require_once __DIR__ . "/../scripts/conexion.php";

$mensaje = '';
$prestamos = [];

// Buscar por DNI o por ID de ejemplar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $dni = trim($_POST['dni'] ?? '');
    $id_ejemplar = trim($_POST['id_ejemplar'] ?? '');

    if ($dni !== '') {
        // Buscar préstamos activos del socio
        $stmt = $conn->prepare("SELECT p.id_prestamo, p.dni, p.id_ejemplar, p.fecha_prestamo, p.fecha_devolucion, t.nombre AS titulo
                                FROM prestamos p
                                JOIN ejemplares e ON p.id_ejemplar = e.id_ejemplar
                                LEFT JOIN titulos t ON e.isbn = t.isbn
                                WHERE p.dni = ? AND p.devuelto = 0");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $prestamos[] = $row;
        $stmt->close();
        if (count($prestamos) === 0) $mensaje = "No hay préstamos activos para el DNI $dni.";
    } elseif ($id_ejemplar !== '') {
        // Buscar préstamo activo por ejemplar
        $stmt = $conn->prepare("SELECT p.id_prestamo, p.dni, p.id_ejemplar, p.fecha_prestamo, p.fecha_devolucion, t.nombre AS titulo
                                FROM prestamos p
                                JOIN ejemplares e ON p.id_ejemplar = e.id_ejemplar
                                LEFT JOIN titulos t ON e.isbn = t.isbn
                                WHERE p.id_ejemplar = ? AND p.devuelto = 0");
        $stmt->bind_param("i", $id_ejemplar);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $prestamos[] = $row;
        $stmt->close();
        if (count($prestamos) === 0) $mensaje = "No hay préstamo activo para el ejemplar $id_ejemplar.";
    } else {
        $mensaje = "Ingresá DNI o ID de ejemplar para buscar.";
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Registrar Devolución</title></head>
<body>
<h1>Registrar Devolución</h1>

<form method="post" action="devolucion_form.php">
    <label>Buscar por DNI: <input type="text" name="dni"></label>
    <label>o por ID de ejemplar: <input type="text" name="id_ejemplar"></label>
    <button type="submit" name="buscar">Buscar</button>
</form>

<?php if ($mensaje): ?>
    <p style="color: red;"><?php echo htmlspecialchars($mensaje); ?></p>
<?php endif; ?>

<?php if (count($prestamos) > 0): ?>
    <h2>Préstamos activos encontrados</h2>
    <form method="post" action="registrar_devolucion.php">
        <table border="1" cellpadding="5">
            <tr><th>Seleccionar</th><th>ID Préstamo</th><th>DNI</th><th>ID Ejemplar</th><th>Título</th><th>Fecha préstamo</th><th>Fecha vencimiento</th><th>Días atraso</th></tr>
            <?php foreach ($prestamos as $p):
                $hoy = new DateTime();
                $venc = new DateTime($p['fecha_devolucion']);
                $dias = (int)$hoy->diff($venc)->format("%r%a"); // negativo si vencido -> days positive? format %r%a returns negative if before
                // Queremos días de retraso: si hoy > venc => retraso = (hoy - venc) days, else 0
                $diasAtraso = 0;
                if ($hoy > $venc) {
                    $diasAtraso = (int)$hoy->diff($venc)->format("%a"); // absolute days
                }
            ?>
            <tr>
                <td><input type="radio" name="id_prestamo" value="<?php echo $p['id_prestamo']; ?>" required></td>
                <td><?php echo $p['id_prestamo']; ?></td>
                <td><?php echo htmlspecialchars($p['dni']); ?></td>
                <td><?php echo $p['id_ejemplar']; ?></td>
                <td><?php echo htmlspecialchars($p['titulo']); ?></td>
                <td><?php echo $p['fecha_prestamo']; ?></td>
                <td><?php echo $p['fecha_devolucion']; ?></td>
                <td><?php echo $diasAtraso > 0 ? $diasAtraso : '0'; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p>
            <label>Registrar pago de multa ahora (si corresponde): 
                <input type="checkbox" name="pagar_multa" value="1">
            </label>
        </p>

        <button type="submit">Registrar devolución</button>
        <p><small>La multa por día de demora es $100 (valor configurable).</small></p>
    </form>
<?php endif; ?>

</body>
</html>
