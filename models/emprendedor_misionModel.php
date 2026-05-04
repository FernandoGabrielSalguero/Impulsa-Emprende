<?php

require_once __DIR__ . '/../config.php';

class EmprendedorMisionModel
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
                 FROM emprendedor_mision
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
        $sql = "INSERT INTO emprendedor_mision
                    (user_auth_id, a_quien_ayudo, que_problema_resuelvo, como_lo_resuelvo, mision_estructura, completado)
                VALUES
                    (:uid, :a_quien_ayudo, :que_problema_resuelvo, :como_lo_resuelvo, :mision_estructura, :completado)
                ON DUPLICATE KEY UPDATE
                    a_quien_ayudo = VALUES(a_quien_ayudo),
                    que_problema_resuelvo = VALUES(que_problema_resuelvo),
                    como_lo_resuelvo = VALUES(como_lo_resuelvo),
                    mision_estructura = VALUES(mision_estructura),
                    completado = VALUES(completado),
                    updated_at = CURRENT_TIMESTAMP()";

        try {
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                'uid' => $userId,
                'a_quien_ayudo' => $data['a_quien_ayudo'],
                'que_problema_resuelvo' => $data['que_problema_resuelvo'],
                'como_lo_resuelvo' => $data['como_lo_resuelvo'],
                'mision_estructura' => $data['mision_estructura'],
                'completado' => $data['completado'],
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
