<?php

session_start();

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

if (($_SESSION['rol'] ?? '') !== 'impulsa_emprendedor') {
    header('Location: /index.php');
    exit;
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/emprendedor_dashboardModel.php';
require_once __DIR__ . '/../models/emprendedor_visionModel.php';

$userId = (int) $_SESSION['user_id'];
$visionModel = new EmprendedorVisionModel($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $conversionFutura = trim((string) ($_POST['conversion_futura'] ?? ''));
    $lugarMercado = trim((string) ($_POST['lugar_mercado'] ?? ''));
    $impactoGenerado = trim((string) ($_POST['impacto_generado'] ?? ''));
    $actual = $visionModel->obtener($userId);
    $completado = !empty($actual['completado']) ? 1 : 0;

    if ($conversionFutura === '' || $lugarMercado === '' || $impactoGenerado === '') {
        echo json_encode(['ok' => false, 'error' => 'Para guardar la visión tenés que responder las tres preguntas.']);
        exit;
    }

    $visionEstructura = sprintf(
        'En los próximos 3 a 5 años buscamos convertirnos en %s, ocupar %s y generar %s.',
        $conversionFutura,
        $lugarMercado,
        $impactoGenerado
    );

    $completado = 1;

    $ok = $visionModel->guardar($userId, [
        'conversion_futura' => $conversionFutura,
        'lugar_mercado' => $lugarMercado,
        'impacto_generado' => $impactoGenerado,
        'vision_estructura' => $visionEstructura,
        'completado' => $completado,
    ]);

    if (!$ok) {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar la visión en la base de datos.']);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'completado' => $completado,
        'vision_estructura' => $visionEstructura,
    ]);
    exit;
}

$dashboardModel = new EmprendedorDashboardModel($pdo);
$perfil = $dashboardModel->obtenerPerfil($userId);
$vision = $visionModel->obtener($userId);
$landingDisponible = $dashboardModel->puedeAccederLanding($userId);
