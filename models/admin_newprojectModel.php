<?php

declare(strict_types=1);

class AdminNewProjectModel
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
    public function obtenerSolicitudes(): array
    {
        $sql = "SELECT *
                FROM project_scope_request
                ORDER BY id DESC";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerSolicitudesLandingExternal(): array
    {
        $sql = "SELECT
                    lpre.*,
                    'nuevo' AS estado,
                    NULL AS updated_at
                FROM landing_page_requests_external lpre
                ORDER BY lpre.id DESC";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return is_array($rows) ? $rows : [];
    }
}
