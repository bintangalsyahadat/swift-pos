<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Alamat Email')
                    ->email()
                    ->nullable(),
                Textarea::make('address')
                    ->label('Alamat')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
