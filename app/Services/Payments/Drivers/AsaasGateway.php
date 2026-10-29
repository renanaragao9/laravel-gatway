<?php

namespace App\Services\Payments\Drivers;

use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\Data\ChargeRequest;
use App\Services\Payments\Data\ChargeResult;
use App\Services\Payments\Data\TransferResult;
use App\Services\Payments\Data\WebhookResult;
use App\Services\Payments\Enums\PaymentStatus;
use App\Services\Payments\Exceptions\GatewayException;
use App\Models\Payout;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AsaasGateway implements PaymentGateway
{
    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private ?string $webhookToken = null,
        private string $mode = 'sandbox',
    ) {}

    public function provider(): string
    {
        return 'asaas';
    }

    public function createTransfer(Payout $payout): TransferResult
    {
        $ong = $payout->ong;

        if ($this->mode !== 'production') {
            return new TransferResult(
                success: true,
                transferId: 'sim_'.Str::uuid()->toString(),
                simulated: true,
                raw: ['note' => 'Transferência simulada em sandbox'],
            );
        }

        $response = $this->client()->post('/transfers', [
            'value' => round($payout->net_cents / 100, 2),
            'pixAddressKey' => $ong->pix_key,
            'operationType' => 'PIX',
            'description' => "Repasse {$payout->period} para {$ong->name}",
        ]);

        if ($response->failed()) {
            return new TransferResult(
                success: false,
                transferId: null,
                raw: $response->json() ?? ['body' => $response->body()],
            );
        }

        return new TransferResult(
            success: true,
            transferId: (string) $response->json('id'),
            raw: $response->json() ?? [],
        );
    }

    public function createPixCharge(ChargeRequest $request): ChargeResult
    {
        $customerId = $this->resolveCustomer($request);

        $response = $this->client()->post('/payments', [
            'customer' => $customerId,
            'billingType' => 'PIX',
            'value' => round($request->amountCents / 100, 2),
            'dueDate' => now()->format('Y-m-d'),
            'description' => $request->description,
            'externalReference' => $request->externalReference,
        ]);

        if ($response->failed()) {
            throw new GatewayException('Asaas: falha ao criar cobrança Pix: '.$response->body());
        }

        $payment = $response->json();
        $qr = $this->fetchPixQrCode($payment['id']);

        return new ChargeResult(
            gatewayPaymentId: (string) $payment['id'],
            status: $this->mapStatus($payment['status'] ?? 'PENDING'),
            amountCents: (int) round(((float) ($payment['value'] ?? 0)) * 100),
            pixPayload: $qr['payload'] ?? null,
            pixQrCodeBase64: $qr['encodedImage'] ?? null,
            invoiceUrl: $payment['invoiceUrl'] ?? null,
            raw: $payment,
        );
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        if ($this->webhookToken === null) {
            return true;
        }

        return hash_equals($this->webhookToken, (string) $request->header('asaas-access-token'));
    }

    public function parseWebhook(array $payload): WebhookResult
    {
        $payment = $payload['payment'] ?? [];

        return new WebhookResult(
            eventId: (string) ($payload['id'] ?? ($payment['id'] ?? '')),
            eventType: (string) ($payload['event'] ?? ''),
            gatewayPaymentId: isset($payment['id']) ? (string) $payment['id'] : null,
            status: $this->mapStatus($payment['status'] ?? ($payload['event'] ?? '')),
            amountCents: isset($payment['value'])
                ? (int) round(((float) $payment['value']) * 100)
                : null,
            raw: $payload,
        );
    }

    private function resolveCustomer(ChargeRequest $request): string
    {
        $response = $this->client()->post('/customers', [
            'name' => $request->payerName ?? 'Doador anônimo',
            'email' => $request->payerEmail,
            'cpfCnpj' => $request->payerDocument,
        ]);

        if ($response->failed()) {
            throw new GatewayException('Asaas: falha ao criar cliente: '.$response->body());
        }

        return (string) $response->json('id');
    }

    private function fetchPixQrCode(string $paymentId): array
    {
        $response = $this->client()->get("/payments/{$paymentId}/pixQrCode");

        if ($response->failed()) {
            return [];
        }

        return $response->json() ?? [];
    }

    private function mapStatus(string $status): PaymentStatus
    {
        return match (strtoupper($status)) {
            'RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH', 'PAYMENT_RECEIVED', 'PAYMENT_CONFIRMED' => PaymentStatus::Paid,
            'REFUNDED', 'PAYMENT_REFUNDED', 'CHARGEBACK_REQUESTED' => PaymentStatus::Refunded,
            'OVERDUE', 'PAYMENT_OVERDUE', 'FAILED', 'PAYMENT_DELETED' => PaymentStatus::Failed,
            default => PaymentStatus::Pending,
        };
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders(['access_token' => $this->apiKey])
            ->acceptJson()
            ->asJson();
    }
}
