# plugins104-reservas-simple

**Solución sencilla para el cliente y el visitante:**  
Muestra un calendario semanal navegable, con los días y franjas horarias disponibles claramente marcados, y permite realizar reservas introduciendo el nombre y el número de teléfono del visitante.

---

## ¿Qué hace este plugin?

- Muestra un **calendario semanal** en frontend, con franjas horarias por día (ej. 10–12h, 12–14h)
- Permite a los usuarios **reservar una franja** sin necesidad de registrarse ni pagar
- Las reservas se almacenan en el panel de administración
- El administrador puede:
  - Ver las reservas pendientes
  - Contactar directamente vía WhatsApp con un clic
  - **Anular días o semanas completas** desde el mismo calendario (modo admin)
  - Marcar reservas como atendidas o eliminarlas

---

##  ¿Para quién está pensado?

Ideal para:
- Granjas educativas
- Pequeños negocios rurales
- Talleres presenciales
- Visitas guiadas
- Cualquier negocio que necesite **reservas por franja horaria**, sin complicarse

---

##  Filosofía

Este plugin nace de una necesidad real: permitir reservas de forma directa y humana, sin intermediarios ni sistemas complejos.  
No usa WooCommerce, ni pasarelas de pago, ni configuraciones infinitas.

-  100% libre y sin suscripciones
-  Ligero para tu WordPress
-  Código abierto y entendible
-  Se puede adaptar fácilmente a otras necesidades

---

##  Instalación

1. Sube la carpeta del plugin a `/wp-content/plugins/plugins104-reservas-simple/`
2. Actívalo desde el panel de administración
3. Inserta el shortcode `[reservas_simple]` en la página donde quieras mostrar el calendario
4. ¡Listo!

>  Opcional: puedes personalizar las franjas horarias, los textos y los estilos desde el código o pedirnos una adaptación.

---

##  Shortcodes disponibles

```shortcode
[reservas_simple]
```

- Muestra el calendario semanal y el formulario de reserva

---

##  Estructura del código y puntos clave

¿Quieres personalizarlo tú mismo? Aquí te dejamos los archivos principales y ejemplos de modificación:

---

### `includes/shortcode.php`
Responsable de **mostrar el calendario** y gestionar el formulario de reservas en el frontend.

####  Modificar franjas horarias:
Busca este fragmento:
```php
$slots = ['10:00 - 12:00', '12:00 - 14:00'];
```
Puedes cambiarlo por cualquier otra combinación, por ejemplo:
```php
$slots = ['09:00 - 11:00', '11:00 - 13:00', '16:00 - 18:00'];
```

####  Modificar la duración del calendario (cuántas semanas se pueden avanzar):
```php
$weeks_to_show = 6;
```

---

### `includes/admin-panel.php`
Controla la **vista de administración** en el backend.

####  Botón de WhatsApp por reserva:
Busca esta línea:
```php
<a href='https://wa.me/$telefono?text=Hola...' ...
```
Aquí puedes personalizar el mensaje que se le envía al cliente desde WhatsApp.

####  Anular días desde el calendario (modo admin):
Busca las funciones que procesan `POST` con `bloquear_fecha` o `desbloquear_fecha`. Aquí puedes adaptar los textos o lógica de bloqueo.

---

### `includes/notifications.php`
Contiene la lógica para **enviar correos al admin** cuando entra una nueva reserva.

####  Cambiar asunto o contenido del email:
```php
$subject = "Nueva reserva en la web";
$message = "Hay una nueva reserva para el día $fecha de $slot.";
```
Puedes añadir más datos como `$telefono` o personalizar el mensaje completo.

---

### `js` y `css`
El JavaScript y el CSS están bastante contenidos:

- `js/calendar.js`: controla la navegación por semanas
- `css/style.css`: puedes personalizar colores y apariencia del calendario

---

##  Capturas

> *(Incluye capturas en la sección de imágenes del repo si lo deseas)*

- Calendario en frontend
- Formulario de reserva
- Vista de reservas en admin
- Botón WhatsApp por reserva
- Calendario en modo admin (bloquear días)

---

##  Licencia

Este plugin se publica bajo licencia MIT. Puedes usarlo, adaptarlo y redistribuirlo libremente.  
Solo te pedimos que conserves la referencia al proyecto original si haces modificaciones públicas.

---

##  ¿Quieres adaptarlo?

Si necesitas que este sistema se adapte mejor a tu negocio (idioma, número de franjas, colores, campos…),  
**te lo personalizamos desde 59 €, sin cuotas ni suscripciones**.

 [plugins@104cubes.com](https://104cubes.com/contacto)

---

##  Más plugins útiles

Descubre otros plugins ligeros en  
👉 [https://104cubes.com/plugins-104]([https://104cubes.com/category/plugins-104/](https://104cubes.com/plugins-104/))
