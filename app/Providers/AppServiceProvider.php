<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Empresa;
use App\Models\Configuracion;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Compartir datos de empresa y configuración con todas las vistas
        View::composer('*', function ($view) {
            try {
                $empresa = Empresa::first();
                $config = Configuracion::pluck('valor', 'clave')->toArray();
                $view->with('empresaGlobal', $empresa)
                     ->with('configGlobal', $config);
            } catch (\Exception $e) {
                $view->with('empresaGlobal', null)
                     ->with('configGlobal', []);
            }
        });
    }
}
