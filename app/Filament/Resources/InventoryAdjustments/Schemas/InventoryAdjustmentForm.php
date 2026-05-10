<?php

namespace App\Filament\Resources\InventoryAdjustments\Schemas;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class InventoryAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Adjustment')
                    ->required()
                    ->placeholder('Contoh: Penyesuaian Stok Mei 2026')
                    ->columnSpanFull(),

                TextInput::make('reference')
                    ->label('Referensi')
                    ->placeholder('Contoh: ADJ/2026/001')
                    ->columnSpan(1),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->columnSpan(1),

                Repeater::make('details')
                    ->relationship()
                    ->label('Detail Produk')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(Product::where('is_active', true)->pluck('name', 'id'))
                            ->getSearchResultsUsing(fn(string $search) => Product::where('is_active', true)
                                ->where(
                                    fn($q) => $q
                                        ->where('name', 'like', "%{$search}%")
                                        ->orWhere('sku', 'like', "%{$search}%")
                                )
                                ->limit(20)
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->helperText(fn(Get $get) => ($p = Product::find($get('product_id')))
                                ? 'Stok saat ini: ' . $p->currentStock()
                                : null)
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->columnSpan(2),

                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'in'  => 'Penambahan (In)',
                                'out' => 'Pengurangan (Out)',
                            ])
                            ->required()
                            ->default('in'),

                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),

                        Textarea::make('notes')
                            ->label('Catatan Item')
                            ->rows(1)
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->addAction(fn(Action $action) => $action->label('Tambah Produk')->icon('heroicon-o-plus'))
                    ->columnSpanFull()
                    ->defaultItems(1)
                    ->minItems(1),
            ])->columns(2);
    }
}
