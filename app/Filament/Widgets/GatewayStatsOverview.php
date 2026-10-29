<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Payout;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GatewayStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $grossPaid = (int) Payment::where('status', 'paid')->sum('amount_cents');
        $feeTotal = (int) Payment::where('status', 'paid')->sum('fee_cents');
        $paidOut = (int) Payout::where('status', 'paid')->sum('net_cents');
        $pendingPayouts = (int) Payout::where('status', 'pending')->sum('net_cents');
        $pendingCount = Payout::where('status', 'pending')->count();

        return [
            Stat::make('Arrecadado (bruto)', $this->money($grossPaid))
                ->description('Pagamentos confirmados')
                ->color('success'),
            Stat::make('Taxas da plataforma', $this->money($feeTotal))
                ->color('gray'),
            Stat::make('Repassado às ONGs', $this->money($paidOut))
                ->color('info'),
            Stat::make('Repasses pendentes', $this->money($pendingPayouts))
                ->description("{$pendingCount} repasse(s) a processar")
                ->color('warning'),
        ];
    }

    private function money(int $cents): string
    {
        return 'R$ '.number_format($cents / 100, 2, ',', '.');
    }
}
