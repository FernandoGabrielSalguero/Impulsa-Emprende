<?php
require_once __DIR__ . '/../../controllers/emprendedor_visionController.php';

$displayName = $perfil['apodo'] ?? $perfil['nombre'] ?? $_SESSION['correo'] ?? 'Emprendedor';
$displayName = htmlspecialchars((string) $displayName, ENT_QUOTES, 'UTF-8');

$val = function (string $key, string $fallback = '') use ($vision): string {
    return htmlspecialchars((string) ($vision[$key] ?? $fallback), ENT_QUOTES, 'UTF-8');
};

$conversionFutura = $val('conversion_futura');
$lugarMercado = $val('lugar_mercado');
$impactoGenerado = $val('impacto_generado');
$visionGuardada = $val('vision_estructura');
$visionCompletada = !empty($vision['completado']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Impulsa - Tu visión</title>

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
        .vision-form-card,
        .vision-preview-card,
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
            background: #0891b2;
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
        .vision-form-card,
        .vision-preview-card,
        .step-actions-card {
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }
        .vision-form-card h2,
        .vision-preview-card h2 {
            margin: 0 0 8px;
            font-size: 22px;
        }
        .vision-form-card p,
        .vision-preview-card p {
            margin: 0 0 18px;
            color: #6b7280;
            line-height: 1.65;
        }
        .vision-fields {
            display: grid;
            gap: 16px;
        }
        .vision-label-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }
        .vision-label-row label {
            color: #374151;
            font-size: 14px;
            font-weight: 600;
            margin: 0;
        }
        .vision-help-btn {
            width: 24px;
            height: 24px;
            border: 0;
            border-radius: 999px;
            background: #cffafe;
            color: #0f766e;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s ease, background 0.15s ease;
        }
        .vision-help-btn:hover {
            background: #a5f3fc;
            transform: translateY(-1px);
        }
        .vision-field textarea {
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
        .vision-field textarea:focus {
            border-color: #0891b2;
            box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.12);
        }
        .vision-preview-box {
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 20px;
        }
        .vision-preview-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
            font-size: 12px;
            font-weight: 700;
            color: #0891b2;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .vision-preview-text {
            margin: 0;
            font-size: 18px;
            color: #111827;
            line-height: 1.7;
        }
        .vision-feedback {
            display: none;
            align-items: center;
            gap: 8px;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .vision-feedback.ok {
            display: flex;
            background: #dcfce7;
            color: #15803d;
        }
        .vision-feedback.error {
            display: flex;
            background: #fee2e2;
            color: #b91c1c;
        }
        .vision-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }
        .vision-status.is-complete {
            background: #dcfce7;
            color: #15803d;
        }
        .vision-status.is-pending {
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
        .vision-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1000;
        }
        .vision-modal-backdrop.is-open {
            display: flex;
        }
        .vision-modal {
            width: min(100%, 560px);
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
        }
        .vision-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }
        .vision-modal-header h3 {
            margin: 0;
            font-size: 20px;
            color: #111827;
        }
        .vision-modal-close {
            border: 0;
            background: transparent;
            color: #6b7280;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .vision-modal-body {
            min-height: 140px;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 16px;
            color: #64748b;
            line-height: 1.6;
            background: #f8fafc;
        }
        .vision-modal-text {
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
                    <li class="active" onclick="location.href='emprendedor_vision.php'">
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
                    <div class="navbar-title">Tu visión</div>
                </div>
                <?= renderBotonPerfil($perfil['avatar_path'] ?? ($_SESSION['avatar_path'] ?? null)) ?>
            </header>

            <section class="content">
                <div class="step-card">
                    <div class="step-hero">
                        <span class="step-badge">Paso 2</span>
                        <h1><?= $displayName ?>, definamos tu visión</h1>
                        <p>La visión pone en palabras el futuro que querés construir para tu emprendimiento. Acá podés responder las preguntas clave y ver cómo se arma tu visión en tiempo real.</p>
                        <span class="vision-status <?= $visionCompletada ? 'is-complete' : 'is-pending' ?>" id="vision-status">
                            <span class="material-icons" style="font-size:16px"><?= $visionCompletada ? 'check_circle' : 'schedule' ?></span>
                            <span id="vision-status-text"><?= $visionCompletada ? 'Visión completada' : 'Visión en progreso' ?></span>
                        </span>
                    </div>

                    <div class="step-grid">
                        <div class="card step-panel">
                            <h3>¿Para qué sirve?</h3>
                            <p>Te ayuda a proyectar crecimiento, tomar decisiones con más foco y mostrar una dirección clara para tu marca.</p>
                        </div>
                        <div class="card step-panel">
                            <h3>¿Qué vamos a trabajar?</h3>
                            <p>Cómo te gustaría ver tu emprendimiento en el futuro, qué lugar querés ocupar en el mercado y qué impacto querés generar.</p>
                        </div>
                        <div class="card step-panel">
                            <h3>¿Qué preguntas tengo que hacerme?</h3>
                            <ul>
                                <li>¿En qué quiero que se convierta mi emprendimiento en 3 o 5 años?</li>
                                <li>¿Qué lugar quiero ocupar en el mercado?</li>
                                <li>¿Qué impacto quiero generar?</li>
                            </ul>
                        </div>
                    </div>

                    <div class="vision-form-card">
                        <h2>Respondé las preguntas</h2>
                        <p>Para guardar la visión necesitás responder las tres preguntas. Cuando esté lista, también podés marcarla como completada.</p>

                        <div class="vision-feedback" id="vision-feedback"></div>

                        <form id="vision-form" novalidate>
                            <div class="vision-fields">
                                <div class="vision-field">
                                    <div class="vision-label-row">
                                        <label for="conversion_futura">¿En qué quiero que se convierta mi emprendimiento en 3 o 5 años?</label>
                                        <button class="vision-help-btn" type="button" data-help-key="conversion_futura" aria-label="Ver ayuda sobre en qué quiero que se convierta mi emprendimiento en 3 o 5 años">?</button>
                                    </div>
                                    <textarea id="conversion_futura" name="conversion_futura" placeholder="Ej: una marca referente para emprendedores de mi ciudad"><?= $conversionFutura ?></textarea>
                                </div>

                                <div class="vision-field">
                                    <div class="vision-label-row">
                                        <label for="lugar_mercado">¿Qué lugar quiero ocupar en el mercado?</label>
                                        <button class="vision-help-btn" type="button" data-help-key="lugar_mercado" aria-label="Ver ayuda sobre qué lugar quiero ocupar en el mercado">?</button>
                                    </div>
                                    <textarea id="lugar_mercado" name="lugar_mercado" placeholder="Ej: ser reconocidos por cercanía, calidad e innovación"><?= $lugarMercado ?></textarea>
                                </div>

                                <div class="vision-field">
                                    <div class="vision-label-row">
                                        <label for="impacto_generado">¿Qué impacto quiero generar?</label>
                                        <button class="vision-help-btn" type="button" data-help-key="impacto_generado" aria-label="Ver ayuda sobre qué impacto quiero generar">?</button>
                                    </div>
                                    <textarea id="impacto_generado" name="impacto_generado" placeholder="Ej: ayudar a más personas a crecer con herramientas accesibles"><?= $impactoGenerado ?></textarea>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="vision-preview-card">
                        <h2>Así se va armando tu visión</h2>
                        <p>La visión debe ser inspiradora, concreta y posible.</p>

                        <div class="vision-preview-box">
                            <span class="vision-preview-label">
                                <span class="material-icons" style="font-size:16px">visibility</span>
                                Estructura base
                            </span>
                            <p class="vision-preview-text" id="vision-preview"><?= $visionGuardada !== '' ? $visionGuardada : 'En los próximos 3 a 5 años buscamos convertirnos en [¿En qué quiero que se convierta mi emprendimiento en 3 o 5 años?], ocupar [¿Qué lugar quiero ocupar en el mercado?] y generar [¿Qué impacto quiero generar?].' ?></p>
                        </div>
                    </div>

                    <div class="step-actions-card">
                        <div class="step-actions">
                            <button class="btn btn-info" type="button" id="save-vision">Guardar visión</button>
                            <button class="btn btn-cancelar" type="button" onclick="history.back()">Ir atrás</button>
                            
                            <button class="btn btn-cancelar" type="button" onclick="location.href='emprendedor_dashboard.php'">Volver al inicio</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php require_once __DIR__ . '/../../partials/modal_perfil/modal_perfil.php'; ?>

    <div class="vision-modal-backdrop" id="vision-help-modal" aria-hidden="true">
        <div class="vision-modal" role="dialog" aria-modal="true" aria-labelledby="vision-help-title">
            <div class="vision-modal-header">
                <h3 id="vision-help-title">Ayuda</h3>
                <button class="vision-modal-close" type="button" id="vision-help-close" aria-label="Cerrar ayuda">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <div class="vision-modal-body">
                <p class="vision-modal-text" id="vision-help-content"></p>
            </div>
        </div>
    </div>

    <div class="flow-modal-backdrop" id="vision-flow-modal" aria-hidden="true">
        <div class="flow-modal" role="dialog" aria-modal="true" aria-labelledby="vision-flow-title">
            <h3 id="vision-flow-title">Visión guardada</h3>
            <p>Ya completaste el paso 2. Ahora seguí con tu buyer persona para definir con precisión a quién le hablás.</p>
            <div class="flow-modal-actions">
                <button class="btn btn-info" type="button" id="vision-flow-next">Ir al paso 3: Buyer Persona</button>
                <button class="btn btn-cancelar" type="button" id="vision-flow-close">Quedarme acá</button>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('vision-form');
        const conversionInput = document.getElementById('conversion_futura');
        const lugarInput = document.getElementById('lugar_mercado');
        const impactoInput = document.getElementById('impacto_generado');
        const preview = document.getElementById('vision-preview');
        const feedback = document.getElementById('vision-feedback');
        const status = document.getElementById('vision-status');
        const statusText = document.getElementById('vision-status-text');
        const saveBtn = document.getElementById('save-vision');
        const helpButtons = document.querySelectorAll('.vision-help-btn');
        const helpModal = document.getElementById('vision-help-modal');
        const helpModalTitle = document.getElementById('vision-help-title');
        const helpModalContent = document.getElementById('vision-help-content');
        const helpModalClose = document.getElementById('vision-help-close');
        const flowModal = document.getElementById('vision-flow-modal');
        const flowModalNext = document.getElementById('vision-flow-next');
        const flowModalClose = document.getElementById('vision-flow-close');
        const defaultButtonLabels = {
            guardar: 'Guardar visión',
        };
        const helpContentMap = {
            conversion_futura: {
                title: '¿En qué quiero que se convierta mi emprendimiento en 3 o 5 años?',
                content: 'Pensá cómo te gustaría ver tu emprendimiento en el futuro cercano. Definí qué querés haber logrado, cómo querés que haya crecido y en qué etapa te gustaría que esté. Esta pregunta te ayuda a marcar un rumbo y no trabajar sin dirección.',
            },
            lugar_mercado: {
                title: '¿Qué lugar quiero ocupar en el mercado?',
                content: 'Definí cómo querés que tu emprendimiento sea reconocido frente a clientes y competidores. Pensá si querés destacarte por precio, calidad, cercanía, innovación, confianza u otro diferencial. Tener esto claro te ayuda a construir una propuesta con identidad y posición propia.',
            },
            impacto_generado: {
                title: '¿Qué impacto quiero generar?',
                content: 'Reflexioná sobre el cambio que querés producir en tus clientes, en tu comunidad o en tu rubro. No se trata solo de vender, sino de pensar qué valor querés dejar con lo que hacés. Esta pregunta te ayuda a darle sentido y propósito al crecimiento de tu emprendimiento.',
            },
        };

        function buildVisionPreview() {
            const conversion = conversionInput.value.trim() || '[¿En qué quiero que se convierta mi emprendimiento en 3 o 5 años?]';
            const lugar = lugarInput.value.trim() || '[¿Qué lugar quiero ocupar en el mercado?]';
            const impacto = impactoInput.value.trim() || '[¿Qué impacto quiero generar?]';

            preview.textContent = `En los próximos 3 a 5 años buscamos convertirnos en ${conversion}, ocupar ${lugar} y generar ${impacto}.`;
        }

        function showFeedback(type, message) {
            feedback.className = 'vision-feedback ' + type;
            feedback.innerHTML = '<span class="material-icons" style="font-size:18px">'
                + (type === 'ok' ? 'check_circle' : 'error') + '</span>' + message;
        }

        function updateStatus(completado) {
            status.classList.toggle('is-complete', completado);
            status.classList.toggle('is-pending', !completado);
            status.querySelector('.material-icons').textContent = completado ? 'check_circle' : 'schedule';
            statusText.textContent = completado ? 'Visión completada' : 'Visión en progreso';
        }

        function openFlowModal() {
            flowModal.classList.add('is-open');
            flowModal.setAttribute('aria-hidden', 'false');
        }

        function closeFlowModal() {
            flowModal.classList.remove('is-open');
            flowModal.setAttribute('aria-hidden', 'true');
        }

        async function saveVision() {
            if (!conversionInput.value.trim() || !lugarInput.value.trim() || !impactoInput.value.trim()) {
                showFeedback('error', 'Para guardar la visión tenés que responder las tres preguntas.');
                return;
            }

            const body = new FormData(form);

            saveBtn.disabled = true;
            saveBtn.textContent = 'Guardando...';
            feedback.className = 'vision-feedback';

            try {
                const res = await fetch('/controllers/emprendedor_visionController.php', {
                    method: 'POST',
                    body,
                });
                const data = await res.json();

                if (!data.ok) {
                    showFeedback('error', data.error ?? 'No se pudo guardar la visión.');
                    return;
                }

                preview.textContent = data.vision_estructura;
                updateStatus(Boolean(data.completado));
                localStorage.setItem('impulsa_progress_vision', 'done');
                showFeedback('ok', 'Visión guardada correctamente.');
                openFlowModal();
            } catch {
                showFeedback('error', 'Error de conexión. Intentá de nuevo.');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = defaultButtonLabels.guardar;
            }
        }

        function openHelpModal(helpKey) {
            const modalData = helpContentMap[helpKey] || {
                title: 'Ayuda',
                content: '',
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

        [conversionInput, lugarInput, impactoInput].forEach((field) => {
            field.addEventListener('input', buildVisionPreview);
        });

        saveBtn.addEventListener('click', saveVision);

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
            location.href = 'emprendedor_buyerPersona.php';
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

        buildVisionPreview();
    </script>
</body>

</html>






