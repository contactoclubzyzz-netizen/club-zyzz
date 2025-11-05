<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'conexion.php'; // tu conexión a la DB

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $zonas = $_POST['zonas'] ?? [];
    $zonas_str = implode(',', $zonas);

    $stmt = $conexion->prepare("UPDATE datos_corporales SET zonas_objetivo = ? WHERE usuario_id = ?");
    $stmt->bind_param("si", $zonas_str, $usuario_id);
    $stmt->execute();
    $stmt->close();

    header("Location: objetivo.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CLUB ZYZZ - Zona Objetivo</title>
<link href="css/zona_objetivo.css" rel="stylesheet">
</head>
<body>

<h1>¿Cuál es tu zona objetivo?</h1>

<form method="POST" class="container" onsubmit="return validarZonas()">
    <button type="button" class="btn-zona" onclick="toggleZona(this,'Todo el cuerpo')">TODO EL CUERPO</button>
    <button type="button" class="btn-zona" onclick="toggleZona(this,'Brazos')">BRAZOS</button>
    <button type="button" class="btn-zona" onclick="toggleZona(this,'Pecho')">PECHO</button>
    <button type="button" class="btn-zona" onclick="toggleZona(this,'Abdominales')">ABDOMINALES</button>
    <button type="button" class="btn-zona" onclick="toggleZona(this,'Piernas')">PIERNAS</button>
    <input type="hidden" name="zonas[]" id="zonasInput">
    <button type="submit" class="continuar-btn">CONTINUAR</button>
</form>


<div class="footer">CLUB ZYZZ™ – Tú contra ti mismo 💪</div>

<script src="js/zona_objetivo.js"></script>
</body>
</html>
