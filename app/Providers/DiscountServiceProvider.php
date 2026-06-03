<?php

namespace App\Providers;

use App\Pricing\Discounts\DiscountStrategyFactory;
use Illuminate\Support\ServiceProvider;

class DiscountServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DiscountStrategyFactory::class, fn () => new DiscountStrategyFactory(config('discounts.strategies'))
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {}
}
