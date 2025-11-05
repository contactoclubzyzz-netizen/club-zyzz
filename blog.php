<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tips / Blog - CLUB ZYZZ</title>
<style>
/* ===================== FUENTES ===================== */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@600;700&display=swap');

/* ===================== RESET ===================== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    width: 100%;
    height: 100%;
    overflow-x: hidden;
    font-family: 'Inter', sans-serif;
    color: #f1f1f1;
    scroll-behavior: smooth;
    background: #0b0b0b;
}

/* ===================== NAVBAR ===================== */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(120deg, #111, #1a1a1a);
    padding: 15px 40px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.7), 0 0 10px #ff5e00;
    position: sticky;
    top: 0;
    z-index: 1000;
    flex-wrap: wrap;
    width: 100%;
    backdrop-filter: blur(6px);
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo img {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    object-fit: cover;
    opacity: 0;
    transform: translateY(-5px);
    animation: fadeDownLogo 1.2s ease forwards;
    animation-delay: 0.5s;
    transition: transform 0.3s ease;
}

.logo img:hover {
    transform: scale(1.05);
}

@keyframes fadeDownLogo {
    0% { opacity: 0; transform: translateY(-5px); filter: drop-shadow(0 0 0 #ff5e00) drop-shadow(0 0 0 #ff3838); }
    50% { opacity: 0.7; transform: translateY(-2px); filter: drop-shadow(0 0 5px #ff5e00) drop-shadow(0 0 10px #ff3838); }
    100% { opacity: 1; transform: translateY(0); filter: drop-shadow(0 0 5px #ff5e00) drop-shadow(0 0 8px #ff3838); }
}

.logo-text {
    font-size: 28px;
    font-weight: 700;
    color: #ffffff;
    font-family: 'Montserrat', sans-serif;
    opacity: 0;
    transform: translateY(-5px);
    animation: fadeDownText 1.2s ease forwards;
    animation-delay: 0.5s;
    transition: transform 0.3s ease;
}

.logo-text:hover {
    transform: scale(1.05);
}

@keyframes fadeDownText {
    0% { opacity: 0; transform: translateY(-5px); }
    100% { opacity: 1; transform: translateY(0); }
}

nav {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}

nav a {
    text-decoration: none;
    margin: 8px 20px;
    color: #f1f1f1;
    font-weight: 600;
    position: relative;
    transition: 0.3s;
}

nav a::after {
    content: '';
    position: absolute;
    width: 0%;
    height: 2px;
    background: #ff5e00;
    bottom: -4px;
    left: 0;
    transition: width 0.4s ease, background 0.3s;
}

nav a:hover::after {
    width: 100%;
    background: #ff8533;
}

nav a:hover {
    color: #ff8533;
    text-shadow: 0 0 10px #ff5e00;
}

/* ===================== HEADER BLOG ===================== */
header.blog-header {
    background: url('https://images.pexels.com/photos/841130/pexels-photo-841130.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940') no-repeat center center/cover;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-shadow: 0 2px 10px rgba(0,0,0,0.5);
    text-align: center;
}

header.blog-header h1 {
    font-size: 48px;
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    text-shadow: 0 0 20px #ff5e00, 0 0 35px #ff3838;
}

/* ===================== SECCIONES DEL BLOG ===================== */
.blog-section {
    max-width: 1200px;
    margin: 50px auto;
    padding: 0 20px;
}

.blog-post {
    background: #1a1a1a;
    border-radius: 15px;
    margin-bottom: 40px;
    padding: 25px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.3);
    transition: transform 0.3s, box-shadow 0.3s;
}

.blog-post:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255,94,0,0.4);
}

.blog-post img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 20px;
}

.blog-post h2 {
    color: #ff5e00;
    margin-bottom: 15px;
}

.blog-post p {
    line-height: 1.7;
    color: #ddd;
}

.blog-post iframe {
    width: 100%;
    height: 400px;
    border-radius: 10px;
    margin: 15px 0;
}

/* ===================== MINI TIPS ===================== */
.tips-mini {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 30px;
}

.tip-card {
    background: #222;
    border-radius: 15px;
    padding: 20px;
    flex: 1 1 calc(33% - 20px);
    text-align: center;
    box-shadow: 0 3px 8px rgba(0,0,0,0.3);
    transition: transform 0.3s, box-shadow 0.3s;
}

.tip-card img {
    width: 80px;
    height: 80px;
    margin-bottom: 15px;
}

.tip-card h3 {
    color: #ff5e00;
    margin-bottom: 10px;
}

.tip-card p {
    color: #ddd;
    font-size: 14px;
}

.tip-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(255,94,0,0.4);
}

/* ===================== FOOTER ===================== */
.footer-section {
    background: #111;
    color: #fff;
    padding: 30px;
    text-align: center;
}

/* ===================== RESPONSIVE ===================== */
@media screen and (max-width: 1024px) {
    .tip-card { flex: 1 1 calc(45% - 20px); }
}

@media screen and (max-width: 768px) {
    .navbar { flex-direction: column; align-items: center; padding: 12px 20px; }
    nav { flex-direction: column; align-items: center; }
    nav a { margin: 8px 0; }
    .tip-card { flex: 1 1 100%; }
}

@media screen and (max-width: 480px) {
    .logo-text { font-size: 20px; }
    header.blog-header h1 { font-size: 28px; }
}
                                                                     
                                                                     /* ===================== ANIMACIÓN GLOBAL ===================== */
body {
    opacity: 0;
    animation: pageFadeIn 1.5s ease forwards;
}

@keyframes pageFadeIn {
    to { opacity: 1; }
}

/* ===================== ANIMACIÓN POST ===================== */
.blog-post {
    background: #1a1a1a;
    border-radius: 15px;
    margin-bottom: 40px;
    padding: 25px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.3);
    transition: transform 0.3s, box-shadow 0.3s, opacity 1s ease;
    opacity: 0;
    transform: translateY(20px);
    animation: postFadeIn 1s ease forwards;
    animation-delay: 0.3s; /* Ajusta si quieres que aparezcan en secuencia */
}

@keyframes postFadeIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ===================== ANIMACIÓN MINI TIPS ===================== */
.tip-card {
    background: #222;
    border-radius: 15px;
    padding: 20px;
    flex: 1 1 calc(33% - 20px);
    text-align: center;
    box-shadow: 0 3px 8px rgba(0,0,0,0.3);
    transition: transform 0.3s, box-shadow 0.3s, opacity 1s ease;
    opacity: 0;
    transform: translateY(20px);
    animation: tipFadeIn 1s ease forwards;
    animation-delay: 0.4s;
}

@keyframes tipFadeIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

</style>
</head>
<body>

<!-- NAVBAR -->
<header class="navbar">
    <div class="logo">
        <img src="logo.png" alt="Logo Club Zyzz">
        <span class="logo-text">CLUB ZYZZ</span>
    </div>
    <nav>
        <a href="panel_usuario.php">Inicio</a>
        <a href="quienes_somos.php">Quienes Somos</a>
        <a href="blog.php">Tips / Blog</a>
    </nav>
</header>

<!-- HEADER DEL BLOG -->
<header class="blog-header">
    <h1>Bienvenido al Blog de Fitness</h1>
</header>

<!-- SECCIONES DEL BLOG -->
<div class="blog-section">

    <!-- POST 1 -->
    <div class="blog-post">
        <img src="https://images.pexels.com/photos/3757955/pexels-photo-3757955.jpeg" alt="Entrenamiento en casa">
        <h2>Entrenamiento en Casa: Tips para Principiantes</h2>
        <p>Entrenar en casa es totalmente posible y efectivo. No necesitas equipos caros; solo constancia y las rutinas correctas.</p>
        <iframe src="https://www.youtube.com/embed/8GDHz8jwmTI?si=yxCOznmfrt8oYi4p" title="Entrenamiento en casa" allowfullscreen></iframe>
        <p>Recuerda siempre calentar antes de entrenar y estirar después. ¡Tu cuerpo te lo agradecerá!</p>
    </div>

    <!-- POST 2 -->
    <div class="blog-post">
        <img src="https://images.pexels.com/photos/841130/pexels-photo-841130.jpeg" alt="Alimentación saludable">
        <h2>Alimentación Saludable para Mejorar Resultados</h2>
        <p>La nutrición es clave para ver cambios reales en tu cuerpo. Incorpora proteínas magras, frutas, verduras y carbohidratos complejos.</p>
        <iframe src="https://www.youtube.com/embed/ho5tVxBJWLA?si=WJFTueWHap-GRg2V" title="Nutrición y fitness" allowfullscreen></iframe>
    </div>

    <!-- MINI TIPS -->
    <h2>Mini Tips Rápidos</h2>
    <div class="tips-mini">
        <div class="tip-card">
            <img src="https://img.icons8.com/color/96/000000/dumbbell.png" alt="Constancia">
            <h3>Entrena con Constancia</h3>
            <p>Realiza tus rutinas al menos 4 días a la semana para ver progreso real.</p>
        </div>
        <div class="tip-card">
            <img src="https://img.icons8.com/color/96/000000/apple.png" alt="Alimentación">
            <h3>Alimentación Equilibrada</h3>
            <p>Combina tus ejercicios con comidas balanceadas para mejores resultados.</p>
        </div>
        <div class="tip-card">
            <img src="https://img.icons8.com/color/96/000000/goal.png" alt="Motivación">
            <h3>Mantente Motivado</h3>
            <p>Fija metas semanales y celebra tus logros, ¡no te rindas!</p>
        </div>
    </div>

</div>

<!-- FOOTER -->
<footer class="footer-section">
    <p>&copy; 2025 CLUB ZYZZ - Todos los derechos reservados.</p>
</footer>

</body>
</html>
