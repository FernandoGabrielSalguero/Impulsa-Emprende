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
require_once __DIR__ . '/../models/emprendedor_misionModel.php';

$userId = (int) $_SESSION['user_id'];
$misionModel = new EmprendedorMisionModel($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $aQuienAyudo = trim((string) ($_POST['a_quien_ayudo'] ?? ''));
    $queProblemaResuelvo = trim((string) ($_POST['que_problema_resuelvo'] ?? ''));
    $comoLoResuelvo = trim((string) ($_POST['como_lo_resuelvo'] ?? ''));
    $actual = $misionModel->obtener($userId);
    $completado = !empty($actual['completado']) ? 1 : 0;

    if ($aQuienAyudo === '' || $queProblemaResuelvo === '' || $comoLoResuelvo === '') {
        echo json_encode(['ok' => false, 'error' => 'Para guardar la misión tenés que responder las tres preguntas.']);
        exit;
    }

    $misionEstructura = sprintf(
        'Ayudamos a %s a %s mediante %s',
        $aQuienAyudo,
        $queProblemaResuelvo,
        $comoLoResuelvo
    );

    $completado = 1;

    $ok = $misionModel->guardar($userId, [
        'a_quien_ayudo' => $aQuienAyudo,
        'que_problema_resuelvo' => $queProblemaResuelvo,
        'como_lo_resuelvo' => $comoLoResuelvo,
        'mision_estructura' => $misionEstructura,
        'completado' => $completado,
    ]);

    if (!$ok) {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar la misión en la base de datos.']);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'completado' => $completado,
        'mision_estructura' => $misionEstructura,
    ]);
    exit;
}

$dashboardModel = new EmprendedorDashboardModel($pdo);
$perfil = $dashboardModel->obtenerPerfil($userId);
$mision = $misionModel->obtener($userId);
$landingDisponible = $dashboardModel->puedeAccederLanding($userId);
