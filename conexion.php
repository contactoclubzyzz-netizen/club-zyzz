<?php
$conexion = new mysqli(
    "sql306.infinityfree.com", 
    "if0_40171034", 
    "zyzzfit1234", 
    "if0_40171034_club_zyzz_db"
);

if ($conexion->connect_error) {
    die("❌ Conexión fallida: " . $conexion->connect_error);
}

// Establecer charset utf8
$conexion->set_charset("utf8");
?>
