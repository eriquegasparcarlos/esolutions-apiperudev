# esolutions/apiperudev

Cliente HTTP **universal** para la API de [apiperu.dev](https://apiperu.dev)
(docs: <https://docs.apiperu.dev>). Consultas de RUC/DNI, SUNAT, validación de comprobantes
(CPE), tipo de cambio, placa, licencia de conducir, AFP, ubigeos y más.

- **Universal**: PHP **7.2+** y Laravel **5.7 → 13**, o **standalone** (sin Laravel).
- Usa **Guzzle** directamente (no el HTTP client de Illuminate, que exige Laravel 7+).
- **URL fija** dentro del paquete (`Client::BASE_URL = https://api.apiperu.dev`): no configurable
  ni inyectable — el paquete solo funciona contra la infraestructura de apiperu.dev.
- Lo único configurable es el **token** (Bearer), por inyección o `config('esolutions.apiperudev.token')`.
- Todos los métodos devuelven `array` (respuesta de la API) o `['success' => false, 'message' => ...]`.

Auth: header `Authorization: Bearer <token>`.

## Instalación

```bash
composer require esolutions/apiperudev
```

En Laravel el `ServiceProvider` se autodescubre. Token por env:

```dotenv
APIPERUDEV_TOKEN=tu_token
```

(Opcional) publicar el archivo de config:

```bash
php artisan vendor:publish --tag=esolutions-apiperudev-config
```

## Uso

### 1) Laravel (config/env)

```php
use Esolutions\ApiPeruDev\Service;

$ruc = Service::ruc('20123456789');
$dni = Service::dni('12345678');
```

### 2) Token guardado en BD (inyección en runtime)

```php
use Esolutions\ApiPeruDev\Client;

$api = new Client($config->token_apiruc);
$ruc = $api->ruc('20123456789');
$tc  = $api->tipoDeCambio('2026-08-13');
```

### 3) Standalone (sin Laravel)

```php
require 'vendor/autoload.php';

$api = new \Esolutions\ApiPeruDev\Client('tu_token');
$api->dni('12345678');
```

## Endpoints

| Método del cliente | HTTP | Endpoint |
|---|---|---|
| `ruc($ruc)` | POST | `/ruc` |
| `dni($dni)` | POST | `/dni` |
| `dniRuc($dni)` | POST | `/dni-ruc` |
| `rucSunat($ruc)` | POST | `/ruc-sunat` |
| `rucDomicilioFiscal($ruc)` | POST | `/ruc-domicilio-fiscal` |
| `rucEstablecimientosAnexos($ruc)` | POST | `/ruc-establecimientos-anexos` |
| `rucRepresentantes($ruc)` | POST | `/ruc-representantes` |
| `rucTrabajadores($ruc)` | POST | `/ruc-trabajadores` |
| `rucDeudaCoactiva($ruc)` | POST | `/ruc-deuda-coactiva` |
| `rucContacto($ruc)` | POST | `/ruc-contacto` |
| `rucSsco($ruc)` | POST | `/ruc-ssco` |
| `placa($placa)` | POST | `/placa` |
| `licenciaConducir($numLicencia)` | POST | `/licencia-conducir` |
| `comisionesAfp($periodo)` | POST | `/comisiones-afp` |
| `tipoDeCambio($fecha)` | POST | `/tipo-de-cambio` |
| `puertos()` | POST | `/puertos` |
| `aeropuertos()` | POST | `/aeropuertos` |
| `ubigeos()` | POST | `/ubigeos` |
| `cpe($rucEmisor, $codTipoDoc, $serie, $numero, $fechaEmision, $total)` | POST | `/cpe` |
| `validacionMultipleCpe($comprobantes, $rucEmisor)` | POST | `/validacion-multiple-cpe` |

También: `Client::buildCpeString(...)` (helper que arma `ruc|tipo|serie|numero|fecha|total`).

## Compatibilidad (facade `Service`)

Se mantienen las firmas históricas para no romper consumidores existentes:

```php
Service::searchWithInput('ruc', '20123456789');   // o 'dni'
Service::searchExchangeRateSaleWithInput('2026-08-13');
Service::searchFiscalAddress($ruc);
Service::searchEstablishments($ruc);
Service::searchPorts();
Service::searchAirports();
Service::searchCpeWithInput($ruc, $tipo, $serie, $numero, $fecha, $total);
Service::searchCpeMultiple($comprobantes, $rucEmisor);
Service::buildCpeString(...);
(new Service)->searchRuc($request);   // $request->input('number')
(new Service)->searchDni($request);
```

## Matriz de versiones

| | Versión |
|---|---|
| PHP | `^7.2 \|\| ^8.0` |
| Laravel (opcional) | `5.7 → 13` |
| Guzzle | `^6.0 \|\| ^7.0` |

> El versionado lo deriva Packagist del **tag de git**; el `composer.json` no lleva campo `version`.
