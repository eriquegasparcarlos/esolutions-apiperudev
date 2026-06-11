# esolutions/apiperudev

Cliente HTTP para la API de [apiconsulta.dev](https://apiconsulta.dev).
Consulta datos de RUC, DNI y tipo de cambio en tiempo real.

## Instalación

```bash
composer require esolutions/apiperudev
```

```bash
composer require esolutions/apiperudev
```

## Namespace

```
Esolutions\ApiPeruDev\
```

---

## Configuración

Agregar en `config/esolutions.php`:

```php
'apiperudev' => [
    'url'   => env('APIPERUDEV_URL', 'https://my.apiconsulta.dev/api'),
    'token' => env('APIPERUDEV_TOKEN'),
],
```

Agregar en `.env`:

```dotenv
APIPERUDEV_URL=https://my.apiconsulta.dev/api
APIPERUDEV_TOKEN=tu_token_aqui
```

---

## Uso

```php
use Esolutions\ApiPeruDev\Service as ApiPeruDev;
```

### Buscar por RUC

```php
$result = ApiPeruDev::searchWithInput('ruc', '20100070970');

// Respuesta exitosa:
// [
//     'success' => true,
//     'data' => [
//         'razon_social' => 'EMPRESA SAC',
//         'direccion'    => 'AV. LIMA 123',
//         'ubigeo'       => '150101',
//         ...
//     ]
// ]

// Respuesta fallida:
// ['success' => false, 'message' => 'RUC no encontrado']
```

### Buscar por DNI

```php
$result = ApiPeruDev::searchWithInput('dni', '12345678');

// Respuesta exitosa:
// [
//     'success' => true,
//     'data' => [
//         'nombres'           => 'JUAN',
//         'apellido_paterno'  => 'PÉREZ',
//         'apellido_materno'  => 'GARCÍA',
//         ...
//     ]
// ]
```

### Tipo de cambio

```php
$result = ApiPeruDev::searchExchangeRateSaleWithInput('2026-05-13');

// Respuesta exitosa:
// [
//     'success' => true,
//     'data' => [
//         'venta'  => 3.72,
//         'compra' => 3.70,
//         'fecha'  => '2026-05-13'
//     ]
// ]
```

---

## Métodos

| Método | Parámetros | Descripción |
|---|---|---|
| `searchWithInput($type, $number)` | `string, string` | Consulta RUC o DNI. `$type`: `'ruc'` o `'dni'` |
| `searchExchangeRateSaleWithInput($date)` | `string` (formato `Y-m-d`) | Tipo de cambio para una fecha |
| `searchRuc(Request $request)` | `Request` | Endpoint de controlador — lee `number` del request |
| `searchDni(Request $request)` | `Request` | Endpoint de controlador — lee `number` del request |

---

## Comportamiento de red

| Parámetro | Valor |
|---|---|
| Timeout de conexión | 5 segundos |
| Timeout de respuesta | 10 segundos |
| SSL verify | Desactivado (compatible con entornos locales) |
| Autenticación | `Authorization: Bearer {token}` |

Todos los métodos retornan `array`. Los errores de red se capturan internamente — nunca lanza excepciones al llamador.

---

## Uso como endpoint de controlador

Si se necesita exponer la búsqueda como ruta API propia del proyecto:

```php
// routes/api.php
use Esolutions\ApiPeruDev\Service as ApiPeruDev;

Route::post('/search-ruc', [ApiPeruDev::class, 'searchRuc']);
Route::post('/search-dni', [ApiPeruDev::class, 'searchDni']);
```

```bash
POST /api/search-ruc
{ "number": "20100070970" }
```
