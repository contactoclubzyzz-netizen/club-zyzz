<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Borrar datos corporales del usuario
$query = "DELETE FROM datos_corporales WHERE usuario_id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->close();

// Redirigir al usuario para que ingrese sus datos de nuevo
header("Location: sexo.php");
exit();
