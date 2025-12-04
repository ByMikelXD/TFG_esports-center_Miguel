<?php
// Conexión a la base de datos
$servername = "db";
$username = "user";
$password = "userpassword";
$dbname = "esports";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

echo "<h1>¡Docker funciona!</h1>";
echo "<p>Conexión a la base de datos MySQL exitosa.</p>";

$conn->close();
?>