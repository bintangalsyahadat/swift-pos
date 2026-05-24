<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\PaymentMethod;
use Filament\Widgets\ChartWidget;

class PaymentMethodChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Metode Pembayaran';

    protected string $color = 'info';

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

        $results = Order::query()
            ->where('status', 'completed')
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->whereNotNull('payment_method_id')
            ->selectRaw('payment_method_id, COUNT(*) as count')
            ->groupBy('payment_method_id')
            ->get();

        $paymentMethods = PaymentMethod::whereIn('id', $results->pluck('payment_method_id'))->pluck('name', 'id');

        $labels = $results->map(fn($r) => $paymentMethods[$r->payment_method_id] ?? 'Tidak Diketahui')->values()->toArray();
        $data   = $results->pluck('count')->values()->toArray();

        $backgroundColors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(20, 184, 166, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'label'           => 'Jumlah Transaksi',
                    'data'            => $data,
                    'backgroundColor' => array_slice($backgroundColors, 0, count($data)),
                    'borderWidth'     => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
