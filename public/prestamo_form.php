<?php
require_once __DIR__ . "/../scripts/conexion.php";

// Si se recibió búsqueda de socio:
$socio = null;
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_dni'])) {
    $dni = trim($_POST['dni']);
    // Buscar socio
    $stmt = $conn->prepare("SELECT dni, nombre, apellido, vigente FROM socios WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $mensaje = "No se encontró socio con DNI: " . htmlspecialchars($dni);
    } else {
        $socio = $res->fetch_assoc();
    }
    $stmt->close();
}

// Traer ejemplares disponibles (siempre)
$ejemplares = [];
$sql = "SELECT e.id_ejemplar, e.isbn, t.nombre AS titulo FROM ejemplares e
        LEFT JOIN titulos t ON e.isbn = t.isbn
        WHERE e.disponible = 1";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) $ejemplares[] = $row;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Registrar Préstamo</title></head>
<body>
<h1>Registrar Préstamo</h1>

<!-- Form para buscar socio -->
<form method="post" action="prestamo_form.php">
    <label>DNI del socio: <input type="text" name="dni" required></label>
    <button type="submit" name="buscar_dni">Buscar socio</button>
</form>

<?php if ($mensaje): ?>
    <p style="color:red;"><?php echo $mensaje; ?></p>
<?php endif; ?>

<?php if ($socio): ?>
    <h2>Socio encontrado</h2>
    <p><?php echo htmlspecialchars($socio['nombre'] . ' ' . $socio['apellido'] . ' (DNI: ' . $socio['dni'] . ')'); ?></p>
    <p>Vigente: <?php echo $socio['vigente'] ? 'Sí' : 'No'; ?></p>

    <?php if (!$socio['vigente']): ?>
        <p style="color:red;">El socio no está vigente. No puede sacar préstamos.</p>
    <?php else: ?>
        <!-- Mostrar ejemplares disponibles para seleccionar -->
        <form method="post" action="registrar_prestamo.php">
            <input type="hidden" name="dni" value="<?php echo htmlspecialchars($socio['dni']); ?>">
            <h3>Ejemplares disponibles</h3>
            <?php if (count($ejemplares) === 0): ?>
                <p>No hay ejemplares disponibles.</p>
            <?php else: ?>
                <table border="1" cellpadding="4">
                    <tr><th>Seleccionar</th><th>ID Ejemplar</th><th>ISBN</th><th>Título</th></tr>
                    <?php foreach ($ejemplares as $e): ?>
                        <tr>
                            <td><input type="checkbox" name="ejemplares[]" value="<?php echo $e['id_ejemplar']; ?>"></td>
                            <td><?php echo $e['id_ejemplar']; ?></td>
                            <td><?php echo htmlspecialchars($e['isbn']); ?></td>
                            <td><?php echo htmlspecialchars($e['titulo']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <p><small>Nota: un socio puede tener hasta 3 préstamos activos.</small></p>
                <button type="submit">Registrar préstamo(s)</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
