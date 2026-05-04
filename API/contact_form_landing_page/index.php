<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

const ENV_PATH = __DIR__ . '/../../.env';
const ALLOWED_DOMAINS_PATH = __DIR__ . '/allowed-domains.txt';

try {
    cargarEnv(ENV_PATH);
    configurarCors(ALLOWED_DOMAINS_PATH);

    $metodo = $_SERVER['REQUEST_METHOD'] ?? '';

    if ($metodo === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    if ($metodo !== 'POST') {
        responderJson(405, false, 'Metodo no permitido. Usa POST.');
    }

    validarApiKey();
    validarContentTypeJson();

    $payload = obtenerPayloadJson();
    $datos = validarPayload($payload);

    $pdo = crearConexionPdo();
    insertarConsulta($pdo, $datos);

    responderJson(201, true, 'Consulta enviada correctamente');
} catch (InvalidArgumentException $exception) {
    responderJson(422, false, $exception->getMessage());
} catch (RuntimeException $exception) {
    $codigo = $exception->getCode();
    $codigoHttp = ($codigo >= 400 && $codigo <= 599) ? $codigo : 500;

    if ($codigoHttp >= 500) {
        error_log('Error interno en API/contact_form_landing_page/index.php: ' . $exception->getMessage());
        responderJson(500, false, 'Error interno del servidor');
    }

    responderJson($codigoHttp, false, $exception->getMessage());
} catch (Throwable $exception) {
    error_log('Error no controlado en API/contact_form_landing_page/index.php: ' . $exception->getMessage());
    responderJson(500, false, 'Error interno del servidor');
}

/**
 * Carga variables simples KEY=VALUE desde el archivo .env.
 */
function cargarEnv(string $ruta): void
{
    if (!is_file($ruta) || !is_readable($ruta)) {
        throw new RuntimeException('Configuracion del servidor no disponible.', 500);
    }

    $lineas = file($ruta, FILE_IGNORE_NEW_LINES);

    if ($lineas === false) {
        throw new RuntimeException('No se pudo leer la configuracion del servidor.', 500);
    }

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if ($linea === '' || strpos($linea, '#') === 0 || strpos($linea, '=') === false) {
            continue;
        }

        [$clave, $valor] = explode('=', $linea, 2);
        $clave = trim($clave);
        $valor = trim($valor);
        $valor = trim($valor, "\"'");

        if ($clave === '') {
            continue;
        }

        putenv($clave . '=' . $valor);
        $_ENV[$clave] = $valor;
        $_SERVER[$clave] = $valor;
    }
}

/**
 * Configura CORS usando coincidencia exacta contra allowed-domains.txt.
 */
function configurarCors(string $rutaDominios): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $dominiosPermitidos = cargarDominiosPermitidos($rutaDominios);

    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-API-KEY');
    header('Access-Control-Max-Age: 600');

    if ($origin === '') {
        return;
    }

    if (!in_array($origin, $dominiosPermitidos, true)) {
        responderJson(403, false, 'Origin no permitido');
    }

    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}

/**
 * Lee dominios permitidos, ignorando comentarios y lineas vacias.
 *
 * @return array<int, string>
 */
function cargarDominiosPermitidos(string $ruta): array
{
    if (!is_file($ruta) || !is_readable($ruta)) {
        throw new RuntimeException('Configuracion de dominios permitidos no disponible.', 500);
    }

    $lineas = file($ruta, FILE_IGNORE_NEW_LINES);

    if ($lineas === false) {
        throw new RuntimeException('No se pudo leer la configuracion de dominios permitidos.', 500);
    }

    $dominios = [];

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if ($linea === '' || strpos($linea, '#') === 0) {
            continue;
        }

        $dominios[] = $linea;
    }

    return array_values(array_unique($dominios));
}

/**
 * Valida X-API-KEY contra API_KEY del .env usando comparacion segura.
 */
function validarApiKey(): void
{
    $apiKeyEsperada = obtenerEnv('API_KEY');
    $apiKeyRecibida = $_SERVER['HTTP_X_API_KEY'] ?? '';

    if ($apiKeyEsperada === '' || $apiKeyRecibida === '') {
        throw new RuntimeException('API key invalida o ausente', 401);
    }

    if (!hash_equals($apiKeyEsperada, $apiKeyRecibida)) {
        throw new RuntimeException('API key invalida o ausente', 401);
    }
}

/**
 * Acepta solo JSON para solicitudes POST.
 */
function validarContentTypeJson(): void
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

    if (stripos($contentType, 'application/json') === false) {
        throw new RuntimeException('Content-Type invalido. Usa application/json.', 400);
    }
}

/**
 * Decodifica el cuerpo JSON de la solicitud.
 *
 * @return array<string, mixed>
 */
function obtenerPayloadJson(): array
{
    $body = file_get_contents('php://input');

    if ($body === false || trim($body) === '') {
        throw new RuntimeException('JSON invalido', 400);
    }

    $payload = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
        throw new RuntimeException('JSON invalido', 400);
    }

    return $payload;
}

/**
 * Valida y normaliza solo los campos permitidos para insertar.
 *
 * @param array<string, mixed> $payload
 * @return array<string, string|null>
 */
function validarPayload(array $payload): array
{
    $page = obtenerTexto($payload, 'page', true, 150);
    $contactNombre = obtenerTexto($payload, 'contact_nombre', true, 150);
    $contactWhatsapp = obtenerTexto($payload, 'contact_whatsapp', false, 50);
    $contactEmail = obtenerTexto($payload, 'contact_email', false, 150);
    $contactDescription = obtenerTexto($payload, 'contact_description', false, null);
    $contactConsultation = obtenerTexto($payload, 'contact_consultation', false, 255);

    if ($contactEmail !== null && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('El campo contact_email debe ser un email valido.');
    }

    // Futuras mejoras: honeypot anti-spam y validaciones especificas por tipo de landing.
    return [
        'page' => $page,
        'contact_nombre' => $contactNombre,
        'contact_whatsapp' => $contactWhatsapp,
        'contact_email' => $contactEmail,
        'contact_description' => $contactDescription,
        'contact_consultation' => $contactConsultation,
    ];
}

/**
 * Obtiene un campo string, aplica trim y valida longitud maxima si corresponde.
 *
 * @param array<string, mixed> $payload
 */
function obtenerTexto(array $payload, string $campo, bool $requerido, ?int $maximo): ?string
{
    if (!array_key_exists($campo, $payload) || $payload[$campo] === null) {
        if ($requerido) {
            throw new InvalidArgumentException('El campo ' . $campo . ' es obligatorio.');
        }

        return null;
    }

    if (!is_string($payload[$campo])) {
        throw new InvalidArgumentException('El campo ' . $campo . ' debe ser texto.');
    }

    $valor = trim($payload[$campo]);

    if ($valor === '') {
        if ($requerido) {
            throw new InvalidArgumentException('El campo ' . $campo . ' es obligatorio.');
        }

        return null;
    }

    if ($maximo !== null && longitudTexto($valor) > $maximo) {
        throw new InvalidArgumentException('El campo ' . $campo . ' no puede superar ' . $maximo . ' caracteres.');
    }

    return $valor;
}

function longitudTexto(string $texto): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($texto, 'UTF-8');
    }

    return strlen($texto);
}

function crearConexionPdo(): PDO
{
    $host = obtenerEnv('DB_HOST');
    $puerto = obtenerEnv('DB_PORT', '');
    $base = obtenerEnv('DB_NAME');
    $usuario = obtenerEnv('DB_USER');
    $password = obtenerEnv('DB_PASS', obtenerEnv('DB_PASSWORD', ''));

    if ($host === '' || $base === '' || $usuario === '') {
        throw new RuntimeException('Configuracion de base de datos incompleta.', 500);
    }

    if (strpos($host, ':') !== false) {
        [$hostSinPuerto, $puertoEnHost] = explode(':', $host, 2);
        $host = $hostSinPuerto;
        $puerto = $puerto !== '' ? $puerto : $puertoEnHost;
    }

    if ($puerto === '') {
        $puerto = '3306';
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $puerto, $base);

    try {
        return new PDO($dsn, $usuario, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        error_log('Error conectando a MySQL en API/contact_form_landing_page/index.php: ' . $exception->getMessage());
        throw new RuntimeException('Error interno del servidor', 500);
    }
}

/**
 * Inserta exclusivamente una consulta nueva. No hay lectura, edicion, borrado ni listado.
 *
 * @param array<string, string|null> $datos
 */
function insertarConsulta(PDO $pdo, array $datos): void
{
    // Futuras mejoras: rate limiting por IP, validacion adicional por Referer y rotacion de API key.
    $sql = 'INSERT INTO forms_clients_contact
        (page, contact_nombre, contact_whatsapp, contact_email, contact_description, contact_consultation, state)
        VALUES
        (:page, :contact_nombre, :contact_whatsapp, :contact_email, :contact_description, :contact_consultation, :state)';

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':page' => $datos['page'],
            ':contact_nombre' => $datos['contact_nombre'],
            ':contact_whatsapp' => $datos['contact_whatsapp'],
            ':contact_email' => $datos['contact_email'],
            ':contact_description' => $datos['contact_description'],
            ':contact_consultation' => $datos['contact_consultation'],
            ':state' => 'recibido',
        ]);
    } catch (PDOException $exception) {
        error_log('Error insertando consulta en forms_clients_contact: ' . $exception->getMessage());
        throw new RuntimeException('Error interno del servidor', 500);
    }
}

function obtenerEnv(string $clave, string $default = ''): string
{
    $valor = getenv($clave);

    if ($valor === false) {
        return $default;
    }

    return (string) $valor;
}

function responderJson(int $codigoHttp, bool $success, string $mensaje): void
{
    http_response_code($codigoHttp);
    echo json_encode([
        'success' => $success,
        'message' => $mensaje,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
