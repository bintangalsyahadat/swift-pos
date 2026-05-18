<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OrderChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Pesanan per Hari';

    protected string $color = 'primary';

    public ?string $filter = '7';

    protected function getFilters(): ?array
    {
        return [
            '7'  => '7 Hari Terakhir',
            '30' => '30 Hari Terakhir',
            '90' => '90 Hari Terakhir',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? 7);

        $range = collect(range($days - 1, 0))->map(fn($i) => Carbon::today()->subDays($i));

        $orders = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_payment) as revenue')
            ->where('created_at', '>=', Carbon::today()->subDays($days - 1)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = $range->map(fn($date) => $date->format('M d'))->values()->toArray();
        $data   = $range->map(fn($date) => $orders[$date->toDateString()] ?? 0)->values()->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Pesanan',
                    'data'            => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor'     => 'rgba(59, 130, 246, 1)',
                    'borderWidth'     => 2,
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
