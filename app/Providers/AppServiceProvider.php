<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Settings::class, function () {
            return Settings::make(storage_path('app/settings.json'));
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // No Docker, o .env do volume costuma ter DB_HOST=127.0.0.1; usar o serviço mysql do Compose.
        if (file_exists('/.dockerenv') && config('database.connections.mysql.host') === '127.0.0.1') {
            config(['database.connections.mysql.host' => 'mysql']);
            config(['database.connections.mysql.port' => 3306]);
        }

        Paginator::useBootstrap();
        Schema::defaultStringLength(191);
    }
}
