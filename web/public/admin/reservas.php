<?php
session_start();
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

include_once "../../includes/db_connection.php";

// ✅ Cancelar reserva
if (isset($_GET["cancel"])) {
    $id = $_GET["cancel"];
    $stmt = $pdo->prepare("
        UPDATE reservas
        SET estado = 'cancelada'
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    header("Location: reservas.php");
    exit;
}

// ✅ Obtener todas las reservas
$stmt = $pdo->query("
    SELECT r.*, u.nombre
    FROM reservas r
    JOIN usuarios u ON r.usuario_id = u.id
    ORDER BY r.fecha, r.hora_inicio
");
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - Reservas</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<div class="panel-container">

    <h1>Gestión de Reservas</h1>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Zona</th>
                    <th>Asiento</th>
                    <th>Fecha</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($reservas as $r): ?>
                <tr>
                    <td><?= $r["nombre"] ?></td>
                    <td><?= $r["zona"] ?></td>
                    <td><?= $r["asiento"] ?></td>
                    <td><?= $r["fecha"] ?></td>
                    <td><?= $r["hora_inicio"] ?></td>
                    <td><?= $r["hora_fin"] ?></td>
                    <td><?= $r["precio"] ?>€</td>
                    <td><?= $r["estado"] ?></td>

                    <td>
                        <?php if ($r["estado"] === "activa"): ?>
                            <a class="panel-btn" 
                               href="reservas.php?cancel=<?= $r["id"] ?>"
                               onclick="return confirm('¿Cancelar reserva?')">
                               Cancelar
                            </a>
                        <?php else: ?>
                            ---
                        <?php endif; ?>
                    </td>

                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>

    <a class="btn-small" href="panel.php">Volver</a>
</div>

</body>
</html>