<?php
declare(strict_types=1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/marketing_reportsModel.php';

$userId = (int) $_SESSION['user_id'];
$rol = (string) ($_SESSION['rol'] ?? '');
$model = new MarketingReportsModel($pdo);
if (!$model->isStaff($rol)) {
    header('Location: /index.php');
    exit;
}
$flash = ['type' => (string) ($_GET['flash_type'] ?? ''), 'message' => (string) ($_GET['flash_message'] ?? '')];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'save_report') {
    try {
        $model->guardarReporte($_POST, $userId);
        header('Location: /views/marketing/marketing_reports.php?flash_type=success&flash_message=' . urlencode('Reporte guardado.'));
        exit;
    } catch (Throwable) {
        $flash = ['type' => 'error', 'message' => 'No se pudo guardar el reporte.'];
    }
}
$perfil = $model->obtenerPerfil($userId);
$suscripciones = $model->listarSuscripciones();
$reportes = $model->listarReportes();
