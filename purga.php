<?php

require_once __DIR__ . "/Bd.php";

$bd = Bd::pdo();

// 🔥 BORRA TODO
$bd->exec("DELETE FROM SUSCRIPCION");

echo "Base de datos purgada";