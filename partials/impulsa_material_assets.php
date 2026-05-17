<?php
declare(strict_types=1);

$impulsaMaterialAssetBase = $impulsaMaterialAssetBase ?? '../..';
$impulsaMaterialUseValidaciones = !empty($impulsaMaterialUseValidaciones);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Icons&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,0,0&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://material.impulsagroup.com/css/material.css">
<link rel="stylesheet" href="<?= htmlspecialchars((string)$impulsaMaterialAssetBase, ENT_QUOTES, 'UTF-8') ?>/assets/css/impulsa-overrides.css">
<script src="https://material.impulsagroup.com/js/material.js" defer></script>
<?php if ($impulsaMaterialUseValidaciones): ?>
<script src="https://material.impulsagroup.com/js/material-validaciones.js" defer></script>
<?php endif; ?>
<script src="<?= htmlspecialchars((string)$impulsaMaterialAssetBase, ENT_QUOTES, 'UTF-8') ?>/assets/js/impulsa-legacy-compat.js" defer></script>
