<?php

namespace Esolutions\ApiPeruDev;

/**
 * Facade estática (compatibilidad con la versión anterior del paquete).
 *
 * La URL sale de la INSTANCIA elegida (lista blanca del paquete), no de un texto libre.
 * El Client por defecto resuelve token e instancia desde config('esolutions.apiperudev.*')
 * → por defecto apiperu.dev. Para apuntar a otra instancia (o inyectar el token en runtime,
 * ej. guardado en BD), registrá un Client configurado:
 *     Service::using(\Esolutions\ApiPeruDev\Client::apiConsulta($tokenDeBd));
 */
class Service
{
    /** @var Client|null */
    private static $default = null;

    /** @return Client */
    public static function client()
    {
        if (self::$default === null) {
            self::$default = new Client();
        }
        return self::$default;
    }

    /** Fija el Client por defecto (ej. con token inyectado desde BD). @return void */
    public static function using(Client $client)
    {
        self::$default = $client;
    }

    // ======= Compatibilidad con la versión anterior (mismas firmas) =======

    public static function searchWithInput($type, $number)
    {
        return $type === 'ruc' ? self::client()->ruc($number) : self::client()->dni($number);
    }

    public static function searchExchangeRateSaleWithInput($date)
    {
        return self::client()->tipoDeCambio($date);
    }

    public static function searchFiscalAddress($number)
    {
        return self::client()->rucDomicilioFiscal($number);
    }

    public static function searchEstablishments($number)
    {
        return self::client()->rucEstablecimientosAnexos($number);
    }

    public static function searchPorts()
    {
        return self::client()->puertos();
    }

    public static function searchAirports()
    {
        return self::client()->aeropuertos();
    }

    public static function searchCpeWithInput($companyNumber, $documentTypeId, $series, $number, $dateOfIssue, $total)
    {
        return self::client()->cpe($companyNumber, $documentTypeId, $series, $number, $dateOfIssue, $total);
    }

    public static function searchCpeMultiple(array $comprobantes, $rucEmisor)
    {
        return self::client()->validacionMultipleCpe($comprobantes, $rucEmisor);
    }

    public static function searchMassiveCpe(array $data)
    {
        return self::client()->validacionMultipleCpeRaw($data);
    }

    public static function buildCpeString($companyNumber, $documentTypeId, $series, $number, $dateOfIssue, $total)
    {
        return Client::buildCpeString($companyNumber, $documentTypeId, $series, $number, $dateOfIssue, $total);
    }

    /** @param mixed $request objeto con ->input('number') (Laravel Request). */
    public function searchRuc($request)
    {
        return self::searchWithInput('ruc', $request->input('number', ''));
    }

    /** @param mixed $request objeto con ->input('number') (Laravel Request). */
    public function searchDni($request)
    {
        return self::searchWithInput('dni', $request->input('number', ''));
    }

    // ======= Nuevos endpoints (docs.apiperu.dev) =======

    public static function ruc($ruc) { return self::client()->ruc($ruc); }
    public static function dni($dni) { return self::client()->dni($dni); }
    public static function dniRuc($dni) { return self::client()->dniRuc($dni); }
    public static function rucSunat($ruc) { return self::client()->rucSunat($ruc); }
    public static function rucRepresentantes($ruc) { return self::client()->rucRepresentantes($ruc); }
    public static function rucTrabajadores($ruc) { return self::client()->rucTrabajadores($ruc); }
    public static function rucDeudaCoactiva($ruc) { return self::client()->rucDeudaCoactiva($ruc); }
    public static function rucContacto($ruc) { return self::client()->rucContacto($ruc); }
    public static function rucSsco($ruc) { return self::client()->rucSsco($ruc); }
    public static function placa($placa) { return self::client()->placa($placa); }
    public static function licenciaConducir($numLicencia) { return self::client()->licenciaConducir($numLicencia); }
    public static function comisionesAfp($periodo) { return self::client()->comisionesAfp($periodo); }
    public static function ubigeos() { return self::client()->ubigeos(); }
}
