<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('description')
                    ->default(null),
                FileUpload::make('image')
                    ->image()
                    ->maxSize(2048)
                    ->directory('Products/Brands'),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
