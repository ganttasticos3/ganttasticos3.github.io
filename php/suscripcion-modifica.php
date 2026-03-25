<?php
header("Access-Control-Allow-Origin: https://ganttasticos3.github.io");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Si sale error 500, el problema está en una de estas líneas:
require_once __DIR__ . "/lib/manejaErrores.php";
require_once __DIR__ . "/lib/devuelveCreated.php";
require_once __DIR__ . "/lib/devuelveJson.php";
require_once __DIR__ . "/Bd.php";
require_once __DIR__ . "/recibeSuscripcion.php";

$modelo = recibeSuscripcion();

$bd = Bd::pdo();

$stmt = $bd->prepare("SELECT * FROM SUSCRIPCION WHERE SUS_ENDPOINT = :SUS_ENDPOINT");
$stmt->execute([":SUS_ENDPOINT" => $modelo["SUS_ENDPOINT"]]);
$anterior = $stmt->fetch(PDO::FETCH_ASSOC);

if ($anterior === false) {

 $stmt = $bd->prepare(
  "INSERT INTO SUSCRIPCION (
    SUS_ENDPOINT, SUS_PUB_KEY, SUS_AUT_TOK, SUS_CONT_ENCOD
   ) values (
    :SUS_ENDPOINT, :SUS_PUB_KEY, :SUS_AUT_TOK, :SUS_CONT_ENCOD
   )"
 );
 $stmt->execute([
  ":SUS_ENDPOINT" => $modelo["SUS_ENDPOINT"],
  ":SUS_PUB_KEY" => $modelo["SUS_PUB_KEY"],
  ":SUS_AUT_TOK" => $modelo["SUS_AUT_TOK"],
  ":SUS_CONT_ENCOD" => $modelo["SUS_CONT_ENCOD"],
 ]);

 devuelveCreated("", $modelo);
} else {

 $stmt = $bd->prepare(
  "UPDATE SUSCRIPCION
   SET
    SUS_PUB_KEY = :SUS_PUB_KEY,
    SUS_AUT_TOK = :SUS_AUT_TOK,
    SUS_CONT_ENCOD = :SUS_CONT_ENCOD
   WHERE
    SUS_ENDPOINT = :SUS_ENDPOINT"
 );
 $stmt->execute([
  ":SUS_PUB_KEY" => $modelo["SUS_PUB_KEY"],
  ":SUS_AUT_TOK" => $modelo["SUS_AUT_TOK"],
  ":SUS_CONT_ENCOD" => $modelo["SUS_CONT_ENCOD"],
  ":SUS_ENDPOINT" => $modelo["SUS_ENDPOINT"],
 ]);

 devuelveJson($modelo);
}