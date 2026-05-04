<?php

ini_set('session.gc_maxlifetime', 31536000);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/AuthModel.php';

$token  = trim((string)($_GET['token'] ?? ''));
$auth   = new AuthModel($pdo);
$result = $auth->verificarToken($token);

if (!$result['ok']) {
    registrarAuditoria($pdo, [
        'evento' => 'verify_email_error',
        'estado' => $result['error'],
        'datos'  => ['token_prefix' => substr($token, 0, 8)], // solo primeros 8 chars para el log
    ]);

    $errorKey = $result['error'] === 'already_verified' ? 'already_verified' : 'invalid_token';
    header('Location: /index.php?verify_error=' . $errorKey);
    exit;
}

registrarAuditoria($pdo, [
    'evento'     => 'verify_email_ok',
    'estado'     => 'ok',
    'usuario_id' => $result['id'],
]);

header('Location: /index.php?verify_ok=1');
exit;
