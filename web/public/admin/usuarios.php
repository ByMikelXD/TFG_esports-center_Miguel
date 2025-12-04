<?php
session_start();
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

include_once "../../includes/db_connection.php";

// ✅ Cambiar rol
if (isset($_GET["rol"]) && isset($_GET["id"])) {
    $id = $_GET["id"];
    $rol = $_GET["rol"];

    $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
    $stmt->execute([$rol, $id]);
    header("Location: usuarios.php");
    exit;
}

// ✅ Eliminar
if (isset($_GET["delete"])) {
    $id = $_GET["delete"];

    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: usuarios.php");
    exit;
}

// ✅ Obtener lista
$stmt = $pdo->query("SELECT * FROM usuarios");
$lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - Usuarios</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

<div class="panel-container">

    <h1>Gestión de Usuarios</h1>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($lista as $u): ?>
                <tr>
                    <td><?= $u["nombre"] ?></td>
                    <td><?= $u["email"] ?></td>
                    <td><?= $u["rol"] ?></td>
                    <td>

                        <!-- Cambiar rol -->
                        <?php if ($u["rol"] === "admin"): ?>
                            <a class="panel-btn" href="usuarios.php?rol=usuario&id=<?= $u["id"] ?>">Hacer Usuario</a>
                        <?php else: ?>
                            <a class="panel-btn" href="usuarios.php?rol=admin&id=<?= $u["id"] ?>">Hacer Admin</a>
                        <?php endif; ?>

                        <!-- Eliminar -->
                        <a class="panel-btn" 
                           href="usuarios.php?delete=<?= $u["id"] ?>"
                           onclick="return confirm('¿Eliminar usuario?')">
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
