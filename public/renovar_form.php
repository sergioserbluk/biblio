<?php
require_once __DIR__ . "/../scripts/conexion.php";

$mensaje = '';
$prestamos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    $dni = trim($_POST['dni'] ?? '');
    $id_prestamo = trim($_POST['id_prestamo'] ?? '');
    $id_ejemplar = trim($_POST['id_ejemplar'] ?? '');

    if ($id_prestamo !== '') {
        $stmt = $conn->prepare("SELECT p.*, e.isbn, t.nombre AS titulo
                                FROM prestamos p
                                JOIN ejemplares e ON p.id_ejemplar = e.id_ejemplar
                                LEFT JOIN titulos t ON e.isbn = t.isbn
                                WHERE p.id_prestamo = ? AND p.devuelto = 0");
        $stmt->bind_param("i", $id_prestamo);
    } elseif ($id_ejemplar !== '') {
        $stmt = $conn->prepare("SELECT p.*, e.isbn, t.nombre AS titulo
                                FROM prestamos p
                                JOIN ejemplares e ON p.id_ejemplar = e.id_ejemplar
                                LEFT JOIN titulos t ON e.isbn = t.isbn
                                WHERE p.id_ejemplar = ? AND p.devuelto = 0");
        $stmt->bind_param("i", $id_ejemplar);
    } elseif ($dni !== '') {
        $stmt = $conn->prepare("SELECT p.*, e.isbn, t.nombre AS titulo
                                FROM prestamos p
                                JOIN ejemplares e ON p.id_ejemplar = e.id_ejemplar
                                LEFT JOIN titulos t ON e.isbn = t.isbn
                                WHERE p.dni = ? AND p.devuelto = 0");
        $stmt->bind_param("s", $dni);
    } else {
        $mensaje = "Ingresá DNI, ID préstamo o ID ejemplar para buscar.";
    }

    if (!empty($stmt)) {
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $prestamos[] = $row;
        }
        $stmt->close();

        if (count($prestamos) === 0 && $mensaje === '') $mensaje = "No se encontraron préstamos activos.";
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Renovar Préstamo</title></head>
<body>
<h1>Renovar Préstamo</h1>

<form method="post" action="renovar_form.php">
    <label>DNI: <input type="text" name="dni"></label>
    <label>ID Préstamo: <input type="text" name="id_prestamo"></label>
    <label>ID Ejemplar: <input type="text" name="id_ejemplar"></label>
    <button type="submit" name="buscar">Buscar</button>
</form>

<?php if ($mensaje): ?>
    <p style="color:red;"><?php echo htmlspecialchars($mensaje); ?></p>
<?php endif; ?>

<?php if (count($prestamos) > 0): ?>
    <h2>Préstamos encontrados</h2>
    <form method="post" action="procesar_renovacion.php">
        <table border="1" cellpadding="5">
            <tr><th>Seleccionar</th><th>ID Préstamo</th><th>DNI</th><th>ID Ejemplar</th><th>Título</th><th>Fecha préstamo</th><th>Fecha vencimiento</th></tr>
            <?php foreach ($prestamos as $p): 
                $hoy = new DateTime(); $venc = new DateTime($p['fecha_devolucion']);
                $vencido = ($hoy > $venc);
            ?>
            <tr>
                <td><input type="radio" name="id_prestamo" value="<?php echo $p['id_prestamo']; ?>" required></td>
                <td><?php echo $p['id_prestamo']; ?></td>
                <td><?php echo htmlspecialchars($p['dni']); ?></td>
                <td><?php echo $p['id_ejemplar']; ?></td>
                <td><?php echo htmlspecialchars($p['titulo']); ?></td>
                <td><?php echo $p['fecha_prestamo']; ?></td>
                <td><?php echo $p['fecha_devolucion'] . ($vencido ? ' (Vencido)' : ''); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p><small>Renovación extiende 15 días adicionales desde la fecha_devolucion actual.</small></p>
        <button type="submit" name="renovar">Renovar préstamo</button>
    </form>
<?php endif; ?>

</body>
</html>
