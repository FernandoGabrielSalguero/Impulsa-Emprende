<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

if (($_SESSION['rol'] ?? '') !== 'impulsa_administrador') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/admin_newprojectModel.php';

$userId = (int) $_SESSION['user_id'];
$model = new AdminNewProjectModel($pdo);

$perfil = $model->obtenerPerfil($userId);
$solicitudes = $model->obtenerSolicitudes();
$solicitudesLandingExternal = $model->obtenerSolicitudesLandingExternal();
