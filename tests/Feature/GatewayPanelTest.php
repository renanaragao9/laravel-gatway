<?php

namespace Tests\Feature;

use App\Models\GatewayAccount;
use App\Models\Ong;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayPanelTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): void
    {
        $this->actingAs(User::factory()->create());
    }

    public function test_admin_can_access_payments_payouts_and_gateway_accounts(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/payments')->assertSuccessful();
        $this->get('/admin/payouts')->assertSuccessful();
        $this->get('/admin/gateway-accounts')->assertSuccessful();
        $this->get('/admin/gateway-accounts/create')->assertSuccessful();
    }

    public function test_guest_is_redirected_from_gateway_panels(): void
    {
        $this->get('/admin/payments')->assertRedirect();
        $this->get('/admin/payouts')->assertRedirect();
        $this->get('/admin/gateway-accounts')->assertRedirect();
    }

    public function test_payment_and_payout_view_pages_render(): void
    {
        $this->actingAsAdmin();

        $ong = Ong::create(['name' => 'ONG X', 'active' => true]);

        $payment = Payment::create([
            'ong_id' => $ong->id,
            'gateway' => 'asaas',
            'method' => 'pix',
            'amount_cents' => 10000,
            'net_cents' => 9500,
            'status' => 'paid',
            'period' => '2026-10',
        ]);

        $payout = Payout::create([
            'ong_id' => $ong->id,
            'period' => '2026-10',
            'gross_cents' => 10000,
            'net_cents' => 9500,
            'status' => 'pending',
        ]);

        $this->get("/admin/payments/{$payment->id}")->assertSuccessful();
        $this->get("/admin/payouts/{$payout->id}")->assertSuccessful();
    }

    public function test_gateway_account_can_be_created_with_encrypted_credentials(): void
    {
        $account = GatewayAccount::create([
            'provider' => 'asaas',
            'mode' => 'sandbox',
            'credentials' => ['api_key' => 'abc123'],
            'active' => true,
        ]);

        $this->assertDatabaseHas('gateway_accounts', [
            'id' => $account->id,
            'provider' => 'asaas',
            'mode' => 'sandbox',
            'active' => true,
        ]);
        $this->assertSame('abc123', $account->fresh()->credentials['api_key']);
    }
}
