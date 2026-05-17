<?php
require_once __DIR__ . '/../../controllers/clientes_metricasController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Cliente';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');

function metricasEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function metricasFecha($value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return '-';
    }

    return date('d/m/Y H:i', strtotime($value . ' -3 hours'));
}

function metricasNombreMes(string $label): string
{
    $parts = explode('/', $label);
    $month = (int) ($parts[0] ?? 0);
    $year = (string) ($parts[1] ?? '');
    $months = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    return trim(($months[$month] ?? $label) . ' ' . $year);
}

$kpisOrdenados = array_reverse($kpisVisitas);
$estadoLabels = [
    'recibido' => 'Recibido',
    'cancelado' => 'Cancelado',
    'aprobado' => 'Aprobado',
];
$detalleContactos = [];
foreach ($contactos as $contacto) {
    $detalleContactos[(int) ($contacto['id'] ?? 0)] = [
        'Nombre' => (string) ($contacto['contact_nombre'] ?? '-'),
        'Whatsapp' => (string) ($contacto['contact_whatsapp'] ?? '-'),
        'Email' => (string) ($contacto['contact_email'] ?? '-'),
        'Descripcion' => (string) ($contacto['contact_description'] ?? '-'),
        'Consulta completa' => (string) ($contacto['contact_consultation'] ?? '-'),
        'Estado' => $estadoLabels[(string) ($contacto['state'] ?? '')] ?? (string) ($contacto['state'] ?? '-'),
        'Page' => (string) ($contacto['page'] ?? '-'),
        'Fecha' => metricasFecha($contacto['created_at'] ?? ''),
        'Actualizado' => metricasFecha($contacto['updated_at'] ?? ''),
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impulsa - Metricas</title>
    <?php $impulsaMaterialAssetBase = '../..'; require __DIR__ . '/../../partials/impulsa_material_assets.php'; ?>
    <style>
        .navbar { justify-content: space-between; }
        .navbar-left { display:flex; align-items:center; gap:8px; }
        .sidebar-brand-icon { width:32px; height:32px; object-fit:contain; flex-shrink:0; }
        .sidebar-menu li { justify-content:flex-start; }
        .sidebar-menu li .material-icons { width:24px; min-width:24px; text-align:center; }
        .sidebar-menu li .link-text { flex:0 0 auto; margin-left:0; }
        .page-stack { display:grid; gap:16px; }
        .section-card { border:1px solid var(--border-color); border-radius:18px; padding:18px; background:var(--card-bg); box-shadow:var(--shadow-soft); }
        .hero-card { display:grid; gap:8px; }
        .hero-card h1, .section-card h2 { margin:0; color:var(--heading-color); }
        .muted { color:var(--text-secondary); }
        .kpi-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:14px; margin-top:16px; }
        .kpi-card { border:1px solid var(--border-color); border-radius:16px; padding:16px; background:linear-gradient(180deg, var(--card-bg-strong) 0%, var(--card-bg) 100%); }
        .kpi-label { color:var(--text-muted); font-size:13px; text-transform:uppercase; letter-spacing:.04em; }
        .kpi-value { margin-top:8px; color:var(--heading-color); font-size:34px; font-weight:800; }
        .kpi-help { margin-top:4px; color:var(--text-secondary); font-size:13px; }
        .table-wrap { overflow-x:auto; margin-top:14px; }
        .metrics-table { width:100%; border-collapse:collapse; }
        .metrics-table th { text-align:left; font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.04em; border-bottom:1px solid var(--border-color); padding:10px 12px; white-space:nowrap; }
        .metrics-table td { padding:12px; border-bottom:1px solid var(--border-color); color:var(--text-secondary); vertical-align:top; }
        .metrics-table tr:last-child td { border-bottom:0; }
        .empty { color:var(--text-muted); text-align:center; padding:24px 0; }
        .badge { display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700; background:var(--primary-soft); color:var(--primary-color); }
        .state-select { min-width:130px; border:1px solid var(--border-color); border-radius:10px; padding:8px 10px; background:var(--card-bg); color:var(--text-secondary); font:inherit; }
        .action-icon-btn { width:34px; height:34px; border:1px solid var(--border-color); border-radius:10px; background:var(--card-bg); color:var(--primary-color); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
        .action-icon-btn:hover { background:var(--bg-muted); }
        .detail-modal-backdrop { position:fixed; inset:0; background:rgba(15, 23, 42, 0.55); display:none; align-items:center; justify-content:center; padding:18px; z-index:1200; }
        .detail-modal-backdrop.is-open { display:flex; }
        .detail-modal { width:min(100%, 760px); max-height:90vh; overflow:auto; background:var(--card-bg); border:1px solid var(--border-color); border-radius:18px; box-shadow:0 24px 55px rgba(15, 23, 42, 0.24); padding:20px; }
        .detail-modal-header { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:14px; }
        .detail-modal-header h3 { margin:0; color:var(--heading-color); font-size:22px; }
        .detail-modal-close { width:34px; height:34px; border:1px solid var(--border-color); border-radius:10px; background:var(--card-bg); color:var(--text-secondary); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
        .detail-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:10px; }
        .detail-item { border:1px solid var(--border-color); border-radius:12px; padding:10px 12px; background:var(--bg-elevated); }
        .detail-item-label { margin:0 0 6px; font-size:11px; color:var(--text-muted); font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
        .detail-item-value { margin:0; font-size:14px; color:var(--text-secondary); line-height:1.6; white-space:pre-line; word-break:break-word; }
        @media (max-width: 820px) {
            .content { padding:16px 12px; }
            .page-stack { gap:12px; }
            .section-card { padding:14px; border-radius:16px; }
            .hero-card h1, .section-card h2 { font-size:22px; }
            .hero-card .muted { display:grid; gap:8px; line-height:1.5; }
            .badge { width:fit-content; max-width:100%; white-space:normal; word-break:break-word; }
            .kpi-grid { grid-template-columns:1fr; gap:10px; }
            .kpi-card { padding:14px; }
            .kpi-value { font-size:30px; }
            .table-wrap { overflow:visible; }
            .metrics-table,
            .metrics-table thead,
            .metrics-table tbody,
            .metrics-table th,
            .metrics-table td,
            .metrics-table tr { display:block; width:100%; }
            .metrics-table thead { display:none; }
            .metrics-table tr {
                border:1px solid var(--border-color);
                border-radius:16px;
                padding:12px;
                margin-bottom:12px;
                background:var(--bg-elevated);
            }
            .metrics-table td {
                border-bottom:1px solid var(--border-color);
                padding:10px 0;
                display:grid;
                grid-template-columns:105px minmax(0, 1fr);
                gap:10px;
                align-items:start;
                word-break:break-word;
            }
            .metrics-table td:last-child { border-bottom:0; }
            .metrics-table td::before {
                content:attr(data-label);
                color:var(--text-muted);
                font-size:11px;
                font-weight:700;
                text-transform:uppercase;
                letter-spacing:.04em;
            }
            .state-select { width:100%; min-width:0; }
            .action-icon-btn { justify-self:start; }
            .detail-modal-backdrop { padding:10px; align-items:flex-end; }
            .detail-modal {
                width:100%;
                max-height:88vh;
                border-radius:18px 18px 0 0;
                padding:16px;
            }
            .detail-grid { grid-template-columns:1fr; }
        }

        @media (max-width: 420px) {
            .content { padding:12px 10px; }
            .navbar-title { font-size:17px; }
            .metrics-table td { grid-template-columns:88px minmax(0, 1fr); }
            .detail-modal-header h3 { font-size:20px; }
        }
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
            <li onclick="location.href='clientes_dashboard.php'"><span class="material-icons">home</span><span class="link-text">Mi proyecto</span></li>
            <li class="active" onclick="location.href='clientes_metricas.php'"><span class="material-icons" style="color:#0f766e">query_stats</span><span class="link-text">Metricas</span></li>
            <li onclick="location.href='../marketing/marketing_user.php'"><span class="material-icons" style="color:#0f766e">campaign</span><span class="link-text">Marketing</span></li>
            <li onclick="location.href='../../logout.php?redirect=https%3A%2F%2Fimpulsagroup.com%2F'"><span class="material-icons" style="color:red">logout</span><span class="link-text">Salir</span></li>
        </ul></nav>
        <div class="sidebar-footer"><button class="btn-icon im-boton-icono" onclick="toggleSidebar()"><span class="material-icons" id="collapseIcon">chevron_left</span></button></div>
    </aside>
    <div class="main im-contenedor">
        <header class="navbar im-barra-superior">
            <div class="navbar-left"><button class="btn-icon im-boton-icono" onclick="toggleSidebar()"><span class="material-icons">menu</span></button><div class="navbar-title">Metricas</div></div>
            <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
        </header>
        <section class="content page-stack im-contenido">
            <div class="section-card hero-card">
                <h1>Metricas de tu pagina</h1>
                <p class="muted">
                    Hola, <?= $displayName ?>. En esta página, vamos a mostrarte las visitas que tuviste en tu página web y además, las consultas enviadas desde el formulario. 
                    <?php if ($pageParam !== null): ?>
                        <span class="badge im-chip"><?= metricasEscape($pageParam) ?></span>
                    <?php endif; ?>
                </p>
            </div>

            <?php if ($pageParam === null): ?>
                <div class="section-card">
                    <div class="empty">Todavia no hay un parametro de pagina configurado para tu usuario.</div>
                </div>
            <?php else: ?>
                <div class="section-card">
                    <h2>Visitas mensuales</h2>
                    <p class="muted" style="margin-top:8px;">Comparativo de visitas de los ultimos tres meses.</p>
                    <div class="kpi-grid">
                        <?php foreach ($kpisOrdenados as $index => $kpi): ?>
                            <div class="kpi-card">
                                <div class="kpi-label">
                                    <?= $index === 0 ? 'Hace dos meses' : ($index === 1 ? 'Mes anterior' : 'Mes actual') ?>
                                    - <?= metricasEscape(metricasNombreMes((string) $kpi['label'])) ?>
                                </div>
                                <div class="kpi-value"><?= (int) $kpi['value'] ?></div>
                                <div class="kpi-help">Visitas registradas</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="section-card">
                    <h2>Consultas recibidas</h2>
                    <p class="muted" style="margin-top:8px;">Listado de las consultas realizadas en tu página web.</p>
                    <div class="table-wrap im-tabla-contenedor">
                        <table class="metrics-table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Whatsapp</th>
                                    <th>Email</th>
                                    <th>Consulta</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($contactos)): ?>
                                    <?php foreach ($contactos as $contacto): ?>
                                        <tr>
                                            <td data-label="Nombre"><?= metricasEscape($contacto['contact_nombre'] ?? '') ?></td>
                                            <td data-label="Whatsapp"><?= metricasEscape($contacto['contact_whatsapp'] ?? '-') ?></td>
                                            <td data-label="Email"><?= metricasEscape($contacto['contact_email'] ?? '-') ?></td>
                                            <td data-label="Consulta">
                                                <?= metricasEscape($contacto['contact_description'] ?? '-') ?>
                                            </td>
                                            <td data-label="Estado">
                                                <form method="post">
                                                    <input type="hidden" name="action" value="update_contact_state">
                                                    <input type="hidden" name="contact_id" value="<?= (int) ($contacto['id'] ?? 0) ?>">
                                                    <select class="state-select" name="state" onchange="this.form.submit()">
                                                        <?php foreach ($estadoLabels as $stateValue => $stateLabel): ?>
                                                            <option value="<?= metricasEscape($stateValue) ?>" <?= (string) ($contacto['state'] ?? '') === $stateValue ? 'selected' : '' ?>>
                                                                <?= metricasEscape($stateLabel) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                            <td data-label="Fecha"><?= metricasEscape(metricasFecha($contacto['created_at'] ?? '')) ?></td>
                                            <td data-label="Detalle">
                                                <button type="button" class="action-icon-btn" data-open-contact-detail data-contact-id="<?= (int) ($contacto['id'] ?? 0) ?>" title="Ver detalle completo">
                                                    <span class="material-icons" style="font-size:18px">visibility</span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7"><div class="empty">Todavia no hay consultas para esta pagina.</div></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
<div class="detail-modal-backdrop" id="contact-detail-modal-backdrop" aria-hidden="true">
    <div class="detail-modal" role="dialog" aria-modal="true" aria-labelledby="contact-detail-title">
        <div class="detail-modal-header">
            <h3 id="contact-detail-title">Detalle del contacto</h3>
            <button type="button" class="detail-modal-close" id="contact-detail-close" aria-label="Cerrar detalle">
                <span class="material-icons">close</span>
            </button>
        </div>
        <div class="detail-grid" id="contact-detail-grid"></div>
    </div>
</div>
<?php $perfilObligatorio = false; ?>
<?php require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>
<script>
    const contactDetails = <?= json_encode($detalleContactos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const detailButtons = document.querySelectorAll('[data-open-contact-detail]');
    const detailBackdrop = document.getElementById('contact-detail-modal-backdrop');
    const detailClose = document.getElementById('contact-detail-close');
    const detailGrid = document.getElementById('contact-detail-grid');

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function openContactDetail(id) {
        const detail = contactDetails[String(id)] || contactDetails[id];
        if (!detail || !detailBackdrop || !detailGrid) return;

        detailGrid.innerHTML = Object.entries(detail).map(([label, value]) => `
            <div class="detail-item">
                <p class="detail-item-label">${escapeHtml(label)}</p>
                <p class="detail-item-value">${escapeHtml(value || '-')}</p>
            </div>
        `).join('');

        detailBackdrop.classList.add('is-open');
        detailBackdrop.setAttribute('aria-hidden', 'false');
    }

    function closeContactDetail() {
        if (!detailBackdrop) return;
        detailBackdrop.classList.remove('is-open');
        detailBackdrop.setAttribute('aria-hidden', 'true');
    }

    detailButtons.forEach((button) => {
        button.addEventListener('click', () => openContactDetail(button.dataset.contactId || ''));
    });

    if (detailClose) detailClose.addEventListener('click', closeContactDetail);
    if (detailBackdrop) {
        detailBackdrop.addEventListener('click', (event) => {
            if (event.target === detailBackdrop) closeContactDetail();
        });
    }
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && detailBackdrop && detailBackdrop.classList.contains('is-open')) {
            closeContactDetail();
        }
    });
</script>
</body>
</html>
