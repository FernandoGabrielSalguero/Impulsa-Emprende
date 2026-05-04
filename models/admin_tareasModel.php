<?php

declare(strict_types=1);

class AdminTareasModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function obtenerPerfil(int $userId): ?array
    {
        $sql = "SELECT
                    ua.id AS user_id,
                    ua.correo,
                    ui.nombre,
                    ui.apellido,
                    ui.apodo,
                    ui.avatar_path
                FROM user_auth ua
                LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
                WHERE ua.id = :user_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($perfil) ? $perfil : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerUsuariosOperativos(): array
    {
        $sql = "SELECT
                    ua.id,
                    ua.correo,
                    ua.rol,
                    ui.nombre,
                    ui.apellido,
                    ui.apodo
                FROM user_auth ua
                LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
                WHERE ua.rol IN ('impulsa_administrador', 'impulsa_colaborador')
                ORDER BY COALESCE(NULLIF(ui.nombre, ''), ua.correo), ui.apellido";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param array{
     *     nombre_tarea: string,
     *     responsable_user_id: int,
     *     descripcion: string,
     *     fecha_entrega: string,
     *     prioridad_defcon: int,
     *     reporta_a: string,
     *     created_by_user_id: int
     * } $data
     */
    public function crearTarea(array $data): bool
    {
        $sql = "INSERT INTO admin_tareas
                    (nombre_tarea, responsable_user_id, descripcion, fecha_entrega, prioridad_defcon, reporta_a, created_by_user_id)
                VALUES
                    (:nombre_tarea, :responsable_user_id, :descripcion, :fecha_entrega, :prioridad_defcon, :reporta_a, :created_by_user_id)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre_tarea' => $data['nombre_tarea'],
            'responsable_user_id' => $data['responsable_user_id'],
            'descripcion' => $data['descripcion'],
            'fecha_entrega' => $data['fecha_entrega'],
            'prioridad_defcon' => $data['prioridad_defcon'],
            'reporta_a' => $data['reporta_a'],
            'created_by_user_id' => $data['created_by_user_id'],
        ]);
    }

    public function actualizarEstado(int $taskId, string $estado): bool
    {
        $completedAtSql = $estado === 'completada' ? 'NOW()' : 'NULL';
        $sql = "UPDATE admin_tareas
                SET estado = :estado,
                    completed_at = {$completedAtSql}
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'estado' => $estado,
            'id' => $taskId,
        ]);
    }

    public function actualizarPrioridad(int $taskId, int $prioridadDefcon): bool
    {
        $sql = "UPDATE admin_tareas
                SET prioridad_defcon = :prioridad_defcon
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'prioridad_defcon' => $prioridadDefcon,
            'id' => $taskId,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerTareas(): array
    {
        $sql = "SELECT
                    t.*,
                    responsable.correo AS responsable_correo,
                    responsable_info.nombre AS responsable_nombre,
                    responsable_info.apellido AS responsable_apellido,
                    responsable_info.apodo AS responsable_apodo,
                    creador.correo AS creador_correo,
                    creador_info.nombre AS creador_nombre,
                    creador_info.apellido AS creador_apellido
                FROM admin_tareas t
                INNER JOIN user_auth responsable ON responsable.id = t.responsable_user_id
                LEFT JOIN user_info responsable_info ON responsable_info.user_auth_id = responsable.id
                INNER JOIN user_auth creador ON creador.id = t.created_by_user_id
                LEFT JOIN user_info creador_info ON creador_info.user_auth_id = creador.id
                ORDER BY
                    t.prioridad_defcon ASC,
                    t.fecha_entrega ASC,
                    t.created_at DESC";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }
}
