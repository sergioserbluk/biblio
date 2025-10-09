<?php
require_once __DIR__ . "/../scripts/conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: devolucion_form.php");
    exit;
}

$id_prestamo = isset($_POST['id_prestamo']) ? (int)$_POST['id_prestamo'] : 0;
$pagarAhora = isset($_POST['pagar_multa']) && $_POST['pagar_multa'] == '1';

if ($id_prestamo <= 0) {
    die("No se indicó un préstamo válido.");
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Bloquear préstamo
    $stmt = $conn->prepare("SELECT id_prestamo, dni, id_ejemplar, fecha_devolucion, devuelto FROM prestamos WHERE id_prestamo = ? FOR UPDATE");
    $stmt->bind_param("i", $id_prestamo);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) throw new Exception("Préstamo no encontrado.");
    $prest = $res->fetch_assoc();
    $stmt->close();

    if ((int)$prest['devuelto'] === 1) throw new Exception("El préstamo ya fue devuelto.");

    $dni = $prest['dni'];
    $id_ejemplar = (int)$prest['id_ejemplar'];
    $fecha_venc = new DateTime($prest['fecha_devolucion']);
    $hoy = new DateTime();
    $diasAtraso = 0;
    if ($hoy > $fecha_venc) {
        $diasAtraso = (int)$hoy->diff($fecha_venc)->format("%a"); // dias de retraso
    }

    $montoMulta = 0.00;
    if ($diasAtraso > 0) {
        $montoMulta = $diasAtraso * 100.00; // regla de negocio: $100 por día
    }

    // 1) Actualizar prestamos: marcar devuelto y fecha_devolucion_real
    $stmt = $conn->prepare("UPDATE prestamos SET devuelto = 1, fecha_devolucion_real = CURDATE() WHERE id_prestamo = ?");
    $stmt->bind_param("i", $id_prestamo);
    $stmt->execute();
    $stmt->close();

    // 2) Actualizar ejemplar: disponible = 1
    $stmt = $conn->prepare("UPDATE ejemplares SET disponible = 1 WHERE id_ejemplar = ?");
    $stmt->bind_param("i", $id_ejemplar);
    $stmt->execute();
    $stmt->close();

    $multaId = null;
    if ($montoMulta > 0) {
        // si existe la tabla 'multas' insertamos la multa
        $check = $conn->query("SHOW TABLES LIKE 'multas'");
        if ($check && $check->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO multas (dni, id_prestamo, monto, fecha_generada, estado) VALUES (?, ?, ?, CURDATE(), ?)");
            $estado = $pagarAhora ? 'Pagada' : 'Pendiente';
            $stmt->bind_param("sids", $dni, $id_prestamo, $montoMulta, $estado);
            $stmt->execute();
            $multaId = $conn->insert_id;
            $stmt->close();
        }
    }

    // Si el bibliotecario pagó ahora y hay multa registrada, actualizamos estado (ya lo pusimos en 'Pagada' si pagó)
    // (Podríamos agregar una tabla de pagos más adelante)

    // Commit
    $conn->commit();

    // Mostrar comprobante
    echo "<h2>Devolución registrada</h2>";
    echo "<p>Préstamo ID: {$id_prestamo}</p>";
    echo "<p>Socio DNI: " . htmlspecialchars($dni) . "</p>";
    echo "<p>Ejemplar ID: {$id_ejemplar}</p>";
    echo "<p>Fecha de vencimiento: " . $prest['fecha_devolucion'] . "</p>";
    echo "<p>Fecha de devolución registrada: " . date('Y-m-d') . "</p>";

    if ($montoMulta > 0) {
        echo "<h3>Multa generada: $" . number_format($montoMulta, 2) . "</h3>";
        if ($multaId) {
            echo "<p>Multa ID: {$multaId}. Estado: " . ($pagarAhora ? "Pagada" : "Pendiente") . "</p>";
        } else {
            echo "<p>No se registró la multa en la tabla 'multas' porque no existe en la BD.</p>";
        }
    } else {
        echo "<p>No corresponde multa.</p>";
    }

    echo '<p><a href="devolucion_form.php">Volver</a></p>';

} catch (Exception $e) {
    $conn->rollback();
    die("Error al registrar devolución: " . $e->getMessage());
}
?>
