<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../login.php");
    exit;
}

include_once "../../includes/db_connection.php";

if (!isset($_GET["id"])) {
    die("Reserva no especificada.");
}

$reserva_id = (int) $_GET["id"];
$usuario_id = $_SESSION["usuario_id"];

// Cargar datos de la reserva (solo si pertenece al usuario)
$stmt = $pdo->prepare("
    SELECT r.*, u.nombre, u.email
    FROM reservas r
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.id = ? AND r.usuario_id = ?
");
$stmt->execute([$reserva_id, $usuario_id]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    die("Reserva no encontrada o no tienes permiso para verla.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva confirmada</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<div class="panel-container">
    <h1>¡Reserva confirmada!</h1>
    <p>Tu reserva se ha realizado correctamente. Estos son los detalles:</p>

    <div class="table-container">
        <table>
            <tr><th>ID reserva</th><td><?= $reserva["id"] ?></td></tr>
            <tr><th>Zona</th><td><?= htmlspecialchars($reserva["zona"]) ?></td></tr>
            <tr><th>Asiento</th><td><?= $reserva["asiento"] ?></td></tr>
            <tr><th>Fecha</th><td><?= $reserva["fecha"] ?></td></tr>
            <tr><th>Hora inicio</th><td><?= substr($reserva["hora_inicio"], 0, 5) ?></td></tr>
            <tr><th>Hora fin</th><td><?= substr($reserva["hora_fin"], 0, 5) ?></td></tr>
            <tr><th>Precio</th><td><?= number_format($reserva["precio"], 2) ?> €</td></tr>
            <tr><th>Estado</th><td><?= $reserva["estado"] ?></td></tr>
        </table>
    </div>

    <br>

    <!-- ✅ Botón para descargar ticket PDF -->
    <a class="btn-primary" href="descargar_ticket.php?id=<?= $reserva["id"] ?>">Descargar ticket (PDF)</a>

    <br><br>
    <a class="btn-small" href="../usuario/panel.php">Volver al panel de usuario</a>
</div>

</body>
</html>
