<section class="marketing-card">
    <h2>Metricas de campañas</h2>
    <div class="metrics-grid">
        <?php foreach ($metricasUsuario as $metric): ?>
            <div class="metric-tile">
                <strong><?= mh($metric['campaign_name'] ?? '') ?></strong>
                <span>Invertido <?= money($metric['amount_spent'] ?? 0) ?></span>
                <span>Impresiones <?= number_format((float) ($metric['impressions'] ?? 0), 0, ',', '.') ?></span>
                <span>Alcance <?= number_format((float) ($metric['reach'] ?? 0), 0, ',', '.') ?></span>
                <span>Conversaciones <?= number_format((float) ($metric['conversations_started'] ?? 0), 0, ',', '.') ?></span>
                <span>ROAS <?= ratioLabel($metric['roas'] ?? null) ?></span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($metricasUsuario)): ?><div class="empty">Todavia no hay metricas cargadas.</div><?php endif; ?>
    </div>
</section>
