<?php

namespace Esolutions\ApiPeruDev;

use Illuminate\Support\ServiceProvider;

/**
 * ServiceProvider OPCIONAL para Laravel (auto-descubierto vía composer extra.laravel.providers).
 *
 * - Registra el default de config('esolutions.apiperudev.token') sin pisar la config existente.
 * - Bindea Client como singleton.
 * - Publica el config: php artisan vendor:publish --tag=esolutions-apiperudev-config
 *
 * Fuera de Laravel este provider no se carga; la clase Client funciona igual (standalone).
 */
class ApiPeruDevServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/esolutions.php', 'esolutions');

        $this->app->singleton(Client::class, function () {
            return new Client();
        });
    }

    public function boot()
    {
        if (method_exists($this->app, 'runningInConsole') && $this->app->runningInConsole()) {
            $target = function_exists('config_path')
                ? config_path('esolutions.php')
                : $this->app->basePath('config/esolutions.php');

            $this->publishes(array(
                __DIR__ . '/../config/esolutions.php' => $target,
            ), 'esolutions-apiperudev-config');
        }
    }
}
