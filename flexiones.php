<?php
session_start();
require 'conexion.php'; // ⚠️ Asegúrate que en conexion.php tu variable sea $conexion

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nivel_flexiones = $_POST['nivel_flexiones'] ?? '';
    $usuario_id = $_SESSION['usuario_id'];

    // Validación: que no pueda continuar sin seleccionar
    if (empty($nivel_flexiones)) {
        $error = "⚠️ Por favor, selecciona tu nivel de flexiones antes de continuar.";
    } else {
        // Actualizar nivel de flexiones en la tabla datos_corporales
        $sql = "UPDATE datos_corporales SET nivel_flexiones = ? WHERE usuario_id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $nivel_flexiones, $usuario_id);
        $stmt->execute();

        // Redirigir a la siguiente página
        header("Location: restricciones.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CLUB ZYZZ - Flexiones</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/flexiones.css">
</head>
<body>
  <h1>¿Cuántas flexiones puedes hacer seguidas?</h1>

  <?php if(!empty($error)): ?>
    <p style="color:red; font-weight:bold; text-align:center;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="opciones">
      <div class="opcion" onclick="document.getElementById('nivelInput').value='Principiante'; this.classList.add('selected');">
        ☝️ <b>Principiante:</b> 3-5 flexiones
      </div>
      <div class="opcion" onclick="document.getElementById('nivelInput').value='Intermedio'; this.classList.add('selected');">
        ✌️ <b>Intermedio:</b> 5-10 flexiones
      </div>
      <div class="opcion" onclick="document.getElementById('nivelInput').value='Avanzado'; this.classList.add('selected');">
        👍 <b>Avanzado:</b> Al menos 10
      </div>
    </div>

    <input type="hidden" name="nivel_flexiones" id="nivelInput" value="<?= isset($_POST['nivel_flexiones']) ? htmlspecialchars($_POST['nivel_flexiones']) : '' ?>">

    <button type="submit" class="continuar-btn">CONTINUAR</button>
  </form>

  <div class="footer">CLUB ZYZZ™ – Tú contra ti mismo 💪</div>
</body>
</html>
