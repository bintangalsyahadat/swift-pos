<?php

namespace App\Filament\Resources\SubCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('description')
                    ->label('Deskripsi')
                    ->default(null),
                FileUpload::make('image')
                    ->label('Gambar')
                    ->image(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
