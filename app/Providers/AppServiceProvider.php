<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        Schema::defaultStringLength(191);

        // Macro to convert any Carbon instance to the configured display timezone.
        // DB stores UTC; this shifts to APP_DISPLAY_TIMEZONE for user-facing views.
        // Usage in Blade: $model->created_at->inDisplayTz()->format('d M Y H:i')
        Carbon::macro('inDisplayTz', function () {
            /** @var Carbon $this */
            return $this->copy()->setTimezone(config('app.display_timezone'));
        });
    }
}
