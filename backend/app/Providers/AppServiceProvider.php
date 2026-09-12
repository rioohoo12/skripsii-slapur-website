<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        if (config('database.default') === 'sqlite') {
            $path = config('database.connections.sqlite.database');
            if ($path && !file_exists($path)) {
                $dir = dirname($path);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                @touch($path);
            }
        }
    }
}
