<?php

namespace Swis\PdfcrowdClient;

use Illuminate\Support\ServiceProvider;

class PdfcrowdServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([__DIR__.'/../config/pdfcrowd.php' => config_path('pdfcrowd.php')], 'config');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom( __DIR__ . '/../config/pdfcrowd.php', 'pdfcrowd');

        $this->app->bind(\Swis\PdfcrowdClient\Pdfcrowd::class, function () {
            return new \Swis\PdfcrowdClient\Pdfcrowd(
                config('pdfcrowd.username'),
                config('pdfcrowd.key')
            );
        });
    }
}
