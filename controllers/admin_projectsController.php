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
require_once __DIR__ . '/../models/admin_projectsModel.php';

function projectPostText(string $key, int $maxLength = 65535): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    if ($maxLength > 0 && mb_strlen($value) > $maxLength) {
        $value = mb_substr($value, 0, $maxLength);
    }

    return $value;
}

function projectPostArray(string $key): array
{
    $value = $_POST[$key] ?? [];
    return is_array($value) ? $value : [];
}

function parsePhaseRows(array $titles, array $descriptions, array $durations, array $dates = []): array
{
    $items = [];
    $count = max(count($titles), count($descriptions), count($durations), count($dates));

    for ($i = 0; $i < $count; $i++) {
        $title = trim((string) ($titles[$i] ?? ''));
        $description = trim((string) ($descriptions[$i] ?? ''));
        $duration = (int) ($durations[$i] ?? 0);
        $dueDate = trim((string) ($dates[$i] ?? ''));

        if ($title === '') {
            continue;
        }

        $items[] = [
            'title' => $title,
            'description' => $description,
            'duration_days' => $duration > 0 ? $duration : null,
            'due_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate) ? $dueDate : '',
            'status' => 'pending',
        ];
    }

    return $items;
}

function isValidProjectDate(string $date): bool
{
    return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
}

function moveToBusinessDay(DateTimeImmutable $date): DateTimeImmutable
{
    $weekday = (int) $date->format('N');
    if ($weekday === 6) {
        return $date->modify('+2 days');
    }
    if ($weekday === 7) {
        return $date->modify('+1 day');
    }

    return $date;
}

function addBusinessDays(DateTimeImmutable $startDate, int $days): DateTimeImmutable
{
    $current = moveToBusinessDay($startDate);
    if ($days <= 1) {
        return $current;
    }

    $remaining = $days - 1;
    while ($remaining > 0) {
        $current = moveToBusinessDay($current->modify('+1 day'));
        $remaining--;
    }

    return $current;
}

function buildComputedSchedule(string $startDate, array $phases): array
{
    if (!isValidProjectDate($startDate) || empty($phases)) {
        return [
            'start_date' => isValidProjectDate($startDate) ? $startDate : '',
            'target_delivery_date' => '',
            'phases' => $phases,
        ];
    }

    $cursor = moveToBusinessDay(new DateTimeImmutable($startDate));
    $computedPhases = [];
    $lastDueDate = '';

    foreach ($phases as $phase) {
        $durationDays = max(0, (int) ($phase['duration_days'] ?? 0));
        $phaseDueDate = '';

        if ($durationDays > 0) {
            $phaseEnd = addBusinessDays($cursor, $durationDays);
            $phaseDueDate = $phaseEnd->format('Y-m-d');
            $lastDueDate = $phaseDueDate;
            $cursor = moveToBusinessDay($phaseEnd->modify('+1 day'));
        }

        $phase['due_date'] = $phaseDueDate;
        $computedPhases[] = $phase;
    }

    return [
        'start_date' => $cursor ? moveToBusinessDay(new DateTimeImmutable($startDate))->format('Y-m-d') : '',
        'target_delivery_date' => $lastDueDate,
        'phases' => $computedPhases,
    ];
}

function parseDeliverableRows(array $titles, array $phaseIndexes, array $dates, array $taskTitlesMap, array $taskDatesMap): array
{
    $items = [];
    $count = max(count($titles), count($phaseIndexes), count($dates), count($taskTitlesMap), count($taskDatesMap));

    for ($i = 0; $i < $count; $i++) {
        $title = trim((string) ($titles[$i] ?? ''));
        $dueDate = trim((string) ($dates[$i] ?? ''));
        $phaseIndex = trim((string) ($phaseIndexes[$i] ?? ''));

        if ($title === '') {
            continue;
        }

        $taskTitles = isset($taskTitlesMap[$i]) && is_array($taskTitlesMap[$i]) ? $taskTitlesMap[$i] : [];
        $taskDates = isset($taskDatesMap[$i]) && is_array($taskDatesMap[$i]) ? $taskDatesMap[$i] : [];
        $tasks = [];
        $taskCount = max(count($taskTitles), count($taskDates));

        for ($taskIndex = 0; $taskIndex < $taskCount; $taskIndex++) {
            $taskTitle = trim((string) ($taskTitles[$taskIndex] ?? ''));
            $taskDate = trim((string) ($taskDates[$taskIndex] ?? ''));

            if ($taskTitle === '') {
                continue;
            }

            $tasks[] = [
                'title' => $taskTitle,
                'due_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $taskDate) ? $taskDate : '',
                'is_completed' => 0,
            ];
        }

        $items[] = [
            'title' => $title,
            'phase_index' => $phaseIndex !== '' && is_numeric($phaseIndex) ? max(0, (int) $phaseIndex) : -1,
            'description' => '',
            'due_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate) ? $dueDate : '',
            'deliverable_type' => 'other',
            'status' => 'pending',
            'client_visible' => 1,
            'tasks' => $tasks,
        ];
    }

    return $items;
}

function parsePhaseDeliverables(array $titlesMap, array $descriptionsMap, array $completedMap): array
{
    $items = [];
    $phaseIndexes = array_unique(array_merge(array_keys($titlesMap), array_keys($descriptionsMap), array_keys($completedMap)));

    foreach ($phaseIndexes as $phaseIndex) {
        $titles = isset($titlesMap[$phaseIndex]) && is_array($titlesMap[$phaseIndex]) ? $titlesMap[$phaseIndex] : [];
        $descriptions = isset($descriptionsMap[$phaseIndex]) && is_array($descriptionsMap[$phaseIndex]) ? $descriptionsMap[$phaseIndex] : [];
        $completed = isset($completedMap[$phaseIndex]) && is_array($completedMap[$phaseIndex]) ? $completedMap[$phaseIndex] : [];
        $count = max(count($titles), count($descriptions), count($completed));

        for ($i = 0; $i < $count; $i++) {
            $title = trim((string) ($titles[$i] ?? ''));
            $description = trim((string) ($descriptions[$i] ?? ''));
            $isCompleted = (string) ($completed[$i] ?? '0') === '1';

            if ($title === '') {
                continue;
            }

            if (!isset($items[$phaseIndex])) {
                $items[$phaseIndex] = [];
            }

            $items[$phaseIndex][] = [
                'title' => $title,
                'description' => $description,
                'status' => $isCompleted ? 'delivered' : 'pending',
            ];
        }
    }

    ksort($items);

    return $items;
}

function flattenPhaseDeliverables(array $deliverablesByPhase): array
{
    $items = [];

    foreach ($deliverablesByPhase as $phaseIndex => $deliverables) {
        if (!is_array($deliverables)) {
            continue;
        }

        foreach ($deliverables as $deliverable) {
            $title = trim((string) ($deliverable['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $items[] = [
                'title' => $title,
                'phase_index' => is_numeric($phaseIndex) ? max(0, (int) $phaseIndex) : -1,
                'description' => trim((string) ($deliverable['description'] ?? '')),
                'due_date' => '',
                'deliverable_type' => 'other',
                'status' => (string) ($deliverable['status'] ?? 'pending'),
                'client_visible' => 1,
                'tasks' => [],
            ];
        }
    }

    return $items;
}

function buildProjectRedirect(int $projectId, string $sourceType, int $sourceId, string $flashType, string $message): string
{
    $query = [
        'project_id' => $projectId > 0 ? $projectId : null,
        'source_type' => $sourceType !== '' ? $sourceType : null,
        'source_id' => $sourceId > 0 ? $sourceId : null,
        'flash_type' => $flashType,
        'flash_message' => $message,
    ];

    return '/views/admin/admin_projects.php?' . http_build_query(array_filter($query, static function ($value) {
        return $value !== null && $value !== '';
    }));
}

function defaultPhasesForType(string $projectType): array
{
    $base = [
        ['title' => 'Descubrimiento', 'description' => 'Alineacion inicial, revision del alcance y prioridades.', 'duration_days' => 2, 'due_date' => '', 'status' => 'pending'],
        ['title' => 'Planificacion funcional', 'description' => 'Definicion de roadmap, entregables y validaciones.', 'duration_days' => 3, 'due_date' => '', 'status' => 'pending'],
        ['title' => 'Desarrollo e implementacion', 'description' => 'Ejecucion tecnica del proyecto acordado.', 'duration_days' => 10, 'due_date' => '', 'status' => 'pending'],
        ['title' => 'Validacion', 'description' => 'Revision funcional con el cliente y ajustes finales.', 'duration_days' => 3, 'due_date' => '', 'status' => 'pending'],
        ['title' => 'Entrega', 'description' => 'Cierre, despliegue y entrega formal.', 'duration_days' => 2, 'due_date' => '', 'status' => 'pending'],
    ];

    if ($projectType === 'landing_page' || $projectType === 'website') {
        $base[1]['title'] = 'Arquitectura y contenido';
        $base[2]['title'] = 'Diseno y maquetacion';
        $base[3]['title'] = 'Revision y ajustes';
    }

    return $base;
}

function defaultDeliverablesForType(string $projectType): array
{
    $items = [
        [
            'title' => 'Roadmap aprobado',
            'phase_index' => 1,
            'description' => '',
            'due_date' => '',
            'deliverable_type' => 'document',
            'status' => 'pending',
            'client_visible' => 1,
            'tasks' => [
                ['title' => 'Definir alcance', 'due_date' => '', 'is_completed' => 0],
                ['title' => 'Ordenar fases', 'due_date' => '', 'is_completed' => 0],
                ['title' => 'Validar roadmap con el cliente', 'due_date' => '', 'is_completed' => 0],
            ],
        ],
        [
            'title' => 'Version inicial funcional',
            'phase_index' => 2,
            'description' => '',
            'due_date' => '',
            'deliverable_type' => 'development',
            'status' => 'pending',
            'client_visible' => 1,
            'tasks' => [
                ['title' => 'Armar estructura base', 'due_date' => '', 'is_completed' => 0],
                ['title' => 'Desarrollar primera version', 'due_date' => '', 'is_completed' => 0],
                ['title' => 'Subir entorno de pruebas', 'due_date' => '', 'is_completed' => 0],
            ],
        ],
        [
            'title' => 'Entrega final',
            'phase_index' => 4,
            'description' => '',
            'due_date' => '',
            'deliverable_type' => 'deployment',
            'status' => 'pending',
            'client_visible' => 1,
            'tasks' => [
                ['title' => 'Aplicar ajustes finales', 'due_date' => '', 'is_completed' => 0],
                ['title' => 'Desplegar version final', 'due_date' => '', 'is_completed' => 0],
                ['title' => 'Compartir accesos y cierre', 'due_date' => '', 'is_completed' => 0],
            ],
        ],
    ];

    if ($projectType === 'landing_page' || $projectType === 'website') {
        $items[1]['title'] = 'Diseno navegable';
        $items[1]['deliverable_type'] = 'design';
        $items[1]['tasks'] = [
            ['title' => 'Disenar pagina principal', 'due_date' => '', 'is_completed' => 0],
            ['title' => 'Maquetar secciones', 'due_date' => '', 'is_completed' => 0],
            ['title' => 'Validar responsive', 'due_date' => '', 'is_completed' => 0],
        ];
        $items[2]['title'] = 'Sitio publicado';
    }

    return $items;
}

$userId = (int) $_SESSION['user_id'];
$model = new AdminProjectsModel($pdo);
$perfil = $model->obtenerPerfil($userId);
$flash = ['type' => (string) ($_GET['flash_type'] ?? ''), 'message' => (string) ($_GET['flash_message'] ?? '')];

function resolveClientAssignment(
    AdminProjectsModel $model,
    string $clientEmail,
    string $clientName,
    string $clientWhatsapp,
    string $clientPassword,
    bool $shouldCreateUser,
    bool $allowMissingClientData
): array {
    $clientEmail = strtolower(trim($clientEmail));

    if ($clientEmail === '') {
        if ($shouldCreateUser && !$allowMissingClientData) {
            return ['ok' => false, 'error' => 'missing_email_for_user'];
        }

        return ['ok' => true, 'client_user_id' => null];
    }

    $existingUser = $model->obtenerUsuarioPorCorreo($clientEmail);
    if (!empty($existingUser)) {
        if ((string) ($existingUser['rol'] ?? '') !== 'impulsa_cliente') {
            return ['ok' => false, 'error' => 'email_exists_other_role'];
        }

        return ['ok' => true, 'client_user_id' => (int) $existingUser['id']];
    }

    if (!$shouldCreateUser) {
        return ['ok' => true, 'client_user_id' => null];
    }

    if (strlen($clientPassword) < 8) {
        return ['ok' => false, 'error' => 'missing_password_for_user'];
    }

    $createUser = $model->crearUsuarioCliente([
        'correo' => $clientEmail,
        'password' => $clientPassword,
        'nombre' => $clientName,
        'whatsapp' => $clientWhatsapp,
    ]);
    if (!$createUser['ok']) {
        return ['ok' => false, 'error' => 'cannot_create_user'];
    }

    return ['ok' => true, 'client_user_id' => (int) $createUser['user_id']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create_project') {
        $sourceType = projectPostText('source_type', 40);
        $sourceId = (int) ($_POST['source_id'] ?? 0);
        $projectType = projectPostText('project_type', 40);
        $projectName = projectPostText('project_name', 180);
        $clientName = projectPostText('client_name', 150);
        $clientEmail = strtolower(projectPostText('client_email', 190));
        $clientWhatsapp = projectPostText('client_whatsapp', 80);
        $summary = projectPostText('summary');
        $scopeSummary = projectPostText('scope_summary');
        $status = projectPostText('status', 30);
        $priority = projectPostText('priority', 20);
        $startDate = projectPostText('start_date', 10);
        $targetDeliveryDate = projectPostText('target_delivery_date', 10);
        $initialUpdateTitle = projectPostText('initial_update_title', 180);
        $initialUpdateMessage = projectPostText('initial_update_message');
        $clientPassword = (string) ($_POST['client_password'] ?? '');
        $generateClientUser = projectPostText('generate_client_user', 5) !== 'no';
        $userPageParam = projectPostText('user_page_param', 150);

        $allowedProjectTypes = ['software', 'landing_page', 'website', 'manual'];
        $allowedStatus = ['draft', 'planned', 'in_progress', 'paused', 'in_review', 'completed', 'cancelled'];
        $allowedPriority = ['low', 'medium', 'high', 'urgent'];
        $isDraft = $status === 'draft';

        $allowedSourceTypes = ['software_form', 'landing_page_external'];
        if (!in_array($sourceType, $allowedSourceTypes, true) || $sourceId <= 0) {
            header('Location: /views/admin/admin_newproject.php?flash_type=error&flash_message=' . urlencode('Los proyectos solo pueden crearse desde una solicitud.'));
            exit;
        }

        if ($projectName === '' && !$isDraft) {
            header('Location: ' . buildProjectRedirect(0, $sourceType, $sourceId, 'error', 'Completa al menos el nombre del proyecto para crear un proyecto no borrador.'));
            exit;
        }

        if ($clientEmail !== '' && !filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            header('Location: ' . buildProjectRedirect(0, $sourceType, $sourceId, 'error', 'Completa nombre de proyecto, nombre del cliente y un correo valido.'));
            exit;
        }

        if (!in_array($projectType, $allowedProjectTypes, true) || !in_array($status, $allowedStatus, true) || !in_array($priority, $allowedPriority, true)) {
            header('Location: ' . buildProjectRedirect(0, $sourceType, $sourceId, 'error', 'Los valores de tipo, estado o prioridad no son validos.'));
            exit;
        }

        $clientResolution = resolveClientAssignment(
            $model,
            $clientEmail,
            $clientName,
            $clientWhatsapp,
            $clientPassword,
            $generateClientUser,
            $isDraft
        );
        if (!$clientResolution['ok']) {
            $message = 'No se pudo resolver el cliente del proyecto.';
            if ($clientResolution['error'] === 'email_exists_other_role') {
                $message = 'Ya existe un usuario con ese correo, pero no tiene rol impulsa_cliente.';
            } elseif ($clientResolution['error'] === 'missing_password_for_user') {
                $message = 'Para generar el usuario cliente debes indicar una contrasena de al menos 8 caracteres.';
            } elseif ($clientResolution['error'] === 'missing_email_for_user') {
                $message = 'Para generar el usuario cliente debes indicar un correo valido.';
            }

            header('Location: ' . buildProjectRedirect(0, $sourceType, $sourceId, 'error', $message));
            exit;
        }
        $clientUserId = $clientResolution['client_user_id'];

        $phases = parsePhaseRows(
            projectPostArray('phase_title'),
            projectPostArray('phase_description'),
            projectPostArray('phase_duration_days'),
            projectPostArray('phase_due_date')
        );
        if (empty($phases)) {
            $phases = defaultPhasesForType($projectType);
        }
        $schedule = buildComputedSchedule($startDate, $phases);
        $startDate = $schedule['start_date'];
        $targetDeliveryDate = $schedule['target_delivery_date'];
        $phases = $schedule['phases'];

        $deliverablesByPhase = parsePhaseDeliverables(
            projectPostArray('deliverable_title'),
            projectPostArray('deliverable_description'),
            projectPostArray('deliverable_completed')
        );
        $deliverables = flattenPhaseDeliverables($deliverablesByPhase);
        if (empty($deliverables)) {
            $deliverables = defaultDeliverablesForType($projectType);
        }

        $createProject = $model->crearProyecto([
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'project_name' => $projectName !== '' ? $projectName : 'Borrador sin nombre',
            'project_type' => $projectType,
            'client_user_id' => $clientUserId,
            'manager_user_id' => $userId,
            'client_name' => $clientName !== '' ? $clientName : 'Cliente pendiente',
            'client_email' => $clientEmail,
            'client_whatsapp' => $clientWhatsapp,
            'summary' => $summary,
            'scope_summary' => $scopeSummary,
            'status' => $status,
            'priority' => $priority,
            'start_date' => $startDate,
            'target_delivery_date' => $targetDeliveryDate,
            'actual_delivery_date' => '',
            'progress_percent' => 0,
            'phases' => $phases,
            'deliverables' => $deliverables,
            'initial_update_title' => $initialUpdateTitle,
            'initial_update_message' => $initialUpdateMessage,
            'initial_update_visible' => isset($_POST['initial_update_visible']) ? 1 : 0,
        ]);

        if (!$createProject['ok']) {
            if (($createProject['error'] ?? '') === 'source_exists' && !empty($createProject['project_id'])) {
                header('Location: ' . buildProjectRedirect((int) $createProject['project_id'], $sourceType, $sourceId, 'error', 'Ya existe un proyecto vinculado a esa solicitud.'));
                exit;
            }

            header('Location: ' . buildProjectRedirect(0, $sourceType, $sourceId, 'error', 'No se pudo crear el proyecto.'));
            exit;
        }

        if ($clientUserId !== null && $clientUserId > 0) {
            $model->guardarUserPageParam((int) $clientUserId, $userPageParam);
        }

        registrarAuditoria($pdo, [
            'evento' => 'admin_project_create',
            'estado' => 'ok',
            'usuario_id' => $userId,
            'usuario_login' => $_SESSION['correo'] ?? null,
            'rol' => $_SESSION['rol'] ?? null,
            'entidad' => 'projects',
            'entidad_id' => $createProject['project_id'],
            'datos' => ['client_email' => $clientEmail, 'source_type' => $sourceType, 'source_id' => $sourceId],
        ]);

        header('Location: ' . buildProjectRedirect((int) $createProject['project_id'], $sourceType, $sourceId, 'success', 'Proyecto creado correctamente.'));
        exit;
    }

    if ($action === 'update_project') {
        $projectId = (int) ($_POST['project_id'] ?? 0);
        $sourceType = projectPostText('source_type', 40);
        $sourceId = (int) ($_POST['source_id'] ?? 0);
        $projectType = projectPostText('project_type', 40);
        $projectName = projectPostText('project_name', 180);
        $clientName = projectPostText('client_name', 150);
        $clientEmail = strtolower(projectPostText('client_email', 190));
        $clientWhatsapp = projectPostText('client_whatsapp', 80);
        $summaryText = projectPostText('summary');
        $scopeText = projectPostText('scope_summary');
        $status = projectPostText('status', 30);
        $startDate = projectPostText('start_date', 10);
        $targetDeliveryDate = projectPostText('target_delivery_date', 10);
        $actualDeliveryDate = projectPostText('actual_delivery_date', 10);
        $clientPassword = (string) ($_POST['client_password'] ?? '');
        $generateClientUser = projectPostText('generate_client_user', 5) !== 'no';
        $isDraft = $status === 'draft';

        if ($clientEmail !== '' && !filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) {
            header('Location: ' . buildProjectRedirect($projectId, $sourceType, $sourceId, 'error', 'El correo del cliente no es valido.'));
            exit;
        }

        $clientResolution = resolveClientAssignment(
            $model,
            $clientEmail,
            $clientName,
            $clientWhatsapp,
            $clientPassword,
            $generateClientUser,
            $isDraft
        );
        if (!$clientResolution['ok']) {
            $message = 'No se pudo actualizar el cliente del proyecto.';
            if ($clientResolution['error'] === 'email_exists_other_role') {
                $message = 'Ya existe un usuario con ese correo, pero no tiene rol impulsa_cliente.';
            } elseif ($clientResolution['error'] === 'missing_password_for_user') {
                $message = 'Para generar el usuario cliente debes indicar una contrasena de al menos 8 caracteres.';
            } elseif ($clientResolution['error'] === 'missing_email_for_user') {
                $message = 'Para generar el usuario cliente debes indicar un correo valido.';
            }
            header('Location: ' . buildProjectRedirect($projectId, $sourceType, $sourceId, 'error', $message));
            exit;
        }

        $currentProject = $projectId > 0 ? $model->obtenerProyectoDetallado($projectId) : [];
        $currentPhases = array_map(static function (array $phase): array {
            return [
                'title' => (string) ($phase['title'] ?? ''),
                'description' => (string) ($phase['description'] ?? ''),
                'duration_days' => (int) ($phase['duration_days'] ?? 0),
                'due_date' => (string) ($phase['due_date'] ?? ''),
                'status' => (string) ($phase['status'] ?? 'pending'),
            ];
        }, $currentProject['phases'] ?? []);
        $schedule = buildComputedSchedule($startDate, $currentPhases);
        $startDate = $schedule['start_date'];
        $targetDeliveryDate = $schedule['target_delivery_date'];

        $ok = $projectId > 0 && $model->actualizarProyecto($projectId, [
            'project_name' => $projectName !== '' ? $projectName : 'Borrador sin nombre',
            'project_type' => $projectType,
            'client_user_id' => $clientResolution['client_user_id'],
            'client_name' => $clientName !== '' ? $clientName : 'Cliente pendiente',
            'client_email' => $clientEmail,
            'client_whatsapp' => $clientWhatsapp,
            'summary' => $summaryText,
            'scope_summary' => $scopeText,
            'status' => $status,
            'start_date' => $startDate,
            'target_delivery_date' => $targetDeliveryDate,
            'actual_delivery_date' => $actualDeliveryDate,
        ]);
        header('Location: ' . buildProjectRedirect($projectId, $sourceType, $sourceId, $ok ? 'success' : 'error', $ok ? 'Proyecto actualizado.' : 'No se pudo actualizar el proyecto.'));
        exit;
    }

    if ($action === 'update_project_plan') {
        $projectId = (int) ($_POST['project_id'] ?? 0);
        $sourceType = projectPostText('source_type', 40);
        $sourceId = (int) ($_POST['source_id'] ?? 0);
        $userPageParam = projectPostText('user_page_param', 150);
        $phases = parsePhaseRows(
            projectPostArray('phase_title'),
            projectPostArray('phase_description'),
            projectPostArray('phase_duration_days'),
            projectPostArray('phase_due_date')
        );
        $deliverablesByPhase = parsePhaseDeliverables(
            projectPostArray('deliverable_title'),
            projectPostArray('deliverable_description'),
            projectPostArray('deliverable_completed')
        );
        $currentProject = $projectId > 0 ? $model->obtenerProyectoDetallado($projectId) : [];
        $schedule = buildComputedSchedule((string) ($currentProject['start_date'] ?? ''), $phases);
        $phases = $schedule['phases'];
        $targetDeliveryDate = $schedule['target_delivery_date'];

        if ($projectId <= 0 || empty($phases)) {
            header('Location: ' . buildProjectRedirect($projectId, $sourceType, $sourceId, 'error', 'Debes cargar al menos una fase.'));
            exit;
        }

        $ok = $model->actualizarPlanProyecto($projectId, $phases, $deliverablesByPhase, $targetDeliveryDate);
        if ($ok && !empty($currentProject['client_user_id'])) {
            $model->guardarUserPageParam((int) $currentProject['client_user_id'], $userPageParam);
        }
        header('Location: ' . buildProjectRedirect($projectId, $sourceType, $sourceId, $ok ? 'success' : 'error', $ok ? 'Plan del proyecto actualizado.' : 'No se pudo actualizar el plan del proyecto.'));
        exit;
    }

    if ($action === 'save_project_contract') {
        $projectId = (int) ($_POST['project_id'] ?? 0);
        $sourceType = projectPostText('source_type', 40);
        $sourceId = (int) ($_POST['source_id'] ?? 0);
        $contractName = projectPostText('contract_name', 180);
        $contractHtml = trim((string) ($_POST['contract_html'] ?? ''));
        $contractText = trim((string) ($_POST['contract_text'] ?? ''));

        if ($projectId <= 0) {
            header('Location: ' . buildProjectRedirect($projectId, $sourceType, $sourceId, 'error', 'No se encontro el proyecto del contrato.'));
            exit;
        }

        $result = $model->guardarContratoProyecto($projectId, [
            'contract_name' => $contractName,
            'contract_html' => $contractHtml,
            'contract_text' => $contractText,
            'user_id' => $userId,
        ]);

        $message = 'Contrato guardado correctamente.';
        if (!$result['ok']) {
            $message = 'No se pudo guardar el contrato.';
            if (($result['error'] ?? '') === 'already_signed') {
                $message = 'El contrato ya fue firmado y no se puede modificar.';
            } elseif (($result['error'] ?? '') === 'missing_data') {
                $message = 'Completa el nombre y el contenido del contrato.';
            }
        } else {
            registrarAuditoria($pdo, [
                'evento' => 'admin_project_contract_save',
                'estado' => 'ok',
                'usuario_id' => $userId,
                'usuario_login' => $_SESSION['correo'] ?? null,
                'rol' => $_SESSION['rol'] ?? null,
                'entidad' => 'project_contracts',
                'entidad_id' => $projectId,
                'datos' => ['project_id' => $projectId, 'contract_name' => $contractName],
            ]);
        }

        header('Location: ' . buildProjectRedirect($projectId, $sourceType, $sourceId, $result['ok'] ? 'success' : 'error', $message));
        exit;
    }

    if ($action === 'update_phase_status') {
        $projectId = (int) ($_POST['project_id'] ?? 0);
        $sourceType = projectPostText('source_type', 40);
        $sourceId = (int) ($_POST['source_id'] ?? 0);
        $phaseId = (int) ($_POST['phase_id'] ?? 0);
        $status = projectPostText('phase_status', 20);
        $ok = $projectId > 0 && $phaseId > 0 && $model->actualizarEstadoFase($projectId, $phaseId, $status);
        header('Location: ' . buildProjectRedirect($projectId, $sourceType, $sourceId, $ok ? 'success' : 'error', $ok ? 'Fase actualizada.' : 'No se pudo actualizar la fase.'));
        exit;
    }

    if ($action === 'toggle_task_status') {
        $projectId = (int) ($_POST['project_id'] ?? 0);
        $sourceType = projectPostText('source_type', 40);
        $sourceId = (int) ($_POST['source_id'] ?? 0);
        $taskId = (int) ($_POST['task_id'] ?? 0);
        $isCompleted = isset($_POST['is_completed']);
        $ok = $projectId > 0 && $taskId > 0 && $model->actualizarEstadoTarea($projectId, $taskId, $isCompleted);
        header('Location: ' . buildProjectRedirect($projectId, $sourceType, $sourceId, $ok ? 'success' : 'error', $ok ? 'Tarea actualizada.' : 'No se pudo actualizar la tarea.'));
        exit;
    }

    if ($action === 'add_project_update') {
        $projectId = (int) ($_POST['project_id'] ?? 0);
        $title = projectPostText('update_title', 180);
        $message = projectPostText('update_message');
        $phaseId = (int) ($_POST['phase_id'] ?? 0);
        $progressDelta = trim((string) ($_POST['progress_delta'] ?? ''));

        if ($projectId <= 0 || $title === '' || $message === '') {
            header('Location: /views/admin/admin_projects.php?project_id=' . $projectId . '&flash_type=error&flash_message=' . urlencode('Completa titulo y detalle para registrar el avance.'));
            exit;
        }

        $ok = $model->crearActualizacionProyecto([
            'project_id' => $projectId,
            'phase_id' => $phaseId > 0 ? $phaseId : null,
            'created_by' => $userId,
            'title' => $title,
            'message' => $message,
            'progress_delta' => $progressDelta,
            'visible_to_client' => isset($_POST['visible_to_client']) ? 1 : 0,
        ]);
        header('Location: /views/admin/admin_projects.php?project_id=' . $projectId . '&flash_type=' . ($ok ? 'success' : 'error') . '&flash_message=' . urlencode($ok ? 'Actualizacion registrada.' : 'No se pudo registrar la actualizacion.'));
        exit;
    }
}

$sourceType = trim((string) ($_GET['source_type'] ?? ''));
$sourceId = (int) ($_GET['source_id'] ?? 0);
$selectedProjectId = (int) ($_GET['project_id'] ?? 0);
$sourceRequest = [];
$sourceProject = [];
$existingClientByEmail = [];

if (!in_array($sourceType, ['software_form', 'landing_page_external'], true) || $sourceId <= 0) {
    header('Location: /views/admin/admin_newproject.php?flash_type=error&flash_message=' . urlencode('Selecciona una solicitud para crear o modificar su proyecto.'));
    exit;
}

if ($sourceType === 'software_form' && $sourceId > 0) {
    $sourceRequest = $model->obtenerSolicitudSoftwarePorId($sourceId);
    if (!empty($sourceRequest)) {
        $existingClientByEmail = $model->obtenerUsuarioPorCorreo((string) ($sourceRequest['correo'] ?? ''));
    }
    $sourceProject = $model->obtenerProyectoPorFuente($sourceType, $sourceId);
    if ($selectedProjectId <= 0 && !empty($sourceProject['id'])) {
        $selectedProjectId = (int) $sourceProject['id'];
    }
} elseif ($sourceType === 'landing_page_external' && $sourceId > 0) {
    $sourceRequest = $model->obtenerSolicitudLandingExternalPorId($sourceId);
    if (!empty($sourceRequest)) {
        $existingClientByEmail = $model->obtenerUsuarioPorCorreo((string) ($sourceRequest['correo'] ?? ''));
    }
    $sourceProject = $model->obtenerProyectoPorFuente($sourceType, $sourceId);
    if ($selectedProjectId <= 0 && !empty($sourceProject['id'])) {
        $selectedProjectId = (int) $sourceProject['id'];
    }
}

$selectedProject = $selectedProjectId > 0 ? $model->obtenerProyectoDetallado($selectedProjectId) : [];
$userPageParam = [];
if (!empty($selectedProject['client_user_id'])) {
    $userPageParam = $model->obtenerUserParamPorUsuario((int) $selectedProject['client_user_id']);
}
