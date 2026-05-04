<?php

session_start();

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
require_once __DIR__ . '/../models/emprendedor_landing_page_requestModel.php';

$userId = (int) $_SESSION['user_id'];
$dashboardModel = new EmprendedorDashboardModel($pdo);

if (!$dashboardModel->puedeAccederLanding($userId)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Primero completá misión, visión y buyer persona.']);
        exit;
    }

    header('Location: /views/emprendedor/emprendedor_dashboard.php');
    exit;
}

$perfil = $dashboardModel->obtenerPerfil($userId);
$model = new EmprendedorLandingPageRequestModel($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $nombreEmprendimiento = trim((string)($_POST['nombre_emprendimiento'] ?? ''));
    $fechaInicio = trim((string)($_POST['fecha_inicio'] ?? ''));
    $descripcion = trim((string)($_POST['descripcion'] ?? ''));
    $dominioRegistrado = isset($_POST['dominio_registrado']) && $_POST['dominio_registrado'] === '1' ? 1 : 0;
    $hostingPropio = isset($_POST['hosting_propio']) && $_POST['hosting_propio'] === '1' ? 1 : 0;
    $cantidadColaboradores = max(1, (int)($_POST['cantidad_colaboradores'] ?? 1));
    $nombreFundador = trim((string)($_POST['nombre_fundador'] ?? ''));
    $vendeProductos = isset($_POST['vende_productos']) && $_POST['vende_productos'] === '1' ? 1 : 0;
    $vendeServicios = isset($_POST['vende_servicios']) && $_POST['vende_servicios'] === '1' ? 1 : 0;
    $yaFactura = isset($_POST['ya_factura']) && $_POST['ya_factura'] === '1' ? 1 : 0;
    $espacioFisico = isset($_POST['espacio_fisico']) && $_POST['espacio_fisico'] === '1' ? 1 : 0;
    $telefonoContacto = trim((string)($_POST['telefono_contacto'] ?? ''));
    $rubroCategoriaId = (int)($_POST['rubro_categoria_id'] ?? 0);
    $rubroSubcategoriaId = (int)($_POST['rubro_subcategoria_id'] ?? 0);

    if ($espacioFisico) {
        $pais = trim((string)($_POST['pais'] ?? '')) ?: null;
        $provincia = trim((string)($_POST['provincia'] ?? '')) ?: null;
        $localidad = trim((string)($_POST['localidad'] ?? '')) ?: null;
        $calle = trim((string)($_POST['calle'] ?? '')) ?: null;
        $numero = trim((string)($_POST['numero'] ?? '')) ?: null;
    } else {
        $pais = $provincia = $localidad = $calle = $numero = null;
    }

    if ($nombreEmprendimiento === '') {
        echo json_encode(['ok' => false, 'error' => 'El nombre del emprendimiento es requerido']);
        exit;
    }
    if ($fechaInicio === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
        echo json_encode(['ok' => false, 'error' => 'La fecha de inicio es requerida y debe ser válida']);
        exit;
    }
    if ($descripcion === '') {
        echo json_encode(['ok' => false, 'error' => 'La descripción es requerida']);
        exit;
    }
    if ($nombreFundador === '') {
        echo json_encode(['ok' => false, 'error' => 'El nombre del fundador es requerido']);
        exit;
    }
    if ($telefonoContacto === '') {
        echo json_encode(['ok' => false, 'error' => 'El teléfono de contacto es requerido']);
        exit;
    }
    if ($rubroCategoriaId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Seleccioná una categoría']);
        exit;
    }
    if ($rubroSubcategoriaId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Seleccioná una subcategoría']);
        exit;
    }
    if (!$vendeProductos && !$vendeServicios) {
        echo json_encode(['ok' => false, 'error' => 'Debés seleccionar al menos una opción: vende productos o vende servicios']);
        exit;
    }
    if (!$model->existeRelacionRubro($rubroCategoriaId, $rubroSubcategoriaId)) {
        echo json_encode(['ok' => false, 'error' => 'La subcategoría no corresponde a la categoría seleccionada']);
        exit;
    }
    if ($espacioFisico && ($pais === null || $provincia === null || $localidad === null || $calle === null || $numero === null)) {
        echo json_encode(['ok' => false, 'error' => 'Si tenés espacio físico, completá todos los campos de dirección']);
        exit;
    }

    $ok = $model->guardar($userId, [
        'nombre_emprendimiento' => $nombreEmprendimiento,
        'fecha_inicio' => $fechaInicio,
        'descripcion' => $descripcion,
        'dominio_registrado' => $dominioRegistrado,
        'hosting_propio' => $hostingPropio,
        'cantidad_colaboradores' => $cantidadColaboradores,
        'nombre_fundador' => $nombreFundador,
        'vende_productos' => $vendeProductos,
        'vende_servicios' => $vendeServicios,
        'ya_factura' => $yaFactura,
        'espacio_fisico' => $espacioFisico,
        'rubro_categoria_id' => $rubroCategoriaId,
        'rubro_subcategoria_id' => $rubroSubcategoriaId,
        'pais' => $pais,
        'provincia' => $provincia,
        'localidad' => $localidad,
        'calle' => $calle,
        'numero' => $numero,
        'telefono_contacto' => $telefonoContacto,
        'completado' => 1,
    ]);

    if (!$ok) {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar en la base de datos']);
        exit;
    }

    echo json_encode(['ok' => true]);
    exit;
}

$request = $model->obtener($userId);
$rubros = $model->obtenerRubros();
$landingDisponible = true;

