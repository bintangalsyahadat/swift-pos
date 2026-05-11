<?php

namespace App\Filament\Resources\PaymentMethods\Tables;

use App\Models\Setting;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PaymentMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('icon')
                    ->label('')
                    ->size(32)
                    ->circular(),

                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable()
                    ->weight('semibold'),

                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->copyable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'cash'    => 'Cash',
                        'card'    => 'Card',
                        'qr_code' => 'QR Code',
                        default   => ucfirst($state),
                    })
                    ->sortable(),

                IconColumn::make('is_online')
                    ->label('Xendit')
                    ->boolean()
                    ->trueIcon('heroicon-o-bolt')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->hidden(fn() => ! Setting::getBool('xendit.enabled')),

                TextColumn::make('xendit_channel_code')
                    ->label('Channel Code')
                    ->badge()
                    ->color('warning')
                    ->placeholder('—')
                    ->hidden(fn() => ! Setting::getBool('xendit.enabled')),

                TextColumn::make('fee_value')
                    ->label('Fee')
                    ->formatStateUsing(function ($record) {
                        if (! $record->fee_type || ! $record->fee_value) {
                            return '—';
                        }
                        return $record->fee_type === 'percentage'
                            ? $record->fee_value . '%'
                            : 'Rp ' . number_format($record->fee_value, 0, ',', '.');
                    })
                    ->color('danger'),

                ImageColumn::make('qr_image')
                    ->label('QR')
                    ->size(40)
                    ->square()
                    ->hidden(fn() => Setting::getBool('xendit.enabled')),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'cash'    => 'Cash',
                        'card'    => 'Card',
                        'qr_code' => 'QR Code',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
