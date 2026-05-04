<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metodo no permitido']);
    exit;
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/modal_perfilModel.php';

function crearRecursoImagenDesdeArchivo(string $tmpPath, string $mimeType)
{
    if (!extension_loaded('gd')) {
        return null;
    }

    switch ($mimeType) {
        case 'image/jpeg':
            return function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($tmpPath) : null;
        case 'image/png':
            return function_exists('imagecreatefrompng') ? @imagecreatefrompng($tmpPath) : null;
        case 'image/webp':
            return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpPath) : null;
        default:
            return null;
    }
}

function guardarAvatarProcesado($sourceImage, string $destinationPath, int $sourceWidth, int $sourceHeight): bool
{
    if (!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled')) {
        return false;
    }

    $targetSize = 512;
    $cropSize = min($sourceWidth, $sourceHeight);
    $srcX = (int) floor(($sourceWidth - $cropSize) / 2);
    $srcY = (int) floor(($sourceHeight - $cropSize) / 2);
    $targetImage = imagecreatetruecolor($targetSize, $targetSize);

    if (!$targetImage) {
        return false;
    }

    if (function_exists('imagealphablending')) {
        imagealphablending($targetImage, true);
    }
    if (function_exists('imagesavealpha')) {
        imagesavealpha($targetImage, true);
    }

    $white = imagecolorallocate($targetImage, 255, 255, 255);
    imagefill($targetImage, 0, 0, $white);

    $copied = imagecopyresampled(
        $targetImage,
        $sourceImage,
        0,
        0,
        $srcX,
        $srcY,
        $targetSize,
        $targetSize,
        $cropSize,
        $cropSize
    );

    if (!$copied) {
        imagedestroy($targetImage);
        return false;
    }

    $saved = function_exists('imagewebp') ? @imagewebp($targetImage, $destinationPath, 88) : false;
    imagedestroy($targetImage);

    return $saved;
}

$userId = (int) $_SESSION['user_id'];
$nombre = trim((string) ($_POST['nombre'] ?? ''));
$apellido = trim((string) ($_POST['apellido'] ?? ''));
$apodo = trim((string) ($_POST['apodo'] ?? ''));
$fechaNacimiento = trim((string) ($_POST['fecha_nacimiento'] ?? ''));
$whatsapp = trim((string) ($_POST['whatsapp'] ?? ''));
$perfilObligatorio = isset($_POST['perfil_obligatorio']) ? 1 : 0;
$permisonCorreo = isset($_POST['permison_correo']) ? 1 : 0;
$permisonWhatsapp = isset($_POST['permison_whatsapp']) ? 1 : 0;

if ($fechaNacimiento !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacimiento)) {
    $fechaNacimiento = '';
}

if ($whatsapp !== '') {
    $wa = preg_replace('/\D/', '', $whatsapp);
    if (!ctype_digit($wa) || strlen($wa) < 10 || strlen($wa) > 11) {
        echo json_encode([
            'ok' => false,
            'error' => 'El numero de WhatsApp debe tener solo numeros, con codigo de area sin 0 y numero sin 15. Ejemplo: 2616686062',
        ]);
        exit;
    }
    $whatsapp = $wa;
}

if ($perfilObligatorio === 1 && ($nombre === '' || $apellido === '' || $apodo === '' || $whatsapp === '')) {
    echo json_encode(['ok' => false, 'error' => 'Necesitas completar nombre, apellido, apodo y WhatsApp para continuar.']);
    exit;
}

$model = new PerfilModel($pdo);
$perfilActual = $model->obtenerPerfil($userId);
$avatarPath = (string) ($perfilActual['avatar_path'] ?? '');

if (isset($_FILES['avatar']) && is_array($_FILES['avatar']) && (int) ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['avatar'];
    $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($uploadError !== UPLOAD_ERR_OK) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo subir el avatar.']);
        exit;
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $fileSize = (int) ($file['size'] ?? 0);
    $mimeType = function_exists('mime_content_type') ? (string) mime_content_type($tmpPath) : '';
    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if ($fileSize <= 0 || $fileSize > 3 * 1024 * 1024) {
        echo json_encode(['ok' => false, 'error' => 'El avatar debe pesar hasta 3 MB.']);
        exit;
    }

    if (!isset($allowedMime[$mimeType])) {
        echo json_encode(['ok' => false, 'error' => 'El avatar debe ser JPG, PNG o WEBP.']);
        exit;
    }

    $avatarDir = __DIR__ . '/../../assets/images/avatar';
    if (!is_dir($avatarDir) && !mkdir($avatarDir, 0775, true) && !is_dir($avatarDir)) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo preparar la carpeta del avatar.']);
        exit;
    }

    $imageInfo = @getimagesize($tmpPath);
    $sourceWidth = (int) ($imageInfo[0] ?? 0);
    $sourceHeight = (int) ($imageInfo[1] ?? 0);

    if ($sourceWidth < 300 || $sourceHeight < 300) {
        echo json_encode(['ok' => false, 'error' => 'El avatar debe tener al menos 300x300 pixeles.']);
        exit;
    }

    $sourceImage = crearRecursoImagenDesdeArchivo($tmpPath, $mimeType);
    if (!$sourceImage) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo procesar el avatar en el servidor.']);
        exit;
    }

    $avatarFilename = 'avatar_' . $userId . '_' . time() . '.webp';
    $avatarAbsolutePath = $avatarDir . '/' . $avatarFilename;

    if (!guardarAvatarProcesado($sourceImage, $avatarAbsolutePath, $sourceWidth, $sourceHeight)) {
        imagedestroy($sourceImage);
        echo json_encode(['ok' => false, 'error' => 'No se pudo guardar el avatar procesado.']);
        exit;
    }
    imagedestroy($sourceImage);

    if ($avatarPath !== '') {
        $avatarAnteriorAbs = __DIR__ . '/../../' . ltrim(str_replace('\\', '/', $avatarPath), '/');
        if (is_file($avatarAnteriorAbs) && realpath(dirname($avatarAnteriorAbs)) === realpath($avatarDir)) {
            @unlink($avatarAnteriorAbs);
        }
    }

    $avatarPath = 'assets/images/avatar/' . $avatarFilename;
}

$ok = $model->actualizarInfo($userId, [
    'nombre' => $nombre,
    'apellido' => $apellido,
    'apodo' => $apodo,
    'avatar_path' => $avatarPath !== '' ? $avatarPath : null,
    'fecha_nacimiento' => $fechaNacimiento,
    'whatsapp' => $whatsapp,
    'permison_correo' => $permisonCorreo,
    'permison_whatsapp' => $permisonWhatsapp,
]);

if (!$ok) {
    echo json_encode(['ok' => false, 'error' => 'Error al guardar en la base de datos']);
    exit;
}

$_SESSION['nombre'] = $nombre ?: null;
$_SESSION['apellido'] = $apellido ?: null;
$_SESSION['apodo'] = $apodo ?: null;
$_SESSION['avatar_path'] = $avatarPath !== '' ? $avatarPath : null;
$_SESSION['fecha_nacimiento'] = $fechaNacimiento ?: null;

echo json_encode([
    'ok' => true,
    'avatar_url' => obtenerAvatarUrl($_SESSION['avatar_path'] ?? null),
]);
