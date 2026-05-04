<?php
declare(strict_types=1);
if (!function_exists('mh')) {
    function mh($value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
    function money($value): string { return '$' . number_format((float) $value, 0, ',', '.'); }
    function dateLabel($value): string { $value = trim((string) $value); return $value !== '' ? date('d/m/Y', strtotime($value)) : '-'; }
    function ratioLabel($value): string { return $value === null ? 'Sin datos' : number_format((float) $value, 2, ',', '.') . 'x'; }
    function percentLabel($value): string { return $value === null ? 'Sin datos' : number_format(((float) $value) * 100, 1, ',', '.') . '%'; }
    function statusLabel($value): string {
        $map = [
            'draft'=>'Borrador','published'=>'Publicado','paused'=>'Pausado','archived'=>'Archivado',
            'requested'=>'Solicitado','meeting_scheduled'=>'Reunion coordinada','approved_manually'=>'Aprobado manualmente',
            'pending_payment'=>'Pendiente de pago','active'=>'Activo','completed'=>'Completado','cancelled'=>'Cancelado',
            'imported'=>'Importado','partial'=>'Parcial','failed'=>'Fallido','previewed'=>'Previsualizado'
        ];
        return $map[(string) $value] ?? (string) $value;
    }
}
