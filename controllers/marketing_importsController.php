<?php
declare(strict_types=1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/marketing_importsModel.php';

$userId = (int) $_SESSION['user_id'];
$rol = (string) ($_SESSION['rol'] ?? '');
$model = new MarketingImportsModel($pdo);
if (!$model->isStaff($rol)) {
    header('Location: /index.php');
    exit;
}
$flash = ['type' => (string) ($_GET['flash_type'] ?? ''), 'message' => (string) ($_GET['flash_message'] ?? '')];
$importResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'upload_csv') {
            $file = $_FILES['meta_csv'] ?? null;
            if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new RuntimeException('No se recibio un CSV valido.');
            }
            $name = (string) ($file['name'] ?? '');
            if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'csv') {
                throw new RuntimeException('Solo se aceptan archivos CSV.');
            }
            $importResult = $model->procesarCsvMeta((string) $file['tmp_name'], $name, $userId);
            $flash = $importResult['ok'] ? ['type' => 'success', 'message' => 'CSV procesado.'] : ['type' => 'error', 'message' => (string) $importResult['error']];
        }
        if ($action === 'save_mapping') {
            $model->guardarMappingManual($_POST, $userId);
            header('Location: /views/marketing/marketing_imports.php?flash_type=success&flash_message=' . urlencode('Mapping guardado.'));
            exit;
        }
    } catch (Throwable $e) {
        $flash = ['type' => 'error', 'message' => $e->getMessage() ?: 'No se pudo procesar el CSV.'];
    }
}
$perfil = $model->obtenerPerfil($userId);
$importaciones = $model->listarImportaciones();
$filasSinResolver = $model->listarFilasSinResolver();
$campanias = $model->listarCampanias();
