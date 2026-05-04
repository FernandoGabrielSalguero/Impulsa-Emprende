📚 Estructura completa de la base de datos: u104036906_gestionImpulsa
📄 Tabla: admin_tareas
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
nombre_tarea	varchar(180)	NO			
responsable_user_id	int(10) unsigned	NO	MUL		
descripcion	text	NO			
fecha_entrega	date	NO			
prioridad_defcon	tinyint(1) unsigned	NO	MUL		
reporta_a	varchar(180)	NO			
estado	enum('pendiente','en_progreso','completada','cancelada')	NO	MUL	pendiente	
created_by_user_id	int(10) unsigned	NO	MUL		
completed_at	datetime	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna created_by_user_id referencia a user_auth.id
Columna responsable_user_id referencia a user_auth.id
📄 Tabla: contacto_landing
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
nombre	varchar(150)	NO			
empresa	varchar(150)	NO			
email	varchar(190)	NO	MUL		
telefono	varchar(80)	NO			
equipo	varchar(80)	YES			
objetivo	varchar(120)	YES			
mensaje	text	YES			
form_source	varchar(100)	NO		landing-impulsa	
ip_address	varchar(45)	YES			
user_agent	varchar(255)	YES			
created_at	timestamp	NO	MUL	current_timestamp()	

📄 Tabla: correos_log
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	YES	MUL		
correo	varchar(255)	NO			
asunto	varchar(255)	NO			
template	varchar(100)	YES			
mensaje_html	longtext	YES			
mensaje_text	text	YES			
estado	enum('enviado','fallido')	NO	MUL	fallido	
error	text	YES			
meta	longtext	YES			
created_at	timestamp	NO		current_timestamp()	

📄 Tabla: emprendedor_buyer_persona
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
cliente_ideal	text	NO			
edad_etapa_vida	text	NO			
ocupacion_realidad_diaria	text	NO			
problema_necesidad	text	NO			
preocupacion_frustracion	text	NO			
objetivo_mejora	text	NO			
motivacion_busqueda	text	NO			
freno_dudas	text	NO			
criterio_eleccion	text	NO			
busqueda_informacion	text	NO			
decision_compra	text	NO			
motivo_eleccion	text	NO			
buyer_persona_estructura	longtext	NO			
completado	tinyint(1)	NO		0	
created_at	timestamp	YES		current_timestamp()	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna user_auth_id referencia a user_auth.id
📄 Tabla: emprendedor_mision
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
a_quien_ayudo	varchar(255)	NO			
que_problema_resuelvo	text	NO			
como_lo_resuelvo	text	NO			
mision_estructura	text	NO			
completado	tinyint(1)	NO		0	
created_at	timestamp	YES		current_timestamp()	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna user_auth_id referencia a user_auth.id
📄 Tabla: emprendedor_vision
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
conversion_futura	text	NO			
lugar_mercado	text	NO			
impacto_generado	text	NO			
vision_estructura	text	NO			
completado	tinyint(1)	NO		0	
created_at	timestamp	YES		current_timestamp()	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna user_auth_id referencia a user_auth.id
📄 Tabla: forms_clients_contact
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
page	varchar(150)	NO			
contact_nombre	varchar(150)	NO			
contact_whatsapp	varchar(50)	YES			
contact_email	varchar(150)	YES			
contact_description	text	YES			
contact_consultation	varchar(255)	YES			
state	enum('recibido','cancelado','aprobado')	NO		recibido	
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

📄 Tabla: landing_page_request
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
nombre_emprendimiento	varchar(255)	NO			
fecha_inicio	date	NO			
descripcion	text	NO			
dominio_registrado	tinyint(1)	NO		0	
hosting_propio	tinyint(1)	NO		0	
cantidad_colaboradores	int(10) unsigned	NO		1	
nombre_fundador	varchar(255)	NO			
vende_productos	tinyint(1)	NO		0	
vende_servicios	tinyint(1)	NO		0	
ya_factura	tinyint(1)	NO		0	
espacio_fisico	tinyint(1)	NO		0	
rubro_categoria_id	int(10) unsigned	YES	MUL		
rubro_subcategoria_id	int(10) unsigned	YES	MUL		
pais	varchar(100)	YES			
provincia	varchar(100)	YES			
localidad	varchar(100)	YES			
calle	varchar(255)	YES			
numero	varchar(20)	YES			
telefono_contacto	varchar(30)	NO			
completado	tinyint(1)	NO		0	
created_at	timestamp	YES		current_timestamp()	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna rubro_categoria_id referencia a rubro_emprendedor_categoria.id
Columna rubro_subcategoria_id referencia a rubro_emprendedor_subcategoria.id
Columna user_auth_id referencia a user_auth.id
📄 Tabla: landing_page_requests_external
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
nombre	varchar(150)	NO			
nombre_proyecto	varchar(180)	NO			
correo	varchar(190)	NO	MUL		
whatsapp	varchar(80)	NO			
q1_nombre_comercial	text	NO			
q2_actividad	text	NO			
q3_objetivo	text	NO			
q4_publico	text	NO			
q5_accion_principal	text	NO			
q6_propuestas_destacar	text	NO			
q7_diferencial	text	NO			
q8_secciones	text	NO			
q9_textos	text	NO			
q10_contacto	text	NO			
q11_material_marca	text	NO			
q12_estilo_visual	text	NO			
q13_referencias	text	NO			
q14_recursos_visuales	text	NO			
q15_imagenes_apoyo	text	NO			
q16_dominio_hosting	text	NO			
q17_correos_corporativos	text	NO			
q18_requerimientos_adicionales	text	NO			
form_source	varchar(80)	NO	MUL	public-new-page	
ip_address	varchar(45)	YES			
user_agent	varchar(255)	YES			
created_at	timestamp	NO	MUL	current_timestamp()	

📄 Tabla: project_contracts
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
project_id	bigint(20) unsigned	NO	UNI		
contract_name	varchar(180)	NO			
contract_html	longtext	NO			
contract_text	longtext	YES			
version_number	int(10) unsigned	NO		1	
is_signed	tinyint(1)	NO	MUL	0	
signed_at	datetime	YES			
signed_by_user_id	int(10) unsigned	YES	MUL		
signer_full_name	varchar(190)	YES			
signer_ip	varchar(45)	YES			
created_by_user_id	int(10) unsigned	YES	MUL		
updated_by_user_id	int(10) unsigned	YES	MUL		
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna created_by_user_id referencia a user_auth.id
Columna project_id referencia a projects.id
Columna signed_by_user_id referencia a user_auth.id
Columna updated_by_user_id referencia a user_auth.id
📄 Tabla: project_deliverable_tasks
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
deliverable_id	bigint(20) unsigned	NO	MUL		
task_order	int(10) unsigned	NO		1	
title	varchar(255)	NO			
due_date	date	YES	MUL		
is_completed	tinyint(1)	NO		0	
completed_at	datetime	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna deliverable_id referencia a project_deliverables.id
📄 Tabla: project_deliverables
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
project_id	bigint(20) unsigned	NO	MUL		
phase_id	bigint(20) unsigned	YES	MUL		
title	varchar(180)	NO			
description	text	YES			
deliverable_type	enum('document','design','development','deployment','training','other')	NO		other	
status	enum('pending','in_progress','ready_for_review','delivered')	NO		pending	
due_date	date	YES			
delivered_at	datetime	YES			
client_visible	tinyint(1)	NO		1	
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna phase_id referencia a project_phases.id
Columna project_id referencia a projects.id
📄 Tabla: project_phases
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
project_id	bigint(20) unsigned	NO	MUL		
title	varchar(180)	NO			
description	text	YES			
duration_days	int(10) unsigned	YES			
phase_order	int(10) unsigned	NO		1	
status	enum('pending','in_progress','blocked','done')	NO	MUL	pending	
due_date	date	YES			
completed_at	datetime	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna project_id referencia a projects.id
📄 Tabla: project_scope_request
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
nombre	varchar(150)	NO			
nombre_proyecto	varchar(180)	NO			
correo	varchar(190)	NO	MUL		
whatsapp	varchar(80)	NO			
q1_descripcion	text	NO			
q2_problema	text	NO			
q3_usuarios	text	NO			
q4_resultado_ideal	text	NO			
q5_tipo_aplicacion	enum('pagina_web','sistema_web_interno','app_mobile','plataforma_web_app_mobile','no_se')	NO			
q6_login	enum('si','no','no_se')	NO			
q7_acceso	enum('solo_equipo','clientes','proveedores','roles_diferentes','no_se')	NO			
q8_funciones_minimas	text	NO			
q9_funcionalidades	longtext	YES			
q10_admin_vs_usuario	text	YES			
q11_integraciones	text	YES			
q12_contenido	enum('claro','medio','no')	NO			
q13_referencias	text	YES			
q14_diseno	enum('completa','parcial','no')	NO			
q15_urgencia	enum('cuanto_antes','1_2_meses','mas_adelante','explorando')	NO	MUL		
q16_presupuesto	enum('sin_definir','menos_1000000','entre_1000000_2000000','mas_2000000')	NO			
q17_modalidad	enum('por_etapas','todo_junto','necesito_recomendacion')	NO			
q18_adicional	text	YES			
form_source	varchar(100)	NO		public-new-project	
estado	enum('nuevo','revisado','descartado')	NO	MUL	nuevo	
ip_address	varchar(45)	YES			
user_agent	varchar(255)	YES			
created_at	timestamp	NO	MUL	current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

📄 Tabla: project_updates
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
project_id	bigint(20) unsigned	NO	MUL		
phase_id	bigint(20) unsigned	YES	MUL		
created_by	int(10) unsigned	NO	MUL		
title	varchar(180)	NO			
message	text	NO			
progress_delta	smallint(6)	YES			
visible_to_client	tinyint(1)	NO		1	
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones:
Columna phase_id referencia a project_phases.id
Columna project_id referencia a projects.id
Columna created_by referencia a user_auth.id
📄 Tabla: projects
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
source_type	varchar(40)	YES	MUL		
source_id	bigint(20) unsigned	YES			
project_name	varchar(180)	NO			
project_type	enum('software','landing_page','website','manual')	NO		software	
client_user_id	int(10) unsigned	YES	MUL		
manager_user_id	int(10) unsigned	NO	MUL		
client_name	varchar(150)	NO			
client_email	varchar(190)	NO			
client_whatsapp	varchar(80)	YES			
summary	text	YES			
scope_summary	text	YES			
status	enum('draft','planned','in_progress','paused','in_review','completed','cancelled')	NO	MUL	planned	
priority	enum('low','medium','high','urgent')	NO		medium	
start_date	date	YES			
target_delivery_date	date	YES			
actual_delivery_date	date	YES			
progress_percent	tinyint(3) unsigned	NO		0	
client_visible	tinyint(1)	NO		1	
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna client_user_id referencia a user_auth.id
Columna manager_user_id referencia a user_auth.id
📄 Tabla: rubro_emprendedor_categoria
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
nombre	varchar(150)	NO	UNI		
descripcion	text	YES			

📄 Tabla: rubro_emprendedor_relaciones
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
categoria_id	int(10) unsigned	NO	MUL		
subcategoria_id	int(10) unsigned	NO	MUL		

🔗 Relaciones:
Columna categoria_id referencia a rubro_emprendedor_categoria.id
Columna subcategoria_id referencia a rubro_emprendedor_subcategoria.id
📄 Tabla: rubro_emprendedor_subcategoria
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
nombre	varchar(150)	NO	UNI		
descripcion	text	YES			

📄 Tabla: user_auth
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
correo	varchar(255)	NO	UNI		
password	varchar(255)	NO			
rol	enum('impulsa_administrador','impulsa_colaborador','impulsa_emprendedor','impulsa_cliente')	NO			
verification_token	varchar(100)	YES			
email_verified_at	timestamp	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

📄 Tabla: user_contacto
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
correo	varchar(255)	NO			
check_correo	tinyint(1)	NO		0	
permison_correo	tinyint(1)	NO		1	
whatsapp	varchar(30)	YES			
check_whatsapp	tinyint(1)	NO		0	
permison_whatsapp	tinyint(1)	NO		1	
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna user_auth_id referencia a user_auth.id
📄 Tabla: user_info
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
user_auth_id	int(10) unsigned	NO	UNI		
nombre	varchar(100)	YES			
apellido	varchar(100)	YES			
apodo	varchar(100)	YES			
avatar_path	varchar(255)	YES			
fecha_nacimiento	date	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	NO		current_timestamp()	on update current_timestamp()

🔗 Relaciones:
Columna user_auth_id referencia a user_auth.id
📄 Tabla: visit_user_page
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
page	varchar(150)	NO	MUL		
visited_at	timestamp	NO	MUL	current_timestamp()	
