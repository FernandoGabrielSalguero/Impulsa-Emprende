<?php
require_once __DIR__ . '/../../controllers/admin_usersController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Admin';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');
$avatarUrl = obtenerAvatarUrl($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null));
$avatarInitial = obtenerInicialAvatar($displayName);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Impulsa - Usuarios</title>

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
            max-width: 860px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .stat-card {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            padding: 18px;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .stat-icon.total { background: #eef2ff; color: #4338ca; }
        .stat-icon.filtered { background: #dcfce7; color: #15803d; }
        .stat-label {
            margin: 0 0 4px;
            font-size: 13px;
            color: #6b7280;
        }
        .stat-value {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 14px;
        }
        .table-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .table-header-copy {
            display: grid;
            gap: 4px;
        }
        .table-header-copy p {
            margin: 0;
            color: #6b7280;
            font-size: 13px;
        }
        .filters-inline {
            flex: 1 1 520px;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            flex: 1 1 480px;
        }
        .filter-hint {
            margin: 10px 0 0;
            color: #6b7280;
            font-size: 12px;
        }
        .table-wrap {
            overflow-x: auto;
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
        .badge-admin { background: #e0e7ff; color: #3730a3; }
        .badge-emprendedor { background: #dcfce7; color: #15803d; }
        .badge-cliente { background: #fef3c7; color: #b45309; }
        .badge-verified { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef3c7; color: #b45309; }
        .muted {
            color: #6b7280;
            font-size: 13px;
        }
        .actions-cell {
            width: 72px;
            position: relative;
        }
        .actions-wrap {
            position: relative;
            display: inline-block;
        }
        .actions-btn {
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #374151;
            border-radius: 10px;
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .actions-btn:hover {
            background: #f9fafb;
        }
        .actions-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 180px;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.10);
            display: none;
            z-index: 30;
        }
        .actions-menu.is-open {
            display: block;
        }
        .actions-empty {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }
        .action-item {
            width: 100%;
            border: 0;
            background: transparent;
            color: #111827;
            text-align: left;
            padding: 8px 10px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
        }
        .action-item:hover {
            background: #f3f4f6;
        }
        .flash-message {
            display: none;
            margin: 0 0 14px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }
        .flash-message.is-visible {
            display: block;
        }
        .flash-message.is-success {
            background: #dcfce7;
            color: #166534;
        }
        .flash-message.is-error {
            background: #fee2e2;
            color: #b91c1c;
        }
        .row-loading td {
            color: #9ca3af;
            text-align: center;
            padding: 24px;
        }
        @media (max-width: 768px) {
            .table-header {
                flex-direction: column;
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
                    <li class="active" onclick="location.href='admin_users.php'">
                        <span class="material-icons" style="color:#2563eb">group</span>
                        <span class="link-text">Usuarios</span>
                    </li>
                    <li onclick="location.href='admin_proceso_emprende.php'">
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
                    <div class="navbar-title">Usuarios</div>
                </div>
                <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
            </header>

            <section class="content">
                <div class="card">
                    <div class="hero-card">
                        <h1>Directorio de usuarios</h1>
                        <p><?= $displayName ?>, aca podes consultar todos los usuarios registrados, filtrar por nombre o correo y revisar su informacion principal. Esta vista no incluye el proceso de landing.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon total">
                                <span class="material-icons">groups</span>
                            </div>
                            <div>
                                <p class="stat-label">Total de usuarios</p>
                                <p class="stat-value"><?= number_format($totalUsuarios, 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon filtered">
                                <span class="material-icons">filter_alt</span>
                            </div>
                            <div>
                                <p class="stat-label"><?= $hayFiltrosActivos ? 'Resultados filtrados' : 'Usuarios visibles' ?></p>
                                <p class="stat-value"><?= number_format($totalFiltrados, 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="table-header">
                        <div class="table-header-copy">
                            <p class="section-title" style="margin:0">Listado de usuarios</p>
                            <p>Filtra por correo o por nombre, apellido o apodo. La busqueda se aplica automaticamente desde el tercer caracter.</p>
                        </div>
                        <div class="filters-inline">
                            <div class="filters-grid">
                                <div class="input-group">
                                    <label for="filtro-nombre">Nombre</label>
                                    <div class="input-icon input-icon-name">
                                        <input type="text" id="filtro-nombre" name="nombre" value="<?= htmlspecialchars($filtros['nombre'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Nombre, apellido o apodo" autocomplete="off">
                                    </div>
                                </div>
                                <div class="input-group">
                                    <label for="filtro-correo">Correo</label>
                                    <div class="input-icon input-icon-email">
                                        <input type="text" id="filtro-correo" name="correo" value="<?= htmlspecialchars($filtros['correo'], ENT_QUOTES, 'UTF-8') ?>" placeholder="Correo electronico" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <p class="filter-hint">Si borras los filtros, vuelve automaticamente el listado completo.</p>
                        </div>
                    </div>
                    <div class="flash-message" id="users-flash"></div>
                    <div class="table-wrap">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Verificado</th>
                                    <th>Whatsapp</th>
                                    <th>Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body">
                                <?php if (!empty($usuariosFiltrados)): ?>
                                    <?php foreach ($usuariosFiltrados as $usuario): ?>
                                        <?php
                                        $nombreCompleto = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''));
                                        $label = $usuario['apodo'] ?: ($nombreCompleto ?: ($usuario['correo'] ?? 'Sin nombre'));
                                        $inicial = mb_strtoupper(mb_substr($label, 0, 1));
                                        $rolUsuario = (string) ($usuario['rol'] ?? '');
                                        $rolLabel = 'Cliente';
                                        $rolBadgeClass = 'badge-cliente';
                                        if ($rolUsuario === 'impulsa_administrador') {
                                            $rolLabel = 'Administrador';
                                            $rolBadgeClass = 'badge-admin';
                                        } elseif ($rolUsuario === 'impulsa_emprendedor') {
                                            $rolLabel = 'Emprendedor';
                                            $rolBadgeClass = 'badge-emprendedor';
                                        }
                                        $rowAvatarUrl = obtenerAvatarUrl($usuario['avatar_path'] ?? null);
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="user-pill">
                                                    <div class="user-initials"><?php if ($rowAvatarUrl): ?><img src="<?= htmlspecialchars($rowAvatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar del usuario"><?php else: ?><?= htmlspecialchars($inicial, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></div>
                                                    <div>
                                                        <div><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></div>
                                                        <div class="muted">ID #<?= (int) ($usuario['usuario_id'] ?? 0) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($usuario['correo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <span class="badge <?= $rolBadgeClass ?>">
                                                    <?= htmlspecialchars($rolLabel, ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($usuario['email_verified_at'])): ?>
                                                    <span class="badge badge-verified">
                                                        <span class="material-icons" style="font-size:14px">verified</span>
                                                        Verificado
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-pending">
                                                        <span class="material-icons" style="font-size:14px">warning</span>
                                                        Pendiente
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars((string) ($usuario['whatsapp'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td class="muted">
                                                <?= !empty($usuario['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime((string) $usuario['created_at'])), ENT_QUOTES, 'UTF-8') : '-' ?>
                                            </td>
                                            <td class="actions-cell">
                                                <div class="actions-wrap">
                                                    <button class="actions-btn" type="button" data-actions-toggle aria-label="Abrir acciones">
                                                        <span class="material-icons">more_vert</span>
                                                    </button>
                                                    <div class="actions-menu" data-actions-menu>
                                                        <button class="action-item" type="button" data-resend-verification data-user-id="<?= (int) ($usuario['usuario_id'] ?? 0) ?>" data-user-email="<?= htmlspecialchars((string) ($usuario['correo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                            Reenviar correo de verificacion
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center;color:#9ca3af;padding:24px">
                                            No se encontraron usuarios con los filtros actuales.
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

    <script>
        const statValues = document.querySelectorAll('.stat-card .stat-value');
        const statLabels = document.querySelectorAll('.stat-card .stat-label');
        const totalUsuariosValue = statValues[0];
        const totalFiltradosValue = statValues[1];
        const totalFiltradosLabel = statLabels[1];
        const filtroNombre = document.getElementById('filtro-nombre');
        const filtroCorreo = document.getElementById('filtro-correo');
        const tableBody = document.getElementById('users-table-body');
        const flash = document.getElementById('users-flash');

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatDate(value) {
            if (!value) {
                return '-';
            }

            const date = new Date(String(value).replace(' ', 'T'));
            if (Number.isNaN(date.getTime())) {
                return escapeHtml(value);
            }

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${day}/${month}/${year} ${hours}:${minutes}`;
        }

        function buildActionsCell(user) {
            return `
                <div class="actions-wrap">
                    <button class="actions-btn" type="button" data-actions-toggle aria-label="Abrir acciones">
                        <span class="material-icons">more_vert</span>
                    </button>
                    <div class="actions-menu" data-actions-menu>
                        <button class="action-item" type="button" data-resend-verification data-user-id="${escapeHtml(user.usuario_id)}" data-user-email="${escapeHtml(user.correo)}">
                            Reenviar correo de verificacion
                        </button>
                    </div>
                </div>
            `;
        }

        function buildUserAvatar(user, initial) {
            if (user.avatar_path) {
                const src = String(user.avatar_path).startsWith('/') ? user.avatar_path : `/${String(user.avatar_path).replace(/^\/+/, '')}`;
                return `<img src="${escapeHtml(src)}" alt="Avatar del usuario">`;
            }

            return escapeHtml(initial);
        }

        function renderRows(users) {
            if (!Array.isArray(users) || users.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align:center;color:#9ca3af;padding:24px">
                            No se encontraron usuarios con los filtros actuales.
                        </td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = users.map((user) => {
                const label = user.apodo || `${user.nombre ?? ''} ${user.apellido ?? ''}`.trim() || user.correo || 'Sin nombre';
                const initial = (label.charAt(0) || '?').toUpperCase();
                const isAdmin = user.rol === 'impulsa_administrador';
                const isEmprendedor = user.rol === 'impulsa_emprendedor';
                const roleClass = isAdmin ? 'badge-admin' : (isEmprendedor ? 'badge-emprendedor' : 'badge-cliente');
                const roleLabel = isAdmin ? 'Administrador' : (isEmprendedor ? 'Emprendedor' : 'Cliente');

                return `
                    <tr>
                        <td>
                            <div class="user-pill">
                                <div class="user-initials">${buildUserAvatar(user, initial)}</div>
                                <div>
                                    <div>${escapeHtml(label)}</div>
                                    <div class="muted">ID #${escapeHtml(user.usuario_id ?? '')}</div>
                                </div>
                            </div>
                        </td>
                        <td>${escapeHtml(user.correo ?? '')}</td>
                        <td>
                            <span class="badge ${roleClass}">
                                ${roleLabel}
                            </span>
                        </td>
                        <td>
                            ${
                                user.email_verified_at
                                    ? '<span class="badge badge-verified"><span class="material-icons" style="font-size:14px">verified</span>Verificado</span>'
                                    : '<span class="badge badge-pending"><span class="material-icons" style="font-size:14px">warning</span>Pendiente</span>'
                            }
                        </td>
                        <td>${escapeHtml(user.whatsapp || '-')}</td>
                        <td class="muted">${formatDate(user.created_at)}</td>
                        <td class="actions-cell">${buildActionsCell(user)}</td>
                    </tr>
                `;
            }).join('');
        }

        function showFlash(type, message) {
            flash.className = `flash-message is-visible ${type === 'success' ? 'is-success' : 'is-error'}`;
            flash.textContent = message;
            window.clearTimeout(showFlash._timer);
            showFlash._timer = window.setTimeout(() => {
                flash.className = 'flash-message';
                flash.textContent = '';
            }, 4000);
        }

        let searchTimer = null;
        let activeSearchController = null;

        function normalizedFilterValue(value) {
            const trimmed = value.trim();
            return trimmed.length >= 3 ? trimmed : '';
        }

        async function fetchUsers() {
            const nombre = normalizedFilterValue(filtroNombre.value);
            const correo = normalizedFilterValue(filtroCorreo.value);

            if (activeSearchController) {
                activeSearchController.abort();
            }

            activeSearchController = new AbortController();
            tableBody.innerHTML = `
                <tr class="row-loading">
                    <td colspan="7">Buscando usuarios...</td>
                </tr>
            `;

            const params = new URLSearchParams({ ajax: 'search' });
            if (nombre !== '') {
                params.set('nombre', nombre);
            }
            if (correo !== '') {
                params.set('correo', correo);
            }

            try {
                const response = await fetch(`/controllers/admin_usersController.php?${params.toString()}`, {
                    signal: activeSearchController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json();

                if (!data.ok) {
                    throw new Error(data.error || 'No se pudo cargar el listado de usuarios.');
                }

                totalUsuariosValue.textContent = Number(data.total || 0).toLocaleString('es-AR');
                totalFiltradosValue.textContent = Number(data.filtrados || 0).toLocaleString('es-AR');
                totalFiltradosLabel.textContent = (nombre !== '' || correo !== '') ? 'Resultados filtrados' : 'Usuarios visibles';
                renderRows(data.usuarios || []);
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align:center;color:#b91c1c;padding:24px">
                            No se pudo actualizar la tabla de usuarios.
                        </td>
                    </tr>
                `;
            }
        }

        function scheduleFetch() {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(fetchUsers, 250);
        }

        [filtroNombre, filtroCorreo].forEach((input) => {
            input.addEventListener('input', scheduleFetch);
        });

        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-actions-toggle]');
            const resendButton = event.target.closest('[data-resend-verification]');
            const openMenus = document.querySelectorAll('[data-actions-menu].is-open');

            openMenus.forEach((menu) => {
                if ((!toggle || !menu.parentElement.contains(toggle)) && (!resendButton || !menu.contains(resendButton))) {
                    menu.classList.remove('is-open');
                }
            });

            if (resendButton) {
                const userId = resendButton.getAttribute('data-user-id');
                const userEmail = resendButton.getAttribute('data-user-email') || '';

                resendButton.disabled = true;
                resendButton.textContent = 'Enviando...';

                fetch('/controllers/admin_usersController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new URLSearchParams({
                        action: 'reenviar_verificacion',
                        user_id: userId || '',
                    }),
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (!data.ok) {
                            throw new Error(data.error || 'No se pudo reenviar el correo.');
                        }

                        showFlash('success', `${data.message} ${userEmail ? `Destino: ${userEmail}.` : ''}`);
                    })
                    .catch((error) => {
                        showFlash('error', error.message || 'No se pudo reenviar el correo de verificacion.');
                    })
                    .finally(() => {
                        resendButton.disabled = false;
                        resendButton.textContent = 'Reenviar correo de verificacion';
                    });
                return;
            }

            if (!toggle) {
                return;
            }

            const wrap = toggle.closest('.actions-wrap');
            const menu = wrap ? wrap.querySelector('[data-actions-menu]') : null;
            if (!menu) {
                return;
            }

            menu.classList.toggle('is-open');
        });

        const sesion = {
            user_id: <?= json_encode($_SESSION['user_id'] ?? null) ?>,
            correo: <?= json_encode($_SESSION['correo'] ?? null) ?>,
            rol: <?= json_encode($_SESSION['rol'] ?? null) ?>,
        };
        console.group('[Impulsa] Sesion activa - Admin Users');
        console.table(sesion);
        console.groupEnd();
    </script>
</body>

</html>
