<?php

namespace App\Filament\Resources\InventoryAdjustments\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InventoryAdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reference')
                    ->label('Referensi')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state) => $state === 'done' ? 'success' : 'warning')
                    ->formatStateUsing(fn(string $state) => ucfirst($state)),
                TextColumn::make('details_count')
                    ->label('Jml Produk')
                    ->counts('details')
                    ->alignCenter(),
                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'done'  => 'Selesai',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn($record) => $record->status === 'draft'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
