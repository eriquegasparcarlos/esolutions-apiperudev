<?php

/*
 * Config del paquete esolutions/apiperudev. Se fusiona (mergeConfigFrom) bajo la clave
 * "esolutions", así que NO pisa otras secciones (ej. "ws" de esolutions/ws).
 *
 * NOTA: la URL de la API NO es texto libre. Se elige una INSTANCIA de una lista blanca
 * ('apiperu' | 'apiconsulta'); el paquete resuelve la URL internamente
 * (\Esolutions\ApiPeruDev\Client). Una instancia desconocida lanza InvalidArgumentException.
 *
 *   php artisan vendor:publish --tag=esolutions-apiperudev-config
 */

return array(
    'apiperudev' => array(
        // Bearer token. En apps que lo guardan en BD, dejar vacío acá e inyectarlo en
        // runtime: new \Esolutions\ApiPeruDev\Client($tokenDeBd).
        'token' => env('APIPERUDEV_TOKEN', ''),

        // Instancia de API: 'apiperu' (default) o 'apiconsulta'. NO es una URL — es una clave
        // de la lista blanca del paquete. Un valor inválido lanza InvalidArgumentException.
        'instancia' => env('APIPERUDEV_INSTANCIA', \Esolutions\ApiPeruDev\Client::APIPERU),
    ),
);
