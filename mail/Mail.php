<?php

declare(strict_types=1);

namespace SVE\Mail;

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/lib/PHPMailer.php';
require_once __DIR__ . '/lib/SMTP.php';
require_once __DIR__ . '/lib/Exception.php';

final class Mailer
{
    // -------------------------------------------------------------------------
    // Infraestructura privada
    // -------------------------------------------------------------------------

    private static function logEmail(array $data): void
    {
        try {
            global $pdo;
            if (!($pdo instanceof \PDO)) {
                return;
            }

            $meta = isset($data['meta']) ? json_encode($data['meta']) : null;

            $stmt = $pdo->prepare(
                "INSERT INTO correos_log
                    (user_auth_id, correo, asunto, template, mensaje_html, mensaje_text, estado, error, meta)
                 VALUES
                    (:user_auth_id, :correo, :asunto, :template, :mensaje_html, :mensaje_text, :estado, :error, :meta)"
            );

            $stmt->execute([
                'user_auth_id' => $data['user_auth_id'] ?? null,
                'correo'       => $data['correo']       ?? '',
                'asunto'       => $data['asunto']       ?? '',
                'template'     => $data['template']     ?? null,
                'mensaje_html' => $data['mensaje_html'] ?? null,
                'mensaje_text' => $data['mensaje_text'] ?? null,
                'estado'       => $data['estado']       ?? 'fallido',
                'error'        => $data['error']        ?? null,
                'meta'         => $meta,
            ]);
        } catch (\Throwable) {
            // No interrumpir el flujo principal si falla el log.
        }
    }

    private static function baseMailer(?array &$debugLog = null): PHPMailer
    {
        $host   = getenv('SMTP_HOST')     ?: '';
        $user   = getenv('SMTP_USERNAME') ?: '';
        $pass   = getenv('SMTP_PASSWORD') ?: '';
        $port   = (int)(getenv('SMTP_PORT')   ?: 0);
        $secure = getenv('SMTP_SECURE')   ?: '';

        if ($host === '' || $user === '' || $pass === '') {
            throw new \RuntimeException('Configuración SMTP incompleta.');
        }

        if ($secure === '') {
            $secure = $port === 465 ? 'ssl' : 'tls';
        }
        if ($port <= 0) {
            $port = $secure === 'ssl' ? 465 : 587;
        }

        $from     = getenv('MAIL_FROM')      ?: $user;
        $fromName = getenv('MAIL_FROM_NAME') ?: 'Impulsa';

        $m = new PHPMailer(true);
        $m->isSMTP();
        $m->Host      = $host;
        $m->SMTPAuth  = true;
        $m->Username  = $user;
        $m->Password  = $pass;

        if ($secure === 'ssl') {
            $m->SMTPSecure  = PHPMailer::ENCRYPTION_SMTPS;
            $m->SMTPAutoTLS = false;
        } else {
            $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $m->Port      = $port;
        $m->CharSet   = 'UTF-8';
        $m->Encoding  = 'base64';
        $m->setFrom($from, $fromName);
        $m->Sender    = $from;
        $m->addReplyTo($from, $fromName);
        $m->isHTML(true);

        if ($debugLog !== null) {
            $m->SMTPDebug   = 2;
            $m->Debugoutput = function (string $str) use (&$debugLog): void {
                $line = trim($str);
                if (stripos($line, 'CLIENT -> SERVER: AUTH') === 0) {
                    $debugLog[] = 'CLIENT -> SERVER: AUTH [redacted]';
                    return;
                }
                if (preg_match('/^CLIENT -> SERVER: [A-Za-z0-9+\\/=]+$/', $line) === 1) {
                    $debugLog[] = 'CLIENT -> SERVER: [redacted]';
                    return;
                }
                $debugLog[] = $line;
            };
        }

        return $m;
    }

    // -------------------------------------------------------------------------
    // Métodos de envío
    // -------------------------------------------------------------------------

    /**
     * Envía el correo de verificación de dirección de email al registrarse.
     *
     * $data = [
     *   'correo'       => string,   // destino
     *   'link'         => string,   // URL con el token de verificación
     *   'user_auth_id' => int|null  // para el log (opcional)
     * ]
     *
     * @return array{ok: bool, error?: string}
     */
    public static function enviarVerificacionCorreo(array $data): array
    {
        $debugLog = [];
        $mail     = null;
        $html     = '';

        try {
            $tplPath = __DIR__ . '/template/verificacion_correo.html';
            if (!is_file($tplPath)) {
                throw new \RuntimeException('Template verificacion_correo.html no encontrado.');
            }

            $correo = (string)($data['correo'] ?? '');
            $link   = (string)($data['link']   ?? '');

            if ($correo === '' || $link === '') {
                throw new \InvalidArgumentException('correo y link son obligatorios.');
            }

            $tpl  = (string)file_get_contents($tplPath);
            $html = str_replace(
                ['{{title}}', '{{correo}}', '{{link}}'],
                [
                    'Verificá tu correo — Impulsa',
                    htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($link,   ENT_QUOTES, 'UTF-8'),
                ],
                $tpl
            );

            $mail          = self::baseMailer($debugLog);
            $subject       = 'Verificá tu dirección de correo — Impulsa';
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = "Verificá tu correo en Impulsa.\n\nUsá este enlace: {$link}\n\nSi no creaste una cuenta, ignorá este mensaje.";
            $mail->addAddress($correo);

            $mail->send();

            self::logEmail([
                'user_auth_id' => $data['user_auth_id'] ?? null,
                'correo'       => $correo,
                'asunto'       => $subject,
                'template'     => 'verificacion_correo',
                'mensaje_html' => $html,
                'mensaje_text' => $mail->AltBody,
                'estado'       => 'enviado',
            ]);

            return ['ok' => true];

        } catch (\Throwable $e) {
            $mailError = $mail instanceof PHPMailer ? trim((string)$mail->ErrorInfo) : '';
            $debugText = '';
            if (!empty($debugLog)) {
                $debugText = ' SMTP Log: ' . implode(' | ', array_slice($debugLog, -10));
            }

            $errorMsg = $e->getMessage();
            if ($mailError !== '' && stripos($errorMsg, $mailError) === false) {
                $errorMsg .= ' | ErrorInfo: ' . $mailError;
            }

            self::logEmail([
                'user_auth_id' => $data['user_auth_id'] ?? null,
                'correo'       => $data['correo'] ?? '',
                'asunto'       => 'Verificá tu dirección de correo — Impulsa',
                'template'     => 'verificacion_correo',
                'mensaje_html' => $html ?: null,
                'mensaje_text' => $mail instanceof PHPMailer ? ($mail->AltBody ?? null) : null,
                'estado'       => 'fallido',
                'error'        => $errorMsg . $debugText,
            ]);

            return ['ok' => false, 'error' => $errorMsg . $debugText];
        }
    }

    /**
     * @return array{ok: bool, error?: string}
     */
    public static function enviarSolicitudNuevoProyecto(array $data): array
    {
        $debugLog = [];
        $mail = null;
        $html = '';

        $mapQ5 = [
            'pagina_web' => 'Pagina web',
            'sistema_web_interno' => 'Sistema web interno',
            'app_mobile' => 'App mobile',
            'plataforma_web_app_mobile' => 'Plataforma web + app mobile',
            'no_se' => 'No estoy seguro/a',
        ];
        $mapQ6 = ['si' => 'Si', 'no' => 'No', 'no_se' => 'No lo se'];
        $mapQ7 = [
            'solo_equipo' => 'Solo mi equipo',
            'clientes' => 'Clientes',
            'proveedores' => 'Proveedores',
            'roles_diferentes' => 'Diferentes tipos de usuarios',
            'no_se' => 'No lo se todavia',
        ];
        $mapQ12 = ['claro' => 'Si, bastante claro', 'medio' => 'Mas o menos', 'no' => 'Todavia no'];
        $mapQ14 = ['completa' => 'Si, ya tengo todo', 'parcial' => 'Tengo algo, pero falta', 'no' => 'No, lo necesito tambien'];
        $mapQ15 = [
            'cuanto_antes' => 'Lo quiero empezar cuanto antes',
            '1_2_meses' => 'En 1 a 2 meses',
            'mas_adelante' => 'Mas adelante',
            'explorando' => 'Solo estoy explorando opciones',
        ];
        $mapQ16 = [
            'sin_definir' => 'Todavia no',
            'menos_1000000' => 'Menos de $1.000.000',
            'entre_1000000_2000000' => 'Entre $1.000.000 y $2.000.000',
            'mas_2000000' => 'Mas de $2.000.000',
        ];
        $mapQ17 = [
            'por_etapas' => 'Por etapas',
            'todo_junto' => 'Todo junto',
            'necesito_recomendacion' => 'Necesito recomendacion',
        ];
        $mapQ9 = [
            'registro_login' => 'Registro e inicio de sesion',
            'perfil_usuario' => 'Perfil de usuario',
            'panel_admin' => 'Panel de administracion',
            'carga_edicion_datos' => 'Carga y edicion de datos',
            'busqueda_filtros' => 'Busqueda y filtros',
            'agenda_turnos' => 'Agenda o turnos',
            'pagos_online' => 'Pagos online',
            'notificaciones_email_whatsapp' => 'Notificaciones por mail o WhatsApp',
            'reportes_metricas' => 'Reportes o metricas',
            'subida_archivos_imagenes' => 'Subida de archivos o imagenes',
            'geolocalizacion_mapas' => 'Geolocalizacion o mapas',
            'integracion_sistemas' => 'Integracion con otros sistemas',
            'chat_mensajeria' => 'Chat o mensajeria',
            'no_se' => 'No estoy seguro/a',
        ];

        $correo = trim((string)($data['correo'] ?? ''));
        if ($correo === '') {
            return ['ok' => false, 'error' => 'Correo de destino vacio.'];
        }

        $q9 = [];
        if (isset($data['q9_funcionalidades']) && is_array($data['q9_funcionalidades'])) {
            $q9 = $data['q9_funcionalidades'];
        }
        $q9Label = [];
        foreach ($q9 as $item) {
            $item = (string)$item;
            $q9Label[] = $mapQ9[$item] ?? $item;
        }
        $q9Html = '';
        foreach ($q9Label as $label) {
            $q9Html .= '<li>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</li>';
        }

        $replace = [
            '{{title}}' => 'Solicitud nuevo proyecto',
            '{{correo}}' => htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'),
            '{{nombre}}' => htmlspecialchars((string)($data['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{nombre_proyecto}}' => htmlspecialchars((string)($data['nombre_proyecto'] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{whatsapp}}' => htmlspecialchars((string)($data['whatsapp'] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{q1_descripcion}}' => nl2br(htmlspecialchars((string)($data['q1_descripcion'] ?? ''), ENT_QUOTES, 'UTF-8')),
            '{{q2_problema}}' => nl2br(htmlspecialchars((string)($data['q2_problema'] ?? ''), ENT_QUOTES, 'UTF-8')),
            '{{q3_usuarios}}' => nl2br(htmlspecialchars((string)($data['q3_usuarios'] ?? ''), ENT_QUOTES, 'UTF-8')),
            '{{q4_resultado_ideal}}' => nl2br(htmlspecialchars((string)($data['q4_resultado_ideal'] ?? ''), ENT_QUOTES, 'UTF-8')),
            '{{q5_tipo_aplicacion}}' => htmlspecialchars((string)($mapQ5[(string)($data['q5_tipo_aplicacion'] ?? '')] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{q6_login}}' => htmlspecialchars((string)($mapQ6[(string)($data['q6_login'] ?? '')] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{q7_acceso}}' => htmlspecialchars((string)($mapQ7[(string)($data['q7_acceso'] ?? '')] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{q8_funciones_minimas}}' => nl2br(htmlspecialchars((string)($data['q8_funciones_minimas'] ?? ''), ENT_QUOTES, 'UTF-8')),
            '{{q9_funcionalidades}}' => $q9Html,
            '{{q10_admin_vs_usuario}}' => nl2br(htmlspecialchars((string)($data['q10_admin_vs_usuario'] ?? ''), ENT_QUOTES, 'UTF-8')),
            '{{q11_integraciones}}' => nl2br(htmlspecialchars((string)($data['q11_integraciones'] ?? ''), ENT_QUOTES, 'UTF-8')),
            '{{q12_contenido}}' => htmlspecialchars((string)($mapQ12[(string)($data['q12_contenido'] ?? '')] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{q13_referencias}}' => nl2br(htmlspecialchars((string)($data['q13_referencias'] ?? ''), ENT_QUOTES, 'UTF-8')),
            '{{q14_diseno}}' => htmlspecialchars((string)($mapQ14[(string)($data['q14_diseno'] ?? '')] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{q15_urgencia}}' => htmlspecialchars((string)($mapQ15[(string)($data['q15_urgencia'] ?? '')] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{q16_presupuesto}}' => htmlspecialchars((string)($mapQ16[(string)($data['q16_presupuesto'] ?? '')] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{q17_modalidad}}' => htmlspecialchars((string)($mapQ17[(string)($data['q17_modalidad'] ?? '')] ?? ''), ENT_QUOTES, 'UTF-8'),
            '{{q18_adicional}}' => nl2br(htmlspecialchars((string)($data['q18_adicional'] ?? ''), ENT_QUOTES, 'UTF-8')),
        ];

        try {
            $tplPath = __DIR__ . '/template/new_project.html';
            if (!is_file($tplPath)) {
                throw new \RuntimeException('Template new_project.html no encontrado.');
            }

            $tpl = (string)file_get_contents($tplPath);
            $html = strtr($tpl, $replace);

            $mail = self::baseMailer($debugLog);
            $subject = 'Solicitud nuevo proyecto';
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody =
                "Solicitud nuevo proyecto\n\n" .
                "Nombre: " . ($data['nombre'] ?? '') . "\n" .
                "Nombre del proyecto: " . ($data['nombre_proyecto'] ?? '') . "\n" .
                "Correo: " . $correo . "\n" .
                "WhatsApp: " . ($data['whatsapp'] ?? '') . "\n\n" .
                "Recibimos tu solicitud. En las proximas 72 horas habiles nos vamos a comunicar por correo o WhatsApp para coordinar una reunion y revisar alcance, metodologia de trabajo e inversion.";
            $mail->addAddress($correo);
            $mail->send();

            self::logEmail([
                'correo' => $correo,
                'asunto' => $subject,
                'template' => 'new_project',
                'mensaje_html' => $html,
                'mensaje_text' => $mail->AltBody,
                'estado' => 'enviado',
            ]);

            return ['ok' => true];
        } catch (\Throwable $e) {
            $mailError = $mail instanceof PHPMailer ? trim((string)$mail->ErrorInfo) : '';
            $debugText = '';
            if (!empty($debugLog)) {
                $debugText = ' SMTP Log: ' . implode(' | ', array_slice($debugLog, -10));
            }

            $errorMsg = $e->getMessage();
            if ($mailError !== '' && stripos($errorMsg, $mailError) === false) {
                $errorMsg .= ' | ErrorInfo: ' . $mailError;
            }

            self::logEmail([
                'correo' => $correo,
                'asunto' => 'Solicitud nuevo proyecto',
                'template' => 'new_project',
                'mensaje_html' => $html ?: null,
                'mensaje_text' => $mail instanceof PHPMailer ? ($mail->AltBody ?? null) : null,
                'estado' => 'fallido',
                'error' => $errorMsg . $debugText,
            ]);

            return ['ok' => false, 'error' => $errorMsg . $debugText];
        }
    }
}
