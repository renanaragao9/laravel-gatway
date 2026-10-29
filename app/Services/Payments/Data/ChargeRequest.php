<?php

namespace App\Services\Payments\Data;

use App\Services\Payments\Enums\PaymentMethod;

final class ChargeRequest
{
    public function __construct(
        public int $amountCents,
        public string $description,
        public PaymentMethod $method = PaymentMethod::Pix,
        public ?string $payerName = null,
        public ?string $payerEmail = null,
        public ?string $payerDocument = null,
        public ?string $externalReference = null,
    ) {
    }
}
