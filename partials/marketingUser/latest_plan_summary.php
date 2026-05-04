<section class="marketing-card status-card">
    <span class="material-icons status-icon">history</span>
    <div>
        <h2>Ultimo plan</h2>
        <p class="muted">Podes revisar el resumen historico y volver a solicitar un plan disponible.</p>
        <div class="detail-grid">
            <div><small>Plan</small><strong><?= mh($suscripcionActual['plan_name'] ?? '-') ?></strong></div>
            <div><small>Estado</small><strong><?= statusLabel($suscripcionActual['status'] ?? '') ?></strong></div>
            <div><small>Inicio</small><strong><?= dateLabel($suscripcionActual['start_date'] ?? '') ?></strong></div>
            <div><small>Fin</small><strong><?= dateLabel($suscripcionActual['end_date'] ?? '') ?></strong></div>
        </div>
    </div>
</section>
