<?php
require_once __DIR__ . "/../scripts/conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: prestamo_form.php");
    exit;
}

$dni = $_POST['dni'] ?? '';
$ejemplares = $_POST['ejemplares'] ?? [];

if (empty($dni) || empty($ejemplares)) {
    die("DNI o ejemplares no provistos.");
}

// 1) Verificar socio
$stmt = $conn->prepare("SELECT dni, vigente FROM socios WHERE dni = ?");
$stmt->bind_param("s", $dni);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die("Socio no encontrado.");
}
$socio = $res->fetch_assoc();
if (!$socio['vigente']) {
    die("El socio no está vigente.");
}
$stmt->close();

// 2) Contar préstamos activos actuales
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM prestamos WHERE dni = ? AND devuelto = 0");
$stmt->bind_param("s", $dni);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$activos = (int)$row['cnt'];
$stmt->close();

$seleccionados = count($ejemplares);
if ($activos + $seleccionados > 3) {
    die("El socio tiene $activos préstamos activos. No puede superar 3 (intenta seleccionar menos ejemplares).");
}

// 3) (Opcional) verificar multas si existe tabla 'multas'
$hayMultas = false;
$check = $conn->query("SHOW TABLES LIKE 'multas'");
if ($check && $check->num_rows > 0) {
    $stmt = $conn->prepare("SELECT SUM(monto) AS total FROM multas WHERE dni = ? AND estado = 'Pendiente'");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $res = $stmt->get_result();
    $m = $res->fetch_assoc();
    $totalMultas = (float)$m['total'];
    $stmt->close();
    if ($totalMultas > 0) {
        die("El socio tiene multas pendientes: $totalMultas. No puede realizar préstamos.");
    }
}

// 4) Empezar transacción
$conn->begin_transaction();

try {
    $inserted = [];
    foreach ($ejemplares as $id_ejemplar) {
        $id_ejemplar = (int)$id_ejemplar;

        // a) Bloquear y chequear disponibilidad
        $stmt = $conn->prepare("SELECT disponible FROM ejemplares WHERE id_ejemplar = ? FOR UPDATE");
        $stmt->bind_param("i", $id_ejemplar);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            throw new Exception("Ejemplar ID $id_ejemplar no existe.");
        }
        $row = $res->fetch_assoc();
        if ((int)$row['disponible'] === 0) {
            throw new Exception("Ejemplar ID $id_ejemplar no está disponible.");
        }
        $stmt->close();

        // b) Insertar préstamo
        $stmt = $conn->prepare("INSERT INTO prestamos (dni, fecha_prestamo, id_ejemplar, fecha_devolucion, devuelto) VALUES (?, CURDATE(), ?, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 0)");
        $stmt->bind_param("si", $dni, $id_ejemplar);
        $stmt->execute();
        $idPrestamo = $conn->insert_id;
        $stmt->close();

        // c) Actualizar ejemplar disponible = 0
        $stmt = $conn->prepare("UPDATE ejemplares SET disponible = 0 WHERE id_ejemplar = ?");
        $stmt->bind_param("i", $id_ejemplar);
        $stmt->execute();
        $stmt->close();

        $inserted[] = $idPrestamo;
    }

    // d) Commit
    $conn->commit();

    // e) Mostrar comprobante simple
    echo "<h2>Préstamo registrado correctamente</h2>";
    echo "<p>Socio DNI: " . htmlspecialchars($dni) . "</p>";
    echo "<ul>";
    foreach ($inserted as $pid) {
        echo "<li>Id préstamo: $pid</li>";
    }
    echo "</ul>";
    echo "<p>Fecha de devolución: " . date('Y-m-d', strtotime('+15 days')) . " (15 días desde hoy)</p>";
    echo '<p><a href="prestamo_form.php">Volver</a></p>';

} catch (Exception $e) {
    $conn->rollback();
    die("Error al registrar préstamo: " . $e->getMessage());
}
?>
