<?php

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
require_once __DIR__ . '/../models/admin_proceso_emprendeModel.php';

$userId = (int) $_SESSION['user_id'];
$model = new AdminProcesoEmprendeModel($pdo);

$perfil = $model->obtenerPerfil($userId);
$resumenProceso = $model->obtenerResumenProceso();
$emprendedoresProceso = $model->obtenerEstadoEmprendedores();
