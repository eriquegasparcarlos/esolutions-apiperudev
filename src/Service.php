<?php

namespace Esolutions\ApiPeruDev;

use Illuminate\Support\Facades\Http;
use Throwable;

class Service
{
    public static function searchWithInput(string $type, string $number): array
    {
        try {
            $param    = $type === 'ruc' ? 'ruc' : 'dni';
            $response = self::baseRequest()
                ->post(self::url() . '/api/' . $type, [$param => $number]);
            return $response->json() ?? ['success' => false, 'message' => 'La API no devolvió una respuesta válida.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function searchExchangeRateSaleWithInput(string $date): array
    {
        try {
            $response = self::baseRequest()
                ->post(self::url() . '/api/tipo_de_cambio', ['fecha' => $date]);
            return $response->json() ?? ['success' => false, 'message' => 'La API no devolvió una respuesta válida.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function searchFiscalAddress(string $number): array
    {
        try {
            $response = self::baseRequest()
                ->post(self::url() . '/api/ruc_domicilio_fiscal', ['ruc' => $number]);
            return $response->json() ?? ['success' => false, 'message' => 'La API no devolvió una respuesta válida.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function searchEstablishments(string $number): array
    {
        try {
            $response = self::baseRequest()
                ->post(self::url() . '/api/ruc_establecimientos_anexos', ['ruc' => $number]);
            return $response->json() ?? ['success' => false, 'message' => 'La API no devolvió una respuesta válida.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function searchPorts(): array
    {
        try {
            $response = self::baseRequest()
                ->post(self::url() . '/api/puertos');
            return $response->json() ?? ['success' => false, 'message' => 'La API no devolvió una respuesta válida.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function searchAirports(): array
    {
        try {
            $response = self::baseRequest()
                ->post(self::url() . '/api/aeropuertos');
            return $response->json() ?? ['success' => false, 'message' => 'La API no devolvió una respuesta válida.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function searchCpeWithInput(
        string $companyNumber,
        string $documentTypeId,
        string $series,
        string $number,
        string $dateOfIssue,
        float  $total
    ): array {
        try {
            $response = self::baseRequest()
                ->post(self::url() . '/api/cpe', [
                    'ruc_emisor'              => $companyNumber,
                    'codigo_tipo_documento'   => $documentTypeId,
                    'serie_documento'         => $series,
                    'numero_documento'        => $number,
                    'fecha_de_emision'        => $dateOfIssue,
                    'total'                   => $total,
                ]);
            return $response->json() ?? ['success' => false, 'message' => 'La API no devolvió una respuesta válida.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function searchCpeMultiple(array $comprobantes, string $rucEmisor): array
    {
        try {
            $response = self::baseRequest()
                ->post(self::url() . '/api/validacion_multiple_cpe', [
                    'ruc_emisor'    => $rucEmisor,
                    'comprobantes'  => $comprobantes,
                ]);
            return $response->json() ?? ['success' => false, 'message' => 'La API no devolvió una respuesta válida.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function buildCpeString(
        string $companyNumber,
        string $documentTypeId,
        string $series,
        string $number,
        string $dateOfIssue,
        float  $total
    ): string {
        return implode('|', [
            $companyNumber,
            $documentTypeId,
            $series,
            $number,
            $dateOfIssue,
            $total,
        ]);
    }

    public function searchRuc(\Illuminate\Http\Request $request): array
    {
        return self::searchWithInput('ruc', $request->input('number', ''));
    }

    public function searchDni(\Illuminate\Http\Request $request): array
    {
        return self::searchWithInput('dni', $request->input('number', ''));
    }

    private static function url(): string
    {
        return rtrim(config('esolutions.apiperudev.url', ''), '/');
    }

    private static function baseRequest(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withOptions(['verify' => false])
            ->withToken(config('esolutions.apiperudev.token'))
            ->withHeaders([
                'x-app-version' => config('version.version', ''),
                'x-app-build'   => config('version.build', ''),
            ])
            ->connectTimeout(5)
            ->timeout(10);
    }
}
