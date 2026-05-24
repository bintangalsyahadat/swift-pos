<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('order_number')
                ->label('No. Pesanan'),
            ExportColumn::make('cashier_id')
                ->label('ID Kasir'),
            ExportColumn::make('customer_id')
                ->label('ID Pelanggan'),
            ExportColumn::make('order_date')
                ->label('Tanggal Pesanan'),
            ExportColumn::make('total_price')
                ->label('Total Harga'),
            ExportColumn::make('discount')
                ->label('Diskon'),
            ExportColumn::make('discount_amount')
                ->label('Jumlah Diskon'),
            ExportColumn::make('total_payment')
                ->label('Total Pembayaran'),
            ExportColumn::make('payment_method')
                ->label('Metode Pembayaran'),
            ExportColumn::make('payment_status')
                ->label('Status Pembayaran'),
            ExportColumn::make('cash_paid')
                ->label('Uang Diterima'),
            ExportColumn::make('change_amount')
                ->label('Kembalian'),
            ExportColumn::make('status')
                ->label('Status'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor pesanan selesai dan ' . Number::format($export->successful_rows) . ' baris berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
