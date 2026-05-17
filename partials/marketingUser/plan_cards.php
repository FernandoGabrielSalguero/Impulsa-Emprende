<?php if (!empty($planesPublicados)): ?>
<div class="plan-grid">
    <?php foreach ($planesPublicados as $plan): ?>
        <article class="marketing-card plan-card im-tarjeta">
            <div class="plan-head">
                <div>
                    <h2><?= mh($plan['name'] ?? '') ?></h2>
                    <p class="muted"><?= mh($plan['short_description'] ?? '') ?></p>
                </div>
                <span class="pill im-chip">Publicado</span>
            </div>
            <p><?= nl2br(mh($plan['full_description'] ?? '')) ?></p>
            <ul class="feature-list">
                <?php foreach (($plan['features'] ?? []) as $feature): ?>
                    <li><span class="material-icons">check_circle</span><span><?= mh($feature['feature_name'] ?? '') ?><?= !empty($feature['feature_description']) ? ' - ' . mh($feature['feature_description']) : '' ?></span></li>
                <?php endforeach; ?>
            </ul>
            <form method="post" class="price-list">
                <input type="hidden" name="action" value="request_plan">
                <?php foreach (($plan['pricing_options'] ?? []) as $price): ?>
                    <label class="price-option">
                        <input type="radio" name="pricing_option_id" value="<?= (int) $price['id'] ?>" <?= !empty($price['is_default']) ? 'checked' : '' ?> required>
                        <span>
                            <strong><?= (int) $price['duration_months'] ?> meses - <?= money($price['monthly_price'] ?? 0) ?>/mes</strong>
                            <small>Total <?= money($price['total_price'] ?? 0) ?><?= !empty($price['saving_message']) ? ' · ' . mh($price['saving_message']) : '' ?></small>
                        </span>
                    </label>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-aceptar im-boton im-boton--principal">Solicitar este plan</button>
            </form>
        </article>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="marketing-card empty im-tarjeta">Todavia no hay planes publicados disponibles.</div>
<?php endif; ?>
