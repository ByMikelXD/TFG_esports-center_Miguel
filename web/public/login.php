<?php
include_once "../includes/db_connection.php";
session_start();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    $query = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $query->execute([$email]);
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario["password"])) {
    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["nombre"] = $usuario["nombre"];
    $_SESSION["rol"] = $usuario["rol"];
    header("Location: index.php");
    exit;
}

     else {
        $msg = "❌ Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>

    <style>
        :root {
            --azul-claro: #4FC3F7;
            --violeta: #8E24AA;
            --negro: #1E1E1E;
            --blanco: #ffffff;
        }

        body {
            background-color: var(--negro);
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: var(--blanco);
        }

        .auth-container {
            background-color: #222;
            padding: 30px;
            border-radius: 12px;
            width: 320px;
            text-align: center;
            box-shadow: 0 0 12px rgba(0,0,0,0.4);
        }

        h2 {
            color: var(--azul-claro);
            margin-bottom: 20px;
        }

        .auth-msg {
            color: var(--violeta);
            margin-bottom: 10px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        input {
            padding: 10px;
            border-radius: 6px;
            border: none;
            background: #333;
            color: var(--blanco);
        }

        .btn-primary {
            padding: 10px;
            background-color: var(--violeta);
            border: none;
            border-radius: 6px;
            color: var(--blanco);
            font-weight: bold;
            cursor: pointer;
        }

        .btn-primary:hover {
            opacity: 0.85;
        }

        a {
            color: var(--azul-claro);
        }
    </style>
</head>

<body>

    <div class="auth-container">
        <h2>Iniciar sesión</h2>

        <?php if ($msg) echo "<p class='auth-msg'>$msg</p>"; ?>

        <form method="POST" class="auth-form">
            <input type="email" name="email" placeholder="Correo" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" class="btn-primary">Entrar</button>
        </form>

        <p>¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
    </div>

</body>
</html>
