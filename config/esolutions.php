<?php

/*
 * Config del paquete esolutions/apiperudev. Se fusiona (mergeConfigFrom) bajo la clave
 * "esolutions", así que NO pisa otras secciones (ej. "ws" de esolutions/ws).
 *
 * NOTA: la URL de la API NO es configurable — está fija dentro del paquete
 * (\Esolutions\ApiPeruDev\Client::BASE_URL = https://api.apiperu.dev). Acá solo el token.
 *
 *   php artisan vendor:publish --tag=esolutions-apiperudev-config
 */

return array(
    'apiperudev' => array(
        // Bearer token de apiperu.dev. En apps que lo guardan en BD, dejar vacío acá e
        // inyectarlo en runtime: new \Esolutions\ApiPeruDev\Client($tokenDeBd).
        'token' => env('APIPERUDEV_TOKEN', ''),
    ),
);
