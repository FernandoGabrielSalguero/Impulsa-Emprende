<?php
require_once __DIR__ . '/../../controllers/marketing_userController.php';
require_once __DIR__ . '/marketing_helpers.php';
$displayName = mh($perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Usuario');
$state = (string) ($estadoMarketing['state'] ?? 'none');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impulsa - Marketing</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/framework/framework.css">
    <script src="../../assets/framework/framework.js" defer></script>
    <link rel="stylesheet" href="marketing_styles.css">
</head>
<body>
<div class="layout">
    <?php require __DIR__ . '/../../partials/marketing_nav.php'; ?>
    <main class="main">
        <header class="navbar">
            <div class="navbar-left"><button class="btn-icon" onclick="toggleSidebar()"><span class="material-icons">menu</span></button><div class="navbar-title">Marketing</div></div>
            <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
        </header>
        <section class="content page-stack">
            <?php if (($flash['message'] ?? '') !== ''): ?><div class="flash <?= mh($flash['type']) ?>"><?= mh($flash['message']) ?></div><?php endif; ?>
            <div class="marketing-card hero-card">
                <h1>Marketing</h1>
                <p class="muted">Hola, <?= $displayName ?>. Desde aca podes solicitar planes y consultar el estado de tu gestion de marketing.</p>
            </div>
            <?php if ($state === 'pending'): ?>
                <?php require __DIR__ . '/../../partials/marketingUser/pending_request.php'; ?>
            <?php elseif ($state === 'active'): ?>
                <?php require __DIR__ . '/../../partials/marketingUser/active_plan.php'; ?>
                <?php require __DIR__ . '/../../partials/marketingUser/user_metrics.php'; ?>
                <?php require __DIR__ . '/../../partials/marketingUser/user_reports.php'; ?>
            <?php elseif ($state === 'closed'): ?>
                <?php require __DIR__ . '/../../partials/marketingUser/latest_plan_summary.php'; ?>
                <?php require __DIR__ . '/../../partials/marketingUser/user_metrics.php'; ?>
                <?php require __DIR__ . '/../../partials/marketingUser/plan_cards.php'; ?>
            <?php else: ?>
                <?php require __DIR__ . '/../../partials/marketingUser/plan_cards.php'; ?>
            <?php endif; ?>
        </section>
    </main>
</div>
<?php $perfilObligatorio = false; require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>
</body>
</html>
