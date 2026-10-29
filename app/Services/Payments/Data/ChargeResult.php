<?php

namespace App\Services\Payments\Data;

use App\Services\Payments\Enums\PaymentStatus;

final class ChargeResult
{
    public function __construct(
        public string $gatewayPaymentId,
        public PaymentStatus $status,
        public int $amountCents,
        public ?string $pixPayload = null,
        public ?string $pixQrCodeBase64 = null,
        public ?string $invoiceUrl = null,
        public array $raw = [],
    ) {
    }
}
