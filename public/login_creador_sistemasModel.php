<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

class LoginCreadorSistemasModel
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function autenticar(string $correo, string $contrasena): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                ua.id,
                ua.correo,
                ua.password,
                ua.rol,
                ui.nombre,
                ui.apellido,
                ui.apodo,
                ui.avatar_path,
                ui.fecha_nacimiento
             FROM user_auth ua
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             WHERE ua.correo = :correo
               AND ua.rol = 'impulsa_cliente'
             LIMIT 1"
        );
        $stmt->execute(['correo' => strtolower(trim($correo))]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || !password_verify($contrasena, (string) $usuario['password'])) {
            return null;
        }

        return [
            'id' => (int) $usuario['id'],
            'correo' => (string) $usuario['correo'],
            'rol' => (string) $usuario['rol'],
            'nombre' => $usuario['nombre'] ?? null,
            'apellido' => $usuario['apellido'] ?? null,
            'apodo' => $usuario['apodo'] ?? null,
            'avatar_path' => $usuario['avatar_path'] ?? null,
            'fecha_nacimiento' => $usuario['fecha_nacimiento'] ?? null,
        ];
    }
}

function resolverRutaPorRol(string $rol): string
{
    return $rol === 'impulsa_cliente'
        ? '/views/clientes/clientes_dashboard.php'
        : '/public/login_creador_sistemas.php';
}
