<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CustomerChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'New Customers per Day';

    protected string $color = 'success';

    public ?string $filter = '7';

    protected function getFilters(): ?array
    {
        return [
            '7'  => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
        ];
    }

    protected function getData(): array
    {
        $days = (int) ($this->filter ?? 7);

        $range = collect(range($days - 1, 0))->map(fn($i) => Carbon::today()->subDays($i));

        $customers = Customer::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::today()->subDays($days - 1)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = $range->map(fn($date) => $date->format('M d'))->values()->toArray();
        $data   = $range->map(fn($date) => $customers[$date->toDateString()] ?? 0)->values()->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'New Customers',
                    'data'            => $data,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'borderColor'     => 'rgba(34, 197, 94, 1)',
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
