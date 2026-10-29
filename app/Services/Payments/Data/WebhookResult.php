<?php

namespace App\Services\Payments\Data;

use App\Services\Payments\Enums\PaymentStatus;

final class WebhookResult
{
    public function __construct(
        public string $eventId,
        public string $eventType,
        public ?string $gatewayPaymentId,
        public PaymentStatus $status,
        public ?int $amountCents = null,
        public array $raw = [],
    ) {
    }
}
