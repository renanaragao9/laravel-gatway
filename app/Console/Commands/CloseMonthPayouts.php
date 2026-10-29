<?php

namespace App\Console\Commands;

use App\Services\Payments\PayoutService;
use Illuminate\Console\Command;

class CloseMonthPayouts extends Command
{
    protected $signature = 'payouts:close-month {period? : Período no formato YYYY-MM (padrão: mês anterior)}';

    protected $description = 'Agrupa os pagamentos pagos do período em repasses pendentes por ONG';

    public function handle(PayoutService $service): int
    {
        $period = $this->argument('period') ?? now()->subMonthNoOverflow()->format('Y-m');

        $this->info("Fechando período {$period}...");

        $created = $service->closeMonth($period);

        $this->info("Repasses criados: {$created}");

        return self::SUCCESS;
    }
}
