# plugins104-popup-oferta

**Muestra un popup de imagen promocional solo cuando el visitante ha aceptado cookies y hace scroll.**  
Ideal para destacar ofertas, campañas estacionales o redirigir a una página clave de tu web.

---

## ¿Qué hace este plugin?

- Muestra una imagen promocional como **popup animado** al hacer scroll.
- Se muestra **solo una vez por sesión**, respetando al usuario.
- Compatible con el plugin **Complianz**: solo aparece si se han aceptado las cookies.
- El administrador puede:
  - Seleccionar la imagen desde la biblioteca de medios.
  - Activar o desactivar el popup desde el panel.
  - Introducir una URL de destino para redirigir al hacer clic en la imagen.
- El visitante puede:
  - Cerrar el popup con un botón `×` o haciendo clic fuera.
  - Ser redirigido a la página configurada si hace clic en la imagen.

---

## ¿Para quién está pensado?

Ideal para:

- Ecommerces con campañas activas (ej. rebajas, Black Friday…)
- Webs informativas que quieren destacar un post o contenido concreto
- Negocios locales que quieren mostrar una promoción simple sin banners intrusivos
- Cualquier sitio WordPress que necesite un **Call To Action eficaz, ligero y sin complicaciones**

---

## Filosofía

Este plugin está diseñado para ser **ligero, útil y respetuoso** con el visitante:

- No rastrea nada
- Respeta la privacidad (no se ejecuta sin consentimiento)
- No requiere configuraciones complejas
- Es **autocontenible y sin dependencias externas**

Forma parte de la colección [Plugins104](https://104cubes.com/plugins-104), pensada para dar soluciones específicas, sin hinchar tu WordPress.

---

## Instalación

1. Sube la carpeta del plugin a `/wp-content/plugins/plugins104-popup-oferta/`  
   o instala el archivo `.zip` desde el panel de WordPress.
2. Actívalo.
3. Accede al menú **"Popup Oferta"** en el panel de administración.
4. Sube una imagen y activa el popup.
5. (Opcional) Introduce una URL para redirigir cuando se haga clic en la imagen.
6. ¡Listo!

---

## Personalización

Este plugin funciona bien tal como viene, pero puedes personalizar fácilmente:

### Enlace clicable
Desde el panel puedes introducir cualquier URL (landing, post, producto…) para redirigir al hacer clic en la imagen.

### Altura del popup
Por defecto se limita a un `90vh` (90% del alto del navegador). Puedes cambiarlo en el CSS.

### Animación de entrada
Usa una animación tipo *bounce*. Puedes cambiarla en `assets/style.css`.

---

## Código clave

### Mostrar la imagen:

```php
<img id="popup-oferta-img" src="" alt="Oferta" />
```

### Con enlace clicable:

```php
<a id="popup-oferta-link" href="#" target="_blank" rel="noopener">
  <img id="popup-oferta-img" src="" alt="Oferta" />
</a>
```

### Detectar cookies aceptadas (Complianz):

```js
if (typeof cmplz_has_consent === 'function' && cmplz_has_consent()) {
    // Mostrar el popup
}
```

---

## Capturas

> *(Puedes añadir capturas si lo subes al repo o directorio de WordPress)*

- Panel de administración del plugin
- Popup mostrado en frontend
- Animación de entrada
- Comportamiento tras aceptar cookies

---

## Licencia

Este plugin se publica bajo licencia MIT.  
Puedes usarlo, adaptarlo y redistribuirlo libremente.  
Solo te pedimos que conserves la referencia al proyecto original si haces modificaciones públicas.

---

## ¿Quieres adaptarlo?

¿Necesitas que el popup incluya un botón con texto, un formulario o se muestre en momentos distintos?

**Lo personalizamos desde 49 €, sin cuotas ni suscripciones.**  
👉 [Contacta con nosotros](https://104cubes.com/contacto)

---

## Más plugins útiles

Descubre otros plugins ligeros en  
👉 [https://104cubes.com/plugins-104](https://104cubes.com/plugins-104)
