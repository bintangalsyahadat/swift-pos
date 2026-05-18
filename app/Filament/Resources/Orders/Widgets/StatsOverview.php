<?php

namespace App\Filament\Resources\Orders\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pesanan Baru', Order::where('status', 'new')->count())
                ->description('Pesanan baru menunggu diproses')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),

            Stat::make('Pesanan Diproses', Order::where('status', 'processing')->count())
                ->description('Pesanan sedang diproses')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),

            Stat::make('Pesanan Selesai', Order::where('status', 'completed')->count())
                ->description('Pesanan berhasil diselesaikan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('Total Pendapatan', \App\Models\Setting::currencySymbol() . ' ' . number_format(Order::where('status', 'completed')->sum('total_payment'), 0))
                ->description('Total pembayaran dari pesanan selesai')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),
        ];
    }
}
