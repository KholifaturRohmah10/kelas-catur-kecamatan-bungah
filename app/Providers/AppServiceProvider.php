<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Support\WindowsSafeFilesystem;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('files', fn (): WindowsSafeFilesystem => new WindowsSafeFilesystem);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));
    }
}
