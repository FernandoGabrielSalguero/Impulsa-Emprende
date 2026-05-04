<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

class AdminProjectsModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function obtenerPerfil(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                ua.id,
                ua.correo,
                ua.rol,
                ua.email_verified_at,
                ui.nombre,
                ui.apellido,
                ui.apodo,
                ui.avatar_path,
                uc.whatsapp,
                uc.check_correo
             FROM user_auth ua
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             LEFT JOIN user_contacto uc ON uc.user_auth_id = ua.id
             WHERE ua.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerSolicitudSoftwarePorId(int $requestId): array
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM project_scope_request
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $requestId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerSolicitudLandingExternalPorId(int $requestId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                id,
                nombre,
                nombre_proyecto,
                correo,
                whatsapp,
                q3_objetivo AS q1_descripcion,
                q2_actividad AS q2_problema,
                q6_propuestas_destacar AS q8_funciones_minimas,
                q16_dominio_hosting AS q11_integraciones,
                q18_requerimientos_adicionales AS q18_adicional,
                q1_nombre_comercial,
                q2_actividad,
                q3_objetivo,
                q4_publico,
                q5_accion_principal,
                q6_propuestas_destacar,
                q7_diferencial,
                q8_secciones,
                q9_textos,
                q10_contacto,
                q11_material_marca,
                q12_estilo_visual,
                q13_referencias,
                q14_recursos_visuales,
                q15_imagenes_apoyo,
                q16_dominio_hosting,
                q17_correos_corporativos,
                q18_requerimientos_adicionales,
                form_source,
                ip_address,
                user_agent,
                created_at
             FROM landing_page_requests_external
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $requestId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerUsuarioPorCorreo(string $correo): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                ua.id,
                ua.correo,
                ua.rol,
                ua.email_verified_at,
                ui.nombre,
                ui.apellido,
                ui.apodo,
                uc.whatsapp
             FROM user_auth ua
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             LEFT JOIN user_contacto uc ON uc.user_auth_id = ua.id
             WHERE ua.correo = :correo
             LIMIT 1"
        );
        $stmt->execute(['correo' => strtolower(trim($correo))]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerProyectoPorFuente(string $sourceType, int $sourceId): array
    {
        if ($sourceType === '' || $sourceId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT id, project_name, status
             FROM projects
             WHERE source_type = :source_type AND source_id = :source_id
             LIMIT 1"
        );
        $stmt->execute([
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerUserParamPorUsuario(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT user_auth_id, page, created_at, updated_at
             FROM user_params
             WHERE user_auth_id = :user_id
             LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function guardarUserPageParam(int $userId, string $page): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $page = trim($page);
        if ($page === '') {
            $stmt = $this->db->prepare("DELETE FROM user_params WHERE user_auth_id = :user_id");
            return $stmt->execute(['user_id' => $userId]);
        }

        $stmt = $this->db->prepare(
            "INSERT INTO user_params (user_auth_id, page)
             VALUES (:user_id, :page)
             ON DUPLICATE KEY UPDATE
                page = VALUES(page),
                updated_at = NOW()"
        );

        return $stmt->execute([
            'user_id' => $userId,
            'page' => mb_substr($page, 0, 150),
        ]);
    }

    public function crearUsuarioCliente(array $data): array
    {
        $correo = strtolower(trim((string) ($data['correo'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $nombreCompleto = trim((string) ($data['nombre'] ?? ''));
        $whatsapp = trim((string) ($data['whatsapp'] ?? ''));

        if ($correo === '' || $password === '') {
            return ['ok' => false, 'error' => 'missing_credentials'];
        }

        $existente = $this->obtenerUsuarioPorCorreo($correo);
        if (!empty($existente)) {
            return ['ok' => false, 'error' => 'email_exists'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $partesNombre = preg_split('/\s+/', $nombreCompleto) ?: [];
        $nombre = (string) ($partesNombre[0] ?? '');
        $apellido = count($partesNombre) > 1 ? trim(implode(' ', array_slice($partesNombre, 1))) : '';

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "INSERT INTO user_auth (correo, password, rol, verification_token, email_verified_at)
                 VALUES (:correo, :password, 'impulsa_cliente', NULL, NOW())"
            );
            $stmt->execute([
                'correo' => $correo,
                'password' => $hash,
            ]);
            $userId = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare(
                "INSERT INTO user_info (user_auth_id, nombre, apellido, apodo)
                 VALUES (:id, :nombre, :apellido, :apodo)"
            );
            $stmt->execute([
                'id' => $userId,
                'nombre' => $nombre !== '' ? $nombre : null,
                'apellido' => $apellido !== '' ? $apellido : null,
                'apodo' => $nombreCompleto !== '' ? $nombreCompleto : null,
            ]);

            $stmt = $this->db->prepare(
                "INSERT INTO user_contacto (user_auth_id, correo, check_correo, whatsapp, check_whatsapp)
                 VALUES (:id, :correo, 1, :whatsapp, :check_whatsapp)"
            );
            $stmt->execute([
                'id' => $userId,
                'correo' => $correo,
                'whatsapp' => $whatsapp !== '' ? $whatsapp : null,
                'check_whatsapp' => $whatsapp !== '' ? 1 : 0,
            ]);

            $this->db->commit();

            return ['ok' => true, 'user_id' => $userId];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return ['ok' => false, 'error' => 'db_error'];
        }
    }

    public function obtenerResumen(): array
    {
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'planned') AS planned_count,
                SUM(status = 'in_progress') AS in_progress_count,
                SUM(status = 'in_review') AS in_review_count,
                SUM(status = 'completed') AS completed_count
             FROM projects"
        );
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : [];

        return [
            'total' => (int) ($row['total'] ?? 0),
            'planned' => (int) ($row['planned_count'] ?? 0),
            'in_progress' => (int) ($row['in_progress_count'] ?? 0),
            'in_review' => (int) ($row['in_review_count'] ?? 0),
            'completed' => (int) ($row['completed_count'] ?? 0),
        ];
    }

    public function obtenerProyectos(): array
    {
        $stmt = $this->db->query(
            "SELECT
                p.id,
                p.project_name,
                p.project_type,
                p.status,
                p.priority,
                p.progress_percent,
                p.start_date,
                p.target_delivery_date,
                p.created_at,
                p.client_name,
                p.client_email,
                manager.correo AS manager_email,
                COALESCE(manager_info.apodo, manager_info.nombre, manager.correo) AS manager_label,
                (SELECT COUNT(*) FROM project_phases ph WHERE ph.project_id = p.id) AS phase_count,
                (SELECT COUNT(*) FROM project_deliverables d WHERE d.project_id = p.id) AS deliverable_count,
                (SELECT COUNT(*) FROM project_deliverable_tasks t INNER JOIN project_deliverables d ON d.id = t.deliverable_id WHERE d.project_id = p.id) AS task_count
             FROM projects p
             LEFT JOIN user_auth manager ON manager.id = p.manager_user_id
             LEFT JOIN user_info manager_info ON manager_info.user_auth_id = manager.id
             ORDER BY p.created_at DESC"
        );

        return $stmt ? ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    }

    public function obtenerProyectoDetallado(int $projectId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                p.*, 
                client.correo AS client_login_email,
                manager.correo AS manager_login_email,
                COALESCE(client_info.apodo, client_info.nombre, p.client_name) AS client_label,
                COALESCE(manager_info.apodo, manager_info.nombre, manager.correo) AS manager_label
             FROM projects p
             LEFT JOIN user_auth client ON client.id = p.client_user_id
             LEFT JOIN user_info client_info ON client_info.user_auth_id = client.id
             LEFT JOIN user_auth manager ON manager.id = p.manager_user_id
             LEFT JOIN user_info manager_info ON manager_info.user_auth_id = manager.id
             WHERE p.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        if (empty($project)) {
            return [];
        }

        $phasesStmt = $this->db->prepare(
            "SELECT *
             FROM project_phases
             WHERE project_id = :id
             ORDER BY phase_order ASC, id ASC"
        );
        $phasesStmt->execute(['id' => $projectId]);
        $project['phases'] = $phasesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $deliverablesStmt = $this->db->prepare(
            "SELECT d.*, ph.title AS phase_title
             FROM project_deliverables d
             LEFT JOIN project_phases ph ON ph.id = d.phase_id
             WHERE d.project_id = :id
             ORDER BY COALESCE(ph.phase_order, 9999) ASC, d.id ASC"
        );
        $deliverablesStmt->execute(['id' => $projectId]);
        $project['deliverables'] = $deliverablesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $tasksStmt = $this->db->prepare(
            "SELECT
                t.id,
                t.deliverable_id,
                t.task_order,
                t.title,
                t.due_date,
                t.is_completed,
                t.completed_at
             FROM project_deliverable_tasks t
             INNER JOIN project_deliverables d ON d.id = t.deliverable_id
             WHERE d.project_id = :id
             ORDER BY d.id ASC, t.task_order ASC, t.id ASC"
        );
        $tasksStmt->execute(['id' => $projectId]);
        $tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $tasksByDeliverable = [];
        foreach ($tasks as $task) {
            $deliverableId = (int) ($task['deliverable_id'] ?? 0);
            if (!isset($tasksByDeliverable[$deliverableId])) {
                $tasksByDeliverable[$deliverableId] = [];
            }
            $tasksByDeliverable[$deliverableId][] = $task;
        }

        foreach ($project['deliverables'] as &$deliverable) {
            $deliverableId = (int) ($deliverable['id'] ?? 0);
            $deliverableTasks = $tasksByDeliverable[$deliverableId] ?? [];
            $deliverable['tasks'] = $deliverableTasks;
            $deliverable['task_total'] = count($deliverableTasks);
            $deliverable['task_completed'] = count(array_filter($deliverableTasks, static function (array $task): bool {
                return !empty($task['is_completed']);
            }));
        }
        unset($deliverable);

        $updatesStmt = $this->db->prepare(
            "SELECT u.*, ph.title AS phase_title,
                    COALESCE(ui.apodo, ui.nombre, ua.correo) AS author_label
             FROM project_updates u
             LEFT JOIN project_phases ph ON ph.id = u.phase_id
             INNER JOIN user_auth ua ON ua.id = u.created_by
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             WHERE u.project_id = :id
             ORDER BY u.created_at DESC, u.id DESC"
        );
        $updatesStmt->execute(['id' => $projectId]);
        $project['updates'] = $updatesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $project['contract'] = $this->obtenerContratoProyecto($projectId);
        $project['task_total'] = count($tasks);
        $project['task_completed'] = count(array_filter($tasks, static function (array $task): bool {
            return !empty($task['is_completed']);
        }));
        $project['active_tasks'] = array_values(array_filter($tasks, static function (array $task): bool {
            return empty($task['is_completed']);
        }));

        return $project;
    }

    public function obtenerContratoProyecto(int $projectId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT
                    pc.id,
                    pc.project_id,
                    pc.contract_name,
                    pc.contract_html,
                    pc.contract_text,
                    pc.version_number,
                    pc.is_signed,
                    pc.signed_at,
                    pc.signed_by_user_id,
                    pc.signer_full_name,
                    pc.signer_ip,
                    pc.created_by_user_id,
                    pc.updated_by_user_id,
                    pc.created_at,
                    pc.updated_at
                 FROM project_contracts pc
                 WHERE pc.project_id = :project_id
                 LIMIT 1"
            );
            $stmt->execute(['project_id' => $projectId]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    public function guardarContratoProyecto(int $projectId, array $data): array
    {
        try {
            $existing = $this->obtenerContratoProyecto($projectId);
            if (!empty($existing) && !empty($existing['is_signed'])) {
                return ['ok' => false, 'error' => 'already_signed'];
            }

            $contractName = trim((string) ($data['contract_name'] ?? ''));
            $contractHtml = trim((string) ($data['contract_html'] ?? ''));
            $contractText = trim((string) ($data['contract_text'] ?? ''));
            $userId = (int) ($data['user_id'] ?? 0);

            if ($contractName === '' || $contractHtml === '') {
                return ['ok' => false, 'error' => 'missing_data'];
            }

            if (empty($existing)) {
                $stmt = $this->db->prepare(
                    "INSERT INTO project_contracts (
                        project_id, contract_name, contract_html, contract_text, version_number, is_signed,
                        signed_at, signed_by_user_id, signer_full_name, signer_ip,
                        created_by_user_id, updated_by_user_id
                    ) VALUES (
                        :project_id, :contract_name, :contract_html, :contract_text, 1, 0,
                        NULL, NULL, NULL, NULL,
                        :created_by_user_id, :updated_by_user_id
                    )"
                );

                $ok = $stmt->execute([
                    'project_id' => $projectId,
                    'contract_name' => $contractName,
                    'contract_html' => $contractHtml,
                    'contract_text' => $contractText !== '' ? $contractText : null,
                    'created_by_user_id' => $userId > 0 ? $userId : null,
                    'updated_by_user_id' => $userId > 0 ? $userId : null,
                ]);

                return ['ok' => $ok, 'error' => $ok ? null : 'db_error'];
            }

            $stmt = $this->db->prepare(
                "UPDATE project_contracts
                 SET contract_name = :contract_name,
                     contract_html = :contract_html,
                     contract_text = :contract_text,
                     version_number = version_number + 1,
                     updated_by_user_id = :updated_by_user_id,
                     updated_at = NOW()
                 WHERE id = :id
                   AND is_signed = 0"
            );

            $ok = $stmt->execute([
                'id' => (int) $existing['id'],
                'contract_name' => $contractName,
                'contract_html' => $contractHtml,
                'contract_text' => $contractText !== '' ? $contractText : null,
                'updated_by_user_id' => $userId > 0 ? $userId : null,
            ]);

            return ['ok' => $ok, 'error' => $ok ? null : 'db_error'];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => 'db_error'];
        }
    }

    public function crearProyecto(array $data): array
    {
        $existente = $this->obtenerProyectoPorFuente((string) ($data['source_type'] ?? ''), (int) ($data['source_id'] ?? 0));
        if (!empty($existente)) {
            return ['ok' => false, 'error' => 'source_exists', 'project_id' => (int) $existente['id']];
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "INSERT INTO projects (
                    source_type, source_id, project_name, project_type, client_user_id, manager_user_id,
                    client_name, client_email, client_whatsapp, summary, scope_summary, status,
                    priority, start_date, target_delivery_date, actual_delivery_date, progress_percent
                ) VALUES (
                    :source_type, :source_id, :project_name, :project_type, :client_user_id, :manager_user_id,
                    :client_name, :client_email, :client_whatsapp, :summary, :scope_summary, :status,
                    :priority, :start_date, :target_delivery_date, :actual_delivery_date, :progress_percent
                )"
            );
            $stmt->execute([
                'source_type' => $data['source_type'] !== '' ? $data['source_type'] : null,
                'source_id' => !empty($data['source_id']) ? (int) $data['source_id'] : null,
                'project_name' => $data['project_name'],
                'project_type' => $data['project_type'],
                'client_user_id' => $data['client_user_id'],
                'manager_user_id' => $data['manager_user_id'],
                'client_name' => $data['client_name'],
                'client_email' => $data['client_email'],
                'client_whatsapp' => $data['client_whatsapp'] !== '' ? $data['client_whatsapp'] : null,
                'summary' => $data['summary'] !== '' ? $data['summary'] : null,
                'scope_summary' => $data['scope_summary'] !== '' ? $data['scope_summary'] : null,
                'status' => $data['status'],
                'priority' => $data['priority'],
                'start_date' => $data['start_date'] !== '' ? $data['start_date'] : null,
                'target_delivery_date' => $data['target_delivery_date'] !== '' ? $data['target_delivery_date'] : null,
                'actual_delivery_date' => $data['actual_delivery_date'] !== '' ? $data['actual_delivery_date'] : null,
                'progress_percent' => $data['progress_percent'],
            ]);
            $projectId = (int) $this->db->lastInsertId();

            $phaseInsert = $this->db->prepare(
                "INSERT INTO project_phases (project_id, title, description, duration_days, phase_order, status, due_date, completed_at)
                 VALUES (:project_id, :title, :description, :duration_days, :phase_order, :status, :due_date, :completed_at)"
            );
            $phaseIdMap = [];
            foreach ($data['phases'] as $index => $phase) {
                $status = (string) ($phase['status'] ?? 'pending');
                $phaseInsert->execute([
                    'project_id' => $projectId,
                    'title' => $phase['title'],
                    'description' => $phase['description'] !== '' ? $phase['description'] : null,
                    'duration_days' => !empty($phase['duration_days']) ? (int) $phase['duration_days'] : null,
                    'phase_order' => $index + 1,
                    'status' => $status,
                    'due_date' => $phase['due_date'] !== '' ? $phase['due_date'] : null,
                    'completed_at' => $status === 'done' ? date('Y-m-d H:i:s') : null,
                ]);
                $phaseIdMap[$index] = (int) $this->db->lastInsertId();
            }

            $deliverableInsert = $this->db->prepare(
                "INSERT INTO project_deliverables (project_id, phase_id, title, description, deliverable_type, status, due_date, delivered_at, client_visible)
                 VALUES (:project_id, :phase_id, :title, :description, :deliverable_type, :status, :due_date, :delivered_at, :client_visible)"
            );
            $taskInsert = $this->db->prepare(
                "INSERT INTO project_deliverable_tasks (deliverable_id, task_order, title, due_date, is_completed, completed_at)
                 VALUES (:deliverable_id, :task_order, :title, :due_date, :is_completed, :completed_at)"
            );

            foreach ($data['deliverables'] as $deliverable) {
                $status = (string) ($deliverable['status'] ?? 'pending');
                $phaseIndex = isset($deliverable['phase_index']) ? (int) $deliverable['phase_index'] : -1;
                $deliverableInsert->execute([
                    'project_id' => $projectId,
                    'phase_id' => $phaseIdMap[$phaseIndex] ?? null,
                    'title' => $deliverable['title'],
                    'description' => $deliverable['description'] !== '' ? $deliverable['description'] : null,
                    'deliverable_type' => $deliverable['deliverable_type'],
                    'status' => $status,
                    'due_date' => $deliverable['due_date'] !== '' ? $deliverable['due_date'] : null,
                    'delivered_at' => $status === 'delivered' ? date('Y-m-d H:i:s') : null,
                    'client_visible' => !empty($deliverable['client_visible']) ? 1 : 0,
                ]);
                $deliverableId = (int) $this->db->lastInsertId();

                foreach (($deliverable['tasks'] ?? []) as $taskIndex => $task) {
                    $isCompleted = !empty($task['is_completed']);
                    $taskInsert->execute([
                        'deliverable_id' => $deliverableId,
                        'task_order' => $taskIndex + 1,
                        'title' => $task['title'],
                        'due_date' => !empty($task['due_date']) ? $task['due_date'] : null,
                        'is_completed' => $isCompleted ? 1 : 0,
                        'completed_at' => $isCompleted ? date('Y-m-d H:i:s') : null,
                    ]);
                }
            }

            if (!empty($data['initial_update_title']) && !empty($data['initial_update_message'])) {
                $updateStmt = $this->db->prepare(
                    "INSERT INTO project_updates (project_id, phase_id, created_by, title, message, progress_delta, visible_to_client)
                     VALUES (:project_id, NULL, :created_by, :title, :message, NULL, :visible_to_client)"
                );
                $updateStmt->execute([
                    'project_id' => $projectId,
                    'created_by' => $data['manager_user_id'],
                    'title' => $data['initial_update_title'],
                    'message' => $data['initial_update_message'],
                    'visible_to_client' => !empty($data['initial_update_visible']) ? 1 : 0,
                ]);
            }

            $this->syncProjectProgressAndStatuses($projectId);
            $this->db->commit();

            return ['ok' => true, 'project_id' => $projectId];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return ['ok' => false, 'error' => 'db_error'];
        }
    }

    public function actualizarProyecto(int $projectId, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE projects
             SET project_name = :project_name,
                 project_type = :project_type,
                 client_user_id = :client_user_id,
                 client_name = :client_name,
                 client_email = :client_email,
                 client_whatsapp = :client_whatsapp,
                 summary = :summary,
                 scope_summary = :scope_summary,
                 status = :status,
                 start_date = :start_date,
                 target_delivery_date = :target_delivery_date,
                 actual_delivery_date = :actual_delivery_date
             WHERE id = :id"
        );

        return $stmt->execute([
            'id' => $projectId,
            'project_name' => $data['project_name'],
            'project_type' => $data['project_type'],
            'client_user_id' => $data['client_user_id'],
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'],
            'client_whatsapp' => $data['client_whatsapp'] !== '' ? $data['client_whatsapp'] : null,
            'summary' => $data['summary'] !== '' ? $data['summary'] : null,
            'scope_summary' => $data['scope_summary'] !== '' ? $data['scope_summary'] : null,
            'status' => $data['status'],
            'start_date' => $data['start_date'] !== '' ? $data['start_date'] : null,
            'target_delivery_date' => $data['target_delivery_date'] !== '' ? $data['target_delivery_date'] : null,
            'actual_delivery_date' => $data['actual_delivery_date'] !== '' ? $data['actual_delivery_date'] : null,
        ]);
    }

    public function actualizarPlanProyecto(int $projectId, array $phases, array $deliverables, string $targetDeliveryDate = ''): bool
    {
        try {
            $this->db->beginTransaction();

            $deleteTasks = $this->db->prepare(
                "DELETE t
                 FROM project_deliverable_tasks t
                 INNER JOIN project_deliverables d ON d.id = t.deliverable_id
                 WHERE d.project_id = :project_id"
            );
            $deleteTasks->execute(['project_id' => $projectId]);

            $deleteDeliverables = $this->db->prepare(
                "DELETE FROM project_deliverables
                 WHERE project_id = :project_id"
            );
            $deleteDeliverables->execute(['project_id' => $projectId]);

            $deletePhases = $this->db->prepare(
                "DELETE FROM project_phases
                 WHERE project_id = :project_id"
            );
            $deletePhases->execute(['project_id' => $projectId]);

            $phaseInsert = $this->db->prepare(
                "INSERT INTO project_phases (project_id, title, description, duration_days, phase_order, status, due_date, completed_at)
                 VALUES (:project_id, :title, :description, :duration_days, :phase_order, :status, :due_date, :completed_at)"
            );
            $deliverableInsert = $this->db->prepare(
                "INSERT INTO project_deliverables (project_id, phase_id, title, description, deliverable_type, status, due_date, delivered_at, client_visible)
                 VALUES (:project_id, :phase_id, :title, :description, 'other', :status, NULL, :delivered_at, 1)"
            );

            foreach ($phases as $phaseIndex => $phase) {
                $status = (string) ($phase['status'] ?? 'pending');
                $phaseInsert->execute([
                    'project_id' => $projectId,
                    'title' => $phase['title'],
                    'description' => $phase['description'] !== '' ? $phase['description'] : null,
                    'duration_days' => !empty($phase['duration_days']) ? (int) $phase['duration_days'] : null,
                    'phase_order' => $phaseIndex + 1,
                    'status' => $status,
                    'due_date' => !empty($phase['due_date']) ? $phase['due_date'] : null,
                    'completed_at' => $status === 'done' ? date('Y-m-d H:i:s') : null,
                ]);
                $phaseId = (int) $this->db->lastInsertId();

                foreach (($deliverables[$phaseIndex] ?? []) as $deliverable) {
                    $deliverableStatus = (string) ($deliverable['status'] ?? 'pending');
                    $deliverableInsert->execute([
                        'project_id' => $projectId,
                        'phase_id' => $phaseId,
                        'title' => $deliverable['title'],
                        'description' => $deliverable['description'] !== '' ? $deliverable['description'] : null,
                        'status' => $deliverableStatus,
                        'delivered_at' => $deliverableStatus === 'delivered' ? date('Y-m-d H:i:s') : null,
                    ]);
                }
            }

            $updateProject = $this->db->prepare(
                "UPDATE projects
                 SET target_delivery_date = :target_delivery_date
                 WHERE id = :project_id"
            );
            $updateProject->execute([
                'target_delivery_date' => $targetDeliveryDate !== '' ? $targetDeliveryDate : null,
                'project_id' => $projectId,
            ]);

            $this->syncProjectProgressAndStatuses($projectId);
            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return false;
        }
    }

    public function actualizarEstadoFase(int $projectId, int $phaseId, string $status): bool
    {
        $completedAt = $status === 'done' ? date('Y-m-d H:i:s') : null;
        $stmt = $this->db->prepare(
            "UPDATE project_phases
             SET status = :status,
                 completed_at = :completed_at
             WHERE id = :phase_id AND project_id = :project_id"
        );
        $ok = $stmt->execute([
            'status' => $status,
            'completed_at' => $completedAt,
            'phase_id' => $phaseId,
            'project_id' => $projectId,
        ]);

        if ($ok) {
            $this->syncProjectProgressAndStatuses($projectId);
        }

        return $ok;
    }

    public function actualizarEstadoTarea(int $projectId, int $taskId, bool $isCompleted): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE project_deliverable_tasks t
             INNER JOIN project_deliverables d ON d.id = t.deliverable_id
             SET t.is_completed = :is_completed,
                 t.completed_at = :completed_at
             WHERE t.id = :task_id
               AND d.project_id = :project_id"
        );
        $ok = $stmt->execute([
            'is_completed' => $isCompleted ? 1 : 0,
            'completed_at' => $isCompleted ? date('Y-m-d H:i:s') : null,
            'task_id' => $taskId,
            'project_id' => $projectId,
        ]);

        if ($ok) {
            $this->syncProjectProgressAndStatuses($projectId);
        }

        return $ok;
    }

    public function crearActualizacionProyecto(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO project_updates (project_id, phase_id, created_by, title, message, progress_delta, visible_to_client)
             VALUES (:project_id, :phase_id, :created_by, :title, :message, :progress_delta, :visible_to_client)"
        );
        $ok = $stmt->execute([
            'project_id' => $data['project_id'],
            'phase_id' => !empty($data['phase_id']) ? $data['phase_id'] : null,
            'created_by' => $data['created_by'],
            'title' => $data['title'],
            'message' => $data['message'],
            'progress_delta' => isset($data['progress_delta']) && $data['progress_delta'] !== '' ? (int) $data['progress_delta'] : null,
            'visible_to_client' => !empty($data['visible_to_client']) ? 1 : 0,
        ]);

        if ($ok && isset($data['progress_delta']) && $data['progress_delta'] !== '') {
            $this->sumarProgreso((int) $data['project_id'], (int) $data['progress_delta']);
        }

        return $ok;
    }

    private function syncProjectProgressAndStatuses(int $projectId): void
    {
        $taskStatsStmt = $this->db->prepare(
            "SELECT COUNT(*) AS total, SUM(is_completed = 1) AS done_count
             FROM project_deliverable_tasks t
             INNER JOIN project_deliverables d ON d.id = t.deliverable_id
             WHERE d.project_id = :id"
        );
        $taskStatsStmt->execute(['id' => $projectId]);
        $taskStats = $taskStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $taskTotal = (int) ($taskStats['total'] ?? 0);
        $taskDone = (int) ($taskStats['done_count'] ?? 0);

        if ($taskTotal > 0) {
            $progress = (int) round(($taskDone / $taskTotal) * 100);
            $this->actualizarEstadosPorTareas($projectId);
        } else {
            $progress = $this->recalcularProgresoPorEntregables($projectId);
            $this->actualizarEstadosPorEntregables($projectId);
        }

        $update = $this->db->prepare(
            "UPDATE projects
             SET progress_percent = :progress
             WHERE id = :id"
        );
        $update->execute([
            'progress' => $progress,
            'id' => $projectId,
        ]);
    }

    private function recalcularProgresoPorFases(int $projectId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total, SUM(status = 'done') AS done_count
             FROM project_phases
             WHERE project_id = :id"
        );
        $stmt->execute(['id' => $projectId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = (int) ($row['total'] ?? 0);
        $done = (int) ($row['done_count'] ?? 0);

        return $total > 0 ? (int) round(($done / $total) * 100) : 0;
    }

    private function recalcularProgresoPorEntregables(int $projectId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total, SUM(status = 'delivered') AS done_count
             FROM project_deliverables
             WHERE project_id = :id"
        );
        $stmt->execute(['id' => $projectId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $total = (int) ($row['total'] ?? 0);
        $done = (int) ($row['done_count'] ?? 0);

        if ($total > 0) {
            return (int) round(($done / $total) * 100);
        }

        return $this->recalcularProgresoPorFases($projectId);
    }

    private function actualizarEstadosPorEntregables(int $projectId): void
    {
        $phaseStatsStmt = $this->db->prepare(
            "SELECT
                ph.id,
                COUNT(d.id) AS deliverable_total,
                SUM(CASE WHEN d.status = 'delivered' THEN 1 ELSE 0 END) AS deliverable_done
             FROM project_phases ph
             LEFT JOIN project_deliverables d ON d.phase_id = ph.id
             WHERE ph.project_id = :id
             GROUP BY ph.id"
        );
        $phaseStatsStmt->execute(['id' => $projectId]);
        $phaseStats = $phaseStatsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $phaseUpdate = $this->db->prepare(
            "UPDATE project_phases
             SET status = :status,
                 completed_at = :completed_at
             WHERE id = :id"
        );

        foreach ($phaseStats as $phase) {
            $deliverableTotal = (int) ($phase['deliverable_total'] ?? 0);
            $deliverableDone = (int) ($phase['deliverable_done'] ?? 0);
            $status = 'pending';
            $completedAt = null;

            if ($deliverableTotal > 0 && $deliverableDone >= $deliverableTotal) {
                $status = 'done';
                $completedAt = date('Y-m-d H:i:s');
            } elseif ($deliverableDone > 0) {
                $status = 'in_progress';
            }

            $phaseUpdate->execute([
                'status' => $status,
                'completed_at' => $completedAt,
                'id' => (int) $phase['id'],
            ]);
        }
    }

    private function actualizarEstadosPorTareas(int $projectId): void
    {
        $deliverablesStmt = $this->db->prepare(
            "SELECT
                d.id,
                COUNT(t.id) AS task_total,
                SUM(t.is_completed = 1) AS task_done
             FROM project_deliverables d
             LEFT JOIN project_deliverable_tasks t ON t.deliverable_id = d.id
             WHERE d.project_id = :id
             GROUP BY d.id"
        );
        $deliverablesStmt->execute(['id' => $projectId]);
        $deliverables = $deliverablesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $deliverableUpdate = $this->db->prepare(
            "UPDATE project_deliverables
             SET status = :status,
                 delivered_at = :delivered_at
             WHERE id = :id"
        );

        foreach ($deliverables as $deliverable) {
            $taskTotal = (int) ($deliverable['task_total'] ?? 0);
            $taskDone = (int) ($deliverable['task_done'] ?? 0);
            $status = 'pending';
            $deliveredAt = null;

            if ($taskTotal > 0 && $taskDone >= $taskTotal) {
                $status = 'delivered';
                $deliveredAt = date('Y-m-d H:i:s');
            } elseif ($taskDone > 0) {
                $status = 'in_progress';
            }

            $deliverableUpdate->execute([
                'status' => $status,
                'delivered_at' => $deliveredAt,
                'id' => (int) $deliverable['id'],
            ]);
        }

        $phaseStatsStmt = $this->db->prepare(
            "SELECT
                ph.id,
                COUNT(t.id) AS task_total,
                SUM(t.is_completed = 1) AS task_done,
                COUNT(DISTINCT d.id) AS deliverable_total,
                SUM(CASE WHEN d.status = 'delivered' THEN 1 ELSE 0 END) AS deliverable_done
             FROM project_phases ph
             LEFT JOIN project_deliverables d ON d.phase_id = ph.id
             LEFT JOIN project_deliverable_tasks t ON t.deliverable_id = d.id
             WHERE ph.project_id = :id
             GROUP BY ph.id"
        );
        $phaseStatsStmt->execute(['id' => $projectId]);
        $phaseStats = $phaseStatsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $phaseUpdate = $this->db->prepare(
            "UPDATE project_phases
             SET status = :status,
                 completed_at = :completed_at
             WHERE id = :id"
        );

        foreach ($phaseStats as $phase) {
            $taskTotal = (int) ($phase['task_total'] ?? 0);
            $taskDone = (int) ($phase['task_done'] ?? 0);
            $deliverableTotal = (int) ($phase['deliverable_total'] ?? 0);
            $deliverableDone = (int) ($phase['deliverable_done'] ?? 0);
            $status = 'pending';
            $completedAt = null;

            if ($taskTotal > 0 && $taskDone >= $taskTotal) {
                $status = 'done';
                $completedAt = date('Y-m-d H:i:s');
            } elseif ($taskDone > 0) {
                $status = 'in_progress';
            } elseif ($deliverableTotal > 0 && $deliverableDone >= $deliverableTotal) {
                $status = 'done';
                $completedAt = date('Y-m-d H:i:s');
            }

            $phaseUpdate->execute([
                'status' => $status,
                'completed_at' => $completedAt,
                'id' => (int) $phase['id'],
            ]);
        }
    }

    private function sumarProgreso(int $projectId, int $delta): void
    {
        $stmt = $this->db->prepare(
            "UPDATE projects
             SET progress_percent = LEAST(100, GREATEST(0, progress_percent + :delta))
             WHERE id = :id"
        );
        $stmt->execute([
            'delta' => $delta,
            'id' => $projectId,
        ]);
    }
}
