<?php
declare(strict_types=1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/marketing_dashboardModel.php';

$userId = (int) $_SESSION['user_id'];
$rol = (string) ($_SESSION['rol'] ?? '');
$model = new MarketingDashboardModel($pdo);

if (!$model->isStaff($rol)) {
    header('Location: /index.php');
    exit;
}

$perfil = $model->obtenerPerfil($userId);
$filters = [
    'user_id' => (int) ($_GET['user_id'] ?? 0) ?: null,
    'campaign_id' => (int) ($_GET['campaign_id'] ?? 0) ?: null,
    'date_from' => trim((string) ($_GET['date_from'] ?? '')) ?: null,
    'date_to' => trim((string) ($_GET['date_to'] ?? '')) ?: null,
];
$usuariosFinales = $model->listarUsuariosFinales();
$campanias = $model->listarCampanias();
$metricas = $model->listarMetricasCampanias(array_filter($filters));
$suscripciones = $model->listarSuscripciones();
