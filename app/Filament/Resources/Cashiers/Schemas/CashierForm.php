<?php

namespace App\Filament\Resources\Cashiers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CashierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->alphaDash(),
                Textarea::make('description')
                    ->maxLength(500)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->default(true)
                    ->label('Active'),
            ])->columns(2);
    }
}
