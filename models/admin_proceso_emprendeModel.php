<?php

require_once __DIR__ . '/../config.php';

class AdminProcesoEmprendeModel
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
     * @return array<int, array<string, mixed>>
     */
    public function obtenerEstadoEmprendedores(): array
    {
        $stmt = $this->db->query(
            "SELECT
                ua.id,
                ua.correo,
                ua.email_verified_at,
                ua.created_at,
                ui.nombre,
                ui.apellido,
                ui.apodo,
                ui.avatar_path,
                ui.fecha_nacimiento,
                uc.whatsapp,
                COALESCE(em.completado, 0) AS mision_completada,
                COALESCE(em.a_quien_ayudo, '') AS mision_a_quien_ayudo,
                COALESCE(em.que_problema_resuelvo, '') AS mision_que_problema_resuelvo,
                COALESCE(em.como_lo_resuelvo, '') AS mision_como_lo_resuelvo,
                COALESCE(em.mision_estructura, '') AS mision_estructura,
                COALESCE(ev.completado, 0) AS vision_completada,
                COALESCE(ev.conversion_futura, '') AS vision_conversion_futura,
                COALESCE(ev.lugar_mercado, '') AS vision_lugar_mercado,
                COALESCE(ev.impacto_generado, '') AS vision_impacto_generado,
                COALESCE(ev.vision_estructura, '') AS vision_estructura,
                COALESCE(ebp.completado, 0) AS buyer_persona_completado,
                COALESCE(ebp.cliente_ideal, '') AS buyer_cliente_ideal,
                COALESCE(ebp.edad_etapa_vida, '') AS buyer_edad_etapa_vida,
                COALESCE(ebp.ocupacion_realidad_diaria, '') AS buyer_ocupacion_realidad_diaria,
                COALESCE(ebp.problema_necesidad, '') AS buyer_problema_necesidad,
                COALESCE(ebp.preocupacion_frustracion, '') AS buyer_preocupacion_frustracion,
                COALESCE(ebp.objetivo_mejora, '') AS buyer_objetivo_mejora,
                COALESCE(ebp.motivacion_busqueda, '') AS buyer_motivacion_busqueda,
                COALESCE(ebp.freno_dudas, '') AS buyer_freno_dudas,
                COALESCE(ebp.criterio_eleccion, '') AS buyer_criterio_eleccion,
                COALESCE(ebp.busqueda_informacion, '') AS buyer_busqueda_informacion,
                COALESCE(ebp.decision_compra, '') AS buyer_decision_compra,
                COALESCE(ebp.motivo_eleccion, '') AS buyer_motivo_eleccion,
                COALESCE(ebp.buyer_persona_estructura, '') AS buyer_persona_estructura,
                COALESCE(lpr.completado, 0) AS landing_completada,
                COALESCE(lpr.nombre_emprendimiento, '') AS landing_nombre_emprendimiento,
                lpr.fecha_inicio AS landing_fecha_inicio,
                COALESCE(lpr.descripcion, '') AS landing_descripcion,
                COALESCE(lpr.dominio_registrado, 0) AS landing_dominio_registrado,
                COALESCE(lpr.hosting_propio, 0) AS landing_hosting_propio,
                COALESCE(lpr.cantidad_colaboradores, 0) AS landing_cantidad_colaboradores,
                COALESCE(lpr.nombre_fundador, '') AS landing_nombre_fundador,
                COALESCE(lpr.vende_productos, 0) AS landing_vende_productos,
                COALESCE(lpr.vende_servicios, 0) AS landing_vende_servicios,
                COALESCE(lpr.ya_factura, 0) AS landing_ya_factura,
                COALESCE(lpr.espacio_fisico, 0) AS landing_espacio_fisico,
                COALESCE(rc.nombre, '') AS landing_categoria,
                COALESCE(rs.nombre, '') AS landing_subcategoria,
                COALESCE(lpr.telefono_contacto, '') AS landing_telefono_contacto,
                COALESCE(lpr.localidad, '') AS landing_localidad,
                COALESCE(lpr.provincia, '') AS landing_provincia,
                COALESCE(lpr.pais, '') AS landing_pais,
                COALESCE(lpr.calle, '') AS landing_calle,
                COALESCE(lpr.numero, '') AS landing_numero
             FROM user_auth ua
             LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
             LEFT JOIN user_contacto uc ON uc.user_auth_id = ua.id
             LEFT JOIN emprendedor_mision em ON em.user_auth_id = ua.id
             LEFT JOIN emprendedor_vision ev ON ev.user_auth_id = ua.id
             LEFT JOIN emprendedor_buyer_persona ebp ON ebp.user_auth_id = ua.id
             LEFT JOIN landing_page_request lpr ON lpr.user_auth_id = ua.id
             LEFT JOIN rubro_emprendedor_categoria rc ON rc.id = lpr.rubro_categoria_id
             LEFT JOIN rubro_emprendedor_subcategoria rs ON rs.id = lpr.rubro_subcategoria_id
             WHERE ua.rol = 'impulsa_emprendedor'
             ORDER BY ua.created_at DESC"
        );

        $usuarios = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        foreach ($usuarios as &$usuario) {
            $misionOk = !empty($usuario['mision_completada']);
            $visionOk = !empty($usuario['vision_completada']);
            $buyerOk = !empty($usuario['buyer_persona_completado']);
            $landingOk = !empty($usuario['landing_completada']);

            if (!$misionOk) {
                $usuario['paso_actual'] = 1;
                $usuario['estado_etapa'] = 'Paso 1: Mision';
            } elseif (!$visionOk) {
                $usuario['paso_actual'] = 2;
                $usuario['estado_etapa'] = 'Paso 2: Vision';
            } elseif (!$buyerOk) {
                $usuario['paso_actual'] = 3;
                $usuario['estado_etapa'] = 'Paso 3: Buyer persona';
            } else {
                $usuario['paso_actual'] = 4;
                $usuario['estado_etapa'] = $landingOk
                    ? 'Landing solicitada'
                    : 'Listo para solicitar landing';
            }
        }
        unset($usuario);

        return $usuarios;
    }

    /**
     * @return array{total:int,paso_1:int,paso_2:int,paso_3:int,listos_landing:int}
     */
    public function obtenerResumenProceso(): array
    {
        $usuarios = $this->obtenerEstadoEmprendedores();
        $resumen = [
            'total' => 0,
            'paso_1' => 0,
            'paso_2' => 0,
            'paso_3' => 0,
            'listos_landing' => 0,
        ];

        foreach ($usuarios as $usuario) {
            $resumen['total']++;

            switch ((int) ($usuario['paso_actual'] ?? 0)) {
                case 1:
                    $resumen['paso_1']++;
                    break;
                case 2:
                    $resumen['paso_2']++;
                    break;
                case 3:
                    $resumen['paso_3']++;
                    break;
                case 4:
                    $resumen['listos_landing']++;
                    break;
            }
        }

        return $resumen;
    }
}
