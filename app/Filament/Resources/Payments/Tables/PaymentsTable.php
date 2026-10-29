<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('ong.name')
                    ->label('ONG')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('gateway')
                    ->label('Gateway')
                    ->badge()
                    ->sortable(),

                TextColumn::make('method')
                    ->label('Método')
                    ->badge(),

                TextColumn::make('amount_cents')
                    ->label('Bruto')
                    ->money('BRL', divideBy: 100)
                    ->sortable(),

                TextColumn::make('fee_cents')
                    ->label('Taxa')
                    ->money('BRL', divideBy: 100),

                TextColumn::make('net_cents')
                    ->label('Líquido')
                    ->money('BRL', divideBy: 100)
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('period')
                    ->label('Período')
                    ->sortable(),

                TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'paid' => 'Pago',
                        'failed' => 'Falhou',
                        'refunded' => 'Estornado',
                    ]),

                SelectFilter::make('ong_id')
                    ->label('ONG')
                    ->relationship('ong', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
