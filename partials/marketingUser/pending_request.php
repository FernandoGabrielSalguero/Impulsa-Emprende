<section class="marketing-card status-card im-tarjeta">
    <span class="material-icons status-icon">pending_actions</span>
    <div>
        <h2>Solicitud en proceso</h2>
        <p class="muted">El equipo de marketing se contactara para coordinar los proximos pasos.</p>
        <div class="detail-grid">
            <div><small>Plan solicitado</small><strong><?= mh($suscripcionActual['plan_name'] ?? '-') ?></strong></div>
            <div><small>Duracion</small><strong><?= (int) ($suscripcionActual['duration_months'] ?? 0) ?> meses</strong></div>
            <div><small>Precio mensual</small><strong><?= money($suscripcionActual['monthly_price'] ?? 0) ?></strong></div>
            <div><small>Total contratado</small><strong><?= money($suscripcionActual['total_contract_value'] ?? 0) ?></strong></div>
            <div><small>Estado actual</small><strong><?= statusLabel($suscripcionActual['status'] ?? '') ?></strong></div>
            <div><small>Fecha de solicitud</small><strong><?= dateLabel($suscripcionActual['created_at'] ?? '') ?></strong></div>
        </div>
    </div>
</section>
