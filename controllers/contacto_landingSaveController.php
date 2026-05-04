<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Metodo no permitido.',
    ]);
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/contacto_landingModel.php';

$nombre = trim((string) ($_POST['nombre'] ?? ''));
$empresa = trim((string) ($_POST['empresa'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$telefono = trim((string) ($_POST['telefono'] ?? ''));
$equipo = trim((string) ($_POST['equipo'] ?? ''));
$objetivo = trim((string) ($_POST['objetivo'] ?? ''));
$mensaje = trim((string) ($_POST['mensaje'] ?? ''));
$captcha = trim((string) ($_POST['captcha'] ?? ''));
$formSource = trim((string) ($_POST['form_source'] ?? 'landing-impulsa-emprende'));
$website = trim((string) ($_POST['website'] ?? ''));
$renderedAt = trim((string) ($_POST['form_rendered_at'] ?? ''));

if ($website !== '') {
    echo json_encode([
        'status' => 'success',
        'message' => 'Gracias. Tu mensaje fue enviado y te responderemos a la brevedad.',
    ]);
    exit;
}

if ($renderedAt === '' || !ctype_digit($renderedAt)) {
    http_response_code(422);
    echo json_encode([
        'status' => 'invalid',
        'message' => 'No pudimos validar el formulario. Recarga la pagina e intenta nuevamente.',
    ]);
    exit;
}

$elapsedMs = ((int) (microtime(true) * 1000)) - (int) $renderedAt;
if ($elapsedMs < 2500) {
    http_response_code(422);
    echo json_encode([
        'status' => 'invalid',
        'message' => 'Espera unos segundos y vuelve a enviar el formulario.',
    ]);
    exit;
}

if ($nombre === '' || $empresa === '' || $telefono === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode([
        'status' => 'invalid',
        'message' => 'Revisa los datos ingresados e intenta nuevamente.',
    ]);
    exit;
}

if ($captcha !== '7') {
    http_response_code(422);
    echo json_encode([
        'status' => 'invalid_captcha',
        'message' => 'No pudimos validar el captcha. Resuelve la cuenta e intenta nuevamente.',
    ]);
    exit;
}

$ipAddress = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null;
$userAgent = substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null;

$model = new ContactoLandingModel($pdo);
$ok = $model->guardar([
    'nombre' => $nombre,
    'empresa' => $empresa,
    'email' => $email,
    'telefono' => $telefono,
    'equipo' => $equipo !== '' ? $equipo : null,
    'objetivo' => $objetivo !== '' ? $objetivo : null,
    'mensaje' => $mensaje !== '' ? $mensaje : null,
    'form_source' => $formSource !== '' ? $formSource : 'landing-impulsa-emprende',
    'ip_address' => $ipAddress,
    'user_agent' => $userAgent,
]);

if (!$ok) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'No se pudo enviar el mensaje en este momento. Intenta nuevamente en unos minutos.',
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Gracias. Tu mensaje fue enviado y te responderemos a la brevedad.',
]);
