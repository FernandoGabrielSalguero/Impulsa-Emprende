<?php
require_once __DIR__ . '/../../controllers/emprendedor_buyerPersonaController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Emprendedor';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');

$val = function (string $key, string $fallback = '') use ($buyerPersona): string {
    return htmlspecialchars((string) ($buyerPersona[$key] ?? $fallback), ENT_QUOTES, 'UTF-8');
};

$buyerPersonaGuardado = $val('buyer_persona_estructura');
$buyerPersonaCompletado = !empty($buyerPersona['completado']);

$questions = [
    'cliente_ideal' => '¿Quién es mi cliente ideal?',
    'edad_etapa_vida' => '¿Qué edad tiene y en qué etapa de su vida está?',
    'ocupacion_realidad_diaria' => '¿A qué se dedica y cómo es su realidad diaria?',
    'problema_necesidad' => '¿Qué problema o necesidad concreta tiene?',
    'preocupacion_frustracion' => '¿Qué le preocupa o frustra hoy en relación con ese problema?',
    'objetivo_mejora' => '¿Qué quiere lograr o mejorar?',
    'motivacion_busqueda' => '¿Qué lo motiva a buscar una solución?',
    'freno_dudas' => '¿Qué lo frena o le genera dudas antes de comprar?',
    'criterio_eleccion' => '¿Qué valora más al momento de elegir: precio, calidad, rapidez, confianza, atención u otra cosa?',
    'busqueda_informacion' => '¿Cómo busca información antes de decidir?',
    'decision_compra' => '¿Cómo toma la decisión de compra?',
    'motivo_eleccion' => '¿Por qué elegiría mi propuesta y no otra?',
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Impulsa - Tu buyer persona</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../../assets/framework/framework.css">
    <script src="../../assets/framework/framework.js" defer></script>

    <style>
        .navbar { justify-content: space-between; }
        .navbar-left { display: flex; align-items: center; gap: 8px; }
        .sidebar-brand-icon {
            width: 32px;
            height: 32px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .step-card {
            display: grid;
            gap: 24px;
        }
        .step-hero,
        .step-panel,
        .buyer-form-card,
        .buyer-preview-card,
        .step-actions-card {
            background: #fff;
        }
        .step-hero {
            border-radius: 18px;
            padding: 28px;
        }
        .step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 76px;
            height: 34px;
            padding: 0 12px;
            border-radius: 999px;
            background: #d97706;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .step-hero h1 {
            margin: 0 0 12px;
            font-size: 30px;
        }
        .step-hero p {
            margin: 0;
            max-width: 760px;
            color: #4b5563;
            line-height: 1.7;
        }
        .step-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 16px;
        }
        .step-panel {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .step-panel h3 {
            margin: 0 0 10px;
        }
        .step-panel p,
        .step-panel li {
            margin: 0;
            color: #6b7280;
            line-height: 1.65;
        }
        .step-panel ul {
            margin: 0;
            padding-left: 18px;
            display: grid;
            gap: 10px;
        }
        .questions-panel-list {
            overflow: hidden;
            max-height: 118px;
            transition: max-height 0.25s ease;
        }
        .questions-panel-list.is-expanded {
            max-height: 640px;
        }
        .questions-panel-actions {
            margin-top: auto;
            padding-top: 14px;
        }
        .questions-toggle-btn {
            border: 0;
            background: transparent;
            color: #b45309;
            font-size: 14px;
            font-weight: 700;
            padding: 0;
            cursor: pointer;
        }
        .questions-toggle-btn:hover {
            text-decoration: underline;
        }
        .buyer-form-card,
        .buyer-preview-card,
        .step-actions-card {
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }
        .buyer-form-card h2,
        .buyer-preview-card h2 {
            margin: 0 0 8px;
            font-size: 22px;
        }
        .buyer-form-card p,
        .buyer-preview-card p {
            margin: 0 0 18px;
            color: #6b7280;
            line-height: 1.65;
        }
        .buyer-fields {
            display: grid;
            gap: 16px;
        }
        .buyer-label-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }
        .buyer-label-row label {
            color: #374151;
            font-size: 14px;
            font-weight: 600;
            margin: 0;
        }
        .buyer-help-btn {
            width: 24px;
            height: 24px;
            border: 0;
            border-radius: 999px;
            background: #fde68a;
            color: #92400e;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }
        .buyer-field textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            box-sizing: border-box;
            outline: none;
        }
        .buyer-field textarea:focus {
            border-color: #d97706;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.12);
        }
        .buyer-preview-box {
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 20px;
        }
        .buyer-preview-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
            font-size: 12px;
            font-weight: 700;
            color: #b45309;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .buyer-preview-text {
            margin: 0;
            font-size: 16px;
            color: #111827;
            line-height: 1.8;
            white-space: pre-line;
        }
        .buyer-feedback {
            display: none;
            align-items: center;
            gap: 8px;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .buyer-feedback.ok {
            display: flex;
            background: #dcfce7;
            color: #15803d;
        }
        .buyer-feedback.error {
            display: flex;
            background: #fee2e2;
            color: #b91c1c;
        }
        .buyer-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }
        .buyer-status.is-complete {
            background: #dcfce7;
            color: #15803d;
        }
        .buyer-status.is-pending {
            background: #fef3c7;
            color: #b45309;
        }
        .step-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .sidebar-menu li.is-disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
        .buyer-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1000;
        }
        .buyer-modal-backdrop.is-open {
            display: flex;
        }
        .buyer-modal {
            width: min(100%, 620px);
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
        }
        .buyer-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }
        .buyer-modal-header h3 {
            margin: 0;
            font-size: 20px;
            color: #111827;
        }
        .buyer-modal-close {
            border: 0;
            background: transparent;
            color: #6b7280;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .buyer-modal-body {
            min-height: 140px;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 16px;
            color: #64748b;
            line-height: 1.6;
            background: #f8fafc;
        }
        .buyer-modal-text {
            margin: 0;
            font-size: 14px;
            white-space: pre-line;
        }

        .flow-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1100;
        }

        .flow-modal-backdrop.is-open {
            display: flex;
        }

        .flow-modal {
            width: min(100%, 540px);
            background: #fff;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
        }

        .flow-modal h3 {
            margin: 0 0 10px;
            font-size: 24px;
            color: #111827;
        }

        .flow-modal p {
            margin: 0 0 18px;
            color: #4b5563;
            line-height: 1.65;
        }

        .flow-modal-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        @media (max-width: 640px) {
            .step-hero h1 {
                font-size: 24px;
            }
            .step-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/institucionales/icons/Isotipo grande.png" alt="Impulsa Emprende" class="sidebar-brand-icon">
                <span class="logo-text">impulsa emprende</span>
            </div>
            <nav class="sidebar-menu">
                <ul>
                    <li onclick="location.href='emprendedor_dashboard.php'">
                        <span class="material-icons" style="color:#6366f1">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='emprendedor_mision.php'">
                        <span class="material-icons" style="color:#6366f1">track_changes</span>
                        <span class="link-text">Misión</span>
                    </li>
                    <li onclick="location.href='emprendedor_vision.php'">
                        <span class="material-icons" style="color:#6366f1">lightbulb</span>
                        <span class="link-text">Visión</span>
                    </li>
                    <li class="active" onclick="location.href='emprendedor_buyerPersona.php'">
                        <span class="material-icons" style="color:#6366f1">groups</span>
                        <span class="link-text">Buyer Persona</span>
                    </li>
                    <li class="<?= $landingDisponible ? '' : 'is-disabled' ?>" <?= $landingDisponible ? "onclick=\"location.href='landing_page_request.php'\"" : '' ?>>
                        <span class="material-icons" style="color:#6366f1">rocket_launch</span>
                        <span class="link-text">Landing Page</span>
                    </li>
                    <li onclick="location.href='../marketing/marketing_user.php'">
                        <span class="material-icons" style="color:#0f766e">campaign</span>
                        <span class="link-text">Marketing</span>
                    </li>
                    <li onclick="location.href='../../logout.php'">
                        <span class="material-icons" style="color:red">logout</span>
                        <span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <div class="main">
            <header class="navbar">
                <div class="navbar-left">
                    <button class="btn-icon" onclick="toggleSidebar()">
                        <span class="material-icons">menu</span>
                    </button>
                    <div class="navbar-title">Tu buyer persona</div>
                </div>
                <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
            </header>

            <section class="content">
                <div class="step-card">
                    <div class="step-hero">
                        <span class="step-badge">Paso 3</span>
                        <h1><?= $displayName ?>, pensemos tu buyer persona</h1>
                        <p>El buyer persona representa a tu cliente ideal. Acá podés responder las preguntas clave y ver cómo se arma un texto base para describirlo con más claridad.</p>
                        <span class="buyer-status <?= $buyerPersonaCompletado ? 'is-complete' : 'is-pending' ?>" id="buyer-status">
                            <span class="material-icons" style="font-size:16px"><?= $buyerPersonaCompletado ? 'check_circle' : 'schedule' ?></span>
                            <span id="buyer-status-text"><?= $buyerPersonaCompletado ? 'Buyer persona completado' : 'Buyer persona en progreso' ?></span>
                        </span>
                    </div>

                    <div class="step-grid">
                        <div class="card step-panel">
                            <h3>¿Para qué sirve?</h3>
                            <p>Te permite comunicar mejor tu propuesta, crear contenido más útil y diseñar una oferta alineada con necesidades concretas.</p>
                        </div>
                        <div class="card step-panel">
                            <h3>¿Qué vamos a trabajar?</h3>
                            <p>Perfil, hábitos, motivaciones, dolores, criterios de decisión y objeciones de tu cliente ideal para tomar decisiones con más criterio.</p>
                        </div>
                        <div class="card step-panel">
                            <h3>¿Qué preguntas tengo que hacerme?</h3>
                            <ul class="questions-panel-list" id="buyer-questions-list">
                                <?php foreach ($questions as $question): ?>
                                    <li><?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="questions-panel-actions">
                                <button class="questions-toggle-btn" type="button" id="buyer-questions-toggle" aria-expanded="false">
                                    Ver todas las preguntas
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="buyer-form-card">
                        <h2>Respondé las preguntas</h2>
                        <p>Para guardar el buyer persona necesitás responder todas las preguntas. Cuando lo guardás, queda marcado como completado.</p>

                        <div class="buyer-feedback" id="buyer-feedback"></div>

                        <form id="buyer-form" novalidate>
                            <div class="buyer-fields">
                                <?php foreach ($questions as $key => $question): ?>
                                    <div class="buyer-field">
                                        <div class="buyer-label-row">
                                            <label for="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?></label>
                                            <button class="buyer-help-btn" type="button" data-help-key="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" aria-label="Ver ayuda sobre <?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?>">?</button>
                                        </div>
                                        <textarea id="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" placeholder="<?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?>"><?= $val($key) ?></textarea>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </form>
                    </div>

                    <div class="buyer-preview-card">
                        <h2>Así se va armando tu buyer persona</h2>
                        <p>Este texto resume quién es tu cliente ideal, qué necesita y cómo decide.</p>

                        <div class="buyer-preview-box">
                            <span class="buyer-preview-label">
                                <span class="material-icons" style="font-size:16px">person_search</span>
                                Texto base
                            </span>
                            <p class="buyer-preview-text" id="buyer-preview"><?= $buyerPersonaGuardado !== '' ? $buyerPersonaGuardado : "Mi cliente ideal es [¿Quién es mi cliente ideal?].\n\nTiene [¿Qué edad tiene y en qué etapa de su vida está?] y su realidad diaria está marcada por [¿A qué se dedica y cómo es su realidad diaria?].\n\nHoy necesita resolver [¿Qué problema o necesidad concreta tiene?] y esto le genera [¿Qué le preocupa o frustra hoy en relación con ese problema?].\n\nQuiere lograr [¿Qué quiere lograr o mejorar?] y lo motiva a buscar una solución [¿Qué lo motiva a buscar una solución?].\n\nAntes de comprar, lo frenan [¿Qué lo frena o le genera dudas antes de comprar?] y al elegir prioriza [¿Qué valora más al momento de elegir: precio, calidad, rapidez, confianza, atención u otra cosa?].\n\nBusca información a través de [¿Cómo busca información antes de decidir?] y toma la decisión de compra de la siguiente manera: [¿Cómo toma la decisión de compra?].\n\nElegiría mi propuesta porque [¿Por qué elegiría mi propuesta y no otra?]." ?></p>
                        </div>
                    </div>

                    <div class="step-actions-card">
                        <div class="step-actions">
                            <button class="btn btn-info" type="button" id="save-buyer">Guardar buyer persona</button>
                            <button class="btn btn-cancelar" type="button" onclick="history.back()">Ir atrás</button>
                            <button class="btn btn-cancelar" type="button" onclick="location.href='emprendedor_dashboard.php'">Volver al inicio</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>

    <div class="buyer-modal-backdrop" id="buyer-help-modal" aria-hidden="true">
        <div class="buyer-modal" role="dialog" aria-modal="true" aria-labelledby="buyer-help-title">
            <div class="buyer-modal-header">
                <h3 id="buyer-help-title">Ayuda</h3>
                <button class="buyer-modal-close" type="button" id="buyer-help-close" aria-label="Cerrar ayuda">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="buyer-modal-body">
                <p class="buyer-modal-text" id="buyer-help-content"></p>
            </div>
        </div>
    </div>

    <div class="flow-modal-backdrop" id="buyer-flow-modal" aria-hidden="true">
        <div class="flow-modal" role="dialog" aria-modal="true" aria-labelledby="buyer-flow-title">
            <h3 id="buyer-flow-title">Buyer persona guardado</h3>
            <p>Ya completaste el paso 3. El siguiente paso es solicitar tu landing page con toda la información clave del emprendimiento.</p>
            <div class="flow-modal-actions">
                <button class="btn btn-info" type="button" id="buyer-flow-next">Ir al paso 4: Landing Page</button>
                <button class="btn btn-cancelar" type="button" id="buyer-flow-close">Quedarme acá</button>
            </div>
        </div>
    </div>

    <script>
        const buyerForm = document.getElementById('buyer-form');
        const buyerPreview = document.getElementById('buyer-preview');
        const buyerFeedback = document.getElementById('buyer-feedback');
        const buyerStatus = document.getElementById('buyer-status');
        const buyerStatusText = document.getElementById('buyer-status-text');
        const saveBuyerBtn = document.getElementById('save-buyer');
        const buyerHelpButtons = document.querySelectorAll('.buyer-help-btn');
        const buyerHelpModal = document.getElementById('buyer-help-modal');
        const buyerHelpTitle = document.getElementById('buyer-help-title');
        const buyerHelpContent = document.getElementById('buyer-help-content');
        const buyerHelpClose = document.getElementById('buyer-help-close');
        const buyerFlowModal = document.getElementById('buyer-flow-modal');
        const buyerFlowNext = document.getElementById('buyer-flow-next');
        const buyerFlowClose = document.getElementById('buyer-flow-close');
        const buyerQuestionsList = document.getElementById('buyer-questions-list');
        const buyerQuestionsToggle = document.getElementById('buyer-questions-toggle');
        const defaultBuyerButtonLabel = 'Guardar buyer persona';
        const buyerFields = <?= json_encode(array_keys($questions)) ?>;
        const buyerQuestions = <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>;
        const buyerHelpMap = {
            cliente_ideal: {
                title: '¿Quién es mi cliente ideal?',
                content: 'Definí con claridad qué tipo de persona es la que más necesita y más valoraría tu propuesta. No pienses en “cualquier cliente”, sino en el perfil que realmente tiene sentido para tu emprendimiento. Cuanto más claro tengas a quién querés ayudar, más fácil va a ser comunicarte y vender.',
            },
            edad_etapa_vida: {
                title: '¿Qué edad tiene y en qué etapa de su vida está?',
                content: 'La edad y el momento de vida influyen en lo que una persona necesita, valora y decide. No piensa igual alguien que recién empieza una etapa que alguien con más experiencia o responsabilidades. Esta pregunta te ayuda a entender mejor su contexto y su forma de actuar.',
            },
            ocupacion_realidad_diaria: {
                title: '¿A qué se dedica y cómo es su realidad diaria?',
                content: 'Conocer su ocupación y cómo es su día a día te permite entender mejor sus tiempos, prioridades y preocupaciones. No es lo mismo alguien con una rutina estable que alguien con jornadas exigentes o poco tiempo. Esta información te ayuda a pensar una propuesta más realista y cercana.',
            },
            problema_necesidad: {
                title: '¿Qué problema o necesidad concreta tiene?',
                content: 'Identificá qué situación necesita resolver, mejorar o cambiar. El problema tiene que ser claro, específico y fácil de reconocer. Si no entendés bien qué le pasa, difícilmente puedas ofrecer una solución que conecte.',
            },
            preocupacion_frustracion: {
                title: '¿Qué le preocupa o frustra hoy en relación con ese problema?',
                content: 'Pensá qué es lo que más le molesta, le pesa o le genera incomodidad sobre esa situación. Acá no alcanza con saber el problema; también hay que entender cómo lo vive. Eso te permite conectar con una necesidad más real y profunda.',
            },
            objetivo_mejora: {
                title: '¿Qué quiere lograr o mejorar?',
                content: 'Definí qué resultado espera conseguir esa persona. Puede ser ahorrar tiempo, vender más, sentirse mejor, resolver algo más rápido o vivir con menos preocupación. Esta pregunta te ayuda a enfocarte en el objetivo que el cliente realmente valora.',
            },
            motivacion_busqueda: {
                title: '¿Qué lo motiva a buscar una solución?',
                content: 'Buscá entender qué lo impulsa a actuar y no seguir igual. A veces es una urgencia, una incomodidad que se volvió insostenible o el deseo de mejorar una situación. Saber esto te ayuda a entender cuándo y por qué una persona decide moverse.',
            },
            freno_dudas: {
                title: '¿Qué lo frena o le genera dudas antes de comprar?',
                content: 'Identificá qué miedos, objeciones o inseguridades pueden hacer que no avance. Puede ser el precio, la desconfianza, una mala experiencia previa o no estar seguro de que le va a servir. Entender estos frenos es clave para anticiparte y generar más confianza.',
            },
            criterio_eleccion: {
                title: '¿Qué valora más al momento de elegir: precio, calidad, rapidez, confianza, atención u otra cosa?',
                content: 'Pensá qué aspectos pesan más cuando esa persona compara opciones. No todos eligen por lo mismo, y entender qué prioriza tu cliente te ayuda a destacar lo que realmente importa. Esta pregunta te permite construir una propuesta más alineada con sus criterios de decisión.',
            },
            busqueda_informacion: {
                title: '¿Cómo busca información antes de decidir?',
                content: 'Observá dónde averigua, compara o consulta antes de comprar. Puede buscar en redes sociales, Google, recomendaciones, videos, opiniones o hablar con otras personas. Saber esto te ayuda a estar presente en los canales correctos y con el mensaje adecuado.',
            },
            decision_compra: {
                title: '¿Cómo toma la decisión de compra?',
                content: 'Definí si decide rápido o si necesita tiempo para comparar, evaluar y pensar. Algunas personas compran solas, otras consultan con alguien más o necesitan varias señales de confianza. Esta pregunta te ayuda a entender mejor el proceso que recorre antes de elegir.',
            },
            motivo_eleccion: {
                title: '¿Por qué elegiría mi propuesta y no otra?',
                content: 'Reflexioná sobre qué valor diferencial podría hacer que te elija a vos por encima de otras opciones. No se trata de decir que sos “mejor”, sino de identificar qué ofrecés que resulte más útil, atractivo o confiable para ese cliente. Esta respuesta te ayuda a reconocer tu ventaja real en el mercado.',
            },
        };

        function getBuyerValue(field) {
            const input = document.getElementById(field);
            return input.value.trim() || `[${buyerQuestions[field]}]`;
        }

        function buildBuyerPreview() {
            buyerPreview.textContent = [
                `Mi cliente ideal es ${getBuyerValue('cliente_ideal')}.`,
                `Tiene ${getBuyerValue('edad_etapa_vida')} y su realidad diaria está marcada por ${getBuyerValue('ocupacion_realidad_diaria')}.`,
                `Hoy necesita resolver ${getBuyerValue('problema_necesidad')} y esto le genera ${getBuyerValue('preocupacion_frustracion')}.`,
                `Quiere lograr ${getBuyerValue('objetivo_mejora')} y lo motiva a buscar una solución ${getBuyerValue('motivacion_busqueda')}.`,
                `Antes de comprar, lo frenan ${getBuyerValue('freno_dudas')} y al elegir prioriza ${getBuyerValue('criterio_eleccion')}.`,
                `Busca información a través de ${getBuyerValue('busqueda_informacion')} y toma la decisión de compra de la siguiente manera: ${getBuyerValue('decision_compra')}.`,
                `Elegiría mi propuesta porque ${getBuyerValue('motivo_eleccion')}.`,
            ].join('\n\n');
        }

        function showBuyerFeedback(type, message) {
            buyerFeedback.className = 'buyer-feedback ' + type;
            buyerFeedback.innerHTML = '<span class="material-icons" style="font-size:18px">'
                + (type === 'ok' ? 'check_circle' : 'error') + '</span>' + message;
        }

        function updateBuyerStatus(completado) {
            buyerStatus.classList.toggle('is-complete', completado);
            buyerStatus.classList.toggle('is-pending', !completado);
            buyerStatus.querySelector('.material-icons').textContent = completado ? 'check_circle' : 'schedule';
            buyerStatusText.textContent = completado ? 'Buyer persona completado' : 'Buyer persona en progreso';
        }

        function openBuyerFlowModal() {
            buyerFlowModal.classList.add('is-open');
            buyerFlowModal.setAttribute('aria-hidden', 'false');
        }

        function closeBuyerFlowModal() {
            buyerFlowModal.classList.remove('is-open');
            buyerFlowModal.setAttribute('aria-hidden', 'true');
        }

        async function saveBuyerPersona() {
            const hasEmptyField = buyerFields.some((field) => !document.getElementById(field).value.trim());
            if (hasEmptyField) {
                showBuyerFeedback('error', 'Para guardar el buyer persona tenés que responder todas las preguntas.');
                return;
            }

            const body = new FormData(buyerForm);

            saveBuyerBtn.disabled = true;
            saveBuyerBtn.textContent = 'Guardando...';
            buyerFeedback.className = 'buyer-feedback';

            try {
                const res = await fetch('/controllers/emprendedor_buyerPersonaController.php', {
                    method: 'POST',
                    body,
                });
                const data = await res.json();

                if (!data.ok) {
                    showBuyerFeedback('error', data.error ?? 'No se pudo guardar el buyer persona.');
                    return;
                }

                buyerPreview.textContent = data.buyer_persona_estructura;
                updateBuyerStatus(Boolean(data.completado));
                localStorage.setItem('impulsa_progress_buyer_persona', 'done');
                showBuyerFeedback('ok', 'Buyer persona guardado correctamente.');
                openBuyerFlowModal();
            } catch {
                showBuyerFeedback('error', 'Error de conexión. Intentá de nuevo.');
            } finally {
                saveBuyerBtn.disabled = false;
                saveBuyerBtn.textContent = defaultBuyerButtonLabel;
            }
        }

        function openBuyerHelp(helpKey) {
            const modalData = buyerHelpMap[helpKey] || { title: 'Ayuda', content: '' };
            buyerHelpTitle.textContent = modalData.title;
            buyerHelpContent.textContent = modalData.content;
            buyerHelpModal.classList.add('is-open');
            buyerHelpModal.setAttribute('aria-hidden', 'false');
        }

        function closeBuyerHelp() {
            buyerHelpModal.classList.remove('is-open');
            buyerHelpModal.setAttribute('aria-hidden', 'true');
        }

        buyerFields.forEach((field) => {
            document.getElementById(field).addEventListener('input', buildBuyerPreview);
        });

        saveBuyerBtn.addEventListener('click', saveBuyerPersona);

        buyerHelpButtons.forEach((button) => {
            button.addEventListener('click', () => {
                openBuyerHelp(button.getAttribute('data-help-key') || '');
            });
        });

        buyerHelpClose.addEventListener('click', closeBuyerHelp);
        buyerHelpModal.addEventListener('click', (event) => {
            if (event.target === buyerHelpModal) {
                closeBuyerHelp();
            }
        });
        buyerFlowClose.addEventListener('click', closeBuyerFlowModal);
        buyerFlowNext.addEventListener('click', () => {
            location.href = 'landing_page_request.php';
        });
        buyerFlowModal.addEventListener('click', (event) => {
            if (event.target === buyerFlowModal) {
                closeBuyerFlowModal();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && buyerHelpModal.classList.contains('is-open')) {
                closeBuyerHelp();
            }
            if (event.key === 'Escape' && buyerFlowModal.classList.contains('is-open')) {
                closeBuyerFlowModal();
            }
        });

        buyerQuestionsToggle.addEventListener('click', () => {
            const expanded = buyerQuestionsList.classList.toggle('is-expanded');
            buyerQuestionsToggle.textContent = expanded ? 'Ver menos preguntas' : 'Ver todas las preguntas';
            buyerQuestionsToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });

        buildBuyerPreview();
    </script>
</body>

</html>
