<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $altura = $_POST['altura'] ?? '';
    $peso = $_POST['peso'] ?? '';
    $edad = $_POST['edad'] ?? '';

    if (!$altura || !$peso || !$edad) {
        $mensaje = "⚠️ Por favor ingresa todos los datos.";
    } else {
        // Verificamos si ya hay un registro existente
        $consulta = $conexion->prepare("SELECT id FROM datos_corporales WHERE usuario_id = ?");
        $consulta->bind_param("i", $usuario_id);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if ($resultado->num_rows > 0) {
            // Si existe, actualizamos
            $stmt = $conexion->prepare("UPDATE datos_corporales SET altura=?, peso=?, edad=? WHERE usuario_id=?");
            $stmt->bind_param("ddii", $altura, $peso, $edad, $usuario_id);
        } else {
            // Si no existe, insertamos
            $stmt = $conexion->prepare("INSERT INTO datos_corporales (usuario_id, altura, peso, edad) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iddi", $usuario_id, $altura, $peso, $edad);
        }

        if ($stmt->execute()) {
            header("Location: zona_objetivo.php");
            exit();
        } else {
            $mensaje = "❌ Error al guardar los datos.";
        }

        $stmt->close();
        $consulta->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CLUB ZYZZ - Datos Corporales</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilo_usuario.css">
</head>
<body>
  <h1>Ingresa tus datos corporales</h1>

  <?php if($mensaje): ?>
    <p style="color: #ff5555; text-align: center; margin-bottom: 20px;"><?php echo $mensaje; ?></p>
  <?php endif; ?>

  <form class="formulario" method="POST" action="">
    <div class="campo">
      <label for="altura">📏 Altura (cm)</label>
      <input type="number" id="altura" name="altura" placeholder="Ej: 175" required>
    </div>

    <div class="campo">
      <label for="peso">⚖️ Peso (kg)</label>
      <input type="number" id="peso" name="peso" placeholder="Ej: 70" required>
    </div>

    <div class="campo">
      <label for="edad">🎂 Edad</label>
      <input type="number" id="edad" name="edad" placeholder="Ej: 25" min="10" max="100" required>
    </div>

    <button class="continuar-btn" type="submit">CONTINUAR</button>
  </form>

  <div class="footer">CLUB ZYZZ™ – Tú contra ti mismo 💪</div>
</body>
</html>

