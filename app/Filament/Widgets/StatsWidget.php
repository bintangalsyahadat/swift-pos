<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Pesanan', Order::count())
                ->description('Jumlah pesanan yang masuk')
                ->descriptionIcon('heroicon-o-shopping-cart', IconPosition::Before)
                ->chart([10, 15, 20, 25, 30, 35])
                ->color('primary'),

            Stat::make('Pendapatan', \App\Models\Setting::currencySymbol() . ' ' . number_format(Order::sum('total_payment'), 2))
                ->description('Total pendapatan yang dihasilkan')
                ->descriptionIcon('heroicon-o-currency-dollar', IconPosition::Before)
                ->chart([1000, 1500, 2000, 2500, 3000, 3500])
                ->color('success'),

            Stat::make('Rata-rata Nilai Pesanan', \App\Models\Setting::currencySymbol() . ' ' . number_format(Order::avg('total_payment'), 2))
                ->description('Rata-rata nilai setiap pesanan')
                ->descriptionIcon('heroicon-o-currency-dollar', IconPosition::Before)
                ->chart([500, 750, 1000, 1250, 1500, 1750])
                ->color('warning'),
        ];
    }
}
