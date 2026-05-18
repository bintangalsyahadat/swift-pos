<?php

namespace App\Filament\Resources\StockMoves\Tables;

use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockMovesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Referensi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state) => $state === 'in' ? 'success' : 'danger')
                    ->formatStateUsing(fn(string $state) => $state === 'in' ? 'In' : 'Out'),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('state')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'done'      => 'success',
                        'draft'     => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => ucfirst($state)),
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->placeholder('-')
                    ->limit(40),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'in'  => 'Masuk',
                        'out' => 'Keluar',
                    ]),
                SelectFilter::make('state')
                    ->label('Status')
                    ->options([
                        'draft'     => 'Draft',
                        'done'      => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
