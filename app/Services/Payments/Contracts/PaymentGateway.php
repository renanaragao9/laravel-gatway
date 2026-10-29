<?php

namespace App\Services\Payments\Contracts;

use App\Services\Payments\Data\ChargeRequest;
use App\Services\Payments\Data\ChargeResult;
use App\Services\Payments\Data\TransferResult;
use App\Services\Payments\Data\WebhookResult;
use App\Models\Payout;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function provider(): string;

    public function createPixCharge(ChargeRequest $request): ChargeResult;

    public function verifyWebhookSignature(Request $request): bool;

    public function parseWebhook(array $payload): WebhookResult;

    public function createTransfer(Payout $payout): TransferResult;
}
