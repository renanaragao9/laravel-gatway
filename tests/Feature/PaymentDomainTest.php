<?php

namespace Tests\Feature;

use App\Models\GatewayAccount;
use App\Models\LedgerEntry;
use App\Models\Ong;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\PayoutItem;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentDomainTest extends TestCase
{
    use RefreshDatabase;

    private function makeOng(): Ong
    {
        return Ong::create([
            'name' => 'ONG Teste',
            'document' => '12.345.678/0001-90',
            'pix_key' => 'ong@pix.com',
            'active' => true,
        ]);
    }

    public function test_ong_has_payments_payouts_and_ledger_relations(): void
    {
        $ong = $this->makeOng();

        $payment = Payment::create([
            'ong_id' => $ong->id,
            'gateway' => 'asaas',
            'method' => 'pix',
            'amount_cents' => 10000,
            'fee_cents' => 500,
            'net_cents' => 9500,
            'status' => 'paid',
            'period' => '2026-10',
        ]);

        $payout = Payout::create([
            'ong_id' => $ong->id,
            'period' => '2026-10',
            'gross_cents' => 10000,
            'fee_cents' => 500,
            'net_cents' => 9500,
            'status' => 'pending',
        ]);

        LedgerEntry::create([
            'ong_id' => $ong->id,
            'type' => 'credit',
            'amount_cents' => 9500,
            'balance_after_cents' => 9500,
        ]);

        $this->assertTrue($ong->payments->contains($payment));
        $this->assertTrue($ong->payouts->contains($payout));
        $this->assertCount(1, $ong->ledgerEntries);
        $this->assertSame($ong->id, $payment->ong->id);
    }

    public function test_payout_groups_payments_via_items(): void
    {
        $ong = $this->makeOng();

        $payout = Payout::create([
            'ong_id' => $ong->id,
            'period' => '2026-10',
            'gross_cents' => 10000,
            'net_cents' => 9500,
            'status' => 'pending',
        ]);

        $payment = Payment::create([
            'ong_id' => $ong->id,
            'gateway' => 'asaas',
            'method' => 'pix',
            'amount_cents' => 10000,
            'net_cents' => 9500,
            'status' => 'paid',
            'period' => '2026-10',
            'payout_id' => $payout->id,
        ]);

        $item = PayoutItem::create([
            'payout_id' => $payout->id,
            'payment_id' => $payment->id,
            'amount_cents' => 9500,
        ]);

        $this->assertTrue($payout->items->contains($item));
        $this->assertTrue($payout->payments->contains($payment));
        $this->assertSame($payout->id, $payment->payout->id);
        $this->assertSame($payment->id, $item->payment->id);
    }

    public function test_money_fields_are_cast_to_integer(): void
    {
        $ong = $this->makeOng();

        $payment = Payment::create([
            'ong_id' => $ong->id,
            'gateway' => 'asaas',
            'method' => 'pix',
            'amount_cents' => '10000',
            'net_cents' => '9500',
            'status' => 'pending',
            'period' => '2026-10',
        ])->fresh();

        $this->assertIsInt($payment->amount_cents);
        $this->assertSame(10000, $payment->amount_cents);
    }

    public function test_gateway_account_credentials_are_encrypted(): void
    {
        $account = GatewayAccount::create([
            'provider' => 'asaas',
            'mode' => 'sandbox',
            'credentials' => ['api_key' => 'secret-123'],
            'active' => true,
        ]);

        $this->assertSame('secret-123', $account->fresh()->credentials['api_key']);

        $raw = \DB::table('gateway_accounts')->where('id', $account->id)->value('credentials');
        $this->assertStringNotContainsString('secret-123', (string) $raw);
    }

    public function test_webhook_payload_is_cast_to_array(): void
    {
        $event = WebhookEvent::create([
            'provider' => 'asaas',
            'event_type' => 'PAYMENT_RECEIVED',
            'external_id' => 'evt_1',
            'payload' => ['id' => 'pay_1', 'value' => 100.0],
            'signature_valid' => true,
            'processed_at' => now(),
        ])->fresh();

        $this->assertIsArray($event->payload);
        $this->assertSame('pay_1', $event->payload['id']);
        $this->assertTrue($event->signature_valid);
    }
}
