<?php

namespace App\Providers;

use App\Console\Commands\CheckClaimability;
use App\Parser\CSVFileParser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CheckClaimability::class, function ($app) {
            return new CheckClaimability($app->make(CSVFileParser::class));
        });
    }
}
