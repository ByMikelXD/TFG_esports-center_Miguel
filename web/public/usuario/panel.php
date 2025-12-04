<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Usuario</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>

    <div class="panel-container">
        <h1>Panel de Usuario</h1>
        <p>Bienvenido, <?= $_SESSION["nombre"]; ?> 👋</p>

        <div class="panel-options">

            <div class="panel-card">
                <h3>Mis reservas</h3>
                <p>Consulta tus próximas reservas y su estado.</p>
                <a class="panel-btn" href="reservas.php">Ver</a>
            </div>

            <div class="panel-card">
                <h3>Mi perfil</h3>
                <p>Edita tu información personal.</p>
                <a class="panel-btn" href="perfil.php">Editar</a>
            </div>
        </div>

        <a class="btn-small" href="../index.php">Volver</a>
    </div>

</body>
</html>