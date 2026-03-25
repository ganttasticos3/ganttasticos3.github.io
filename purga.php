<?php

require_once __DIR__ . "/php/lib/Bd.php";

$bd = Bd::pdo();

if (isset($_GET["accion"])) {

    if ($_GET["accion"] === "fcm") {
        $n = $bd->exec("DELETE FROM SUSCRIPCION WHERE SUS_ENDPOINT LIKE '%fcm.googleapis.com%'");
        echo "Eliminados FCM: $n<br>";
    }

    if ($_GET["accion"] === "wns") {
        $n = $bd->exec("DELETE FROM SUSCRIPCION WHERE SUS_ENDPOINT LIKE '%notify.windows.com%'");
        echo "Eliminados WNS: $n<br>";
    }

    if ($_GET["accion"] === "todo") {
        $n = $bd->exec("DELETE FROM SUSCRIPCION");
        echo "Eliminados TODOS: $n<br>";
    }
}

echo "<h2>Suscripciones actuales</h2>";

$stmt = $bd->query("SELECT SUS_ENDPOINT FROM SUSCRIPCION");

while ($row = $stmt->fetch()) {
    echo "<p>" . htmlspecialchars($row["SUS_ENDPOINT"]) . "</p>";
}
