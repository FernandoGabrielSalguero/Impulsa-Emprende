<?php
declare(strict_types=1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/marketing_plansModel.php';

$userId = (int) $_SESSION['user_id'];
$rol = (string) ($_SESSION['rol'] ?? '');
$model = new MarketingPlansModel($pdo);
if (!$model->isStaff($rol)) {
    header('Location: /index.php');
    exit;
}

$flash = ['type' => (string) ($_GET['flash_type'] ?? ''), 'message' => (string) ($_GET['flash_message'] ?? '')];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'save_plan') {
            $model->guardarPlan($_POST, $userId);
            header('Location: /views/marketing/marketing_plans.php?flash_type=success&flash_message=' . urlencode('Plan guardado.'));
            exit;
        }
        if ($action === 'save_feature') {
            $model->guardarFeature($_POST);
            header('Location: /views/marketing/marketing_plans.php?flash_type=success&flash_message=' . urlencode('Beneficio agregado.'));
            exit;
        }
        if ($action === 'save_price') {
            $model->guardarPrecio($_POST);
            header('Location: /views/marketing/marketing_plans.php?flash_type=success&flash_message=' . urlencode('Precio agregado.'));
            exit;
        }
        if ($action === 'change_status') {
            $model->cambiarEstadoPlan((int) ($_POST['plan_id'] ?? 0), (string) ($_POST['status'] ?? 'draft'));
            header('Location: /views/marketing/marketing_plans.php?flash_type=success&flash_message=' . urlencode('Estado actualizado.'));
            exit;
        }
    } catch (Throwable $e) {
        $flash = ['type' => 'error', 'message' => 'No se pudo guardar. Verifica que el SQL del modulo este aplicado.'];
    }
}
$perfil = $model->obtenerPerfil($userId);
$planes = $model->listarPlanes(false);
