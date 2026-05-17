<?php
require_once __DIR__ . '/../../controllers/admin_projectsController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Admin';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');

$projectStatusLabels = [
    'draft' => 'Borrador',
    'planned' => 'Planificado',
    'in_progress' => 'En progreso',
    'paused' => 'Pausado',
    'in_review' => 'En revision',
    'completed' => 'Completado',
    'cancelled' => 'Cancelado',
];

$defaultProjectName = (string) ($selectedProject['project_name'] ?? $sourceRequest['nombre_proyecto'] ?? '');
$defaultClientName = (string) ($selectedProject['client_name'] ?? $sourceRequest['nombre'] ?? '');
$defaultClientEmail = (string) ($selectedProject['client_email'] ?? $sourceRequest['correo'] ?? '');
$defaultClientWhatsapp = (string) ($selectedProject['client_whatsapp'] ?? $sourceRequest['whatsapp'] ?? '');
$defaultSummary = (string) ($selectedProject['summary'] ?? $sourceRequest['q1_descripcion'] ?? '');
$defaultProjectType = (string) ($selectedProject['project_type'] ?? ($sourceType === 'landing_page_external' ? 'landing_page' : 'software'));
$defaultUserPageParam = (string) ($userPageParam['page'] ?? '');
$fallbackScope = trim(implode("\n\n", array_filter([
    !empty($sourceRequest['q2_problema']) ? 'Problema a resolver: ' . (string) $sourceRequest['q2_problema'] : '',
    !empty($sourceRequest['q8_funciones_minimas']) ? 'Funciones minimas: ' . (string) $sourceRequest['q8_funciones_minimas'] : '',
    !empty($sourceRequest['q11_integraciones']) ? 'Integraciones: ' . (string) $sourceRequest['q11_integraciones'] : '',
    !empty($sourceRequest['q18_adicional']) ? 'Adicional: ' . (string) $sourceRequest['q18_adicional'] : '',
])));
$defaultScope = (string) ($selectedProject['scope_summary'] ?? $fallbackScope);
$projectContract = $selectedProject['contract'] ?? [];
$defaultContractName = (string) ($projectContract['contract_name'] ?? '');
$defaultContractHtml = (string) ($projectContract['contract_html'] ?? '');
$hasContract = !empty($projectContract);
$isContractSigned = !empty($projectContract['is_signed']);
$contractVersion = (int) ($projectContract['version_number'] ?? 0);
$contractSignedAt = (string) ($projectContract['signed_at'] ?? '');
$contractSigner = (string) ($projectContract['signer_full_name'] ?? '');

$phasePlans = [];
foreach (($selectedProject['phases'] ?? []) as $phaseIndex => $phase) {
    $phasePlans[$phaseIndex] = [
        'title' => (string) ($phase['title'] ?? ''),
        'description' => (string) ($phase['description'] ?? ''),
        'duration_days' => (string) ($phase['duration_days'] ?? ''),
        'due_date' => (string) ($phase['due_date'] ?? ''),
        'deliverables' => [],
    ];
}
foreach (($selectedProject['deliverables'] ?? []) as $deliverable) {
    $matchedIndex = null;
    foreach (($selectedProject['phases'] ?? []) as $phaseIndex => $phase) {
        if ((int) ($phase['id'] ?? 0) === (int) ($deliverable['phase_id'] ?? 0)) {
            $matchedIndex = $phaseIndex;
            break;
        }
    }
    if ($matchedIndex === null) {
        continue;
    }
    $phasePlans[$matchedIndex]['deliverables'][] = [
        'title' => (string) ($deliverable['title'] ?? ''),
        'description' => (string) ($deliverable['description'] ?? ''),
        'completed' => (string) (($deliverable['status'] ?? '') === 'delivered' ? '1' : '0'),
    ];
}
$phasePlans = array_values($phasePlans);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impulsa - Proyecto desde solicitud</title>
    <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
    <?php $impulsaMaterialAssetBase = '../..'; require __DIR__ . '/../../partials/impulsa_material_assets.php'; ?>
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <style>
        .navbar { justify-content: space-between; }
        .navbar-left { display: flex; align-items: center; gap: 8px; }
        .sidebar-brand-icon { width: 32px; height: 32px; object-fit: contain; flex-shrink: 0; }
        .section-card h1, .section-card h2, .section-card h3 { margin: 0; }
        .muted { color: #6b7280; }
        .flash { margin-bottom: 16px; padding: 12px 14px; border-radius: 12px; font-size: 14px; }
        .flash.success { background: #dcfce7; color: #166534; }
        .flash.error { background: #fee2e2; color: #991b1b; }
        .stack { display:grid; gap:16px; }
        .section-card { border: 1px solid #e5e7eb; border-radius: 18px; padding: 18px; background: #fff; }
        .header-row, .phase-head, .deliverable-head { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap; }
        .badge { display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700; }
        .badge.planned { background:#e0f2fe; color:#0369a1; }
        .badge.in_progress { background:#ede9fe; color:#6d28d9; }
        .badge.completed { background:#dcfce7; color:#15803d; }
        .field-grid { display:grid; gap:14px; }
        .field-grid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .field-grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .field-grid.four { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .field label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
        .field input, .field select, .field textarea { width:100%; border:1px solid #d1d5db; border-radius:12px; padding:10px 12px; font:inherit; }
        .field textarea { min-height:90px; resize:vertical; }
        .meta-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-top:16px; }
        .meta-card { border:1px solid #e5e7eb; border-radius:14px; padding:12px 14px; background:#f8fafc; }
        .meta-card .label { display:block; font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:.04em; }
        .meta-card .value { display:block; font-size:16px; color:#111827; margin-top:4px; }
        .btn-row { display:flex; gap:10px; flex-wrap:wrap; }
        .title-stack { display:grid; gap:8px; }
        .project-title-meta { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        .badge.contract-none { background:#e5e7eb; color:#374151; }
        .badge.contract-pending { background:#fef3c7; color:#92400e; }
        .badge.contract-signed { background:#dcfce7; color:#166534; }
        .phase-list { display:grid; gap:16px; }
        .phase-card { border:1px solid var(--primary-color); border-radius:18px; padding:16px; background:transparent; display:grid; gap:14px; }
        .phase-title { font-size:13px; font-weight:700; color:var(--primary-color); text-transform:uppercase; letter-spacing:.04em; }
        .deliverable-list { display:grid; gap:12px; }
        .deliverable-card { border:1px solid var(--primary-color); border-radius:14px; padding:12px; background:transparent; display:grid; gap:10px; }
        .remove-btn { border:0; background:transparent; color:#b91c1c; cursor:pointer; padding:0; display:inline-flex; align-items:center; }
        .checkbox-row { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--primary-color); }
        .checkbox-row input { width:18px; height:18px; accent-color:var(--primary-color); }
        .client-user-panel { margin-top:14px; border:1px solid #e5e7eb; border-radius:16px; padding:14px; background:#f8fafc; display:grid; gap:12px; }
        .client-user-panel .checkbox-row { color:#111827; }
        .client-user-password { display:none; }
        .client-user-panel.is-enabled .client-user-password { display:block; }
        .empty { color:#9ca3af; text-align:center; padding:20px 0; }
        .modal-shell { position:fixed; inset:0; display:none; align-items:center; justify-content:center; padding:24px; background:rgba(15, 23, 42, 0.54); z-index:1400; }
        .modal-shell.is-open { display:flex; }
        .modal-card { width:min(100%, 900px); max-height:calc(100vh - 48px); overflow:auto; border-radius:24px; background:#fff; border:1px solid #e5e7eb; box-shadow:0 24px 70px rgba(15, 23, 42, 0.24); }
        .modal-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; padding:22px 24px 16px; border-bottom:1px solid #e5e7eb; }
        .modal-header h2 { margin:0; }
        .modal-close { border:0; background:transparent; cursor:pointer; color:#6b7280; }
        .modal-body { padding:22px 24px 24px; display:grid; gap:16px; }
        .quill-shell { border:1px solid #d1d5db; border-radius:16px; overflow:hidden; }
        #contract-editor { min-height:320px; background:#fff; }
        .contract-note { font-size:13px; color:#6b7280; }
        @media (max-width: 1080px) { .field-grid.two, .field-grid.three, .field-grid.four { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="layout im-aplicacion">
    <aside class="sidebar im-menu-lateral" id="sidebar">
        <div class="sidebar-header">
            <img src="../../assets/institucionales/icons/Isotipo grande.png" alt="Impulsa Emprende" class="sidebar-brand-icon">
            <span class="logo-text">impulsa emprende</span>
        </div>
        <nav class="sidebar-menu im-navegacion"><ul>
            <li onclick="location.href='admin_dashboard.php'"><span class="material-icons" style="color:#6366f1">home</span><span class="link-text">Inicio</span></li>
            <li onclick="location.href='admin_users.php'"><span class="material-icons" style="color:#2563eb">group</span><span class="link-text">Usuarios</span></li>
            <li onclick="location.href='admin_proceso_emprende.php'"><span class="material-icons" style="color:#0f766e">assignment</span><span class="link-text">Proceso emprende</span></li>
            <li class="active" onclick="location.href='admin_newproject.php'"><span class="material-icons" style="color:#f59e0b">rocket_launch</span><span class="link-text">Solicitudes externas</span></li>
            <li onclick="location.href='admin_tareas.php'"><span class="material-icons" style="color:#7c3aed">task_alt</span><span class="link-text">Tareas</span></li>
            <?php require __DIR__ . '/../../partials/marketing_submenu.php'; ?>
            <li onclick="location.href='../../logout.php'"><span class="material-icons" style="color:red">logout</span><span class="link-text">Salir</span></li>
        </ul></nav>
        <div class="sidebar-footer"><button class="btn-icon im-boton-icono" onclick="toggleSidebar()"><span class="material-icons" id="collapseIcon">chevron_left</span></button></div>
    </aside>
    <div class="main im-contenedor">
        <header class="navbar im-barra-superior">
            <div class="navbar-left"><button class="btn-icon im-boton-icono" onclick="toggleSidebar()"><span class="material-icons">menu</span></button><div class="navbar-title">Proyecto desde solicitud</div></div>
            <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
        </header>
        <section class="content stack im-contenido">
            <?php if ($flash['message'] !== ''): ?>
                <div class="flash <?= $flash['type'] === 'success' ? : 'error' ?>"><?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if (empty($selectedProject)): ?>
                <form method="post" class="stack">
                    <input type="hidden" name="action" value="create_project">
                    <input type="hidden" name="source_type" value="<?= htmlspecialchars($sourceType, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="source_id" value="<?= (int) $sourceId ?>">

                    <div class="section-card">
                        <div class="header-row">
                            <div>
                                <h1>Solicitud #<?= (int) ($sourceRequest['id'] ?? 0) ?></h1>
                                <p class="muted" style="margin-top:8px;">Los proyectos solo se crean desde una solicitud seleccionada.</p>
                            </div>
                            <span class="badge planned im-chip">Nuevo proyecto</span>
                        </div>
                        <div class="meta-grid">
                            <div class="meta-card"><span class="label">Solicitante</span><span class="value"><?= htmlspecialchars((string) ($sourceRequest['nombre'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="meta-card"><span class="label">Correo</span><span class="value"><?= htmlspecialchars((string) ($sourceRequest['correo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="meta-card"><span class="label">Whatsapp</span><span class="value"><?= htmlspecialchars((string) ($sourceRequest['whatsapp'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="meta-card"><span class="label">Proyecto solicitado</span><span class="value"><?= htmlspecialchars((string) ($sourceRequest['nombre_proyecto'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                        </div>
                        <div class="field-grid four" style="margin-top:16px;">
                            <div class="field"><label for="project_name">Nombre del proyecto</label><input id="project_name" name="project_name" value="<?= htmlspecialchars($defaultProjectName, ENT_QUOTES, 'UTF-8') ?>"></div>
                            <div class="field"><label for="project_type">Tipo</label><select id="project_type" name="project_type"><option value="software" <?= $defaultProjectType === 'software' ? 'selected' : '' ?>>Software</option><option value="landing_page" <?= $defaultProjectType === 'landing_page' ? 'selected' : '' ?>>Landing page</option><option value="website" <?= $defaultProjectType === 'website' ? 'selected' : '' ?>>Sitio web</option><option value="manual" <?= $defaultProjectType === 'manual' ? 'selected' : '' ?>>Manual</option></select></div>
                            <div class="field"><label for="start_date">Fecha de inicio</label><input id="start_date" type="date" name="start_date"></div>
                            <div class="field"><label for="target_delivery_date">Fecha de entrega</label><input id="target_delivery_date" type="date" name="target_delivery_date" readonly></div>
                        </div>
                        <div class="field-grid three" style="margin-top:14px;">
                            <div class="field"><label for="client_name">Nombre</label><input id="client_name" name="client_name" value="<?= htmlspecialchars($defaultClientName, ENT_QUOTES, 'UTF-8') ?>"></div>
                            <div class="field"><label for="client_email">Correo</label><input id="client_email" type="email" name="client_email" value="<?= htmlspecialchars($defaultClientEmail, ENT_QUOTES, 'UTF-8') ?>"></div>
                            <div class="field"><label for="client_whatsapp">Whatsapp</label><input id="client_whatsapp" name="client_whatsapp" value="<?= htmlspecialchars($defaultClientWhatsapp, ENT_QUOTES, 'UTF-8') ?>"></div>
                        </div>
                        <div class="field-grid two" style="margin-top:14px;">
                            <div class="field"><label for="summary">Resumen</label><textarea id="summary" name="summary"><?= htmlspecialchars($defaultSummary, ENT_QUOTES, 'UTF-8') ?></textarea></div>
                            <div class="field"><label for="scope_summary">Alcance</label><textarea id="scope_summary" name="scope_summary"><?= htmlspecialchars($defaultScope, ENT_QUOTES, 'UTF-8') ?></textarea></div>
                        </div>
                        <div class="client-user-panel" data-client-user-panel>
                            <label class="checkbox-row">
                                <input type="hidden" name="generate_client_user" value="no">
                                <input type="checkbox" name="generate_client_user" value="yes" data-client-user-toggle>
                                <span>Generar usuario cliente con este correo</span>
                            </label>
                            <div class="field client-user-password">
                                <label for="client_password">Contrasena inicial</label>
                                <input id="client_password" type="password" name="client_password" minlength="8" autocomplete="new-password" placeholder="Minimo 8 caracteres">
                                <p class="muted" style="margin:6px 0 0;">El usuario sera el correo del cliente. Si el correo ya existe como cliente, se vincula sin cambiar su contrasena.</p>
                            </div>
                        </div>
                        <input type="hidden" name="status" value="planned">
                        <input type="hidden" name="priority" value="medium">
                        <input type="hidden" name="initial_update_visible" value="1">
                        <input type="hidden" name="initial_update_title" value="Proyecto creado desde solicitud">
                        <input type="hidden" name="initial_update_message" value="Se creo el proyecto y se cargo su plan inicial.">
                    </div>

                    <div class="section-card">
                        <div class="header-row" style="margin-bottom:14px;">
                            <div>
                                <h2>Plan del proyecto</h2>
                                <p class="muted" style="margin-top:8px;">Cada fase contiene sus propios entregables dentro de la misma tarjeta.</p>
                            </div>
                        </div>
                        <div class="field" style="margin-bottom:14px;">
                            <label for="user_page_param">Parametro de pagina para metricas del cliente</label>
                            <input id="user_page_param" name="user_page_param" value="<?= htmlspecialchars($defaultUserPageParam, ENT_QUOTES, 'UTF-8') ?>" placeholder="Valor exacto de page, por ejemplo: impulsagroup.com/cliente">
                            <p class="muted" style="margin:6px 0 0;">Debe coincidir exactamente con la columna page de visit_user_page y forms_clients_contact.</p>
                        </div>
                        <div class="phase-list" id="phase-rows"></div>
                        <div class="btn-row" style="margin-top:14px;"><button class="btn btn-aceptar im-boton im-boton--principal" type="button" id="add-phase-row">Agregar fase</button><button class="btn btn-aceptar im-boton im-boton--principal" type="submit">Crear proyecto</button></div>
                    </div>
                </form>
            <?php else: ?>
                <div class="section-card">
                    <form method="post" class="stack">
                        <input type="hidden" name="action" value="update_project">
                        <input type="hidden" name="project_id" value="<?= (int) ($selectedProject['id'] ?? 0) ?>">
                        <input type="hidden" name="source_type" value="<?= htmlspecialchars($sourceType, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="source_id" value="<?= (int) $sourceId ?>">
                        <div class="header-row">
                            <div class="title-stack">
                                <h1><?= htmlspecialchars((string) ($selectedProject['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
                                <div class="project-title-meta">
                                    <span class="badge <?= $hasContract ? 'contract-pending' : 'contract-none' ?> im-chip">
                                        <?= $hasContract ? 'Con contrato v' . max(1, $contractVersion) : 'Sin contrato' ?>
                                    </span>
                                    <span class="badge <?= $isContractSigned ? 'contract-signed' : 'contract-pending' ?> im-chip">
                                        <?= $isContractSigned ? 'Firmado' : 'Pendiente de firma' ?>
                                    </span>
                                </div>
                                <p class="muted" style="margin-top:8px;">Proyecto vinculado a la solicitud #<?= (int) ($sourceRequest['id'] ?? 0) ?>.</p>
                                <?php if ($isContractSigned): ?>
                                    <p class="muted" style="margin-top:0;">
                                        Firmado por <?= htmlspecialchars($contractSigner !== '' ? $contractSigner : 'cliente', ENT_QUOTES, 'UTF-8') ?>
                                        <?= $contractSignedAt !== '' ? ' el ' . htmlspecialchars(date('d/m/Y H:i', strtotime($contractSignedAt)), ENT_QUOTES, 'UTF-8') : '' ?>.
                                    </p>
                                <?php endif; ?>
                            </div>
                            <span class="badge <?= htmlspecialchars((string) ($selectedProject['status'] ?? 'planned'), ENT_QUOTES, 'UTF-8') ?> im-chip"><?= htmlspecialchars($projectStatusLabels[(string) ($selectedProject['status'] ?? 'planned')] ?? (string) ($selectedProject['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="meta-grid">
                            <div class="meta-card"><span class="label">Solicitante</span><span class="value"><?= htmlspecialchars((string) ($sourceRequest['nombre'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="meta-card"><span class="label">Correo</span><span class="value"><?= htmlspecialchars((string) ($sourceRequest['correo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="meta-card"><span class="label">Whatsapp</span><span class="value"><?= htmlspecialchars((string) ($sourceRequest['whatsapp'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></div>
                            <div class="meta-card"><span class="label">Avance</span><span class="value"><?= (int) ($selectedProject['progress_percent'] ?? 0) ?>%</span></div>
                        </div>
                        <div class="field-grid four">
                            <div class="field"><label for="edit_project_name">Nombre del proyecto</label><input id="edit_project_name" name="project_name" value="<?= htmlspecialchars($defaultProjectName, ENT_QUOTES, 'UTF-8') ?>"></div>
                            <div class="field"><label for="edit_project_type">Tipo</label><select id="edit_project_type" name="project_type"><option value="software" <?= $defaultProjectType === 'software' ? 'selected' : '' ?>>Software</option><option value="landing_page" <?= $defaultProjectType === 'landing_page' ? 'selected' : '' ?>>Landing page</option><option value="website" <?= $defaultProjectType === 'website' ? 'selected' : '' ?>>Sitio web</option><option value="manual" <?= $defaultProjectType === 'manual' ? 'selected' : '' ?>>Manual</option></select></div>
                            <div class="field"><label for="edit_start_date">Fecha de inicio</label><input id="edit_start_date" type="date" name="start_date" value="<?= htmlspecialchars((string) ($selectedProject['start_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                            <div class="field"><label for="edit_target_delivery_date">Fecha de entrega</label><input id="edit_target_delivery_date" type="date" name="target_delivery_date" value="<?= htmlspecialchars((string) ($selectedProject['target_delivery_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" readonly></div>
                        </div>
                        <div class="field-grid three">
                            <div class="field"><label for="edit_client_name">Nombre</label><input id="edit_client_name" name="client_name" value="<?= htmlspecialchars($defaultClientName, ENT_QUOTES, 'UTF-8') ?>"></div>
                            <div class="field"><label for="edit_client_email">Correo</label><input id="edit_client_email" type="email" name="client_email" value="<?= htmlspecialchars($defaultClientEmail, ENT_QUOTES, 'UTF-8') ?>"></div>
                            <div class="field"><label for="edit_client_whatsapp">Whatsapp</label><input id="edit_client_whatsapp" name="client_whatsapp" value="<?= htmlspecialchars($defaultClientWhatsapp, ENT_QUOTES, 'UTF-8') ?>"></div>
                        </div>
                        <div class="field-grid two">
                            <div class="field"><label for="edit_summary">Resumen</label><textarea id="edit_summary" name="summary"><?= htmlspecialchars($defaultSummary, ENT_QUOTES, 'UTF-8') ?></textarea></div>
                            <div class="field"><label for="edit_scope_summary">Alcance</label><textarea id="edit_scope_summary" name="scope_summary"><?= htmlspecialchars($defaultScope, ENT_QUOTES, 'UTF-8') ?></textarea></div>
                        </div>
                        <div class="client-user-panel <?= !empty($selectedProject['client_user_id']) ? 'is-enabled' : '' ?>" data-client-user-panel>
                            <label class="checkbox-row">
                                <input type="hidden" name="generate_client_user" value="no">
                                <input type="checkbox" name="generate_client_user" value="yes" data-client-user-toggle <?= !empty($selectedProject['client_user_id']) ? 'checked' : '' ?>>
                                <span>Generar o vincular usuario cliente con este correo</span>
                            </label>
                            <div class="field client-user-password">
                                <label for="edit_client_password">Contrasena inicial</label>
                                <input id="edit_client_password" type="password" name="client_password" minlength="8" autocomplete="new-password" placeholder="Minimo 8 caracteres">
                                <p class="muted" style="margin:6px 0 0;">El usuario sera el correo del cliente. Si el correo ya existe como cliente, se vincula sin cambiar su contrasena.</p>
                            </div>
                        </div>
                        <input type="hidden" name="status" value="<?= htmlspecialchars((string) ($selectedProject['status'] ?? 'planned'), ENT_QUOTES, 'UTF-8') ?>">
                        <div class="btn-row">
                            <button class="btn btn-aceptar im-boton im-boton--principal" type="submit">Guardar datos</button>
                            <button class="btn btn-aceptar im-boton im-boton--principal" type="button" id="open-contract-modal">Anadir contrato</button>
                        </div>
                    </form>
                </div>

                <form method="post" class="section-card stack">
                    <input type="hidden" name="action" value="update_project_plan">
                    <input type="hidden" name="project_id" value="<?= (int) ($selectedProject['id'] ?? 0) ?>">
                    <input type="hidden" name="source_type" value="<?= htmlspecialchars($sourceType, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="source_id" value="<?= (int) $sourceId ?>">
                    <div class="header-row">
                        <div>
                            <h2>Plan del proyecto</h2>
                            <p class="muted" style="margin-top:8px;">Edita fases y entregables en el mismo bloque.</p>
                        </div>
                    </div>
                    <div class="field">
                        <label for="edit_user_page_param">Parametro de pagina para metricas del cliente</label>
                        <input id="edit_user_page_param" name="user_page_param" value="<?= htmlspecialchars($defaultUserPageParam, ENT_QUOTES, 'UTF-8') ?>" placeholder="Valor exacto de page, por ejemplo: impulsagroup.com/cliente">
                        <p class="muted" style="margin:6px 0 0;">Debe coincidir exactamente con la columna page de visit_user_page y forms_clients_contact.</p>
                    </div>
                    <div class="phase-list" id="phase-rows"></div>
                    <div class="btn-row"><button class="btn btn-aceptar im-boton im-boton--principal" type="button" id="add-phase-row">Agregar fase</button><button class="btn btn-aceptar im-boton im-boton--principal" type="submit">Guardar plan</button></div>
                </form>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php $perfilObligatorio = false; ?>
<?php require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>

<?php if (!empty($selectedProject)): ?>
<div class="modal-shell" id="contract-modal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="contract-modal-title">
        <div class="modal-header">
            <div>
                <h2 id="contract-modal-title"><?= $hasContract ? 'Editar contrato' : 'Nuevo contrato' ?></h2>
                <p class="muted" style="margin:8px 0 0;">El contrato puede editarse hasta que el cliente lo firme desde su perfil.</p>
            </div>
            <button type="button" class="modal-close" id="close-contract-modal" aria-label="Cerrar modal">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="modal-body">
            <form method="post" id="contract-form" class="stack">
                <input type="hidden" name="action" value="save_project_contract">
                <input type="hidden" name="project_id" value="<?= (int) ($selectedProject['id'] ?? 0) ?>">
                <input type="hidden" name="source_type" value="<?= htmlspecialchars($sourceType, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="source_id" value="<?= (int) $sourceId ?>">
                <input type="hidden" name="contract_html" id="contract_html">
                <input type="hidden" name="contract_text" id="contract_text">

                <div class="field">
                    <label for="contract_name">Nombre</label>
                    <input id="contract_name" name="contract_name" value="<?= htmlspecialchars($defaultContractName, ENT_QUOTES, 'UTF-8') ?>" <?= $isContractSigned ? 'readonly' : '' ?> required>
                </div>

                <div class="field">
                    <label for="contract-editor">Contrato</label>
                    <div class="quill-shell">
                        <div id="contract-editor"><?= $defaultContractHtml ?></div>
                    </div>
                </div>

                <p class="contract-note">
                    <?php if ($isContractSigned): ?>
                        Este contrato ya fue firmado. Queda visible en modo lectura y no admite cambios.
                    <?php else: ?>
                        Usa el editor para redactar el contrato. Cuando el cliente lo firme, quedara bloqueado para futuras ediciones.
                    <?php endif; ?>
                </p>

                <div class="btn-row">
                    <button class="btn btn-aceptar im-boton im-boton--principal" type="submit" <?= $isContractSigned ? 'disabled' : '' ?>>Guardar contrato</button>
                    <button class="btn im-boton" type="button" id="cancel-contract-modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<template id="phase-row-template">
    <div class="phase-card" data-phase-row>
        <div class="header-row">
            <span class="phase-title">Fase</span>
            <button type="button" class="remove-btn" data-remove-phase aria-label="Eliminar fase"><span class="material-icons">delete</span></button>
        </div>
        <div class="field-grid four">
            <div class="field"><label>Titulo</label><input name="phase_title[]" required></div>
            <div class="field"><label>Descripcion</label><textarea name="phase_description[]"></textarea></div>
            <div class="field"><label>Duracion</label><input type="number" min="1" name="phase_duration_days[]"></div>
            <div class="field"><label>Fecha finalizacion</label><input type="date" name="phase_due_date[]" readonly></div>
        </div>
        <div>
            <div class="header-row" style="margin-bottom:10px;">
                <strong>Entregables</strong>
                <button type="button" class="btn btn-aceptar im-boton im-boton--principal" data-add-deliverable>Agregar entregable</button>
            </div>
            <div class="deliverable-list" data-deliverable-list></div>
        </div>
    </div>
</template>

<template id="deliverable-row-template">
    <div class="deliverable-card" data-deliverable-row>
        <div class="header-row">
            <strong>Entregable</strong>
            <button type="button" class="remove-btn" data-remove-deliverable aria-label="Eliminar entregable"><span class="material-icons">delete</span></button>
        </div>
        <div class="field-grid two">
            <div class="field"><label>Titulo</label><input data-deliverable-title required></div>
            <div class="field"><label>Descripcion</label><textarea data-deliverable-description></textarea></div>
        </div>
        <label class="checkbox-row">
            <input type="hidden" value="0" data-deliverable-completed-hidden>
            <input type="checkbox" data-deliverable-completed>
            <span>Completado</span>
        </label>
    </div>
</template>

<script>
(function () {
    document.querySelectorAll('[data-client-user-panel]').forEach((panel) => {
        const toggle = panel.querySelector('[data-client-user-toggle]');
        if (!toggle) {
            return;
        }

        const refresh = () => {
            panel.classList.toggle('is-enabled', toggle.checked);
        };

        toggle.addEventListener('change', refresh);
        refresh();
    });
})();

(function () {
    const phaseRows = document.getElementById('phase-rows');
    const phaseTemplate = document.getElementById('phase-row-template');
    const deliverableTemplate = document.getElementById('deliverable-row-template');
    const addPhaseButton = document.getElementById('add-phase-row');
    const initialPhases = <?= json_encode($phasePlans, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    if (!phaseRows || !phaseTemplate || !deliverableTemplate || !addPhaseButton) {
        return;
    }

    function refreshPhaseLabels() {
        phaseRows.querySelectorAll('[data-phase-row]').forEach((row, phaseIndex) => {
            const title = row.querySelector('.phase-title');
            if (title) title.textContent = 'Fase ' + (phaseIndex + 1);
            row.querySelectorAll('[data-deliverable-row]').forEach((deliverableRow, deliverableIndex) => {
                const titleInput = deliverableRow.querySelector('[data-deliverable-title]');
                const descriptionInput = deliverableRow.querySelector('[data-deliverable-description]');
                const completedHiddenInput = deliverableRow.querySelector('[data-deliverable-completed-hidden]');
                if (titleInput) titleInput.name = 'deliverable_title[' + phaseIndex + '][]';
                if (descriptionInput) descriptionInput.name = 'deliverable_description[' + phaseIndex + '][]';
                if (completedHiddenInput) completedHiddenInput.name = 'deliverable_completed[' + phaseIndex + '][' + deliverableIndex + ']';
            });
        });
        refreshComputedDates();
    }

    function moveToBusinessDay(date) {
        const result = new Date(date.getTime());
        const day = result.getDay();
        if (day === 6) result.setDate(result.getDate() + 2);
        if (day === 0) result.setDate(result.getDate() + 1);
        return result;
    }

    function addBusinessDays(startDate, days) {
        let current = moveToBusinessDay(startDate);
        if (days <= 1) return current;
        let remaining = days - 1;
        while (remaining > 0) {
            current.setDate(current.getDate() + 1);
            current = moveToBusinessDay(current);
            remaining--;
        }
        return current;
    }

    function toInputDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    function refreshComputedDates() {
        const startInput = document.getElementById('edit_start_date') || document.getElementById('start_date');
        const targetInput = document.getElementById('edit_target_delivery_date') || document.getElementById('target_delivery_date');
        if (!startInput || !targetInput || !startInput.value) {
            phaseRows.querySelectorAll('input[name="phase_due_date[]"]').forEach((input) => { input.value = ''; });
            if (targetInput) targetInput.value = '';
            return;
        }

        let cursor = moveToBusinessDay(new Date(startInput.value + 'T00:00:00'));
        let lastDueDate = '';

        phaseRows.querySelectorAll('[data-phase-row]').forEach((row) => {
            const durationInput = row.querySelector('input[name="phase_duration_days[]"]');
            const dueDateInput = row.querySelector('input[name="phase_due_date[]"]');
            const durationDays = Math.max(0, parseInt(durationInput && durationInput.value ? durationInput.value : '0', 10) || 0);

            if (durationDays <= 0) {
                dueDateInput.value = '';
                return;
            }

            const phaseEnd = addBusinessDays(cursor, durationDays);
            const dueDate = toInputDate(phaseEnd);
            dueDateInput.value = dueDate;
            lastDueDate = dueDate;
            phaseEnd.setDate(phaseEnd.getDate() + 1);
            cursor = moveToBusinessDay(phaseEnd);
        });

        targetInput.value = lastDueDate;
    }

    function createDeliverableRow(list, values) {
        const row = deliverableTemplate.content.firstElementChild.cloneNode(true);
        const completedHiddenInput = row.querySelector('[data-deliverable-completed-hidden]');
        const completedInput = row.querySelector('[data-deliverable-completed]');
        row.querySelector('[data-deliverable-title]').value = values && values.title ? values.title : '';
        row.querySelector('[data-deliverable-description]').value = values && values.description ? values.description : '';
        if (completedInput) {
            completedInput.checked = values && values.completed === '1';
            completedInput.addEventListener('change', function () {
                if (completedHiddenInput) {
                    completedHiddenInput.value = completedInput.checked ? '1' : '0';
                }
            });
        }
        if (completedHiddenInput) {
            completedHiddenInput.value = values && values.completed === '1' ? '1' : '0';
        }
        row.querySelector('[data-remove-deliverable]').addEventListener('click', function () {
            row.remove();
            refreshPhaseLabels();
        });
        list.appendChild(row);
        refreshPhaseLabels();
    }

    function createPhaseRow(values) {
        const row = phaseTemplate.content.firstElementChild.cloneNode(true);
        const list = row.querySelector('[data-deliverable-list]');
        row.querySelector('input[name="phase_title[]"]').value = values && values.title ? values.title : '';
        row.querySelector('textarea[name="phase_description[]"]').value = values && values.description ? values.description : '';
        row.querySelector('input[name="phase_duration_days[]"]').value = values && values.duration_days ? values.duration_days : '';
        row.querySelector('input[name="phase_due_date[]"]').value = values && values.due_date ? values.due_date : '';
        row.querySelector('input[name="phase_duration_days[]"]').addEventListener('input', refreshComputedDates);
        row.querySelector('[data-add-deliverable]').addEventListener('click', function () {
            createDeliverableRow(list);
        });
        row.querySelector('[data-remove-phase]').addEventListener('click', function () {
            if (phaseRows.children.length <= 1) return;
            row.remove();
            refreshPhaseLabels();
        });
        phaseRows.appendChild(row);
        const deliverables = values && Array.isArray(values.deliverables) && values.deliverables.length ? values.deliverables : [];
        deliverables.forEach(function (deliverable) { createDeliverableRow(list, deliverable); });
        refreshPhaseLabels();
    }

    addPhaseButton.addEventListener('click', function () {
        createPhaseRow({ title: '', description: '', duration_days: '', due_date: '', deliverables: [] });
    });

    const scheduleStartInput = document.getElementById('edit_start_date') || document.getElementById('start_date');
    if (scheduleStartInput) {
        scheduleStartInput.addEventListener('change', refreshComputedDates);
    }

    if (Array.isArray(initialPhases) && initialPhases.length) {
        initialPhases.forEach(function (phase) { createPhaseRow(phase); });
    } else {
        createPhaseRow({ title: 'Fase 1', description: '', duration_days: '', due_date: '', deliverables: [{ title: '', description: '', completed: '0' }] });
    }
    refreshComputedDates();
})();

(function () {
    const modal = document.getElementById('contract-modal');
    const openButton = document.getElementById('open-contract-modal');
    const closeButton = document.getElementById('close-contract-modal');
    const cancelButton = document.getElementById('cancel-contract-modal');
    const form = document.getElementById('contract-form');
    const htmlInput = document.getElementById('contract_html');
    const textInput = document.getElementById('contract_text');
    const editorNode = document.getElementById('contract-editor');
    const isSigned = <?= $isContractSigned ? 'true' : 'false' ?>;

    if (!modal || !openButton || !closeButton || !cancelButton || !form || !htmlInput || !textInput || !editorNode || typeof Quill === 'undefined') {
        return;
    }

    const quill = new Quill(editorNode, {
        theme: 'snow',
        readOnly: isSigned,
        modules: {
            toolbar: isSigned ? false : [
                [{ header: [1, 2, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'blockquote'],
                [{ align: [] }],
                ['clean']
            ]
        }
    });

    const openModal = () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    openButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });

    form.addEventListener('submit', (event) => {
        const plainText = quill.getText().trim();
        const editor = editorNode.querySelector('.ql-editor');
        htmlInput.value = editor ? editor.innerHTML : '';
        textInput.value = plainText;

        if (!isSigned && plainText === '') {
            event.preventDefault();
            alert('Completa el contenido del contrato antes de guardarlo.');
        }
    });
})();
</script>
</body>
</html>
