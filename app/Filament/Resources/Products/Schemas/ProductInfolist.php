<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nama'),
                TextEntry::make('description')
                    ->label('Deskripsi')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('price')
                    ->label('Harga')
                    ->money('IDR'),
                TextEntry::make('current_stock')
                    ->label('Stok')
                    ->state(fn($record) => $record->currentStock())
                    ->numeric(),
                TextEntry::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
