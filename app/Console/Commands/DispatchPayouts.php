<?php

namespace App\Console\Commands;

use App\Services\Payments\PayoutService;
use Illuminate\Console\Command;

class DispatchPayouts extends Command
{
    protected $signature = 'payouts:dispatch';

    protected $description = 'Processa os repasses pendentes, enviando a transferência pelo gateway';

    public function handle(PayoutService $service): int
    {
        $this->info('Processando repasses pendentes...');

        $dispatched = $service->dispatchPending();

        $this->info("Repasses processados: {$dispatched}");

        return self::SUCCESS;
    }
}
