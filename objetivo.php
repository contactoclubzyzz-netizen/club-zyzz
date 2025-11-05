<?php
session_start();

// Conexión a la base de datos
$host = "sql306.infinityfree.com";
$usuario = "if0_40171034";
$clave = "zyzzfit1234";
$basededatos = "if0_40171034_club_zyzz_db";

$conexion = new mysqli($host, $usuario, $clave, $basededatos);
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$error = "";

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Guardar objetivo si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $objetivo = $_POST['objetivo'] ?? '';

    if (empty($objetivo)) {
        $error = "⚠️ Por favor, selecciona un objetivo antes de continuar.";
    } else {
        // Obtener el último registro de datos_corporales del usuario
        $sql = "SELECT id FROM datos_corporales WHERE usuario_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $stmt->bind_result($ultimo_id);
        $stmt->fetch();
        $stmt->close();

        if ($ultimo_id) {
            // Actualizar el objetivo en ese registro
            $sql = "UPDATE datos_corporales SET objetivo_principal = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("si", $objetivo, $ultimo_id);

            if ($stmt->execute()) {
                header("Location: nivel_entrenamiento.php");
                exit();
            } else {
                $error = "Error al guardar: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $error = "No se encontró un registro de datos corporales para este usuario.";
        }
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CLUB ZYZZ - Objetivos</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/objetivo.css">
</head>
<body>
  <h1>¿Cuáles son tus objetivos principales?</h1>
  <?php if(!empty($error)): ?>
    <p style="color:red; font-weight:bold; text-align:center;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" class="container">
    <input type="hidden" name="objetivo" id="objetivoInput">

    <div class="opcion" onclick="seleccionarObjetivo(this, 'Pierde Peso')">🔥 Pierde Peso</div>
    <div class="opcion" onclick="seleccionarObjetivo(this, 'Aumentar Músculo')">💪 Aumentar Músculo</div>
    <div class="opcion" onclick="seleccionarObjetivo(this, 'Mantenerme en Forma')">🏃‍♂️ Mantenerme en Forma</div>

    <button type="submit" class="continuar-btn">CONTINUAR</button>
  </form>

  <div class="footer">CLUB ZYZZ™ – Tú contra ti mismo 💪</div>

  <script>
    function seleccionarObjetivo(elemento, objetivo) {
      document.querySelectorAll('.opcion').forEach(el => el.classList.remove('selected'));
      elemento.classList.add('selected');
      document.getElementById("objetivoInput").value = objetivo;
    }
  </script>
</body>
</html>
