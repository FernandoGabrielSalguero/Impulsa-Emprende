<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impulsa - Solicitud de Landing Page</title>
    <meta name="description" content="Completa este formulario para que podamos conocer mejor tu proyecto de landing page y proponerte una solucion alineada a tus necesidades.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Impulsa | Formulario para Landing Page">
    <meta property="og:description" content="Formulario para clientes interesados en desarrollar una landing page.">
    <meta property="og:site_name" content="Impulsa Group">
    <meta property="twitter:card" content="summary">
    <meta property="twitter:title" content="Impulsa | Formulario para Landing Page">
    <meta property="twitter:description" content="Contanos tu proyecto para avanzar de forma mas ordenada y eficiente.">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../assets/framework/framework.css">
    <script src="../assets/framework/framework.js" defer></script>

    <style>
        .page-public-wrap {
            max-width: 1040px;
            margin: 0 auto;
            padding: 28px 14px 44px;
        }

        .page-hero {
            background: linear-gradient(135deg, #ffffff 0%, #f7fbff 55%, #eef7f4 100%);
            border: 1px solid #e2eef4;
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 18px;
        }

        .page-hero h1 {
            margin: 0 0 8px;
            font-size: 30px;
            color: #0f172a;
        }

        .page-hero p {
            margin: 0;
            color: #4b5563;
            line-height: 1.6;
            font-size: 15px;
        }

        .page-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 2px 16px rgba(15, 23, 42, 0.06);
            margin-bottom: 16px;
        }

        .page-card h2 {
            margin: 0 0 12px;
            font-size: 20px;
            color: #0f172a;
        }

        .page-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .page-grid .full {
            grid-column: 1 / -1;
        }

        .page-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .page-field label {
            font-size: 13px;
            color: #334155;
            font-weight: 600;
        }

        .page-help {
            font-size: 12px;
            color: #64748b;
            margin-top: -2px;
        }

        .page-field input[type="text"],
        .page-field input[type="email"],
        .page-field input[type="tel"],
        .page-field textarea {
            width: 100%;
            border: 1px solid #d7dce8;
            border-radius: 10px;
            padding: 10px 12px;
            background: #fff;
            color: #0f172a;
            font-size: 14px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .page-field input[type="text"],
        .page-field input[type="email"],
        .page-field input[type="tel"] {
            height: 42px;
        }

        .page-field textarea {
            resize: vertical;
            min-height: 96px;
        }

        .page-field input:focus,
        .page-field textarea:focus {
            outline: none;
            border-color: #0f766e;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.14);
        }

        .page-actions {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
        }

        .page-required {
            color: #dc2626;
            font-weight: 700;
        }

        .confirm-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
            z-index: 999;
        }

        .confirm-modal-backdrop.is-open {
            display: flex;
        }

        .confirm-modal {
            width: min(100%, 480px);
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 24px 55px rgba(15, 23, 42, 0.24);
        }

        .confirm-modal h3 {
            margin: 0 0 10px;
            color: #0f172a;
            font-size: 22px;
        }

        .confirm-modal p {
            margin: 0;
            color: #475569;
            line-height: 1.5;
        }

        .confirm-modal-actions {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 820px) {
            .page-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="page-public-wrap">
        <section class="page-hero">
            <h1>Formulario para clientes interesados en una Landing Page</h1>
            <p>
                Gracias por tu interes en desarrollar tu landing page. Este formulario nos permitira conocer mejor
                tu proyecto, tus objetivos y los recursos disponibles, para poder proponerte una solucion alineada
                a tus necesidades y avanzar de forma mas ordenada y eficiente.
            </p>
        </section>

        <form id="new-page-form" class="form-modern" method="post" action="new_page_controller.php">
            <section class="page-card">
                <h2>Datos iniciales</h2>
                <div class="page-grid">
                    <div class="page-field">
                        <label for="nombre">Nombre <span class="page-required">*</span></label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>

                    <div class="page-field">
                        <label for="nombre_proyecto">Nombre del proyecto <span class="page-required">*</span></label>
                        <input type="text" id="nombre_proyecto" name="nombre_proyecto" required>
                    </div>

                    <div class="page-field">
                        <label for="correo">Correo <span class="page-required">*</span></label>
                        <input type="email" id="correo" name="correo" required>
                    </div>

                    <div class="page-field">
                        <label for="whatsapp">Whatsapp <span class="page-required">*</span></label>
                        <input type="tel" id="whatsapp" name="whatsapp" required placeholder="+54911XXXXXXXX">
                    </div>
                </div>
            </section>

            <section class="page-card">
                <h2>Informacion del proyecto</h2>
                <div class="page-grid">
                    <div class="page-field full">
                        <label for="q1_nombre_comercial">1. &iquest;Cual es el nombre comercial de tu empresa o proyecto? <span class="page-required">*</span></label>
                        <div class="page-help">Podes incluir tambien la razon social, si corresponde.</div>
                        <textarea id="q1_nombre_comercial" name="q1_nombre_comercial" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q2_actividad">2. &iquest;A que se dedica tu empresa, marca o emprendimiento? <span class="page-required">*</span></label>
                        <div class="page-help">Describi brevemente tu actividad principal, servicios o productos.</div>
                        <textarea id="q2_actividad" name="q2_actividad" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q3_objetivo">3. &iquest;Cual es el objetivo principal de la landing page? <span class="page-required">*</span></label>
                        <div class="page-help">Por ejemplo: generar consultas, captar potenciales clientes, vender, agendar turnos, mostrar servicios, presentar la marca o lanzar un producto.</div>
                        <textarea id="q3_objetivo" name="q3_objetivo" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q4_publico">4. &iquest;A que publico esta dirigida la pagina? <span class="page-required">*</span></label>
                        <div class="page-help">Describi el tipo de cliente ideal: edad, perfil, ubicacion, intereses o necesidad principal.</div>
                        <textarea id="q4_publico" name="q4_publico" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q5_accion_principal">5. &iquest;Que accion principal queres que realice el visitante al entrar al sitio? <span class="page-required">*</span></label>
                        <div class="page-help">Por ejemplo: escribir por WhatsApp, completar un formulario, pedir presupuesto, comprar, reservar o descargar un archivo.</div>
                        <textarea id="q5_accion_principal" name="q5_accion_principal" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q6_propuestas_destacar">6. &iquest;Que servicios, productos o propuestas queres destacar si o si en la pagina? <span class="page-required">*</span></label>
                        <div class="page-help">Indica cuales son las prioridades comerciales.</div>
                        <textarea id="q6_propuestas_destacar" name="q6_propuestas_destacar" required></textarea>
                    </div>
                </div>
            </section>

            <section class="page-card">
                <h2>Contenido y diferenciacion</h2>
                <div class="page-grid">
                    <div class="page-field full">
                        <label for="q7_diferencial">7. &iquest;Que te diferencia de tu competencia? <span class="page-required">*</span></label>
                        <div class="page-help">Contanos tus fortalezas, beneficios, experiencia, metodologia, tiempos, precios, calidad, atencion, garantia, etc.</div>
                        <textarea id="q7_diferencial" name="q7_diferencial" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q8_secciones">8. &iquest;Que secciones queres incluir en la landing page? <span class="page-required">*</span></label>
                        <div class="page-help">Por ejemplo: inicio, sobre nosotros, servicios, beneficios, galeria, testimonios, preguntas frecuentes, contacto, mapa, formulario o llamada a la accion.</div>
                        <textarea id="q8_secciones" name="q8_secciones" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q9_textos">9. &iquest;Ya contas con los textos para la pagina o necesitas ayuda para redactarlos? <span class="page-required">*</span></label>
                        <div class="page-help">Aclarar si ya tienen contenido listo, borradores o si necesitan apoyo con textos comerciales.</div>
                        <textarea id="q9_textos" name="q9_textos" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q10_contacto">10. &iquest;Que informacion de contacto queres mostrar en la web? <span class="page-required">*</span></label>
                        <div class="page-help">Indica telefono, WhatsApp, correo, direccion, horarios, redes sociales y cualquier otro dato importante.</div>
                        <textarea id="q10_contacto" name="q10_contacto" required></textarea>
                    </div>
                </div>
            </section>

            <section class="page-card">
                <h2>Marca y estilo visual</h2>
                <div class="page-grid">
                    <div class="page-field full">
                        <label for="q11_material_marca">11. &iquest;Tenes material de marca disponible? <span class="page-required">*</span></label>
                        <div class="page-help">Por ejemplo: logo en buena calidad, colores corporativos, tipografias, manual de marca o piezas graficas existentes.</div>
                        <textarea id="q11_material_marca" name="q11_material_marca" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q12_estilo_visual">12. &iquest;Que estilo visual queres para la landing page? <span class="page-required">*</span></label>
                        <div class="page-help">Podes describirlo como: moderna, minimalista, elegante, premium, corporativa, sobria, creativa, tecnica, calida, juvenil, etc.</div>
                        <textarea id="q12_estilo_visual" name="q12_estilo_visual" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q13_referencias">13. &iquest;Tenes ejemplos de paginas web que te gusten? <span class="page-required">*</span></label>
                        <div class="page-help">Comparti enlaces y explica que te gusta de cada una: diseno, estructura, colores, animaciones, claridad, velocidad, etc.</div>
                        <textarea id="q13_referencias" name="q13_referencias" required></textarea>
                    </div>
                </div>
            </section>

            <section class="page-card">
                <h2>Recursos y configuracion</h2>
                <div class="page-grid">
                    <div class="page-field full">
                        <label for="q14_recursos_visuales">14. &iquest;Contas con imagenes, videos o recursos visuales propios para usar en la web? <span class="page-required">*</span></label>
                        <div class="page-help">Por ejemplo: fotos de productos, equipo, instalaciones, trabajos realizados, videos, banners, catalogos o flyers.</div>
                        <textarea id="q14_recursos_visuales" name="q14_recursos_visuales" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q15_imagenes_apoyo">15. Si no tenes material visual suficiente, &iquest;queres que trabajemos con imagenes de apoyo o de referencia de forma temporal? <span class="page-required">*</span></label>
                        <div class="page-help">Esto ayuda a avanzar mientras se reune el material definitivo.</div>
                        <textarea id="q15_imagenes_apoyo" name="q15_imagenes_apoyo" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q16_dominio_hosting">16. &iquest;Ya tenes dominio y hosting contratados? <span class="page-required">*</span></label>
                        <div class="page-help">Indica si ya los tenes, con que proveedor, o si necesitas ayuda para elegirlos y configurarlos.</div>
                        <textarea id="q16_dominio_hosting" name="q16_dominio_hosting" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q17_correos_corporativos">17. &iquest;Queres que la pagina tenga correos corporativos con tu dominio? <span class="page-required">*</span></label>
                        <div class="page-help">Por ejemplo: info@tuempresa.com, ventas@tuempresa.com o contacto@tuempresa.com. En caso de que si, indica cuantos necesitarias aproximadamente.</div>
                        <textarea id="q17_correos_corporativos" name="q17_correos_corporativos" required></textarea>
                    </div>

                    <div class="page-field full">
                        <label for="q18_requerimientos_adicionales">18. &iquest;Hay algun requerimiento adicional que debamos tener en cuenta antes de comenzar? <span class="page-required">*</span></label>
                        <div class="page-help">Podes incluir fechas estimadas, necesidades tecnicas, medios de pago, integracion con WhatsApp, formulario especial, pixel de Meta, Google Analytics, politicas legales, aprobadores del proyecto o cualquier preferencia importante.</div>
                        <textarea id="q18_requerimientos_adicionales" name="q18_requerimientos_adicionales" required></textarea>
                    </div>
                </div>

                <div class="page-actions">
                    <button class="btn btn-aceptar" type="submit">Enviar formulario</button>
                </div>
            </section>
        </form>
    </main>

    <div class="confirm-modal-backdrop" id="confirmModalBackdrop" aria-hidden="true">
        <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
            <h3 id="confirm-modal-title">Confirmar envio</h3>
            <p>Vamos a enviar tu formulario con la informacion cargada. Queres continuar?</p>
            <div class="confirm-modal-actions">
                <button type="button" class="btn btn-cancelar" id="confirmCancelBtn">Cancelar</button>
                <button type="button" class="btn btn-aceptar" id="confirmAcceptBtn">Aceptar</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('new-page-form');
            const modalBackdrop = document.getElementById('confirmModalBackdrop');
            const confirmCancelBtn = document.getElementById('confirmCancelBtn');
            const confirmAcceptBtn = document.getElementById('confirmAcceptBtn');

            let pendingSubmit = false;

            function openModal() {
                modalBackdrop.classList.add('is-open');
                modalBackdrop.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                modalBackdrop.classList.remove('is-open');
                modalBackdrop.setAttribute('aria-hidden', 'true');
            }

            async function sendForm() {
                if (pendingSubmit) return;

                pendingSubmit = true;
                confirmAcceptBtn.disabled = true;
                confirmAcceptBtn.textContent = 'Enviando...';

                try {
                    const response = await fetch('new_page_controller.php', {
                        method: 'POST',
                        body: new FormData(form),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.ok) {
                        alert(data.error || 'No se pudo enviar el formulario. Intenta nuevamente.');
                        return;
                    }

                    alert('Formulario enviado correctamente.');
                    window.location.href = 'https://impulsagroup.com/';
                } catch (error) {
                    alert('Ocurrio un error de conexion. Intenta nuevamente.');
                } finally {
                    pendingSubmit = false;
                    confirmAcceptBtn.disabled = false;
                    confirmAcceptBtn.textContent = 'Aceptar';
                }
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                openModal();
            });

            confirmCancelBtn.addEventListener('click', closeModal);
            confirmAcceptBtn.addEventListener('click', function () {
                closeModal();
                sendForm();
            });

            modalBackdrop.addEventListener('click', function (event) {
                if (event.target === modalBackdrop) {
                    closeModal();
                }
            });
        })();
    </script>
</body>
</html>
