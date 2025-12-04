<?php
include_once "../includes/db_connection.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombre"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $query = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
    
    try {
        $query->execute([$nombre, $email, $password]);
        $msg = "✅ Registro exitoso. Ahora puedes iniciar sesión.";
    } catch (PDOException $e) {
        $msg = "❌ Error: el correo ya existe o hubo un problema.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>

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
        <h2>Registro</h2>

        <?php if ($msg) echo "<p class='auth-msg'>$msg</p>"; ?>

        <form method="POST" class="auth-form">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <input type="email" name="email" placeholder="Correo" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" class="btn-primary">Registrar</button>
        </form>

        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>

</body>
</html>
