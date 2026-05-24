<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Exports\OrderExporter;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cashier.name')
                    ->label('Kasir')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('order_date')
                    ->label('Tanggal Pesanan')
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y') : '—')
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label('Total Harga')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                TextColumn::make('discount')
                    ->label('Diskon')
                    ->suffix('%')
                    ->toggleable(),
                TextColumn::make('discount_amount')
                    ->label('Jumlah Diskon')
                    ->money('IDR', locale: 'id_ID')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('total_payment')
                    ->label('Total Pembayaran')
                    ->money('IDR', locale: 'id_ID'),
                TextColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->sortable()
                    ->toggleable()
                    ->color(fn($state) => match ($state) {
                        'unpaid' => 'info',
                        'paid' => 'success',
                        'failed' => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn($state) => match ($state) {
                        'new' => 'info',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ]),
            ])
            ->headerActions([
                ExportAction::make()->exporter(OrderExporter::class)
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
