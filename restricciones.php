<?php
session_start();
require 'conexion.php'; // Asegúrate que aquí se define $conexion

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    $restricciones_seleccionadas = isset($_POST['restricciones']) ? array_filter($_POST['restricciones']) : [];

    if (empty($restricciones_seleccionadas)) {
        $error = "⚠️ Por favor, selecciona o escribe al menos una restricción.";
    } else {
        $restricciones = implode(", ", $restricciones_seleccionadas);

        // Guardar en la tabla datos_corporales
        $sql = "UPDATE datos_corporales SET restricciones = ? WHERE usuario_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $restricciones, $usuario_id);
        $stmt->execute();

        // Redirigir a la siguiente página
        header("Location: actividad.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CLUB ZYZZ - Restricciones</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/restrincciones.css">
</head>
<body>
  <h1>Restricciones de entrenamiento</h1>
  <p>Selecciona si tienes alguna condición para adaptar tu rutina:</p>

  <?php if (!empty($error)): ?>
      <p style="color:red; font-weight:bold; text-align:center;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" action="" class="form-restricciones">
    <div class="opciones">
      <label class="opcion-btn">
        <input type="checkbox" name="restricciones[]" value="Lesión en rodilla">
        ☝️ Lesión en rodilla
      </label>
      <label class="opcion-btn">
        <input type="checkbox" name="restricciones[]" value="Lesión en espalda">
        ✌️ Lesión en espalda
      </label>
      <label class="opcion-btn">
        <input type="checkbox" name="restricciones[]" value="Lesión en hombros">
        👍 Lesión en hombros
      </label>
      <label class="opcion-btn">
        <input type="checkbox" name="restricciones[]" value="Problemas cardíacos">
        ❤️ Problemas cardíacos
      </label>
      <label class="opcion-btn">
        <input type="checkbox" name="restricciones[]" value="Problemas respiratorios">
        😮‍💨 Problemas respiratorios
      </label>
    </div>

    <input type="text" name="restricciones[]" placeholder="Otra restricción..." class="otra-restriccion" />

    <button type="submit" class="continuar-btn">CONTINUAR</button>
  </form>

  <div class="footer">CLUB ZYZZ™ – Tú contra ti mismo 💪</div>
</body>
</html>
