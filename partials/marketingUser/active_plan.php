<section class="marketing-card">
    <div class="plan-head">
        <div>
            <h2>Plan contratado</h2>
            <p class="muted"><?= mh($suscripcionActual['plan_name'] ?? '-') ?></p>
        </div>
        <span class="pill success">Activo</span>
    </div>
    <div class="detail-grid">
        <div><small>Duracion contratada</small><strong><?= (int) ($suscripcionActual['duration_months'] ?? 0) ?> meses</strong></div>
        <div><small>Precio mensual</small><strong><?= money($suscripcionActual['monthly_price'] ?? 0) ?></strong></div>
        <div><small>Total contratado</small><strong><?= money($suscripcionActual['total_contract_value'] ?? 0) ?></strong></div>
        <div><small>Presupuesto mensual ads</small><strong><?= money($suscripcionActual['monthly_ad_budget'] ?? 0) ?></strong></div>
        <div><small>Inicio</small><strong><?= dateLabel($suscripcionActual['start_date'] ?? '') ?></strong></div>
        <div><small>Fin</small><strong><?= dateLabel($suscripcionActual['end_date'] ?? '') ?></strong></div>
        <div><small>Responsable marketing</small><strong><?= mh($suscripcionActual['marketing_responsable'] ?? '-') ?></strong></div>
    </div>
    <h3>Detalle del plan</h3>
    <ul class="feature-list">
        <?php foreach (($suscripcionActual['features'] ?? []) as $feature): ?>
            <li><span class="material-icons">check_circle</span><span><?= mh($feature['feature_name'] ?? '') ?></span></li>
        <?php endforeach; ?>
    </ul>
    <h3>Campañas asociadas</h3>
    <div class="mini-table">
        <?php foreach ($campaniasUsuario as $campaign): ?>
            <div><strong><?= mh($campaign['campaign_name'] ?? '') ?></strong><span><?= mh($campaign['channel'] ?? '') ?> · <?= statusLabel($campaign['status'] ?? '') ?></span></div>
        <?php endforeach; ?>
        <?php if (empty($campaniasUsuario)): ?><div class="empty">Todavia no hay campañas asociadas.</div><?php endif; ?>
    </div>
</section>
