<?php
require_once __DIR__ . '/../../controllers/clientes_dashboardController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Cliente';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');
$avatarUrl = obtenerAvatarUrl($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null));
$avatarInitial = obtenerInicialAvatar($displayName);
$projectContract = $selectedProject['contract'] ?? [];
$hasContract = !empty($projectContract['id']);
$isContractSigned = !empty($projectContract['is_signed']);
$contractName = (string) ($projectContract['contract_name'] ?? 'Contrato del proyecto');
$contractHtml = (string) ($projectContract['contract_html'] ?? '');
$contractVersion = (int) ($projectContract['version_number'] ?? 0);
$contractSignedAt = (string) ($projectContract['signed_at'] ?? '');
$contractSigner = (string) ($projectContract['signer_full_name'] ?? '');

function escapeDetail(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$statusLabels = [
    'draft' => 'Borrador',
    'planned' => 'Planificado',
    'in_progress' => 'En progreso',
    'paused' => 'Pausado',
    'in_review' => 'En revision',
    'completed' => 'Completado',
    'cancelled' => 'Cancelado',
    'pending' => 'Pendiente',
    'blocked' => 'Bloqueada',
    'done' => 'Completada',
    'ready_for_review' => 'Listo para revision',
    'delivered' => 'Entregado',
];

$deliverablesByPhase = [];
foreach (($selectedProject['deliverables'] ?? []) as $deliverable) {
    $phaseId = (int) ($deliverable['phase_id'] ?? 0);
    if (!isset($deliverablesByPhase[$phaseId])) {
        $deliverablesByPhase[$phaseId] = [];
    }
    $deliverablesByPhase[$phaseId][] = $deliverable;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impulsa - Dashboard cliente</title>
    <?php $impulsaMaterialAssetBase = '../..'; require __DIR__ . '/../../partials/impulsa_material_assets.php'; ?>
    <style>
        .navbar { justify-content: space-between; }
        .navbar-left { display:flex; align-items:center; gap:8px; }
        .sidebar-brand-icon { width:32px; height:32px; object-fit:contain; flex-shrink:0; }
        .sidebar-menu li { justify-content:flex-start; }
        .sidebar-menu li .material-icons { width:24px; min-width:24px; text-align:center; }
        .sidebar-menu li .link-text { flex:0 0 auto; margin-left:0; }
        .hero-card { display:grid; gap:10px; }
        .hero-card h1, .section-card h2, .section-card h3 { margin:0; color:var(--heading-color); }
        .hero-card p, .muted { color:var(--text-secondary); }
        .summary-grid, .phase-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; }
        .summary-card, .section-card { border:1px solid var(--border-color); border-radius:18px; padding:18px; background:var(--card-bg); box-shadow:var(--shadow-soft); }
        .summary-card .value { font-size:28px; font-weight:700; color:var(--heading-color); }
        .summary-card .label { color:var(--text-muted); font-size:13px; text-transform:uppercase; letter-spacing:.04em; }
        .page-stack, .phase-list, .deliverable-list { display:grid; gap:16px; }
        .flash { margin-bottom:16px; padding:12px 14px; border-radius:12px; font-size:14px; }
        .flash.success { background:#dcfce7; color:#166534; border:1px solid #86efac; }
        .flash.error { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
        .badge { display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700; }
        .badge.in_progress { background:var(--primary-soft); color:var(--primary-color); }
        .badge.planned, .badge.pending { background:var(--info-bg); color:var(--info-color); }
        .badge.in_review, .badge.ready_for_review { background:var(--warning-bg); color:var(--warning-color); }
        .badge.completed, .badge.done, .badge.delivered { background:var(--success-bg); color:var(--success-color); }
        .badge.paused, .badge.blocked, .badge.cancelled { background:var(--danger-bg); color:var(--danger-color); }
        .badge.contract-none { background:#e5e7eb; color:#374151; }
        .badge.contract-pending { background:#fef3c7; color:#92400e; }
        .badge.contract-signed { background:#dcfce7; color:#166534; }
        .progress-track { height:10px; background:var(--bg-muted); border-radius:999px; overflow:hidden; margin-top:10px; }
        .progress-fill { height:100%; background:linear-gradient(90deg, var(--primary-color), var(--primary-strong)); }
        .phase-grid { align-items:start; }
        .phase-card { display:grid; gap:14px; border:1px solid var(--border-color); border-left:4px solid var(--primary-color); border-radius:16px; padding:18px; background:linear-gradient(180deg, var(--card-bg-strong) 0%, var(--card-bg) 100%); box-shadow:0 10px 24px rgba(15, 23, 42, 0.08); }
        .phase-head, .deliverable-row { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap; }
        .phase-title-row, .deliverable-title-row { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .phase-meta, .deliverable-meta { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        .mini-meta { display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-size:12px; background:var(--primary-soft); color:var(--primary-color); }
        .deliverable-row { padding:12px 14px; border:1px solid var(--border-color); border-radius:14px; background:var(--bg-elevated); }
        .deliverable-title { font-weight:700; color:var(--heading-color); }
        .subtle { color:var(--text-secondary); font-size:13px; }
        .empty { color:var(--text-muted); text-align:center; padding:24px 0; }
        .detail-trigger { width:28px; height:28px; border-radius:999px; border:1px solid var(--border-color); background:var(--card-bg); color:var(--primary-color); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
        .detail-trigger .material-icons { font-size:18px; }
        .detail-trigger:hover { transform:translateY(-1px); border-color:var(--primary-color); box-shadow:0 8px 18px rgba(37, 99, 235, 0.16); }
        .detail-modal-backdrop { position:fixed; inset:0; background:rgba(15, 23, 42, 0.54); display:none; align-items:center; justify-content:center; padding:20px; z-index:1200; }
        .detail-modal-backdrop.is-open { display:flex; }
        .detail-modal { width:min(100%, 560px); background:var(--card-bg); border:1px solid var(--border-color); border-radius:22px; box-shadow:0 30px 60px rgba(15, 23, 42, 0.26); overflow:hidden; }
        .detail-modal-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:20px 22px 12px; border-bottom:1px solid var(--border-color); }
        .detail-modal-title { margin:0; color:var(--heading-color); font-size:22px; }
        .detail-modal-subtitle { margin:6px 0 0; color:var(--text-secondary); font-size:14px; }
        .detail-modal-close { border:0; background:transparent; color:var(--text-secondary); cursor:pointer; padding:2px; }
        .detail-modal-body { padding:20px 22px 24px; }
        .detail-modal-description { margin:0; color:var(--text-secondary); line-height:1.7; white-space:pre-line; }
        .contract-card { display:grid; gap:14px; }
        .contract-head { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap; }
        .contract-meta { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        .contract-copy { color:var(--text-secondary); line-height:1.6; }
        .contract-actions { display:flex; gap:10px; flex-wrap:wrap; }
        .contract-modal-backdrop { position:fixed; inset:0; background:rgba(15, 23, 42, 0.58); display:none; align-items:center; justify-content:center; padding:20px; z-index:1300; }
        .contract-modal-backdrop.is-open { display:flex; }
        .contract-modal { width:min(100%, 860px); max-height:calc(100vh - 40px); overflow:auto; background:var(--card-bg); border:1px solid var(--border-color); border-radius:22px; box-shadow:0 30px 60px rgba(15, 23, 42, 0.28); }
        .contract-modal-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:20px 22px 12px; border-bottom:1px solid var(--border-color); }
        .contract-modal-title { margin:0; color:var(--heading-color); font-size:24px; }
        .contract-modal-subtitle { margin:6px 0 0; color:var(--text-secondary); font-size:14px; }
        .contract-modal-close { border:0; background:transparent; color:var(--text-secondary); cursor:pointer; padding:2px; }
        .contract-modal-body { padding:20px 22px 24px; display:grid; gap:18px; }
        .contract-viewer { border:1px solid var(--border-color); border-radius:16px; padding:18px; background:var(--bg-elevated); color:var(--text-secondary); line-height:1.7; }
        .contract-viewer h1, .contract-viewer h2, .contract-viewer h3, .contract-viewer h4 { color:var(--heading-color); }
        .contract-sign-box { border:1px solid var(--border-color); border-radius:16px; padding:16px; background:var(--card-bg-strong); display:grid; gap:14px; }
        .contract-check { display:flex; gap:10px; align-items:flex-start; color:var(--text-secondary); }
        .contract-check input { margin-top:4px; width:18px; height:18px; accent-color:var(--primary-color); }
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
            <li class="active" onclick="location.href='clientes_dashboard.php'"><span class="material-icons">home</span><span class="link-text">Mi proyecto</span></li>
            <li onclick="location.href='clientes_metricas.php'"><span class="material-icons" style="color:#0f766e">query_stats</span><span class="link-text">Metricas</span></li>
            <li onclick="location.href='../marketing/marketing_user.php'"><span class="material-icons" style="color:#0f766e">campaign</span><span class="link-text">Marketing</span></li>
            <li onclick="location.href='../../logout.php?redirect=https%3A%2F%2Fimpulsagroup.com%2F'"><span class="material-icons" style="color:red">logout</span><span class="link-text">Salir</span></li>
        </ul></nav>
        <div class="sidebar-footer"><button class="btn-icon im-boton-icono" onclick="toggleSidebar()"><span class="material-icons" id="collapseIcon">chevron_left</span></button></div>
    </aside>
    <div class="main im-contenedor">
        <header class="navbar im-barra-superior">
            <div class="navbar-left"><button class="btn-icon im-boton-icono" onclick="toggleSidebar()"><span class="material-icons">menu</span></button><div class="navbar-title">Dashboard del cliente</div></div>
            <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
        </header>
        <section class="content im-contenido">
            <?php if (($flash['message'] ?? '') !== ''): ?>
                <div class="flash <?= ($flash['type'] ?? '') === 'success' ? : 'error' ?>"><?= htmlspecialchars((string) $flash['message'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <div class="section-card hero-card" style="margin-bottom:16px;">
                <h1>Hola, <?= $displayName ?></h1>
                <p>Desde aca podes seguir el estado general de tu proyecto y ver el plan resumido por fases.</p>
            </div>

            <?php if (!empty($selectedProject)): ?>
                <div class="summary-grid" style="margin-bottom:16px;">
                    <div class="summary-card"><div class="label">Proyecto</div><div class="value" style="font-size:20px;"><?= htmlspecialchars((string) ($selectedProject['project_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div></div>
                    <div class="summary-card"><div class="label">Estado</div><div class="value" style="font-size:20px;"><?= htmlspecialchars($statusLabels[(string) ($selectedProject['status'] ?? 'planned')] ?? (string) ($selectedProject['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div></div>
                    <div class="summary-card"><div class="label">Avance</div><div class="value"><?= (int) ($selectedProject['progress_percent'] ?? 0) ?>%</div><div class="progress-track"><div class="progress-fill" style="width: <?= max(0, min(100, (int) ($selectedProject['progress_percent'] ?? 0))) ?>%;"></div></div></div>
                    <div class="summary-card"><div class="label">Entrega estimada</div><div class="value" style="font-size:20px;"><?= !empty($selectedProject['target_delivery_date']) ? htmlspecialchars(date('d/m/Y', strtotime((string) $selectedProject['target_delivery_date'])), ENT_QUOTES, 'UTF-8') : '-' ?></div></div>
                </div>
            <?php endif; ?>

            <div class="page-stack">
                <?php if (!empty($selectedProject)): ?>
                    <?php if ($hasContract): ?>
                        <div class="section-card contract-card">
                            <div class="contract-head">
                                <div>
                                    <h2>Contrato</h2>
                                    <p class="muted" style="margin-top:8px;">Lee el contrato de tu proyecto y firmalo desde este panel cuando estes de acuerdo.</p>
                                </div>
                                <div class="contract-meta">
                                    <span class="badge contract-pending im-chip">
                                        Version <?= max(1, $contractVersion) ?>
                                    </span>
                                    <span class="badge <?= $isContractSigned ? 'contract-signed' : 'contract-pending' ?> im-chip">
                                        <?= $isContractSigned ? 'Firmado' : 'Pendiente de firma' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="contract-copy">
                                <strong style="color:var(--heading-color);"><?= htmlspecialchars($contractName, ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if ($isContractSigned): ?>
                                    <div style="margin-top:8px;">
                                        Firmado por <?= htmlspecialchars($contractSigner !== '' ? $contractSigner : $displayName, ENT_QUOTES, 'UTF-8') ?>
                                        <?= $contractSignedAt !== '' ? ' el ' . htmlspecialchars(date('d/m/Y H:i', strtotime($contractSignedAt)), ENT_QUOTES, 'UTF-8') : '' ?>.
                                    </div>
                                <?php else: ?>
                                    <div style="margin-top:8px;">Todavia no fue firmado. Puedes revisarlo completo antes de aceptar.</div>
                                <?php endif; ?>
                            </div>
                            <div class="contract-actions">
                                <button type="button" class="btn btn-aceptar im-boton im-boton--principal" id="openContractModal">Leer contrato</button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="section-card">
                        <h2>Fases y entregables</h2>
                        <p class="muted" style="margin-top:8px;">Vista resumida del plan de trabajo actual.</p>
                        <div class="phase-grid" style="margin-top:16px;">
                            <?php foreach (($selectedProject['phases'] ?? []) as $phase): ?>
                                <div class="phase-card">
                                    <div class="phase-head">
                                        <div>
                                            <div class="phase-title-row">
                                                <strong style="font-size:18px;"><?= htmlspecialchars((string) ($phase['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                                <button
                                                    type="button"
                                                    class="detail-trigger"
                                                    data-detail-title="<?= escapeDetail((string) ($phase['title'] ?? 'Detalle de la fase')) ?>"
                                                    data-detail-type="Fase"
                                                    data-detail-description="<?= escapeDetail(trim((string) ($phase['description'] ?? ''))) ?>"
                                                    aria-label="Ver detalle de la fase"
                                                >
                                                    <span class="material-icons">help_outline</span>
                                                </button>
                                            </div>
                                            <div class="phase-meta" style="margin-top:8px;">
                                                <span class="mini-meta">Entrega <?= !empty($phase['due_date']) ? htmlspecialchars(date('d/m/Y', strtotime((string) $phase['due_date'])), ENT_QUOTES, 'UTF-8') : '-' ?></span>
                                            </div>
                                        </div>
                                        <span class="badge <?= htmlspecialchars((string) ($phase['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?> im-chip"><?= htmlspecialchars($statusLabels[(string) ($phase['status'] ?? 'pending')] ?? (string) ($phase['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <div class="deliverable-list">
                                        <?php $phaseDeliverables = $deliverablesByPhase[(int) ($phase['id'] ?? 0)] ?? []; ?>
                                        <?php if (!empty($phaseDeliverables)): ?>
                                            <?php foreach ($phaseDeliverables as $deliverable): ?>
                                                <div class="deliverable-row">
                                                    <div>
                                                        <div class="deliverable-title-row">
                                                            <div class="deliverable-title"><?= htmlspecialchars((string) ($deliverable['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                                            <button
                                                                type="button"
                                                                class="detail-trigger"
                                                                data-detail-title="<?= escapeDetail((string) ($deliverable['title'] ?? 'Detalle del entregable')) ?>"
                                                                data-detail-type="Entregable"
                                                                data-detail-description="<?= escapeDetail(trim((string) ($deliverable['description'] ?? ''))) ?>"
                                                                aria-label="Ver detalle del entregable"
                                                            >
                                                                <span class="material-icons">help_outline</span>
                                                            </button>
                                                        </div>
                                                        <div class="deliverable-meta" style="margin-top:8px;">
                                                            <span class="subtle">Entrega <?= !empty($deliverable['due_date']) ? htmlspecialchars(date('d/m/Y', strtotime((string) ($deliverable['due_date'] ?? ''))), ENT_QUOTES, 'UTF-8') : (!empty($phase['due_date']) ? htmlspecialchars(date('d/m/Y', strtotime((string) $phase['due_date'])), ENT_QUOTES, 'UTF-8') : '-') ?></span>
                                                        </div>
                                                    </div>
                                                    <span class="badge <?= htmlspecialchars((string) ($deliverable['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?> im-chip"><?= htmlspecialchars($statusLabels[(string) ($deliverable['status'] ?? 'pending')] ?? (string) ($deliverable['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="empty">No hay entregables cargados en esta fase.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="section-card"><div class="empty">No hay un proyecto activo para mostrar.</div></div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
<div class="detail-modal-backdrop" id="detailModalBackdrop" aria-hidden="true">
    <div class="detail-modal" role="dialog" aria-modal="true" aria-labelledby="detailModalTitle">
        <div class="detail-modal-header">
            <div>
                <h3 class="detail-modal-title" id="detailModalTitle">Detalle</h3>
                <p class="detail-modal-subtitle" id="detailModalType">Informacion</p>
            </div>
            <button type="button" class="detail-modal-close" id="detailModalClose" aria-label="Cerrar detalle">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="detail-modal-body">
            <p class="detail-modal-description" id="detailModalDescription">Sin descripcion disponible.</p>
        </div>
    </div>
</div>
<?php if ($hasContract): ?>
<div class="contract-modal-backdrop" id="contractModalBackdrop" aria-hidden="true">
    <div class="contract-modal" role="dialog" aria-modal="true" aria-labelledby="contractModalTitle">
        <div class="contract-modal-header">
            <div>
                <h3 class="contract-modal-title" id="contractModalTitle"><?= htmlspecialchars($contractName, ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="contract-modal-subtitle">
                    <?= $isContractSigned ? 'Contrato firmado' : 'Contrato pendiente de firma' ?>
                </p>
            </div>
            <button type="button" class="contract-modal-close" id="contractModalClose" aria-label="Cerrar contrato">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="contract-modal-body">
            <div class="contract-viewer">
                <?= $contractHtml !== '' ? $contractHtml : '<p>No hay contenido disponible para este contrato.</p>' ?>
            </div>
            <?php if (!$isContractSigned): ?>
                <form method="post" class="contract-sign-box">
                    <input type="hidden" name="action" value="sign_project_contract">
                    <input type="hidden" name="project_id" value="<?= (int) ($selectedProject['id'] ?? 0) ?>">
                    <label class="contract-check">
                        <input type="checkbox" name="accept_contract" value="1" required>
                        <span>Confirmo que lei el contrato completo, comprendo sus condiciones y acepto firmarlo electronicamente.</span>
                    </label>
                    <div class="contract-actions">
                        <button type="submit" class="btn btn-aceptar im-boton im-boton--principal">Firmar contrato</button>
                        <button type="button" class="btn im-boton" id="contractCancelButton">Cerrar</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="contract-sign-box">
                    <div class="contract-copy">
                        Este contrato ya fue firmado por <?= htmlspecialchars($contractSigner !== '' ? $contractSigner : $displayName, ENT_QUOTES, 'UTF-8') ?>
                        <?= $contractSignedAt !== '' ? ' el ' . htmlspecialchars(date('d/m/Y H:i', strtotime($contractSignedAt)), ENT_QUOTES, 'UTF-8') : '' ?>.
                    </div>
                    <div class="contract-actions">
                        <button type="button" class="btn im-boton" id="contractCancelButton">Cerrar</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $perfilObligatorio = false; ?>
<?php require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalBackdrop = document.getElementById('detailModalBackdrop');
        const modalClose = document.getElementById('detailModalClose');
        const modalTitle = document.getElementById('detailModalTitle');
        const modalType = document.getElementById('detailModalType');
        const modalDescription = document.getElementById('detailModalDescription');
        const detailTriggers = document.querySelectorAll('.detail-trigger');

        if (!modalBackdrop || !modalClose || !modalTitle || !modalType || !modalDescription) {
            return;
        }

        const closeDetailModal = () => {
            modalBackdrop.classList.remove('is-open');
            modalBackdrop.setAttribute('aria-hidden', 'true');
        };

        const openDetailModal = (button) => {
            const title = button.dataset.detailTitle || 'Detalle';
            const type = button.dataset.detailType || 'Informacion';
            const description = (button.dataset.detailDescription || '').trim();

            modalTitle.textContent = title;
            modalType.textContent = type;
            modalDescription.textContent = description !== ''
                ? description
                : 'Todavia no hay una descripcion cargada para este elemento.';

            modalBackdrop.classList.add('is-open');
            modalBackdrop.setAttribute('aria-hidden', 'false');
        };

        detailTriggers.forEach((button) => {
            button.addEventListener('click', () => openDetailModal(button));
        });

        modalClose.addEventListener('click', closeDetailModal);
        modalBackdrop.addEventListener('click', (event) => {
            if (event.target === modalBackdrop) {
                closeDetailModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modalBackdrop.classList.contains('is-open')) {
                closeDetailModal();
            }
        });

        const contractModalBackdrop = document.getElementById('contractModalBackdrop');
        const contractOpenButton = document.getElementById('openContractModal');
        const contractCloseButton = document.getElementById('contractModalClose');
        const contractCancelButton = document.getElementById('contractCancelButton');

        if (contractModalBackdrop && contractOpenButton && contractCloseButton) {
            const closeContractModal = () => {
                contractModalBackdrop.classList.remove('is-open');
                contractModalBackdrop.setAttribute('aria-hidden', 'true');
            };

            const openContractModal = () => {
                contractModalBackdrop.classList.add('is-open');
                contractModalBackdrop.setAttribute('aria-hidden', 'false');
            };

            contractOpenButton.addEventListener('click', openContractModal);
            contractCloseButton.addEventListener('click', closeContractModal);
            if (contractCancelButton) {
                contractCancelButton.addEventListener('click', closeContractModal);
            }

            contractModalBackdrop.addEventListener('click', (event) => {
                if (event.target === contractModalBackdrop) {
                    closeContractModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && contractModalBackdrop.classList.contains('is-open')) {
                    closeContractModal();
                }
            });
        }
    });
</script>
</body>
</html>
