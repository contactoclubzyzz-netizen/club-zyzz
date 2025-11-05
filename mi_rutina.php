<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

$query = "SELECT contenido FROM rutinas WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

$rutina = $result['contenido'] ?? 'No tienes una rutina generada aún.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi Rutina - CLUB ZYZZ</title>
<link rel="stylesheet" href="css/estilo_rutina.css">
</head>
<body>
<div class="container">
    <h1>🏋️ Tu Rutina Personalizada</h1>
    <pre><?= nl2br(htmlspecialchars($rutina)) ?></pre>
    <a href="panel_usuario.php" class="btn-primary">⬅ Volver al Panel</a>
</div>
</body>
</html>
