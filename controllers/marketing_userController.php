<?php
declare(strict_types=1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/marketing_userModel.php';

$userId = (int) $_SESSION['user_id'];
$rol = (string) ($_SESSION['rol'] ?? '');
$model = new MarketingUserModel($pdo);
if (!in_array($rol, MarketingDashboardModel::USER_ROLES, true)) {
    header('Location: /index.php');
    exit;
}

$flash = ['type' => (string) ($_GET['flash_type'] ?? ''), 'message' => (string) ($_GET['flash_message'] ?? '')];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'request_plan') {
    try {
        $result = $model->solicitarPlan($userId, $rol, (int) ($_POST['pricing_option_id'] ?? 0));
        $type = $result['ok'] ? 'success' : 'error';
        $message = $result['ok'] ? 'Solicitud enviada. El equipo de marketing se contactara para coordinar los proximos pasos.' : (string) $result['error'];
        header('Location: /views/marketing/marketing_user.php?flash_type=' . $type . '&flash_message=' . urlencode($message));
        exit;
    } catch (Throwable) {
        $flash = ['type' => 'error', 'message' => 'No se pudo enviar la solicitud.'];
    }
}

$perfil = $model->obtenerPerfil($userId);
$estadoMarketing = $model->obtenerEstadoUsuarioMarketing($userId, $rol);
$planesPublicados = $model->listarPlanes(true);
$suscripcionActual = $estadoMarketing['subscription'] ?? [];
$campaniasUsuario = $model->listarCampanias(['user_id' => $userId]);
$metricasUsuario = $model->listarMetricasCampanias(['user_id' => $userId]);
$reportesUsuario = $model->listarReportes(['user_id' => $userId, 'visible_only' => true]);
