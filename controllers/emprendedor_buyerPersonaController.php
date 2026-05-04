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
require_once __DIR__ . '/../models/emprendedor_buyerPersonaModel.php';

$userId = (int) $_SESSION['user_id'];
$buyerPersonaModel = new EmprendedorBuyerPersonaModel($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $fields = [
        'cliente_ideal',
        'edad_etapa_vida',
        'ocupacion_realidad_diaria',
        'problema_necesidad',
        'preocupacion_frustracion',
        'objetivo_mejora',
        'motivacion_busqueda',
        'freno_dudas',
        'criterio_eleccion',
        'busqueda_informacion',
        'decision_compra',
        'motivo_eleccion',
    ];

    $data = [];
    foreach ($fields as $field) {
        $data[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    foreach ($fields as $field) {
        if ($data[$field] === '') {
            echo json_encode(['ok' => false, 'error' => 'Para guardar el buyer persona tenés que responder todas las preguntas.']);
            exit;
        }
    }

    $buyerPersonaEstructura = implode("\n\n", [
        "Mi cliente ideal es {$data['cliente_ideal']}.",
        "Tiene {$data['edad_etapa_vida']} y su realidad diaria está marcada por {$data['ocupacion_realidad_diaria']}.",
        "Hoy necesita resolver {$data['problema_necesidad']} y esto le genera {$data['preocupacion_frustracion']}.",
        "Quiere lograr {$data['objetivo_mejora']} y lo motiva a buscar una solución {$data['motivacion_busqueda']}.",
        "Antes de comprar, lo frenan {$data['freno_dudas']} y al elegir prioriza {$data['criterio_eleccion']}.",
        "Busca información a través de {$data['busqueda_informacion']} y toma la decisión de compra de la siguiente manera: {$data['decision_compra']}.",
        "Elegiría mi propuesta porque {$data['motivo_eleccion']}.",
    ]);

    $ok = $buyerPersonaModel->guardar($userId, $data + [
        'buyer_persona_estructura' => $buyerPersonaEstructura,
        'completado' => 1,
    ]);

    if (!$ok) {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar el buyer persona en la base de datos.']);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'completado' => 1,
        'buyer_persona_estructura' => $buyerPersonaEstructura,
    ]);
    exit;
}

$dashboardModel = new EmprendedorDashboardModel($pdo);
$perfil = $dashboardModel->obtenerPerfil($userId);
$buyerPersona = $buyerPersonaModel->obtener($userId);
$landingDisponible = $dashboardModel->puedeAccederLanding($userId);
