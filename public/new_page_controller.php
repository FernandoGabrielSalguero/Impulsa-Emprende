<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'error' => 'Metodo no permitido.',
    ]);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/new_page_model.php';

function postText(string $key, int $maxLen = 0): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    if ($maxLen > 0) {
        $value = mb_substr($value, 0, $maxLen);
    }
    return $value;
}

$payload = [
    'nombre' => postText('nombre', 150),
    'nombre_proyecto' => postText('nombre_proyecto', 180),
    'correo' => postText('correo', 190),
    'whatsapp' => postText('whatsapp', 80),
    'q1_nombre_comercial' => postText('q1_nombre_comercial'),
    'q2_actividad' => postText('q2_actividad'),
    'q3_objetivo' => postText('q3_objetivo'),
    'q4_publico' => postText('q4_publico'),
    'q5_accion_principal' => postText('q5_accion_principal'),
    'q6_propuestas_destacar' => postText('q6_propuestas_destacar'),
    'q7_diferencial' => postText('q7_diferencial'),
    'q8_secciones' => postText('q8_secciones'),
    'q9_textos' => postText('q9_textos'),
    'q10_contacto' => postText('q10_contacto'),
    'q11_material_marca' => postText('q11_material_marca'),
    'q12_estilo_visual' => postText('q12_estilo_visual'),
    'q13_referencias' => postText('q13_referencias'),
    'q14_recursos_visuales' => postText('q14_recursos_visuales'),
    'q15_imagenes_apoyo' => postText('q15_imagenes_apoyo'),
    'q16_dominio_hosting' => postText('q16_dominio_hosting'),
    'q17_correos_corporativos' => postText('q17_correos_corporativos'),
    'q18_requerimientos_adicionales' => postText('q18_requerimientos_adicionales'),
];

foreach ($payload as $value) {
    if ($value === '') {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Completa todos los campos obligatorios del formulario.']);
        exit;
    }
}

if (!filter_var($payload['correo'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'El correo ingresado no es valido.']);
    exit;
}

$payload['ip_address'] = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null;
$payload['user_agent'] = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null;
$payload['form_source'] = 'public-new-page';

try {
    $model = new NewPageModel($pdo);
    $saved = $model->guardar($payload);
} catch (Throwable $exception) {
    $saved = false;
}

if (!$saved) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'No se pudo guardar el formulario. Intenta nuevamente.',
    ]);
    exit;
}

echo json_encode([
    'ok' => true,
    'message' => 'Formulario enviado correctamente.',
]);
