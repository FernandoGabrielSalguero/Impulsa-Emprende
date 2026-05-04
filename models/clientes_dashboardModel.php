<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

class ClientesDashboardModel
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
                ua.created_at,
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

    public function obtenerProyectosCliente(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                p.id,
                p.project_name,
                p.project_type,
                p.status,
                p.priority,
                p.progress_percent,
                p.start_date,
                p.target_delivery_date,
                p.actual_delivery_date,
                p.summary,
                p.client_name,
                COALESCE(ui.apodo, ui.nombre, ua.correo) AS manager_label
             FROM projects p
             INNER JOIN user_auth ua ON ua.id = p.manager_user_id
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             WHERE p.client_user_id = :user_id
               AND p.client_visible = 1
             ORDER BY p.updated_at DESC, p.id DESC"
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function obtenerProyectoDetallado(int $projectId, int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                p.*,
                COALESCE(ui.apodo, ui.nombre, ua.correo) AS manager_label,
                ua.correo AS manager_email
             FROM projects p
             INNER JOIN user_auth ua ON ua.id = p.manager_user_id
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             WHERE p.id = :id
               AND p.client_user_id = :user_id
               AND p.client_visible = 1
             LIMIT 1"
        );
        $stmt->execute([
            'id' => $projectId,
            'user_id' => $userId,
        ]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        if (empty($project)) {
            return [];
        }

        $phasesStmt = $this->db->prepare(
            "SELECT id, title, description, duration_days, phase_order, status, due_date, completed_at
             FROM project_phases
             WHERE project_id = :id
             ORDER BY phase_order ASC, id ASC"
        );
        $phasesStmt->execute(['id' => $projectId]);
        $project['phases'] = $phasesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $deliverablesStmt = $this->db->prepare(
            "SELECT d.id, d.phase_id, d.title, d.description, d.deliverable_type, d.status, d.due_date, d.delivered_at, ph.title AS phase_title
             FROM project_deliverables d
             LEFT JOIN project_phases ph ON ph.id = d.phase_id
             WHERE d.project_id = :id
               AND d.client_visible = 1
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
               AND d.client_visible = 1
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
            "SELECT u.id, u.title, u.message, u.created_at, u.progress_delta, ph.title AS phase_title
             FROM project_updates u
             LEFT JOIN project_phases ph ON ph.id = u.phase_id
             WHERE u.project_id = :id
               AND u.visible_to_client = 1
             ORDER BY u.created_at DESC, u.id DESC"
        );
        $updatesStmt->execute(['id' => $projectId]);
        $project['updates'] = $updatesStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $project['contract'] = $this->obtenerContratoProyecto($projectId, $userId);
        $project['task_total'] = count($tasks);
        $project['task_completed'] = count(array_filter($tasks, static function (array $task): bool {
            return !empty($task['is_completed']);
        }));
        $project['pending_tasks'] = array_values(array_filter($tasks, static function (array $task): bool {
            return empty($task['is_completed']);
        }));

        return $project;
    }

    public function obtenerContratoProyecto(int $projectId, int $userId): array
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
                    pc.created_at,
                    pc.updated_at
                 FROM project_contracts pc
                 INNER JOIN projects p ON p.id = pc.project_id
                 WHERE pc.project_id = :project_id
                   AND p.client_user_id = :user_id
                   AND p.client_visible = 1
                 LIMIT 1"
            );
            $stmt->execute([
                'project_id' => $projectId,
                'user_id' => $userId,
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    public function firmarContratoProyecto(int $projectId, int $userId, string $signerName, string $signerIp): array
    {
        try {
            $contract = $this->obtenerContratoProyecto($projectId, $userId);
            if (empty($contract)) {
                return ['ok' => false, 'error' => 'not_found'];
            }

            if (!empty($contract['is_signed'])) {
                return ['ok' => false, 'error' => 'already_signed'];
            }

            $stmt = $this->db->prepare(
                "UPDATE project_contracts pc
                 INNER JOIN projects p ON p.id = pc.project_id
                 SET pc.is_signed = 1,
                     pc.signed_at = NOW(),
                     pc.signed_by_user_id = :user_id,
                     pc.signer_full_name = :signer_name,
                     pc.signer_ip = :signer_ip,
                     pc.updated_at = NOW()
                 WHERE pc.project_id = :project_id
                   AND p.client_user_id = :user_id
                   AND pc.is_signed = 0"
            );
            $stmt->execute([
                'project_id' => $projectId,
                'user_id' => $userId,
                'signer_name' => $signerName !== '' ? $signerName : null,
                'signer_ip' => $signerIp !== '' ? $signerIp : null,
            ]);

            return ['ok' => $stmt->rowCount() > 0, 'error' => $stmt->rowCount() > 0 ? null : 'db_error'];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => 'db_error'];
        }
    }
}
