<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class MonthlyRevenueChart extends ChartWidget
{
    protected ?string $heading = 'Arrecadação por mês';

    protected function getData(): array
    {
        $periods = collect(range(5, 0))
            ->map(fn (int $back): string => now()->subMonthsNoOverflow($back)->format('Y-m'));

        $totals = Payment::query()
            ->where('status', 'paid')
            ->whereIn('period', $periods->all())
            ->selectRaw('period, SUM(amount_cents) as total')
            ->groupBy('period')
            ->pluck('total', 'period');

        $data = $periods->map(fn (string $period): float => ((int) ($totals[$period] ?? 0)) / 100);

        return [
            'datasets' => [
                [
                    'label' => 'Arrecadado (R$)',
                    'data' => $data->values()->all(),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $periods->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
