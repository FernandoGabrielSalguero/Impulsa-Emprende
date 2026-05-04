<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

if (($_SESSION['rol'] ?? '') !== 'impulsa_administrador') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/admin_usersModel.php';
require_once __DIR__ . '/../mail/Mail.php';

use SVE\Mail\Mailer;

$userId = (int) $_SESSION['user_id'];
$model = new AdminUsersModel($pdo);

if (isset($_GET['ajax']) && $_GET['ajax'] === 'search') {
    header('Content-Type: application/json; charset=utf-8');

    $filtros = $model->normalizarFiltros($_GET);
    $usuarios = $model->obtenerUsuarios($filtros);
    $totalGeneral = $model->contarUsuarios();
    $totalFiltrados = $model->contarUsuarios($filtros);

    echo json_encode([
        'ok' => true,
        'total' => $totalGeneral,
        'filtrados' => $totalFiltrados,
        'usuarios' => $usuarios,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'reenviar_verificacion') {
    header('Content-Type: application/json; charset=utf-8');

    $targetUserId = (int) ($_POST['user_id'] ?? 0);
    if ($targetUserId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Usuario invalido.']);
        exit;
    }

    $usuario = $model->obtenerUsuarioPorId($targetUserId);
    if (empty($usuario)) {
        echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado.']);
        exit;
    }

    if (!empty($usuario['email_verified_at'])) {
        echo json_encode(['ok' => false, 'error' => 'El correo de este usuario ya fue verificado.']);
        exit;
    }

    try {
        $token = bin2hex(random_bytes(32));
    } catch (\Throwable) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo generar el token de verificacion.']);
        exit;
    }

    if (!$model->actualizarTokenVerificacion($targetUserId, $token)) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo actualizar el token de verificacion.']);
        exit;
    }

    $model->marcarCorreoPendiente($targetUserId);

    $appUrl = rtrim((string) (getenv('APP_URL') ?: ''), '/');
    $verifyUrl = $appUrl . '/auth/verificar.php?token=' . urlencode($token);

    $mailResult = Mailer::enviarVerificacionCorreo([
        'correo' => (string) ($usuario['correo'] ?? ''),
        'link' => $verifyUrl,
        'user_auth_id' => $targetUserId,
    ]);

    if (!$mailResult['ok']) {
        echo json_encode([
            'ok' => false,
            'error' => $mailResult['error'] ?? 'No se pudo reenviar el correo de verificacion.',
        ]);
        exit;
    }

    registrarAuditoria($pdo, [
        'evento' => 'admin_reenvio_verificacion',
        'estado' => 'ok',
        'usuario_id' => $_SESSION['user_id'] ?? null,
        'usuario_login' => $_SESSION['correo'] ?? null,
        'rol' => $_SESSION['rol'] ?? null,
        'entidad' => 'user_auth',
        'entidad_id' => $targetUserId,
        'datos' => ['correo' => $usuario['correo'] ?? null],
    ]);

    echo json_encode([
        'ok' => true,
        'message' => 'Correo de verificacion reenviado correctamente.',
    ]);
    exit;
}

$perfil = $model->obtenerPerfil($userId);
$filtros = $model->normalizarFiltros($_GET);
$totalUsuarios = $model->contarUsuarios();
$usuariosFiltrados = $model->obtenerUsuarios($filtros);
$totalFiltrados = $model->contarUsuarios($filtros);
$hayFiltrosActivos = $filtros['nombre'] !== '' || $filtros['correo'] !== '';
