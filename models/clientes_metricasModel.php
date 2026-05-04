<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

class ClientesMetricasModel
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

    public function obtenerParametroPagina(int $userId): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT page
             FROM user_params
             WHERE user_auth_id = :user_id
             LIMIT 1"
        );
        $stmt->execute(['user_id' => $userId]);
        $page = $stmt->fetchColumn();

        $page = is_string($page) ? trim($page) : '';
        return $page !== '' ? $page : null;
    }

    /**
     * @return array<int, array{label:string, value:int, start:string, end:string}>
     */
    public function obtenerKpisVisitasMensuales(string $page): array
    {
        $months = [];
        $current = new DateTimeImmutable('first day of this month 00:00:00');

        for ($i = 0; $i < 3; $i++) {
            $monthStart = $current->modify("-{$i} months");
            $monthEnd = $monthStart->modify('+1 month');
            $months[] = [
                'label' => $monthStart->format('m/Y'),
                'value' => 0,
                'start' => $monthStart->format('Y-m-d H:i:s'),
                'end' => $monthEnd->format('Y-m-d H:i:s'),
            ];
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total
             FROM visit_user_page
             WHERE page = :page
               AND visited_at >= :start
               AND visited_at < :end"
        );

        foreach ($months as &$month) {
            $stmt->execute([
                'page' => $page,
                'start' => $month['start'],
                'end' => $month['end'],
            ]);
            $month['value'] = (int) ($stmt->fetchColumn() ?: 0);
        }
        unset($month);

        return $months;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerContactos(string $page, int $limit = 200): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                id,
                page,
                contact_nombre,
                contact_whatsapp,
                contact_email,
                contact_description,
                contact_consultation,
                state,
                created_at,
                updated_at
             FROM forms_clients_contact
             WHERE page = :page
             ORDER BY created_at DESC, id DESC
             LIMIT {$limit}"
        );
        $stmt->execute(['page' => $page]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function actualizarEstadoContacto(string $page, int $contactId, string $state): bool
    {
        $allowedStates = ['recibido', 'cancelado', 'aprobado'];
        if ($contactId <= 0 || !in_array($state, $allowedStates, true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE forms_clients_contact
             SET state = :state
             WHERE id = :id
               AND page = :page"
        );
        $stmt->execute([
            'state' => $state,
            'id' => $contactId,
            'page' => $page,
        ]);

        return $stmt->rowCount() > 0;
    }
}
