<?php

namespace App\Providers;

use App\Domain\Kost\Models\Kost;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Route model binding for Super Admin kost submissions
        Route::model('submission', Kost::class);
    }
}
