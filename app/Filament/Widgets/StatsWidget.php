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
            Stat::make('Total Orders', Order::count())
                ->description('Number of orders placed')
                ->descriptionIcon('heroicon-o-shopping-cart', IconPosition::Before)
                ->chart([10, 15, 20, 25, 30, 35])
                ->color('primary'),

            Stat::make('Revenue', 'IDR ' . number_format(Order::sum('total_payment'), 2))
                ->description('Total revenue generated')
                ->descriptionIcon('heroicon-o-currency-dollar', IconPosition::Before)
                ->chart([1000, 1500, 2000, 2500, 3000, 3500])
                ->color('success'),

            Stat::make('Average Order Value', 'IDR ' . number_format(Order::avg('total_payment'), 2))
                ->description('Average value of each order')
                ->descriptionIcon('heroicon-o-currency-dollar', IconPosition::Before)
                ->chart([500, 750, 1000, 1250, 1500, 1750])
                ->color('warning'),
        ];
    }
}
