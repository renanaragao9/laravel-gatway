<?php

namespace Tests\Feature;

use App\Models\GatewayAccount;
use App\Services\Payments\Data\ChargeRequest;
use App\Services\Payments\Drivers\AsaasGateway;
use App\Services\Payments\Enums\PaymentStatus;
use App\Services\Payments\Exceptions\GatewayException;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AsaasGatewayTest extends TestCase
{
    use RefreshDatabase;

    private function gateway(): AsaasGateway
    {
        return new AsaasGateway(
            apiKey: 'key-test',
            baseUrl: 'https://api-sandbox.asaas.com/v3',
            webhookToken: 'wh-secret',
        );
    }

    public function test_create_pix_charge_returns_qr_code(): void
    {
        Http::fake([
            '*/customers' => Http::response(['id' => 'cus_1'], 200),
            '*/payments/pay_1/pixQrCode' => Http::response([
                'payload' => '000201PIXCOPIACOLA',
                'encodedImage' => 'base64img',
            ], 200),
            '*/payments' => Http::response([
                'id' => 'pay_1',
                'status' => 'PENDING',
                'value' => 100.0,
                'invoiceUrl' => 'https://asaas/invoice/pay_1',
            ], 200),
        ]);

        $result = $this->gateway()->createPixCharge(new ChargeRequest(
            amountCents: 10000,
            description: 'Doação',
        ));

        $this->assertSame('pay_1', $result->gatewayPaymentId);
        $this->assertSame(PaymentStatus::Pending, $result->status);
        $this->assertSame(10000, $result->amountCents);
        $this->assertSame('000201PIXCOPIACOLA', $result->pixPayload);
        $this->assertSame('base64img', $result->pixQrCodeBase64);
    }

    public function test_create_pix_charge_throws_on_api_error(): void
    {
        Http::fake([
            '*/customers' => Http::response(['id' => 'cus_1'], 200),
            '*/payments' => Http::response(['errors' => [['description' => 'invalid']]], 400),
        ]);

        $this->expectException(GatewayException::class);

        $this->gateway()->createPixCharge(new ChargeRequest(
            amountCents: 10000,
            description: 'Doação',
        ));
    }

    public function test_parse_webhook_maps_received_to_paid(): void
    {
        $result = $this->gateway()->parseWebhook([
            'id' => 'evt_1',
            'event' => 'PAYMENT_RECEIVED',
            'payment' => ['id' => 'pay_1', 'status' => 'RECEIVED', 'value' => 100.0],
        ]);

        $this->assertSame('evt_1', $result->eventId);
        $this->assertSame('pay_1', $result->gatewayPaymentId);
        $this->assertSame(PaymentStatus::Paid, $result->status);
        $this->assertSame(10000, $result->amountCents);
    }

    public function test_verify_webhook_signature(): void
    {
        $valid = Request::create('/webhooks/asaas', 'POST', server: ['HTTP_ASAAS_ACCESS_TOKEN' => 'wh-secret']);
        $invalid = Request::create('/webhooks/asaas', 'POST', server: ['HTTP_ASAAS_ACCESS_TOKEN' => 'wrong']);

        $this->assertTrue($this->gateway()->verifyWebhookSignature($valid));
        $this->assertFalse($this->gateway()->verifyWebhookSignature($invalid));
    }

    public function test_manager_resolves_active_asaas_account(): void
    {
        GatewayAccount::create([
            'provider' => 'asaas',
            'mode' => 'sandbox',
            'credentials' => ['api_key' => 'key-123'],
            'active' => true,
        ]);

        $gateway = app(PaymentGatewayManager::class)->resolve('asaas');

        $this->assertInstanceOf(AsaasGateway::class, $gateway);
        $this->assertSame('asaas', $gateway->provider());
    }

    public function test_manager_throws_when_no_active_account(): void
    {
        $this->expectException(GatewayException::class);

        app(PaymentGatewayManager::class)->resolve('asaas');
    }

    public function test_manager_throws_when_api_key_missing(): void
    {
        $account = GatewayAccount::create([
            'provider' => 'asaas',
            'mode' => 'sandbox',
            'credentials' => ['webhook_token' => 'x'],
            'active' => true,
        ]);

        $this->expectException(GatewayException::class);

        app(PaymentGatewayManager::class)->make($account);
    }
}
