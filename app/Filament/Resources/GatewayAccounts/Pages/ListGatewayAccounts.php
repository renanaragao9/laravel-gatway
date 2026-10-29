<?php

namespace App\Filament\Resources\GatewayAccounts\Pages;

use App\Filament\Resources\GatewayAccounts\GatewayAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGatewayAccounts extends ListRecords
{
    protected static string $resource = GatewayAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
