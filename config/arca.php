<?php

return [
    'cuit'         => env('ARCA_CUIT'),
    'cert'         => env('ARCA_CERT',  'app/arca/cert.crt'),
    'key'          => env('ARCA_KEY',   'app/arca/private.key'),
    'production'   => env('ARCA_PRODUCTION', false),
    'punto_venta'  => (int) env('ARCA_PUNTO_VENTA', 1),

    // Tipo de comprobante por defecto:
    // 1=Factura A, 6=Factura B, 11=Factura C (monotributistas)
    'tipo_cbte'    => (int) env('ARCA_TIPO_CBTE', 11),

    // Condición IVA del emisor (esta empresa):
    // 'monotributo'           → solo emite Factura C / NC-C
    // 'responsable_inscripto' → emite A para RI, B para el resto
    'condicion_emisor' => env('ARCA_CONDICION_EMISOR', 'monotributo'),

    'url' => [
        'wsaa_prod' => 'https://wsaa.afip.gov.ar/ws/services/LoginCms',
        'wsfe_prod' => 'https://servicios1.afip.gov.ar/wsfev1/service.asmx',
        'wsaa_homo' => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms',
        'wsfe_homo' => 'http://wswhomo.afip.gov.ar/wsfev1/service.asmx',
    ],
];
