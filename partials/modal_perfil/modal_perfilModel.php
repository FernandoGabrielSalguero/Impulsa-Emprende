<?php

require_once __DIR__ . '/../../config.php';

class PerfilModel
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
            "SELECT ui.avatar_path
             FROM user_info ui
             WHERE ui.user_auth_id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Actualiza los datos personales del usuario en user_info.
     */
    public function actualizarInfo(int $userId, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $s1 = $this->db->prepare(
                "UPDATE user_info
                 SET nombre           = :nombre,
                     apellido         = :apellido,
                     apodo            = :apodo,
                     avatar_path      = :avatar_path,
                     fecha_nacimiento = :fecha_nacimiento
                 WHERE user_auth_id = :id"
            );
            $s1->execute([
                'nombre'           => $data['nombre']           ?: null,
                'apellido'         => $data['apellido']         ?: null,
                'apodo'            => $data['apodo']            ?: null,
                'avatar_path'      => $data['avatar_path']      ?: null,
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?: null,
                'id'               => $userId,
            ]);

            $s2 = $this->db->prepare(
                "UPDATE user_contacto
                 SET whatsapp          = :whatsapp,
                     permison_correo   = :permison_correo,
                     permison_whatsapp = :permison_whatsapp
                 WHERE user_auth_id = :id"
            );
            $s2->execute([
                'whatsapp'          => $data['whatsapp']          ?: null,
                'permison_correo'   => $data['permison_correo'],
                'permison_whatsapp' => $data['permison_whatsapp'],
                'id'                => $userId,
            ]);

            $this->db->commit();
            return true;
        } catch (Throwable) {
            $this->db->rollBack();
            return false;
        }
    }
}
