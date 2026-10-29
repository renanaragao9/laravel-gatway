<?php

namespace App\Services\Payments\Data;

final class TransferResult
{
    public function __construct(
        public bool $success,
        public ?string $transferId,
        public bool $simulated = false,
        public array $raw = [],
    ) {
    }
}
