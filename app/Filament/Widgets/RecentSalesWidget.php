<?php

namespace App\Filament\Widgets;

use App\Models\Vendor;
use App\Repositories\AnalyticsRepository;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Collection;
use Throwable;

class RecentSalesWidget extends ChartWidget
{
    protected ?string $heading = 'Recent Sales (delivered, last 6 months)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $rows = $this->salesRows();

        return [
            'datasets' => [[
                'label' => 'Sales (EGP)',
                'data' => $rows->pluck('monthly_sales')->map(fn ($v) => (float) $v)->all(),
                'borderColor' => '#10b981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                'fill' => true,
                'tension' => 0.4,
            ]],
            'labels' => $rows->pluck('month')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * The analytics repository uses MySQL-only SQL (DATE_FORMAT, window
     * functions). Guard the call so the dashboard degrades gracefully on
     * any other driver instead of 500-ing the whole panel.
     */
    private function salesRows(): Collection
    {
        try {
            $vendorId = Vendor::query()->value('id');

            if ($vendorId === null) {
                return collect();
            }

            return app(AnalyticsRepository::class)->vendorMonthlySales((int) $vendorId, 6);
        } catch (Throwable) {
            return collect();
        }
    }
}
