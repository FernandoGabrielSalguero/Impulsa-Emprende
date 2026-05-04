<?php

require_once __DIR__ . '/../config.php';

class EmprendedorVisionModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * @return array<string, mixed>
     */
    public function obtener(int $userId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT *
                 FROM emprendedor_vision
                 WHERE user_auth_id = :uid
                 LIMIT 1"
            );
            $stmt->execute(['uid' => $userId]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function guardar(int $userId, array $data): bool
    {
        $sql = "INSERT INTO emprendedor_vision
                    (user_auth_id, conversion_futura, lugar_mercado, impacto_generado, vision_estructura, completado)
                VALUES
                    (:uid, :conversion_futura, :lugar_mercado, :impacto_generado, :vision_estructura, :completado)
                ON DUPLICATE KEY UPDATE
                    conversion_futura = VALUES(conversion_futura),
                    lugar_mercado = VALUES(lugar_mercado),
                    impacto_generado = VALUES(impacto_generado),
                    vision_estructura = VALUES(vision_estructura),
                    completado = VALUES(completado),
                    updated_at = CURRENT_TIMESTAMP()";

        try {
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                'uid' => $userId,
                'conversion_futura' => $data['conversion_futura'],
                'lugar_mercado' => $data['lugar_mercado'],
                'impacto_generado' => $data['impacto_generado'],
                'vision_estructura' => $data['vision_estructura'],
                'completado' => $data['completado'],
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
