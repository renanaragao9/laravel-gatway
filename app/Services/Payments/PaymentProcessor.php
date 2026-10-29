<?php

namespace App\Services\Payments;

use App\Models\LedgerEntry;
use App\Models\Ong;
use App\Models\Payment;
use App\Services\Payments\Data\ChargeRequest;
use App\Services\Payments\Data\ChargeResult;
use App\Services\Payments\Data\WebhookResult;
use App\Services\Payments\Enums\PaymentMethod;
use App\Services\Payments\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

class PaymentProcessor
{
    public function __construct(
        private PaymentGatewayManager $manager,
        private FeeCalculator $fees,
    ) {
    }

    public function createCharge(Ong $ong, int $amountCents, ?string $payerName = null, ?string $payerEmail = null): array
    {
        $gateway = $this->manager->resolve();

        $result = $gateway->createPixCharge(new ChargeRequest(
            amountCents: $amountCents,
            description: "Doação para {$ong->name}",
            method: PaymentMethod::Pix,
            payerName: $payerName,
            payerEmail: $payerEmail,
            externalReference: 'ong:'.$ong->id,
        ));

        $payment = Payment::create([
            'ong_id' => $ong->id,
            'gateway' => $gateway->provider(),
            'gateway_payment_id' => $result->gatewayPaymentId,
            'method' => PaymentMethod::Pix->value,
            'amount_cents' => $amountCents,
            'fee_cents' => 0,
            'net_cents' => 0,
            'status' => PaymentStatus::Pending->value,
            'period' => now()->format('Y-m'),
            'payer_name' => $payerName,
            'payer_email' => $payerEmail,
            'pix_payload' => $result->pixPayload,
            'pix_qr_base64' => $result->pixQrCodeBase64,
        ]);

        return [$payment, $result];
    }

    public function confirmFromWebhook(WebhookResult $event): ?Payment
    {
        if ($event->gatewayPaymentId === null || $event->status !== PaymentStatus::Paid) {
            return null;
        }

        return DB::transaction(function () use ($event) {
            $payment = Payment::where('gateway_payment_id', $event->gatewayPaymentId)
                ->lockForUpdate()
                ->first();

            if (! $payment || $payment->status === PaymentStatus::Paid->value) {
                return $payment;
            }

            $fee = $this->fees->feeFor($payment->amount_cents);
            $net = $payment->amount_cents - $fee;

            $payment->update([
                'status' => PaymentStatus::Paid->value,
                'fee_cents' => $fee,
                'net_cents' => $net,
                'paid_at' => now(),
            ]);

            $lastBalance = (int) LedgerEntry::where('ong_id', $payment->ong_id)
                ->latest('id')
                ->value('balance_after_cents');

            LedgerEntry::create([
                'ong_id' => $payment->ong_id,
                'type' => 'credit',
                'amount_cents' => $net,
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
                'balance_after_cents' => $lastBalance + $net,
            ]);

            return $payment;
        });
    }
}
