<?php
session_start();
require 'conexion.php'; // usa $conexion

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nivel = $_POST['nivel'] ?? '';
    $usuario_id = $_SESSION['usuario_id'];

    // Validación: no dejar continuar si no seleccionó nada
    if (empty($nivel)) {
        $error = "⚠️ Por favor, selecciona tu nivel antes de continuar.";
    } else {
        $stmt = $conexion->prepare("UPDATE datos_corporales SET nivel_entrenamiento = ? WHERE usuario_id = ?");
        $stmt->bind_param("si", $nivel, $usuario_id);
        $stmt->execute();

        header("Location: flexiones.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CLUB ZYZZ - Nivel de Entrenamiento</title>
  <link rel="stylesheet" href="css/nivel_entrenamiento.css">
</head>
<body>
  <h1>¿Cuál es tu nivel actual de entrenamiento?</h1>

  <?php if(!empty($error)): ?>
    <p style="color:red; font-weight:bold; text-align:center;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="container">
      <div class="opcion" onclick="document.getElementById('nivelInput').value='Principiante'; this.classList.add('selected');">
        🐣 PRINCIPIANTE
      </div>
      <div class="opcion" onclick="document.getElementById('nivelInput').value='Intermedio'; this.classList.add('selected');">
        🏋️ INTERMEDIO
      </div>
      <div class="opcion" onclick="document.getElementById('nivelInput').value='Avanzado'; this.classList.add('selected');">
        🔥 AVANZADO
      </div>
    </div>

    <input type="hidden" name="nivel" id="nivelInput" value="<?= isset($_POST['nivel']) ? htmlspecialchars($_POST['nivel']) : '' ?>">

    <button type="submit" class="continuar-btn">CONTINUAR</button>
  </form>

  <div class="footer">CLUB ZYZZ™ – Tú contra ti mismo 💪</div>
</body>
</html>

