<?php

namespace App\Filament\Resources\Payouts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PayoutsTable
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

                TextColumn::make('period')
                    ->label('Período')
                    ->sortable(),

                TextColumn::make('gross_cents')
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
                        'processing' => 'info',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('scheduled_for')
                    ->label('Agendado para')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('paid_at')
                    ->label('Pago em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('period', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'paid' => 'Pago',
                        'failed' => 'Falhou',
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
