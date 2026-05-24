<?php

namespace App\Filament\Resources\PosSessions\Pages;

use App\Filament\Resources\PosSessions\PosSessionResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewPosSession extends ViewRecord
{
    protected static string $resource = PosSessionResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Info Sesi')
                ->columns(3)
                ->schema([
                    TextEntry::make('terminal.name')->label('Terminal'),
                    TextEntry::make('terminal.code')->label('Kode Terminal'),
                    TextEntry::make('state')
                        ->badge()
                        ->color(fn($state) => match ($state) {
                            'open'   => 'success',
                            'closed' => 'gray',
                            default  => 'secondary',
                        }),
                    TextEntry::make('user.name')->label('Kasir'),
                    TextEntry::make('opened_at')->label('Dibuka Pada')->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—'),
                    TextEntry::make('closed_at')->label('Ditutup Pada')->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—')->placeholder('Masih buka'),
                ]),

            Section::make('Ringkasan Keuangan')
                ->columns(2)
                ->schema([
                    TextEntry::make('opening_balance')->label('Saldo Awal')->formatStateUsing(fn ($state) => $state !== null ? 'Rp ' . number_format($state, 0, ',', '.') : '—'),
                    TextEntry::make('expected_balance')->label('Perkiraan Saldo')->formatStateUsing(fn ($state) => $state !== null ? 'Rp ' . number_format($state, 0, ',', '.') : '—')->placeholder('—'),
                    TextEntry::make('actual_balance')->label('Saldo Aktual')->formatStateUsing(fn ($state) => $state !== null ? 'Rp ' . number_format($state, 0, ',', '.') : '—')->placeholder('—'),
                    TextEntry::make('difference_amount')
                        ->label('Selisih')
                        ->formatStateUsing(
                            fn($state) => $state !== null
                                ? ($state >= 0 ? '+' : '') . \App\Models\Setting::currencySymbol() . ' ' . number_format($state, 0, ',', '.')
                                : '—'
                        )
                        ->color(fn($state) => match (true) {
                            $state === null => 'gray',
                            $state > 0      => 'warning',
                            $state < 0      => 'danger',
                            default         => 'success',
                        }),
                    TextEntry::make('closing_notes')
                        ->label('Catatan Selisih')
                        ->placeholder('Tidak ada catatan')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
