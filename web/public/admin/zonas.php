<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

include_once "../../includes/db_connection.php";

// ✅ ELIMINAR zona
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];
    $stmt = $pdo->prepare("DELETE FROM zonas WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: zonas.php");
    exit;
}

// ✅ OBTENER lista de zonas
$stmt = $pdo->query("SELECT * FROM zonas ORDER BY id ASC");
$zonas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Zonas</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>

<div class="panel-container">

    <h1>Gestión de Zonas</h1>

    <a class="panel-btn" href="zonas_crear.php">+ Añadir Zona</a>

    <div class="table-container">

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Zona</th>
                    <th>Descripción</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($zonas as $z): ?>
                    <tr>
                        <td><?= $z["id"] ?></td>
                        <td><?= $z["nombre"] ?></td>
                        <td><?= $z["descripcion"] ?></td>
                        <td>
                            <a class="panel-btn" href="zonas_editar.php?id=<?= $z['id'] ?>">Editar</a>

                            <a class="panel-btn"
                               onclick="return confirm('¿Eliminar zona?')"
                               href="zonas.php?delete=<?= $z['id'] ?>">
                               Eliminar
                            </a>
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
