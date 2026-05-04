<?php
require_once __DIR__ . '/../../controllers/admin_newprojectController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Admin';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');

$preguntasFormulario = [
    'q1_descripcion' => '1. Contanos brevemente que queres desarrollar',
    'q2_problema' => '2. Que problema queres resolver con esta aplicacion',
    'q3_usuarios' => '3. Quienes van a usar la aplicacion',
    'q4_resultado_ideal' => '4. Cual seria el resultado ideal para vos dentro de 6 meses',
    'q5_tipo_aplicacion' => '5. Que queres desarrollar',
    'q6_login' => '6. La aplicacion necesita registro e inicio de sesion',
    'q7_acceso' => '7. Quienes tendrian acceso',
    'q8_funciones_minimas' => '8. Que cosas si o si tiene que permitir la primera version',
    'q9_funcionalidades' => '9. Marca las funciones que crees que vas a necesitar',
    'q10_admin_vs_usuario' => '10. Hay algo que queres que puedan hacer los administradores y no los usuarios comunes',
    'q11_integraciones' => '11. La app necesita conectarse con alguna herramienta que ya usas',
    'q12_contenido' => '12. Ya tenes definido el contenido, informacion o procesos de la app',
    'q13_referencias' => '13. Tenes referencias de apps o sistemas parecidos',
    'q14_diseno' => '14. Ya contas con logo, identidad visual o diseno',
    'q15_urgencia' => '15. Que tan urgente es este proyecto',
    'q16_presupuesto' => '16. Tenes una idea de presupuesto para este proyecto',
    'q17_modalidad' => '17. Preferis avanzar por etapas o resolver todo en una sola propuesta',
    'q18_adicional' => '18. Hay algo importante que no te preguntamos y deberiamos saber antes de armar una propuesta',
];

$preguntasLandingExternal = [
    'q1_nombre_comercial' => '1. Cual es el nombre comercial de tu empresa o proyecto',
    'q2_actividad' => '2. A que se dedica tu empresa, marca o emprendimiento',
    'q3_objetivo' => '3. Cual es el objetivo principal de la landing page',
    'q4_publico' => '4. A que publico esta dirigida la pagina',
    'q5_accion_principal' => '5. Que accion principal queres que realice el visitante al entrar al sitio',
    'q6_propuestas_destacar' => '6. Que servicios, productos o propuestas queres destacar si o si en la pagina',
    'q7_diferencial' => '7. Que te diferencia de tu competencia',
    'q8_secciones' => '8. Que secciones queres incluir en la landing page',
    'q9_textos' => '9. Ya contas con los textos para la pagina o necesitas ayuda para redactarlos',
    'q10_contacto' => '10. Que informacion de contacto queres mostrar en la web',
    'q11_material_marca' => '11. Tenes material de marca disponible',
    'q12_estilo_visual' => '12. Que estilo visual queres para la landing page',
    'q13_referencias' => '13. Tenes ejemplos de paginas web que te gusten',
    'q14_recursos_visuales' => '14. Contas con imagenes, videos o recursos visuales propios para usar en la web',
    'q15_imagenes_apoyo' => '15. Si no tenes material visual suficiente, queres que trabajemos con imagenes de apoyo o de referencia de forma temporal',
    'q16_dominio_hosting' => '16. Ya tenes dominio y hosting contratados',
    'q17_correos_corporativos' => '17. Queres que la pagina tenga correos corporativos con tu dominio',
    'q18_requerimientos_adicionales' => '18. Hay algun requerimiento adicional que debamos tener en cuenta antes de comenzar',
];

$respuestaOpciones = [
    'q5_tipo_aplicacion' => [
        'pagina_web' => 'Pagina web',
        'sistema_web_interno' => 'Sistema web interno',
        'app_mobile' => 'App mobile',
        'plataforma_web_app_mobile' => 'Plataforma web + app mobile',
        'no_se' => 'No estoy seguro/a',
    ],
    'q6_login' => [
        'si' => 'Si',
        'no' => 'No',
        'no_se' => 'No lo se',
    ],
    'q7_acceso' => [
        'solo_equipo' => 'Solo mi equipo',
        'clientes' => 'Clientes',
        'proveedores' => 'Proveedores',
        'roles_diferentes' => 'Diferentes tipos de usuarios',
        'no_se' => 'No lo se todavia',
    ],
    'q9_funcionalidades' => [
        'registro_login' => 'Registro e inicio de sesion',
        'perfil_usuario' => 'Perfil de usuario',
        'panel_admin' => 'Panel de administracion',
        'carga_edicion_datos' => 'Carga y edicion de datos',
        'busqueda_filtros' => 'Busqueda y filtros',
        'agenda_turnos' => 'Agenda o turnos',
        'pagos_online' => 'Pagos online',
        'notificaciones_email_whatsapp' => 'Notificaciones por mail o WhatsApp',
        'reportes_metricas' => 'Reportes o metricas',
        'subida_archivos_imagenes' => 'Subida de archivos o imagenes',
        'geolocalizacion_mapas' => 'Geolocalizacion o mapas',
        'integracion_sistemas' => 'Integracion con otros sistemas',
        'chat_mensajeria' => 'Chat o mensajeria',
        'no_se' => 'No estoy seguro/a',
    ],
    'q12_contenido' => [
        'claro' => 'Si, bastante claro',
        'medio' => 'Mas o menos',
        'no' => 'Todavia no',
    ],
    'q14_diseno' => [
        'completa' => 'Si, ya tengo todo',
        'parcial' => 'Tengo algo, pero falta',
        'no' => 'No, lo necesito tambien',
    ],
    'q15_urgencia' => [
        'cuanto_antes' => 'Lo quiero empezar cuanto antes',
        '1_2_meses' => 'En 1 a 2 meses',
        'mas_adelante' => 'Mas adelante',
        'explorando' => 'Solo estoy explorando opciones',
    ],
    'q16_presupuesto' => [
        'sin_definir' => 'Todavia no',
        'menos_1000000' => 'Menos de $1.000.000',
        'entre_1000000_2000000' => 'Entre $1.000.000 y $2.000.000',
        'mas_2000000' => 'Mas de $2.000.000',
    ],
    'q17_modalidad' => [
        'por_etapas' => 'Por etapas',
        'todo_junto' => 'Todo junto',
        'necesito_recomendacion' => 'Necesito recomendacion',
    ],
];

$mapearRespuesta = static function (string $campo, $valor) use ($respuestaOpciones): string {
    if (is_array($valor)) {
        $labels = array_map(
            static fn($item): string => $respuestaOpciones[$campo][(string)$item] ?? (string)$item,
            $valor
        );

        return empty($labels) ? '-' : implode(', ', $labels);
    }

    $valor = (string)$valor;
    if ($valor === '') {
        return '-';
    }

    return $respuestaOpciones[$campo][$valor] ?? $valor;
};

$detalleSolicitudes = [];
foreach ($solicitudes as $solicitud) {
    $q9Listado = '-';
    $q9 = json_decode((string)($solicitud['q9_funcionalidades'] ?? ''), true);
    if (is_array($q9) && !empty($q9)) {
        $q9Listado = $mapearRespuesta('q9_funcionalidades', $q9);
    }

    $createdAtLocal = !empty($solicitud['created_at'])
        ? date('d/m/Y H:i', strtotime((string)$solicitud['created_at'] . ' -3 hours'))
        : '-';
    $updatedAtLocal = !empty($solicitud['updated_at'])
        ? date('d/m/Y H:i', strtotime((string)$solicitud['updated_at'] . ' -3 hours'))
        : '-';

    $detalleSolicitudes[(int)($solicitud['id'] ?? 0)] = [
        'ID' => (string)($solicitud['id'] ?? '-'),
        'Nombre' => (string)($solicitud['nombre'] ?? '-'),
        'Nombre del proyecto' => (string)($solicitud['nombre_proyecto'] ?? '-'),
        'Correo' => (string)($solicitud['correo'] ?? '-'),
        'Whatsapp' => (string)($solicitud['whatsapp'] ?? '-'),
        'Estado' => (string)($solicitud['estado'] ?? '-'),
        $preguntasFormulario['q1_descripcion'] => (string)($solicitud['q1_descripcion'] ?? '-'),
        $preguntasFormulario['q2_problema'] => (string)($solicitud['q2_problema'] ?? '-'),
        $preguntasFormulario['q3_usuarios'] => (string)($solicitud['q3_usuarios'] ?? '-'),
        $preguntasFormulario['q4_resultado_ideal'] => (string)($solicitud['q4_resultado_ideal'] ?? '-'),
        $preguntasFormulario['q5_tipo_aplicacion'] => $mapearRespuesta('q5_tipo_aplicacion', $solicitud['q5_tipo_aplicacion'] ?? ''),
        $preguntasFormulario['q6_login'] => $mapearRespuesta('q6_login', $solicitud['q6_login'] ?? ''),
        $preguntasFormulario['q7_acceso'] => $mapearRespuesta('q7_acceso', $solicitud['q7_acceso'] ?? ''),
        $preguntasFormulario['q8_funciones_minimas'] => (string)($solicitud['q8_funciones_minimas'] ?? '-'),
        $preguntasFormulario['q9_funcionalidades'] => $q9Listado,
        $preguntasFormulario['q10_admin_vs_usuario'] => (string)($solicitud['q10_admin_vs_usuario'] ?? '-'),
        $preguntasFormulario['q11_integraciones'] => (string)($solicitud['q11_integraciones'] ?? '-'),
        $preguntasFormulario['q12_contenido'] => $mapearRespuesta('q12_contenido', $solicitud['q12_contenido'] ?? ''),
        $preguntasFormulario['q13_referencias'] => (string)($solicitud['q13_referencias'] ?? '-'),
        $preguntasFormulario['q14_diseno'] => $mapearRespuesta('q14_diseno', $solicitud['q14_diseno'] ?? ''),
        $preguntasFormulario['q15_urgencia'] => $mapearRespuesta('q15_urgencia', $solicitud['q15_urgencia'] ?? ''),
        $preguntasFormulario['q16_presupuesto'] => $mapearRespuesta('q16_presupuesto', $solicitud['q16_presupuesto'] ?? ''),
        $preguntasFormulario['q17_modalidad'] => $mapearRespuesta('q17_modalidad', $solicitud['q17_modalidad'] ?? ''),
        $preguntasFormulario['q18_adicional'] => (string)($solicitud['q18_adicional'] ?? '-'),
        'Form source' => (string)($solicitud['form_source'] ?? '-'),
        'IP address' => (string)($solicitud['ip_address'] ?? '-'),
        'User agent' => (string)($solicitud['user_agent'] ?? '-'),
        'Creado (local -3h)' => $createdAtLocal,
        'Actualizado (local -3h)' => $updatedAtLocal,
    ];
}

$detalleSolicitudesLandingExternal = [];
foreach ($solicitudesLandingExternal as $solicitud) {
    $createdAtLocal = !empty($solicitud['created_at'])
        ? date('d/m/Y H:i', strtotime((string)$solicitud['created_at'] . ' -3 hours'))
        : '-';

    $detalleSolicitudesLandingExternal[(int)($solicitud['id'] ?? 0)] = [
        'ID' => (string)($solicitud['id'] ?? '-'),
        'Nombre' => (string)($solicitud['nombre'] ?? '-'),
        'Nombre del proyecto' => (string)($solicitud['nombre_proyecto'] ?? '-'),
        'Correo' => (string)($solicitud['correo'] ?? '-'),
        'Whatsapp' => (string)($solicitud['whatsapp'] ?? '-'),
        'Estado' => (string)($solicitud['estado'] ?? 'nuevo'),
        $preguntasLandingExternal['q1_nombre_comercial'] => (string)($solicitud['q1_nombre_comercial'] ?? '-'),
        $preguntasLandingExternal['q2_actividad'] => (string)($solicitud['q2_actividad'] ?? '-'),
        $preguntasLandingExternal['q3_objetivo'] => (string)($solicitud['q3_objetivo'] ?? '-'),
        $preguntasLandingExternal['q4_publico'] => (string)($solicitud['q4_publico'] ?? '-'),
        $preguntasLandingExternal['q5_accion_principal'] => (string)($solicitud['q5_accion_principal'] ?? '-'),
        $preguntasLandingExternal['q6_propuestas_destacar'] => (string)($solicitud['q6_propuestas_destacar'] ?? '-'),
        $preguntasLandingExternal['q7_diferencial'] => (string)($solicitud['q7_diferencial'] ?? '-'),
        $preguntasLandingExternal['q8_secciones'] => (string)($solicitud['q8_secciones'] ?? '-'),
        $preguntasLandingExternal['q9_textos'] => (string)($solicitud['q9_textos'] ?? '-'),
        $preguntasLandingExternal['q10_contacto'] => (string)($solicitud['q10_contacto'] ?? '-'),
        $preguntasLandingExternal['q11_material_marca'] => (string)($solicitud['q11_material_marca'] ?? '-'),
        $preguntasLandingExternal['q12_estilo_visual'] => (string)($solicitud['q12_estilo_visual'] ?? '-'),
        $preguntasLandingExternal['q13_referencias'] => (string)($solicitud['q13_referencias'] ?? '-'),
        $preguntasLandingExternal['q14_recursos_visuales'] => (string)($solicitud['q14_recursos_visuales'] ?? '-'),
        $preguntasLandingExternal['q15_imagenes_apoyo'] => (string)($solicitud['q15_imagenes_apoyo'] ?? '-'),
        $preguntasLandingExternal['q16_dominio_hosting'] => (string)($solicitud['q16_dominio_hosting'] ?? '-'),
        $preguntasLandingExternal['q17_correos_corporativos'] => (string)($solicitud['q17_correos_corporativos'] ?? '-'),
        $preguntasLandingExternal['q18_requerimientos_adicionales'] => (string)($solicitud['q18_requerimientos_adicionales'] ?? '-'),
        'Form source' => (string)($solicitud['form_source'] ?? '-'),
        'IP address' => (string)($solicitud['ip_address'] ?? '-'),
        'User agent' => (string)($solicitud['user_agent'] ?? '-'),
        'Creado (local -3h)' => $createdAtLocal,
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impulsa - Solicitudes de Proyecto</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/framework/framework.css">
    <script src="../../assets/framework/framework.js" defer></script>

    <style>
        .navbar { justify-content: space-between; }
        .navbar-left { display: flex; align-items: center; gap: 8px; }

        .sidebar-brand-icon {
            width: 32px;
            height: 32px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .hero-card h1 {
            margin: 0;
            font-size: 28px;
        }

        .hero-card p {
            margin: 8px 0 0;
            color: #6b7280;
            line-height: 1.6;
        }

        .section-title {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .section-title h2 {
            margin: 0;
            font-size: 22px;
            color: #0f172a;
        }

        .section-title p {
            margin: 4px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        .requests-table {
            width: 100%;
            border-collapse: collapse;
        }

        .requests-table thead th {
            text-align: left;
            font-size: 12px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .requests-table tbody td {
            padding: 12px;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .requests-table tbody tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-nuevo { background: #dbeafe; color: #1d4ed8; }
        .badge-revisado { background: #dcfce7; color: #15803d; }
        .badge-descartado { background: #fee2e2; color: #b91c1c; }

        .muted {
            color: #6b7280;
            font-size: 13px;
        }

        .action-icon-btn {
            width: 34px;
            height: 34px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .action-icon-btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .detail-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            z-index: 1200;
        }

        .detail-modal-backdrop.is-open {
            display: flex;
        }

        .detail-modal {
            width: min(100%, 960px);
            max-height: 90vh;
            overflow: auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 24px 55px rgba(15, 23, 42, 0.24);
            padding: 20px;
        }

        .detail-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
        }

        .detail-modal-header h3 {
            margin: 0;
            color: #0f172a;
            font-size: 22px;
        }

        .detail-modal-close {
            width: 34px;
            height: 34px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 10px;
        }

        .detail-item {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 10px 12px;
            background: #f8fafc;
        }

        .detail-item-label {
            margin: 0 0 6px;
            font-size: 11px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .detail-item-value {
            margin: 0;
            font-size: 14px;
            color: #0f172a;
            line-height: 1.6;
            white-space: pre-line;
            word-break: break-word;
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/institucionales/icons/Isotipo grande.png" alt="Impulsa Emprende" class="sidebar-brand-icon">
                <span class="logo-text">impulsa emprende</span>
            </div>
            <nav class="sidebar-menu">
                <ul>
                    <li onclick="location.href='admin_dashboard.php'">
                        <span class="material-icons" style="color:#6366f1">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='admin_users.php'">
                        <span class="material-icons" style="color:#2563eb">group</span>
                        <span class="link-text">Usuarios</span>
                    </li>
                    <li onclick="location.href='admin_proceso_emprende.php'">
                        <span class="material-icons" style="color:#0f766e">assignment</span>
                        <span class="link-text">Proceso emprende</span>
                    </li>
                    <li class="active" onclick="location.href='admin_newproject.php'">
                        <span class="material-icons" style="color:#f59e0b">rocket_launch</span>
                        <span class="link-text">Solicitudes externas</span>
                    </li>
                    <li onclick="location.href='admin_tareas.php'">
                        <span class="material-icons" style="color:#7c3aed">task_alt</span>
                        <span class="link-text">Tareas</span>
                    </li>
                    <?php require __DIR__ . '/../../partials/marketing_submenu.php'; ?>
                    <li onclick="location.href='../../logout.php'">
                        <span class="material-icons" style="color:red">logout</span>
                        <span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <div class="main">
            <header class="navbar">
                <div class="navbar-left">
                    <button class="btn-icon" onclick="toggleSidebar()">
                        <span class="material-icons">menu</span>
                    </button>
                    <div class="navbar-title">Solicitudes de proyecto</div>
                </div>
                <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
            </header>

            <section class="content">
                <div class="card">
                    <div class="hero-card">
                        <h1>Solicitudes recibidas</h1>
                        <p><?= $displayName ?>, aca podes ver las solicitudes enviadas desde el formulario publico de nuevos proyectos.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="section-title">
                        <div>
                            <h2>Solicitudes de proyectos de software</h2>
                            <p>Formularios enviados desde la pagina publica de nuevo proyecto.</p>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table class="requests-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Nombre del proyecto</th>
                                    <th>Estado</th>
                                    <th>Correo</th>
                                    <th>Whatsapp</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($solicitudes)): ?>
                                    <?php foreach ($solicitudes as $solicitud): ?>
                                        <?php
                                        $estado = (string)($solicitud['estado'] ?? 'nuevo');
                                        $estadoClass = 'badge-nuevo';
                                        if ($estado === 'revisado') {
                                            $estadoClass = 'badge-revisado';
                                        } elseif ($estado === 'descartado') {
                                            $estadoClass = 'badge-descartado';
                                        }
                                        ?>
                                        <tr>
                                            <td class="muted">#<?= (int)($solicitud['id'] ?? 0) ?></td>
                                            <td><?= htmlspecialchars((string)($solicitud['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string)($solicitud['nombre_proyecto'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="badge <?= $estadoClass ?>">
                                                    <?= htmlspecialchars(ucfirst($estado), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars((string)($solicitud['correo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string)($solicitud['whatsapp'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="muted">
                                                <?= !empty($solicitud['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime((string)$solicitud['created_at'] . ' -3 hours')), ENT_QUOTES, 'UTF-8') : '-' ?>
                                            </td>
                                            <td>
                                                <a class="action-icon-btn" href="admin_projects.php?source_type=software_form&amp;source_id=<?= (int)($solicitud['id'] ?? 0) ?>" title="Crear o modificar proyecto desde esta solicitud" style="margin-right:8px;text-decoration:none;">
                                                    <span class="material-icons" style="font-size:18px">add_task</span>
                                                </a>
                                                <button type="button" class="action-icon-btn" data-open-request-detail data-request-group="software" data-request-id="<?= (int)($solicitud['id'] ?? 0) ?>" title="Ver detalle completo">
                                                    <span class="material-icons" style="font-size:18px">visibility</span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center;color:#9ca3af;padding:24px">
                                            Todavia no hay solicitudes de proyecto cargadas.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="section-title">
                        <div>
                            <h2>Solicitudes de landing pages</h2>
                            <p>Formularios enviados desde la nueva pagina publica para landing pages.</p>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table class="requests-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Nombre del proyecto</th>
                                    <th>Estado</th>
                                    <th>Correo</th>
                                    <th>Whatsapp</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($solicitudesLandingExternal)): ?>
                                    <?php foreach ($solicitudesLandingExternal as $solicitud): ?>
                                        <?php
                                        $estado = (string)($solicitud['estado'] ?? 'nuevo');
                                        $estadoClass = 'badge-nuevo';
                                        if ($estado === 'revisado') {
                                            $estadoClass = 'badge-revisado';
                                        } elseif ($estado === 'descartado') {
                                            $estadoClass = 'badge-descartado';
                                        }
                                        ?>
                                        <tr>
                                            <td class="muted">#<?= (int)($solicitud['id'] ?? 0) ?></td>
                                            <td><?= htmlspecialchars((string)($solicitud['nombre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string)($solicitud['nombre_proyecto'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="badge <?= $estadoClass ?>">
                                                    <?= htmlspecialchars(ucfirst($estado), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars((string)($solicitud['correo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string)($solicitud['whatsapp'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="muted">
                                                <?= !empty($solicitud['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime((string)$solicitud['created_at'] . ' -3 hours')), ENT_QUOTES, 'UTF-8') : '-' ?>
                                            </td>
                                            <td>
                                                <a class="action-icon-btn" href="admin_projects.php?source_type=landing_page_external&amp;source_id=<?= (int)($solicitud['id'] ?? 0) ?>" title="Crear o modificar proyecto desde esta solicitud" style="margin-right:8px;text-decoration:none;">
                                                    <span class="material-icons" style="font-size:18px">add_task</span>
                                                </a>
                                                <button type="button" class="action-icon-btn" data-open-request-detail data-request-group="landing_external" data-request-id="<?= (int)($solicitud['id'] ?? 0) ?>" title="Ver detalle completo">
                                                    <span class="material-icons" style="font-size:18px">visibility</span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center;color:#9ca3af;padding:24px">
                                            Todavia no hay solicitudes de landing pages cargadas.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="detail-modal-backdrop" id="request-detail-modal-backdrop" aria-hidden="true">
        <div class="detail-modal" role="dialog" aria-modal="true" aria-labelledby="request-detail-title">
            <div class="detail-modal-header">
                <h3 id="request-detail-title">Detalle de solicitud</h3>
                <button type="button" class="detail-modal-close" id="request-detail-close" aria-label="Cerrar detalle">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="detail-grid" id="request-detail-grid"></div>
        </div>
    </div>

    <?php $perfilObligatorio = false; ?>
    <?php require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>

    <script>
        const requestDetails = {
            software: <?= json_encode($detalleSolicitudes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
            landing_external: <?= json_encode($detalleSolicitudesLandingExternal, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
        };
        const detailButtons = document.querySelectorAll('[data-open-request-detail]');
        const detailBackdrop = document.getElementById('request-detail-modal-backdrop');
        const detailClose = document.getElementById('request-detail-close');
        const detailGrid = document.getElementById('request-detail-grid');

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        }

        function openDetailModal(group, id) {
            const detailGroup = requestDetails[group] || {};
            const detail = detailGroup[String(id)] || detailGroup[id];
            if (!detail) return;

            detailGrid.innerHTML = Object.entries(detail).map(([label, value]) => `
                <div class="detail-item">
                    <p class="detail-item-label">${escapeHtml(label)}</p>
                    <p class="detail-item-value">${escapeHtml(value || '-')}</p>
                </div>
            `).join('');

            detailBackdrop.classList.add('is-open');
            detailBackdrop.setAttribute('aria-hidden', 'false');
        }

        function closeDetailModal() {
            detailBackdrop.classList.remove('is-open');
            detailBackdrop.setAttribute('aria-hidden', 'true');
        }

        detailButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                openDetailModal(btn.dataset.requestGroup || 'software', btn.dataset.requestId || '');
            });
        });

        detailClose.addEventListener('click', closeDetailModal);
        detailBackdrop.addEventListener('click', (event) => {
            if (event.target === detailBackdrop) closeDetailModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && detailBackdrop.classList.contains('is-open')) {
                closeDetailModal();
            }
        });
    </script>
</body>
</html>
