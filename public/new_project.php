<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impulsa - Nuevo Proyecto de Software</title>
    <meta name="description" content="Contanos tu idea en 3 minutos. Completa el formulario y en hasta 72 hs habiles te contactamos para planificar una reunion.">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Impulsa | Formulario de Nuevo Proyecto">
    <meta property="og:description" content="Completa este formulario para conocer mejor tu proyecto. En hasta 72 hs habiles te contactamos para planificar una reunion.">
    <meta property="og:site_name" content="Impulsa Group">
    <meta property="twitter:card" content="summary">
    <meta property="twitter:title" content="Impulsa | Formulario de Nuevo Proyecto">
    <meta property="twitter:description" content="Completa el formulario y en hasta 72 hs habiles te contactamos para planificar una reunion.">
    <?php $impulsaMaterialAssetBase = '..'; $impulsaMaterialUseValidaciones = true; require __DIR__ . '/../partials/impulsa_material_assets.php'; ?>

    <style>
        .project-public-wrap {
            max-width: 1040px;
            margin: 0 auto;
            padding: 28px 14px 44px;
        }

        .project-hero {
            background: linear-gradient(135deg, #ffffff 0%, #f5f8ff 100%);
            border: 1px solid #e8eefc;
            border-radius: 18px;
            padding: 22px;
            margin-bottom: 18px;
        }

        .project-hero h1 {
            margin: 0 0 8px;
            font-size: 30px;
            color: #0f172a;
        }

        .project-hero p {
            margin: 0;
            color: #4b5563;
            line-height: 1.6;
            font-size: 15px;
        }

        .project-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 2px 16px rgba(15, 23, 42, 0.06);
            margin-bottom: 16px;
        }

        .project-card h2 {
            margin: 0 0 12px;
            font-size: 20px;
            color: #0f172a;
        }

        .project-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .project-grid .full {
            grid-column: 1 / -1;
        }

        .project-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .project-field label,
        .project-legend {
            font-size: 13px;
            color: #334155;
            font-weight: 600;
        }

        .project-help {
            font-size: 12px;
            color: #64748b;
            margin-top: -2px;
        }

        .project-field input[type="text"],
        .project-field input[type="email"],
        .project-field input[type="tel"],
        .project-field select,
        .project-field textarea {
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

        .project-field textarea {
            resize: vertical;
            min-height: 96px;
        }

        .project-field input:focus,
        .project-field select:focus,
        .project-field textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.14);
        }

        .project-options {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-height: 46px;
        }

        .project-option {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: #334155;
            font-size: 14px;
        }

        .project-option input {
            margin-top: 2px;
            accent-color: #2563eb;
        }

        .project-actions {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
        }

        .project-required {
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
            .project-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="project-public-wrap">
        <section class="project-hero">
            <h1>Formulario de Nuevo Proyecto</h1>
            <p>
                Completando este formulario nos ayudas a estimar alcance, prioridad y version inicial
                para tu proyecto de software.
            </p>
        </section>

        <form id="new-project-form" class="form-modern im-formulario" method="post" action="new_project_controller.php">
            <section class="project-card im-tarjeta">
                <h2>Datos iniciales</h2>
                <div class="project-grid im-grilla">
                    <div class="project-field im-campo">
                        <label for="nombre">Nombre <span class="project-required">*</span></label>
                        <input type="text" id="nombre" name="nombre" required data-im-campo="nombre">
                    </div>
                    <div class="project-field im-campo">
                        <label for="nombre_proyecto">Nombre del proyecto <span class="project-required">*</span></label>
                        <input type="text" id="nombre_proyecto" name="nombre_proyecto" required data-im-campo="generico">
                    </div>
                    <div class="project-field im-campo">
                        <label for="correo">Correo <span class="project-required">*</span></label>
                        <input type="email" id="correo" name="correo" required data-im-campo="email">
                    </div>
                    <div class="project-field im-campo">
                        <label for="whatsapp">Whatsapp <span class="project-required">*</span></label>
                        <input type="tel" id="whatsapp" name="whatsapp" required placeholder="+54911XXXXXXXX" data-im-campo="whatsapp">
                    </div>
                </div>
            </section>

            <section class="project-card im-tarjeta">
                <h2>1. Sobre el proyecto</h2>
                <div class="project-grid im-grilla">
                    <div class="project-field full im-campo im-campo--ancho">
                        <label for="q1_descripcion">1. Contanos brevemente que queres desarrollar <span class="project-required">*</span></label>
                        <div class="project-help">Ejemplo: una app para turnos, una plataforma para vender cursos, un sistema interno.</div>
                        <textarea id="q1_descripcion" name="q1_descripcion" required data-im-campo="textarea"></textarea>
                    </div>

                    <div class="project-field full im-campo im-campo--ancho">
                        <label for="q2_problema">2. Que problema queres resolver con esta aplicacion <span class="project-required">*</span></label>
                        <div class="project-help">Ejemplo: ordenar procesos, vender mas, ahorrar tiempo, mejorar atencion al cliente.</div>
                        <textarea id="q2_problema" name="q2_problema" required data-im-campo="textarea"></textarea>
                    </div>

                    <div class="project-field full im-campo im-campo--ancho">
                        <label for="q3_usuarios">3. Quienes van a usar la aplicacion <span class="project-required">*</span></label>
                        <div class="project-help">Ejemplo: clientes, equipo interno, vendedores, proveedores, administradores.</div>
                        <textarea id="q3_usuarios" name="q3_usuarios" required data-im-campo="textarea"></textarea>
                    </div>

                    <div class="project-field full im-campo im-campo--ancho">
                        <label for="q4_resultado_ideal">4. Cual seria el resultado ideal para vos dentro de 6 meses <span class="project-required">*</span></label>
                        <textarea id="q4_resultado_ideal" name="q4_resultado_ideal" required data-im-campo="textarea"></textarea>
                    </div>
                </div>
            </section>

            <section class="project-card im-tarjeta">
                <h2>2. Sobre el tipo de aplicacion</h2>
                <div class="project-grid im-grilla">
                    <div class="project-field full im-campo im-campo--ancho">
                        <div class="project-legend">5. Que queres desarrollar <span class="project-required">*</span></div>
                        <div class="project-options">
                            <label class="project-option"><input type="radio" name="q5_tipo_aplicacion" value="pagina_web" required>Pagina web</label>
                            <label class="project-option"><input type="radio" name="q5_tipo_aplicacion" value="sistema_web_interno">Sistema web interno</label>
                            <label class="project-option"><input type="radio" name="q5_tipo_aplicacion" value="app_mobile">App mobile</label>
                            <label class="project-option"><input type="radio" name="q5_tipo_aplicacion" value="plataforma_web_app_mobile">Plataforma web + app mobile</label>
                            <label class="project-option"><input type="radio" name="q5_tipo_aplicacion" value="no_se">No estoy seguro/a</label>
                        </div>
                    </div>

                    <div class="project-field im-campo">
                        <div class="project-legend">6. La aplicacion necesita registro e inicio de sesion <span class="project-required">*</span></div>
                        <div class="project-options">
                            <label class="project-option"><input type="radio" name="q6_login" value="si" required>Si</label>
                            <label class="project-option"><input type="radio" name="q6_login" value="no">No</label>
                            <label class="project-option"><input type="radio" name="q6_login" value="no_se">No lo se</label>
                        </div>
                    </div>

                    <div class="project-field im-campo">
                        <div class="project-legend">7. Quienes tendrian acceso <span class="project-required">*</span></div>
                        <div class="project-options">
                            <label class="project-option"><input type="radio" name="q7_acceso" value="solo_equipo" required>Solo mi equipo</label>
                            <label class="project-option"><input type="radio" name="q7_acceso" value="clientes">Clientes</label>
                            <label class="project-option"><input type="radio" name="q7_acceso" value="proveedores">Proveedores</label>
                            <label class="project-option"><input type="radio" name="q7_acceso" value="roles_diferentes">Diferentes tipos de usuarios</label>
                            <label class="project-option"><input type="radio" name="q7_acceso" value="no_se">No lo se todavia</label>
                        </div>
                    </div>
                </div>
            </section>

            <section class="project-card im-tarjeta">
                <h2>3. Sobre funcionalidades</h2>
                <div class="project-grid im-grilla">
                    <div class="project-field full im-campo im-campo--ancho">
                        <label for="q8_funciones_minimas">8. Que cosas si o si tiene que permitir la primera version <span class="project-required">*</span></label>
                        <div class="project-help">Pensa en las funciones minimas para que el proyecto tenga sentido.</div>
                        <textarea id="q8_funciones_minimas" name="q8_funciones_minimas" required data-im-campo="textarea"></textarea>
                    </div>

                    <div class="project-field full im-campo im-campo--ancho">
                        <div class="project-legend">9. Marca las funciones que crees que vas a necesitar</div>
                        <div class="project-options">
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="registro_login">Registro e inicio de sesion</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="perfil_usuario">Perfil de usuario</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="panel_admin">Panel de administracion</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="carga_edicion_datos">Carga y edicion de datos</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="busqueda_filtros">Busqueda y filtros</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="agenda_turnos">Agenda o turnos</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="pagos_online">Pagos online</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="notificaciones_email_whatsapp">Notificaciones por mail o Whatsapp</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="reportes_metricas">Reportes o metricas</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="subida_archivos_imagenes">Subida de archivos o imagenes</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="geolocalizacion_mapas">Geolocalizacion o mapas</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="integracion_sistemas">Integracion con otros sistemas</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="chat_mensajeria">Chat o mensajeria</label>
                            <label class="project-option"><input type="checkbox" name="q9_funcionalidades[]" value="no_se">No estoy seguro/a</label>
                        </div>
                    </div>

                    <div class="project-field full im-campo im-campo--ancho">
                        <label for="q10_admin_vs_usuario">10. Hay algo que queres que puedan hacer los administradores y no los usuarios comunes <span class="project-required">*</span></label>
                        <textarea id="q10_admin_vs_usuario" name="q10_admin_vs_usuario" required data-im-campo="textarea"></textarea>
                    </div>

                    <div class="project-field full im-campo im-campo--ancho">
                        <label for="q11_integraciones">11. La app necesita conectarse con alguna herramienta que ya usas <span class="project-required">*</span></label>
                        <div class="project-help">Ejemplo: Whatsapp, Mercado Pago, Stripe, Google Calendar, Excel, CRM, ERP.</div>
                        <textarea id="q11_integraciones" name="q11_integraciones" required data-im-campo="textarea"></textarea>
                    </div>
                </div>
            </section>

            <section class="project-card im-tarjeta">
                <h2>4. Sobre operacion y contenido</h2>
                <div class="project-grid im-grilla">
                    <div class="project-field im-campo">
                        <label for="q12_contenido">12. Ya tenes definido el contenido, informacion o procesos de la app <span class="project-required">*</span></label>
                        <select id="q12_contenido" name="q12_contenido" required data-im-campo="generico">
                            <option value="">Seleccionar</option>
                            <option value="claro">Si, bastante claro</option>
                            <option value="medio">Mas o menos</option>
                            <option value="no">Todavia no</option>
                        </select>
                    </div>

                    <div class="project-field im-campo">
                        <label for="q14_diseno">14. Ya contas con logo, identidad visual o diseno <span class="project-required">*</span></label>
                        <select id="q14_diseno" name="q14_diseno" required data-im-campo="generico">
                            <option value="">Seleccionar</option>
                            <option value="completa">Si, ya tengo todo</option>
                            <option value="parcial">Tengo algo, pero falta</option>
                            <option value="no">No, lo necesito tambien</option>
                        </select>
                    </div>

                    <div class="project-field full im-campo im-campo--ancho">
                        <label for="q13_referencias">13. Tenes referencias de apps o sistemas parecidos <span class="project-required">*</span></label>
                        <div class="project-help">Podes compartir nombres o links.</div>
                        <textarea id="q13_referencias" name="q13_referencias" required data-im-campo="textarea"></textarea>
                    </div>
                </div>
            </section>

            <section class="project-card im-tarjeta">
                <h2>5. Sobre prioridad y decision</h2>
                <div class="project-grid im-grilla">
                    <div class="project-field im-campo">
                        <label for="q15_urgencia">15. Que tan urgente es este proyecto <span class="project-required">*</span></label>
                        <select id="q15_urgencia" name="q15_urgencia" required data-im-campo="generico">
                            <option value="">Seleccionar</option>
                            <option value="cuanto_antes">Lo quiero empezar cuanto antes</option>
                            <option value="1_2_meses">En 1 a 2 meses</option>
                            <option value="mas_adelante">Mas adelante</option>
                            <option value="explorando">Solo estoy explorando opciones</option>
                        </select>
                    </div>

                    <div class="project-field im-campo">
                        <label for="q16_presupuesto">16. Tenes una idea de presupuesto para este proyecto <span class="project-required">*</span></label>
                        <select id="q16_presupuesto" name="q16_presupuesto" required data-im-campo="generico">
                            <option value="">Seleccionar</option>
                            <option value="sin_definir">Todavia no</option>
                            <option value="menos_1000000">Menos de $1.000.000</option>
                            <option value="entre_1000000_2000000">Entre $1.000.000 y $2.000.000</option>
                            <option value="mas_2000000">Mas de $2.000.000</option>
                        </select>
                    </div>

                    <div class="project-field im-campo">
                        <label for="q17_modalidad">17. Preferis avanzar por etapas o resolver todo en una sola propuesta <span class="project-required">*</span></label>
                        <select id="q17_modalidad" name="q17_modalidad" required data-im-campo="generico">
                            <option value="">Seleccionar</option>
                            <option value="por_etapas">Por etapas</option>
                            <option value="todo_junto">Todo junto</option>
                            <option value="necesito_recomendacion">Necesito recomendacion</option>
                        </select>
                    </div>

                    <div class="project-field full im-campo im-campo--ancho">
                        <label for="q18_adicional">18. Hay algo importante que no te preguntamos y deberiamos saber antes de armar una propuesta <span class="project-required">*</span></label>
                        <textarea id="q18_adicional" name="q18_adicional" required data-im-campo="textarea"></textarea>
                    </div>
                </div>

                <div class="project-actions">
                    <button class="btn btn-aceptar im-boton im-boton--principal" type="submit">Enviar formulario</button>
                </div>
            </section>
        </form>
    </main>

    <div class="confirm-modal-backdrop" id="confirmModalBackdrop" aria-hidden="true">
        <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
            <h3 id="confirm-modal-title">Confirmar envio</h3>
            <p>Vamos a enviar tu formulario con la informacion cargada. Queres continuar?</p>
            <div class="confirm-modal-actions">
                <button type="button" class="btn btn-cancelar im-boton--tonal im-boton" id="confirmCancelBtn">Cancelar</button>
                <button type="button" class="btn btn-aceptar im-boton im-boton--principal" id="confirmAcceptBtn">Aceptar</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const form = document.getElementById('new-project-form');
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
                    const response = await fetch('new_project_controller.php', {
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

                const q9Checked = form.querySelectorAll('input[name="q9_funcionalidades[]"]:checked').length;
                if (q9Checked === 0) {
                    alert('Selecciona al menos una opcion en funcionalidades.');
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
