<?php

namespace App\Filament\Resources\Ongs;

use App\Filament\Resources\Ongs\Pages\CreateOng;
use App\Filament\Resources\Ongs\Pages\EditOng;
use App\Filament\Resources\Ongs\Pages\ListOngs;
use App\Filament\Resources\Ongs\Schemas\OngForm;
use App\Filament\Resources\Ongs\Tables\OngsTable;
use App\Models\Ong;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OngResource extends Resource
{
    protected static ?string $model = Ong::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'ONG';

    protected static ?string $pluralModelLabel = 'ONGs';

    protected static ?string $navigationLabel = 'ONGs';

    public static function form(Schema $schema): Schema
    {
        return OngForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OngsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOngs::route('/'),
            'create' => CreateOng::route('/create'),
            'edit' => EditOng::route('/{record}/edit'),
        ];
    }
}
