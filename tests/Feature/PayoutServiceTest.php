<?php

namespace Tests\Feature;

use App\Models\GatewayAccount;
use App\Models\LedgerEntry;
use App\Models\Ong;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\PayoutItem;
use App\Services\Payments\PayoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private function activeAsaasAccount(): void
    {
        GatewayAccount::create([
            'provider' => 'asaas',
            'mode' => 'sandbox',
            'credentials' => ['api_key' => 'key-123'],
            'active' => true,
        ]);
    }

    private function paidPayment(Ong $ong, string $period, int $amount, int $fee): Payment
    {
        return Payment::create([
            'ong_id' => $ong->id,
            'gateway' => 'asaas',
            'gateway_payment_id' => 'pay_'.uniqid(),
            'method' => 'pix',
            'amount_cents' => $amount,
            'fee_cents' => $fee,
            'net_cents' => $amount - $fee,
            'status' => 'paid',
            'period' => $period,
        ]);
    }

    public function test_close_month_groups_paid_payments_per_ong(): void
    {
        $ong = Ong::create(['name' => 'ONG A', 'active' => true, 'pix_key' => 'a@pix']);
        $this->paidPayment($ong, '2026-10', 10000, 500);
        $this->paidPayment($ong, '2026-10', 5000, 250);

        $service = app(PayoutService::class);
        $created = $service->closeMonth('2026-10');

        $this->assertSame(1, $created);

        $payout = Payout::where('ong_id', $ong->id)->where('period', '2026-10')->first();
        $this->assertNotNull($payout);
        $this->assertSame(15000, $payout->gross_cents);
        $this->assertSame(750, $payout->fee_cents);
        $this->assertSame(14250, $payout->net_cents);
        $this->assertSame('pending', $payout->status);
        $this->assertSame(2, PayoutItem::where('payout_id', $payout->id)->count());
        $this->assertSame(2, Payment::where('payout_id', $payout->id)->count());
    }

    public function test_close_month_is_idempotent(): void
    {
        $ong = Ong::create(['name' => 'ONG A', 'active' => true]);
        $this->paidPayment($ong, '2026-10', 10000, 500);

        $service = app(PayoutService::class);

        $this->assertSame(1, $service->closeMonth('2026-10'));
        $this->assertSame(0, $service->closeMonth('2026-10'));
        $this->assertSame(1, Payout::count());
    }

    public function test_dispatch_pays_payout_and_records_debit_ledger_entry(): void
    {
        $this->activeAsaasAccount();
        $ong = Ong::create(['name' => 'ONG A', 'active' => true, 'pix_key' => 'a@pix']);
        $this->paidPayment($ong, '2026-10', 10000, 500);

        $service = app(PayoutService::class);
        $service->closeMonth('2026-10');

        $dispatched = $service->dispatchPending();
        $this->assertSame(1, $dispatched);

        $payout = Payout::first();
        $this->assertSame('paid', $payout->status);
        $this->assertNotNull($payout->gateway_transfer_id);
        $this->assertNotNull($payout->paid_at);

        $debit = LedgerEntry::where('ong_id', $ong->id)->where('type', 'debit')->first();
        $this->assertNotNull($debit);
        $this->assertSame(9500, $debit->amount_cents);
    }

    public function test_dispatch_is_idempotent(): void
    {
        $this->activeAsaasAccount();
        $ong = Ong::create(['name' => 'ONG A', 'active' => true, 'pix_key' => 'a@pix']);
        $this->paidPayment($ong, '2026-10', 10000, 500);

        $service = app(PayoutService::class);
        $service->closeMonth('2026-10');

        $service->dispatchPending();
        $service->dispatchPending();

        $this->assertSame(1, LedgerEntry::where('type', 'debit')->count());
    }

    public function test_close_month_command_runs(): void
    {
        $ong = Ong::create(['name' => 'ONG A', 'active' => true]);
        $this->paidPayment($ong, '2026-10', 10000, 500);

        $this->artisan('payouts:close-month', ['period' => '2026-10'])
            ->assertSuccessful();

        $this->assertSame(1, Payout::count());
    }
}
