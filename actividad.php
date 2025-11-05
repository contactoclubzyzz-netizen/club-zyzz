<?php
session_start();
require 'conexion.php'; // Debe definir $conexion

// Comprobar que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuarioId = $_SESSION['usuario_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['nivelActividad'])) {
        $actividadSeleccionada = $_POST['nivelActividad'];

        // Insertar o actualizar en la tabla datos_corporales
        $stmt = $conexion->prepare("UPDATE datos_corporales SET nivel_actividad=? WHERE usuario_id=?");
        $stmt->bind_param("si", $actividadSeleccionada, $usuarioId);

        if ($stmt->execute()) {
            header("Location: dias_entrenamiento.php"); // Página siguiente
            exit();
        } else {
            $error = "Error al guardar el nivel de actividad.";
        }
    } else {
        $error = "Selecciona un nivel de actividad antes de continuar.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CLUB ZYZZ - Nivel de Actividad</title>
  <link rel="stylesheet" href="css/actividad.css">
</head>
<body>
  <h1>¿Cuál es tu nivel de actividad?</h1>

  <?php if($error): ?>
    <p style="color:red; text-align:center; margin-bottom:15px;"><?php echo $error; ?></p>
  <?php endif; ?>

  <form method="POST" id="actividadForm">
    <input type="hidden" name="nivelActividad" id="nivelActividadInput">

    <div class="opciones">
      <div class="opcion" onclick="seleccionarActividad(this, 'Sedentario')">
        👨‍💻 Sedentario
        <span class="descripcion">Paso el día en el escritorio</span>
      </div>
      <div class="opcion" onclick="seleccionarActividad(this, 'Actividad Ligera')">
        🚶 Actividad Ligera
        <span class="descripcion">A veces hago ejercicio o ando 30 minutos</span>
      </div>
      <div class="opcion" onclick="seleccionarActividad(this, 'Moderadamente Activa')">
        🏋️ Moderadamente Activa
        <span class="descripcion">Estoy una hora o más entrenando cada día</span>
      </div>
      <div class="opcion" onclick="seleccionarActividad(this, 'Muy Activa')">
        🔥 Muy Activa
        <span class="descripcion">Me encanta entrenar y quiero más ejercicios</span>
      </div>
    </div>

    <button type="submit" class="continuar-btn">CONTINUAR</button>
  </form>

  <script>
    let actividadSeleccionada = null;

    function seleccionarActividad(elemento, nivel) {
      document.querySelectorAll('.opcion').forEach(el => el.classList.remove('selected'));
      elemento.classList.add('selected');
      actividadSeleccionada = nivel;
      document.getElementById('nivelActividadInput').value = nivel;
    }
  </script>
</body>
</html>
