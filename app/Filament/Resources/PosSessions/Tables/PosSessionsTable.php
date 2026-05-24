<?php

namespace App\Filament\Resources\PosSessions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PosSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('opened_at', 'desc')
            ->columns([
                TextColumn::make('terminal.name')
                    ->label('Terminal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('terminal.code')
                    ->label('Kode')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->label('Dibuka')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('Ditutup')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('opening_balance')
                    ->label('Saldo Awal')
                    ->money('IDR', locale: 'id_ID')
                    ->sortable(),
                TextColumn::make('expected_balance')
                    ->label('Perkiraan')
                    ->money('IDR', locale: 'id_ID')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('actual_balance')
                    ->label('Aktual')
                    ->money('IDR', locale: 'id_ID')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('difference_amount')
                    ->label('Selisih')
                    ->formatStateUsing(
                        fn($state) => $state !== null
                            ? ($state >= 0 ? '+' : '') . \App\Models\Setting::currencySymbol() . ' ' . number_format($state, 0, ',', '.')
                            : '—'
                    )
                    ->color(fn($state) => match (true) {
                        $state === null   => 'gray',
                        $state > 0        => 'warning',
                        $state < 0        => 'danger',
                        default           => 'success',
                    })
                    ->sortable(),
                TextColumn::make('state')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'open'   => 'success',
                        'closed' => 'gray',
                        default  => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Pesanan')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('has_discrepancy')
                    ->label('Ada Selisih')
                    ->query(fn(Builder $query) => $query->whereNotNull('difference_amount')
                        ->where('difference_amount', '!=', 0))
                    ->toggle(),
                Filter::make('open')
                    ->label('Sedang Dibuka')
                    ->query(fn(Builder $query) => $query->where('state', 'open'))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
