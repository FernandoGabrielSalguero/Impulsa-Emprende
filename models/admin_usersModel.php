<?php

require_once __DIR__ . '/../config.php';

class AdminUsersModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * @return array<string, mixed>
     */
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
                ui.fecha_nacimiento,
                uc.check_correo,
                uc.permison_correo,
                uc.whatsapp,
                uc.check_whatsapp,
                uc.permison_whatsapp
             FROM user_auth ua
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             LEFT JOIN user_contacto uc ON uc.user_auth_id = ua.id
             WHERE ua.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array{nombre:string,correo:string}
     */
    public function normalizarFiltros(array $input): array
    {
        return [
            'nombre' => trim((string) ($input['nombre'] ?? '')),
            'correo' => trim((string) ($input['correo'] ?? '')),
        ];
    }

    /**
     * @return array{sql:string,params:array<string, mixed>}
     */
    private function construirWhereFiltros(array $filtros): array
    {
        $where = [];
        $params = [];

        if ($filtros['nombre'] !== '') {
            $where[] = "(ui.nombre LIKE :nombre OR ui.apellido LIKE :nombre OR ui.apodo LIKE :nombre OR CONCAT(COALESCE(ui.nombre, ''), ' ', COALESCE(ui.apellido, '')) LIKE :nombre)";
            $params['nombre'] = '%' . $filtros['nombre'] . '%';
        }

        if ($filtros['correo'] !== '') {
            $where[] = "ua.correo LIKE :correo";
            $params['correo'] = '%' . $filtros['correo'] . '%';
        }

        $sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        return ['sql' => $sqlWhere, 'params' => $params];
    }

    public function contarUsuarios(array $filtros = []): int
    {
        $partes = $this->construirWhereFiltros($this->normalizarFiltros($filtros));

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total
             FROM user_auth ua
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             " . $partes['sql']
        );
        $stmt->execute($partes['params']);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array<string, mixed>
     */
    public function obtenerUsuarioPorId(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                ua.id AS usuario_id,
                ua.correo,
                ua.rol,
                ua.email_verified_at,
                ua.verification_token,
                ua.created_at,
                ui.nombre,
                ui.apellido,
                ui.apodo,
                ui.avatar_path,
                uc.whatsapp
             FROM user_auth ua
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             LEFT JOIN user_contacto uc ON uc.user_auth_id = ua.id
             WHERE ua.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function actualizarTokenVerificacion(int $userId, string $token): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE user_auth
             SET verification_token = :token, email_verified_at = NULL
             WHERE id = :id"
        );

        return $stmt->execute([
            'id' => $userId,
            'token' => $token,
        ]);
    }

    public function marcarCorreoPendiente(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE user_contacto
             SET check_correo = 0
             WHERE user_auth_id = :id"
        );

        return $stmt->execute(['id' => $userId]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerUsuarios(array $filtros = []): array
    {
        $partes = $this->construirWhereFiltros($this->normalizarFiltros($filtros));

        $stmt = $this->db->prepare(
            "SELECT
                ua.id AS usuario_id,
                ua.correo,
                ua.rol,
                ua.email_verified_at,
                ua.created_at,
                ua.updated_at,
                ui.nombre,
                ui.apellido,
                ui.apodo,
                ui.avatar_path,
                ui.fecha_nacimiento,
                uc.whatsapp,
                uc.check_correo,
                uc.check_whatsapp
             FROM user_auth ua
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             LEFT JOIN user_contacto uc ON uc.user_auth_id = ua.id
             " . $partes['sql'] . "
             ORDER BY ua.created_at DESC"
        );
        $stmt->execute($partes['params']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
