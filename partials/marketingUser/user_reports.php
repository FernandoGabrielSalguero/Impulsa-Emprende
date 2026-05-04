<section class="marketing-card">
    <h2>Reportes visibles</h2>
    <div class="report-list">
        <?php foreach ($reportesUsuario as $report): ?>
            <article>
                <div class="plan-head">
                    <h3><?= mh($report['title'] ?? '') ?></h3>
                    <span class="pill"><?= dateLabel($report['period_start'] ?? '') ?> - <?= dateLabel($report['period_end'] ?? '') ?></span>
                </div>
                <p><?= nl2br(mh($report['summary'] ?? '')) ?></p>
                <p><strong>Conclusiones:</strong> <?= nl2br(mh($report['conclusions'] ?? '')) ?></p>
                <p><strong>Proximas acciones:</strong> <?= nl2br(mh($report['next_actions'] ?? '')) ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (empty($reportesUsuario)): ?><div class="empty">No hay reportes visibles por ahora.</div><?php endif; ?>
    </div>
</section>
