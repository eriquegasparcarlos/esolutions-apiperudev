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
                ->post(config('esolutions.apiperudev.url') . '/' . $type, [$param => $number]);
            return $response->json() ?? ['success' => false, 'message' => 'La API no devolvió una respuesta válida.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function searchExchangeRateSaleWithInput(string $date): array
    {
        try {
            $response = self::baseRequest()
                ->post(config('esolutions.apiperudev.url') . '/tipo-de-cambio', ['fecha' => $date]);
            return $response->json() ?? ['success' => false, 'message' => 'La API no devolvió una respuesta válida.'];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function searchRuc(\Illuminate\Http\Request $request): array
    {
        return self::searchWithInput('ruc', $request->input('number', ''));
    }

    public function searchDni(\Illuminate\Http\Request $request): array
    {
        return self::searchWithInput('dni', $request->input('number', ''));
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
