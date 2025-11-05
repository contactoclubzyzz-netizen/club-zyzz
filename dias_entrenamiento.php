<?php
// dias_entrenamiento.php
session_start();
require 'conexion.php'; // debe definir $conexion

// Mostrar errores (temporal para depurar, quita en producción si quieres)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Aceptamos dos nombres de sesión por compatibilidad (usa el que tengas en login)
$usuario_id = null;
if (!empty($_SESSION['usuario_id'])) $usuario_id = $_SESSION['usuario_id'];
if (!$usuario_id && !empty($_SESSION['user_id'])) $usuario_id = $_SESSION['user_id'];

// Si el usuario no está logueado -> enviar a login
if (!$usuario_id) {
    echo "<p style='color:red; text-align:center;'>⚠️ Debes iniciar sesión para continuar. <a href='login.php'>Ir a login</a></p>";
    exit();
}

$error = '';
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aseguramos que venga un valor numérico
    $dias = isset($_POST['dias']) ? intval($_POST['dias']) : 0;

    if ($dias <= 0 || $dias > 7) {
        $error = "⚠️ Selecciona un número válido de días (1-7).";
    } else {
        // Primero verificamos si ya existe un registro para este usuario
        $check = $conexion->prepare("SELECT id FROM datos_corporales WHERE usuario_id = ? LIMIT 1");
        if (!$check) {
            $error = "Error en la consulta: " . $conexion->error;
        } else {
            $check->bind_param("i", $usuario_id);
            $check->execute();
            $res = $check->get_result();

            if ($res && $res->num_rows > 0) {
                // Existe -> UPDATE
                $row = $res->fetch_assoc();
                $idRegistro = $row['id'];
                $upd = $conexion->prepare("UPDATE datos_corporales SET dias_por_semana = ? WHERE id = ?");
                if (!$upd) {
                    $error = "Error al preparar UPDATE: " . $conexion->error;
                } else {
                    $upd->bind_param("ii", $dias, $idRegistro);
                    if ($upd->execute()) {
                        $ok = true;
                    } else {
                        $error = "Error al actualizar: " . $upd->error;
                    }
                    $upd->close();
                }
            } else {
                // No existe -> INSERT
                $ins = $conexion->prepare("INSERT INTO datos_corporales (usuario_id, dias_por_semana) VALUES (?, ?)");
                if (!$ins) {
                    $error = "Error al preparar INSERT: " . $conexion->error;
                } else {
                    $ins->bind_param("ii", $usuario_id, $dias);
                    if ($ins->execute()) {
                        $ok = true;
                    } else {
                        $error = "Error al insertar: " . $ins->error;
                    }
                    $ins->close();
                }
            }
            $check->close();
        }
    }

    // Si todo ok, redirigimos al panel (sin producir salida antes)
    if ($ok) {
        header("Location: panel_usuario.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>CLUB ZYZZ - Días de Entrenamiento</title>
  <link href="css/dias_entrenamiento.css" rel="stylesheet" />
</head>
<body>
  <h1>¡No empieces la semana sin un objetivo claro!</h1>
  <p>3 días de entrenamiento a la semana marcan la diferencia. ¡Hazlo por ti!</p>

  <?php if ($error): ?>
    <div class="error" style="color:#ff6b6b; text-align:center; margin:12px 0;"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <h2>🎯 Días de entrenamiento por semana</h2>

  <form method="POST" novalidate>
    <div class="numeros">
      <?php for ($i = 1; $i <= 7; $i++): ?>
        <input type="radio" name="dias" id="dia<?= $i ?>" value="<?= $i ?>">
        <label class="numero" for="dia<?= $i ?>"><?= $i ?></label>
      <?php endfor; ?>
    </div>

    <div style="text-align:center; margin-top:18px;">
      <button type="submit" class="continuar-btn">CONTINUAR</button>
    </div>
  </form>

  <div class="footer">CLUB ZYZZ™ – Tú contra ti mismo 💪</div>
</body>
</html>
