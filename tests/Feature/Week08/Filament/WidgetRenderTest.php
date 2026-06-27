<?php

namespace Tests\Feature\Week08\Filament;

use App\Filament\Widgets\KpiWidget;
use App\Filament\Widgets\RecentSalesWidget;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WidgetRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_kpi_widget_renders_with_seeded_data(): void
    {
        Order::factory()->count(5)->create();

        Livewire::test(KpiWidget::class)->assertOk();
    }

    public function test_recent_sales_widget_renders(): void
    {
        Livewire::test(RecentSalesWidget::class)->assertOk();
    }
}
