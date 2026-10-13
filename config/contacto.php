<?php

/*
|--------------------------------------------------------------------------
| Datos de contacto del landing (plote.ar)
|--------------------------------------------------------------------------
| Única fuente del número de WhatsApp y de las casillas que reciben las
| consultas del formulario. Antes el número estaba repetido en tres vistas
| con un placeholder (341 000 0000) que no existía.
|
| Al cambiar algo acá, en producción hay que correr `php artisan config:cache`.
*/

return [

    // Número para los links wa.me: solo dígitos, con país y el 9 de móvil.
    'whatsapp' => env('CONTACTO_WHATSAPP', '5491121728813'),

    // Cómo se muestra en pantalla.
    'whatsapp_label' => env('CONTACTO_WHATSAPP_LABEL', '+54 9 11 2172-8813'),

    // Casillas que reciben cada consulta del formulario. Van las dos en el
    // mismo envío (To), así ambas ven la consulta completa.
    'emails' => [
        'info@sistemasintegrales.net',
        'impresion@sistemasintegrales.com.ar',
    ],

    // Mail que se muestra en el landing como contacto directo.
    'email_publico' => env('CONTACTO_EMAIL_PUBLICO', 'info@sistemasintegrales.net'),

];
