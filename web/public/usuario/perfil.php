<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../login.php");
    exit;
}

include_once "../../includes/db_connection.php";

$usuario_id = $_SESSION["usuario_id"];
$msg = "";

// ✅ Obtener datos del usuario
$stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ Actualizar datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombre"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Si hay nuevo password → lo actualizamos
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET nombre = ?, email = ?, password = ?
            WHERE id = ?
        ");

        $stmt->execute([$nombre, $email, $password_hash, $usuario_id]);
    } else {
        // No cambia password
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET nombre = ?, email = ?
            WHERE id = ?
        ");

        $stmt->execute([$nombre, $email, $usuario_id]);
    }

    // Actualizar sesión también
    $_SESSION["nombre"] = $nombre;

    $msg = "✅ Datos actualizados correctamente";

    // Obtener datos de nuevo
    $stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil</title>
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        .profile-box {
            margin: 120px auto;
            width: 400px;
            background: #222;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
        }

        .profile-box input {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 12px;
            background: #333;
            color: white;
            border: none;
        }

        .btn-primary {
            width: 100%;
            margin-top: 10px;
        }

        .msg {
            margin-bottom: 20px;
            color: var(--azul-claro);
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="profile-box">

    <h1>Mi perfil</h1>

    <?php if ($msg): ?>
        <p class="msg"><?= $msg ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= $user['nombre'] ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= $user['email'] ?>" required>

        <label>Nueva contraseña</label>
        <input type="password" name="password" placeholder="(Dejar vacío para no cambiar)">

        <button class="btn-primary" type="submit">Guardar cambios</button>
    </form>

    <a class="btn-small" href="panel.php">Volver</a>
</div>

</body>
</html>
