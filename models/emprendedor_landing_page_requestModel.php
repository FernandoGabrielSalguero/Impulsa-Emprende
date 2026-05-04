<?php

require_once __DIR__ . '/../config.php';

class EmprendedorLandingPageRequestModel
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
        $stmt = $this->db->prepare(
            "SELECT
                lpr.*,
                ui.nombre AS perfil_nombre,
                ui.apellido AS perfil_apellido,
                uc.whatsapp AS perfil_whatsapp
             FROM landing_page_request lpr
             LEFT JOIN user_info ui ON ui.user_auth_id = lpr.user_auth_id
             LEFT JOIN user_contacto uc ON uc.user_auth_id = lpr.user_auth_id
             WHERE lpr.user_auth_id = :uid
             LIMIT 1"
        );
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row;
        }

        $stmt2 = $this->db->prepare(
            "SELECT ui.nombre, ui.apellido, uc.whatsapp
             FROM user_auth ua
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             LEFT JOIN user_contacto uc ON uc.user_auth_id = ua.id
             WHERE ua.id = :uid
             LIMIT 1"
        );
        $stmt2->execute(['uid' => $userId]);
        $perfil = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'perfil_nombre' => $perfil['nombre'] ?? '',
            'perfil_apellido' => $perfil['apellido'] ?? '',
            'perfil_whatsapp' => $perfil['whatsapp'] ?? '',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerRubros(): array
    {
        $sql = "SELECT
                    c.id AS categoria_id,
                    c.nombre AS categoria_nombre,
                    s.id AS subcategoria_id,
                    s.nombre AS subcategoria_nombre
                FROM rubro_emprendedor_categoria c
                LEFT JOIN rubro_emprendedor_relaciones r
                    ON r.categoria_id = c.id
                LEFT JOIN rubro_emprendedor_subcategoria s
                    ON s.id = r.subcategoria_id
                ORDER BY c.nombre ASC, s.nombre ASC";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $categorias = [];
        foreach ($rows as $row) {
            $categoriaId = (int) $row['categoria_id'];

            if (!isset($categorias[$categoriaId])) {
                $categorias[$categoriaId] = [
                    'id' => $categoriaId,
                    'nombre' => (string) $row['categoria_nombre'],
                    'subcategorias' => [],
                ];
            }

            if (!empty($row['subcategoria_id'])) {
                $categorias[$categoriaId]['subcategorias'][] = [
                    'id' => (int) $row['subcategoria_id'],
                    'nombre' => (string) $row['subcategoria_nombre'],
                ];
            }
        }

        return array_values($categorias);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function guardar(int $userId, array $data): bool
    {
        $sql = "INSERT INTO landing_page_request
                    (user_auth_id, nombre_emprendimiento, fecha_inicio, descripcion,
                     dominio_registrado, hosting_propio, cantidad_colaboradores,
                     nombre_fundador, vende_productos, vende_servicios, ya_factura,
                     espacio_fisico, rubro_categoria_id, rubro_subcategoria_id,
                     pais, provincia, localidad, calle, numero,
                     telefono_contacto, completado)
                VALUES
                    (:uid, :nombre_emprendimiento, :fecha_inicio, :descripcion,
                     :dominio_registrado, :hosting_propio, :cantidad_colaboradores,
                     :nombre_fundador, :vende_productos, :vende_servicios, :ya_factura,
                     :espacio_fisico, :rubro_categoria_id, :rubro_subcategoria_id,
                     :pais, :provincia, :localidad, :calle, :numero,
                     :telefono_contacto, :completado)
                ON DUPLICATE KEY UPDATE
                    nombre_emprendimiento = VALUES(nombre_emprendimiento),
                    fecha_inicio = VALUES(fecha_inicio),
                    descripcion = VALUES(descripcion),
                    dominio_registrado = VALUES(dominio_registrado),
                    hosting_propio = VALUES(hosting_propio),
                    cantidad_colaboradores = VALUES(cantidad_colaboradores),
                    nombre_fundador = VALUES(nombre_fundador),
                    vende_productos = VALUES(vende_productos),
                    vende_servicios = VALUES(vende_servicios),
                    ya_factura = VALUES(ya_factura),
                    espacio_fisico = VALUES(espacio_fisico),
                    rubro_categoria_id = VALUES(rubro_categoria_id),
                    rubro_subcategoria_id = VALUES(rubro_subcategoria_id),
                    pais = VALUES(pais),
                    provincia = VALUES(provincia),
                    localidad = VALUES(localidad),
                    calle = VALUES(calle),
                    numero = VALUES(numero),
                    telefono_contacto = VALUES(telefono_contacto),
                    completado = VALUES(completado)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'uid' => $userId,
            'nombre_emprendimiento' => $data['nombre_emprendimiento'],
            'fecha_inicio' => $data['fecha_inicio'],
            'descripcion' => $data['descripcion'],
            'dominio_registrado' => $data['dominio_registrado'],
            'hosting_propio' => $data['hosting_propio'],
            'cantidad_colaboradores' => $data['cantidad_colaboradores'],
            'nombre_fundador' => $data['nombre_fundador'],
            'vende_productos' => $data['vende_productos'],
            'vende_servicios' => $data['vende_servicios'],
            'ya_factura' => $data['ya_factura'],
            'espacio_fisico' => $data['espacio_fisico'],
            'rubro_categoria_id' => $data['rubro_categoria_id'],
            'rubro_subcategoria_id' => $data['rubro_subcategoria_id'],
            'pais' => $data['pais'],
            'provincia' => $data['provincia'],
            'localidad' => $data['localidad'],
            'calle' => $data['calle'],
            'numero' => $data['numero'],
            'telefono_contacto' => $data['telefono_contacto'],
            'completado' => $data['completado'] ?? 0,
        ]);
    }

    public function existeRelacionRubro(int $categoriaId, int $subcategoriaId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1
             FROM rubro_emprendedor_relaciones
             WHERE categoria_id = :categoria_id
               AND subcategoria_id = :subcategoria_id
             LIMIT 1"
        );

        $stmt->execute([
            'categoria_id' => $categoriaId,
            'subcategoria_id' => $subcategoriaId,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
