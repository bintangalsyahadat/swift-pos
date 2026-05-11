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
                    ->label('Code')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Cashier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->label('Opened')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('Closed')
                    ->dateTime('d M Y, H:i')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('opening_balance')
                    ->label('Opening')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('expected_balance')
                    ->label('Expected')
                    ->money('IDR')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('actual_balance')
                    ->label('Actual')
                    ->money('IDR')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('difference_amount')
                    ->label('Difference')
                    ->formatStateUsing(
                        fn($state) => $state !== null
                            ? ($state >= 0 ? '+' : '') . 'IDR ' . number_format($state, 0, ',', '.')
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
                    ->label('Orders')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('has_discrepancy')
                    ->label('Has Discrepancy')
                    ->query(fn(Builder $query) => $query->whereNotNull('difference_amount')
                        ->where('difference_amount', '!=', 0))
                    ->toggle(),
                Filter::make('open')
                    ->label('Currently Open')
                    ->query(fn(Builder $query) => $query->where('state', 'open'))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
