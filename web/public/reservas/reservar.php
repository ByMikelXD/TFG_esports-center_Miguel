<?php
session_start();
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../login.php");
    exit;
}

include_once "../../includes/db_connection.php";

$msg = "";

// ✅ Cargar tabla de precios
$prices = [];
$stmt = $pdo->query("SELECT zona, tiempo, precio FROM precios");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $prices[$row['zona']][] = $row;
}

// ✅ Cuando se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $zona        = $_POST["zona"];
    $asiento     = $_POST["asiento"];
    $fecha       = $_POST["fecha"];
    $hora_inicio = $_POST["hora_inicio"];
    $duracion    = floatval($_POST["duracion"]);
    $usuario_id  = $_SESSION["usuario_id"];

    // ✅ Calcular hora fin
    $inicio  = strtotime($hora_inicio);
    $fin     = $inicio + ($duracion * 3600);
    $hora_fin = date("H:i", $fin);

    // ✅ Obtener precio
    $stmt = $pdo->prepare("
        SELECT precio 
        FROM precios 
        WHERE zona = ?
        AND tiempo = ?
    ");
    $stmt->execute([$zona, $duracion]);
    $precio = $stmt->fetchColumn();

    if (!$precio) {
        $msg = "❌ No existe tarifa para esta duración.";
    } else {

        // ✅ Insertar reserva (incluyendo estado y creado_en)
        $query = $pdo->prepare("
            INSERT INTO reservas (usuario_id, zona, asiento, fecha, hora_inicio, hora_fin, precio, creado_en, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'activa')
        ");

        $query->execute([
            $usuario_id,
            $zona,
            $asiento,
            $fecha,
            $hora_inicio,
            $hora_fin,
            $precio
        ]);

        // ✅ ID de la reserva recién creada
        $reserva_id = $pdo->lastInsertId();

        // ✅ Redirigir a la página de confirmación
        header("Location: reserva_confirmada.php?id=" . $reserva_id);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar</title>
    <link rel="stylesheet" href="../css/styles.css">

    <style>
        .map-container {
            text-align: center;
            margin-top: 120px;
        }

        .plano-wrapper {
            position: relative;
            display: inline-block;
            max-width: 900px;
            width: 100%;
        }

        .map-img {
            width: 100%;
            border: 2px solid var(--violeta);
            border-radius: 10px;
            display: block;
        }

        /* Capa encima del plano donde van los asientos clicables */
        #seat-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none; /* se habilita en los asientos */
        }

        .seat-point {
            position: absolute;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background-color: #4CAF50; /* libre: verde */
            border: 2px solid #000;
            cursor: pointer;
            pointer-events: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
            color: #fff;
            box-shadow: 0 0 6px rgba(0,0,0,0.6);
            transition: transform 0.15s ease, background-color 0.15s ease;
        }

        .seat-point:hover {
            transform: scale(1.15);
        }

        .seat-point.seleccionado {
            background-color: #FDD835; /* amarillo */
            color: #000;
        }

        .seat-point.ocupado {
            background-color: #E53935; /* rojo */
            cursor: not-allowed;
        }

        .map-legend {
            margin-top: 10px;
            font-size: 0.9rem;
        }

        .map-legend span {
            display: inline-block;
            width: 16px;
            height: 16px;
            margin: 0 4px;
            border-radius: 50%;
            vertical-align: middle;
        }

        .legend-libre { background: #4CAF50; }
        .legend-ocupado { background: #E53935; }
        .legend-sel { background: #FDD835; }

        .form-box {
            margin: 30px auto;
            width: 400px;
            padding: 25px;
            background: #222;
            border-radius: 10px;
        }

        .form-box label {
            display: block;
            margin: 8px 0;
        }

        .form-box input,
        .form-box select {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            margin-bottom: 10px;
            border: none;
            background: #333;
            color: #fff;
        }

        .btn-primary {
            width: 100%;
            margin-top: 10px;
        }

        .msg-box {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="map-container">

    <h1>Reservar tu puesto</h1>

    <?php if ($msg): ?>
        <p class="msg-box" style="color: var(--azul-claro);"><?= $msg ?></p>
    <?php endif; ?>

    <!-- ✅ Plano con capa de asientos encima -->
    <div class="plano-wrapper">
        <img src="../imagenes/plano.png" class="map-img" alt="Plano de la sala">
        <div id="seat-layer"></div>
    </div>

    <p class="map-legend">
        <span class="legend-libre"></span> Libre
        <span class="legend-ocupado"></span> Ocupado
        <span class="legend-sel"></span> Seleccionado
    </p>

    <!-- ✅ FORMULARIO -->
    <form class="form-box" method="POST">

        <label>Zona de juego</label>
        <select id="zona" name="zona" required>
            <?php foreach ($prices as $zone => $options): ?>
                <option value="<?= $zone ?>"><?= $zone ?></option>
            <?php endforeach; ?>
        </select>

        <label>Nº de asiento</label>
        <input id="asiento" type="number" name="asiento" min="1" required>

        <label>Fecha</label>
        <input id="fecha" type="date" name="fecha" required>

        <label>Hora de inicio</label>
        <input id="hora_inicio" type="time" name="hora_inicio" required>

        <label>Duración</label>
        <select id="duracion" name="duracion" required></select>

        <p id="statusMsg" class="msg-box"></p>

        <button id="btnReservar" class="btn-primary" type="submit">Reservar</button>
    </form>

    <a class="btn-small" href="../usuario/panel.php">Volver</a>
</div>

<script>
    // Datos de precios desde PHP
    const priceData = <?= json_encode($prices) ?>;

    const zonaSelect      = document.getElementById("zona");
    const asientoInput    = document.getElementById("asiento");
    const fechaInput      = document.getElementById("fecha");
    const horaInicioInput = document.getElementById("hora_inicio");
    const duracionSelect  = document.getElementById("duracion");
    const statusMsg       = document.getElementById("statusMsg");
    const btnReservar     = document.getElementById("btnReservar");
    const seatLayer       = document.getElementById("seat-layer");

    // 🔹 Configuración de asientos sobre el plano
    // top / left son PORCENTAJES aproximados sobre la imagen.
    // Ajusta a tu gusto moviendo un poco los valores.
    const seatsByZone = {
        // OPEN incluye PS5 + OPEN arriba + OPEN abajo
        "OPEN": [
            // PS5 ZONE (4)
            { num: 1,  top: 12, left: 5  },
            { num: 2,  top: 12, left: 10  },
            { num: 3,  top: 12, left: 19 },
            { num: 4,  top: 12, left: 23 },

            // OPEN ZONE arriba (5)
            { num: 5,  top: 8,  left: 35 },
            { num: 6,  top: 8,  left: 41 },
            { num: 7,  top: 8,  left: 46 },
            { num: 8,  top: 8,  left: 52 },
            { num: 9,  top: 8,  left: 57 },

            // OPEN ZONE abajo (10)
            { num: 10, top: 79, left: 4 },
            { num: 11, top: 79, left: 10 },
            { num: 12, top: 79, left: 15 },
            { num: 13, top: 79, left: 20 },
            { num: 14, top: 79, left: 26 },
            { num: 15, top: 88, left: 4 },
            { num: 16, top: 88, left: 10 },
            { num: 17, top: 88, left: 15 },
            { num: 18, top: 88, left: 20 },
            { num: 19, top: 88, left: 26 }
        ],

        "SIMUFY": [
            { num: 1, top: 38, left: 5 },
            { num: 2, top: 47, left: 5 },
            { num: 3, top: 47, left: 13 }
        ],

        "BATTLE": [
            // BATTLE central (4)
            { num: 1, top: 27, left: 31 },
            { num: 2, top: 27, left: 37 },
            { num: 3, top: 27, left: 43 },
            { num: 4, top: 38, left: 31 },
            // BATTLE abajo (6)
            { num: 5, top: 78, left: 60 },
            { num: 6, top: 78, left: 66 },
            { num: 7, top: 78, left: 72 },
            { num: 8, top: 78, left: 77 },
            { num: 9, top: 78, left: 83 },
            { num: 10, top: 89, left: 60 },
            { num: 11, top: 89, left: 66 },
            { num: 12, top: 89, left: 72 },
            { num: 13, top: 89, left: 77 },
            { num: 14, top: 89, left: 83 }
        ],

        "ASUS": [
            { num: 1, top: 10, left: 73 },
            { num: 2, top: 19, left: 73 },
            { num: 3, top: 30, left: 87 },
            { num: 4, top: 20, left: 90 },
            { num: 5, top: 10, left: 89 }
        ]
    };

    // ✅ Actualizar selector de duración según zona
    function updateDurations() {
        const zone = zonaSelect.value;
        duracionSelect.innerHTML = "";

        priceData[zone].forEach(t => {
            let label = t.tiempo + "h";
            if (t.tiempo == 24) label = "Day Pass";
            if (t.tiempo == 0.25) label = "15 min";
            if (t.tiempo == 0.50) label = "30 min";

            const opt = document.createElement("option");
            opt.value = t.tiempo;
            opt.textContent = label;
            duracionSelect.appendChild(opt);
        });
    }

    zonaSelect.addEventListener("change", () => {
        updateDurations();
        asientoInput.value = "";
        statusMsg.textContent = "";
        btnReservar.disabled = false;
        renderSeats();
    });

    updateDurations();

    // ✅ Consultar precio + disponibilidad (usado también por el mapa)
    async function checkReserva(returnData = false) {
        const zona        = zonaSelect.value;
        const asiento     = asientoInput.value;
        const fecha       = fechaInput.value;
        const hora_inicio = horaInicioInput.value;
        const duracion    = duracionSelect.value;

        if (!zona || !asiento || !fecha || !hora_inicio || !duracion) {
            statusMsg.textContent = "";
            btnReservar.disabled = false;
            if (returnData) return null;
            return;
        }

        const res = await fetch(
            `check.php?zona=${encodeURIComponent(zona)}&asiento=${encodeURIComponent(asiento)}&fecha=${encodeURIComponent(fecha)}&hora_inicio=${encodeURIComponent(hora_inicio)}&duracion=${encodeURIComponent(duracion)}`
        );
        const data = await res.json();

        if (data.ocupado) {
            statusMsg.style.color = "red";
            statusMsg.textContent = "❌ Este asiento ya está reservado en ese horario";
            btnReservar.disabled = true;
        } else {
            statusMsg.style.color = "var(--azul-claro)";
            statusMsg.textContent = `✅ Disponible — Precio: ${data.precio} €`;
            btnReservar.disabled = false;
        }

        if (returnData) return data;
    }

    ["asiento", "fecha", "hora_inicio", "duracion"].forEach(id => {
        document.getElementById(id).addEventListener("change", () => checkReserva(false));
    });

    // ✅ Pintar los asientos encima del plano según la zona
    function renderSeats() {
        seatLayer.innerHTML = "";
        const zona = zonaSelect.value;
        const seats = seatsByZone[zona] || [];

        seats.forEach(seat => {
            const div = document.createElement("div");
            div.classList.add("seat-point");
            div.style.top  = seat.top  + "%";
            div.style.left = seat.left + "%";
            div.textContent = seat.num;
            div.dataset.num = seat.num;

            div.addEventListener("click", () => seleccionarAsiento(seat.num, div));

            seatLayer.appendChild(div);
        });
    }

    async function seleccionarAsiento(num, div) {
        asientoInput.value = num;

        // Quitar selección previa
        document.querySelectorAll(".seat-point").forEach(s => {
            s.classList.remove("seleccionado");
        });

        div.classList.add("seleccionado");

        const data = await checkReserva(true);

        if (data && data.ocupado) {
            // Si está ocupado, marcarlo rojo
            div.classList.remove("seleccionado");
            div.classList.add("ocupado");
            asientoInput.value = "";
        }
    }

    // Primera renderización
    renderSeats();
</script>

</body>
</html>
