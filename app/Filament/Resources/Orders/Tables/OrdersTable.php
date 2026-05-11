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
                    ->label('Order No.')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('customer.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cashier.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('discount')
                    ->suffix('%')
                    ->toggleable(),
                TextColumn::make('discount_amount')
                    ->money('IDR')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('total_payment')
                    ->money('IDR'),
                TextColumn::make('payment_method')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('payment_status')
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
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
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
            ]);
    }
}
