<?php

namespace App\Filament\Resources\GatewayAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GatewayAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider')
                    ->label('Provedor')
                    ->badge()
                    ->sortable(),

                TextColumn::make('mode')
                    ->label('Ambiente')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'production' ? 'danger' : 'gray')
                    ->sortable(),

                IconColumn::make('active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
