# Impulsa Emprende

Plataforma web interna (PHP + MySQL) para captar emprendedores, guiarlos en su onboarding y gestionar su avance comercial/operativo desde paneles por rol.

## Alcance funcional

- Registro/login con verificacion de correo.
- Sesiones y dashboards por rol (`admin`, `emprendedor`, `clientes`, `marketing`).
- Flujo emprendedor: mision, vision, buyer persona y solicitud de landing.
- API de formularios para landings externas.
- Auditoria de eventos y soporte de correo transaccional.

## Estructura principal

- `auth/`: autenticacion y verificacion.
- `controllers/`, `models/`, `views/`: arquitectura MVC.
- `API/`: endpoints para integraciones externas.
- `mail/`: envio de correos y plantillas.
- `assets/`, `partials/`, `public/`: frontend y componentes reutilizables.
- `docs/`: documentos estrategicos y revisiones tecnicas.

## Estado general

El nucleo funcional esta operativo. La consolidacion pendiente se concentra en:

- unificacion de autorizacion por rol;
- cierre de reglas comerciales pendientes en docs;
- pruebas automatizadas del flujo critico.
