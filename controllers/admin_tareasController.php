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
require_once __DIR__ . '/../models/admin_tareasModel.php';

$userId = (int) $_SESSION['user_id'];
$model = new AdminTareasModel($pdo);

$perfil = $model->obtenerPerfil($userId);
$prioridadesDefcon = [
    1 => 'DEFCON 1 - Dejen lo que estan haciendo y resuelvan esto',
    2 => 'DEFCON 2 - El dia de hoy debe estar listo',
    3 => 'DEFCON 3 - Con plazo de entrega corto',
    4 => 'DEFCON 4 - Con plazo de entrega medio',
    5 => 'DEFCON 5 - Con plazo de entrega largo',
];
$estadosTarea = [
    'pendiente' => 'Pendiente',
    'en_progreso' => 'En progreso',
    'completada' => 'Completada',
    'cancelada' => 'Cancelada',
];

$flash = [
    'type' => '',
    'message' => '',
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'create_task') {
    $nombreTarea = trim((string) ($_POST['nombre_tarea'] ?? ''));
    $responsableUserId = (int) ($_POST['responsable_user_id'] ?? 0);
    $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
    $fechaEntrega = trim((string) ($_POST['fecha_entrega'] ?? ''));
    $prioridadDefcon = (int) ($_POST['prioridad_defcon'] ?? 0);
    $reportaA = trim((string) ($_POST['reporta_a'] ?? ''));

    $fechaValida = false;
    if ($fechaEntrega !== '') {
        $fecha = DateTimeImmutable::createFromFormat('Y-m-d', $fechaEntrega);
        $fechaValida = $fecha instanceof DateTimeImmutable && $fecha->format('Y-m-d') === $fechaEntrega;
    }

    if ($nombreTarea === '' || $responsableUserId <= 0 || $descripcion === '' || !$fechaValida || !isset($prioridadesDefcon[$prioridadDefcon]) || $reportaA === '') {
        $flash = [
            'type' => 'error',
            'message' => 'Completa todos los campos obligatorios antes de crear la tarea.',
        ];
    } else {
        try {
            $ok = $model->crearTarea([
                'nombre_tarea' => $nombreTarea,
                'responsable_user_id' => $responsableUserId,
                'descripcion' => $descripcion,
                'fecha_entrega' => $fechaEntrega,
                'prioridad_defcon' => $prioridadDefcon,
                'reporta_a' => $reportaA,
                'created_by_user_id' => $userId,
            ]);

            $flash = [
                'type' => $ok ? 'success' : 'error',
                'message' => $ok ? 'Tarea creada correctamente.' : 'No se pudo crear la tarea.',
            ];
        } catch (Throwable $e) {
            $flash = [
                'type' => 'error',
                'message' => 'No se pudo crear la tarea. Error de base de datos: ' . $e->getMessage(),
            ];
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $taskId = (int) ($_POST['task_id'] ?? 0);
    $estado = (string) ($_POST['estado'] ?? '');

    if ($taskId <= 0 || !isset($estadosTarea[$estado])) {
        $flash = [
            'type' => 'error',
            'message' => 'No se pudo actualizar el estado de la tarea.',
        ];
    } else {
        try {
            $ok = $model->actualizarEstado($taskId, $estado);
            $flash = [
                'type' => $ok ? 'success' : 'error',
                'message' => $ok ? 'Estado actualizado correctamente.' : 'No se pudo actualizar el estado de la tarea.',
            ];
        } catch (Throwable $e) {
            $flash = [
                'type' => 'error',
                'message' => 'No se pudo actualizar el estado. Verifica que la tabla admin_tareas este actualizada.',
            ];
        }
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'update_priority') {
    $taskId = (int) ($_POST['task_id'] ?? 0);
    $prioridadDefcon = (int) ($_POST['prioridad_defcon'] ?? 0);

    if ($taskId <= 0 || !isset($prioridadesDefcon[$prioridadDefcon])) {
        $flash = [
            'type' => 'error',
            'message' => 'No se pudo actualizar la prioridad de la tarea.',
        ];
    } else {
        try {
            $ok = $model->actualizarPrioridad($taskId, $prioridadDefcon);
            $flash = [
                'type' => $ok ? 'success' : 'error',
                'message' => $ok ? 'Prioridad actualizada correctamente.' : 'No se pudo actualizar la prioridad de la tarea.',
            ];
        } catch (Throwable $e) {
            $flash = [
                'type' => 'error',
                'message' => 'No se pudo actualizar la prioridad. Verifica que la tabla admin_tareas este actualizada.',
            ];
        }
    }
}

$usuariosOperativos = [];
$tareas = [];
$databaseWarning = '';

try {
    $usuariosOperativos = $model->obtenerUsuariosOperativos();
} catch (Throwable $e) {
    $databaseWarning = 'No se pudieron cargar los usuarios operativos. Verifica que el rol impulsa_colaborador exista en user_auth.';
}

try {
    $tareas = $model->obtenerTareas();
} catch (Throwable $e) {
    $databaseWarning = 'No se pudieron cargar las tareas. Error de base de datos: ' . $e->getMessage();
}
