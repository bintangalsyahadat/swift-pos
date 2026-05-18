<?php

namespace App\Filament\Widgets;

use App\Models\OrderDetail;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class BestSellingProductsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Produk Terlaris';

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderDetail::query()
                    ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
                    ->groupBy('product_id')
                    ->orderByDesc('total_qty')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_qty')
                    ->label('Qty Terjual')
                    ->sortable(),
                TextColumn::make('total_revenue')
                    ->label('Pendapatan')
                    ->formatStateUsing(fn($state) => 'IDR ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
            ])
            ->paginated(false);
    }

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model|array $record): string
    {
        return (string) (is_array($record) ? $record['product_id'] : $record->product_id);
    }
}
