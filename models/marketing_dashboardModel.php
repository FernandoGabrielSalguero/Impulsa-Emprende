<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

class MarketingDashboardModel
{
    protected PDO $db;

    public const STAFF_ROLES = ['impulsa_administrador', 'impulsa_marketing'];
    public const USER_ROLES = ['impulsa_cliente', 'impulsa_emprendedor'];
    public const PENDING_STATUSES = ['requested', 'meeting_scheduled', 'approved_manually', 'pending_payment'];
    public const CLOSED_STATUSES = ['paused', 'completed', 'cancelled'];

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function isStaff(string $rol): bool
    {
        return in_array($rol, self::STAFF_ROLES, true);
    }

    public function obtenerPerfil(int $userId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT ua.id, ua.correo, ua.rol, ua.created_at, ui.nombre, ui.apellido, ui.apodo, ui.avatar_path, uc.whatsapp
                 FROM user_auth ua
                 LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
                 LEFT JOIN user_contacto uc ON uc.user_auth_id = ua.id
                 WHERE ua.id = :id
                 LIMIT 1"
            );
            $stmt->execute(['id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function listarUsuariosFinales(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT ua.id, ua.correo, ua.rol, COALESCE(ui.apodo, ui.nombre, ua.correo) AS display_name, uc.whatsapp
                 FROM user_auth ua
                 LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
                 LEFT JOIN user_contacto uc ON uc.user_auth_id = ua.id
                 WHERE ua.rol IN ('impulsa_cliente', 'impulsa_emprendedor')
                 ORDER BY display_name ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function listarResponsablesMarketing(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT ua.id, ua.correo, ua.rol, COALESCE(ui.apodo, ui.nombre, ua.correo) AS display_name
                 FROM user_auth ua
                 LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
                 WHERE ua.rol IN ('impulsa_marketing', 'impulsa_administrador')
                 ORDER BY display_name ASC"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function listarPlanes(bool $soloPublicos = false): array
    {
        try {
            $where = $soloPublicos ? "WHERE p.status = 'published' AND p.is_visible_to_clients = 1" : '';
            $stmt = $this->db->query(
                "SELECT p.*, COALESCE(ui.apodo, ui.nombre, ua.correo) AS created_by_label
                 FROM marketing_plans p
                 LEFT JOIN user_auth ua ON ua.id = p.created_by_user_id
                 LEFT JOIN user_info ui ON ui.user_auth_id = ua.id
                 $where
                 ORDER BY p.status = 'published' DESC, p.updated_at DESC, p.id DESC"
            );
            $planes = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($planes as &$plan) {
                $plan['features'] = $this->listarFeatures((int) $plan['id']);
                $plan['pricing_options'] = $this->listarPrecios((int) $plan['id']);
            }
            unset($plan);
            return $planes;
        } catch (Throwable) {
            return [];
        }
    }

    public function listarFeatures(int $planId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM marketing_plan_features
                 WHERE plan_id = :plan_id
                 ORDER BY feature_order ASC, id ASC"
            );
            $stmt->execute(['plan_id' => $planId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function listarPrecios(int $planId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM marketing_plan_pricing_options
                 WHERE plan_id = :plan_id
                 ORDER BY display_order ASC, duration_months ASC, id ASC"
            );
            $stmt->execute(['plan_id' => $planId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            if (empty($rows)) {
                return [];
            }

            $base = $rows[0];
            foreach ($rows as $row) {
                if ((int) $row['duration_months'] === 1) {
                    $base = $row;
                    break;
                }
                if ((int) $row['duration_months'] < (int) $base['duration_months']) {
                    $base = $row;
                }
            }

            $baseMonthly = (float) $base['monthly_price'];
            foreach ($rows as &$row) {
                $duration = max(1, (int) $row['duration_months']);
                $saving = ($baseMonthly * $duration) - (float) $row['total_price'];
                $row['saving_amount'] = max(0, $saving);
                $row['saving_message'] = $saving > 0
                    ? 'Contratando ' . $duration . ' meses ahorras $' . number_format($saving, 0, ',', '.') . ' frente al plan mensual.'
                    : '';
            }
            unset($row);

            return $rows;
        } catch (Throwable) {
            return [];
        }
    }

    public function guardarPlan(array $data, int $userId): int
    {
        $id = (int) ($data['id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        $slug = $this->slugify($data['slug'] ?? $name);
        $payload = [
            'name' => $name,
            'slug' => $slug,
            'short_description' => trim((string) ($data['short_description'] ?? '')),
            'full_description' => trim((string) ($data['full_description'] ?? '')),
            'objective' => trim((string) ($data['objective'] ?? '')),
            'recommended_ad_budget_min' => $this->nullableDecimal($data['recommended_ad_budget_min'] ?? null),
            'recommended_ad_budget_max' => $this->nullableDecimal($data['recommended_ad_budget_max'] ?? null),
            'setup_fee' => $this->nullableDecimal($data['setup_fee'] ?? null) ?? 0,
            'billing_period' => trim((string) ($data['billing_period'] ?? 'monthly')),
            'report_frequency' => trim((string) ($data['report_frequency'] ?? 'monthly')),
            'support_level' => trim((string) ($data['support_level'] ?? 'standard')),
            'is_visible_to_clients' => isset($data['is_visible_to_clients']) ? 1 : 0,
            'status' => trim((string) ($data['status'] ?? 'draft')),
        ];

        if ($id > 0) {
            $payload['id'] = $id;
            $stmt = $this->db->prepare(
                "UPDATE marketing_plans
                 SET name=:name, slug=:slug, short_description=:short_description, full_description=:full_description,
                     objective=:objective, recommended_ad_budget_min=:recommended_ad_budget_min,
                     recommended_ad_budget_max=:recommended_ad_budget_max, setup_fee=:setup_fee,
                     billing_period=:billing_period, report_frequency=:report_frequency, support_level=:support_level,
                     is_visible_to_clients=:is_visible_to_clients, status=:status, updated_at=NOW()
                 WHERE id=:id"
            );
            $stmt->execute($payload);
            return $id;
        }

        $payload['created_by_user_id'] = $userId;
        $stmt = $this->db->prepare(
            "INSERT INTO marketing_plans
             (name, slug, short_description, full_description, objective, recommended_ad_budget_min,
              recommended_ad_budget_max, setup_fee, billing_period, report_frequency, support_level,
              is_visible_to_clients, status, created_by_user_id, created_at, updated_at)
             VALUES
             (:name, :slug, :short_description, :full_description, :objective, :recommended_ad_budget_min,
              :recommended_ad_budget_max, :setup_fee, :billing_period, :report_frequency, :support_level,
              :is_visible_to_clients, :status, :created_by_user_id, NOW(), NOW())"
        );
        $stmt->execute($payload);
        return (int) $this->db->lastInsertId();
    }

    public function guardarFeature(array $data): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO marketing_plan_features
             (plan_id, feature_name, feature_description, quantity, unit, feature_order, is_highlighted, created_at, updated_at)
             VALUES (:plan_id, :feature_name, :feature_description, :quantity, :unit, :feature_order, :is_highlighted, NOW(), NOW())"
        );
        $stmt->execute([
            'plan_id' => (int) ($data['plan_id'] ?? 0),
            'feature_name' => trim((string) ($data['feature_name'] ?? '')),
            'feature_description' => trim((string) ($data['feature_description'] ?? '')),
            'quantity' => $this->nullableDecimal($data['quantity'] ?? null),
            'unit' => trim((string) ($data['unit'] ?? '')),
            'feature_order' => (int) ($data['feature_order'] ?? 0),
            'is_highlighted' => isset($data['is_highlighted']) ? 1 : 0,
        ]);
    }

    public function guardarPrecio(array $data): void
    {
        $duration = max(1, (int) ($data['duration_months'] ?? 1));
        $monthly = (float) str_replace(',', '.', (string) ($data['monthly_price'] ?? 0));
        $total = trim((string) ($data['total_price'] ?? '')) !== ''
            ? (float) str_replace(',', '.', (string) $data['total_price'])
            : $monthly * $duration;

        $stmt = $this->db->prepare(
            "INSERT INTO marketing_plan_pricing_options
             (plan_id, duration_months, monthly_price, total_price, setup_fee, currency, is_featured, is_default, display_order, created_at, updated_at)
             VALUES (:plan_id, :duration_months, :monthly_price, :total_price, :setup_fee, :currency, :is_featured, :is_default, :display_order, NOW(), NOW())"
        );
        $stmt->execute([
            'plan_id' => (int) ($data['plan_id'] ?? 0),
            'duration_months' => $duration,
            'monthly_price' => $monthly,
            'total_price' => $total,
            'setup_fee' => $this->nullableDecimal($data['setup_fee'] ?? null) ?? 0,
            'currency' => trim((string) ($data['currency'] ?? 'ARS')),
            'is_featured' => isset($data['is_featured']) ? 1 : 0,
            'is_default' => isset($data['is_default']) ? 1 : 0,
            'display_order' => (int) ($data['display_order'] ?? 0),
        ]);
    }

    public function cambiarEstadoPlan(int $planId, string $status): void
    {
        $stmt = $this->db->prepare("UPDATE marketing_plans SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $planId, 'status' => $status]);
    }

    public function obtenerPrecio(int $pricingId): array
    {
        $stmt = $this->db->prepare(
            "SELECT po.*, p.name AS plan_name
             FROM marketing_plan_pricing_options po
             INNER JOIN marketing_plans p ON p.id = po.plan_id
             WHERE po.id = :id AND p.status = 'published' AND p.is_visible_to_clients = 1
             LIMIT 1"
        );
        $stmt->execute(['id' => $pricingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function solicitarPlan(int $userId, string $rol, int $pricingId): array
    {
        $pricing = $this->obtenerPrecio($pricingId);
        if (empty($pricing)) {
            return ['ok' => false, 'error' => 'No se encontro la opcion elegida.'];
        }

        $active = $this->obtenerUltimaSuscripcionUsuario($userId, $rol, array_merge(self::PENDING_STATUSES, ['active']));
        if (!empty($active)) {
            return ['ok' => false, 'error' => 'Ya tenes una solicitud o plan activo.'];
        }

        $stmt = $this->db->prepare(
            "INSERT INTO marketing_plan_subscriptions
             (plan_id, pricing_option_id, client_user_id, entrepreneur_user_id, status, payment_status, payment_provider,
              payment_required, duration_months, monthly_price, total_contract_value, created_at, updated_at)
             VALUES (:plan_id, :pricing_option_id, :client_user_id, :entrepreneur_user_id, 'requested', 'not_required_yet', NULL,
              0, :duration_months, :monthly_price, :total_contract_value, NOW(), NOW())"
        );
        $stmt->execute([
            'plan_id' => (int) $pricing['plan_id'],
            'pricing_option_id' => (int) $pricing['id'],
            'client_user_id' => $rol === 'impulsa_cliente' ? $userId : null,
            'entrepreneur_user_id' => $rol === 'impulsa_emprendedor' ? $userId : null,
            'duration_months' => (int) $pricing['duration_months'],
            'monthly_price' => (float) $pricing['monthly_price'],
            'total_contract_value' => (float) $pricing['total_price'],
        ]);

        return ['ok' => true, 'id' => (int) $this->db->lastInsertId()];
    }

    public function obtenerUltimaSuscripcionUsuario(int $userId, string $rol, array $statuses = []): array
    {
        try {
            $field = $rol === 'impulsa_emprendedor' ? 's.entrepreneur_user_id' : 's.client_user_id';
            $params = ['user_id' => $userId];
            $statusSql = '';
            if (!empty($statuses)) {
                $keys = [];
                foreach ($statuses as $i => $status) {
                    $key = 'status_' . $i;
                    $keys[] = ':' . $key;
                    $params[$key] = $status;
                }
                $statusSql = ' AND s.status IN (' . implode(',', $keys) . ')';
            }
            $stmt = $this->db->prepare(
                "SELECT s.*, p.name AS plan_name, p.short_description, p.full_description,
                        COALESCE(mu.apodo, mu.nombre, ma.correo) AS marketing_responsable
                 FROM marketing_plan_subscriptions s
                 INNER JOIN marketing_plans p ON p.id = s.plan_id
                 LEFT JOIN user_auth ma ON ma.id = s.assigned_marketing_user_id
                 LEFT JOIN user_info mu ON mu.user_auth_id = ma.id
                 WHERE $field = :user_id $statusSql
                 ORDER BY s.created_at DESC, s.id DESC
                 LIMIT 1"
            );
            $stmt->execute($params);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            if (!empty($subscription)) {
                $subscription['features'] = $this->listarFeatures((int) $subscription['plan_id']);
            }
            return $subscription;
        } catch (Throwable) {
            return [];
        }
    }

    public function obtenerEstadoUsuarioMarketing(int $userId, string $rol): array
    {
        $pending = $this->obtenerUltimaSuscripcionUsuario($userId, $rol, self::PENDING_STATUSES);
        if (!empty($pending)) {
            return ['state' => 'pending', 'subscription' => $pending];
        }

        $active = $this->obtenerUltimaSuscripcionUsuario($userId, $rol, ['active']);
        if (!empty($active)) {
            return ['state' => 'active', 'subscription' => $active];
        }

        $latest = $this->obtenerUltimaSuscripcionUsuario($userId, $rol, self::CLOSED_STATUSES);
        if (!empty($latest)) {
            return ['state' => 'closed', 'subscription' => $latest];
        }

        return ['state' => 'none', 'subscription' => []];
    }

    public function listarSuscripciones(array $filters = []): array
    {
        try {
            $where = [];
            $params = [];
            if (!empty($filters['status'])) {
                $where[] = 's.status = :status';
                $params['status'] = $filters['status'];
            }
            if (!empty($filters['user_id'])) {
                $where[] = '(s.client_user_id = :user_id OR s.entrepreneur_user_id = :user_id)';
                $params['user_id'] = (int) $filters['user_id'];
            }
            $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $stmt = $this->db->prepare(
                "SELECT s.*, p.name AS plan_name,
                        COALESCE(cui.apodo, cui.nombre, cua.correo, eui.apodo, eui.nombre, eua.correo) AS user_label,
                        COALESCE(cua.correo, eua.correo) AS user_email,
                        COALESCE(cuc.whatsapp, euc.whatsapp) AS user_whatsapp,
                        COALESCE(mui.apodo, mui.nombre, mua.correo) AS marketing_label
                 FROM marketing_plan_subscriptions s
                 INNER JOIN marketing_plans p ON p.id = s.plan_id
                 LEFT JOIN user_auth cua ON cua.id = s.client_user_id
                 LEFT JOIN user_info cui ON cui.user_auth_id = cua.id
                 LEFT JOIN user_contacto cuc ON cuc.user_auth_id = cua.id
                 LEFT JOIN user_auth eua ON eua.id = s.entrepreneur_user_id
                 LEFT JOIN user_info eui ON eui.user_auth_id = eua.id
                 LEFT JOIN user_contacto euc ON euc.user_auth_id = eua.id
                 LEFT JOIN user_auth mua ON mua.id = s.assigned_marketing_user_id
                 LEFT JOIN user_info mui ON mui.user_auth_id = mua.id
                 $whereSql
                 ORDER BY FIELD(s.status, 'requested','meeting_scheduled','approved_manually','pending_payment','active','paused','completed','cancelled'), s.created_at DESC"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function actualizarSuscripcion(array $data): void
    {
        $stmt = $this->db->prepare(
            "UPDATE marketing_plan_subscriptions
             SET status=:status, assigned_marketing_user_id=:assigned_marketing_user_id, notes=:notes,
                 monthly_ad_budget=:monthly_ad_budget, start_date=:start_date, end_date=:end_date,
                 activated_at = CASE WHEN :status = 'active' AND activated_at IS NULL THEN NOW() ELSE activated_at END,
                 updated_at=NOW()
             WHERE id=:id"
        );
        $stmt->execute([
            'id' => (int) ($data['id'] ?? 0),
            'status' => trim((string) ($data['status'] ?? 'requested')),
            'assigned_marketing_user_id' => $this->nullableInt($data['assigned_marketing_user_id'] ?? null),
            'notes' => trim((string) ($data['notes'] ?? '')),
            'monthly_ad_budget' => $this->nullableDecimal($data['monthly_ad_budget'] ?? null),
            'start_date' => $this->nullableDate($data['start_date'] ?? null),
            'end_date' => $this->nullableDate($data['end_date'] ?? null),
        ]);
    }

    public function listarCampanias(array $filters = []): array
    {
        try {
            $where = [];
            $params = [];
            if (!empty($filters['subscription_id'])) {
                $where[] = 'c.subscription_id = :subscription_id';
                $params['subscription_id'] = (int) $filters['subscription_id'];
            }
            if (!empty($filters['user_id'])) {
                $where[] = '(c.client_user_id = :user_id OR c.entrepreneur_user_id = :user_id)';
                $params['user_id'] = (int) $filters['user_id'];
            }
            $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $stmt = $this->db->prepare(
                "SELECT c.*, s.status AS subscription_status, p.name AS plan_name,
                        COALESCE(cui.apodo, cui.nombre, cua.correo, eui.apodo, eui.nombre, eua.correo) AS user_label
                 FROM marketing_campaigns c
                 LEFT JOIN marketing_plan_subscriptions s ON s.id = c.subscription_id
                 LEFT JOIN marketing_plans p ON p.id = s.plan_id
                 LEFT JOIN user_auth cua ON cua.id = c.client_user_id
                 LEFT JOIN user_info cui ON cui.user_auth_id = cua.id
                 LEFT JOIN user_auth eua ON eua.id = c.entrepreneur_user_id
                 LEFT JOIN user_info eui ON eui.user_auth_id = eua.id
                 $whereSql
                 ORDER BY c.updated_at DESC, c.id DESC"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function guardarCampania(array $data, int $createdBy): int
    {
        $subscription = $this->obtenerSuscripcion((int) ($data['subscription_id'] ?? 0));
        $clientId = $this->nullableInt($subscription['client_user_id'] ?? null);
        $entrepreneurId = $this->nullableInt($subscription['entrepreneur_user_id'] ?? null);
        $ownerId = $clientId ?: $entrepreneurId;
        $displayName = (string) ($subscription['user_label'] ?? ('USR' . $ownerId));
        $code = $ownerId ? $this->asegurarCodigoCliente($ownerId, $displayName) : 'USR';
        $recommended = $this->generarNombreMeta($code, (string) ($data['objective'] ?? 'OTRO'), (string) ($data['channel'] ?? 'meta_ads'), (string) ($data['start_date'] ?? date('Y-m-d')));

        $stmt = $this->db->prepare(
            "INSERT INTO marketing_campaigns
             (subscription_id, client_user_id, entrepreneur_user_id, campaign_name, recommended_meta_campaign_name,
              internal_code, channel, objective, start_date, end_date, budget, status, external_platform,
              external_campaign_name, created_by_user_id, created_at, updated_at)
             VALUES (:subscription_id, :client_user_id, :entrepreneur_user_id, :campaign_name, :recommended_meta_campaign_name,
              :internal_code, :channel, :objective, :start_date, :end_date, :budget, :status, :external_platform,
              :external_campaign_name, :created_by_user_id, NOW(), NOW())"
        );
        $stmt->execute([
            'subscription_id' => (int) ($data['subscription_id'] ?? 0),
            'client_user_id' => $clientId,
            'entrepreneur_user_id' => $entrepreneurId,
            'campaign_name' => trim((string) ($data['campaign_name'] ?? 'Campania')),
            'recommended_meta_campaign_name' => $recommended,
            'internal_code' => $code,
            'channel' => trim((string) ($data['channel'] ?? 'meta_ads')),
            'objective' => trim((string) ($data['objective'] ?? 'OTRO')),
            'start_date' => $this->nullableDate($data['start_date'] ?? null),
            'end_date' => $this->nullableDate($data['end_date'] ?? null),
            'budget' => $this->nullableDecimal($data['budget'] ?? null),
            'status' => trim((string) ($data['status'] ?? 'draft')),
            'external_platform' => trim((string) ($data['external_platform'] ?? 'meta_ads')),
            'external_campaign_name' => trim((string) ($data['external_campaign_name'] ?? '')),
            'created_by_user_id' => $createdBy,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function obtenerSuscripcion(int $id): array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, COALESCE(cui.apodo, cui.nombre, cua.correo, eui.apodo, eui.nombre, eua.correo) AS user_label
             FROM marketing_plan_subscriptions s
             LEFT JOIN user_auth cua ON cua.id = s.client_user_id
             LEFT JOIN user_info cui ON cui.user_auth_id = cua.id
             LEFT JOIN user_auth eua ON eua.id = s.entrepreneur_user_id
             LEFT JOIN user_info eui ON eui.user_auth_id = eua.id
             WHERE s.id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function asegurarCodigoCliente(int $userId, string $displayName): string
    {
        $stmt = $this->db->prepare("SELECT client_code FROM marketing_client_codes WHERE user_auth_id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        $code = (string) ($stmt->fetchColumn() ?: '');
        if ($code !== '') {
            return $code;
        }

        $base = $this->normalizarCodigo($displayName);
        if ($base === '') {
            $base = 'USR' . str_pad((string) $userId, 3, '0', STR_PAD_LEFT);
        }
        $candidate = substr($base, 0, 12);
        $i = 1;
        while ($this->existeClientCode($candidate)) {
            $candidate = substr($base, 0, 9) . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $i++;
        }

        $insert = $this->db->prepare(
            "INSERT INTO marketing_client_codes (user_auth_id, client_code, display_name, created_at, updated_at)
             VALUES (:user_auth_id, :client_code, :display_name, NOW(), NOW())"
        );
        $insert->execute([
            'user_auth_id' => $userId,
            'client_code' => $candidate,
            'display_name' => $displayName,
        ]);

        return $candidate;
    }

    public function guardarCodigoCliente(int $userId, string $code, string $displayName): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO marketing_client_codes (user_auth_id, client_code, display_name, created_at, updated_at)
             VALUES (:user_auth_id, :client_code, :display_name, NOW(), NOW())
             ON DUPLICATE KEY UPDATE client_code = VALUES(client_code), display_name = VALUES(display_name), updated_at = NOW()"
        );
        $stmt->execute([
            'user_auth_id' => $userId,
            'client_code' => $this->normalizarCodigo($code),
            'display_name' => trim($displayName),
        ]);
    }

    private function existeClientCode(string $code): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM marketing_client_codes WHERE client_code = :code");
        $stmt->execute(['code' => $code]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function generarNombreMeta(string $clientCode, string $objective, string $channel, string $startDate): string
    {
        $objective = $this->normalizarObjetivo($objective);
        $channel = $channel === 'meta_ads' ? 'META' : $this->normalizarCodigo($channel);
        $month = date('Y-m', strtotime($startDate !== '' ? $startDate : 'now'));
        return 'IMP-' . $this->normalizarCodigo($clientCode) . '-' . $objective . '-' . $channel . '-' . $month;
    }

    public function guardarMetricasComerciales(array $data, int $userId): void
    {
        $campaignId = (int) ($data['campaign_id'] ?? 0);
        $campaign = $this->obtenerCampania($campaignId);
        $stmt = $this->db->prepare(
            "INSERT INTO marketing_commercial_metrics
             (campaign_id, subscription_id, metric_period_start, metric_period_end, closed_clients, proposals_sent,
              unsuccessful_contacts, successful_contacts, meetings_scheduled, meetings_completed, quoted_amount,
              closed_revenue, notes, created_by_user_id, created_at, updated_at)
             VALUES (:campaign_id, :subscription_id, :metric_period_start, :metric_period_end, :closed_clients, :proposals_sent,
              :unsuccessful_contacts, :successful_contacts, :meetings_scheduled, :meetings_completed, :quoted_amount,
              :closed_revenue, :notes, :created_by_user_id, NOW(), NOW())"
        );
        $stmt->execute([
            'campaign_id' => $campaignId,
            'subscription_id' => (int) ($campaign['subscription_id'] ?? 0),
            'metric_period_start' => $this->nullableDate($data['metric_period_start'] ?? null),
            'metric_period_end' => $this->nullableDate($data['metric_period_end'] ?? null),
            'closed_clients' => (int) ($data['closed_clients'] ?? 0),
            'proposals_sent' => (int) ($data['proposals_sent'] ?? 0),
            'unsuccessful_contacts' => (int) ($data['unsuccessful_contacts'] ?? 0),
            'successful_contacts' => (int) ($data['successful_contacts'] ?? 0),
            'meetings_scheduled' => (int) ($data['meetings_scheduled'] ?? 0),
            'meetings_completed' => (int) ($data['meetings_completed'] ?? 0),
            'quoted_amount' => $this->nullableDecimal($data['quoted_amount'] ?? null) ?? 0,
            'closed_revenue' => $this->nullableDecimal($data['closed_revenue'] ?? null) ?? 0,
            'notes' => trim((string) ($data['notes'] ?? '')),
            'created_by_user_id' => $userId,
        ]);
    }

    public function obtenerCampania(int $campaignId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM marketing_campaigns WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $campaignId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function listarMetricasCampanias(array $filters = []): array
    {
        try {
            $where = [];
            $params = [];
            if (!empty($filters['user_id'])) {
                $where[] = '(c.client_user_id = :user_id OR c.entrepreneur_user_id = :user_id)';
                $params['user_id'] = (int) $filters['user_id'];
            }
            if (!empty($filters['campaign_id'])) {
                $where[] = 'c.id = :campaign_id';
                $params['campaign_id'] = (int) $filters['campaign_id'];
            }
            if (!empty($filters['date_from'])) {
                $where[] = 'm.report_start_date >= :date_from';
                $params['date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $where[] = 'm.report_end_date <= :date_to';
                $params['date_to'] = $filters['date_to'];
            }
            $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $stmt = $this->db->prepare(
                "SELECT c.id AS campaign_id, c.campaign_name, c.recommended_meta_campaign_name,
                        COALESCE(SUM(m.amount_spent_ars),0) AS amount_spent,
                        COALESCE(SUM(m.impressions),0) AS impressions,
                        COALESCE(SUM(m.reach),0) AS reach,
                        COALESCE(SUM(m.results),0) AS results,
                        COALESCE(SUM(m.total_message_contacts),0) AS total_message_contacts,
                        COALESCE(SUM(m.new_message_contacts),0) AS new_message_contacts,
                        COALESCE(SUM(m.messaging_conversations_started),0) AS conversations_started,
                        COALESCE(SUM(m.purchases),0) AS purchases,
                        COALESCE(SUM(cm.proposals_sent),0) AS proposals_sent,
                        COALESCE(SUM(cm.successful_contacts),0) AS successful_contacts,
                        COALESCE(SUM(cm.unsuccessful_contacts),0) AS unsuccessful_contacts,
                        COALESCE(SUM(cm.meetings_scheduled),0) AS meetings_scheduled,
                        COALESCE(SUM(cm.meetings_completed),0) AS meetings_completed,
                        COALESCE(SUM(cm.closed_clients),0) AS closed_clients,
                        COALESCE(SUM(cm.closed_revenue),0) AS closed_revenue,
                        COALESCE(SUM(cm.quoted_amount),0) AS quoted_amount,
                        COALESCE(s.total_contract_value,0) AS management_fee
                 FROM marketing_campaigns c
                 LEFT JOIN marketing_campaign_metrics m ON m.campaign_id = c.id
                 LEFT JOIN marketing_commercial_metrics cm ON cm.campaign_id = c.id
                 LEFT JOIN marketing_plan_subscriptions s ON s.id = c.subscription_id
                 $whereSql
                 GROUP BY c.id
                 ORDER BY c.updated_at DESC, c.id DESC"
            );
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$row) {
                $row += $this->calcularKpis($row);
            }
            unset($row);
            return $rows;
        } catch (Throwable) {
            return [];
        }
    }

    private function calcularKpis(array $row): array
    {
        $spent = (float) ($row['amount_spent'] ?? 0);
        $contacts = (float) ($row['successful_contacts'] ?? 0);
        $proposals = (float) ($row['proposals_sent'] ?? 0);
        $closed = (float) ($row['closed_clients'] ?? 0);
        $revenue = (float) ($row['closed_revenue'] ?? 0);
        $fee = (float) ($row['management_fee'] ?? 0);
        $investment = $spent + $fee;
        return [
            'cost_per_successful_contact' => $contacts > 0 ? $spent / $contacts : null,
            'cost_per_proposal' => $proposals > 0 ? $spent / $proposals : null,
            'cost_per_closed_client' => $closed > 0 ? $spent / $closed : null,
            'closing_rate' => $contacts > 0 ? $closed / $contacts : null,
            'roas' => $spent > 0 ? $revenue / $spent : null,
            'roi' => $investment > 0 ? ($revenue - $investment) / $investment : null,
        ];
    }

    public function listarReportes(array $filters = []): array
    {
        try {
            $where = [];
            $params = [];
            if (!empty($filters['user_id'])) {
                $where[] = '(s.client_user_id = :user_id OR s.entrepreneur_user_id = :user_id)';
                $params['user_id'] = (int) $filters['user_id'];
            }
            if (!empty($filters['visible_only'])) {
                $where[] = 'r.visible_to_client = 1';
            }
            if (!empty($filters['subscription_id'])) {
                $where[] = 'r.subscription_id = :subscription_id';
                $params['subscription_id'] = (int) $filters['subscription_id'];
            }
            $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $stmt = $this->db->prepare(
                "SELECT r.*, p.name AS plan_name,
                        COALESCE(cui.apodo, cui.nombre, cua.correo, eui.apodo, eui.nombre, eua.correo) AS user_label
                 FROM marketing_reports r
                 INNER JOIN marketing_plan_subscriptions s ON s.id = r.subscription_id
                 INNER JOIN marketing_plans p ON p.id = s.plan_id
                 LEFT JOIN user_auth cua ON cua.id = s.client_user_id
                 LEFT JOIN user_info cui ON cui.user_auth_id = cua.id
                 LEFT JOIN user_auth eua ON eua.id = s.entrepreneur_user_id
                 LEFT JOIN user_info eui ON eui.user_auth_id = eua.id
                 $whereSql
                 ORDER BY r.period_end DESC, r.id DESC"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function guardarReporte(array $data, int $userId): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO marketing_reports
             (subscription_id, period_start, period_end, title, summary, conclusions, next_actions, visible_to_client, created_by_user_id, created_at, updated_at)
             VALUES (:subscription_id, :period_start, :period_end, :title, :summary, :conclusions, :next_actions, :visible_to_client, :created_by_user_id, NOW(), NOW())"
        );
        $stmt->execute([
            'subscription_id' => (int) ($data['subscription_id'] ?? 0),
            'period_start' => $this->nullableDate($data['period_start'] ?? null),
            'period_end' => $this->nullableDate($data['period_end'] ?? null),
            'title' => trim((string) ($data['title'] ?? '')),
            'summary' => trim((string) ($data['summary'] ?? '')),
            'conclusions' => trim((string) ($data['conclusions'] ?? '')),
            'next_actions' => trim((string) ($data['next_actions'] ?? '')),
            'visible_to_client' => isset($data['visible_to_client']) ? 1 : 0,
            'created_by_user_id' => $userId,
        ]);
    }

    public function procesarCsvMeta(string $tmpPath, string $originalName, int $userId): array
    {
        $required = ['Inicio del informe', 'Fin del informe', 'Nombre de la campana', 'Nombre de la campaña', 'Importe gastado (ARS)', 'Impresiones', 'Alcance'];
        $handle = fopen($tmpPath, 'rb');
        if (!$handle) {
            return ['ok' => false, 'error' => 'No se pudo leer el archivo.'];
        }

        $headers = fgetcsv($handle, 0, ',');
        if (!$headers) {
            fclose($handle);
            return ['ok' => false, 'error' => 'El CSV no tiene encabezados.'];
        }
        $headers = array_map(static fn($h) => trim((string) $h), $headers);
        $campaignHeader = in_array('Nombre de la campaña', $headers, true) ? 'Nombre de la campaña' : 'Nombre de la campana';
        foreach (['Inicio del informe', 'Fin del informe', $campaignHeader, 'Importe gastado (ARS)', 'Impresiones', 'Alcance'] as $column) {
            if (!in_array($column, $headers, true)) {
                fclose($handle);
                return ['ok' => false, 'error' => 'Falta la columna obligatoria: ' . $column];
            }
        }

        $uploadDir = __DIR__ . '/../uploads/marketing_imports';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        $storedName = date('Ymd_His') . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
        $storedPath = $uploadDir . '/' . $storedName;
        copy($tmpPath, $storedPath);

        $batchStmt = $this->db->prepare(
            "INSERT INTO marketing_import_batches
             (platform, uploaded_by_user_id, original_filename, stored_file_path, file_type, total_rows, imported_rows, skipped_rows, unresolved_rows, status, created_at, updated_at)
             VALUES ('meta_ads', :uploaded_by_user_id, :original_filename, :stored_file_path, 'csv', 0, 0, 0, 0, 'uploaded', NOW(), NOW())"
        );
        $batchStmt->execute([
            'uploaded_by_user_id' => $userId,
            'original_filename' => $originalName,
            'stored_file_path' => 'uploads/marketing_imports/' . $storedName,
        ]);
        $batchId = (int) $this->db->lastInsertId();

        $total = 0;
        $imported = 0;
        $unresolved = 0;
        $skipped = 0;
        $summary = ['spent' => 0.0, 'impressions' => 0, 'reach' => 0, 'conversations' => 0, 'purchases' => 0];
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $total++;
            $assoc = [];
            foreach ($headers as $i => $header) {
                $assoc[$header] = $row[$i] ?? null;
            }
            $campaignName = trim((string) ($assoc[$campaignHeader] ?? ''));
            $match = $this->detectarCampaniaExterna($campaignName);
            $status = empty($match['campaign_id']) ? 'unresolved' : 'imported';
            if ($status === 'unresolved') {
                $unresolved++;
            } else {
                $inserted = $this->insertarMetricaMeta($batchId, (int) $match['campaign_id'], $assoc, $campaignHeader);
                $inserted ? $imported++ : $skipped++;
                $summary['spent'] += (float) $this->parseNumber($assoc['Importe gastado (ARS)'] ?? 0);
                $summary['impressions'] += (int) $this->parseNumber($assoc['Impresiones'] ?? 0);
                $summary['reach'] += (int) $this->parseNumber($assoc['Alcance'] ?? 0);
                $summary['conversations'] += (int) $this->parseNumber($assoc['Conversaciones con mensajes iniciadas'] ?? 0);
                $summary['purchases'] += (int) $this->parseNumber($assoc['Compras'] ?? 0);
            }
            $this->guardarFilaImport($batchId, $total, $campaignName, $match, $status, $assoc);
        }
        fclose($handle);

        $status = $unresolved > 0 ? ($imported > 0 ? 'partial' : 'previewed') : 'imported';
        $update = $this->db->prepare(
            "UPDATE marketing_import_batches
             SET total_rows=:total_rows, imported_rows=:imported_rows, skipped_rows=:skipped_rows, unresolved_rows=:unresolved_rows, status=:status, updated_at=NOW()
             WHERE id=:id"
        );
        $update->execute([
            'id' => $batchId,
            'total_rows' => $total,
            'imported_rows' => $imported,
            'skipped_rows' => $skipped,
            'unresolved_rows' => $unresolved,
            'status' => $status,
        ]);

        return ['ok' => true, 'batch_id' => $batchId, 'total' => $total, 'imported' => $imported, 'skipped' => $skipped, 'unresolved' => $unresolved, 'summary' => $summary];
    }

    public function detectarCampaniaExterna(string $externalName): array
    {
        $name = trim($externalName);
        if ($name === '') {
            return [];
        }
        $mapping = $this->fetchOne(
            "SELECT * FROM marketing_external_campaign_mappings WHERE platform = 'meta_ads' AND external_campaign_name = :name LIMIT 1",
            ['name' => $name]
        );
        if (!empty($mapping['internal_campaign_id'])) {
            return ['campaign_id' => (int) $mapping['internal_campaign_id'], 'rule' => 'manual_assignment', 'confidence' => 100];
        }
        $campaign = $this->fetchOne(
            "SELECT id FROM marketing_campaigns WHERE recommended_meta_campaign_name = :name OR external_campaign_name = :name LIMIT 1",
            ['name' => $name]
        );
        if (!empty($campaign)) {
            return ['campaign_id' => (int) $campaign['id'], 'rule' => 'recommended_name_match', 'confidence' => 100];
        }
        $codes = $this->fetchAll("SELECT user_auth_id, client_code FROM marketing_client_codes ORDER BY LENGTH(client_code) DESC", []);
        foreach ($codes as $code) {
            if (stripos($name, (string) $code['client_code']) !== false) {
                $campaign = $this->fetchOne(
                    "SELECT id FROM marketing_campaigns WHERE client_user_id = :uid OR entrepreneur_user_id = :uid ORDER BY updated_at DESC LIMIT 1",
                    ['uid' => (int) $code['user_auth_id']]
                );
                if (!empty($campaign)) {
                    return ['campaign_id' => (int) $campaign['id'], 'rule' => 'client_code_match', 'confidence' => 70];
                }
            }
        }
        return [];
    }

    private function insertarMetricaMeta(int $batchId, int $campaignId, array $row, string $campaignHeader): bool
    {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO marketing_campaign_metrics
             (campaign_id, import_batch_id, report_start_date, report_end_date, campaign_name, campaign_delivery,
              results, result_indicator, cost_per_result, adset_budget, adset_budget_type, amount_spent_ars,
              impressions, reach, campaign_end_date, attribution_setting, total_message_contacts, new_message_contacts,
              purchases, cost_per_purchase_ars, messaging_conversations_started, cost_per_messaging_conversation_started_ars,
              source, created_at, updated_at)
             VALUES (:campaign_id, :import_batch_id, :report_start_date, :report_end_date, :campaign_name, :campaign_delivery,
              :results, :result_indicator, :cost_per_result, :adset_budget, :adset_budget_type, :amount_spent_ars,
              :impressions, :reach, :campaign_end_date, :attribution_setting, :total_message_contacts, :new_message_contacts,
              :purchases, :cost_per_purchase_ars, :messaging_conversations_started, :cost_per_messaging_conversation_started_ars,
              'meta_csv', NOW(), NOW())"
        );
        $stmt->execute([
            'campaign_id' => $campaignId,
            'import_batch_id' => $batchId,
            'report_start_date' => $this->parseDate($row['Inicio del informe'] ?? null),
            'report_end_date' => $this->parseDate($row['Fin del informe'] ?? null),
            'campaign_name' => trim((string) ($row[$campaignHeader] ?? '')),
            'campaign_delivery' => trim((string) ($row['Entrega de la campaña'] ?? '')),
            'results' => $this->parseNumber($row['Resultados'] ?? null),
            'result_indicator' => trim((string) ($row['Indicador de resultado'] ?? '')),
            'cost_per_result' => $this->parseNumber($row['Costo por resultados'] ?? null),
            'adset_budget' => $this->parseNumber($row['Presupuesto del conjunto de anuncios'] ?? null),
            'adset_budget_type' => trim((string) ($row['Tipo de presupuesto del conjunto de anuncios'] ?? '')),
            'amount_spent_ars' => $this->parseNumber($row['Importe gastado (ARS)'] ?? null),
            'impressions' => (int) $this->parseNumber($row['Impresiones'] ?? 0),
            'reach' => (int) $this->parseNumber($row['Alcance'] ?? 0),
            'campaign_end_date' => $this->parseDate($row['Finalización'] ?? null),
            'attribution_setting' => trim((string) ($row['Configuración de atribución'] ?? '')),
            'total_message_contacts' => (int) $this->parseNumber($row['Contactos de mensajes totales'] ?? 0),
            'new_message_contacts' => (int) $this->parseNumber($row['Nuevos contactos de mensajes'] ?? 0),
            'purchases' => (int) $this->parseNumber($row['Compras'] ?? 0),
            'cost_per_purchase_ars' => $this->parseNumber($row['Costo por compra (ARS)'] ?? null),
            'messaging_conversations_started' => (int) $this->parseNumber($row['Conversaciones con mensajes iniciadas'] ?? 0),
            'cost_per_messaging_conversation_started_ars' => $this->parseNumber($row['Costo por conversación con mensajes iniciada (ARS)'] ?? null),
        ]);
        return $stmt->rowCount() > 0;
    }

    private function guardarFilaImport(int $batchId, int $rowNumber, string $campaignName, array $match, string $status, array $raw): void
    {
        $campaign = !empty($match['campaign_id']) ? $this->obtenerCampania((int) $match['campaign_id']) : [];
        $stmt = $this->db->prepare(
            "INSERT INTO marketing_import_rows
             (import_batch_id, csv_row_number, external_campaign_name, internal_client_user_id, internal_entrepreneur_user_id,
              internal_subscription_id, internal_campaign_id, status, reason, raw_data_json, created_at)
             VALUES (:import_batch_id, :csv_row_number, :external_campaign_name, :internal_client_user_id, :internal_entrepreneur_user_id,
              :internal_subscription_id, :internal_campaign_id, :status, :reason, :raw_data_json, NOW())"
        );
        $stmt->execute([
            'import_batch_id' => $batchId,
            'csv_row_number' => $rowNumber,
            'external_campaign_name' => $campaignName,
            'internal_client_user_id' => $campaign['client_user_id'] ?? null,
            'internal_entrepreneur_user_id' => $campaign['entrepreneur_user_id'] ?? null,
            'internal_subscription_id' => $campaign['subscription_id'] ?? null,
            'internal_campaign_id' => $match['campaign_id'] ?? null,
            'status' => $status,
            'reason' => $match['rule'] ?? 'unresolved',
            'raw_data_json' => json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function guardarMappingManual(array $data, int $userId): void
    {
        $campaign = $this->obtenerCampania((int) ($data['internal_campaign_id'] ?? 0));
        $stmt = $this->db->prepare(
            "INSERT INTO marketing_external_campaign_mappings
             (platform, external_campaign_name, internal_client_user_id, internal_entrepreneur_user_id, internal_subscription_id,
              internal_campaign_id, detection_rule, confidence_score, created_by_user_id, created_at, updated_at)
             VALUES ('meta_ads', :external_campaign_name, :internal_client_user_id, :internal_entrepreneur_user_id, :internal_subscription_id,
              :internal_campaign_id, 'manual_assignment', 100, :created_by_user_id, NOW(), NOW())
             ON DUPLICATE KEY UPDATE internal_client_user_id=VALUES(internal_client_user_id),
              internal_entrepreneur_user_id=VALUES(internal_entrepreneur_user_id), internal_subscription_id=VALUES(internal_subscription_id),
              internal_campaign_id=VALUES(internal_campaign_id), detection_rule='manual_assignment', confidence_score=100, updated_at=NOW()"
        );
        $stmt->execute([
            'external_campaign_name' => trim((string) ($data['external_campaign_name'] ?? '')),
            'internal_client_user_id' => $campaign['client_user_id'] ?? null,
            'internal_entrepreneur_user_id' => $campaign['entrepreneur_user_id'] ?? null,
            'internal_subscription_id' => $campaign['subscription_id'] ?? null,
            'internal_campaign_id' => $campaign['id'] ?? null,
            'created_by_user_id' => $userId,
        ]);
    }

    public function listarImportaciones(): array
    {
        return $this->fetchAll("SELECT * FROM marketing_import_batches ORDER BY created_at DESC, id DESC LIMIT 30", []);
    }

    public function listarFilasSinResolver(): array
    {
        return $this->fetchAll(
            "SELECT * FROM marketing_import_rows WHERE status = 'unresolved' ORDER BY created_at DESC, id DESC LIMIT 50",
            []
        );
    }

    private function fetchOne(string $sql, array $params): array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    private function fetchAll(string $sql, array $params): array
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    private function nullableInt($value): ?int
    {
        return trim((string) $value) === '' ? null : (int) $value;
    }

    private function nullableDecimal($value): ?float
    {
        return trim((string) $value) === '' ? null : (float) str_replace(',', '.', (string) $value);
    }

    private function nullableDate($value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function parseDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '--') {
            return null;
        }
        $value = str_replace('/', '-', $value);
        $time = strtotime($value);
        return $time ? date('Y-m-d', $time) : null;
    }

    private function parseNumber($value): float
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '--') {
            return 0.0;
        }
        $value = str_replace(['$', 'ARS', ' '], '', $value);
        if (substr_count($value, ',') === 1 && substr_count($value, '.') >= 1) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (substr_count($value, ',') === 1) {
            $value = str_replace(',', '.', $value);
        }
        return (float) $value;
    }

    private function slugify($value): string
    {
        $value = strtolower($this->normalizarCodigo((string) $value));
        $value = str_replace('_', '-', $value);
        return trim($value, '-') ?: 'plan';
    }

    private function normalizarCodigo(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $value));
        return trim((string) $value, '-');
    }

    private function normalizarObjetivo(string $value): string
    {
        $value = $this->normalizarCodigo($value);
        $allowed = ['MENSAJES', 'LEADS', 'TRAFICO', 'VENTAS', 'REMARKETING', 'ALCANCE', 'OTRO'];
        return in_array($value, $allowed, true) ? $value : 'OTRO';
    }
}
