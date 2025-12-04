<?php
// ✅ Iniciamos buffer de salida para que nada se imprima antes del PDF
ob_start();

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../login.php");
    exit;
}

include_once "../../includes/db_connection.php";
require_once "../../includes/fpdf/fpdf.php";
require_once "../../includes/phpqrcode/qrlib.php"; // ✅ librería QR

if (!isset($_GET["id"])) {
    die("Reserva no especificada.");
}

$reserva_id = (int) $_GET["id"];
$usuario_id = $_SESSION["usuario_id"];

// Cargar datos de la reserva + usuario
$stmt = $pdo->prepare("
    SELECT r.*, u.nombre, u.email
    FROM reservas r
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.id = ? AND r.usuario_id = ?
");
$stmt->execute([$reserva_id, $usuario_id]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    die("Reserva no encontrada o no tienes permiso para verla.");
}

// ✅ Función helper para convertir texto a ISO-8859-1 (lo que usa FPDF por defecto)
function pdf_text($txt) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $txt);
}

// ✅ Generar texto del QR
$qrTexto = "Reserva ID: {$reserva['id']}\n".
           "Nombre: {$reserva['nombre']}\n".
           "Zona: {$reserva['zona']}\n".
           "Asiento: {$reserva['asiento']}\n".
           "Fecha: {$reserva['fecha']}\n".
           "Hora: ".substr($reserva['hora_inicio'],0,5)." - ".substr($reserva['hora_fin'],0,5);

// ✅ Carpeta temporal para guardar QR
$qrDir = "../../tempqr/";
if (!file_exists($qrDir)) {
    mkdir($qrDir, 0777, true);
}

$qrFile = $qrDir . "qr_reserva_" . $reserva["id"] . ".png";

// Generar QR en fichero (no en salida)
QRcode::png($qrTexto, $qrFile, QR_ECLEVEL_M, 4, 2);

class PDF extends FPDF {}

$pdf = new PDF();
$pdf->AddPage();

// ✅ LOGO (esquina superior izquierda)
$logoPath = '../../imagenes/logo_esports.png'; // pon aquí el nombre real de tu logo
if (file_exists($logoPath)) {
    // x=10, y=8, width=30 mm
    $pdf->Image($logoPath, 10, 8, 30);
}

// Título
$pdf->SetFont('Arial', 'B', 16);
$pdf->Ln(5);
$pdf->Cell(0, 10, pdf_text('Ticket de Reserva - eSports Center'), 0, 1, 'C');
$pdf->Ln(5);

// Datos del usuario
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, pdf_text('Nombre: ' . $reserva["nombre"]), 0, 1);
$pdf->Cell(0, 8, 'Email: ' . $reserva["email"], 0, 1);
$pdf->Ln(3);

// Datos de la reserva
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, pdf_text('Datos de la reserva'), 0, 1);
$pdf->SetFont('Arial', '', 12);

$pdf->Cell(0, 8, 'ID Reserva: ' . $reserva["id"], 0, 1);
$pdf->Cell(0, 8, 'Zona: ' . pdf_text($reserva["zona"]), 0, 1);
$pdf->Cell(0, 8, 'Asiento: ' . $reserva["asiento"], 0, 1);
$pdf->Cell(0, 8, 'Fecha: ' . $reserva["fecha"], 0, 1);
$pdf->Cell(0, 8, 'Hora inicio: ' . substr($reserva["hora_inicio"], 0, 5), 0, 1);
$pdf->Cell(0, 8, 'Hora fin: ' . substr($reserva["hora_fin"], 0, 5), 0, 1);
$pdf->Cell(0, 8, 'Precio: ' . number_format($reserva["precio"], 2) . ' EUR', 0, 1);
$pdf->Cell(0, 8, 'Estado: ' . pdf_text($reserva["estado"]), 0, 1);

$pdf->Ln(10);
$pdf->MultiCell(0, 6, pdf_text(
    "Por favor, muestra este ticket en la entrada del centro eSports. ".
    "Si llegas tarde más de 10 minutos, la reserva puede ser cancelada."
));

$pdf->Ln(10);
$pdf->Cell(0, 8, pdf_text('Gracias por reservar en eSports Center.'), 0, 1);

// ✅ Insertar QR en el PDF (esquina derecha)
if (file_exists($qrFile)) {
    // x=150, y=60, width=40 mm
    $pdf->Image($qrFile, 150, 60, 40, 40);
    $pdf->SetXY(150, 102);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(40, 6, pdf_text('Escanea para verificar'), 0, 1, 'C');
}

// ✅ Limpiamos cualquier cosa que se haya podido volcar antes (avisos, espacios, etc.)
ob_end_clean();

// Descargar el PDF
$nombreArchivo = "ticket_reserva_" . $reserva["id"] . ".pdf";
$pdf->Output('D', $nombreArchivo);

// Opcional: borrar QR temporal
if (file_exists($qrFile)) {
    unlink($qrFile);
}

exit;
