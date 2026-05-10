<?php

namespace App\Filament\Resources\InventoryAdjustments\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryAdjustmentDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    protected static ?string $title = 'Detail Produk';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state) => $state === 'in' ? 'success' : 'danger')
                    ->formatStateUsing(fn(string $state) => $state === 'in' ? 'Penambahan (In)' : 'Pengurangan (Out)'),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->placeholder('-'),
            ]);
    }
}
