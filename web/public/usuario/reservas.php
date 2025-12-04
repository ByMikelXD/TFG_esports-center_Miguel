<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../login.php");
    exit;
}

include_once "../../includes/db_connection.php";

$usuario_id = $_SESSION["usuario_id"];

// ✅ Cancelar
if (isset($_GET["cancel"])) {
    $id = $_GET["cancel"];

    $stmt = $pdo->prepare("
        UPDATE reservas 
        SET estado = 'cancelada'
        WHERE id = ? AND usuario_id = ?
    ");
    $stmt->execute([$id, $usuario_id]);

    header("Location: reservas.php");
    exit;
}

// ✅ Obtener reservas del usuario
$stmt = $pdo->prepare("
    SELECT *
    FROM reservas
    WHERE usuario_id = ?
    ORDER BY fecha, hora_inicio
");
$stmt->execute([$usuario_id]);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reservas</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>

<div class="panel-container">
    <h1>Mis Reservas</h1>

    <?php if (count($reservas) === 0): ?>
        <p>No tienes reservas.</p>
    <?php else: ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
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
                        <td><?= $r["zona"] ?></td>
                        <td><?= $r["asiento"] ?></td>
                        <td><?= $r["fecha"] ?></td>
                        <td><?= $r["hora_inicio"] ?></td>
                        <td><?= $r["hora_fin"] ?></td>
                        <td><?= $r["precio"] ?> €</td>
                        <td><?= $r["estado"] ?></td>
                        <td>
                            <?php if ($r["estado"] === "activa"): ?>
                                <a class="panel-btn" 
                                   href="reservas.php?cancel=<?= $r["id"] ?>"
                                   onclick="return confirm('¿Seguro que quieres cancelar esta reserva?')">
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

    <?php endif; ?>

    <a class="btn-small" href="panel.php">Volver</a>
</div>

</body>
</html>
