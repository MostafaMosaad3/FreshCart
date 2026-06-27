<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Vendor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KpiWidget extends StatsOverviewWidget
{
    protected ?string $heading = 'At a glance';

    protected function getStats(): array
    {
        return [
            Stat::make('Total Products', Product::count())
                ->description('All time')
                ->color('primary'),
            Stat::make('Orders This Month', Order::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count())
                ->description('Created in '.now()->format('F'))
                ->color('success'),
            Stat::make('Verified Vendors', Vendor::where('is_verified', true)->count())
                ->color('info'),
            Stat::make('Avg Rating', number_format((float) Review::avg('rating'), 2))
                ->description(Review::count().' reviews')
                ->color('warning'),
        ];
    }
}
