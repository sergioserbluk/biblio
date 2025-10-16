<?php
require_once __DIR__ . "/../scripts/conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_prestamo'])) {
    exit("Acceso inválido");
}

$id_prestamo = (int)$_POST['id_prestamo'];

try {
    // 1) Iniciar transacción
    $conn->begin_transaction();

    // 2) Bloquear y traer préstamo
    $stmt = $conn->prepare("SELECT id_prestamo, dni, id_ejemplar, fecha_devolucion, devuelto FROM prestamos WHERE id_prestamo = ? FOR UPDATE");
    $stmt->bind_param("i", $id_prestamo);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        throw new Exception("Préstamo no encontrado.");
    }
    $prest = $res->fetch_assoc();
    $stmt->close();

    if ((int)$prest['devuelto'] === 1) {
        throw new Exception("El préstamo ya fue devuelto, no puede renovarse.");
    }

    $hoy = new DateTime();
    $fecha_devolucion = new DateTime($prest['fecha_devolucion']);
    if ($hoy > $fecha_devolucion) {
        throw new Exception("El préstamo está vencido y no puede renovarse.");
    }

    // 3) Verificar reservas activas en el ejemplar
    $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM reservas WHERE id_ejemplar = ? AND estado = 'Activa'");
    $stmt->bind_param("i", $prest['id_ejemplar']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ((int)$row['cnt'] > 0) {
        throw new Exception("El ejemplar está reservado por otro socio. Renovación bloqueada.");
    }

    // 4) Calcular nueva fecha (+15 días desde la fecha_devolucion actual)
    $nueva_fecha = $fecha_devolucion->modify('+15 days')->format('Y-m-d');

    // 5) Actualizar prestamos
    $stmt = $conn->prepare("UPDATE prestamos SET fecha_devolucion = ? WHERE id_prestamo = ?");
    $stmt->bind_param("si", $nueva_fecha, $id_prestamo);
    $stmt->execute();
    $stmt->close();

    // 6) Registrar en tabla renovaciones
    $stmt = $conn->prepare("INSERT INTO renovaciones (id_prestamo, dni, fecha_renovacion, fecha_vieja, fecha_nueva, observacion) VALUES (?, ?, CURDATE(), ?, ?, ?)");
    $observ = "Renovación automática +15 días";
    $fecha_vieja = $fecha_devolucion->format('Y-m-d'); // note: modify changed object; we saved earlier value
    // since $fecha_devolucion was modified, get previous by subtracting 15? better re-calc:
    // We'll recompute original: original = (new - 15)
    $fecha_nueva = $nueva_fecha;
    // compute fecha_vieja by subtracting 15 days from nueva_fecha
    $dt = new DateTime($nueva_fecha);
    $dt->modify('-15 days');
    $fecha_vieja = $dt->format('Y-m-d');

    $stmt->bind_param("issss", $id_prestamo, $prest['dni'], $fecha_vieja, $fecha_nueva, $observ);
    $stmt->execute();
    $stmt->close();

    // 7) Commit
    $conn->commit();

    echo "<h2>Renovación exitosa</h2>";
    echo "<p>ID préstamo: " . $id_prestamo . "</p>";
    echo "<p>Socio DNI: " . htmlspecialchars($prest['dni']) . "</p>";
    echo "<p>Fecha de devolución anterior: " . htmlspecialchars($fecha_vieja) . "</p>";
    echo "<p>Nueva fecha de devolución: " . htmlspecialchars($fecha_nueva) . "</p>";
    echo '<p><a href="renovar_form.php">Volver</a></p>';

} catch (Exception $e) {
    $conn->rollback();
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo '<p><a href="renovar_form.php">Volver</a></p>';
}
