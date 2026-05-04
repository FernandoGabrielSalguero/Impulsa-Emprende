# Integracion de formularios con la API central de Impulsa Emprende

Este instructivo explica como conectar una landing page HTML/CSS/JavaScript con la API centralizada de Impulsa Emprende para guardar consultas de contacto.

La landing page no se conecta a MySQL. La landing page no debe copiar ni contener la API PHP. La API vive dentro del proyecto Impulsa Emprende y la landing solo le envia datos por HTTP usando `fetch()`.

## Arquitectura

```text
Landing page externa
  -> JavaScript fetch()
  -> URL publica de la API en Impulsa Emprende
  -> PHP con PDO
  -> MySQL
```

Responsabilidades:

- La landing page muestra el formulario y envia un request HTTP.
- La API central valida dominio, API key, JSON y campos.
- La API central inserta los datos en `forms_clients_contact`.
- MySQL nunca se expone a la landing page.

Importante: la API no se copia en cada landing. Lo que se copia en cada landing es el HTML/JS necesario para enviar datos al endpoint remoto.

## Flujo de comunicacion

1. El usuario completa el formulario en la landing.
2. JavaScript arma un payload JSON con los campos soportados.
3. JavaScript hace `fetch()` a la URL publica de la API.
4. El navegador envia automaticamente el header `Origin`.
5. La API verifica que ese `Origin` este permitido en `API/contact_form_landing_page/allowed-domains.txt`.
6. La API verifica el header `X-API-KEY`.
7. La API inserta el registro con `state = "recibido"`.
8. La API responde JSON con exito o error.

## URL del endpoint

Ejemplo de URL publica:

```text
https://mi-dominio-de-impulsa-emprende.com/API/contact_form_landing_page/index.php
```

Reemplazar `https://mi-dominio-de-impulsa-emprende.com` por el dominio real donde esta publicado Impulsa Emprende.

Ejemplo de constante en JavaScript:

```js
const CONTACT_API_ENDPOINT = 'https://mi-dominio-de-impulsa-emprende.com/API/contact_form_landing_page/index.php';
```

## Operaciones permitidas

La API solo permite insertar consultas nuevas.

No existen endpoints ni funcionalidades para:

- Leer registros.
- Listar registros.
- Editar registros.
- Borrar registros.

La landing page solo debe enviar datos del formulario. No debe intentar consultar la base de datos ni modificar registros existentes.

## Headers requeridos

El request debe enviar estos headers:

```http
Content-Type: application/json
X-API-KEY: TU_API_KEY
```

Ejemplo:

```js
headers: {
  'Content-Type': 'application/json',
  'X-API-KEY': CONTACT_API_KEY
}
```

La API key se valida contra `API_KEY` en el `.env` del proyecto Impulsa Emprende. La landing no debe enviar credenciales de base de datos.

## Origin y dominios permitidos

Cuando una landing llama a la API desde el navegador, el navegador envia el header `Origin` con el dominio de la landing.

La API compara ese `Origin` exactamente contra el archivo de dominios permitidos del proyecto Impulsa Emprende:

```text
/API/contact_form_landing_page/allowed-domains.txt
```

Formato del archivo:

```text
# Dominios permitidos para llamar a esta API desde el navegador.
# Escribir un dominio por linea, siempre con https:// y sin barra final.

https://norumestudio.com.ar
https://www.norumestudio.com.ar
```

Reglas:

- Usar un dominio por linea.
- Usar siempre `https://` en produccion.
- No agregar barra final.
- No agregar paths.
- Correcto: `https://norumestudio.com.ar`.
- Incorrecto: `https://norumestudio.com.ar/`.
- Incorrecto: `https://norumestudio.com.ar/contacto`.

Si el dominio de la landing no esta permitido, la API responde `403`.

## Payload JSON esperado

La landing puede enviar solamente estos campos:

```json
{
  "page": "landing-norum-home",
  "contact_nombre": "Juan Perez",
  "contact_whatsapp": "+5491123456789",
  "contact_email": "juan@email.com",
  "contact_description": "Quiero informacion sobre sus servicios.",
  "contact_consultation": "Desarrollo web"
}
```

Campos obligatorios:

- `page`: string, maximo 150 caracteres.
- `contact_nombre`: string, maximo 150 caracteres.

Campos opcionales:

- `contact_whatsapp`: string, maximo 50 caracteres.
- `contact_email`: email valido, maximo 150 caracteres.
- `contact_description`: string.
- `contact_consultation`: string, maximo 255 caracteres.

Campos que no deben enviarse:

- `id`
- `state`
- `created_at`
- `updated_at`

Aunque se envien campos extra, la API los ignora. El campo `state` se guarda siempre como `recibido` desde el servidor.

## Respuestas JSON

Respuesta exitosa:

```json
{
  "success": true,
  "message": "Consulta enviada correctamente"
}
```

Respuesta de error:

```json
{
  "success": false,
  "message": "Mensaje del error"
}
```

Codigos HTTP esperados:

- `201`: consulta creada correctamente.
- `400`: JSON invalido o `Content-Type` incorrecto.
- `401`: API key ausente o incorrecta.
- `403`: dominio no permitido por CORS.
- `405`: metodo incorrecto. Usar `POST`.
- `422`: error de validacion de campos.
- `500`: error interno del servidor.

## Ejemplo completo de HTML

Este ejemplo se puede copiar en una landing. Si la landing ya tiene un formulario, adaptar los `id` y `name` para que coincidan con el JavaScript.

```html
<form id="contactForm" novalidate>
  <div>
    <label for="contact_nombre">Nombre *</label>
    <input
      id="contact_nombre"
      name="contact_nombre"
      type="text"
      maxlength="150"
      required
    >
  </div>

  <div>
    <label for="contact_whatsapp">WhatsApp</label>
    <input
      id="contact_whatsapp"
      name="contact_whatsapp"
      type="tel"
      maxlength="50"
    >
  </div>

  <div>
    <label for="contact_email">Email</label>
    <input
      id="contact_email"
      name="contact_email"
      type="email"
      maxlength="150"
    >
  </div>

  <div>
    <label for="contact_description">Descripcion</label>
    <textarea
      id="contact_description"
      name="contact_description"
      rows="4"
    ></textarea>
  </div>

  <div>
    <label for="contact_consultation">Consulta</label>
    <input
      id="contact_consultation"
      name="contact_consultation"
      type="text"
      maxlength="255"
    >
  </div>

  <button type="submit">Enviar consulta</button>

  <p id="contactFormMessage" aria-live="polite"></p>
</form>

<script src="js/contact-form-api.js"></script>
```

## Ejemplo completo de JavaScript con fetch()

Crear el archivo en la landing:

```text
js/contact-form-api.js
```

Contenido:

```js
const CONTACT_API_ENDPOINT = 'https://mi-dominio-de-impulsa-emprende.com/API/contact_form_landing_page/index.php';
const CONTACT_API_KEY = 'TU_API_KEY';
const CONTACT_PAGE_NAME = 'landing-nombre-unico';

const contactForm = document.querySelector('#contactForm');
const contactFormMessage = document.querySelector('#contactFormMessage');

function showContactMessage(message, isError = false) {
  if (!contactFormMessage) {
    return;
  }

  contactFormMessage.textContent = message;
  contactFormMessage.dataset.state = isError ? 'error' : 'success';
}

if (contactForm) {
  contactForm.addEventListener('submit', async function (event) {
    event.preventDefault();

    const submitButton = contactForm.querySelector('button[type="submit"]');
    const formData = new FormData(contactForm);

    const payload = {
      page: CONTACT_PAGE_NAME,
      contact_nombre: String(formData.get('contact_nombre') || '').trim(),
      contact_whatsapp: String(formData.get('contact_whatsapp') || '').trim(),
      contact_email: String(formData.get('contact_email') || '').trim(),
      contact_description: String(formData.get('contact_description') || '').trim(),
      contact_consultation: String(formData.get('contact_consultation') || '').trim()
    };

    if (!payload.contact_nombre) {
      showContactMessage('Ingresa tu nombre para enviar la consulta.', true);
      return;
    }

    if (submitButton) {
      submitButton.disabled = true;
    }

    showContactMessage('Enviando consulta...');

    try {
      const response = await fetch(CONTACT_API_ENDPOINT, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-API-KEY': CONTACT_API_KEY
        },
        body: JSON.stringify(payload)
      });

      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.message || 'No se pudo enviar la consulta.');
      }

      contactForm.reset();
      showContactMessage(result.message || 'Consulta enviada correctamente');
    } catch (error) {
      console.error('Error enviando formulario:', error);
      showContactMessage(error.message || 'No se pudo enviar la consulta.', true);
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
      }
    }
  });
}
```

Valores que hay que reemplazar:

```js
const CONTACT_API_ENDPOINT = 'https://mi-dominio-de-impulsa-emprende.com/API/contact_form_landing_page/index.php';
const CONTACT_API_KEY = 'TU_API_KEY';
const CONTACT_PAGE_NAME = 'landing-nombre-unico';
```

Recomendacion para `CONTACT_PAGE_NAME`: usar un identificador claro de la landing, por ejemplo `norum-home`, `impulsa-ads-abril` o `cliente-servicio-contacto`.

## Como adaptar IDs y names del formulario

El JavaScript del ejemplo busca:

- Formulario: `#contactForm`.
- Mensajes: `#contactFormMessage`.
- Campos por `name`: `contact_nombre`, `contact_whatsapp`, `contact_email`, `contact_description`, `contact_consultation`.

Si tu landing ya tiene otros IDs o names, tenes dos opciones:

- Cambiar el HTML para usar los mismos `id` y `name` del ejemplo.
- Cambiar los selectores y nombres usados en el JavaScript.

Ejemplo si tu formulario tiene otro ID:

```js
const contactForm = document.querySelector('#formularioPrincipal');
```

Ejemplo si tu campo de nombre se llama `name`:

```js
contact_nombre: String(formData.get('name') || '').trim()
```

Lo importante es que el payload final enviado a la API use los nombres soportados por la API.

## Prueba rapida desde consola del navegador

Ejecutar desde una landing cuyo dominio ya este agregado en `API/contact_form_landing_page/allowed-domains.txt`:

```js
fetch('https://mi-dominio-de-impulsa-emprende.com/API/contact_form_landing_page/index.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-API-KEY': 'TU_API_KEY'
  },
  body: JSON.stringify({
    page: 'prueba-consola',
    contact_nombre: 'Prueba API',
    contact_whatsapp: '+5491123456789',
    contact_email: 'test@test.com',
    contact_description: 'Prueba desde consola del navegador',
    contact_consultation: 'Consulta de prueba'
  })
})
  .then(async response => {
    const result = await response.json();
    console.log(response.status, result);
  })
  .catch(console.error);
```

Resultado correcto esperado:

```text
201 { success: true, message: 'Consulta enviada correctamente' }
```

## Troubleshooting

### Error 401: API key incorrecta o ausente

Causa probable: falta el header `X-API-KEY` o el valor no coincide con `API_KEY` en el `.env` de Impulsa Emprende.

Verificar:

```js
'X-API-KEY': CONTACT_API_KEY
```

Tambien verificar que la API key exista en el `.env` central.

### Error 403: dominio no permitido

Causa probable: el dominio de la landing no esta en `/API/contact_form_landing_page/allowed-domains.txt` o no coincide exactamente con el `Origin` enviado por el navegador.

Verificar diferencias como:

- `https://dominio.com` vs `https://www.dominio.com`.
- Barra final `/` agregada por error.
- Uso de `http://` en vez de `https://`.
- Puerto local, por ejemplo `http://localhost:5500`, si se esta probando en desarrollo.

### Error 405: metodo incorrecto

Causa probable: se llamo al endpoint con `GET`, `PUT`, `PATCH` o `DELETE`.

La API acepta solamente:

- `POST`
- `OPTIONS` para preflight CORS

### Error 422: validacion

Causa probable: faltan campos obligatorios o algun campo supera el maximo permitido.

Minimo valido:

```json
{
  "page": "landing-prueba",
  "contact_nombre": "Juan Perez"
}
```

### Error 500: error interno

Causa probable: problema de configuracion o base de datos en el proyecto Impulsa Emprende.

Revisar en el servidor central:

- `.env` con credenciales correctas.
- Existencia de la tabla `forms_clients_contact`.
- Permisos del usuario MySQL para ejecutar `INSERT`.
- Logs de PHP generados con `error_log`.

## Checklist de integracion

- [ ] Confirmar la URL publica real de `API/contact_form_landing_page/index.php`.
- [ ] Agregar el dominio exacto de la landing en `/API/contact_form_landing_page/allowed-domains.txt` del proyecto Impulsa Emprende.
- [ ] Confirmar que la landing no contiene credenciales de MySQL.
- [ ] Confirmar que la landing no copia `API/contact_form_landing_page/index.php`.
- [ ] Definir un `CONTACT_PAGE_NAME` unico para la landing.
- [ ] Configurar `CONTACT_API_ENDPOINT` con la URL remota de la API.
- [ ] Configurar `CONTACT_API_KEY` con la API key correspondiente.
- [ ] Agregar `id="contactForm"` al formulario o adaptar el selector JS.
- [ ] Agregar `id="contactFormMessage"` o adaptar el selector JS.
- [ ] Verificar los `name` de los campos del formulario.
- [ ] Enviar una prueba desde el navegador.
- [ ] Confirmar que la respuesta sea `{ "success": true, "message": "Consulta enviada correctamente" }`.
- [ ] Confirmar que se creo un registro en `forms_clients_contact` con `state = "recibido"`.

## Nota de seguridad

En una integracion directa desde JavaScript, la `X-API-KEY` queda visible en el navegador. Esta API reduce el riesgo validando `Origin`, CORS, metodo, JSON y prepared statements, pero esa key no debe considerarse un secreto absoluto si se publica en frontend.

Para landings criticas o con alto volumen, evaluar una capa backend propia de la landing que mantenga la API key fuera del navegador. Esa mejora no cambia la arquitectura central: la landing sigue sin conectarse a MySQL y la insercion sigue ocurriendo en la API de Impulsa Emprende.
