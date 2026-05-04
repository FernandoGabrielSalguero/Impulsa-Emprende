<?php
require_once __DIR__ . '/../../controllers/admin_tareasController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Admin';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');

$nombreUsuario = static function (array $usuario): string {
    $nombre = trim((string) ($usuario['nombre'] ?? '') . ' ' . (string) ($usuario['apellido'] ?? ''));
    $label = (string) ($usuario['apodo'] ?? '');

    if ($label !== '') {
        return $label;
    }

    return $nombre !== '' ? $nombre : (string) ($usuario['correo'] ?? 'Usuario');
};

$defconClass = static fn(int $prioridad): string => 'defcon-' . max(1, min(5, $prioridad));
$tareasPendientes = array_values(array_filter($tareas, static fn(array $tarea): bool => (string) ($tarea['estado'] ?? 'pendiente') !== 'completada'));
$tareasFinalizadas = array_values(array_filter($tareas, static fn(array $tarea): bool => (string) ($tarea['estado'] ?? 'pendiente') === 'completada'));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Impulsa - Tareas</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
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
        .hero-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
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
        .section-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }
        .section-copy {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }
        .task-form {
            display: grid;
            gap: 16px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .field.full {
            grid-column: 1 / -1;
        }
        .field label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
        }
        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 10px 12px;
            color: #111827;
            background: #fff;
            font: inherit;
        }
        .field textarea {
            min-height: 110px;
            resize: vertical;
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .primary-btn {
            border: 0;
            border-radius: 12px;
            background: #4f46e5;
            color: #fff;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 16px;
            font-weight: 700;
            cursor: pointer;
        }
        .primary-btn:hover {
            background: #4338ca;
        }
        .secondary-btn {
            border: 1px solid #d1d5db;
            border-radius: 12px;
            background: #fff;
            color: #374151;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 16px;
            font-weight: 700;
            cursor: pointer;
        }
        .secondary-btn:hover {
            background: #f9fafb;
        }
        .task-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1500;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15, 23, 42, 0.55);
        }
        .task-modal-backdrop.is-open {
            display: flex;
        }
        .task-modal {
            width: min(100%, 860px);
            max-height: calc(100vh - 36px);
            overflow: auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.24);
        }
        .task-modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 22px 24px 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        .task-modal-header h2 {
            margin: 0 0 6px;
        }
        .task-modal-close {
            width: 38px;
            height: 38px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            color: #374151;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .task-modal-close:hover {
            background: #f9fafb;
        }
        .task-modal-body {
            padding: 22px 24px 24px;
        }
        .status-form {
            margin: 0;
        }
        .status-select,
        .priority-select {
            min-width: 142px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 8px 10px;
            color: #111827;
            background: #fff;
            font: inherit;
            font-size: 13px;
            cursor: pointer;
        }
        .priority-select {
            min-width: 190px;
            font-weight: 700;
        }
        .flash-message {
            margin: 0 0 14px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
        }
        .flash-message.success {
            background: #dcfce7;
            color: #166534;
        }
        .flash-message.error {
            background: #fee2e2;
            color: #991b1b;
        }
        .tasks-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }
        .tasks-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 12px;
            border-radius: 999px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }
        .tasks-count.done {
            background: #dcfce7;
            color: #166534;
        }
        .table-wrap {
            overflow-x: auto;
        }
        .table-wrap.modern {
            border: 1px solid #e5e7eb;
            border-radius: 18px;
            background:
                linear-gradient(180deg, rgba(248, 250, 252, 0.94), rgba(255, 255, 255, 1));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }
        .table-wrap.limited-rows {
            max-height: 548px;
            overflow: auto;
        }
        .table-wrap.limited-rows thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #f8fafc;
        }
        .table-wrap.modern::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        .table-wrap.modern::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border: 3px solid #f8fafc;
            border-radius: 999px;
        }
        .tasks-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 920px;
        }
        .tasks-table thead th {
            text-align: left;
            color: #64748b;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 13px 14px;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }
        .tasks-table tbody td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            color: #111827;
            font-size: 14px;
            vertical-align: middle;
            background: rgba(255, 255, 255, 0.72);
        }
        .tasks-table tbody tr {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .tasks-table tbody tr:hover td {
            background: #f8fafc;
        }
        .tasks-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .task-name {
            display: grid;
            gap: 4px;
        }
        .task-name strong {
            color: #111827;
        }
        .task-name span,
        .muted {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }
        .defcon-1 { background: #fee2e2; color: #991b1b; }
        .defcon-2 { background: #ffedd5; color: #9a3412; }
        .defcon-3 { background: #fef3c7; color: #92400e; }
        .defcon-4 { background: #dbeafe; color: #1d4ed8; }
        .defcon-5 { background: #dcfce7; color: #166534; }
        .empty-row {
            text-align: center;
            color: #9ca3af;
            padding: 26px 12px;
        }
        @media (max-width: 820px) {
            .hero-card-header {
                display: grid;
            }
            .tasks-card-header {
                display: grid;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .primary-btn,
            .secondary-btn {
                width: 100%;
                justify-content: center;
            }
            .form-actions {
                display: grid;
                gap: 10px;
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
                    <li onclick="location.href='admin_proceso_emprende.php'">
                        <span class="material-icons" style="color:#0f766e">assignment</span>
                        <span class="link-text">Proceso emprende</span>
                    </li>
                    <li onclick="location.href='admin_newproject.php'">
                        <span class="material-icons" style="color:#f59e0b">rocket_launch</span>
                        <span class="link-text">Solicitudes externas</span>
                    </li>
                    <li class="active" onclick="location.href='admin_tareas.php'">
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
                    <div class="navbar-title">Tareas pendientes</div>
                </div>
                <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
            </header>

            <section class="content">
                <?php if (($flash['message'] ?? '') !== ''): ?>
                    <div class="flash-message <?= htmlspecialchars((string) $flash['type'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars((string) $flash['message'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if ($databaseWarning !== ''): ?>
                    <div class="flash-message error">
                        <?= htmlspecialchars($databaseWarning, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="hero-card">
                        <div class="hero-card-header">
                            <div>
                                <h1>Tareas pendientes</h1>
                            </div>
                            <button class="primary-btn" type="button" id="open-create-task-modal">
                                <span class="material-icons" style="font-size:18px">add_task</span>
                                Crear tarea
                            </button>
                        </div>
                        <p><?= $displayName ?>, desde aca podes crear tareas internas y verlas ordenadas automaticamente por prioridad DEFCON y fecha de entrega.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="tasks-card-header">
                        <div style="display:grid;gap:8px">
                            <h2 class="section-title">Tareas creadas</h2>
                            <p class="section-copy">Se muestran las tareas activas. Las completadas pasan automaticamente a tareas finalizadas.</p>
                        </div>
                        <span class="tasks-count"><?= count($tareasPendientes) ?></span>
                    </div>

                    <div class="table-wrap modern limited-rows">
                        <table class="tasks-table">
                            <thead>
                                <tr>
                                    <th>Tarea</th>
                                    <th>Responsable</th>
                                    <th>Prioridad</th>
                                    <th>Entrega</th>
                                    <th>Reporta a</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tareasPendientes)): ?>
                                    <?php foreach ($tareasPendientes as $tarea): ?>
                                        <?php
                                        $prioridad = (int) ($tarea['prioridad_defcon'] ?? 5);
                                        $responsable = $nombreUsuario([
                                            'nombre' => $tarea['responsable_nombre'] ?? '',
                                            'apellido' => $tarea['responsable_apellido'] ?? '',
                                            'apodo' => $tarea['responsable_apodo'] ?? '',
                                            'correo' => $tarea['responsable_correo'] ?? '',
                                        ]);
                                        $estadoActual = (string) ($tarea['estado'] ?? 'pendiente');
                                        $fechaEntrega = !empty($tarea['fecha_entrega'])
                                            ? date('d/m/Y', strtotime((string) $tarea['fecha_entrega']))
                                            : '-';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="task-name">
                                                    <strong><?= htmlspecialchars((string) ($tarea['nombre_tarea'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                                    <span><?= htmlspecialchars((string) ($tarea['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($responsable, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <form class="status-form" method="post" action="admin_tareas.php">
                                                    <input type="hidden" name="action" value="update_priority">
                                                    <input type="hidden" name="task_id" value="<?= (int) ($tarea['id'] ?? 0) ?>">
                                                    <select class="priority-select <?= htmlspecialchars($defconClass($prioridad), ENT_QUOTES, 'UTF-8') ?>" name="prioridad_defcon" onchange="this.form.submit()">
                                                        <?php foreach ($prioridadesDefcon as $valor => $label): ?>
                                                            <option value="<?= (int) $valor ?>" <?= $prioridad === (int) $valor ? 'selected' : '' ?>>
                                                                DEFCON <?= (int) $valor ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?= htmlspecialchars($fechaEntrega, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string) ($tarea['reporta_a'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <form class="status-form" method="post" action="admin_tareas.php">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="task_id" value="<?= (int) ($tarea['id'] ?? 0) ?>">
                                                    <select class="status-select" name="estado" onchange="this.form.submit()">
                                                        <?php foreach ($estadosTarea as $valor => $label): ?>
                                                            <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>" <?= $estadoActual === $valor ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-row">No hay tareas pendientes.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="tasks-card-header">
                        <div style="display:grid;gap:8px">
                            <h2 class="section-title">Tareas finalizadas</h2>
                            <p class="section-copy">Tareas que tienen estado completada.</p>
                        </div>
                        <span class="tasks-count done"><?= count($tareasFinalizadas) ?></span>
                    </div>

                    <div class="table-wrap modern">
                        <table class="tasks-table">
                            <thead>
                                <tr>
                                    <th>Tarea</th>
                                    <th>Responsable</th>
                                    <th>Prioridad</th>
                                    <th>Entrega</th>
                                    <th>Reporta a</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tareasFinalizadas)): ?>
                                    <?php foreach ($tareasFinalizadas as $tarea): ?>
                                        <?php
                                        $prioridad = (int) ($tarea['prioridad_defcon'] ?? 5);
                                        $responsable = $nombreUsuario([
                                            'nombre' => $tarea['responsable_nombre'] ?? '',
                                            'apellido' => $tarea['responsable_apellido'] ?? '',
                                            'apodo' => $tarea['responsable_apodo'] ?? '',
                                            'correo' => $tarea['responsable_correo'] ?? '',
                                        ]);
                                        $estadoActual = (string) ($tarea['estado'] ?? 'pendiente');
                                        $fechaEntrega = !empty($tarea['fecha_entrega'])
                                            ? date('d/m/Y', strtotime((string) $tarea['fecha_entrega']))
                                            : '-';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="task-name">
                                                    <strong><?= htmlspecialchars((string) ($tarea['nombre_tarea'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                                    <span><?= htmlspecialchars((string) ($tarea['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($responsable, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <form class="status-form" method="post" action="admin_tareas.php">
                                                    <input type="hidden" name="action" value="update_priority">
                                                    <input type="hidden" name="task_id" value="<?= (int) ($tarea['id'] ?? 0) ?>">
                                                    <select class="priority-select <?= htmlspecialchars($defconClass($prioridad), ENT_QUOTES, 'UTF-8') ?>" name="prioridad_defcon" onchange="this.form.submit()">
                                                        <?php foreach ($prioridadesDefcon as $valor => $label): ?>
                                                            <option value="<?= (int) $valor ?>" <?= $prioridad === (int) $valor ? 'selected' : '' ?>>
                                                                DEFCON <?= (int) $valor ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?= htmlspecialchars($fechaEntrega, ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string) ($tarea['reporta_a'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td>
                                                <form class="status-form" method="post" action="admin_tareas.php">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="task_id" value="<?= (int) ($tarea['id'] ?? 0) ?>">
                                                    <select class="status-select" name="estado" onchange="this.form.submit()">
                                                        <?php foreach ($estadosTarea as $valor => $label): ?>
                                                            <option value="<?= htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') ?>" <?= $estadoActual === $valor ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-row">Todavia no hay tareas finalizadas.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="task-modal-backdrop" id="create-task-modal" aria-hidden="true">
        <div class="task-modal" role="dialog" aria-modal="true" aria-labelledby="create-task-modal-title">
            <div class="task-modal-header">
                <div>
                    <h2 class="section-title" id="create-task-modal-title">Crear tarea</h2>
                    <p class="section-copy">Los responsables disponibles son administradores y colaboradores.</p>
                </div>
                <button type="button" class="task-modal-close" id="close-create-task-modal" aria-label="Cerrar modal">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="task-modal-body">
                <form class="task-form" method="post" action="admin_tareas.php">
                    <input type="hidden" name="action" value="create_task">

                    <div class="form-grid">
                        <div class="field">
                            <label for="nombre_tarea">Nombre tarea</label>
                            <input id="nombre_tarea" name="nombre_tarea" type="text" maxlength="180" required>
                        </div>

                        <div class="field">
                            <label for="responsable_user_id">Responsable</label>
                            <select id="responsable_user_id" name="responsable_user_id" required>
                                <option value="">Seleccionar responsable</option>
                                <?php foreach ($usuariosOperativos as $usuario): ?>
                                    <option value="<?= (int) $usuario['id'] ?>">
                                        <?= htmlspecialchars($nombreUsuario($usuario) . ' - ' . ($usuario['correo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field full">
                            <label for="descripcion">Descripcion</label>
                            <textarea id="descripcion" name="descripcion" required></textarea>
                        </div>

                        <div class="field">
                            <label for="fecha_entrega">Fecha de entrega</label>
                            <input id="fecha_entrega" name="fecha_entrega" type="date" required>
                        </div>

                        <div class="field">
                            <label for="prioridad_defcon">Prioridad</label>
                            <select id="prioridad_defcon" name="prioridad_defcon" required>
                                <option value="">Seleccionar prioridad</option>
                                <?php foreach ($prioridadesDefcon as $valor => $label): ?>
                                    <option value="<?= (int) $valor ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="reporta_a">A quien reportar</label>
                            <input id="reporta_a" name="reporta_a" type="text" maxlength="180" placeholder="Nombre, equipo o referente" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="secondary-btn" type="button" id="cancel-create-task-modal">Cancelar</button>
                        <button class="primary-btn" type="submit">
                            <span class="material-icons" style="font-size:18px">add_task</span>
                            Crear tarea
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $perfilObligatorio = false; ?>
    <?php require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>

    <script>
        const createTaskModal = document.getElementById('create-task-modal');
        const openCreateTaskModal = document.getElementById('open-create-task-modal');
        const closeCreateTaskModal = document.getElementById('close-create-task-modal');
        const cancelCreateTaskModal = document.getElementById('cancel-create-task-modal');
        const firstCreateTaskField = document.getElementById('nombre_tarea');

        function openTaskModal() {
            createTaskModal.classList.add('is-open');
            createTaskModal.setAttribute('aria-hidden', 'false');
            firstCreateTaskField.focus();
        }

        function closeTaskModal() {
            createTaskModal.classList.remove('is-open');
            createTaskModal.setAttribute('aria-hidden', 'true');
            openCreateTaskModal.focus();
        }

        openCreateTaskModal.addEventListener('click', openTaskModal);
        closeCreateTaskModal.addEventListener('click', closeTaskModal);
        cancelCreateTaskModal.addEventListener('click', closeTaskModal);
        createTaskModal.addEventListener('click', (event) => {
            if (event.target === createTaskModal) {
                closeTaskModal();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && createTaskModal.classList.contains('is-open')) {
                closeTaskModal();
            }
        });
    </script>
</body>

</html>
