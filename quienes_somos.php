<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del usuario
$query = "SELECT nombre FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quienes Somos - CLUB ZYZZ</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700&display=swap">
<style>
/* ===================== RESET ===================== */
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { font-family: 'Inter', sans-serif; scroll-behavior: smooth; background: #0b0b0b; color: #f1f1f1; overflow-x: hidden; }

/* ===================== BODY ANIMATION ===================== */
body { opacity: 0; animation: fadeInBody 1.2s ease forwards; }
@keyframes fadeInBody { to { opacity: 1; } }

/* ===================== NAVBAR ===================== */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(120deg,#111,#1a1a1a);
    padding: 15px 40px;
    position: sticky; top: 0; z-index: 1000;
    flex-wrap: wrap;
    backdrop-filter: blur(6px);
    box-shadow: 0 4px 25px rgba(0,0,0,0.7),0 0 10px #ff5e00;
}
.logo { display: flex; align-items: center; gap: 12px; }
.logo img { width: 55px; height: 55px; border-radius: 50%; object-fit: cover; transition: transform 0.3s ease; }
.logo img:hover { transform: scale(1.05); }
.logo-text { font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 28px; color: #ffffff; transition: transform 0.3s ease; }
.logo-text:hover { transform: scale(1.05); }
nav { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
nav a { text-decoration: none; color: #f1f1f1; font-weight: 600; position: relative; transition: 0.3s; }
nav a::after { content: ''; position: absolute; width: 0%; height: 2px; background: #ff5e00; bottom: -4px; left: 0; transition: width 0.4s ease; }
nav a:hover::after { width: 100%; }
nav a:hover { color: #ff8533; text-shadow: 0 0 10px #ff5e00; }

/* ===================== MAIN CONTENT ===================== */
.main-content { max-width: 1200px; margin: 60px auto; padding: 0 20px; text-align: center; }
.about-section { background: linear-gradient(145deg,#1a1a1a,#121212); border-radius: 25px; padding: 50px 30px; box-shadow: 0 12px 35px rgba(0,0,0,0.7),0 0 20px rgba(255,94,0,0.15); animation: fadeInUp 1.5s ease forwards; }
.about-section h1 { font-family: 'Montserrat', sans-serif; font-size: 2.8rem; color: #ff5e00; margin-bottom: 25px; text-shadow: 0 0 20px #ff5e00,0 0 35px #ff3838; transition: transform 0.3s; }
.about-section h1:hover { transform: scale(1.03); }
.about-section p { font-size: 1.1rem; line-height: 1.8; margin-bottom: 20px; color: #ddd; opacity: 0; animation: fadeInText 2s ease forwards; animation-delay: 0.5s; }
.about-section p:nth-of-type(2) { animation-delay: 1s; }
.about-section p:nth-of-type(3) { animation-delay: 1.5s; }

/* ===================== BOTÓN ===================== */
.btn-back {
    display: inline-block;
    padding: 14px 28px;
    border-radius: 16px;
    font-weight: 600;
    font-size: 16px;
    text-decoration: none;
    color: #fff;
    background: linear-gradient(145deg,#ff5e00,#ff3838);
    box-shadow: 0 6px 20px rgba(0,0,0,0.6),0 0 10px #ff5e00 inset;
    transition: transform 0.3s, box-shadow 0.3s, filter 0.3s;
    margin-top: 20px;
}
.btn-back:hover { transform: translateY(-3px) scale(1.05); box-shadow: 0 10px 35px rgba(255,94,0,0.6),0 0 25px #ff3838; filter: brightness(1.15); }

/* ===================== ANIMACIONES ===================== */
@keyframes fadeInUp { 0% { opacity:0; transform: translateY(30px); } 100% { opacity:1; transform: translateY(0); } }
@keyframes fadeInText { to { opacity:1; } }

/* ===================== RESPONSIVE ===================== */
@media (max-width: 1024px) { .about-section { padding: 40px 20px; } h1 { font-size: 2.3rem; } }
@media (max-width: 768px) { .navbar { flex-direction: column; align-items: center; padding: 12px 20px; } nav { flex-direction: column; gap: 12px; } .about-section { padding: 30px 20px; } h1 { font-size: 2rem; } }
@media (max-width: 480px) { h1 { font-size: 1.6rem; } .about-section p { font-size: 1rem; } .btn-back { font-size: 14px; padding: 10px 18px; } }
 /* ===================== LOGO ===================== */
.logo img {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: none;
    transition: transform 0.3s ease;
    opacity: 0;
    transform: translateY(-5px);
    animation: fadeDownLogo 1.2s ease forwards;
    animation-delay: 0.5s;
}
.logo img:hover {
    transform: scale(1.05);
}

/* ===================== ANIMACIÓN LOGO ===================== */
@keyframes fadeDownLogo {
    0% { opacity: 0; transform: translateY(-5px); filter: drop-shadow(0 0 0 #ff5e00) drop-shadow(0 0 0 #ff3838); }
    50% { opacity: 0.7; transform: translateY(-2px); filter: drop-shadow(0 0 5px #ff5e00) drop-shadow(0 0 10px #ff3838); }
    100% { opacity: 1; transform: translateY(0); filter: drop-shadow(0 0 5px #ff5e00) drop-shadow(0 0 8px #ff3838); }
}

/* ===================== TEXTO CLUB ZYZZ ===================== */
.logo-text {
    font-size: 28px;
    font-weight: 700;
    color: #ffffff;
    font-family: 'Montserrat', sans-serif;
    text-shadow: 0 0 5px #ff5e00, 0 0 10px #ff3838;
    transition: transform 0.3s ease;
    opacity: 0;
    transform: translateY(-5px);
    animation: fadeDownText 1.2s ease forwards;
    animation-delay: 0.5s;
}
.logo-text:hover {
    transform: scale(1.05);
}

/* ===================== ANIMACIÓN TEXTO ===================== */
@keyframes fadeDownText {
    0% { opacity: 0; transform: translateY(-5px); }
    100% { opacity: 1; transform: translateY(0); }
}
</style>
</head>
<body>
<header class="navbar">
    <div class="logo">
        <img src="logo.png" alt="Logo Club Zyzz">
        <span class="logo-text">CLUB ZYZZ</span>
    </div>
    <nav>
        <a href="quienes_somos.php">Quienes Somos</a>
        <a href="panel_usuario.php#progreso">Tu Progreso</a>
        <a href="panel_usuario.php#perfil">Tu Perfil</a>
    </nav>
</header>

<main class="main-content">
    <section class="about-section">
        <h1>Quienes Somos</h1>
        <p>¡Hola <?= htmlspecialchars($usuario['nombre']) ?>! En <strong>CLUB ZYZZ</strong> somos soñadores apasionados que buscan mejorar el mundo a través del fitness y la superación personal. Creemos que cada persona tiene el potencial de transformar su vida, y queremos inspirarte a alcanzar tus metas físicas y mentales, mientras creas hábitos saludables que perduren para siempre.</p>
        <p>Nuestra comunidad está diseñada para impactar positivamente en cada individuo: fomentamos disciplina, motivación y progreso constante. Aquí no solo entrenas tu cuerpo, sino también tu mente y tu actitud frente a la vida.</p>
        <p>Si tienes dudas, sugerencias o quieres colaborar con nosotros, contáctanos en: <a href="mailto:contactoclubzyzz@gmail.com" style="color:#ff5e00; text-decoration:underline;">contactoclubzyzz@gmail.com</a></p>
        <a href="panel_usuario.php" class="btn-back">⬅ Volver al Panel</a>
    </section>
</main>
</body>
</html>
