<?php
session_start();
include_once "../../includes/db_connection.php";

$zona = $_GET["zona"];
$asiento = $_GET["asiento"];
$fecha = $_GET["fecha"];
$hora_inicio = $_GET["hora_inicio"];
$duracion = floatval($_GET["duracion"]);

// Calcular hora_fin
$inicio = strtotime($hora_inicio);
$fin = $inicio + ($duracion * 3600);
$hora_fin = date("H:i", $fin);

// ✅ Buscar precio
$stmt = $pdo->prepare("SELECT precio FROM precios WHERE zona = ? AND tiempo = ?");
$stmt->execute([$zona, $duracion]);
$precio = $stmt->fetchColumn();

// ✅ Comprobar si hay una reserva que se solape
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM reservas
    WHERE zona = ?
    AND asiento = ?
    AND fecha = ?
    AND (
        (hora_inicio < ? AND hora_fin > ?) OR
        (hora_inicio < ? AND hora_fin > ?) OR
        (hora_inicio >= ? AND hora_fin <= ?)
    )
");

$stmt->execute([
    $zona,
    $asiento,
    $fecha,
    $hora_fin, $hora_inicio,
    $hora_fin, $hora_inicio,
    $hora_inicio, $hora_fin
]);

$ocupado = $stmt->fetchColumn() > 0;

// ✅ Respuesta
echo json_encode([
    "precio" => $precio,
    "ocupado" => $ocupado
]);