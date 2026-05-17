<?php
// Requiere que $perfil esté definido en el scope del archivo que incluye este partial.
// Fuente: modelos de cada dashboard (obtenerPerfil).
?>

<style>
    .perfil-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
        padding: 24px;
    }
    .perfil-overlay.is-open {
        opacity: 1;
        pointer-events: auto;
    }
    .perfil-box {
        background: #fff;
        border-radius: 18px;
        padding: 26px;
        width: min(440px, 100%);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.15);
        max-height: 90vh;
        overflow-y: auto;
    }
    .perfil-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .perfil-header h3 { margin: 0; font-size: 17px; font-weight: 600; }

    .perfil-section {
        font-size: 11px;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin: 22px 0 12px;
    }

    .perfil-intro {
        margin: 0 0 18px;
        padding: 14px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #f8fafc;
        color: #4b5563;
        font-size: 13px;
        line-height: 1.6;
    }

    .perfil-field { margin-bottom: 14px; }
    .perfil-avatar-preview {
        width: 86px;
        height: 86px;
        border-radius: 50%;
        overflow: hidden;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        text-transform: uppercase;
        margin: 0 auto 14px;
    }
    .perfil-avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .perfil-file-input {
        display: none;
    }
    .perfil-file-trigger {
        width: 100%;
        border: 1px dashed #c7d2fe;
        border-radius: 14px;
        background: #eef2ff;
        color: #3730a3;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        text-align: center;
        cursor: pointer;
        box-sizing: border-box;
    }
    .perfil-file-trigger:hover {
        background: #e0e7ff;
    }
    .perfil-file-name {
        margin-top: 8px;
        font-size: 12px;
        color: #6b7280;
        text-align: center;
        word-break: break-word;
    }

    /* Labels de texto (excluye el label-toggle) */
    .perfil-field > label:not(.perfil-toggle) {
        display: block;
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 6px;
    }

    /* Inputs de texto / tel / date */
    .perfil-field input[type="text"],
    .perfil-field input[type="tel"],
    .perfil-field input[type="date"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 14px;
        outline: none;
        box-sizing: border-box;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .perfil-field input[type="text"]:focus,
    .perfil-field input[type="tel"]:focus,
    .perfil-field input[type="date"]:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .perfil-field.has-error > label:not(.perfil-toggle) {
        color: #b91c1c;
        font-weight: 600;
    }
    .perfil-field.has-error input[type="text"],
    .perfil-field.has-error input[type="tel"],
    .perfil-field.has-error input[type="date"] {
        border-color: #dc2626;
        background: #fef2f2;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.10);
    }
    .perfil-field.has-error .perfil-hint {
        color: #b91c1c;
    }

    .perfil-hint {
        display: block;
        font-size: 12px;
        color: #9ca3af;
        margin-top: 5px;
    }

    /* Toggle switch */
    .perfil-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 11px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        cursor: pointer;
        user-select: none;
        color: inherit;
        font-size: inherit;
        margin-bottom: 0;
    }
    .perfil-toggle:hover { background: #f9fafb; }
    .perfil-toggle-text  { font-size: 14px; color: #374151; }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 22px;
        flex-shrink: 0;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }
    .toggle-slider {
        position: absolute;
        inset: 0;
        background: #d1d5db;
        border-radius: 22px;
        transition: background 0.2s;
        cursor: pointer;
    }
    .toggle-slider::before {
        content: '';
        position: absolute;
        width: 16px;
        height: 16px;
        left: 3px;
        top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: transform 0.2s;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.18);
    }
    .toggle-switch input:checked + .toggle-slider              { background: #6366f1; }
    .toggle-switch input:checked + .toggle-slider::before      { transform: translateX(18px); }

    /* Feedback */
    .perfil-feedback {
        display: none;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 13px;
        margin-bottom: 14px;
    }
    .perfil-feedback.ok    { background: #dcfce7; color: #15803d; }
    .perfil-feedback.error { background: #fee2e2; color: #b91c1c; }
</style>

<?php
$perfilObligatorio = isset($perfilObligatorio)
    ? !empty($perfilObligatorio)
    : (
        trim((string) ($perfil['nombre'] ?? '')) === ''
        || trim((string) ($perfil['apellido'] ?? '')) === ''
        || trim((string) ($perfil['apodo'] ?? '')) === ''
        || trim((string) ($perfil['whatsapp'] ?? '')) === ''
    );
$avatarUrl = obtenerAvatarUrl($perfil['avatar_path'] ?? null);
$avatarLabel = $perfil['apodo'] ?? $perfil['nombre'] ?? $perfil['correo'] ?? 'U';
$avatarInitial = obtenerInicialAvatar($avatarLabel);
?>

<div class="perfil-overlay" id="modal-perfil" aria-hidden="true" data-required-profile="<?= $perfilObligatorio ? '1' : '0' ?>">
    <div class="perfil-box" role="dialog" aria-labelledby="perfil-title">

        <div class="perfil-header">
            <h3 id="perfil-title">Mi perfil</h3>
            <button class="btn-icon im-boton-icono" id="btn-cerrar-perfil" aria-label="Cerrar">
                <span class="material-icons">close</span>
            </button>
        </div>

        <div class="perfil-feedback" id="perfil-feedback"></div>

        <form id="form-perfil" novalidate enctype="multipart/form-data">
            <input type="hidden" name="perfil_obligatorio" value="<?= $perfilObligatorio ? '1' : '0' ?>">

            <p class="perfil-intro">
                Te pedimos estos datos para conocerte mejor, dirigirnos a vos de la forma adecuada y contar con la información de contacto necesaria para coordinar una reunión y avanzar en la creación de tu landing page. Esta información nos permite brindarte una atención más cercana, ordenada y personalizada.
            </p>

            <p class="perfil-section">Información personal</p>


            <div class="perfil-field">
                <label for="p-nombre">Nombre</label>
                <input id="p-nombre" type="text" name="nombre"
                    value="<?= htmlspecialchars($perfil['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Tu nombre">
            </div>
            <div class="perfil-field">
                <label for="p-apellido">Apellido</label>
                <input id="p-apellido" type="text" name="apellido"
                    value="<?= htmlspecialchars($perfil['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Tu apellido">
            </div>
            <div class="perfil-field">
                <label for="p-apodo">Apodo</label>
                <input id="p-apodo" type="text" name="apodo"
                    value="<?= htmlspecialchars($perfil['apodo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Apodo o nombre de marca">
            </div>
            <div class="perfil-field">
                <label for="p-fecha">Fecha de nacimiento</label>
                <input id="p-fecha" type="date" name="fecha_nacimiento"
                    value="<?= htmlspecialchars($perfil['fecha_nacimiento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <p class="perfil-section">Contacto</p>

            <!-- Correo (solo lectura) con estado de verificación -->
            <div class="perfil-field">
                <label>Correo electrónico</label>
                <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid #e5e7eb;border-radius:10px;background:#f9fafb">
                    <span style="font-size:14px;color:#374151;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= htmlspecialchars($perfil['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <?php if (!empty($perfil['check_correo'])): ?>
                        <span style="display:inline-flex;align-items:center;gap:3px;font-size:12px;font-weight:600;color:#15803d;white-space:nowrap;flex-shrink:0">
                            <span class="material-icons" style="font-size:15px">verified</span>
                            Verificado
                        </span>
                    <?php else: ?>
                        <span style="display:inline-flex;align-items:center;gap:3px;font-size:12px;font-weight:600;color:#b45309;white-space:nowrap;flex-shrink:0">
                            <span class="material-icons" style="font-size:15px">warning</span>
                            Sin verificar
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- WhatsApp -->
            <div class="perfil-field">
                <label for="p-whatsapp">WhatsApp</label>
                <input id="p-whatsapp" type="tel" name="whatsapp"
                    value="<?= htmlspecialchars($perfil['whatsapp'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    placeholder="Tu número de celular">
                <span class="perfil-hint">
                    <span class="material-icons" style="font-size:12px;vertical-align:middle">info</span>
                    Solo numeros. Codigo de area sin 0 y numero sin 15. Ej: 2618896523
                </span>
            </div>

            <p class="perfil-section">Notificaciones</p>

            <div class="perfil-field">
                <label class="perfil-toggle">
                    <span class="perfil-toggle-text">Recibir correos de Impulsa</span>
                    <span class="toggle-switch">
                        <input type="checkbox" name="permison_correo" value="1"
                            <?= !empty($perfil['permison_correo']) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </span>
                </label>
            </div>
            <div class="perfil-field">
                <label class="perfil-toggle">
                    <span class="perfil-toggle-text">Recibir mensajes de WhatsApp</span>
                    <span class="toggle-switch">
                        <input type="checkbox" name="permison_whatsapp" value="1"
                            <?= !empty($perfil['permison_whatsapp']) ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </span>
                </label>
            </div>

            <div class="perfil-field">
                <div class="perfil-avatar-preview" id="perfil-avatar-preview">
                    <?php if ($avatarUrl): ?>
                        <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar del usuario">
                    <?php else: ?>
                        <span><?= htmlspecialchars($avatarInitial, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
                <label for="p-avatar">Avatar</label>
                <input id="p-avatar" class="perfil-file-input" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                <label for="p-avatar" class="perfil-file-trigger">
                    <span class="material-icons" style="font-size:18px">add_a_photo</span>
                    <span>Subir foto de perfil</span>
                </label>
                <div class="perfil-file-name" id="perfil-file-name">Ningun archivo seleccionado</div>
                <span class="perfil-hint">Opcional. Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 3 MB.</span>
            </div>
            <button class="btn btn-aceptar im-boton im-boton--principal" type="submit" id="btn-guardar-perfil" style="width:100%;margin-top:20px">
                Guardar cambios
            </button>

        </form>
    </div>
</div>

<script>
    const modalPerfil    = document.getElementById('modal-perfil');
    const btnPerfil      = document.getElementById('btn-perfil');
    const btnCerrar      = document.getElementById('btn-cerrar-perfil');
    const formPerfil     = document.getElementById('form-perfil');
    const perfilFeedback = document.getElementById('perfil-feedback');
    const btnGuardar     = document.getElementById('btn-guardar-perfil');
    const avatarInput    = document.getElementById('p-avatar');
    const avatarPreview  = document.getElementById('perfil-avatar-preview');
    const avatarFileName = document.getElementById('perfil-file-name');
    const perfilObligatorio = modalPerfil.dataset.requiredProfile === '1';
    const avatarFallback = <?= json_encode($avatarInitial) ?>;
    const perfilBox = modalPerfil.querySelector('.perfil-box');

    const abrirModal = () => {
        modalPerfil.classList.add('is-open');
        modalPerfil.setAttribute('aria-hidden', 'false');
        document.getElementById('p-nombre').focus();
    };

    const cerrarModal = () => {
        if (perfilObligatorio) return;
        modalPerfil.classList.remove('is-open');
        modalPerfil.setAttribute('aria-hidden', 'true');
        perfilFeedback.className = 'perfil-feedback';
        perfilFeedback.style.display = 'none';
    };

    btnPerfil.addEventListener('click', abrirModal);
    btnCerrar.addEventListener('click', cerrarModal);
    modalPerfil.addEventListener('click', (e) => { if (e.target === modalPerfil) cerrarModal(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalPerfil.classList.contains('is-open')) cerrarModal();
    });

    // Normaliza WhatsApp a E.164: strips espacios/guiones/parens, reemplaza 00 → +
    function normalizarWhatsapp(raw) {
        if (!raw) return '';
        let n = raw.replace(/\D/g, '');
        return n;
    }

    function marcarErrorCampo(input) {
        const field = input.closest('.perfil-field');
        if (field) field.classList.add('has-error');
    }

    function limpiarErrorCampo(input) {
        const field = input.closest('.perfil-field');
        if (field) field.classList.remove('has-error');
    }

    function mostrarErrorPerfil(message, focusInput = null) {
        perfilFeedback.className = 'perfil-feedback error';
        perfilFeedback.textContent = message;
        perfilFeedback.style.display = 'block';
        if (perfilBox) perfilBox.scrollTo({ top: 0, behavior: 'smooth' });
        if (focusInput) focusInput.focus();
    }

    function formatearListaCampos(campos) {
        if (campos.length === 1) return campos[0];
        if (campos.length === 2) return `${campos[0]} y ${campos[1]}`;
        return `${campos.slice(0, -1).join(', ')} y ${campos[campos.length - 1]}`;
    }

    const WA_REGEX = /^\d{10,11}$/;
    const requiredFields = [
        { input: document.getElementById('p-nombre'), label: 'el nombre' },
        { input: document.getElementById('p-apellido'), label: 'el apellido' },
        { input: document.getElementById('p-apodo'), label: 'el apodo' },
        { input: document.getElementById('p-whatsapp'), label: 'el WhatsApp' },
    ];

    requiredFields.forEach(({ input }) => {
        input.addEventListener('input', () => limpiarErrorCampo(input));
        input.addEventListener('blur', () => {
            if (input.value.trim()) limpiarErrorCampo(input);
        });
    });

    avatarInput.addEventListener('change', () => {
        const file = avatarInput.files && avatarInput.files[0];
        if (!file) {
            avatarPreview.innerHTML = `<span>${avatarFallback}</span>`;
            avatarFileName.textContent = 'Ningun archivo seleccionado';
            return;
        }

        avatarFileName.textContent = file.name;
        const reader = new FileReader();
        reader.onload = () => {
            avatarPreview.innerHTML = `<img src="${reader.result}" alt="Vista previa del avatar">`;
        };
        reader.readAsDataURL(file);
    });

    formPerfil.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validar y normalizar WhatsApp antes de enviar
        const waInput = document.getElementById('p-whatsapp');
        const waNorm  = normalizarWhatsapp(waInput.value.trim());
        requiredFields.forEach(({ input }) => limpiarErrorCampo(input));

        if (perfilObligatorio) {
            const missingFields = requiredFields.filter(({ input }) => !input.value.trim());

            if (missingFields.length > 0) {
                missingFields.forEach(({ input }) => marcarErrorCampo(input));
                const camposFaltantes = formatearListaCampos(missingFields.map(({ label }) => label));
                const verbo = missingFields.length === 1 ? 'el siguiente campo' : 'los siguientes campos';
                mostrarErrorPerfil(`Necesitas completar ${verbo}: ${camposFaltantes}.`, missingFields[0].input);
                return;
            }
        }

        if (waNorm !== '' && !WA_REGEX.test(waNorm)) {
            marcarErrorCampo(waInput);
            mostrarErrorPerfil('El numero de WhatsApp debe tener solo numeros, con codigo de area sin 0 y numero sin 15. Ejemplo: 2618896523', waInput);
            return;
        }

        // Actualizar el campo con el valor normalizado para que FormData lo envíe limpio
        waInput.value = waNorm;

        btnGuardar.disabled = true;
        btnGuardar.textContent = 'Guardando...';
        perfilFeedback.style.display = 'none';

        try {
            const res  = await fetch('/partials/modal_perfil/modal_perfilController.php', {
                method: 'POST',
                body: new FormData(formPerfil),
            });
            const data = await res.json();

            if (data.ok) {
                perfilFeedback.className = 'perfil-feedback ok';
                perfilFeedback.textContent = 'Perfil guardado correctamente.';
                perfilFeedback.style.display = 'block';
                setTimeout(() => {
                    window.location.reload();
                }, 900);
            } else {
                perfilFeedback.className = 'perfil-feedback error';
                perfilFeedback.textContent = data.error ?? 'Error al guardar.';
                perfilFeedback.style.display = 'block';
            }
        } catch {
            perfilFeedback.className = 'perfil-feedback error';
            perfilFeedback.textContent = 'Error de conexión. Intentá de nuevo.';
            perfilFeedback.style.display = 'block';
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = 'Guardar cambios';
        }
    });

    if (perfilObligatorio) {
        abrirModal();
    }
</script>

