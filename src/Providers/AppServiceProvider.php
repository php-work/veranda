<?php
namespace Veranda\Providers;

use Illuminate\Support\{
    ServiceProvider
};
use Veranda\{
    Luri,
    VerScheme
};

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Luri::registerScheme('ver', VerScheme::instance());
    }

    public function register()
    {
    }
}
