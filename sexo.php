<?php
session_start();
require 'conexion.php';

if(!isset($_SESSION['usuario_id'])){
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $sexo = $_POST['sexo'] ?? '';
    if($sexo === 'Hombre' || $sexo === 'Mujer'){
        // Insertar solo el sexo por ahora
        $stmt = $conexion->prepare("INSERT INTO datos_corporales (usuario_id, sexo) VALUES (?, ?)");
        $stmt->bind_param("is", $usuario_id, $sexo);
        if($stmt->execute()){
            header("Location: peso_altura.php");
            exit();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CLUB ZYZZ – Selección de Género</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilo_usuario.css">
</head>
<body>
  <h1>Hola <?php echo htmlspecialchars($nombre_usuario); ?> 👋 ¿Eres hombre o mujer?</h1>

  <form class="container" method="POST" action="">
    <button class="option" type="submit" name="sexo" value="Hombre">🔥 Hombre</button>
    <button class="option" type="submit" name="sexo" value="Mujer">💖 Mujer</button>
  </form>

  <div class="footer">CLUB ZYZZ™ – Tú contra ti mismo 💪</div>
</body>
</html>
