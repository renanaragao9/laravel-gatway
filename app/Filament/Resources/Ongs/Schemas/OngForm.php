<?php

namespace App\Filament\Resources\Ongs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OngForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da ONG')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('document')
                            ->label('CNPJ')
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(255),

                        TextInput::make('city')
                            ->label('Cidade')
                            ->maxLength(255),

                        Toggle::make('active')
                            ->label('Ativo')
                            ->default(true),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('Dados de repasse')
                    ->description('Usados no repasse mensal para a ONG.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('pix_key')
                            ->label('Chave Pix')
                            ->maxLength(255),

                        Select::make('account_type')
                            ->label('Tipo de conta')
                            ->options([
                                'checking' => 'Corrente',
                                'savings' => 'Poupança',
                            ]),

                        TextInput::make('bank_name')
                            ->label('Banco')
                            ->maxLength(255),

                        TextInput::make('bank_agency')
                            ->label('Agência')
                            ->maxLength(255),

                        TextInput::make('bank_account')
                            ->label('Conta')
                            ->maxLength(255),
                    ]),
            ]);
    }
}
