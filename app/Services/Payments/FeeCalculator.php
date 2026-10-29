<?php

namespace App\Services\Payments;

class FeeCalculator
{
    public function feeFor(int $amountCents): int
    {
        $percent = (float) config('payments.fee.percent', 0);
        $fixed = (int) config('payments.fee.fixed_cents', 0);

        $percentPart = (int) round($amountCents * ($percent / 100));

        return $percentPart + $fixed;
    }
}
