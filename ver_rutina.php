<?php
session_start();
require_once 'conexion.php';
require_once 'config_api.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener la rutina del usuario
$stmt = $conexion->prepare("
    SELECT r.*, dc.restricciones, dc.objetivo
    FROM rutinas r
    JOIN datos_corporales dc ON r.datos_corporales_id = dc.id
    WHERE r.usuario_id = ?
    ORDER BY r.fecha_generacion DESC
    LIMIT 1
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$rutina_row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$rutina_row) {
    $conexion->close();
    header('Location: generar_rutina.php');
    exit;
}

$rutina = json_decode($rutina_row['rutina_json'], true);

// Función para buscar GIF del ejercicio con CACHE
function buscarGifEjercicioConCache($nombre_ejercicio, $conexion) {
    // Limpiar nombre
    $nombre_limpio = trim(strtolower($nombre_ejercicio));
    
    // 1. Buscar en cache primero
    $stmt = $conexion->prepare("SELECT gif_url, target, body_part, equipment FROM ejercicios_cache WHERE nombre_ejercicio = ?");
    $stmt->bind_param("s", $nombre_limpio);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($resultado && !empty($resultado['gif_url'])) {
        return $resultado;
    }
    
    // 2. Si no está en cache, buscar en ExerciseDB API
    $nombre_busqueda = str_replace(' ', '%20', $nombre_limpio);
    $url = "https://exercisedb.p.rapidapi.com/exercises/name/" . $nombre_busqueda . "?limit=1";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-RapidAPI-Key: ' . EXERCISEDB_API_KEY,
        'X-RapidAPI-Host: ' . EXERCISEDB_HOST
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $ejercicios = json_decode($response, true);
        
        if (!empty($ejercicios) && isset($ejercicios[0])) {
            $ejercicio = $ejercicios[0];
            $datos = [
                'gif_url' => $ejercicio['gifUrl'] ?? null,
                'target' => $ejercicio['target'] ?? null,
                'body_part' => $ejercicio['bodyPart'] ?? null,
                'equipment' => $ejercicio['equipment'] ?? null
            ];
            
            // 3. Guardar en cache
            $stmt = $conexion->prepare("
                INSERT INTO ejercicios_cache (nombre_ejercicio, gif_url, target, body_part, equipment) 
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    gif_url = VALUES(gif_url),
                    target = VALUES(target),
                    body_part = VALUES(body_part),
                    equipment = VALUES(equipment),
                    fecha_cache = CURRENT_TIMESTAMP
            ");
            $stmt->bind_param("sssss", 
                $nombre_limpio, 
                $datos['gif_url'], 
                $datos['target'], 
                $datos['body_part'], 
                $datos['equipment']
            );
            $stmt->execute();
            $stmt->close();
            
            return $datos;
        }
    }
    
    // 4. Si falla todo, guardar como no encontrado
    $stmt = $conexion->prepare("
        INSERT INTO ejercicios_cache (nombre_ejercicio, gif_url) 
        VALUES (?, NULL)
        ON DUPLICATE KEY UPDATE fecha_cache = CURRENT_TIMESTAMP
    ");
    $stmt->bind_param("s", $nombre_limpio);
    $stmt->execute();
    $stmt->close();
    
    return null;
}

// Manejar eliminación de rutina
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_rutina'])) {
    $stmt = $conexion->prepare("DELETE FROM rutinas WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
    header('Location: generar_rutina.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Rutina Personalizada - Epic Fitness</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
            color: #fff;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .hero-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%);
            padding: 50px;
            border-radius: 25px;
            margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: fadeInDown 0.8s ease;
        }
        
        .hero-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .hero-header h1 {
            font-size: 42px;
            margin-bottom: 15px;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        
        .hero-header .fecha {
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }
        
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease 0.2s both;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .stat-card i {
            font-size: 32px;
            color: #ffd700;
            margin-bottom: 10px;
        }
        
        .stat-card .label {
            font-size: 14px;
            opacity: 0.8;
            margin-bottom: 5px;
        }
        
        .stat-card .value {
            font-size: 24px;
            font-weight: 700;
            color: #fff;
        }
        
        .resumen-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 35px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            animation: fadeInUp 0.8s ease 0.4s both;
        }
        
        .resumen-section h2 {
            color: #ffd700;
            margin-bottom: 20px;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .resumen-section h2 i {
            font-size: 32px;
        }
        
        .advertencias {
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.2) 0%, rgba(255, 152, 0, 0.2) 100%);
            border-left: 4px solid #ffc107;
            padding: 25px;
            border-radius: 15px;
            margin-top: 25px;
            backdrop-filter: blur(5px);
        }
        
        .advertencias h3 {
            color: #ffd700;
            margin-bottom: 15px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* ACORDEÓN DE DÍAS */
        .dias-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .dia-accordion {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s;
            animation: fadeInUp 0.8s ease both;
        }
        
        .dia-accordion:nth-child(1) { animation-delay: 0.5s; }
        .dia-accordion:nth-child(2) { animation-delay: 0.6s; }
        .dia-accordion:nth-child(3) { animation-delay: 0.7s; }
        .dia-accordion:nth-child(4) { animation-delay: 0.8s; }
        .dia-accordion:nth-child(5) { animation-delay: 0.9s; }
        .dia-accordion:nth-child(6) { animation-delay: 1s; }
        .dia-accordion:nth-child(7) { animation-delay: 1.1s; }
        
        .dia-accordion.active {
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 15px 50px rgba(102, 126, 234, 0.3);
        }
        
        .dia-header {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.3) 100%);
            padding: 25px 30px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
            user-select: none;
        }
        
        .dia-header:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.4) 0%, rgba(118, 75, 162, 0.4) 100%);
        }
        
        .dia-header-left {
            flex: 1;
        }
        
        .dia-header h2 {
            font-size: 26px;
            margin-bottom: 5px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .dia-numero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 20px;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .dia-enfoque {
            color: #ffd700;
            font-size: 16px;
            font-weight: 500;
            margin-left: 60px;
        }
        
        .toggle-icon {
            font-size: 28px;
            color: #ffd700;
            transition: transform 0.3s;
        }
        
        .dia-accordion.active .toggle-icon {
            transform: rotate(180deg);
        }
        
        .dia-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease;
        }
        
        .dia-accordion.active .dia-content {
            max-height: 5000px;
        }
        
        .ejercicios-grid {
            padding: 30px;
            display: grid;
            gap: 25px;
        }
        
        /* TARJETAS DE EJERCICIOS */
        .ejercicio-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
        }
        
        .ejercicio-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        
        .ejercicio-card:hover::before {
            left: 100%;
        }
        
        .ejercicio-card:hover {
            transform: translateY(-5px);
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
            background: rgba(255, 255, 255, 0.12);
        }
        
        .ejercicio-header-flex {
            display: flex;
            gap: 25px;
            margin-bottom: 20px;
        }
        
        .ejercicio-info {
            flex: 1;
        }
        
        .ejercicio-nombre-principal {
            font-size: 24px;
            color: #fff;
            font-weight: 700;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .ejercicio-nombre-principal i {
            color: #ffd700;
        }
        
        .ejercicio-nombre-secundario {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            font-style: italic;
            margin-bottom: 15px;
        }
        
        .ejercicio-gif-container {
            flex-shrink: 0;
            width: 220px;
            height: 220px;
        }
        
        .ejercicio-gif {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 15px;
            border: 3px solid rgba(255, 215, 0, 0.3);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            transition: all 0.3s;
        }
        
        .ejercicio-card:hover .ejercicio-gif {
            transform: scale(1.05);
            border-color: rgba(255, 215, 0, 0.6);
            box-shadow: 0 15px 40px rgba(255, 215, 0, 0.3);
        }
        
        .gif-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            text-align: center;
            padding: 20px;
            border: 2px dashed rgba(255, 255, 255, 0.3);
        }
        
        .gif-placeholder i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-box {
            background: rgba(102, 126, 234, 0.2);
            border: 1px solid rgba(102, 126, 234, 0.4);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .stat-box:hover {
            background: rgba(102, 126, 234, 0.3);
            transform: translateY(-3px);
        }
        
        .stat-box-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .stat-box-value {
            font-size: 22px;
            font-weight: 800;
            color: #ffd700;
            text-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
        }
        
        .ejercicio-notas {
            background: rgba(23, 162, 184, 0.2);
            border-left: 4px solid #17a2b8;
            padding: 18px;
            border-radius: 12px;
            margin-top: 20px;
            backdrop-filter: blur(5px);
        }
        
        .ejercicio-notas strong {
            color: #5ddef4;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .ejercicio-notas strong i {
            font-size: 18px;
        }
        
        .ejercicio-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .tag {
            background: rgba(255, 215, 0, 0.2);
            color: #ffd700;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid rgba(255, 215, 0, 0.3);
            text-transform: capitalize;
        }
        
        /* RECOMENDACIONES */
        .recomendaciones {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.2) 0%, rgba(32, 201, 151, 0.2) 100%);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(40, 167, 69, 0.3);
            border-radius: 20px;
            padding: 35px;
            margin-top: 40px;
            animation: fadeInUp 0.8s ease 1.2s both;
        }
        
        .recomendaciones h2 {
            color: #5dff9f;
            margin-bottom: 25px;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .recomendaciones h2 i {
            font-size: 32px;
        }
        
        .recomendaciones ul {
            list-style: none;
        }
        
        .recomendaciones li {
            padding: 15px 0;
            padding-left: 40px;
            position: relative;
            color: #fff;
            font-size: 16px;
            line-height: 1.6;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .recomendaciones li:last-child {
            border-bottom: none;
        }
        
        .recomendaciones li:before {
            content: "✓";
            position: absolute;
            left: 0;
            top: 15px;
            color: #5dff9f;
            font-weight: bold;
            font-size: 24px;
            width: 30px;
            height: 30px;
            background: rgba(93, 255, 159, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* BOTONES DE ACCIÓN */
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 40px;
            flex-wrap: wrap;
            justify-content: center;
            animation: fadeInUp 0.8s ease 1.4s both;
        }
        
        .btn {
            padding: 18px 35px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        .btn i {
            font-size: 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.5);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(108, 117, 125, 0.5);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(220, 53, 69, 0.5);
        }
        
        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .modal-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 10% auto;
            padding: 40px;
            border-radius: 25px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            animation: slideInDown 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .modal-content::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        }
        
        .modal-content h2 {
            margin-bottom: 20px;
            font-size: 28px;
            position: relative;
            z-index: 1;
        }
        
        .modal-content p {
            margin-bottom: 15px;
            font-size: 16px;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }
        
        .modal-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .hero-header {
                padding: 30px 20px;
            }
            
            .hero-header h1 {
                font-size: 28px;
            }
            
            .stats-bar {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .dia-header h2 {
                font-size: 20px;
            }
            
            .dia-numero {
                width: 35px;
                height: 35px;
                font-size: 16px;
            }
            
            .dia-enfoque {
                font-size: 14px;
                margin-left: 50px;
            }
            
            .ejercicio-header-flex {
                flex-direction: column;
            }
            
            .ejercicio-gif-container {
                width: 100%;
                height: 280px;
                margin: 0 auto;
            }
            
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }
            
            .stat-box {
                padding: 12px;
            }
            
            .stat-box-value {
                font-size: 18px;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .modal-content {
                margin: 20% 20px;
                padding: 30px 20px;
            }
        }
        
        @media print {
            body {
                background: white;
                color: black;
            }
            
            .actions, .modal {
                display: none !important;
            }
            
            .dia-accordion {
                page-break-inside: avoid;
            }
            
            .dia-content {
                max-height: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero-header">
            <h1><i class="fas fa-fire"></i> TU RUTINA ÉPICA PERSONALIZADA <i class="fas fa-fire"></i></h1>
            <p class="fecha">Generada el <?php echo date('d/m/Y', strtotime($rutina_row['fecha_generacion'])); ?></p>
        </div>
        
        <?php
        // Calcular estadísticas
        $total_dias = count($rutina['dias']);
        $total_ejercicios = 0;
        foreach ($rutina['dias'] as $dia) {
            $total_ejercicios += count($dia['ejercicios']);
        }
        $promedio_ejercicios = round($total_ejercicios / $total_dias, 1);
        ?>
        
        <div class="stats-bar">
            <div class="stat-card">
                <i class="fas fa-calendar-week"></i>
                <div class="label">Días de Entrenamiento</div>
                <div class="value"><?php echo $total_dias; ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-dumbbell"></i>
                <div class="label">Total de Ejercicios</div>
                <div class="value"><?php echo $total_ejercicios; ?></div>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <div class="label">Promedio por Día</div>
                <div class="value"><?php echo $promedio_ejercicios; ?></div>
            </div>
        </div>
        
        <?php if (!empty($rutina['resumen'])): ?>
        <div class="resumen-section">
            <h2><i class="fas fa-clipboard-check"></i> Resumen de tu Rutina</h2>
            <p><?php echo nl2br(htmlspecialchars($rutina['resumen'])); ?></p>
            
            <?php if (!empty($rutina['advertencias'])): ?>
            <div class="advertencias">
                <h3><i class="fas fa-exclamation-triangle"></i> Advertencias Importantes</h3>
                <p><?php echo nl2br(htmlspecialchars($rutina['advertencias'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- DÍAS CON ACORDEÓN -->
        <div class="dias-container">
            <?php foreach ($rutina['dias'] as $index => $dia): ?>
            <div class="dia-accordion" id="dia-<?php echo $index; ?>">
                <div class="dia-header" onclick="toggleDia(<?php echo $index; ?>)">
                    <div class="dia-header-left">
                        <h2>
                            <span class="dia-numero"><?php echo $index + 1; ?></span>
                            <?php echo htmlspecialchars($dia['dia']); ?>
                        </h2>
                        <?php if (!empty($dia['enfoque'])): ?>
                        <div class="dia-enfoque">
                            <i class="fas fa-bullseye"></i> <?php echo htmlspecialchars($dia['enfoque']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </div>
                
                <div class="dia-content">
                    <div class="ejercicios-grid">
                        <?php foreach ($dia['ejercicios'] as $ejercicio): ?>
                        <div class="ejercicio-card">
                            <div class="ejercicio-header-flex">
                                <div class="ejercicio-info">
                                    <div class="ejercicio-nombre-principal">
                                        <i class="fas fa-fire-alt"></i>
                                        <?php echo htmlspecialchars($ejercicio['nombre_es']); ?>
                                    </div>
                                    <div class="ejercicio-nombre-secundario">
                                        <?php echo htmlspecialchars($ejercicio['nombre']); ?>
                                    </div>
                                    
                                    <div class="stats-grid">
                                        <div class="stat-box">
                                            <div class="stat-box-label">Series</div>
                                            <div class="stat-box-value"><?php echo htmlspecialchars($ejercicio['series']); ?></div>
                                        </div>
                                        <div class="stat-box">
                                            <div class="stat-box-label">Reps</div>
                                            <div class="stat-box-value"><?php echo htmlspecialchars($ejercicio['repeticiones']); ?></div>
                                        </div>
                                        <div class="stat-box">
                                            <div class="stat-box-label">Descanso</div>
                                            <div class="stat-box-value"><?php echo htmlspecialchars($ejercicio['descanso']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="ejercicio-gif-container">
                                    <?php 
                                    $datos_ejercicio = buscarGifEjercicioConCache($ejercicio['nombre'], $conexion);
                                    if ($datos_ejercicio && !empty($datos_ejercicio['gif_url'])): 
                                    ?>
                                        <img src="<?php echo htmlspecialchars($datos_ejercicio['gif_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($ejercicio['nombre']); ?>" 
                                             class="ejercicio-gif"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="gif-placeholder">
                                            <i class="fas fa-video-slash"></i>
                                            <p>Video no disponible</p>
                                            <small>Busca en YouTube:<br>"<?php echo htmlspecialchars($ejercicio['nombre']); ?>"</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($ejercicio['notas'])): ?>
                            <div class="ejercicio-notas">
                                <strong><i class="fas fa-lightbulb"></i> Nota Técnica:</strong>
                                <?php echo htmlspecialchars($ejercicio['notas']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($datos_ejercicio && (!empty($datos_ejercicio['target']) || !empty($datos_ejercicio['body_part']) || !empty($datos_ejercicio['equipment']))): ?>
                            <div class="ejercicio-tags">
                                <?php if (!empty($datos_ejercicio['target'])): ?>
                                    <span class="tag"><i class="fas fa-crosshairs"></i> <?php echo htmlspecialchars($datos_ejercicio['target']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($datos_ejercicio['body_part'])): ?>
                                    <span class="tag"><i class="fas fa-user"></i> <?php echo htmlspecialchars($datos_ejercicio['body_part']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($datos_ejercicio['equipment'])): ?>
                                    <span class="tag"><i class="fas fa-tools"></i> <?php echo htmlspecialchars($datos_ejercicio['equipment']); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($rutina['recomendaciones'])): ?>
        <div class="recomendaciones">
            <h2><i class="fas fa-medal"></i> Recomendaciones Pro</h2>
            <ul>
                <?php foreach ($rutina['recomendaciones'] as $rec): ?>
                <li><?php echo htmlspecialchars($rec); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="actions">
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button onclick="expandirTodo()" class="btn btn-secondary" id="btnExpandir">
                <i class="fas fa-expand-alt"></i> Expandir Todo
            </button>
            <button onclick="mostrarModalEliminar()" class="btn btn-danger">
                <i class="fas fa-sync-alt"></i> Nueva Rutina
            </button>
        </div>
    </div>
    
    <!-- Modal de confirmación -->
    <div id="modalEliminar" class="modal">
        <div class="modal-content">
            <h2><i class="fas fa-exclamation-triangle"></i> Confirmar Acción</h2>
            <p>¿Estás seguro de que quieres eliminar esta rutina épica y generar una nueva?</p>
            <p><strong>Deberás actualizar tus datos corporales primero.</strong></p>
            <div class="modal-buttons">
                <button onclick="cerrarModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="eliminar_rutina" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Sí, Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle individual
        function toggleDia(index) {
            const accordion = document.getElementById('dia-' + index);
            accordion.classList.toggle('active');
        }
        
        // Expandir/contraer todo
        let todoExpandido = false;
        function expandirTodo() {
            const accordions = document.querySelectorAll('.dia-accordion');
            const btn = document.getElementById('btnExpandir');
            
            if (!todoExpandido) {
                accordions.forEach(acc => acc.classList.add('active'));
                btn.innerHTML = '<i class="fas fa-compress-alt"></i> Contraer Todo';
                todoExpandido = true;
            } else {
                accordions.forEach(acc => acc.classList.remove('active'));
                btn.innerHTML = '<i class="fas fa-expand-alt"></i> Expandir Todo';
                todoExpandido = false;
            }
        }
        
        // Modal
        function mostrarModalEliminar() {
            document.getElementById('modalEliminar').style.display = 'block';
        }
        
        function cerrarModal() {
            document.getElementById('modalEliminar').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('modalEliminar');
            if (event.target == modal) {
                cerrarModal();
            }
        }
        
        // Abrir primer día por defecto
        document.addEventListener('DOMContentLoaded', function() {
            const primerDia = document.getElementById('dia-0');
            if (primerDia) {
                primerDia.classList.add('active');
            }
        });
    </script>
</body>
</html>
<?php
$conexion->close();
?>