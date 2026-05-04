<?php
declare(strict_types=1);

ini_set('session.gc_maxlifetime', '31536000');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/login_creador_sistemasModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /public/login_creador_sistemas.php');
    exit;
}

$correo = strtolower(trim((string) ($_POST['correo'] ?? '')));
$contrasena = (string) ($_POST['contrasena'] ?? '');

if ($correo === '' || $contrasena === '') {
    registrarAuditoria($pdo, [
        'evento' => 'login_creador_sistemas_error',
        'estado' => 'empty',
        'datos' => ['correo' => $correo],
    ]);

    header('Location: /public/login_creador_sistemas.php?login_error=empty&correo=' . urlencode($correo));
    exit;
}

$model = new LoginCreadorSistemasModel($pdo);
$usuario = $model->autenticar($correo, $contrasena);

if ($usuario === null || ($usuario['rol'] ?? '') !== 'impulsa_cliente') {
    registrarAuditoria($pdo, [
        'evento' => 'login_creador_sistemas_error',
        'estado' => 'invalid',
        'datos' => ['correo' => $correo],
    ]);

    header('Location: /public/login_creador_sistemas.php?login_error=invalid&correo=' . urlencode($correo));
    exit;
}

session_regenerate_id(true);

$_SESSION['user_id'] = (int) $usuario['id'];
$_SESSION['correo'] = (string) $usuario['correo'];
$_SESSION['rol'] = (string) $usuario['rol'];
$_SESSION['nombre'] = $usuario['nombre'];
$_SESSION['apellido'] = $usuario['apellido'];
$_SESSION['apodo'] = $usuario['apodo'];
$_SESSION['avatar_path'] = $usuario['avatar_path'];
$_SESSION['fecha_nacimiento'] = $usuario['fecha_nacimiento'];

registrarAuditoria($pdo, [
    'evento' => 'login_creador_sistemas_ok',
    'estado' => 'ok',
    'usuario_id' => (int) $usuario['id'],
    'usuario_login' => (string) $usuario['correo'],
    'rol' => (string) $usuario['rol'],
]);

header('Location: ' . resolverRutaPorRol((string) $usuario['rol']));
exit;
