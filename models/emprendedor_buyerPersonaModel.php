<?php

require_once __DIR__ . '/../config.php';

class EmprendedorBuyerPersonaModel
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
                 FROM emprendedor_buyer_persona
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
        $sql = "INSERT INTO emprendedor_buyer_persona
                    (user_auth_id, cliente_ideal, edad_etapa_vida, ocupacion_realidad_diaria,
                     problema_necesidad, preocupacion_frustracion, objetivo_mejora, motivacion_busqueda,
                     freno_dudas, criterio_eleccion, busqueda_informacion, decision_compra,
                     motivo_eleccion, buyer_persona_estructura, completado)
                VALUES
                    (:uid, :cliente_ideal, :edad_etapa_vida, :ocupacion_realidad_diaria,
                     :problema_necesidad, :preocupacion_frustracion, :objetivo_mejora, :motivacion_busqueda,
                     :freno_dudas, :criterio_eleccion, :busqueda_informacion, :decision_compra,
                     :motivo_eleccion, :buyer_persona_estructura, :completado)
                ON DUPLICATE KEY UPDATE
                    cliente_ideal = VALUES(cliente_ideal),
                    edad_etapa_vida = VALUES(edad_etapa_vida),
                    ocupacion_realidad_diaria = VALUES(ocupacion_realidad_diaria),
                    problema_necesidad = VALUES(problema_necesidad),
                    preocupacion_frustracion = VALUES(preocupacion_frustracion),
                    objetivo_mejora = VALUES(objetivo_mejora),
                    motivacion_busqueda = VALUES(motivacion_busqueda),
                    freno_dudas = VALUES(freno_dudas),
                    criterio_eleccion = VALUES(criterio_eleccion),
                    busqueda_informacion = VALUES(busqueda_informacion),
                    decision_compra = VALUES(decision_compra),
                    motivo_eleccion = VALUES(motivo_eleccion),
                    buyer_persona_estructura = VALUES(buyer_persona_estructura),
                    completado = VALUES(completado),
                    updated_at = CURRENT_TIMESTAMP()";

        try {
            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                'uid' => $userId,
                'cliente_ideal' => $data['cliente_ideal'],
                'edad_etapa_vida' => $data['edad_etapa_vida'],
                'ocupacion_realidad_diaria' => $data['ocupacion_realidad_diaria'],
                'problema_necesidad' => $data['problema_necesidad'],
                'preocupacion_frustracion' => $data['preocupacion_frustracion'],
                'objetivo_mejora' => $data['objetivo_mejora'],
                'motivacion_busqueda' => $data['motivacion_busqueda'],
                'freno_dudas' => $data['freno_dudas'],
                'criterio_eleccion' => $data['criterio_eleccion'],
                'busqueda_informacion' => $data['busqueda_informacion'],
                'decision_compra' => $data['decision_compra'],
                'motivo_eleccion' => $data['motivo_eleccion'],
                'buyer_persona_estructura' => $data['buyer_persona_estructura'],
                'completado' => $data['completado'],
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
