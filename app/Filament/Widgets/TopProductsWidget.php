<?php

namespace App\Filament\Widgets;

use App\Repositories\AnalyticsRepository;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Throwable;

class TopProductsWidget extends Widget
{
    protected string $view = 'filament.widgets.top-products';

    protected int|string|array $columnSpan = 'full';

    /**
     * Top-rated active products per category. Guarded because the underlying
     * query relies on MySQL window functions (see RecentSalesWidget note).
     */
    public function getProducts(): Collection
    {
        try {
            return app(AnalyticsRepository::class)->topProductsPerCategory(3);
        } catch (Throwable) {
            return collect();
        }
    }
}
