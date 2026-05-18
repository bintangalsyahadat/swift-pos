<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class CustomerChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Pelanggan Baru per Hari';

    protected string $color = 'success';

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

        $customers = Customer::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::today()->subDays($days - 1)->startOfDay())
            ->groupBy('date')
            ->pluck('count', 'date');

        $labels = $range->map(fn($date) => $date->format('M d'))->values()->toArray();
        $data   = $range->map(fn($date) => $customers[$date->toDateString()] ?? 0)->values()->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Pelanggan Baru',
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
