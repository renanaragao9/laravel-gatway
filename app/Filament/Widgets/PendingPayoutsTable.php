<?php

namespace App\Filament\Widgets;

use App\Models\Payout;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingPayoutsTable extends TableWidget
{
    protected static ?string $heading = 'Repasses pendentes';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Payout::query()->where('status', 'pending')->with('ong'))
            ->columns([
                TextColumn::make('ong.name')
                    ->label('ONG')
                    ->searchable(),
                TextColumn::make('period')
                    ->label('Período')
                    ->sortable(),
                TextColumn::make('net_cents')
                    ->label('Valor a repassar')
                    ->money('BRL', divideBy: 100)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('period', 'desc');
    }
}
