<?php

use App\Exceptions\CouponMaxedOutException;
use App\Exceptions\InvalidDiscountConfigException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(
            fn (CouponMaxedOutException $e) => response()->json([
                'error' => 'coupon_maxed_out',
            ], 422)
        );

        $exceptions->render(
            fn (InvalidDiscountConfigException $e) => response()->json([
                'error' => 'invalid_discount_config',
            ], 422)
        );

        // UnknownDiscountStrategyException is intentionally not mapped.
        // It will fall back to Laravel's default exception handling (500).
    })
    ->create();
