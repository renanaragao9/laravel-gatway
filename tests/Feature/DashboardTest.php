<?php

namespace Tests\Feature;

use App\Filament\Widgets\GatewayStatsOverview;
use App\Filament\Widgets\MonthlyRevenueChart;
use App\Filament\Widgets\PendingPayoutsTable;
use App\Models\Ong;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_for_admin(): void
    {
        $this->actingAs(User::factory()->create());

        $ong = Ong::create(['name' => 'ONG A', 'active' => true]);

        Payment::create([
            'ong_id' => $ong->id,
            'gateway' => 'asaas',
            'method' => 'pix',
            'amount_cents' => 10000,
            'fee_cents' => 500,
            'net_cents' => 9500,
            'status' => 'paid',
            'period' => now()->format('Y-m'),
        ]);

        Payout::create([
            'ong_id' => $ong->id,
            'period' => now()->format('Y-m'),
            'gross_cents' => 10000,
            'fee_cents' => 500,
            'net_cents' => 9500,
            'status' => 'pending',
        ]);

        $this->get('/admin')->assertSuccessful();
    }

    public function test_widget_classes_are_discoverable(): void
    {
        $this->assertTrue(class_exists(GatewayStatsOverview::class));
        $this->assertTrue(class_exists(MonthlyRevenueChart::class));
        $this->assertTrue(class_exists(PendingPayoutsTable::class));
    }
}
