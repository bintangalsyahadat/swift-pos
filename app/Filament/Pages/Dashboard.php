<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BestSellingProductsWidget;
use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\OrderChartWidget;
use App\Filament\Widgets\PaymentMethodChartWidget;
use App\Filament\Widgets\StatsWidget;
use Filament\Facades\Filament;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    public static function shouldRegisterNavigation(): bool
    {
        $widgets = [
            StatsWidget::class,
            OrderChartWidget::class,
            PaymentMethodChartWidget::class,
            BestSellingProductsWidget::class,
            LatestOrdersWidget::class,
        ];

        foreach ($widgets as $widget) {
            if ($widget::canView()) {
                return true;
            }
        }

        return false;
    }

    public function mount(): void
    {
        if (!static::shouldRegisterNavigation()) {
            $firstUrl = collect(Filament::getNavigation())
                ->flatMap(fn($group) => method_exists($group, 'getItems') ? $group->getItems() : [$group])
                ->first(fn($item) => method_exists($item, 'getUrl') && $item->getUrl());

            $this->redirect($firstUrl?->getUrl() ?? Filament::getUrl());
        }
    }
}
