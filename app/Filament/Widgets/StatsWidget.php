<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderDetail;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsWidget extends StatsOverviewWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayPaid = Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->where('payment_status', 'paid');

        return [
            Stat::make('Total Pesanan', (clone $todayPaid)->count())
                ->description('Jumlah pesanan hari ini')
                ->descriptionIcon('heroicon-o-shopping-cart', IconPosition::Before)
                ->chart([10, 15, 20, 25, 30, 35])
                ->color('primary'),

            Stat::make('Pendapatan', \App\Models\Setting::currencySymbol() . ' ' . number_format((clone $todayPaid)->sum('total_payment'), 2))
                ->description('Total pendapatan hari ini')
                ->descriptionIcon('heroicon-o-currency-dollar', IconPosition::Before)
                ->chart([1000, 1500, 2000, 2500, 3000, 3500])
                ->color('success'),

            Stat::make('Produk Terjual', OrderDetail::whereHas('order', function ($q) {
                $q->whereDate('created_at', today())
                    ->where('status', 'completed')
                    ->where('payment_status', 'paid');
            })->sum('quantity'))
                ->description('Total produk terjual hari ini')
                ->descriptionIcon('heroicon-o-cube', IconPosition::Before)
                ->chart([500, 750, 1000, 1250, 1500, 1750])
                ->color('warning'),
        ];
    }
}
