<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function generateSku(Get $get, Set $set): void
    {
        $brand = Brand::find($get('brand_id'));
        $category = Category::find($get('category_id'));
        $subCategory = SubCategory::find($get('sub_category_id'));

        if ($brand && $category && $subCategory) {
            // Generate SKU based on brand, category, and sub-category
            $catCode = strtoupper(substr($category->name, 0, 3));
            $subCatCode = strtoupper(substr($subCategory->name, 0, 3));
            $brandCode = strtoupper(substr($brand->name, 0, 3));

            $lastSku = Product::where('category_id', $category->id)
                ->where('sub_category_id', $subCategory->id)
                ->where('brand_id', $brand->id)
                ->orderBy('id', 'desc')
                ->value('sku');

            $nextNumber = 1;

            if ($lastSku) {
                $parts = explode('-', $lastSku);
                $lastNumber = intval(end($parts));
                $nextNumber = $lastNumber + 1;
            }

            $sku = sprintf('%s-%s-%s-%04d', $brandCode, $catCode, $subCatCode, $nextNumber);
            $set('sku', $sku);
        } else {
            // If any of the associations are missing, clear the SKU
            $set('sku', null);
        }
    }

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
                                ->unique()
                                ->default(null),
                            TextInput::make('barcode')
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
                            ->default(null)
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                static::generateSku($get, $set);
                            })
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                                FileUpload::make('logo'),
                            ]),
                        Select::make('category_id')
                            ->relationship('category', 'name', fn($query) => $query->where('is_active', true)->orderBy('name'))
                            ->label('Category ID')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $set('sub_category_id', null);
                                static::generateSku($get, $set);
                            })
                            ->default(null)
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                                FileUpload::make('logo'),
                            ]),
                        Select::make('sub_category_id')
                            ->label('Sub Category ID')
                            ->options(fn(Get $get) => SubCategory::query()
                                ->where('is_active', true)
                                ->when($get('category_id'), fn($q, $categoryId) => $q->where('category_id', $categoryId))
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->default(null)
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                static::generateSku($get, $set);
                            })
                            ->createOptionForm([
                                Select::make('category_id')
                                    ->relationship('category', 'name', fn($query) => $query->where('is_active', true)->orderBy('name'))
                                    ->label('Category')
                                    ->default(null),
                                TextInput::make('name')
                                    ->required(),
                                FileUpload::make('logo'),
                            ]),
                    ])->columnSpan(1)
            ])->columns(3);
    }
}
