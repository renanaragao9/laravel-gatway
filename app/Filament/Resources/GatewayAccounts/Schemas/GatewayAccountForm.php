<?php

namespace App\Filament\Resources\GatewayAccounts\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GatewayAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('provider')
                    ->label('Provedor')
                    ->options([
                        'asaas' => 'Asaas',
                        'mercadopago' => 'Mercado Pago',
                    ])
                    ->required(),

                Select::make('mode')
                    ->label('Ambiente')
                    ->options([
                        'sandbox' => 'Sandbox',
                        'production' => 'Produção',
                    ])
                    ->default('sandbox')
                    ->required(),

                KeyValue::make('credentials')
                    ->label('Credenciais')
                    ->keyLabel('Chave')
                    ->valueLabel('Valor')
                    ->helperText('Ex.: api_key, webhook_token. Os valores são armazenados criptografados.')
                    ->columnSpanFull(),

                Toggle::make('active')
                    ->label('Ativo')
                    ->default(false),
            ]);
    }
}
