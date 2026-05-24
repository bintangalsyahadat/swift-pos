<?php

namespace App\Filament\Widgets;

use App\Models\OrderDetail;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class BestSellingProductsWidget extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Produk Terlaris';

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderDetail::query()
                    ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
                    ->whereHas(
                        'order',
                        fn(Builder $q) => $q
                            ->where('status', 'completed')
                            ->where('payment_status', 'paid')
                    )
                    ->groupBy('product_id')
                    ->orderByDesc('total_qty')
                    ->limit(5)
            )
            ->filters([
                SelectFilter::make('period')
                    ->label('Periode')
                    ->options([
                        '7'  => '7 Hari Terakhir',
                        '30' => '30 Hari Terakhir',
                        '90' => '90 Hari Terakhir',
                    ])
                    ->query(fn(Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn($q, $v) => $q->whereHas(
                            'order',
                            fn($oq) => $oq
                                ->where('created_at', '>=', now()->subDays((int) $v)->startOfDay())
                        )
                    )),
            ])
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
                    ->formatStateUsing(fn($state) => \App\Models\Setting::currencySymbol() . ' ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
            ])
            ->paginated(false);
    }

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model|array $record): string
    {
        return (string) (is_array($record) ? $record['product_id'] : $record->product_id);
    }
}
