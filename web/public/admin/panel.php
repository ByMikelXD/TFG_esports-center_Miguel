<?php
session_start();
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>

<div class="panel-container">
    <h1>Panel Administrador</h1>

    <p>Hola, <?= $_SESSION["nombre"]; ?> 👋</p>

    <div class="panel-options">

        <div class="panel-card">
            <h3>Usuarios</h3>
            <p>Gestiona los datos de los usuarios.</p>
            <a class="panel-btn" href="usuarios.php">Ver</a>
        </div>

        <div class="panel-card">
            <h3>Reservas</h3>
            <p>Gestiona todas las reservas del sistema.</p>
            <a class="panel-btn" href="reservas.php">Ver</a>
        </div>

        <div class="panel-card">
            <h3>Zonas / Instalaciones</h3>
            <p>Modificar lista de zonas, puestos, etc.</p>
            <a class="panel-btn" href="zonas.php">Ver</a>
        </div>

        <div class="panel-card">
            <h3>Estadísticas</h3>
            <p>Resumen de usuarios, reservas e ingresos.</p>
            <a class="panel-btn" href="estadisticas.php">Ver</a>
        </div>

    </div>

    <a class="btn-small" href="../index.php">Volver</a>
</div>

</body>
</html>