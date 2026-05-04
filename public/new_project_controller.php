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
require_once __DIR__ . '/new_project_model.php';
require_once __DIR__ . '/../mail/Mail.php';

use SVE\Mail\Mailer;

function postText(string $key, int $maxLen = 0): string
{
    $value = trim((string)($_POST[$key] ?? ''));
    if ($maxLen > 0) {
        $value = mb_substr($value, 0, $maxLen);
    }
    return $value;
}

function isValidEnum(string $value, array $allowed): bool
{
    return in_array($value, $allowed, true);
}

$allowedQ5 = ['pagina_web', 'sistema_web_interno', 'app_mobile', 'plataforma_web_app_mobile', 'no_se'];
$allowedQ6 = ['si', 'no', 'no_se'];
$allowedQ7 = ['solo_equipo', 'clientes', 'proveedores', 'roles_diferentes', 'no_se'];
$allowedQ9 = [
    'registro_login',
    'perfil_usuario',
    'panel_admin',
    'carga_edicion_datos',
    'busqueda_filtros',
    'agenda_turnos',
    'pagos_online',
    'notificaciones_email_whatsapp',
    'reportes_metricas',
    'subida_archivos_imagenes',
    'geolocalizacion_mapas',
    'integracion_sistemas',
    'chat_mensajeria',
    'no_se',
];
$allowedQ12 = ['claro', 'medio', 'no'];
$allowedQ14 = ['completa', 'parcial', 'no'];
$allowedQ15 = ['cuanto_antes', '1_2_meses', 'mas_adelante', 'explorando'];
$allowedQ16 = ['sin_definir', 'menos_1000000', 'entre_1000000_2000000', 'mas_2000000'];
$allowedQ17 = ['por_etapas', 'todo_junto', 'necesito_recomendacion'];

$payload = [
    'nombre' => postText('nombre', 150),
    'nombre_proyecto' => postText('nombre_proyecto', 180),
    'correo' => postText('correo', 190),
    'whatsapp' => postText('whatsapp', 80),
    'q1_descripcion' => postText('q1_descripcion'),
    'q2_problema' => postText('q2_problema'),
    'q3_usuarios' => postText('q3_usuarios'),
    'q4_resultado_ideal' => postText('q4_resultado_ideal'),
    'q5_tipo_aplicacion' => postText('q5_tipo_aplicacion', 60),
    'q6_login' => postText('q6_login', 20),
    'q7_acceso' => postText('q7_acceso', 40),
    'q8_funciones_minimas' => postText('q8_funciones_minimas'),
    'q10_admin_vs_usuario' => postText('q10_admin_vs_usuario'),
    'q11_integraciones' => postText('q11_integraciones'),
    'q12_contenido' => postText('q12_contenido', 10),
    'q13_referencias' => postText('q13_referencias'),
    'q14_diseno' => postText('q14_diseno', 20),
    'q15_urgencia' => postText('q15_urgencia', 20),
    'q16_presupuesto' => postText('q16_presupuesto', 32),
    'q17_modalidad' => postText('q17_modalidad', 32),
    'q18_adicional' => postText('q18_adicional'),
];

$q9Raw = $_POST['q9_funcionalidades'] ?? [];
if (!is_array($q9Raw)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Formato invalido en funcionalidades.']);
    exit;
}

$q9Clean = [];
foreach ($q9Raw as $option) {
    $option = trim((string)$option);
    if ($option === '') {
        continue;
    }
    if (!isValidEnum($option, $allowedQ9)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Se detectaron funcionalidades invalidas.']);
        exit;
    }
    $q9Clean[$option] = $option;
}
$payload['q9_funcionalidades'] = array_values($q9Clean);

if (count($payload['q9_funcionalidades']) === 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Selecciona al menos una funcionalidad.']);
    exit;
}

$requiredTextFields = [
    'nombre',
    'nombre_proyecto',
    'correo',
    'whatsapp',
    'q1_descripcion',
    'q2_problema',
    'q3_usuarios',
    'q4_resultado_ideal',
    'q8_funciones_minimas',
    'q10_admin_vs_usuario',
    'q11_integraciones',
    'q13_referencias',
    'q18_adicional',
];

foreach ($requiredTextFields as $field) {
    if ($payload[$field] === '') {
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

if (!isValidEnum($payload['q5_tipo_aplicacion'], $allowedQ5)
    || !isValidEnum($payload['q6_login'], $allowedQ6)
    || !isValidEnum($payload['q7_acceso'], $allowedQ7)
    || !isValidEnum($payload['q12_contenido'], $allowedQ12)
    || !isValidEnum($payload['q14_diseno'], $allowedQ14)
    || !isValidEnum($payload['q15_urgencia'], $allowedQ15)
    || !isValidEnum($payload['q16_presupuesto'], $allowedQ16)
    || !isValidEnum($payload['q17_modalidad'], $allowedQ17)
) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Se detectaron opciones invalidas en el formulario.']);
    exit;
}

$payload['ip_address'] = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null;
$payload['user_agent'] = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null;
$payload['form_source'] = 'public-new-project';

$model = new NewProjectModel($pdo);
$saved = $model->guardar($payload);

if (!$saved) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'No se pudo guardar el formulario. Intenta nuevamente.',
    ]);
    exit;
}

$mailResult = Mailer::enviarSolicitudNuevoProyecto([
    'correo' => $payload['correo'],
    'nombre' => $payload['nombre'],
    'nombre_proyecto' => $payload['nombre_proyecto'],
    'whatsapp' => $payload['whatsapp'],
    'q1_descripcion' => $payload['q1_descripcion'],
    'q2_problema' => $payload['q2_problema'],
    'q3_usuarios' => $payload['q3_usuarios'],
    'q4_resultado_ideal' => $payload['q4_resultado_ideal'],
    'q5_tipo_aplicacion' => $payload['q5_tipo_aplicacion'],
    'q6_login' => $payload['q6_login'],
    'q7_acceso' => $payload['q7_acceso'],
    'q8_funciones_minimas' => $payload['q8_funciones_minimas'],
    'q9_funcionalidades' => $payload['q9_funcionalidades'],
    'q10_admin_vs_usuario' => $payload['q10_admin_vs_usuario'],
    'q11_integraciones' => $payload['q11_integraciones'],
    'q12_contenido' => $payload['q12_contenido'],
    'q13_referencias' => $payload['q13_referencias'],
    'q14_diseno' => $payload['q14_diseno'],
    'q15_urgencia' => $payload['q15_urgencia'],
    'q16_presupuesto' => $payload['q16_presupuesto'],
    'q17_modalidad' => $payload['q17_modalidad'],
    'q18_adicional' => $payload['q18_adicional'],
]);

if (!$mailResult['ok']) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Guardamos tu solicitud, pero no pudimos enviar el correo de confirmacion. Escribinos por WhatsApp y lo resolvemos.',
    ]);
    exit;
}

echo json_encode([
    'ok' => true,
    'message' => 'Formulario enviado correctamente.',
]);
