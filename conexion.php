<?php
$conexion = new mysqli(
    "bb2quyrjq72z3krsicwd-mysql.services.clever-cloud.com", // Host
    "u37xmonw2xgktd6m", // Usuario
    "RS3Gzn0LON0figDDExFP", // Contraseña
    "bb2quyrjq72z3krsicwd", // Nombre de la base de datos
    3306 // Puerto
);

// Comprobamos la conexión
if ($conexion->connect_error) {
    die("❌ Conexión fallida: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");

// echo "✅ Conexión exitosa a Clever Cloud MySQL";

?>
