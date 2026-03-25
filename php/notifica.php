<?php

require_once __DIR__ . "/lib/manejaErrores.php";
require_once __DIR__ . "/../vendor/autoload.php";
require_once  __DIR__ . "/lib/devuelveJson.php";
require_once  __DIR__ . "/Bd.php";
require_once __DIR__ . "/Suscripcion.php";
require_once __DIR__ . "/suscripcionElimina.php";

use Minishlink\WebPush\WebPush;

const AUTH = [
    "VAPID" => [
        "subject" => "https://ganttasticos3-github-io.onrender.com",
        "publicKey" => "BH3P1bFUPWjLhduRYdyax-ar70d_Rpn_plxlnKVwfOlGMX0apZlwMzZSyGGQkaGLfw7DFCGZH7akBfyYkMpw1t4",
        "privateKey" => "eW9FSObHt2uBdBca9KSrSd_Rq3w89KLsntwaS3t4qCg"
    ]
];

$webPush = new WebPush(AUTH);

$datos = json_decode(file_get_contents("php://input"), true);
$texto =  $datos["mensaje"] ?? "Mensaje vacío";

$mensaje = json_encode([
    "title" => "Ganttasticos 💻",
    "body" => $texto
]);

$bd = Bd::pdo();
$stmt = $bd->query("SELECT * FROM SUSCRIPCION");
$suscripciones =
    $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Suscripcion::class);

foreach ($suscripciones as $suscripcion) {
    $webPush->queueNotification($suscripcion, $mensaje);
}
$reportes = $webPush->flush();

// Genera el reporte de envio a cada suscripcion.
$reporteDeEnvios = "";
foreach ($reportes as $reporte) {
    $endpoint = $reporte->getRequest()->getUri();
    $htmlEndpoint = htmlentities($endpoint);
    if ($reporte->isSuccess()) {

        $reporteDeEnvios .= "<dt>$htmlEndpoint</dt><dd>Éxito</dd>";
    } else {

        $explicacion = $reporte->getReason();
        $statusCode = $reporte->getResponse()?->getStatusCode();

        // 🔥 eliminar tokens muertos o inválidos
        if (
            $reporte->isSubscriptionExpired() ||
            $statusCode === 410 ||
            $statusCode === 404 ||
            $statusCode === 401
        ) {
            suscripcionElimina($bd, $endpoint);
        }

        $explicacionHtml = htmlentities($explicacion);
        $reporteDeEnvios .= "<dt>$htmlEndpoint</dt><dd>Fallo: $explicacionHtml</dd>";
    }
}

devuelveJson(["reporte" => ["innerHTML" => $reporteDeEnvios]]);
