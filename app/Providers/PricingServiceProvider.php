<?php

namespace App\Providers;

use App\Pricing\BasePrice;
use App\Pricing\Decorators\DiscountDecorator;
use App\Pricing\Decorators\ShippingDecorator;
use App\Pricing\Decorators\TaxDecorator;
use App\Pricing\Discounts\DiscountStrategyFactory;
use App\Pricing\PriceCalculatorInterface;
use Illuminate\Support\ServiceProvider;

class PricingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(PriceCalculatorInterface::class, function () {
            $chain = new BasePrice;
            $chain = new TaxDecorator($chain, (float) config('commerce.tax.default_rate'));
            $chain = new ShippingDecorator($chain);
            $chain = new DiscountDecorator($chain, $this->app->make(DiscountStrategyFactory::class));

            return $chain;
        });
    }
}
