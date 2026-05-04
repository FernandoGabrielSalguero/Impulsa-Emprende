<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

if (($_SESSION['rol'] ?? '') !== 'impulsa_cliente') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/clientes_dashboardModel.php';

$userId = (int) $_SESSION['user_id'];
$model = new ClientesDashboardModel($pdo);
$flash = [
    'type' => (string) ($_GET['flash_type'] ?? ''),
    'message' => (string) ($_GET['flash_message'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));
    $selectedProjectId = (int) ($_POST['project_id'] ?? 0);

    if ($action === 'sign_project_contract') {
        $acceptContract = isset($_POST['accept_contract']) && (string) $_POST['accept_contract'] === '1';

        if ($selectedProjectId <= 0) {
            header('Location: /views/clientes/clientes_dashboard.php?flash_type=error&flash_message=' . urlencode('No se encontro el proyecto del contrato.'));
            exit;
        }

        if (!$acceptContract) {
            header('Location: /views/clientes/clientes_dashboard.php?project_id=' . $selectedProjectId . '&flash_type=error&flash_message=' . urlencode('Debes confirmar la lectura y aceptacion del contrato para poder firmarlo.'));
            exit;
        }

        $signerName = trim((string) ($_SESSION['apodo'] ?? $_SESSION['nombre'] ?? $_SESSION['correo'] ?? ''));
        $signerIp = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
        $result = $model->firmarContratoProyecto($selectedProjectId, $userId, $signerName, $signerIp);

        if ($result['ok']) {
            registrarAuditoria($pdo, [
                'evento' => 'client_project_contract_signed',
                'estado' => 'ok',
                'usuario_id' => $userId,
                'usuario_login' => $_SESSION['correo'] ?? null,
                'rol' => $_SESSION['rol'] ?? null,
                'entidad' => 'project_contracts',
                'entidad_id' => $selectedProjectId,
            ]);

            header('Location: /views/clientes/clientes_dashboard.php?project_id=' . $selectedProjectId . '&flash_type=success&flash_message=' . urlencode('Contrato firmado correctamente.'));
            exit;
        }

        $message = 'No se pudo firmar el contrato.';
        if (($result['error'] ?? '') === 'already_signed') {
            $message = 'El contrato ya habia sido firmado.';
        } elseif (($result['error'] ?? '') === 'not_found') {
            $message = 'No se encontro un contrato disponible para este proyecto.';
        }

        header('Location: /views/clientes/clientes_dashboard.php?project_id=' . $selectedProjectId . '&flash_type=error&flash_message=' . urlencode($message));
        exit;
    }
}

$perfil = $model->obtenerPerfil($userId);
$projects = $model->obtenerProyectosCliente($userId);
$selectedProjectId = (int) ($_GET['project_id'] ?? 0);

if ($selectedProjectId <= 0 && !empty($projects)) {
    $selectedProjectId = (int) ($projects[0]['id'] ?? 0);
}

$selectedProject = $selectedProjectId > 0 ? $model->obtenerProyectoDetallado($selectedProjectId, $userId) : [];
