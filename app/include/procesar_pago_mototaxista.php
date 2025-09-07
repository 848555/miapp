<?php
include(__DIR__ . '../../../config/conexion.php');
session_start();

$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    die("Usuario no autenticado.");
}

if (!isset($_POST['id_solicitud'], $_POST['numero_nequi'])) {
    $_SESSION['mensaje'] = "Debes seleccionar una solicitud y colocar tu número Nequi.";
    header("Location: /app/pages/pagar_mototaxista.php");
    exit;
}

$id_solicitud = intval($_POST['id_solicitud']);
$numero_nequi = trim($_POST['numero_nequi']);

// Guardar número Nequi en tabla temporal
$stmt = $conexion->prepare("
    INSERT INTO numeros_nequi (id_usuario, numero) 
    VALUES (?, ?) 
    ON DUPLICATE KEY UPDATE numero = ?
");
$stmt->bind_param("iss", $id_usuario, $numero_nequi, $numero_nequi);
$stmt->execute();
$stmt->close();

// Consultar costo de la solicitud
$stmt = $conexion->prepare("
    SELECT costo_total 
    FROM solicitudes 
    WHERE id_solicitud = ? AND id_usuarios = ?
");
$stmt->bind_param("ii", $id_solicitud, $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $_SESSION['mensaje'] = "Solicitud no encontrada o no autorizada.";
    header("Location: /app/pages/pagar_mototaxista.php");
    exit;
}

$solicitud = $resultado->fetch_assoc();
$costo_total = intval($solicitud['costo_total']); // en pesos
$stmt->close();

// =======================
// INTEGRACIÓN CON NEQUI
// =======================

$clientId = "TU_CLIENT_ID";
$clientSecret = "TU_CLIENT_SECRET";
$apiKey = "TU_API_KEY";

// 1. Obtener token
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.sandbox.nequi.com/oauth/token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/x-www-form-urlencoded"
    ],
    CURLOPT_POSTFIELDS => http_build_query([
        "grant_type" => "client_credentials",
        "client_id" => $clientId,
        "client_secret" => $clientSecret
    ])
]);

$response = curl_exec($curl);
if(curl_errno($curl)){
    die("Error al generar token: " . curl_error($curl));
}
curl_close($curl);

$data = json_decode($response, true);
$accessToken = $data['access_token'] ?? null;
if(!$accessToken){
    die("No se pudo obtener token Nequi.");
}

// 2. Crear solicitud de cobro
$messageId = uniqid("pago_", true);

$body = [
    "RequestMessage" => [
        "RequestHeader" => [
            "Channel" => "APP",
            "RequestDate" => date("c"),
            "MessageID" => $messageId,
            "ClientID" => $clientId,
            "Destination" => [
                "ServiceName" => "PaymentsService",
                "ServiceOperation" => "CreatePayment",
                "ServiceRegion" => "C001",
                "ServiceVersion" => "1.2.0"
            ]
        ],
        "RequestBody" => [
            "any" => [
                "phoneNumber" => $numero_nequi,
                "code" => "1",
                "value" => strval($costo_total),
                "reference1" => "Solicitud #$id_solicitud",
                "reference2" => "Pago MotoTaxi",
                "reference3" => "MotoTaxiApp"
            ]
        ]
    ]
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.sandbox.nequi.com/payments/v2/-services-paymentservice-unregisteredpayment",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $accessToken",
        "x-api-key: $apiKey"
    ],
    CURLOPT_POSTFIELDS => json_encode($body)
]);

$response = curl_exec($curl);
if(curl_errno($curl)){
    die("Error al crear cobro: " . curl_error($curl));
}
curl_close($curl);

$resultado = json_decode($response, true);

// =======================
// VALIDAR RESPUESTA
// =======================
$statusCode = $resultado["ResponseMessage"]["ResponseHeader"]["Status"]["StatusCode"] ?? null;

if ($statusCode === "0") {
    // Cobro creado correctamente, guardar messageId en numeros_nequi
    $stmt = $conexion->prepare("
        UPDATE numeros_nequi 
        SET nequi_message_id = ? 
        WHERE id_usuario = ?
    ");
    $stmt->bind_param("si", $messageId, $id_usuario);
    $stmt->execute();
    $stmt->close();

    $_SESSION['mensaje'] = "Se envió la solicitud de pago a tu app Nequi. Debes aprobarla para completar el pago.";
} else {
    $_SESSION['mensaje'] = "Error al procesar el pago con Nequi. Intenta nuevamente.";
}

$conexion->close();
header("Location: /app/pages/pagar_mototaxista.php");
exit;
?>
