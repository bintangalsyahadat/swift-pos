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
                TextEntry::make('type')
                    ->label('Tipe Produk')
                    ->formatStateUsing(fn ($state) => $state === 'storable' ? 'Produk Stok (Barang Fisik)' : 'Produk Jasa (Non-Stok)'),
                TextEntry::make('description')
                    ->label('Deskripsi')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn ($state) => $state !== null ? 'Rp ' . number_format($state, 0, ',', '.') : '—'),
                TextEntry::make('current_stock')
                    ->label('Stok')
                    ->state(fn($record) => $record->currentStock() !== null ? strval($record->currentStock()) : '—')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->label('Dibuat Pada')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—')
                    ->placeholder('-'),
            ]);
    }
}
