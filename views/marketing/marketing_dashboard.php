<?php
require_once __DIR__ . '/../../controllers/marketing_dashboardController.php';
require_once __DIR__ . '/../../views/marketing/marketing_helpers.php';
$displayName = mh($perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Marketing');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketing - Dashboard comercial</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/framework/framework.css">
    <script src="../../assets/framework/framework.js" defer></script>
    <link rel="stylesheet" href="marketing_styles.css">
</head>
<body>
<div class="layout">
    <?php require __DIR__ . '/../../partials/marketing_nav.php'; ?>
    <main class="main">
        <header class="navbar"><div class="navbar-left"><button class="btn-icon" onclick="toggleSidebar()"><span class="material-icons">menu</span></button><div class="navbar-title">Dashboard comercial de marketing</div></div><?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?></header>
        <section class="content page-stack">
            <div class="marketing-card hero-card"><h1>Marketing y Panel Comercial</h1><p class="muted">Hola, <?= $displayName ?>. Vista consolidada de inversion, resultados y metricas comerciales.</p></div>
            <form class="marketing-card filters" method="get">
                <select name="user_id"><option value="">Todos los clientes/emprendedores</option><?php foreach ($usuariosFinales as $u): ?><option value="<?= (int) $u['id'] ?>" <?= (int)($_GET['user_id'] ?? 0)===(int)$u['id']?'selected':'' ?>><?= mh($u['display_name'] ?? $u['correo'] ?? '') ?></option><?php endforeach; ?></select>
                <select name="campaign_id"><option value="">Todas las campañas</option><?php foreach ($campanias as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int)($_GET['campaign_id'] ?? 0)===(int)$c['id']?'selected':'' ?>><?= mh($c['campaign_name'] ?? '') ?></option><?php endforeach; ?></select>
                <input type="date" name="date_from" value="<?= mh($_GET['date_from'] ?? '') ?>">
                <input type="date" name="date_to" value="<?= mh($_GET['date_to'] ?? '') ?>">
                <button class="btn btn-aceptar" type="submit">Filtrar</button>
            </form>
            <?php
            $totals = ['spent'=>0,'impressions'=>0,'reach'=>0,'contacts'=>0,'closed'=>0,'revenue'=>0,'proposals'=>0];
            foreach ($metricas as $m) {
                $totals['spent'] += (float)($m['amount_spent'] ?? 0);
                $totals['impressions'] += (float)($m['impressions'] ?? 0);
                $totals['reach'] += (float)($m['reach'] ?? 0);
                $totals['contacts'] += (float)($m['successful_contacts'] ?? 0);
                $totals['closed'] += (float)($m['closed_clients'] ?? 0);
                $totals['revenue'] += (float)($m['closed_revenue'] ?? 0);
                $totals['proposals'] += (float)($m['proposals_sent'] ?? 0);
            }
            ?>
            <div class="kpi-grid">
                <div class="kpi-card"><span>Invertido</span><strong><?= money($totals['spent']) ?></strong></div>
                <div class="kpi-card"><span>Impresiones</span><strong><?= number_format($totals['impressions'],0,',','.') ?></strong></div>
                <div class="kpi-card"><span>Alcance</span><strong><?= number_format($totals['reach'],0,',','.') ?></strong></div>
                <div class="kpi-card"><span>Propuestas</span><strong><?= number_format($totals['proposals'],0,',','.') ?></strong></div>
                <div class="kpi-card"><span>Clientes cerrados</span><strong><?= number_format($totals['closed'],0,',','.') ?></strong></div>
                <div class="kpi-card"><span>Ventas cerradas</span><strong><?= money($totals['revenue']) ?></strong></div>
            </div>
            <div class="marketing-card">
                <h2>Campañas</h2>
                <div class="table-wrap"><table class="marketing-table">
                    <thead><tr><th>Campaña</th><th>Invertido</th><th>Resultados</th><th>Conversaciones</th><th>Propuestas</th><th>Cierre</th><th>ROAS</th><th>ROI</th></tr></thead>
                    <tbody><?php foreach ($metricas as $m): ?><tr>
                        <td><?= mh($m['campaign_name'] ?? '') ?></td><td><?= money($m['amount_spent'] ?? 0) ?></td><td><?= number_format((float)($m['results'] ?? 0),0,',','.') ?></td><td><?= number_format((float)($m['conversations_started'] ?? 0),0,',','.') ?></td><td><?= number_format((float)($m['proposals_sent'] ?? 0),0,',','.') ?></td><td><?= percentLabel($m['closing_rate'] ?? null) ?></td><td><?= ratioLabel($m['roas'] ?? null) ?></td><td><?= percentLabel($m['roi'] ?? null) ?></td>
                    </tr><?php endforeach; ?></tbody>
                </table></div>
                <?php if (empty($metricas)): ?><div class="empty">Sin datos para mostrar.</div><?php endif; ?>
            </div>
        </section>
    </main>
</div>
<?php $perfilObligatorio = false; require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>
</body>
</html>
