<?php
declare(strict_types=1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/marketing_campaignsModel.php';

$userId = (int) $_SESSION['user_id'];
$rol = (string) ($_SESSION['rol'] ?? '');
$model = new MarketingCampaignsModel($pdo);
if (!$model->isStaff($rol)) {
    header('Location: /index.php');
    exit;
}
$flash = ['type' => (string) ($_GET['flash_type'] ?? ''), 'message' => (string) ($_GET['flash_message'] ?? '')];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'save_campaign') {
            $model->guardarCampania($_POST, $userId);
            header('Location: /views/marketing/marketing_campaigns.php?flash_type=success&flash_message=' . urlencode('Campania creada.'));
            exit;
        }
        if ($action === 'save_commercial_metrics') {
            $model->guardarMetricasComerciales($_POST, $userId);
            header('Location: /views/marketing/marketing_campaigns.php?flash_type=success&flash_message=' . urlencode('Metricas comerciales guardadas.'));
            exit;
        }
        if ($action === 'save_client_code') {
            $model->guardarCodigoCliente((int) ($_POST['user_auth_id'] ?? 0), (string) ($_POST['client_code'] ?? ''), (string) ($_POST['display_name'] ?? ''));
            header('Location: /views/marketing/marketing_campaigns.php?flash_type=success&flash_message=' . urlencode('Codigo actualizado.'));
            exit;
        }
    } catch (Throwable) {
        $flash = ['type' => 'error', 'message' => 'No se pudo guardar la informacion.'];
    }
}
$perfil = $model->obtenerPerfil($userId);
$suscripciones = $model->listarSuscripciones();
$campanias = $model->listarCampanias();
$usuariosFinales = $model->listarUsuariosFinales();
