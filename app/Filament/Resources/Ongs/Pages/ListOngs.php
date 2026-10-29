<?php

namespace App\Filament\Resources\Ongs\Pages;

use App\Filament\Resources\Ongs\OngResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOngs extends ListRecords
{
    protected static string $resource = OngResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
