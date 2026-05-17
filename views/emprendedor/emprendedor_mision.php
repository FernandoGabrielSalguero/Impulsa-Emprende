<?php
require_once __DIR__ . '/../../controllers/emprendedor_misionController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Emprendedor';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');
$perfilObligatorio = trim((string) ($perfil['nombre'] ?? '')) === ''
    || trim((string) ($perfil['apellido'] ?? '')) === ''
    || trim((string) ($perfil['apodo'] ?? '')) === ''
    || trim((string) ($perfil['whatsapp'] ?? '')) === '';

$val = function (string $key, string $fallback = '') use ($mision): string {
    return htmlspecialchars((string) ($mision[$key] ?? $fallback), ENT_QUOTES, 'UTF-8');
};

$aQuienAyudo = $val('a_quien_ayudo');
$queProblemaResuelvo = $val('que_problema_resuelvo');
$comoLoResuelvo = $val('como_lo_resuelvo');
$misionGuardada = $val('mision_estructura');
$misionCompletada = !empty($mision['completado']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Impulsa - Tu misión</title>
    <?php $impulsaMaterialAssetBase = '../..'; require __DIR__ . '/../../partials/impulsa_material_assets.php'; ?>

    <style>
        .navbar {
            justify-content: space-between;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

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
        .mision-form-card,
        .mision-preview-card,
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
            background: #4338ca;
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

        .mision-form-card,
        .mision-preview-card,
        .step-actions-card {
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }

        .mision-form-card h2,
        .mision-preview-card h2 {
            margin: 0 0 8px;
            font-size: 22px;
        }

        .mision-form-card p,
        .mision-preview-card p {
            margin: 0 0 18px;
            color: #6b7280;
            line-height: 1.65;
        }

        .mision-fields {
            display: grid;
            gap: 16px;
        }

        .mision-field label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
        }

        .mision-label-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .mision-label-row label {
            margin-bottom: 0;
        }

        .mision-help-btn {
            width: 24px;
            height: 24px;
            border: 0;
            border-radius: 999px;
            background: #e0e7ff;
            color: #4338ca;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s ease, background 0.15s ease;
        }

        .mision-help-btn:hover {
            background: #c7d2fe;
            transform: translateY(-1px);
        }

        .mision-field textarea {
            width: 100%;
            min-height: 110px;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .mision-field textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }

        .mision-preview-box {
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 20px;
        }

        .mision-preview-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
            font-size: 12px;
            font-weight: 700;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .mision-preview-text {
            margin: 0;
            font-size: 18px;
            color: #111827;
            line-height: 1.7;
        }

        .mision-feedback {
            display: none;
            align-items: center;
            gap: 8px;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .mision-feedback.ok {
            display: flex;
            background: #dcfce7;
            color: #15803d;
        }

        .mision-feedback.error {
            display: flex;
            background: #fee2e2;
            color: #b91c1c;
        }

        .mision-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .mision-status.is-complete {
            background: #dcfce7;
            color: #15803d;
        }

        .mision-status.is-pending {
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

        .mision-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1000;
        }

        .mision-modal-backdrop.is-open {
            display: flex;
        }

        .mision-modal {
            width: min(100%, 560px);
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
        }

        .mision-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .mision-modal-header h3 {
            margin: 0;
            font-size: 20px;
            color: #111827;
        }

        .mision-modal-close {
            border: 0;
            background: transparent;
            color: #6b7280;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .mision-modal-body {
            min-height: 140px;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 16px;
            color: #64748b;
            line-height: 1.6;
            background: #f8fafc;
        }

        .mision-modal-marker {
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
            width: min(100%, 520px);
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
    <div class="layout im-aplicacion">
        <aside class="sidebar im-menu-lateral" id="sidebar">
            <div class="sidebar-header">
                <img src="../../assets/institucionales/icons/Isotipo grande.png" alt="Impulsa Emprende" class="sidebar-brand-icon">
                <span class="logo-text">impulsa emprende</span>
            </div>
            <nav class="sidebar-menu im-navegacion">
                <ul>
                    <li onclick="location.href='emprendedor_dashboard.php'">
                        <span class="material-icons" style="color:#6366f1">home</span>
                        <span class="link-text">Inicio</span>
                    </li>
                    <li class="active" onclick="location.href='emprendedor_mision.php'">
                        <span class="material-icons" style="color:#6366f1">track_changes</span>
                        <span class="link-text">Misión</span>
                    </li>
                    <li onclick="location.href='emprendedor_vision.php'">
                        <span class="material-icons" style="color:#6366f1">lightbulb</span>
                        <span class="link-text">Visión</span>
                    </li>
                    <li onclick="location.href='emprendedor_buyerPersona.php'">
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
                <button class="btn-icon im-boton-icono" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <div class="main im-contenedor">
            <header class="navbar im-barra-superior">
                <div class="navbar-left">
                    <button class="btn-icon im-boton-icono" onclick="toggleSidebar()">
                        <span class="material-icons">menu</span>
                    </button>
                    <div class="navbar-title">Tu misión</div>
                </div>
                <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
            </header>

            <section class="content im-contenido">
                <div class="step-card">
                    <div class="step-hero">
                        <span class="step-badge">Paso 1</span>
                        <h1><?= $displayName ?>, construyamos tu misión</h1>
                        <p>La misión resume qué hace tu emprendimiento, para quién lo hace y qué valor entrega. Acá podés responder las preguntas clave y ver cómo se arma tu misión en tiempo real.</p>
                        <span class="mision-status <?= $misionCompletada ? 'is-complete' : 'is-pending' ?>" id="mision-status">
                            <span class="material-icons" style="font-size:16px"><?= $misionCompletada ? 'check_circle' : 'schedule' ?></span>
                            <span id="mision-status-text"><?= $misionCompletada ? 'Misión completada' : 'Misión en progreso' ?></span>
                        </span>
                    </div>

                    <div class="step-grid">
                        <div class="card step-panel im-tarjeta">
                            <h3>¿Para qué sirve?</h3>
                            <p>Te ayuda a explicar con claridad qué problema resolvés y por qué tu propuesta importa. Es la base para comunicar mejor tu negocio.</p>
                        </div>
                        <div class="card step-panel im-tarjeta">
                            <h3>¿Qué vamos a trabajar?</h3>
                            <p>Qué hacés, a quién ayudás y cuál es el valor diferencial de tu emprendimiento. Todo eso se transforma en una frase simple y clara.</p>
                        </div>
                        <div class="card step-panel im-tarjeta">
                            <h3>¿Qué preguntas tengo que hacerme?</h3>
                            <ul>
                                <li>¿A quien ayudo exactamente?</li>
                                <li>¿Qué problema resuelvo?</li>
                                <li>¿Cómo lo resuelvo?</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mision-form-card">
                        <h2>Respondé las preguntas</h2>
                        <p>Para guardar la misión necesitás responder las tres preguntas. Cuando esté lista, también podés marcarla como completada.</p>

                        <div class="mision-feedback" id="mision-feedback"></div>

                        <form id="mision-form" novalidate>
                            <div class="mision-fields">
                                <div class="mision-field">
                                    <div class="mision-label-row">
                                        <label for="a_quien_ayudo">¿A quien ayudo exactamente?</label>
                                        <button class="mision-help-btn" type="button" data-help-key="a_quien_ayudo" aria-label="Ver ayuda sobre a quien ayudo exactamente">?</button>
                                    </div>
                                    <textarea id="a_quien_ayudo" name="a_quien_ayudo" placeholder="Ej: emprendedores que recién empiezan a vender online"><?= $aQuienAyudo ?></textarea>
                                </div>

                                <div class="mision-field">
                                    <div class="mision-label-row">
                                        <label for="que_problema_resuelvo">¿Qué problema resuelvo?</label>
                                        <button class="mision-help-btn" type="button" data-help-key="que_problema_resuelvo" aria-label="Ver ayuda sobre qué problema resuelvo">?</button>
                                    </div>
                                    <textarea id="que_problema_resuelvo" name="que_problema_resuelvo" placeholder="Ej: la falta de claridad para comunicar su propuesta de valor"><?= $queProblemaResuelvo ?></textarea>
                                </div>

                                <div class="mision-field">
                                    <div class="mision-label-row">
                                        <label for="como_lo_resuelvo">¿Cómo lo resuelvo?</label>
                                        <button class="mision-help-btn" type="button" data-help-key="como_lo_resuelvo" aria-label="Ver ayuda sobre cómo lo resuelvo">?</button>
                                    </div>
                                    <textarea id="como_lo_resuelvo" name="como_lo_resuelvo" placeholder="Ej: con acompañamiento estratégico, mentoría y herramientas simples"><?= $comoLoResuelvo ?></textarea>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="mision-preview-card">
                        <h2>Así se va armando tu misión</h2>
                        <p>La misión debe ser corta, clara y real.</p>

                        <div class="mision-preview-box">
                            <span class="mision-preview-label">
                                <span class="material-icons" style="font-size:16px">edit_note</span>
                                Estructura base
                            </span>
                            <p class="mision-preview-text" id="mision-preview">
                                <?= $misionGuardada !== '' ? $misionGuardada : 'Ayudamos a [¿A quien ayudo exactamente?] a [¿Qué problema resuelvo?] mediante [¿Cómo lo resuelvo?]' ?>
                            </p>
                        </div>
                    </div>

                    <div class="step-actions-card">
                        <div class="step-actions">
                            <button class="btn btn-info im-boton--principal im-boton" type="button" id="save-mision-draft">Guardar misión</button>
                            <button class="btn btn-cancelar im-boton--tonal im-boton" type="button" onclick="history.back()">Ir atrás</button>
                            <button class="btn btn-cancelar im-boton--tonal im-boton" type="button" onclick="location.href='emprendedor_dashboard.php'">Volver al inicio</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>

    <div class="mision-modal-backdrop" id="mision-help-modal" aria-hidden="true">
        <div class="mision-modal" role="dialog" aria-modal="true" aria-labelledby="mision-help-title">
            <div class="mision-modal-header">
                <h3 id="mision-help-title">Ayuda</h3>
                <button class="mision-modal-close" type="button" id="mision-help-close" aria-label="Cerrar ayuda">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="mision-modal-body">
                <p class="mision-modal-marker" id="mision-help-content">ACA VA EL TEXTO DE AYUDA DE ESTA PREGUNTA.

Podés reemplazar este contenido cuando quieras.</p>
            </div>
        </div>
    </div>

    <div class="flow-modal-backdrop" id="mision-flow-modal" aria-hidden="true">
        <div class="flow-modal" role="dialog" aria-modal="true" aria-labelledby="mision-flow-title">
            <h3 id="mision-flow-title">Misión guardada</h3>
            <p>Ya completaste el paso 1. Seguimos con tu visión para definir hacia dónde querés llevar tu emprendimiento.</p>
            <div class="flow-modal-actions">
                <button class="btn btn-info im-boton--principal im-boton" type="button" id="mision-flow-next">Ir al paso 2: Visión</button>
                <button class="btn btn-cancelar im-boton--tonal im-boton" type="button" id="mision-flow-close">Quedarme acá</button>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('mision-form');
        const aQuienInput = document.getElementById('a_quien_ayudo');
        const problemaInput = document.getElementById('que_problema_resuelvo');
        const comoInput = document.getElementById('como_lo_resuelvo');
        const preview = document.getElementById('mision-preview');
        const feedback = document.getElementById('mision-feedback');
        const status = document.getElementById('mision-status');
        const statusText = document.getElementById('mision-status-text');
        const saveDraftBtn = document.getElementById('save-mision-draft');
        const helpButtons = document.querySelectorAll('.mision-help-btn');
        const helpModal = document.getElementById('mision-help-modal');
        const helpModalTitle = document.getElementById('mision-help-title');
        const helpModalContent = document.getElementById('mision-help-content');
        const helpModalClose = document.getElementById('mision-help-close');
        const flowModal = document.getElementById('mision-flow-modal');
        const flowModalNext = document.getElementById('mision-flow-next');
        const flowModalClose = document.getElementById('mision-flow-close');
        const defaultButtonLabels = {
            guardar: 'Guardar misión',
        };
        const helpContentMap = {
            a_quien_ayudo: {
                title: '¿A quien ayudo exactamente?',
                content: 'Definí con claridad quién es la persona, grupo o tipo de cliente al que querés ayudar. Cuanto más específico seas, más fácil va a ser entender a quién le hablás y qué necesita. No intentes ayudar a todos, porque cuando hablás para todos, no conectás con nadie.',
            },
            que_problema_resuelvo: {
                title: '¿Qué problema resuelvo?',
                content: 'Identificá cuál es la necesidad, dificultad o dolor concreto que esa persona tiene y que tu emprendimiento puede solucionar. El problema tiene que ser real, claro y fácil de reconocer. Si no podés explicarlo de forma simple, probablemente todavía no lo tenés del todo claro.',
            },
            como_lo_resuelvo: {
                title: '¿Cómo lo resuelvo?',
                content: 'Explicá de qué manera ayudás a resolver ese problema a través de tu producto, servicio o propuesta. No se trata de dar todos los detalles, sino de mostrar con claridad qué hacés y cómo generás valor. La idea es que cualquiera pueda entender rápidamente qué ofrecés y por qué sirve.',
            },
        };

        function buildMissionPreview() {
            const aQuien = aQuienInput.value.trim() || '[¿A quien ayudo exactamente?]';
            const problema = problemaInput.value.trim() || '[¿Qué problema resuelvo?]';
            const como = comoInput.value.trim() || '[¿Cómo lo resuelvo?]';

            preview.textContent = `Ayudamos a ${aQuien} a ${problema} mediante ${como}`;
        }

        function showFeedback(type, message) {
            feedback.className = 'mision-feedback ' + type;
            feedback.innerHTML = '<span class="material-icons" style="font-size:18px">' +
                (type === 'ok' ? 'check_circle' : 'error') + '</span>' + message;
        }

        function updateStatus(completado) {
            status.classList.toggle('is-complete', completado);
            status.classList.toggle('is-pending', !completado);
            status.querySelector('.material-icons').textContent = completado ? 'check_circle' : 'schedule';
            statusText.textContent = completado ? 'Misión completada' : 'Misión en progreso';
        }

        function openFlowModal() {
            flowModal.classList.add('is-open');
            flowModal.setAttribute('aria-hidden', 'false');
        }

        function closeFlowModal() {
            flowModal.classList.remove('is-open');
            flowModal.setAttribute('aria-hidden', 'true');
        }

        async function saveMission() {
            if (!aQuienInput.value.trim() || !problemaInput.value.trim() || !comoInput.value.trim()) {
                showFeedback('error', 'Para guardar la misión tenés que responder las tres preguntas.');
                return;
            }

            const body = new FormData(form);

            saveDraftBtn.disabled = true;
            saveDraftBtn.textContent = 'Guardando...';
            feedback.className = 'mision-feedback';

            try {
                const res = await fetch('/controllers/emprendedor_misionController.php', {
                    method: 'POST',
                    body,
                });
                const data = await res.json();

                if (!data.ok) {
                    showFeedback('error', data.error ?? 'No se pudo guardar la misión.');
                    return;
                }

                preview.textContent = data.mision_estructura;
                updateStatus(Boolean(data.completado));
                localStorage.setItem('impulsa_progress_mision', 'done');
                showFeedback('ok', 'Misión guardada correctamente.');
                openFlowModal();
            } catch {
                showFeedback('error', 'Error de conexión. Intentá de nuevo.');
            } finally {
                saveDraftBtn.disabled = false;
                saveDraftBtn.textContent = defaultButtonLabels.guardar;
            }
        }

        [aQuienInput, problemaInput, comoInput].forEach((field) => {
            field.addEventListener('input', buildMissionPreview);
        });

        saveDraftBtn.addEventListener('click', saveMission);

        function openHelpModal(helpKey) {
            const modalData = helpContentMap[helpKey] || {
                title: 'Ayuda',
                content: 'ACA VA EL TEXTO DE AYUDA DE ESTA PREGUNTA.\n\nPodés reemplazar este contenido cuando quieras.',
            };

            helpModalTitle.textContent = modalData.title;
            helpModalContent.textContent = modalData.content;
            helpModal.classList.add('is-open');
            helpModal.setAttribute('aria-hidden', 'false');
        }

        function closeHelpModal() {
            helpModal.classList.remove('is-open');
            helpModal.setAttribute('aria-hidden', 'true');
        }

        helpButtons.forEach((button) => {
            button.addEventListener('click', () => {
                openHelpModal(button.getAttribute('data-help-key') || '');
            });
        });

        helpModalClose.addEventListener('click', closeHelpModal);
        helpModal.addEventListener('click', (event) => {
            if (event.target === helpModal) {
                closeHelpModal();
            }
        });
        flowModalClose.addEventListener('click', closeFlowModal);
        flowModalNext.addEventListener('click', () => {
            location.href = 'emprendedor_vision.php';
        });
        flowModal.addEventListener('click', (event) => {
            if (event.target === flowModal) {
                closeFlowModal();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && helpModal.classList.contains('is-open')) {
                closeHelpModal();
            }
            if (event.key === 'Escape' && flowModal.classList.contains('is-open')) {
                closeFlowModal();
            }
        });

        buildMissionPreview();
    </script>
</body>

</html>






