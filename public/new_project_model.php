<?php

declare(strict_types=1);

class NewProjectModel
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
        $sql = "INSERT INTO project_scope_request (
                    nombre,
                    nombre_proyecto,
                    correo,
                    whatsapp,
                    q1_descripcion,
                    q2_problema,
                    q3_usuarios,
                    q4_resultado_ideal,
                    q5_tipo_aplicacion,
                    q6_login,
                    q7_acceso,
                    q8_funciones_minimas,
                    q9_funcionalidades,
                    q10_admin_vs_usuario,
                    q11_integraciones,
                    q12_contenido,
                    q13_referencias,
                    q14_diseno,
                    q15_urgencia,
                    q16_presupuesto,
                    q17_modalidad,
                    q18_adicional,
                    form_source,
                    ip_address,
                    user_agent
                ) VALUES (
                    :nombre,
                    :nombre_proyecto,
                    :correo,
                    :whatsapp,
                    :q1_descripcion,
                    :q2_problema,
                    :q3_usuarios,
                    :q4_resultado_ideal,
                    :q5_tipo_aplicacion,
                    :q6_login,
                    :q7_acceso,
                    :q8_funciones_minimas,
                    :q9_funcionalidades,
                    :q10_admin_vs_usuario,
                    :q11_integraciones,
                    :q12_contenido,
                    :q13_referencias,
                    :q14_diseno,
                    :q15_urgencia,
                    :q16_presupuesto,
                    :q17_modalidad,
                    :q18_adicional,
                    :form_source,
                    :ip_address,
                    :user_agent
                )";

        $stmt = $this->db->prepare($sql);

        $q9Json = null;
        if (!empty($data['q9_funcionalidades']) && is_array($data['q9_funcionalidades'])) {
            $q9Json = json_encode(array_values($data['q9_funcionalidades']), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $stmt->execute([
            'nombre' => $data['nombre'],
            'nombre_proyecto' => $data['nombre_proyecto'],
            'correo' => $data['correo'],
            'whatsapp' => $data['whatsapp'],
            'q1_descripcion' => $data['q1_descripcion'],
            'q2_problema' => $data['q2_problema'],
            'q3_usuarios' => $data['q3_usuarios'],
            'q4_resultado_ideal' => $data['q4_resultado_ideal'],
            'q5_tipo_aplicacion' => $data['q5_tipo_aplicacion'],
            'q6_login' => $data['q6_login'],
            'q7_acceso' => $data['q7_acceso'],
            'q8_funciones_minimas' => $data['q8_funciones_minimas'],
            'q9_funcionalidades' => $q9Json,
            'q10_admin_vs_usuario' => ($data['q10_admin_vs_usuario'] ?? '') !== '' ? $data['q10_admin_vs_usuario'] : null,
            'q11_integraciones' => ($data['q11_integraciones'] ?? '') !== '' ? $data['q11_integraciones'] : null,
            'q12_contenido' => $data['q12_contenido'],
            'q13_referencias' => ($data['q13_referencias'] ?? '') !== '' ? $data['q13_referencias'] : null,
            'q14_diseno' => $data['q14_diseno'],
            'q15_urgencia' => $data['q15_urgencia'],
            'q16_presupuesto' => $data['q16_presupuesto'],
            'q17_modalidad' => $data['q17_modalidad'],
            'q18_adicional' => ($data['q18_adicional'] ?? '') !== '' ? $data['q18_adicional'] : null,
            'form_source' => $data['form_source'] ?? 'public-new-project',
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
    }
}
