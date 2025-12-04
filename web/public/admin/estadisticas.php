<?php
session_start();
if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

include_once "../../includes/db_connection.php";

$totalUsuarios = 0;
$usuariosUltimoMes = 0;
$totalReservas = 0;
$reservasActivas = 0;
$reservasCanceladas = 0;
$ingresosTotales = 0;
$ingresosMes = 0;
$reservasPorZona = [];
$reservasProximosDias = [];

// ✅ Filtros de fecha (GET)
$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;

// Normalizar cadenas vacías
if ($desde === '') $desde = null;
if ($hasta === '') $hasta = null;

// Condición común para filtrar por fecha en algunas consultas
$condicionFecha = "";
if ($desde && $hasta) {
    $condicionFecha = " AND fecha BETWEEN '$desde' AND '$hasta' ";
}

try {
    // Total usuarios (sin filtro)
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    $totalUsuarios = $stmt->fetchColumn();

    // Usuarios creados en los últimos 30 días (sin filtro)
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM usuarios 
        WHERE creado_en >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $usuariosUltimoMes = $stmt->fetchColumn();

    // Total reservas (sin filtro)
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservas");
    $totalReservas = $stmt->fetchColumn();

    // Reservas activas (sin filtro)
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado = 'activa'");
    $reservasActivas = $stmt->fetchColumn();

    // Reservas canceladas (sin filtro)
    $stmt = $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado = 'cancelada'");
    $reservasCanceladas = $stmt->fetchColumn();

    // Ingresos totales (solo reservas activas, sin filtro)
    $stmt = $pdo->query("SELECT SUM(precio) FROM reservas WHERE estado = 'activa'");
    $ingresosTotales = $stmt->fetchColumn() ?? 0;

    // Ingresos este mes (sin filtro)
    $stmt = $pdo->query("
        SELECT SUM(precio) 
        FROM reservas 
        WHERE estado = 'activa'
        AND fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    ");
    $ingresosMes = $stmt->fetchColumn() ?? 0;

    // ✅ Reservas por zona (AFECTADAS POR FILTRO)
    $stmt = $pdo->query("
        SELECT zona, COUNT(*) AS total, SUM(precio) AS ingresos
        FROM reservas
        WHERE 1=1 $condicionFecha
        GROUP BY zona
        ORDER BY total DESC
    ");
    $reservasPorZona = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Reservas por día (gráfica y tabla)
    if ($desde && $hasta) {
        // Si hay filtro, usar el rango elegido
        $stmt = $pdo->query("
            SELECT fecha, COUNT(*) AS total
            FROM reservas
            WHERE fecha BETWEEN '$desde' AND '$hasta'
            GROUP BY fecha
            ORDER BY fecha ASC
        ");
    } else {
        // Si no hay filtro, próximos 7 días (comportamiento original)
        $stmt = $pdo->query("
            SELECT fecha, COUNT(*) AS total
            FROM reservas
            WHERE fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            GROUP BY fecha
            ORDER BY fecha ASC
        ");
    }
    $reservasProximosDias = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $errorMsg = "Error cargando estadísticas: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body>

<div class="panel-container">
    <h1>Estadísticas generales</h1>

    <!-- ✅ Filtro de fechas -->
    <form method="GET" class="filtro-fechas">
        <label>Desde:</label>
        <input type="date" name="desde" value="<?= htmlspecialchars($_GET['desde'] ?? '') ?>">

        <label>Hasta:</label>
        <input type="date" name="hasta" value="<?= htmlspecialchars($_GET['hasta'] ?? '') ?>">

        <button type="submit" class="btn-primary">Filtrar</button>

        <?php if ($desde || $hasta): ?>
            <a href="estadisticas.php" class="btn-small" style="margin-left:10px;">Quitar filtros</a>
        <?php endif; ?>
    </form>

    <p>Resumen del estado actual del centro eSports.</p>

    <?php if (isset($errorMsg)): ?>
        <p style="color:red;"><?= $errorMsg ?></p>
    <?php endif; ?>

    <!-- 🔹 Tarjetas resumen (sin filtro, visión global) -->
    <div class="stats-grid">

        <div class="stat-card">
            <h3>Usuarios totales</h3>
            <p class="stat-number"><?= $totalUsuarios ?></p>
            <span class="stat-sub">Altas últimos 30 días: <?= $usuariosUltimoMes ?></span>
        </div>

        <div class="stat-card">
            <h3>Reservas totales</h3>
            <p class="stat-number"><?= $totalReservas ?></p>
            <span class="stat-sub">Activas: <?= $reservasActivas ?> · Canceladas: <?= $reservasCanceladas ?></span>
        </div>

        <div class="stat-card">
            <h3>Ingresos totales</h3>
            <p class="stat-number"><?= number_format($ingresosTotales, 2) ?> €</p>
            <span class="stat-sub">Este mes: <?= number_format($ingresosMes, 2) ?> €</span>
        </div>

    </div>

    <!-- GRÁFICA: Reservas por zona -->
    <h2>Gráfica: Reservas por zona</h2>
    <canvas id="chartReservasZona" width="400" height="180"></canvas>

    <!-- GRÁFICA: Reservas próximos días (o rango filtrado) -->
    <h2>Gráfica: Reservas por día</h2>
    <canvas id="chartReservasDias" width="400" height="180"></canvas>

    <!-- GRÁFICA: Ingresos por zona -->
    <h2>Gráfica: Ingresos por zona</h2>
    <canvas id="chartIngresosZona" width="400" height="180"></canvas>

    <br>
    <a class="btn-small" href="panel.php">Volver</a>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Pasar datos PHP → JS -->
<script>
const reservasZonaLabels = <?= json_encode(array_column($reservasPorZona, "zona")) ?>;
const reservasZonaData   = <?= json_encode(array_column($reservasPorZona, "total")) ?>;
const ingresosZonaData   = <?= json_encode(array_column($reservasPorZona, "ingresos")) ?>;

const diasLabels = <?= json_encode(array_column($reservasProximosDias, "fecha")) ?>;
const diasData   = <?= json_encode(array_column($reservasProximosDias, "total")) ?>;
</script>

<!-- Gráficas -->
<script>
// 1️⃣ Gráfica de Barras: Reservas por zona
new Chart(document.getElementById('chartReservasZona'), {
    type: 'bar',
    data: {
        labels: reservasZonaLabels,
        datasets: [{
            label: 'Reservas',
            data: reservasZonaData,
            backgroundColor: 'rgba(79, 195, 247, 0.6)',
            borderColor: 'rgba(79, 195, 247, 1)',
            borderWidth: 2
        }]
    }
});

// 2️⃣ Gráfica de Línea: Reservas por día (según filtro o próximos 7 días)
new Chart(document.getElementById('chartReservasDias'), {
    type: 'line',
    data: {
        labels: diasLabels,
        datasets: [{
            label: 'Reservas',
            data: diasData,
            fill: false,
            borderColor: 'rgba(142, 36, 170, 1)',
            borderWidth: 2,
            tension: 0.3
        }]
    }
});

// 3️⃣ Gráfica de Pastel: Ingresos por zona
new Chart(document.getElementById('chartIngresosZona'), {
    type: 'pie',
    data: {
        labels: reservasZonaLabels,
        datasets: [{
            data: ingresosZonaData,
            backgroundColor: [
                'rgba(79, 195, 247, 0.7)',
                'rgba(142, 36, 170, 0.7)',
                'rgba(255, 99, 132, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)'
            ]
        }]
    }
});
</script>

</body>
</html>