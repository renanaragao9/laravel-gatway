<?php

namespace App\Filament\Resources\GatewayAccounts\Pages;

use App\Filament\Resources\GatewayAccounts\GatewayAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGatewayAccount extends EditRecord
{
    protected static string $resource = GatewayAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
