function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleIcon = document.getElementById('collapseIcon');

    if (!sidebar) return;

    if (window.innerWidth <= 768) {
        sidebar.classList.toggle('open');
        return;
    }

    sidebar.classList.toggle('collapsed');
    if (toggleIcon) {
        toggleIcon.textContent = sidebar.classList.contains('collapsed') ? 'chevron_right' : 'chevron_left';
    }
}

function openModal() {
    const modal = document.getElementById('modal');
    if (modal) modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('modal');
    if (modal) modal.classList.add('hidden');
}

function showSpinner() {
    const spinner = document.getElementById('globalSpinner');
    if (spinner) spinner.classList.remove('hidden');
}

function hideSpinner() {
    const spinner = document.getElementById('globalSpinner');
    if (spinner) spinner.classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-button');

    tabButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');

            tabButtons.forEach((btn) => btn.classList.remove('active'));
            button.classList.add('active');

            document.querySelectorAll('.tab-content').forEach((content) => {
                content.classList.toggle('active', content.id === tabId);
            });
        });
    });

    document.querySelectorAll('textarea[maxlength]').forEach((textarea) => {
        const id = textarea.id;
        const max = parseInt(textarea.getAttribute('maxlength'), 10);
        const counter = document.querySelector(`.char-count[data-for="${id}"]`);

        if (!counter || Number.isNaN(max)) return;

        const updateCount = () => {
            const remaining = max - textarea.value.length;
            counter.textContent = `Quedan ${remaining} caracteres.`;
            counter.classList.toggle('warning', remaining <= 20);
        };

        textarea.addEventListener('input', updateCount);
        updateCount();
    });

    const formPublicacion = document.getElementById('form-publicacion');
    const btnGuardar = document.getElementById('btn-guardar');

    if (formPublicacion && btnGuardar) {
        const camposRequeridos = formPublicacion.querySelectorAll('[required]');
        const validarCampos = () => {
            const completo = Array.from(camposRequeridos).every((campo) => campo.value.trim());

            btnGuardar.disabled = !completo;
            btnGuardar.classList.toggle('btn-disabled', !completo);
            btnGuardar.classList.toggle('btn-aceptar', completo);
            btnGuardar.textContent = completo ? 'Publicar' : 'Completar datos...';
        };

        formPublicacion.addEventListener('input', validarCampos);
        validarCampos();
    }
});
