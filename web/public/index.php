<?php
session_start();
include_once "../includes/db_connection.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Reservas eSports</title>
    <link rel="stylesheet" href="./css/styles.css">
</head>

<body>

<!-- ✅ HEADER -->
<header>
    <div class="logo">eSports Center</div>

    <nav>
        <a href="#">Inicio</a>
        <a href="#instalaciones">Instalaciones</a>
        <a href="reservas/reservar.php">Reservar</a>

        <?php if (isset($_SESSION["usuario_id"])): ?>
            <?php if ($_SESSION["rol"] === "admin"): ?>
                <a href="admin/panel.php">Panel Admin</a>
            <?php else: ?>
                <a href="usuario/panel.php">Panel Usuario</a>
            <?php endif; ?>
        <?php endif; ?>

        <a href="#contacto">Contacto</a>
    </nav>

    <?php if (!isset($_SESSION["usuario_id"])): ?>
        <a class="btn-login" href="login.php">Iniciar sesión</a>
    <?php else: ?>
        <a class="btn-login" href="logout.php">Salir</a>
    <?php endif; ?>
</header>

<!-- ✅ HERO -->
<section class="hero">
    <div class="hero-content">
        <h1>Reserva tu sala de eSports</h1>
        <p>Entrena o compite en una de nuestras instalaciones gaming profesionales.</p>
        <a class="btn-primary" href="reservas/reservar.php">Reserva ahora</a>
    </div>
</section>

<!-- ✅ SECCIÓN INSTALACIONES -->
<section class="instalaciones" id="instalaciones">
    <h2>Nuestras instalaciones</h2>

    <div class="instalaciones-grid">

    <?php
    try {
        $query = $pdo->query("SELECT * FROM instalaciones");

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            echo '
            <div class="inst-card">
                <img src="./imagenes/'.$row["imagen"].'" alt="'.$row["nombre"].'">
                <h3>'.$row["nombre"].'</h3>
                <p>'.$row["descripcion"].'</p>
            </div>
            ';
        }
    } catch (PDOException $e) {
        echo "<p>Error cargando instalaciones: " . $e->getMessage() . "</p>";
    }
    ?>

    </div>
</section>

<section class="contacto" id="contacto">

    <h2>Contacto</h2>

    <div class="contacto-contenido">

        <div class="contacto-info">
            <p><strong>eSports Center</strong></p>
            <p>Calle Gaming 23, Madrid</p>
            <p>+34 600 000 000</p>
            <p>info@esportscenter.com</p>
        </div>

        <form class="contacto-form">
            <input type="text" placeholder="Tu nombre" required>
            <input type="email" placeholder="Tu correo" required>
            <textarea placeholder="Tu mensaje" required></textarea>
            <button class="btn-primary" type="submit">Enviar</button>
        </form>

    </div>

</section>


</body>
</html>
