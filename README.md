# esolutions/apiperudev

Cliente HTTP **universal** para la API de [apiperu.dev](https://apiperu.dev)
(docs: <https://docs.apiperu.dev>). Consultas de RUC/DNI, SUNAT, validación de comprobantes
(CPE), tipo de cambio, placa, licencia de conducir, AFP, ubigeos y más.

- **Universal**: PHP **7.2+** y Laravel **5.7 → 13**, o **standalone** (sin Laravel).
- Usa **Guzzle** directamente (no el HTTP client de Illuminate, que exige Laravel 7+).
- **Instancia por lista blanca**, no URL libre: se elige `Client::APIPERU` (default) o
  `Client::APICONSULTA` y el paquete resuelve la URL internamente. Una instancia desconocida
  (o una URL) lanza `InvalidArgumentException` — así el token nunca viaja a un host no controlado.
- Configurable: el **token** (Bearer) y la **instancia**, por inyección o `config('esolutions.apiperudev.*')`.
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

### Elegir instancia (apiperu.dev / apiconsulta.dev)

Por defecto apunta a **apiperu.dev**. Para **apiconsulta.dev**:

```php
use Esolutions\ApiPeruDev\Client;

$api = Client::apiConsulta($token);   // apiconsulta.dev
$api = Client::apiPeru($token);       // apiperu.dev (= default)

// equivalente por constructor:
$api = new Client($token, [], Client::APICONSULTA);
```

En Laravel, por env/config (sin tocar código):

```dotenv
APIPERUDEV_INSTANCIA=apiconsulta   # 'apiperu' (default) | 'apiconsulta'
```

> La instancia es una **clave de lista blanca**, no una URL. Un valor fuera de la lista
> lanza `InvalidArgumentException`. Para habilitar un white-label propio nuevo, se agrega
> al paquete y se publica una versión. El token de cada instancia es el suyo (bases de
> datos y usuarios separados): usá el token de la instancia a la que apuntás.

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
