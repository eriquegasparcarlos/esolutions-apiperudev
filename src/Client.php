<?php

namespace Esolutions\ApiPeruDev;

use GuzzleHttp\Client as HttpClient;
use Throwable;

/**
 * Cliente universal para la API de apiperu.dev / apiconsulta.dev (RUC/DNI, SUNAT, CPE, etc.).
 *
 * La URL base NO es texto libre inyectable: se elige entre una LISTA BLANCA de instancias
 * (Client::APIPERU / Client::APICONSULTA) que el paquete resuelve internamente. Una instancia
 * desconocida (o una URL) lanza InvalidArgumentException. Así el token NUNCA puede terminar
 * enviándose a un host no controlado (p. ej. de la competencia o un servidor que lo robe).
 * Configurable: el token (Bearer) y la instancia (default apiperu).
 *
 * Universal:
 *  - PHP 7.2+ y Laravel 5.7 a 13 (y standalone, sin Laravel).
 *  - Usa Guzzle directamente (no el HTTP client de Illuminate, que requiere Laravel 7+).
 *  - Sintaxis compatible con PHP 7.2.
 *
 * Token (por orden de prioridad):
 *  1) Inyección:  new Client('API_TOKEN')
 *  2) Fallback a config Laravel: config('esolutions.apiperudev.token') (si config() existe).
 *
 * Instancia (por orden de prioridad):
 *  1) Inyección:  Client::apiConsulta($token)  ó  new Client($token, [], Client::APICONSULTA)
 *  2) Fallback a config Laravel: config('esolutions.apiperudev.instancia') (si config() existe).
 *  3) Default: Client::APIPERU (apiperu.dev).
 *
 * Todos los métodos devuelven un array (respuesta de la API) o
 * ['success' => false, 'message' => '...'] ante error de red/decodificación.
 */
class Client
{
    /**
     * Instancias de API VÁLIDAS (lista blanca). El consumidor elige UNA de estas; NO puede
     * inyectar una URL arbitraria. Para habilitar una instancia nueva (un white-label propio),
     * se agrega al mapa self::$BASES y se publica una versión del paquete.
     */
    const APIPERU     = 'apiperu';
    const APICONSULTA = 'apiconsulta';

    /** Mapa CERRADO instancia → URL base. No se expone ni se sobrescribe desde afuera. */
    private static $BASES = array(
        'apiperu'     => 'https://api.apiperu.dev',
        'apiconsulta' => 'https://api.apiconsulta.dev',
    );

    /** @var string URL base resuelta desde la instancia (lista blanca). */
    private $baseUrl;

    /** @var string */
    private $token;

    /** @var string */
    private $appVersion;

    /** @var string */
    private $appBuild;

    /** @var HttpClient|null */
    private $http = null;

    /**
     * @param string|null $token      Bearer token. Si es null, usa config('esolutions.apiperudev.token').
     * @param array       $meta       ['version' => '', 'build' => ''] para cabeceras x-app-*.
     * @param string|null $instancia  Client::APIPERU (default) o Client::APICONSULTA. Si es null, usa
     *                                config('esolutions.apiperudev.instancia') y, si no, APIPERU.
     *                                NO acepta URLs: una instancia desconocida lanza InvalidArgumentException.
     */
    public function __construct($token = null, array $meta = array(), $instancia = null)
    {
        $this->token      = $token !== null ? $token : self::cfg('esolutions.apiperudev.token', '');
        $this->appVersion = isset($meta['version']) ? $meta['version'] : self::cfg('version.version', '');
        $this->appBuild   = isset($meta['build']) ? $meta['build'] : self::cfg('version.build', '');

        $instancia = $instancia !== null ? $instancia : self::cfg('esolutions.apiperudev.instancia', self::APIPERU);
        $this->baseUrl = self::resolveBaseUrl($instancia);
    }

    /** @return self */
    public static function make($token = null, array $meta = array(), $instancia = null)
    {
        return new self($token, $meta, $instancia);
    }

    /** Cliente apuntando a apiperu.dev (instancia por defecto). @return self */
    public static function apiPeru($token = null, array $meta = array())
    {
        return new self($token, $meta, self::APIPERU);
    }

    /** Cliente apuntando a apiconsulta.dev. @return self */
    public static function apiConsulta($token = null, array $meta = array())
    {
        return new self($token, $meta, self::APICONSULTA);
    }

    /**
     * Resuelve la URL base desde la instancia (lista blanca). Rechaza cualquier valor que
     * no esté en self::$BASES (incluida una URL) para que el paquete solo pueda hablar con
     * infraestructura propia y el token nunca viaje a un host no controlado.
     * @param string $instancia
     * @return string
     */
    private static function resolveBaseUrl($instancia)
    {
        $key = is_string($instancia) ? strtolower(trim($instancia)) : '';
        if (!isset(self::$BASES[$key])) {
            $shown = is_string($instancia) ? $instancia : gettype($instancia);
            throw new \InvalidArgumentException(
                "Instancia de API no válida: '" . $shown . "'. Usá Client::APIPERU o Client::APICONSULTA."
            );
        }
        return self::$BASES[$key];
    }

    /** @return $this */
    public function setToken($token)
    {
        $this->token = (string) $token;
        return $this;
    }

    // ===================== RUC / DNI =====================

    /** Datos del contribuyente (padrón reducido SUNAT). POST /ruc @return array */
    public function ruc($ruc)
    {
        return $this->post('/ruc', array('ruc' => $ruc));
    }

    /** Datos de la persona (RENIEC). POST /dni @return array */
    public function dni($dni)
    {
        return $this->post('/dni', array('dni' => $dni));
    }

    /** Calcula el RUC (10...) a partir de un DNI. POST /dni-ruc @return array */
    public function dniRuc($dni)
    {
        return $this->post('/dni-ruc', array('dni' => $dni));
    }

    /** Ficha SUNAT completa. POST /ruc-sunat @return array */
    public function rucSunat($ruc)
    {
        return $this->post('/ruc-sunat', array('ruc' => $ruc));
    }

    /** Domicilio fiscal. POST /ruc-domicilio-fiscal @return array */
    public function rucDomicilioFiscal($ruc)
    {
        return $this->post('/ruc-domicilio-fiscal', array('ruc' => $ruc));
    }

    /** Establecimientos anexos. POST /ruc-establecimientos-anexos @return array */
    public function rucEstablecimientosAnexos($ruc)
    {
        return $this->post('/ruc-establecimientos-anexos', array('ruc' => $ruc));
    }

    /** Representantes legales. POST /ruc-representantes @return array */
    public function rucRepresentantes($ruc)
    {
        return $this->post('/ruc-representantes', array('ruc' => $ruc));
    }

    /** Cantidad de trabajadores. POST /ruc-trabajadores @return array */
    public function rucTrabajadores($ruc)
    {
        return $this->post('/ruc-trabajadores', array('ruc' => $ruc));
    }

    /** Deuda coactiva. POST /ruc-deuda-coactiva @return array */
    public function rucDeudaCoactiva($ruc)
    {
        return $this->post('/ruc-deuda-coactiva', array('ruc' => $ruc));
    }

    /** Datos de contacto. POST /ruc-contacto @return array */
    public function rucContacto($ruc)
    {
        return $this->post('/ruc-contacto', array('ruc' => $ruc));
    }

    /** Sociedad de comerciantes (SSCO). POST /ruc-ssco @return array */
    public function rucSsco($ruc)
    {
        return $this->post('/ruc-ssco', array('ruc' => $ruc));
    }

    // ===================== Otros =====================

    /** Datos vehiculares por placa. POST /placa @return array */
    public function placa($placa)
    {
        return $this->post('/placa', array('placa' => $placa));
    }

    /** Titular de una licencia de conducir. POST /licencia-conducir @return array */
    public function licenciaConducir($numLicencia)
    {
        return $this->post('/licencia-conducir', array('num_licencia' => $numLicencia));
    }

    /** Comisiones AFP por período (AAAA-MM). POST /comisiones-afp @return array */
    public function comisionesAfp($periodo)
    {
        return $this->post('/comisiones-afp', array('periodo' => $periodo));
    }

    /** Tipo de cambio por fecha (AAAA-MM-DD). POST /tipo-de-cambio @return array */
    public function tipoDeCambio($fecha)
    {
        return $this->post('/tipo-de-cambio', array('fecha' => $fecha));
    }

    /** Catálogo de puertos. POST /puertos @return array */
    public function puertos()
    {
        return $this->post('/puertos', array());
    }

    /** Catálogo de aeropuertos. POST /aeropuertos @return array */
    public function aeropuertos()
    {
        return $this->post('/aeropuertos', array());
    }

    /** Catálogo de ubigeos SUNAT (árbol). POST /ubigeos @return array */
    public function ubigeos()
    {
        return $this->post('/ubigeos', array());
    }

    // ===================== CPE (validación de comprobantes) =====================

    /**
     * Valida un comprobante ante SUNAT. POST /cpe
     * @return array
     */
    public function cpe($rucEmisor, $codigoTipoDocumento, $serie, $numero, $fechaEmision, $total)
    {
        return $this->post('/cpe', array(
            'ruc_emisor'            => $rucEmisor,
            'codigo_tipo_documento' => $codigoTipoDocumento,
            'serie_documento'       => $serie,
            'numero_documento'      => $numero,
            'fecha_de_emision'      => $fechaEmision,
            'total'                 => $total,
        ));
    }

    /**
     * Validación múltiple de comprobantes. POST /validacion-multiple-cpe
     * @param array  $comprobantes
     * @param string $rucEmisor
     * @return array
     */
    public function validacionMultipleCpe(array $comprobantes, $rucEmisor)
    {
        return $this->post('/validacion-multiple-cpe', array(
            'ruc_emisor'   => $rucEmisor,
            'comprobantes' => $comprobantes,
        ));
    }

    /**
     * Validación múltiple de comprobantes con payload crudo (compat: el body se envía tal cual).
     * POST /validacion-multiple-cpe
     * @return array
     */
    public function validacionMultipleCpeRaw(array $data)
    {
        return $this->post('/validacion-multiple-cpe', $data, 60);
    }

    /** Arma el string "ruc|tipo|serie|numero|fecha|total" para CPE. @return string */
    public static function buildCpeString($rucEmisor, $codigoTipoDocumento, $serie, $numero, $fechaEmision, $total)
    {
        return implode('|', array($rucEmisor, $codigoTipoDocumento, $serie, $numero, $fechaEmision, $total));
    }

    // ===================== Infraestructura =====================

    /** @return HttpClient */
    private function client()
    {
        if ($this->http === null) {
            $this->http = new HttpClient(array(
                'verify'          => false,
                'connect_timeout' => 5,
                'http_errors'     => false,
            ));
        }
        return $this->http;
    }

    /** @return array */
    private function headers()
    {
        return array(
            'Authorization' => 'Bearer ' . $this->token,
            'x-app-version' => $this->appVersion,
            'x-app-build'   => $this->appBuild,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        );
    }

    /**
     * @param string $path
     * @param array  $body
     * @param int    $timeout
     * @return array
     */
    private function post($path, array $body = array(), $timeout = 12)
    {
        try {
            $options = array(
                'headers' => $this->headers(),
                'timeout' => $timeout,
            );
            if (!empty($body)) {
                $options['json'] = $body;
            }
            $response = $this->client()->request('POST', $this->baseUrl . $path, $options);
            $decoded  = json_decode((string) $response->getBody(), true);
            if (is_array($decoded)) {
                return $decoded;
            }
            return array('success' => false, 'message' => 'La API no devolvió una respuesta válida.', 'status' => $response->getStatusCode());
        } catch (Throwable $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Lee config() de Laravel si está disponible; si no (standalone), devuelve el default.
     * @return string
     */
    private static function cfg($key, $default = '')
    {
        if (function_exists('config')) {
            $value = config($key, $default);
            return $value !== null ? $value : $default;
        }
        return $default;
    }
}
