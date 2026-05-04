<?php

declare(strict_types=1);

class NewPageModel
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
        $sql = "INSERT INTO landing_page_requests_external (
                    nombre,
                    nombre_proyecto,
                    correo,
                    whatsapp,
                    q1_nombre_comercial,
                    q2_actividad,
                    q3_objetivo,
                    q4_publico,
                    q5_accion_principal,
                    q6_propuestas_destacar,
                    q7_diferencial,
                    q8_secciones,
                    q9_textos,
                    q10_contacto,
                    q11_material_marca,
                    q12_estilo_visual,
                    q13_referencias,
                    q14_recursos_visuales,
                    q15_imagenes_apoyo,
                    q16_dominio_hosting,
                    q17_correos_corporativos,
                    q18_requerimientos_adicionales,
                    form_source,
                    ip_address,
                    user_agent
                ) VALUES (
                    :nombre,
                    :nombre_proyecto,
                    :correo,
                    :whatsapp,
                    :q1_nombre_comercial,
                    :q2_actividad,
                    :q3_objetivo,
                    :q4_publico,
                    :q5_accion_principal,
                    :q6_propuestas_destacar,
                    :q7_diferencial,
                    :q8_secciones,
                    :q9_textos,
                    :q10_contacto,
                    :q11_material_marca,
                    :q12_estilo_visual,
                    :q13_referencias,
                    :q14_recursos_visuales,
                    :q15_imagenes_apoyo,
                    :q16_dominio_hosting,
                    :q17_correos_corporativos,
                    :q18_requerimientos_adicionales,
                    :form_source,
                    :ip_address,
                    :user_agent
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $data['nombre'],
            'nombre_proyecto' => $data['nombre_proyecto'],
            'correo' => $data['correo'],
            'whatsapp' => $data['whatsapp'],
            'q1_nombre_comercial' => $data['q1_nombre_comercial'],
            'q2_actividad' => $data['q2_actividad'],
            'q3_objetivo' => $data['q3_objetivo'],
            'q4_publico' => $data['q4_publico'],
            'q5_accion_principal' => $data['q5_accion_principal'],
            'q6_propuestas_destacar' => $data['q6_propuestas_destacar'],
            'q7_diferencial' => $data['q7_diferencial'],
            'q8_secciones' => $data['q8_secciones'],
            'q9_textos' => $data['q9_textos'],
            'q10_contacto' => $data['q10_contacto'],
            'q11_material_marca' => $data['q11_material_marca'],
            'q12_estilo_visual' => $data['q12_estilo_visual'],
            'q13_referencias' => $data['q13_referencias'],
            'q14_recursos_visuales' => $data['q14_recursos_visuales'],
            'q15_imagenes_apoyo' => $data['q15_imagenes_apoyo'],
            'q16_dominio_hosting' => $data['q16_dominio_hosting'],
            'q17_correos_corporativos' => $data['q17_correos_corporativos'],
            'q18_requerimientos_adicionales' => $data['q18_requerimientos_adicionales'],
            'form_source' => $data['form_source'] ?? 'public-new-page',
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);
    }
}
