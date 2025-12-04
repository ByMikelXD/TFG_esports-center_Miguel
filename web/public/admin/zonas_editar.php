<?php
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

include_once "../../includes/db_connection.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];

    $stmt = $pdo->prepare("
        INSERT INTO zonas (nombre, descripcion)
        VALUES (?, ?)
    ");
    $stmt->execute([$nombre, $descripcion]);

    header("Location: zonas.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Crear Zona</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>

<div class="panel-container">

    <h1>Crear Zona</h1>

    <form class="form-box" method="POST">

        <label>Nombre</label>
        <input name="nombre" required>

        <label>Descripción</label>
        <textarea name="descripcion"></textarea>

        <button class="btn-primary" type="submit">Guardar</button>
    </form>

    <a class="btn-small" href="zonas.php">Volver</a>

</div>

</body>
</html>
