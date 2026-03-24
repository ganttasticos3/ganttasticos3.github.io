<?php

require_once __DIR__ . "/php/lib/Bd.php";

$bd = Bd::pdo();

$bd->exec("DELETE FROM SUSCRIPCION");

echo "Base de datos purgada 🔥";
