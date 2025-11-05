<?php
session_start();
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bienvenido | CLUB ZYZZ</title>
  <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
  <h1>Bienvenido al CLUB ZYZZ</h1>
  <h2>Hola, <?php echo $_SESSION['usuario_nombre']; ?> 💪</h2>
  <p>¡Prepárate para transformar tu físico!</p>

  <a href="logout.php">Cerrar sesión</a>
</body>
</html>
