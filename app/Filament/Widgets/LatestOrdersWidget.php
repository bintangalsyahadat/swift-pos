<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'Pesanan Terbaru';

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with('customer')
                    ->latest()
            )
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->searchable(),
                TextColumn::make('total_payment')
                    ->label('Total')
                    ->formatStateUsing(fn($state) => \App\Models\Setting::currencySymbol() . ' ' . number_format($state, 0, ',', '.')),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending'   => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
