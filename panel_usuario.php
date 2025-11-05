
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

// ✅ CORREGIDO: Usando created_at que es el nombre correcto
$query = "
SELECT u.nombre, u.email, d.peso, d.altura, d.edad, d.zonas_objetivo, d.objetivo_principal,
       d.nivel_entrenamiento, d.nivel_flexiones, d.restricciones, d.nivel_actividad, d.dias_por_semana,
       r.rutina_json AS ultima_rutina, r.id AS rutina_id, r.created_at
FROM usuarios u
LEFT JOIN datos_corporales d ON u.id = d.usuario_id
LEFT JOIN rutinas r ON u.id = r.usuario_id
WHERE u.id = ?
ORDER BY r.created_at DESC
LIMIT 1
";

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
<title>Panel Usuario - CLUB ZYZZ</title>
<link rel="stylesheet" href="css/estilo_rutina_ULTRA.css">
</head>
<body>

<!-- 🌌 Pantalla de carga CLUB ZYZZ -->
<div id="loading-screen">
  <div class="loading-logo"></div>
  <h2 class="loading-title">GENERANDO TU RUTINA...</h2>
  <p class="loading-text">💪 Prepárate para un entrenamiento ÉPICO 🔥</p>
  <div class="progress-bar"><span></span></div>

  <!-- 🎵 Música épica -->
  <audio id="epic-audio" src="audio/epic.mp3" preload="auto"></audio>
</div>

<header class="navbar fade-in delay-1">
    <div class="logo">
        <img src="logo.png" alt="Logo Club Zyzz">
        <span class="logo-text">CLUB ZYZZ</span>
    </div>
    <nav>
        <a href="quienes_somos.php">Quienes Somos</a>
        <a href="#progreso">Tu Progreso</a>
        <a href="#perfil" id="perfil-link">Tu Perfil</a>
        <a href="blog.php">Tips / Blog</a>
    </nav>
</header>

<main class="main-content">
<section class="welcome-section fade-in delay-2">
    <h1>Hola <?= htmlspecialchars($usuario['nombre']) ?>! 🔥</h1>

    <!-- 💥 BOTÓN + EFECTO DE CARGA -->
    <?php if (empty($usuario['rutina_id'])): ?>
        <form method="POST" action="generar_rutina.php" id="form-rutina">
          <button type="submit" class="btn-primary">💥 Generar Mi Rutina</button>
        </form>
    <?php else: ?>
        <a href="ver_rutina.php" class="btn-primary">🏋️ Ver Mi Rutina</a>
        <form method="POST" action="generar_rutina.php" id="form-rutina" style="display: inline;">
          <button type="submit" class="btn-secondary">🔄 Generar Nueva Rutina</button>
        </form>
    <?php endif; ?>
</section>

<section id="progreso" class="progress-section fade-in delay-3">
    <h2>Tu Progreso</h2>
    <div class="stats">
        <div class="stat-card fade-in delay-1"><p>Peso</p><span><?= $usuario['peso'] ?? '-' ?> kg</span></div>
        <div class="stat-card fade-in delay-2"><p>Altura</p><span><?= $usuario['altura'] ?? '-' ?> cm</span></div>
        <div class="stat-card fade-in delay-3"><p>Días por semana</p><span><?= $usuario['dias_por_semana'] ?? '-' ?></span></div>
    </div>
</section>

<section id="perfil" class="profile-section fade-in delay-4" style="display: none;">
    <h2>Tu Perfil</h2>
    <div class="profile-info">
        <?php foreach ($usuario as $key => $value): ?>
            <?php if ($key != 'ultima_rutina' && $key != 'rutina_id' && $key != 'created_at' && $value): ?>
                <p><strong><?= ucwords(str_replace("_"," ",$key)) ?>:</strong> <?= htmlspecialchars($value) ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="profile-actions">
        <a href="logout.php" class="btn-secondary">Cerrar Sesión</a>
        <a href="reiniciar_datos.php" class="btn-secondary">Reiniciar Mis Datos</a>
    </div>
</section>

<script>
// ⚡ Mostrar perfil
document.getElementById('perfil-link').addEventListener('click', function(e) {
  e.preventDefault();
  const perfilSection = document.getElementById('perfil');
  perfilSection.style.display = (perfilSection.style.display === 'none') ? 'block' : 'none';
  perfilSection.scrollIntoView({ behavior: 'smooth' });
});

// ⚡ Efecto de carga al generar rutina
document.getElementById("form-rutina").addEventListener("submit", function(e) {
  e.preventDefault();

  // Mostrar pantalla de carga
  document.body.classList.add("loading");
  const loadingScreen = document.getElementById("loading-screen");
  loadingScreen.classList.add("active");

  // Reproducir música épica
  const audio = document.getElementById("epic-audio");
  audio.play().catch(() => {});

  // Redirigir después de animación
  setTimeout(() => {
    this.submit();
  }, 4000); // 4 segundos de animación
});
</script>

<section id="beneficios" class="benefits-section fade-in delay-5">
    <h2>BENEFICIOS DE USAR CLUB ZYZZ 💪</h2>
    <div class="benefits-cards">
        <div class="benefit-card fade-in delay-1">
            <img src="jpg/imagen1.jpg" alt="Mejora Física">
            <h3>Mejora Física</h3>
            <p>Aumenta tu fuerza, resistencia y definición muscular con rutinas personalizadas.</p>
        </div>
        <div class="benefit-card fade-in delay-2">
            <img src="jpg/imagen2.jpg" alt="Salud y Bienestar">
            <h3>Salud y Bienestar</h3>
            <p>Reduce el estrés, mejora tu postura y tu salud cardiovascular entrenando de forma constante.</p>
        </div>
        <div class="benefit-card fade-in delay-3">
            <img src="jpg/imagen3.jpg" alt="Flexibilidad y Comodidad">
            <h3>Flexibilidad y Comodidad</h3>
            <p>Entrena desde casa o el gimnasio, adaptando las rutinas a tu horario y nivel.</p>
        </div>
        <div class="benefit-card fade-in delay-4">
            <img src="jpg/imagen4.jpg" alt="Motivación Constante">
            <h3>Motivación Constante</h3>
            <p>Recibe recomendaciones, seguimiento y rutinas que te mantienen motivado día a día.</p>
        </div>
        <div class="benefit-card fade-in delay-5">
            <img src="jpg/imagen5.jpg" alt="Progreso Medible">
            <h3>Progreso Medible</h3>
            <p>Monitorea tus avances y objetivos para ver tu transformación física y mental.</p>
        </div>
    </div>
</section>

<section class="tips-section fade-in delay-6">
    <h2>💡 Tips Rápidos de Fitness</h2>
    <div class="tips-cards">
        <div class="tip-card fade-in delay-1">
            <img src="https://img.icons8.com/color/96/000000/dumbbell.png" alt="Entrena con constancia">
            <h3>Entrena con Constancia</h3>
            <p>Realiza tus rutinas regularmente para ver resultados visibles y sostenibles.</p>
        </div>
        <div class="tip-card fade-in delay-2">
            <img src="https://img.icons8.com/color/96/000000/apple.png" alt="Alimentación Saludable">
            <h3>Alimentación Saludable</h3>
            <p>Combina tus entrenamientos con una dieta equilibrada para maximizar resultados.</p>
        </div>
        <div class="tip-card fade-in delay-3">
            <img src="https://img.icons8.com/color/96/000000/goal.png" alt="Mantente Motivado">
            <h3>Mantente Motivado</h3>
            <p>Fija objetivos claros, registra tu progreso y celebra tus logros cada semana.</p>
        </div>
    </div>
</section>

<section id="nutricion" class="nutrition-section fade-in delay-7">
  <h2>Consejos de Nutrición</h2>
  <p class="nutrition-intro">La alimentación es clave para obtener resultados reales. Sigue estos consejos básicos para mejorar tu rendimiento y tu físico.</p>
  
  <div class="nutrition-cards">
    <div class="nutrition-card fade-in delay-1">
      <img src="jpg/frutas1.jpg" alt="Hidrátate">
      <h3>Hidrátate</h3>
      <p>Bebe al menos 2 litros de agua al día para mantener tu cuerpo activo y saludable.</p>
    </div>
    <div class="nutrition-card fade-in delay-2">
      <img src="jpg/fruta3.jpg" alt="Proteínas">
      <h3>Come proteínas</h3>
      <p>Incluye pollo, pescado, huevos o legumbres para fortalecer tus músculos.</p>
    </div>
    <div class="nutrition-card fade-in delay-3">
      <img src="jpg/frutas2.jpg" alt="Frutas y verduras">
      <h3>Frutas y verduras</h3>
      <p>Aporta vitaminas y minerales esenciales para una recuperación óptima.</p>
    </div>
    <div class="nutrition-card fade-in delay-4">
      <img src="jpg/fruta4.jpg" alt="Evitar azúcar">
      <h3>Evita el azúcar</h3>
      <p>Reduce el consumo de dulces y comidas procesadas para mejorar tu rendimiento.</p>
    </div>
  </div>
</section>

<section class="faq-section fade-in delay-8">
  <h2>Preguntas Frecuentes</h2>

  <details class="faq-item fade-in delay-1">
    <summary>¿Qué nivel de experiencia necesito?</summary>
    <p>No necesitas experiencia previa. Nuestras rutinas se adaptan a tu nivel, ya seas principiante o avanzado.</p>
  </details>

  <details class="faq-item fade-in delay-2">
    <summary>¿Puedo entrenar en casa?</summary>
    <p>Sí, muchas rutinas están pensadas para hacerlas sin equipamiento, directamente en tu casa.</p>
  </details>

  <details class="faq-item fade-in delay-3">
    <summary>¿Con qué frecuencia debo entrenar?</summary>
    <p>Depende de tu objetivo. Generalmente recomendamos entre 3 a 5 días por semana para ver resultados óptimos.</p>
  </details>

  <details class="faq-item fade-in delay-4">
    <summary>¿Las rutinas son adaptables?</summary>
    <p>Sí, se ajustan automáticamente a tus datos: peso, edad, experiencia y disponibilidad semanal.</p>
  </details>
</section>
</main>

<footer class="footer-section fade-in delay-9">
    <div class="footer-content">
        <p>© 2025 CLUB ZYZZ. Todos los derechos reservados.</p>
        <p>Soporte: contactoclubzyzz@gmail.com</p>
    </div>
</footer>

</body>
</html>