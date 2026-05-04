<?php

class ContactoLandingModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function guardar(array $data): bool
    {
        $sql = "INSERT INTO contacto_landing
                    (nombre, empresa, email, telefono, equipo, objetivo, mensaje, form_source, ip_address, user_agent)
                VALUES
                    (:nombre, :empresa, :email, :telefono, :equipo, :objetivo, :mensaje, :form_source, :ip_address, :user_agent)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $data['nombre'],
            'empresa' => $data['empresa'],
            'email' => $data['email'],
            'telefono' => $data['telefono'],
            'equipo' => $data['equipo'],
            'objetivo' => $data['objetivo'],
            'mensaje' => $data['mensaje'],
            'form_source' => $data['form_source'],
            'ip_address' => $data['ip_address'],
            'user_agent' => $data['user_agent'],
        ]);
    }
}
