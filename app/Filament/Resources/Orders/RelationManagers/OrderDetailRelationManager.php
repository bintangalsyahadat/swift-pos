<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'orderDetails';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('product.image')
                    ->label('Gambar'),
                TextColumn::make('product.name'),
                TextColumn::make('product.price')
                    ->label('Harga Satuan')
                    ->money('IDR'),
                TextColumn::make('quantity'),
                TextColumn::make('subtotal')
                    ->money('IDR'),
            ])
            ->headerActions([
                // CreateAction::make(),
            ]);
    }
}
