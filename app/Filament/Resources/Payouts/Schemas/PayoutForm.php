<?php

namespace App\Filament\Resources\Payouts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ong_id')
                    ->label('ONG')
                    ->relationship('ong', 'name')
                    ->disabled(),

                TextInput::make('period')
                    ->label('Período')
                    ->disabled(),

                TextInput::make('gross_cents')
                    ->label('Bruto (centavos)')
                    ->numeric()
                    ->disabled(),

                TextInput::make('fee_cents')
                    ->label('Taxa (centavos)')
                    ->numeric()
                    ->disabled(),

                TextInput::make('net_cents')
                    ->label('Líquido (centavos)')
                    ->numeric()
                    ->disabled(),

                TextInput::make('status')
                    ->label('Status')
                    ->disabled(),

                TextInput::make('gateway')
                    ->label('Gateway')
                    ->disabled(),

                TextInput::make('gateway_transfer_id')
                    ->label('ID da transferência')
                    ->disabled(),
            ]);
    }
}
