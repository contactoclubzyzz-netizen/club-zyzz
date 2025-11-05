<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'conexion.php';
require_once 'config_api.php';

// ---------------------------
// 1) Verificar sesión
// ---------------------------
if (!isset($_SESSION['usuario_id'])) {
    // Para pruebas locales puedes descomentar la línea siguiente:
    // $_SESSION['usuario_id'] = 1;
    header('Location: login.php');
    exit;
}
$usuario_id = (int) $_SESSION['usuario_id'];

// ---------------------------
// 2) Obtener datos corporales
// ---------------------------
$stmt = $conexion->prepare("SELECT * FROM datos_corporales WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 1");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$datos = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$datos) {
    die("❌ No se encontraron datos corporales. Por favor completa tu perfil primero. <a href='panel_usuario.php'>Volver</a>");
}

// ---------------------------
// Helper corregido: Llamar ListModels y devolver un modelo válido
// ---------------------------
function buscarModeloValido() {
    $key = GEMINI_API_KEY;
    // URL CORRECTA para ListModels:
    $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . rawurlencode($key);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 15,
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['error' => "Error CURL (ListModels): $err"];
    }
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http !== 200) {
        // intentar decodificar para dar mejor error
        $decoded = json_decode($resp, true);
        $msg = $resp;
        if (isset($decoded['error']['message'])) {
            $msg = $decoded['error']['message'];
        }
        return ['error' => "ListModels API (HTTP $http): $msg", 'raw' => $resp];
    }

    $data = json_decode($resp, true);
    if (!isset($data['models']) || !is_array($data['models'])) {
        return ['error' => 'Respuesta ListModels inesperada.', 'raw' => $resp];
    }

    // Buscar modelos que aparenten soportar generación (priorizar gemini)
    foreach ($data['models'] as $m) {
        // 'name' suele tener el nombre completo como 'models/gemini-2.1' o similar.
        $name = $m['name'] ?? ($m['model'] ?? null);
        // Normalizar si viene en formato 'models/xxx'
        if ($name && preg_match('#models/(.+)$#', $name, $mm)) {
            $name = $mm[1];
        }
        // Si el objeto trae información sobre métodos soportados:
        $supported = $m['supportedGenerationMethods'] ?? $m['supported_methods'] ?? [];
        if (is_array($supported)) {
            foreach ($supported as $sm) {
                if (stripos($sm, 'generate') !== false || stripos($sm, 'generateContent') !== false) {
                    return ['model' => $name];
                }
            }
        }
        // fallback por nombre
        if ($name && (stripos($name, 'gemini') !== false || stripos($name, 'bison') !== false || stripos($name, 'flash') !== false)) {
            return ['model' => $name];
        }
    }

    // Si no hay match, devolvemos el primer modelo en formato utilizable
    if (!empty($data['models'][0]['name'])) {
        $firstName = $data['models'][0]['name'];
        if (preg_match('#models/(.+)$#', $firstName, $mm)) $firstName = $mm[1];
        return ['model' => $firstName];
    }

    return ['error' => 'No se encontró un modelo válido en ListModels.', 'raw' => $resp];
}

// ---------------------------
// Función: construir prompt
// ---------------------------
function construirPrompt($datos) {
    $restricciones = $datos['restricciones'] ?? 'Ninguna';
    $dias = (int)($datos['dias_por_semana'] ?? 3);
    if ($dias < 1) $dias = 3;

    $prompt = <<<EOT
Eres un entrenador personal experto. Crea una rutina de ejercicios en formato JSON.

DATOS DEL USUARIO:
- Sexo: {$datos['sexo']}
- Edad: {$datos['edad']} años
- Altura: {$datos['altura']} cm
- Peso: {$datos['peso']} kg
- Objetivo: {$datos['objetivo_principal']}
- Zonas objetivo: {$datos['zonas_objetivo']}
- Nivel: {$datos['nivel_entrenamiento']}
- Días por semana: {$dias}
- Restricciones: $restricciones

IMPORTANTE:
- Adapta la rutina al nivel del usuario.
- Usa nombres de ejercicios en inglés en el campo "nombre" y nombre en español en "nombre_es".
- Genera exactamente {$dias} días en el array "dias".
- Responde SOLO con un JSON válido (sin texto adicional) con la estructura:

{
  "resumen": "Breve descripción de la rutina",
  "advertencias": "Advertencias o null",
  "dias": [
    {
      "dia": "Día 1",
      "enfoque": "Tren superior",
      "ejercicios": [
        {
          "nombre": "push up",
          "nombre_es": "Flexiones",
          "series": 3,
          "repeticiones": "10-12",
          "descanso": "60 segundos",
          "notas": "Buena técnica"
        }
      ]
    }
  ],
  "recomendaciones": ["Calienta 5-10 min", "Hidrátate", "Descansa"]
}

EOT;
    return $prompt;
}

// ---------------------------
// Función: generar rutina (usa ListModels -> generateContent)
// ---------------------------
function generarRutinaConGemini($datos) {
    // 1) Buscar modelo válido
    $busqueda = buscarModeloValido();
    if (isset($busqueda['error'])) return ['error' => $busqueda['error'], 'raw' => $busqueda['raw'] ?? null];
    $modelo = $busqueda['model'] ?? null;
    if (!$modelo) return ['error' => 'No se encontró modelo válido.'];

    // 2) Preparar prompt y endpoint
    $prompt = construirPrompt($datos);
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . rawurlencode($modelo) . ":generateContent?key=" . rawurlencode(GEMINI_API_KEY);

    $payload = [
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.6,
            "maxOutputTokens" => 3500
        ]
    ];

    // 3) LLamada cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 60
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['error' => "Error CURL: $err"];
    }
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 4) Manejo errores API explícitos (ej: API key inválida)
    if ($http !== 200) {
        // intentar decodificar mensaje de error para dar info útil
        $decoded = json_decode($response, true);
        if (isset($decoded['error']['message'])) {
            $msg = $decoded['error']['message'];
            // Mensaje especial si la API key es inválida
            if (stripos($msg, 'API key not valid') !== false || stripos($msg, 'API_KEY_INVALID') !== false) {
                return ['error' => "API Key inválida o sin permisos. Respuesta API: $msg", 'raw' => $response];
            }
            return ['error' => "Error API (HTTP $http): $msg", 'raw' => $response];
        }
        return ['error' => "Error API (HTTP $http): $response", 'raw' => $response];
    }

    // 5) Procesar respuesta exitosa
    $result = json_decode($response, true);
    $texto = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if (!$texto) {
        // Guardar la respuesta completa para depuración
        file_put_contents("error_respuesta_gemini.txt", $response);
        return ['error' => 'No se recibió texto válido desde la API. Respuesta completa guardada en error_respuesta_gemini.txt', 'raw' => $response];
    }

    // 6) Limpieza y reparación del JSON
    $texto = preg_replace('/```(json)?/i', '', $texto);
    $texto = trim($texto);

    // Intento directo
    $rutina = json_decode($texto, true);

    // Si falla, intentamos reparaciones simples
    if (json_last_error() !== JSON_ERROR_NONE) {
        // 1) eliminar comas finales antes de } o ]
        $texto_reparado = preg_replace('/,\s*([\}\]])/', '$1', $texto);
        // 2) intentar cerrar corchetes/llaves faltantes: contar llaves
        $openCurly = substr_count($texto_reparado, '{');
        $closeCurly = substr_count($texto_reparado, '}');
        $openSquare = substr_count($texto_reparado, '[');
        $closeSquare = substr_count($texto_reparado, ']');
        while ($closeCurly < $openCurly) { $texto_reparado .= '}'; $closeCurly++; }
        while ($closeSquare < $openSquare) { $texto_reparado .= ']'; $closeSquare++; }

        $texto_reparado = rtrim($texto_reparado, ", \n\r\t");
        $rutina = json_decode($texto_reparado, true);

        // si aún falla, guardar la respuesta cruda para inspección
        if (json_last_error() !== JSON_ERROR_NONE) {
            file_put_contents("error_respuesta_gemini.txt", $texto . "\n\n---RAW RESPONSE---\n\n" . $response);
            return ['error' => '⚠️ Formato JSON inválido o incompleto. Respuesta guardada en error_respuesta_gemini.txt', 'raw' => $response];
        }
    }

    if (!isset($rutina['dias'])) {
        // Guardar por seguridad
        file_put_contents("error_respuesta_gemini.txt", $texto . "\n\n---PARSED---\n\n" . json_encode($rutina, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return ['error' => 'JSON recibido no contiene la estructura esperada ("dias"). Respuesta guardada en error_respuesta_gemini.txt', 'raw' => $response];
    }

    return $rutina;
}

// ---------------------------
// Lógica principal del formulario
// ---------------------------
$error = null;
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eliminar rutina anterior (asegúrate de que esto sea lo que quieres)
    $del_stmt = $conexion->prepare("DELETE FROM rutinas WHERE usuario_id = ?");
    $del_stmt->bind_param("i", $usuario_id);
    $del_stmt->execute();
    $del_stmt->close();

    $resultado = generarRutinaConGemini($datos);

    if (isset($resultado['error'])) {
        $error = $resultado['error'];
        // si viene 'raw', lo guardamos para depuración
        if (isset($resultado['raw'])) {
            file_put_contents("debug_response_raw.txt", $resultado['raw']);
        }
    } else {
        $rutina_json = json_encode($resultado, JSON_UNESCAPED_UNICODE);
        $stmt = $conexion->prepare("INSERT INTO rutinas (usuario_id, datos_corporales_id, rutina_json) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $usuario_id, $datos['id'], $rutina_json);

        if ($stmt->execute()) {
            $exito = true;
            file_put_contents("ultima_rutina.json", $rutina_json);
            header("refresh:2;url=ver_rutina.php");
        } else {
            $error = "Error al guardar rutina: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Generar Rutina Personalizada</title>
<style>
body{font-family:Arial;background:#eef2ff;display:flex;justify-content:center;align-items:center;min-height:100vh;}
.container{background:white;padding:40px;border-radius:15px;box-shadow:0 5px 20px rgba(0,0,0,0.1);max-width:650px;width:100%;}
.btn{display:block;width:100%;background:#667eea;color:white;padding:15px;border:none;border-radius:8px;font-size:16px;cursor:pointer;}
.btn:hover{background:#5a67d8;}
.success{background:#d4edda;padding:20px;border-radius:10px;text-align:center;}
.error{background:#f8d7da;padding:15px;border-radius:10px;margin-bottom:20px;color:#721c24;}
small {color: #666;}
</style>
</head>
<body>
<div class="container">
    <h2>🎯 Generar Rutina Personalizada</h2>
    <p>Basada en tus datos corporales</p>

    <?php if ($error): ?>
        <div class="error">⚠️ <?php echo htmlspecialchars($error); ?>
            <?php if (file_exists('debug_response_raw.txt')): ?>
                <br><small>Se creó <code>debug_response_raw.txt</code> con la respuesta cruda.</small>
            <?php endif; ?>
        </div>
    <?php elseif ($exito): ?>
        <div class="success">✅ ¡Rutina generada y guardada! Redirigiendo a ver_rutina.php...</div>
    <?php else: ?>
        <ul>
            <li><strong>Edad:</strong> <?php echo htmlspecialchars($datos['edad']); ?> años</li>
            <li><strong>Peso:</strong> <?php echo htmlspecialchars($datos['peso']); ?> kg</li>
            <li><strong>Altura:</strong> <?php echo htmlspecialchars($datos['altura']); ?> cm</li>
            <li><strong>Objetivo:</strong> <?php echo htmlspecialchars($datos['objetivo_principal']); ?></li>
        </ul>
        <form method="POST">
            <button type="submit" class="btn">✨ Generar Mi Rutina</button>
        </form>
    <?php endif; ?>

    <a href="panel_usuario.php">← Volver al panel</a>
</div>
</body>
</html>
