<?php
declare(strict_types=1);

if (!function_exists('marketingActive')) {
    function marketingActive(string $file): string
    {
        return basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) === $file ? ' class="active"' : '';
    }
}

$marketingRol = (string) ($_SESSION['rol'] ?? '');
$isMarketingStaffNav = in_array($marketingRol, ['impulsa_administrador', 'impulsa_marketing'], true);
$isAdminNav = $marketingRol === 'impulsa_administrador';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/institucionales/icons/Isotipo grande.png" alt="Impulsa Emprende" class="sidebar-brand-icon">
        <span class="logo-text">impulsa emprende</span>
    </div>
    <nav class="sidebar-menu">
        <ul>
            <?php if ($isAdminNav): ?>
                <li onclick="location.href='../admin/admin_dashboard.php'"><span class="material-icons" style="color:#6366f1">home</span><span class="link-text">Inicio</span></li>
                <li onclick="location.href='../admin/admin_users.php'"><span class="material-icons" style="color:#2563eb">group</span><span class="link-text">Usuarios</span></li>
                <li onclick="location.href='../admin/admin_proceso_emprende.php'"><span class="material-icons" style="color:#0f766e">assignment</span><span class="link-text">Proceso emprende</span></li>
                <li onclick="location.href='../admin/admin_newproject.php'"><span class="material-icons" style="color:#f59e0b">rocket_launch</span><span class="link-text">Solicitudes externas</span></li>
                <li onclick="location.href='../admin/admin_tareas.php'"><span class="material-icons" style="color:#7c3aed">task_alt</span><span class="link-text">Tareas</span></li>
                <?php require __DIR__ . '/marketing_submenu.php'; ?>
            <?php elseif ($isMarketingStaffNav): ?>
                <?php require __DIR__ . '/marketing_submenu.php'; ?>
            <?php else: ?>
                <?php if ($marketingRol === 'impulsa_cliente'): ?>
                    <li onclick="location.href='../clientes/clientes_dashboard.php'"><span class="material-icons">home</span><span class="link-text">Mi proyecto</span></li>
                    <li onclick="location.href='../clientes/clientes_metricas.php'"><span class="material-icons" style="color:#0f766e">query_stats</span><span class="link-text">Metricas</span></li>
                    <li class="active" onclick="location.href='marketing_user.php'"><span class="material-icons" style="color:#0f766e">campaign</span><span class="link-text">Marketing</span></li>
                <?php elseif ($marketingRol === 'impulsa_emprendedor'): ?>
                    <li onclick="location.href='../emprendedor/emprendedor_dashboard.php'">
                        <span class="material-icons" style="color:#6366f1">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='../emprendedor/emprendedor_mision.php'">
                        <span class="material-icons" style="color:#6366f1">track_changes</span>
                        <span class="link-text">Mision</span>
                    </li>
                    <li onclick="location.href='../emprendedor/emprendedor_vision.php'">
                        <span class="material-icons" style="color:#6366f1">lightbulb</span>
                        <span class="link-text">Vision</span>
                    </li>
                    <li onclick="location.href='../emprendedor/emprendedor_buyerPersona.php'">
                        <span class="material-icons" style="color:#6366f1">groups</span>
                        <span class="link-text">Buyer Persona</span>
                    </li>
                    <li onclick="location.href='../emprendedor/landing_page_request.php'">
                        <span class="material-icons" style="color:#6366f1">rocket_launch</span>
                        <span class="link-text">Landing Page</span>
                    </li>
                    <li class="active" onclick="location.href='marketing_user.php'">
                        <span class="material-icons" style="color:#0f766e">campaign</span>
                        <span class="link-text">Marketing</span>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($marketingRol === 'impulsa_cliente'): ?>
                <li onclick="location.href='../../logout.php?redirect=https%3A%2F%2Fimpulsagroup.com%2F'"><span class="material-icons" style="color:red">logout</span><span class="link-text">Salir</span></li>
            <?php else: ?>
                <li onclick="location.href='../../logout.php'"><span class="material-icons" style="color:red">logout</span><span class="link-text">Salir</span></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <button class="btn-icon" onclick="toggleSidebar()"><span class="material-icons" id="collapseIcon">chevron_left</span></button>
    </div>
</aside>
