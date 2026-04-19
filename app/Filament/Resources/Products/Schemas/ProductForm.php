<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\SubCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Section::make('Product Details')
                        ->schema([
                            TextInput::make('name')
                                ->required(),
                            TextInput::make('base_price')
                                ->required()
                                ->numeric()
                                ->prefix('IDR')
                                ->default(0),

                            TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->prefix('IDR'),
                            TextInput::make('stock')
                                ->required()
                                ->numeric()
                                ->default(0),
                            TextInput::make('sku')
                                ->required()
                                ->unique()
                                ->default(null),
                            TextInput::make('barcode')
                                ->required()
                                ->unique()
                                ->default(null),
                            Toggle::make('is_active')
                                ->label('Is Active')
                                ->default(true),
                            Toggle::make('in_stock')
                                ->label('In Stock')
                                ->default(false),
                            RichEditor::make('description')
                                ->default(null)
                                ->columnSpanFull(),

                        ])->columns(2),
                ])->columnSpan(2),

                Section::make('Associations')
                    ->schema([
                        FileUpload::make('image'),
                        Select::make('brand_id')
                            ->relationship('brand', 'name', fn($query) => $query->where('is_active', true)->orderBy('name'))
                            ->label('Brand ID')
                            ->default(null),
                        Select::make('category_id')
                            ->relationship('category', 'name', fn($query) => $query->where('is_active', true)->orderBy('name'))
                            ->label('Category ID')
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('sub_category_id', null))
                            ->default(null),
                        Select::make('sub_category_id')
                            ->label('Sub Category ID')
                            ->options(fn(Get $get) => SubCategory::query()
                                ->where('is_active', true)
                                ->when($get('category_id'), fn($q, $categoryId) => $q->where('category_id', $categoryId))
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->default(null),
                    ])->columnSpan(1)
            ])->columns(3);
    }
}
