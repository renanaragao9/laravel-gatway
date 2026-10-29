<?php

namespace App\Filament\Resources\GatewayAccounts;

use App\Filament\Resources\GatewayAccounts\Pages\CreateGatewayAccount;
use App\Filament\Resources\GatewayAccounts\Pages\EditGatewayAccount;
use App\Filament\Resources\GatewayAccounts\Pages\ListGatewayAccounts;
use App\Filament\Resources\GatewayAccounts\Schemas\GatewayAccountForm;
use App\Filament\Resources\GatewayAccounts\Tables\GatewayAccountsTable;
use App\Models\GatewayAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GatewayAccountResource extends Resource
{
    protected static ?string $model = GatewayAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Contas de Gateway';

    protected static ?string $modelLabel = 'Conta de Gateway';

    protected static ?string $pluralModelLabel = 'Contas de Gateway';

    protected static string|null|\UnitEnum $navigationGroup = 'Gateway';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return GatewayAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GatewayAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGatewayAccounts::route('/'),
            'create' => CreateGatewayAccount::route('/create'),
            'edit' => EditGatewayAccount::route('/{record}/edit'),
        ];
    }
}
