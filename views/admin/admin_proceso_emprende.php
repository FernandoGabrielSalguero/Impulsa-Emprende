<?php
require_once __DIR__ . '/../../controllers/admin_proceso_emprendeController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Admin';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');
$avatarUrl = obtenerAvatarUrl($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null));
$avatarInitial = obtenerInicialAvatar($displayName);
$detalleCompletoPorUsuario = [];

foreach ($emprendedoresProceso as $usuarioDetalle) {
    $nombreCompleto = trim((string) (($usuarioDetalle['nombre'] ?? '') . ' ' . ($usuarioDetalle['apellido'] ?? '')));
    $labelDetalle = $usuarioDetalle['apodo'] ?: ($nombreCompleto ?: ($usuarioDetalle['correo'] ?? 'Sin nombre'));
    $ubicacionLanding = trim(implode(', ', array_filter([
        (string) ($usuarioDetalle['landing_localidad'] ?? ''),
        (string) ($usuarioDetalle['landing_provincia'] ?? ''),
        (string) ($usuarioDetalle['landing_pais'] ?? ''),
    ])));
    $direccionLanding = trim(implode(' ', array_filter([
        (string) ($usuarioDetalle['landing_calle'] ?? ''),
        (string) ($usuarioDetalle['landing_numero'] ?? ''),
    ])));

    $detalleCompletoPorUsuario[(int) $usuarioDetalle['id']] = [
        'usuario' => [
            'id' => (int) $usuarioDetalle['id'],
            'label' => (string) $labelDetalle,
            'nombre_completo' => (string) $nombreCompleto,
            'correo' => (string) ($usuarioDetalle['correo'] ?? ''),
            'whatsapp' => (string) ($usuarioDetalle['whatsapp'] ?? ''),
            'fecha_nacimiento' => !empty($usuarioDetalle['fecha_nacimiento']) ? date('d/m/Y', strtotime((string) $usuarioDetalle['fecha_nacimiento'])) : '',
            'fecha_registro' => !empty($usuarioDetalle['created_at']) ? date('d/m/Y H:i', strtotime((string) $usuarioDetalle['created_at'])) : '',
            'verificado' => !empty($usuarioDetalle['email_verified_at']) ? 'Si' : 'No',
        ],
        'mision' => [
            'estructura' => (string) ($usuarioDetalle['mision_estructura'] ?? ''),
            'a_quien_ayudo' => (string) ($usuarioDetalle['mision_a_quien_ayudo'] ?? ''),
            'que_problema_resuelvo' => (string) ($usuarioDetalle['mision_que_problema_resuelvo'] ?? ''),
            'como_lo_resuelvo' => (string) ($usuarioDetalle['mision_como_lo_resuelvo'] ?? ''),
        ],
        'vision' => [
            'estructura' => (string) ($usuarioDetalle['vision_estructura'] ?? ''),
            'conversion_futura' => (string) ($usuarioDetalle['vision_conversion_futura'] ?? ''),
            'lugar_mercado' => (string) ($usuarioDetalle['vision_lugar_mercado'] ?? ''),
            'impacto_generado' => (string) ($usuarioDetalle['vision_impacto_generado'] ?? ''),
        ],
        'buyer' => [
            'estructura' => (string) ($usuarioDetalle['buyer_persona_estructura'] ?? ''),
            'cliente_ideal' => (string) ($usuarioDetalle['buyer_cliente_ideal'] ?? ''),
            'edad_etapa_vida' => (string) ($usuarioDetalle['buyer_edad_etapa_vida'] ?? ''),
            'ocupacion_realidad_diaria' => (string) ($usuarioDetalle['buyer_ocupacion_realidad_diaria'] ?? ''),
            'problema_necesidad' => (string) ($usuarioDetalle['buyer_problema_necesidad'] ?? ''),
            'preocupacion_frustracion' => (string) ($usuarioDetalle['buyer_preocupacion_frustracion'] ?? ''),
            'objetivo_mejora' => (string) ($usuarioDetalle['buyer_objetivo_mejora'] ?? ''),
            'motivacion_busqueda' => (string) ($usuarioDetalle['buyer_motivacion_busqueda'] ?? ''),
            'freno_dudas' => (string) ($usuarioDetalle['buyer_freno_dudas'] ?? ''),
            'criterio_eleccion' => (string) ($usuarioDetalle['buyer_criterio_eleccion'] ?? ''),
            'busqueda_informacion' => (string) ($usuarioDetalle['buyer_busqueda_informacion'] ?? ''),
            'decision_compra' => (string) ($usuarioDetalle['buyer_decision_compra'] ?? ''),
            'motivo_eleccion' => (string) ($usuarioDetalle['buyer_motivo_eleccion'] ?? ''),
        ],
        'landing' => [
            'nombre_emprendimiento' => (string) ($usuarioDetalle['landing_nombre_emprendimiento'] ?? ''),
            'fecha_inicio' => !empty($usuarioDetalle['landing_fecha_inicio']) ? date('d/m/Y', strtotime((string) $usuarioDetalle['landing_fecha_inicio'])) : '',
            'descripcion' => (string) ($usuarioDetalle['landing_descripcion'] ?? ''),
            'dominio_registrado' => !empty($usuarioDetalle['landing_dominio_registrado']) ? 'Si' : 'No',
            'hosting_propio' => !empty($usuarioDetalle['landing_hosting_propio']) ? 'Si' : 'No',
            'cantidad_colaboradores' => (string) ($usuarioDetalle['landing_cantidad_colaboradores'] ?? ''),
            'nombre_fundador' => (string) ($usuarioDetalle['landing_nombre_fundador'] ?? ''),
            'vende_productos' => !empty($usuarioDetalle['landing_vende_productos']) ? 'Si' : 'No',
            'vende_servicios' => !empty($usuarioDetalle['landing_vende_servicios']) ? 'Si' : 'No',
            'ya_factura' => !empty($usuarioDetalle['landing_ya_factura']) ? 'Si' : 'No',
            'espacio_fisico' => !empty($usuarioDetalle['landing_espacio_fisico']) ? 'Si' : 'No',
            'categoria' => (string) ($usuarioDetalle['landing_categoria'] ?? ''),
            'subcategoria' => (string) ($usuarioDetalle['landing_subcategoria'] ?? ''),
            'telefono_contacto' => (string) ($usuarioDetalle['landing_telefono_contacto'] ?? ''),
            'ubicacion' => (string) $ubicacionLanding,
            'direccion' => (string) $direccionLanding,
            'estado' => !empty($usuarioDetalle['landing_completada']) ? 'Solicitada' : 'Pendiente',
        ],
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Impulsa - Proceso Emprende</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
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
        .hero-card {
            display: grid;
            gap: 10px;
        }
        .hero-card h1 {
            margin: 0;
            font-size: 28px;
        }
        .hero-card p {
            margin: 0;
            color: #6b7280;
            line-height: 1.6;
            max-width: 850px;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 14px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .summary-card {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 18px;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .summary-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .summary-icon.total { background: #eef2ff; color: #4338ca; }
        .summary-icon.step-1 { background: #fee2e2; color: #b91c1c; }
        .summary-icon.step-2 { background: #fef3c7; color: #b45309; }
        .summary-icon.step-3 { background: #dbeafe; color: #1d4ed8; }
        .summary-icon.ready { background: #dcfce7; color: #15803d; }
        .summary-label {
            margin: 0 0 4px;
            font-size: 13px;
            color: #6b7280;
        }
        .summary-value {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        .users-table thead th {
            text-align: left;
            font-size: 12px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .users-table tbody td {
            padding: 12px;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .users-table tbody tr:last-child td {
            border-bottom: none;
        }
        .users-table th.progress-column,
        .users-table td.progress-column {
            width: 360px;
            min-width: 360px;
        }
        .table-wrap {
            overflow-x: auto;
        }
        .user-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .user-initials {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #e0e7ff;
            color: #3730a3;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .user-initials img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
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
        .badge-step-1 { background: #fee2e2; color: #b91c1c; }
        .badge-step-2 { background: #fef3c7; color: #b45309; }
        .badge-step-3 { background: #dbeafe; color: #1d4ed8; }
        .badge-step-4 { background: #dcfce7; color: #15803d; }
        .progress-list {
            display: flex;
            align-items: flex-start;
            gap: 0;
            min-width: 340px;
        }
        .progress-step {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 78px;
            flex-shrink: 0;
        }
        .progress-step:not(:last-child) {
            margin-right: 12px;
        }
        .progress-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 17px;
            left: calc(100% - 6px);
            width: 24px;
            height: 4px;
            background: #e5e7eb;
            border-radius: 999px;
        }
        .progress-step.is-done:not(:last-child)::after {
            background: #86efac;
        }
        .progress-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background: #f3f4f6;
            color: #6b7280;
            border: 2px solid transparent;
            flex-shrink: 0;
        }
        .progress-chip.is-done {
            background: #dcfce7;
            color: #15803d;
            border-color: #86efac;
        }
        .progress-chip.is-current {
            background: #dbeafe;
            color: #1d4ed8;
            border-color: #93c5fd;
        }
        .progress-chip-label {
            display: block;
            width: 100%;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            line-height: 1.2;
        }
        .progress-chip-label.is-done {
            color: #15803d;
        }
        .progress-chip-label.is-current {
            color: #1d4ed8;
        }
        .progress-cell {
            padding-bottom: 16px !important;
        }
        .actions-cell {
            position: relative;
        }
        .actions-wrap {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .action-btn,
        .action-menu-btn {
            width: 34px;
            height: 34px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }
        .action-btn:hover,
        .action-menu-btn:hover,
        .action-menu-btn.is-open {
            background: #f8fafc;
            color: #111827;
            border-color: #cbd5e1;
        }
        .action-btn.is-complete {
            background: #dcfce7;
            color: #15803d;
            border-color: #86efac;
        }
        .action-btn .material-icons,
        .action-menu-btn .material-icons {
            font-size: 18px;
        }
        .action-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 12px;
            min-width: 180px;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
            display: none;
            z-index: 30;
        }
        .action-menu.is-open {
            display: block;
        }
        .action-menu-empty {
            margin: 0;
            font-size: 13px;
            color: #94a3b8;
        }
        .action-menu-item {
            width: 100%;
            border: 0;
            background: transparent;
            padding: 10px 12px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            text-align: left;
        }
        .action-menu-item:hover {
            background: #f8fafc;
        }
        .action-btn.is-disabled {
            cursor: default;
            opacity: 0.7;
        }
        .detail-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1200;
        }
        .detail-modal-backdrop.is-open {
            display: flex;
        }
        .detail-modal {
            width: min(100%, 720px);
            max-height: min(85vh, 820px);
            overflow: auto;
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.2);
            padding: 24px;
        }
        .detail-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }
        .detail-modal-kicker {
            margin: 0 0 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #0f766e;
        }
        .detail-modal-title {
            margin: 0;
            font-size: 24px;
            color: #111827;
        }
        .detail-modal-subtitle {
            margin: 6px 0 0;
            font-size: 14px;
            color: #64748b;
        }
        .detail-modal-close {
            width: 38px;
            height: 38px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .detail-modal-content {
            display: grid;
            gap: 14px;
        }
        .detail-modal-block {
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 16px 18px;
            background: #f8fafc;
        }
        .detail-modal-block-title {
            margin: 0 0 8px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #0f172a;
        }
        .detail-modal-block-text {
            margin: 0;
            font-size: 14px;
            line-height: 1.7;
            color: #334155;
            white-space: pre-line;
        }
        .detail-modal-empty {
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }
        .full-detail-modal {
            width: min(100%, 920px);
            max-height: min(88vh, 920px);
        }
        .full-detail-grid {
            display: grid;
            gap: 16px;
        }
        .full-detail-section {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            background: #fff;
            overflow: hidden;
        }
        .full-detail-section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 18px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }
        .full-detail-section-header h4 {
            margin: 0;
            font-size: 16px;
            color: #0f172a;
        }
        .full-detail-fields {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            padding: 18px;
        }
        .full-detail-field {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
            background: #f8fafc;
        }
        .full-detail-field.is-full {
            grid-column: 1 / -1;
        }
        .full-detail-field-label {
            margin: 0 0 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
        }
        .full-detail-field-value {
            margin: 0;
            font-size: 14px;
            line-height: 1.65;
            color: #0f172a;
            white-space: pre-line;
        }
        .muted {
            color: #6b7280;
            font-size: 13px;
        }
        @media (max-width: 900px) {
            .users-table th.progress-column,
            .users-table td.progress-column {
                width: 340px;
                min-width: 340px;
            }
            .progress-step {
                width: 72px;
            }
            .progress-step:not(:last-child)::after {
                width: 20px;
            }
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
                    <li class="active" onclick="location.href='admin_proceso_emprende.php'">
                        <span class="material-icons" style="color:#0f766e">assignment</span>
                        <span class="link-text">Proceso emprende</span>
                    </li>
                    <li onclick="location.href='admin_newproject.php'">
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
                    <div class="navbar-title">Proceso de incubacion</div>
                </div>
                <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
            </header>

            <section class="content">
                <div class="card">
                    <div class="hero-card">
                        <h1>Seguimiento de emprendedores</h1>
                        <p><?= $displayName ?>, aca podes ver en que etapa del recorrido esta cada usuario con rol <strong>impulsa_emprendedor</strong>. El proceso se ordena en mision, vision, buyer persona y luego la habilitacion para solicitar la landing page.</p>
                    </div>
                </div>

                <div class="card">
                    <p class="section-title">Resumen del proceso</p>
                    <div class="summary-grid">
                        <div class="summary-card">
                            <div class="summary-icon total">
                                <span class="material-icons">group</span>
                            </div>
                            <div>
                                <p class="summary-label">Total de emprendedores</p>
                                <p class="summary-value"><?= number_format((int) $resumenProceso['total'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-icon step-1">
                                <span class="material-icons">looks_one</span>
                            </div>
                            <div>
                                <p class="summary-label">En paso 1: Mision</p>
                                <p class="summary-value"><?= number_format((int) $resumenProceso['paso_1'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-icon step-2">
                                <span class="material-icons">looks_two</span>
                            </div>
                            <div>
                                <p class="summary-label">En paso 2: Vision</p>
                                <p class="summary-value"><?= number_format((int) $resumenProceso['paso_2'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-icon step-3">
                                <span class="material-icons">looks_3</span>
                            </div>
                            <div>
                                <p class="summary-label">En paso 3: Buyer persona</p>
                                <p class="summary-value"><?= number_format((int) $resumenProceso['paso_3'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-icon ready">
                                <span class="material-icons">rocket_launch</span>
                            </div>
                            <div>
                                <p class="summary-label">Listos para landing</p>
                                <p class="summary-value"><?= number_format((int) $resumenProceso['listos_landing'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <p class="section-title">Detalle por usuario</p>
                    <div class="table-wrap">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th>Verificado</th>
                                    <th>Paso actual</th>
                                    <th class="progress-column">Progreso</th>
                                    <th>Landing</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($emprendedoresProceso)): ?>
                                    <?php foreach ($emprendedoresProceso as $usuario): ?>
                                        <?php
                                        $nombre = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''));
                                        $label = $usuario['apodo'] ?: ($nombre ?: ($usuario['correo'] ?? 'Sin nombre'));
                                        $inicial = mb_strtoupper(mb_substr($label, 0, 1));
                                        $pasoActual = (int) ($usuario['paso_actual'] ?? 1);
                                        $badgeClass = 'badge-step-' . min(max($pasoActual, 1), 4);
                                        $landingSolicitada = !empty($usuario['landing_completada']);
                                        $rowAvatarUrl = obtenerAvatarUrl($usuario['avatar_path'] ?? null);
                                        $acciones = [
                                            [
                                                'key' => 'mision',
                                                'label' => 'Mision',
                                                'icon' => 'track_changes',
                                                'done' => !empty($usuario['mision_completada']),
                                                'content' => trim((string) ($usuario['mision_estructura'] ?? '')),
                                            ],
                                            [
                                                'key' => 'vision',
                                                'label' => 'Vision',
                                                'icon' => 'lightbulb',
                                                'done' => !empty($usuario['vision_completada']),
                                                'content' => trim((string) ($usuario['vision_estructura'] ?? '')),
                                            ],
                                            [
                                                'key' => 'buyer_persona',
                                                'label' => 'Buyer Persona',
                                                'icon' => 'groups',
                                                'done' => !empty($usuario['buyer_persona_completado']),
                                                'content' => trim((string) ($usuario['buyer_persona_estructura'] ?? '')),
                                            ],
                                            [
                                                'key' => 'landing',
                                                'label' => 'Solicitud Landing Page',
                                                'icon' => 'rocket_launch',
                                                'done' => $landingSolicitada,
                                                'content' => trim((string) ($usuario['landing_descripcion'] ?? '')),
                                            ],
                                        ];
                                        $progreso = [
                                            [
                                                'short' => 'M',
                                                'label' => 'Mision',
                                                'done' => !empty($usuario['mision_completada']),
                                                'current' => $pasoActual === 1,
                                            ],
                                            [
                                                'short' => 'V',
                                                'label' => 'Vision',
                                                'done' => !empty($usuario['vision_completada']),
                                                'current' => $pasoActual === 2,
                                            ],
                                            [
                                                'short' => 'BP',
                                                'label' => 'Buyer',
                                                'done' => !empty($usuario['buyer_persona_completado']),
                                                'current' => $pasoActual === 3,
                                            ],
                                            [
                                                'short' => 'LP',
                                                'label' => 'Landing',
                                                'done' => $landingSolicitada,
                                                'current' => $pasoActual === 4,
                                            ],
                                        ];
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="user-pill">
                                                    <div class="user-initials"><?php if ($rowAvatarUrl): ?><img src="<?= htmlspecialchars($rowAvatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar del usuario"><?php else: ?><?= htmlspecialchars($inicial, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></div>
                                                    <div>
                                                        <div><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></div>
                                                        <div class="muted">ID #<?= (int) $usuario['id'] ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($usuario['correo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <?php if (!empty($usuario['email_verified_at'])): ?>
                                                    <span class="badge badge-step-4">
                                                        <span class="material-icons" style="font-size:14px">verified</span>
                                                        Verificado
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-step-2">
                                                        <span class="material-icons" style="font-size:14px">warning</span>
                                                        Pendiente
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $badgeClass ?>">
                                                    <span class="material-icons" style="font-size:14px"><?= $pasoActual === 4 ? 'check_circle' : 'pending_actions' ?></span>
                                                    <?= htmlspecialchars((string) ($usuario['estado_etapa'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td class="progress-cell progress-column">
                                                <div class="progress-list">
                                                    <?php foreach ($progreso as $item): ?>
                                                        <span class="progress-step <?= $item['done'] ? 'is-done' : '' ?>">
                                                            <span class="progress-chip <?= $item['done'] ? 'is-done' : '' ?> <?= !$item['done'] && $item['current'] ? 'is-current' : '' ?>">
                                                                <?= htmlspecialchars($item['short'], ENT_QUOTES, 'UTF-8') ?>
                                                            </span>
                                                            <span class="progress-chip-label <?= $item['done'] ? 'is-done' : '' ?> <?= !$item['done'] && $item['current'] ? 'is-current' : '' ?>"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $landingSolicitada ? 'badge-step-4' : 'badge-step-2' ?>">
                                                    <?= $landingSolicitada ? 'Solicitada' : 'Pendiente' ?>
                                                </span>
                                            </td>
                                            <td class="muted">
                                                <?= !empty($usuario['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime((string) $usuario['created_at'])), ENT_QUOTES, 'UTF-8') : '-' ?>
                                            </td>
                                            <td class="actions-cell">
                                                <div class="actions-wrap">
                                                    <?php foreach ($acciones as $accion): ?>
                                                        <button
                                                            type="button"
                                                            class="action-btn <?= $accion['done'] ? 'is-complete' : 'is-disabled' ?>"
                                                            title="<?= htmlspecialchars($accion['label'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-detail-trigger
                                                            data-detail-key="<?= htmlspecialchars($accion['key'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-detail-label="<?= htmlspecialchars($accion['label'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-detail-user="<?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?>"
                                                            data-detail-content="<?= htmlspecialchars((string) $accion['content'], ENT_QUOTES, 'UTF-8') ?>"
                                                            data-landing-name="<?= htmlspecialchars((string) ($usuario['landing_nombre_emprendimiento'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                            data-landing-founder="<?= htmlspecialchars((string) ($usuario['landing_nombre_fundador'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                            data-landing-phone="<?= htmlspecialchars((string) ($usuario['landing_telefono_contacto'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                            data-landing-location="<?= htmlspecialchars(trim(implode(', ', array_filter([
                                                                (string) ($usuario['landing_localidad'] ?? ''),
                                                                (string) ($usuario['landing_provincia'] ?? ''),
                                                                (string) ($usuario['landing_pais'] ?? ''),
                                                            ]))), ENT_QUOTES, 'UTF-8') ?>"
                                                            <?= $accion['done'] ? '' : 'disabled' ?>>
                                                            <span class="material-icons"><?= htmlspecialchars($accion['icon'], ENT_QUOTES, 'UTF-8') ?></span>
                                                        </button>
                                                    <?php endforeach; ?>
                                                    <button
                                                        type="button"
                                                        class="action-menu-btn"
                                                        data-actions-toggle
                                                        aria-haspopup="true"
                                                        aria-expanded="false"
                                                        title="Mas opciones">
                                                        <span class="material-icons">more_vert</span>
                                                    </button>
                                                </div>
                                                <div class="action-menu" data-actions-menu>
                                                    <button
                                                        type="button"
                                                        class="action-menu-item"
                                                        data-full-detail-trigger
                                                        data-user-id="<?= (int) $usuario['id'] ?>">
                                                        <span class="material-icons" style="font-size:18px">visibility</span>
                                                        Ver completo
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align:center;color:#9ca3af;padding:24px">
                                            No hay usuarios con rol impulsa_emprendedor para mostrar.
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

    <?php require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>

    <div class="detail-modal-backdrop" id="detail-modal-backdrop" aria-hidden="true">
        <div class="detail-modal" role="dialog" aria-modal="true" aria-labelledby="detail-modal-title">
            <div class="detail-modal-header">
                <div>
                    <p class="detail-modal-kicker" id="detail-modal-kicker">Detalle</p>
                    <h3 class="detail-modal-title" id="detail-modal-title">Informacion del proceso</h3>
                    <p class="detail-modal-subtitle" id="detail-modal-subtitle"></p>
                </div>
                <button type="button" class="detail-modal-close" id="detail-modal-close" aria-label="Cerrar detalle">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="detail-modal-content" id="detail-modal-content"></div>
        </div>
    </div>

    <div class="detail-modal-backdrop" id="full-detail-modal-backdrop" aria-hidden="true">
        <div class="detail-modal full-detail-modal" role="dialog" aria-modal="true" aria-labelledby="full-detail-modal-title">
            <div class="detail-modal-header">
                <div>
                    <p class="detail-modal-kicker">Ver completo</p>
                    <h3 class="detail-modal-title" id="full-detail-modal-title">Expediente del emprendedor</h3>
                    <p class="detail-modal-subtitle" id="full-detail-modal-subtitle"></p>
                </div>
                <button type="button" class="detail-modal-close" id="full-detail-modal-close" aria-label="Cerrar detalle completo">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="full-detail-grid" id="full-detail-modal-content"></div>
        </div>
    </div>

    <script>
        const sesion = {
            user_id: <?= json_encode($_SESSION['user_id'] ?? null) ?>,
            correo: <?= json_encode($_SESSION['correo'] ?? null) ?>,
            rol: <?= json_encode($_SESSION['rol'] ?? null) ?>,
        };
        const detalleCompletoPorUsuario = <?= json_encode($detalleCompletoPorUsuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        console.group('[Impulsa] Sesion activa - Admin Proceso Emprende');
        console.table(sesion);
        console.groupEnd();

        const detailModalBackdrop = document.getElementById('detail-modal-backdrop');
        const detailModalClose = document.getElementById('detail-modal-close');
        const detailModalKicker = document.getElementById('detail-modal-kicker');
        const detailModalTitle = document.getElementById('detail-modal-title');
        const detailModalSubtitle = document.getElementById('detail-modal-subtitle');
        const detailModalContent = document.getElementById('detail-modal-content');
        const fullDetailModalBackdrop = document.getElementById('full-detail-modal-backdrop');
        const fullDetailModalClose = document.getElementById('full-detail-modal-close');
        const fullDetailModalTitle = document.getElementById('full-detail-modal-title');
        const fullDetailModalSubtitle = document.getElementById('full-detail-modal-subtitle');
        const fullDetailModalContent = document.getElementById('full-detail-modal-content');
        const actionToggles = document.querySelectorAll('[data-actions-toggle]');
        const detailTriggers = document.querySelectorAll('[data-detail-trigger]');
        const fullDetailTriggers = document.querySelectorAll('[data-full-detail-trigger]');

        const escapeHtml = (value) => {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        };

        const buildFullDetailField = (label, value, isFull = false) => `
            <div class="full-detail-field ${isFull ? 'is-full' : ''}">
                <p class="full-detail-field-label">${escapeHtml(label)}</p>
                <p class="full-detail-field-value">${escapeHtml(value || '-')}</p>
            </div>
        `;

        const buildFullDetailSection = (title, icon, fields) => `
            <section class="full-detail-section">
                <div class="full-detail-section-header">
                    <span class="material-icons">${escapeHtml(icon)}</span>
                    <h4>${escapeHtml(title)}</h4>
                </div>
                <div class="full-detail-fields">
                    ${fields.join('')}
                </div>
            </section>
        `;

        const createDetailBlock = (title, text) => {
            if (!text) {
                return '';
            }

            return `
                <div class="detail-modal-block">
                    <p class="detail-modal-block-title">${escapeHtml(title)}</p>
                    <p class="detail-modal-block-text">${escapeHtml(text)}</p>
                </div>
            `;
        };

        const openDetailModal = (button) => {
            if (!button || button.disabled) {
                return;
            }

            const key = button.dataset.detailKey || '';
            const label = button.dataset.detailLabel || 'Detalle';
            const user = button.dataset.detailUser || 'Usuario';
            const content = button.dataset.detailContent || '';
            const landingName = button.dataset.landingName || '';
            const landingFounder = button.dataset.landingFounder || '';
            const landingPhone = button.dataset.landingPhone || '';
            const landingLocation = button.dataset.landingLocation || '';

            detailModalKicker.textContent = label;
            detailModalTitle.textContent = user;
            detailModalSubtitle.textContent = `Contenido cargado por ${user}`;

            if (key === 'landing') {
                detailModalContent.innerHTML = [
                    createDetailBlock('Emprendimiento', landingName),
                    createDetailBlock('Descripcion', content),
                    createDetailBlock('Fundador/a', landingFounder),
                    createDetailBlock('Telefono', landingPhone),
                    createDetailBlock('Ubicacion', landingLocation),
                ].filter(Boolean).join('') || '<p class="detail-modal-empty">No hay informacion disponible para esta solicitud.</p>';
            } else {
                detailModalContent.innerHTML = createDetailBlock(label, content) || '<p class="detail-modal-empty">No hay informacion disponible para este paso.</p>';
            }

            detailModalBackdrop.classList.add('is-open');
            detailModalBackdrop.setAttribute('aria-hidden', 'false');
        };

        const closeDetailModal = () => {
            detailModalBackdrop.classList.remove('is-open');
            detailModalBackdrop.setAttribute('aria-hidden', 'true');
        };

        const openFullDetailModal = (userId) => {
            const detalle = detalleCompletoPorUsuario[String(userId)] || detalleCompletoPorUsuario[userId];
            if (!detalle) {
                return;
            }

            const usuario = detalle.usuario || {};
            const mision = detalle.mision || {};
            const vision = detalle.vision || {};
            const buyer = detalle.buyer || {};
            const landing = detalle.landing || {};

            fullDetailModalTitle.textContent = usuario.label || 'Expediente del emprendedor';
            fullDetailModalSubtitle.textContent = `ID #${usuario.id || '-'} · Correo ${usuario.correo || '-'}`;
            fullDetailModalContent.innerHTML = [
                buildFullDetailSection('Datos personales', 'person', [
                    buildFullDetailField('Nombre completo', usuario.nombre_completo || usuario.label || '-'),
                    buildFullDetailField('Apodo', usuario.label || '-'),
                    buildFullDetailField('Correo', usuario.correo || '-'),
                    buildFullDetailField('Whatsapp', usuario.whatsapp || '-'),
                    buildFullDetailField('Fecha de nacimiento', usuario.fecha_nacimiento || '-'),
                    buildFullDetailField('Email verificado', usuario.verificado || '-'),
                    buildFullDetailField('Registro', usuario.fecha_registro || '-'),
                ]),
                buildFullDetailSection('Mision', 'track_changes', [
                    buildFullDetailField('A quien ayuda', mision.a_quien_ayudo || '-'),
                    buildFullDetailField('Que problema resuelve', mision.que_problema_resuelvo || '-', true),
                    buildFullDetailField('Como lo resuelve', mision.como_lo_resuelvo || '-', true),
                    buildFullDetailField('mision_estructura', mision.estructura || '-', true),
                ]),
                buildFullDetailSection('Vision', 'lightbulb', [
                    buildFullDetailField('Conversion futura', vision.conversion_futura || '-', true),
                    buildFullDetailField('Lugar en el mercado', vision.lugar_mercado || '-', true),
                    buildFullDetailField('Impacto generado', vision.impacto_generado || '-', true),
                    buildFullDetailField('vision_estructura', vision.estructura || '-', true),
                ]),
                buildFullDetailSection('Buyer Persona', 'groups', [
                    buildFullDetailField('Cliente ideal', buyer.cliente_ideal || '-'),
                    buildFullDetailField('Edad y etapa de vida', buyer.edad_etapa_vida || '-'),
                    buildFullDetailField('Ocupacion y realidad diaria', buyer.ocupacion_realidad_diaria || '-', true),
                    buildFullDetailField('Problema o necesidad', buyer.problema_necesidad || '-', true),
                    buildFullDetailField('Preocupacion o frustracion', buyer.preocupacion_frustracion || '-', true),
                    buildFullDetailField('Objetivo o mejora', buyer.objetivo_mejora || '-', true),
                    buildFullDetailField('Motivacion de busqueda', buyer.motivacion_busqueda || '-', true),
                    buildFullDetailField('Frenos o dudas', buyer.freno_dudas || '-', true),
                    buildFullDetailField('Criterio de eleccion', buyer.criterio_eleccion || '-', true),
                    buildFullDetailField('Busqueda de informacion', buyer.busqueda_informacion || '-', true),
                    buildFullDetailField('Decision de compra', buyer.decision_compra || '-', true),
                    buildFullDetailField('Motivo de eleccion', buyer.motivo_eleccion || '-', true),
                    buildFullDetailField('buyer_persona_estructura', buyer.estructura || '-', true),
                ]),
                buildFullDetailSection('Solicitud Landing Page', 'rocket_launch', [
                    buildFullDetailField('Estado', landing.estado || '-'),
                    buildFullDetailField('Emprendimiento', landing.nombre_emprendimiento || '-'),
                    buildFullDetailField('Fecha de inicio', landing.fecha_inicio || '-'),
                    buildFullDetailField('Fundador/a', landing.nombre_fundador || '-'),
                    buildFullDetailField('Telefono de contacto', landing.telefono_contacto || '-'),
                    buildFullDetailField('Cantidad de colaboradores', landing.cantidad_colaboradores || '-'),
                    buildFullDetailField('Categoria', landing.categoria || '-'),
                    buildFullDetailField('Subcategoria', landing.subcategoria || '-'),
                    buildFullDetailField('Vende productos', landing.vende_productos || '-'),
                    buildFullDetailField('Vende servicios', landing.vende_servicios || '-'),
                    buildFullDetailField('Ya factura', landing.ya_factura || '-'),
                    buildFullDetailField('Dominio registrado', landing.dominio_registrado || '-'),
                    buildFullDetailField('Hosting propio', landing.hosting_propio || '-'),
                    buildFullDetailField('Espacio fisico', landing.espacio_fisico || '-'),
                    buildFullDetailField('Ubicacion', landing.ubicacion || '-'),
                    buildFullDetailField('Direccion', landing.direccion || '-'),
                    buildFullDetailField('Descripcion', landing.descripcion || '-', true),
                ]),
            ].join('');

            fullDetailModalBackdrop.classList.add('is-open');
            fullDetailModalBackdrop.setAttribute('aria-hidden', 'false');
        };

        const closeFullDetailModal = () => {
            fullDetailModalBackdrop.classList.remove('is-open');
            fullDetailModalBackdrop.setAttribute('aria-hidden', 'true');
        };

        const closeAllActionMenus = () => {
            document.querySelectorAll('[data-actions-menu].is-open').forEach((menu) => {
                menu.classList.remove('is-open');
            });
            document.querySelectorAll('[data-actions-toggle].is-open').forEach((button) => {
                button.classList.remove('is-open');
                button.setAttribute('aria-expanded', 'false');
            });
        };

        actionToggles.forEach((toggle) => {
            toggle.addEventListener('click', (event) => {
                event.stopPropagation();

                const container = toggle.closest('.actions-cell');
                const menu = container ? container.querySelector('[data-actions-menu]') : null;
                const isOpen = Boolean(menu?.classList.contains('is-open'));

                closeAllActionMenus();

                if (!menu || isOpen) {
                    return;
                }

                menu.classList.add('is-open');
                toggle.classList.add('is-open');
                toggle.setAttribute('aria-expanded', 'true');
            });
        });

        detailTriggers.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                openDetailModal(button);
            });
        });

        fullDetailTriggers.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                closeAllActionMenus();
                openFullDetailModal(button.dataset.userId || '');
            });
        });

        detailModalClose.addEventListener('click', closeDetailModal);
        detailModalBackdrop.addEventListener('click', (event) => {
            if (event.target === detailModalBackdrop) {
                closeDetailModal();
            }
        });
        fullDetailModalClose.addEventListener('click', closeFullDetailModal);
        fullDetailModalBackdrop.addEventListener('click', (event) => {
            if (event.target === fullDetailModalBackdrop) {
                closeFullDetailModal();
            }
        });
        document.addEventListener('click', closeAllActionMenus);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAllActionMenus();
                closeDetailModal();
                closeFullDetailModal();
            }
        });
    </script>
</body>

</html>
