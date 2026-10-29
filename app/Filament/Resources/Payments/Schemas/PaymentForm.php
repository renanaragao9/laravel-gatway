<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ong_id')
                    ->label('ONG')
                    ->relationship('ong', 'name')
                    ->disabled(),

                TextInput::make('gateway')
                    ->label('Gateway')
                    ->disabled(),

                TextInput::make('gateway_payment_id')
                    ->label('ID no gateway')
                    ->disabled(),

                TextInput::make('method')
                    ->label('Método')
                    ->disabled(),

                TextInput::make('status')
                    ->label('Status')
                    ->disabled(),

                TextInput::make('amount_cents')
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

                TextInput::make('period')
                    ->label('Período')
                    ->disabled(),

                TextInput::make('payer_name')
                    ->label('Pagador')
                    ->disabled(),

                TextInput::make('payer_email')
                    ->label('E-mail do pagador')
                    ->disabled(),
            ]);
    }
}
