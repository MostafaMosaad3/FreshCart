<?php

namespace Tests\Feature\Week08\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_allows_admins_into_the_panel(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->get('/admin')->assertOk();
    }

    public function test_it_forbids_non_admins(): void
    {
        $customer = User::factory()->customer()->create();
        $this->actingAs($customer);

        $this->get('/admin')->assertForbidden();
    }
}
