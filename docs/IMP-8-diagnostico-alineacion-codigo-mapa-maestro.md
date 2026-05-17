# IMP-8 - Diagnostico de alineacion entre codigo y Mapa Maestro

Fecha: 2026-05-05

## 1. Lectura general

El codigo esta parcialmente alineado con el Mapa Maestro de Impulsa.

La base tecnica existe y representa bastante bien la idea central: una plataforma PHP + MySQL con roles, registro, onboarding de emprendedores, paneles por perfil, solicitudes de landing, proyectos, contratos, tareas, metricas y modulo de marketing.

El problema principal no parece ser falta de codigo, sino falta de orden operativo y de reglas de negocio formalizadas dentro del sistema. Hay piezas construidas, pero todavia no estan unificadas en un flujo claro de conversion, seguimiento, estados, planes, pagos y recurrencia.

En terminos simples:

> La plataforma ya contiene varios modulos del ecosistema Impulsa, pero todavia no gobierna el negocio de punta a punta.

## 2. Que esta alineado

### 2.1. Arquitectura base

Existe una estructura MVC clara:

- `controllers/`
- `models/`
- `views/`
- `public/`
- `auth/`
- `API/`
- `partials/`
- `assets/`

Esto coincide con la necesidad del Mapa Maestro de ordenar la plataforma como centro operativo.

### 2.2. Roles principales

El sistema ya contempla roles alineados con el mapa:

- `impulsa_administrador`
- `impulsa_emprendedor`
- `impulsa_cliente`
- `impulsa_marketing`

Tambien hay modulos separados para emprendedores, clientes, administracion y marketing.

Falta terminar de incorporar o definir `impulsa_colaborador`, que en el Mapa Maestro aparece como rol proyectado.

### 2.3. Flujo emprendedor inicial

El flujo de Impulsa Emprende existe:

- registro;
- verificacion de correo;
- carga de mision;
- carga de vision;
- carga de buyer persona;
- solicitud de landing;
- panel de seguimiento.

Esto esta alineado con la idea de usar Impulsa Emprende como puerta de entrada.

### 2.4. Solicitudes y proyectos

Hay dos caminos de captacion:

- solicitud de landing publica: `public/new_page.php`;
- solicitud de proyecto robusto: `public/new_project.php`.

Tambien existe gestion de proyectos, fases, entregables, tareas y contratos en el area admin.

Esto se alinea con la division entre Emprendedor y Empresa, aunque todavia falta ordenar mejor los estados y la conversion entre solicitud, proyecto, publicacion y recurrencia.

### 2.5. Metricas y APIs de landings

Existen APIs para:

- registrar visitas;
- recibir formularios de contacto desde landings externas.

Esto es coherente con el objetivo de que el cliente pueda ver visitas y respuestas de formularios.

### 2.6. Modulo marketing

Hay modulo de marketing con:

- planes;
- precios;
- solicitudes;
- suscripciones;
- campanias;
- reportes;
- metricas.

Esto esta bastante alineado con la idea del Mapa Maestro de vender marketing como servicio tercerizado o coordinado desde Impulsa.

## 3. Desalineaciones principales

## 3.1. El mensaje comercial todavia dice "gratis"

Prioridad: alta.

El `index.php` todavia comunica:

- "de manera gratuita";
- "pagina web sin costo";
- "pagina web gratis";
- "acompanamiento gratuito".

Esto contradice la recomendacion central del Mapa Maestro: no vender la propuesta como "web gratis", sino como "landing inicial bonificada dentro del programa Impulsa Emprende".

Riesgo:

- atrae leads de baja calidad;
- baja el valor percibido;
- genera expectativa de soporte ilimitado;
- dificulta vender hosting, mantenimiento o analitica;
- refuerza el problema comercial actual de leads frios que no convierten.

Correccion recomendada:

- reemplazar el lenguaje de gratuidad por "bonificada";
- explicar condiciones desde el primer contacto;
- aclarar que dominio y hosting son necesarios para publicar;
- separar "acompanamiento inicial" de "servicios pagos".

Archivos a revisar:

- `index.php`
- `views/emprendedor/emprendedor_dashboard.php`
- cualquier texto publico en `public/new_page.php`

## 3.2. Falta un estado operativo real para la landing bonificada

Prioridad: alta.

La tabla `landing_page_request` guarda datos y un booleano `completado`, pero no refleja el proceso operativo completo.

El Mapa Maestro propone estados como:

- recibido;
- reunion agendada;
- falta informacion;
- esperando dominio;
- esperando hosting;
- listo para publicar;
- publicado;
- desestimado;
- reactivado.

Hoy el sistema parece tratar la landing como "completada" o "pendiente", lo cual es insuficiente para operar el negocio.

Riesgo:

- el admin no sabe que accion corresponde;
- no se puede automatizar seguimiento;
- no se puede medir abandono;
- no se puede aplicar la regla de 7 dias;
- no se puede distinguir solicitud completa de landing publicada.

Correccion recomendada:

- agregar un campo `status` a `landing_page_request`;
- crear una tabla de historial, por ejemplo `landing_page_request_status_history`;
- permitir al admin cambiar estado desde `admin_proceso_emprende.php`;
- mostrar al emprendedor el estado actual y el proximo paso;
- registrar fechas clave: solicitud, reunion, informacion pendiente, dominio confirmado, hosting confirmado, publicacion, desestimacion.

## 3.3. El admin ve solicitudes, pero no gestiona suficientemente el flujo

Prioridad: alta.

`admin_proceso_emprende.php` consolida bien el avance de mision, vision, buyer persona y landing. Sin embargo, funciona mas como tablero de observacion que como sistema de gestion.

Faltan acciones operativas claras:

- agendar reunion;
- marcar contacto realizado;
- pedir informacion faltante;
- marcar esperando dominio;
- marcar esperando hosting;
- marcar listo para desarrollar;
- marcar publicado;
- desestimar;
- reactivar;
- crear tarea interna desde el emprendedor;
- convertir solicitud en proyecto;
- registrar servicio contratado.

Correccion recomendada:

- convertir `admin_proceso_emprende.php` en un tablero operativo tipo pipeline;
- agregar filtros por etapa y urgencia;
- agregar columna "proxima accion";
- agregar "dias sin respuesta";
- agregar botones de cambio de estado;
- integrar tareas con `admin_tareas`.

## 3.4. Las solicitudes publicas externas no tienen estado real

Prioridad: media-alta.

En `AdminNewProjectModel::obtenerSolicitudesLandingExternal()` las solicitudes externas de landing se devuelven con:

```php
'nuevo' AS estado,
NULL AS updated_at
```

Eso significa que el estado no vive realmente en la base de datos para ese flujo.

Riesgo:

- no se puede marcar revisado, descartado o convertido;
- no se puede saber que lead ya fue trabajado;
- no se puede medir conversion;
- el embudo publico queda desconectado del seguimiento comercial.

Correccion recomendada:

- agregar `estado`, `updated_at`, `assigned_to_user_id`, `notes` a `landing_page_requests_external`;
- permitir acciones desde `admin_newproject.php`;
- unificar criterios con `project_scope_request`;
- registrar cuando una solicitud externa se convierte en proyecto o cliente.

## 3.5. Los estados de proyecto no coinciden del todo con el Mapa Maestro

Prioridad: media.

El codigo usa estados tecnicos como:

- `draft`;
- `planned`;
- `in_progress`;
- `paused`;
- `in_review`;
- `completed`;
- `cancelled`.

El Mapa Maestro usa estados mas operativos/comerciales:

- recibido;
- en revision;
- falta informacion;
- cotizado;
- aprobado;
- en desarrollo;
- en pruebas;
- en mantenimiento;
- pausado;
- cancelado.

No es obligatorio usar exactamente los mismos nombres, pero hoy falta representar etapas comerciales previas al desarrollo:

- cotizado;
- esperando aprobacion;
- esperando pago;
- esperando informacion;
- en mantenimiento/evolucion.

Correccion recomendada:

- separar estados de solicitud, proyecto y mantenimiento;
- no usar un unico `status` para cubrir todo;
- crear una definicion oficial de estados en un solo lugar;
- mapear estados internos a etiquetas visibles para admin y cliente.

## 3.6. Falta la capa de planes comerciales de Impulsa Emprende

Prioridad: alta.

El Mapa Maestro define:

- Emprende Base;
- Emprende Activo;
- Emprende Pro;
- Emprende Marketing;
- Empresa Base;
- Empresa Proyecto;
- Empresa Evolucion.

En el codigo hay planes de marketing, pero no se ve una capa general de planes/servicios recurrentes para hosting, correo, analitica, mantenimiento y soporte.

Riesgo:

- la plataforma no empuja la conversion a ingreso recurrente;
- el admin debe resolver ventas manualmente;
- no se puede medir ARPU ni MRR;
- el emprendedor no ve claramente que puede contratar despues.

Correccion recomendada:

- crear entidades para `service_plans`, `service_subscriptions` y `service_orders`;
- cargar planes Base, Activo y Pro;
- permitir que el admin asigne plan a un usuario;
- mostrar al emprendedor servicios disponibles;
- medir ingreso mensual estimado por cliente.

## 3.7. Falta pasarela o registro formal de pagos

Prioridad: alta.

El Mapa Maestro pone mucho foco en recurrencia mensual, pero el codigo aun no parece tener un modulo formal para pagos, facturacion, vencimientos o recordatorios.

Correccion recomendada:

- empezar simple: registrar contrataciones manuales antes de integrar pasarela;
- crear tabla de pagos/servicios contratados;
- registrar fecha de alta, monto mensual, estado y vencimiento;
- luego integrar Mercado Pago u otra pasarela.

## 3.8. La autorizacion por rol esta repetida

Prioridad: media-alta.

Muchos controladores hacen manualmente:

```php
if (($_SESSION['rol'] ?? '') !== 'impulsa_administrador') {
    header('Location: /index.php');
    exit;
}
```

Existe `middleware/authMiddleware.php`, pero no parece estar unificado en todos los controladores.

Riesgo:

- reglas inconsistentes;
- cambios de permisos dificiles;
- errores de acceso entre roles;
- mayor costo de mantenimiento.

Correccion recomendada:

- centralizar `requireLogin()`, `requireRole()` y `requireAnyRole()`;
- reemplazar validaciones repetidas;
- definir matriz de permisos por rol;
- agregar `impulsa_colaborador` cuando este definido.

## 3.9. Faltan protecciones CSRF en formularios internos

Prioridad: alta.

Hay muchos formularios POST en admin, emprendedor, cliente y marketing. No se observa una estrategia general de CSRF.

Riesgo:

- acciones internas podrian ejecutarse desde formularios externos si un usuario autenticado cae en una pagina maliciosa;
- afecta tareas, proyectos, contratos, estados, marketing y perfil.

Correccion recomendada:

- implementar helper de CSRF;
- generar token por sesion;
- incluir token en todos los formularios POST;
- validar token en todos los controladores que modifican datos;
- priorizar admin, contratos, proyectos, marketing y cambios de estado.

## 3.10. El frontend esta demasiado embebido en vistas grandes

Prioridad: media.

Hay vistas con mucho CSS y JavaScript embebido. Esto acelera al principio, pero ahora dificulta ordenar UX/UI, consistencia visual y mantenimiento.

Correccion recomendada:

- mover estilos repetidos a `assets/framework/framework.css` o archivos por modulo;
- extraer componentes reutilizables: sidebar, navbar, cards, badges, empty states, tablas, formularios;
- mantener las vistas mas enfocadas en estructura y datos;
- evitar duplicar estilos entre emprendedor, admin y marketing.

## 4. Prioridades recomendadas de correccion

## Prioridad 1 - Alinear comunicacion publica

Objetivo: dejar de atraer leads por "gratis" y empezar a comunicar valor.

Acciones:

- cambiar textos de `index.php`;
- cambiar "pagina web gratis" por "landing inicial bonificada";
- explicar dominio y hosting;
- aclarar limites;
- agregar CTA mas orientado a solicitud/calificacion que a regalo.

## Prioridad 2 - Crear estado real para solicitud de landing

Objetivo: que cada emprendedor tenga una etapa operativa clara.

Acciones:

- agregar `status` a `landing_page_request`;
- definir estados oficiales;
- mostrar estado al admin;
- mostrar estado al emprendedor;
- agregar historial de cambios;
- agregar acciones de admin.

## Prioridad 3 - Convertir `admin_proceso_emprende` en pipeline

Objetivo: que el admin sepa que hacer con cada lead.

Acciones:

- filtros por etapa;
- proxima accion;
- dias desde registro;
- dias desde ultima actualizacion;
- botones de estado;
- notas internas;
- crear tarea;
- desestimar/reactivar.

## Prioridad 4 - Formalizar planes y servicios recurrentes

Objetivo: empezar a medir y vender recurrencia.

Acciones:

- modelar planes Emprende Base, Activo y Pro;
- modelar servicios: hosting, correo, analitica, mantenimiento, soporte;
- registrar contrataciones manuales;
- calcular MRR estimado;
- mostrar servicios en dashboard emprendedor/cliente.

## Prioridad 5 - Seguridad basica transversal

Objetivo: reducir riesgo antes de escalar usuarios.

Acciones:

- unificar middleware de autenticacion/autorizacion;
- agregar CSRF;
- revisar sesiones;
- revisar permisos por rol;
- auditar endpoints publicos;
- documentar matriz de acceso.

## Prioridad 6 - Unificar estados Empresa

Objetivo: que el flujo Empresa tenga trazabilidad comercial y operativa.

Acciones:

- agregar estados previos: recibido, en revision, cotizado, esperando aprobacion, esperando pago;
- diferenciar solicitud de proyecto;
- agregar mantenimiento/evolucion mensual;
- vincular contrato, pago y fase de desarrollo.

## Prioridad 7 - Ordenar UX/UI

Objetivo: que la plataforma sea clara para usuarios no tecnicos.

Acciones:

- revisar dashboard emprendedor;
- simplificar solicitud de landing;
- mostrar progreso y proximo paso;
- revisar mobile;
- extraer componentes comunes;
- usar lenguaje menos tecnico.

## 5. Roadmap tecnico sugerido

### Semana 1

- Corregir comunicacion publica.
- Definir estados oficiales de landing.
- Crear migracion para `landing_page_request.status`.
- Mostrar estado en admin y emprendedor.

### Semana 2

- Agregar acciones de admin para cambiar estado.
- Agregar notas internas.
- Agregar historial de estados.
- Implementar regla de desestimacion manual por 7 dias.

### Semana 3

- Crear estructura inicial de planes/servicios recurrentes.
- Registrar contrataciones manuales.
- Mostrar MRR estimado en admin.
- Mostrar servicios disponibles al emprendedor.

### Semana 4

- Unificar middleware de permisos.
- Agregar CSRF a formularios criticos.
- Revisar endpoints publicos.
- Documentar matriz de roles.

## 6. Conclusion

El codigo no esta mal orientado. La direccion general coincide con el Mapa Maestro.

La brecha principal es que la plataforma todavia funciona como un conjunto de modulos, no como un sistema operativo comercial completo.

Para alinearla mejor, conviene priorizar:

1. comunicacion correcta;
2. estados reales;
3. seguimiento operativo;
4. planes recurrentes;
5. seguridad base;
6. UX simple;
7. metricas de conversion y MRR.

La mejora mas importante ahora no es agregar muchas funcionalidades nuevas, sino hacer que el flujo actual sea gobernable, medible y vendible.

