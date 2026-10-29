<?php

namespace App\Services\Payments;

use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\PayoutItem;
use App\Services\Payments\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;

class PayoutService
{
    public function __construct(private PaymentGatewayManager $manager)
    {
    }

    public function closeMonth(string $period): int
    {
        $ongIds = Payment::query()
            ->where('status', PaymentStatus::Paid->value)
            ->where('period', $period)
            ->whereNull('payout_id')
            ->distinct()
            ->pluck('ong_id');

        $created = 0;

        foreach ($ongIds as $ongId) {
            $done = DB::transaction(function () use ($ongId, $period) {
                $payments = Payment::query()
                    ->where('ong_id', $ongId)
                    ->where('status', PaymentStatus::Paid->value)
                    ->where('period', $period)
                    ->whereNull('payout_id')
                    ->lockForUpdate()
                    ->get();

                if ($payments->isEmpty()) {
                    return false;
                }

                $gross = (int) $payments->sum('amount_cents');
                $fee = (int) $payments->sum('fee_cents');
                $net = (int) $payments->sum('net_cents');

                $payout = Payout::create([
                    'ong_id' => $ongId,
                    'period' => $period,
                    'gross_cents' => $gross,
                    'fee_cents' => $fee,
                    'net_cents' => $net,
                    'status' => 'pending',
                ]);

                foreach ($payments as $payment) {
                    PayoutItem::create([
                        'payout_id' => $payout->id,
                        'payment_id' => $payment->id,
                        'amount_cents' => $payment->net_cents,
                    ]);
                    $payment->update(['payout_id' => $payout->id]);
                }

                return true;
            });

            if ($done) {
                $created++;
            }
        }

        return $created;
    }

    public function dispatchPending(): int
    {
        $payouts = Payout::query()
            ->where('status', 'pending')
            ->get();

        $dispatched = 0;

        foreach ($payouts as $payout) {
            if ($this->dispatchOne($payout)) {
                $dispatched++;
            }
        }

        return $dispatched;
    }

    public function dispatchOne(Payout $payout): bool
    {
        return DB::transaction(function () use ($payout) {
            $fresh = Payout::whereKey($payout->id)->lockForUpdate()->first();

            if (! $fresh || $fresh->status !== 'pending') {
                return false;
            }

            $gateway = $this->manager->resolve();
            $result = $gateway->createTransfer($fresh);

            if (! $result->success) {
                $fresh->update(['status' => 'failed']);

                return false;
            }

            $fresh->update([
                'status' => 'paid',
                'gateway' => $gateway->provider(),
                'gateway_transfer_id' => $result->transferId,
                'paid_at' => now(),
            ]);

            $lastBalance = (int) LedgerEntry::where('ong_id', $fresh->ong_id)
                ->latest('id')
                ->value('balance_after_cents');

            LedgerEntry::create([
                'ong_id' => $fresh->ong_id,
                'type' => 'debit',
                'amount_cents' => $fresh->net_cents,
                'reference_type' => Payout::class,
                'reference_id' => $fresh->id,
                'balance_after_cents' => $lastBalance - $fresh->net_cents,
            ]);

            return true;
        });
    }
}
