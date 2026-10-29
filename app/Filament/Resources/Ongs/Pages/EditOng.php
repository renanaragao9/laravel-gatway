<?php

namespace App\Filament\Resources\Ongs\Pages;

use App\Filament\Resources\Ongs\OngResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOng extends EditRecord
{
    protected static string $resource = OngResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
