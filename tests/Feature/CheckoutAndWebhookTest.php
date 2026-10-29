<?php

namespace Tests\Feature;

use App\Models\GatewayAccount;
use App\Models\LedgerEntry;
use App\Models\Ong;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\Payments\Enums\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutAndWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function activeAsaasAccount(): void
    {
        GatewayAccount::create([
            'provider' => 'asaas',
            'mode' => 'sandbox',
            'credentials' => ['api_key' => 'key-123', 'webhook_token' => 'wh-secret'],
            'active' => true,
        ]);
    }

    private function activeOng(): Ong
    {
        return Ong::create(['name' => 'ONG Teste', 'active' => true]);
    }

    public function test_checkout_form_renders(): void
    {
        $ong = $this->activeOng();

        $this->get("/doar/{$ong->id}")->assertSuccessful()->assertSee('ONG Teste');
    }

    public function test_checkout_creates_pending_payment_and_redirects(): void
    {
        $this->activeAsaasAccount();
        $ong = $this->activeOng();

        Http::fake([
            '*/customers' => Http::response(['id' => 'cus_1'], 200),
            '*/payments/pay_1/pixQrCode' => Http::response([
                'payload' => '000201PIX',
                'encodedImage' => 'base64img',
            ], 200),
            '*/payments' => Http::response([
                'id' => 'pay_1',
                'status' => 'PENDING',
                'value' => 50.0,
            ], 200),
        ]);

        $response = $this->post("/doar/{$ong->id}", [
            'amount' => '50.00',
            'payer_name' => 'João',
        ]);

        $payment = Payment::first();
        $this->assertNotNull($payment);
        $this->assertSame(PaymentStatus::Pending->value, $payment->status);
        $this->assertSame(5000, $payment->amount_cents);
        $this->assertSame('pay_1', $payment->gateway_payment_id);
        $this->assertSame('000201PIX', $payment->pix_payload);

        $response->assertRedirect("/doar/{$ong->id}/pagamento/{$payment->id}");
    }

    public function test_webhook_marks_paid_and_creates_ledger_entry(): void
    {
        $this->activeAsaasAccount();
        $ong = $this->activeOng();

        $payment = Payment::create([
            'ong_id' => $ong->id,
            'gateway' => 'asaas',
            'gateway_payment_id' => 'pay_1',
            'method' => 'pix',
            'amount_cents' => 10000,
            'status' => 'pending',
            'period' => '2026-10',
        ]);

        $payload = [
            'id' => 'evt_1',
            'event' => 'PAYMENT_RECEIVED',
            'payment' => ['id' => 'pay_1', 'status' => 'RECEIVED', 'value' => 100.0],
        ];

        $this->withHeaders(['asaas-access-token' => 'wh-secret'])
            ->postJson('/webhooks/asaas', $payload)
            ->assertOk();

        $payment->refresh();
        $this->assertSame('paid', $payment->status);
        $this->assertSame(500, $payment->fee_cents);
        $this->assertSame(9500, $payment->net_cents);
        $this->assertNotNull($payment->paid_at);

        $ledger = LedgerEntry::where('ong_id', $ong->id)->first();
        $this->assertNotNull($ledger);
        $this->assertSame('credit', $ledger->type);
        $this->assertSame(9500, $ledger->amount_cents);
        $this->assertSame(9500, $ledger->balance_after_cents);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $this->activeAsaasAccount();

        $this->withHeaders(['asaas-access-token' => 'wrong'])
            ->postJson('/webhooks/asaas', ['id' => 'evt_1', 'event' => 'PAYMENT_RECEIVED'])
            ->assertStatus(401);
    }

    public function test_webhook_is_idempotent(): void
    {
        $this->activeAsaasAccount();
        $ong = $this->activeOng();

        Payment::create([
            'ong_id' => $ong->id,
            'gateway' => 'asaas',
            'gateway_payment_id' => 'pay_1',
            'method' => 'pix',
            'amount_cents' => 10000,
            'status' => 'pending',
            'period' => '2026-10',
        ]);

        $payload = [
            'id' => 'evt_1',
            'event' => 'PAYMENT_RECEIVED',
            'payment' => ['id' => 'pay_1', 'status' => 'RECEIVED', 'value' => 100.0],
        ];

        $headers = ['asaas-access-token' => 'wh-secret'];

        $this->withHeaders($headers)->postJson('/webhooks/asaas', $payload)->assertOk();
        $this->withHeaders($headers)->postJson('/webhooks/asaas', $payload)->assertOk();

        $this->assertSame(1, WebhookEvent::count());
        $this->assertSame(1, LedgerEntry::where('ong_id', $ong->id)->count());
    }
}
