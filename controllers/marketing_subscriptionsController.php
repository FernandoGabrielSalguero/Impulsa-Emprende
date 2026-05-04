<?php
declare(strict_types=1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/marketing_subscriptionsModel.php';

$userId = (int) $_SESSION['user_id'];
$rol = (string) ($_SESSION['rol'] ?? '');
$model = new MarketingSubscriptionsModel($pdo);
if (!$model->isStaff($rol)) {
    header('Location: /index.php');
    exit;
}
$flash = ['type' => (string) ($_GET['flash_type'] ?? ''), 'message' => (string) ($_GET['flash_message'] ?? '')];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'update_subscription') {
    try {
        $model->actualizarSuscripcion($_POST);
        header('Location: /views/marketing/marketing_subscriptions.php?flash_type=success&flash_message=' . urlencode('Solicitud actualizada.'));
        exit;
    } catch (Throwable) {
        $flash = ['type' => 'error', 'message' => 'No se pudo actualizar la solicitud.'];
    }
}
$perfil = $model->obtenerPerfil($userId);
$responsables = $model->listarResponsablesMarketing();
$suscripciones = $model->listarSuscripciones(['status' => trim((string) ($_GET['status'] ?? ''))]);
