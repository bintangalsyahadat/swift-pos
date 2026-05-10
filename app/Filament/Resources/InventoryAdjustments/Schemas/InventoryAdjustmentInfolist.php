<?php

namespace App\Filament\Resources\InventoryAdjustments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventoryAdjustmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Adjustment')
                    ->schema([
                        TextEntry::make('name')->label('Nama'),
                        TextEntry::make('reference')->label('Referensi')->placeholder('-'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state) => $state === 'done' ? 'success' : 'warning')
                            ->formatStateUsing(fn(string $state) => ucfirst($state)),
                        TextEntry::make('user.name')->label('Dibuat Oleh'),
                        TextEntry::make('notes')->label('Catatan')->columnSpanFull()->placeholder('-'),
                        TextEntry::make('created_at')->label('Tanggal')->dateTime('d M Y, H:i'),
                    ])->columnSpanFull(),
            ]);
    }
}
