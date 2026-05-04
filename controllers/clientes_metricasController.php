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
require_once __DIR__ . '/../models/clientes_metricasModel.php';

$userId = (int) $_SESSION['user_id'];
$model = new ClientesMetricasModel($pdo);

$perfil = $model->obtenerPerfil($userId);
$pageParam = $model->obtenerParametroPagina($userId);
$kpisVisitas = [];
$contactos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));

    if ($action === 'update_contact_state') {
        $contactId = (int) ($_POST['contact_id'] ?? 0);
        $state = trim((string) ($_POST['state'] ?? ''));

        if ($pageParam !== null) {
            $model->actualizarEstadoContacto($pageParam, $contactId, $state);
        }

        header('Location: /views/clientes/clientes_metricas.php');
        exit;
    }
}

if ($pageParam !== null) {
    $kpisVisitas = $model->obtenerKpisVisitasMensuales($pageParam);
    $contactos = $model->obtenerContactos($pageParam);
}
