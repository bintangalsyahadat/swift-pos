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
            Section::make('Session Info')
                ->columns(3)
                ->schema([
                    TextEntry::make('terminal.name')->label('Terminal'),
                    TextEntry::make('terminal.code')->label('Terminal Code'),
                    TextEntry::make('state')
                        ->badge()
                        ->color(fn($state) => match ($state) {
                            'open'   => 'success',
                            'closed' => 'gray',
                            default  => 'secondary',
                        }),
                    TextEntry::make('user.name')->label('Cashier'),
                    TextEntry::make('opened_at')->label('Opened At')->dateTime('d M Y, H:i'),
                    TextEntry::make('closed_at')->label('Closed At')->dateTime('d M Y, H:i')->placeholder('Still open'),
                ]),

            Section::make('Financial Summary')
                ->columns(2)
                ->schema([
                    TextEntry::make('opening_balance')->label('Opening Balance')->money('IDR'),
                    TextEntry::make('expected_balance')->label('Expected Balance')->money('IDR')->placeholder('—'),
                    TextEntry::make('actual_balance')->label('Actual Balance')->money('IDR')->placeholder('—'),
                    TextEntry::make('difference_amount')
                        ->label('Difference')
                        ->formatStateUsing(
                            fn($state) => $state !== null
                                ? ($state >= 0 ? '+' : '') . 'IDR ' . number_format($state, 0, ',', '.')
                                : '—'
                        )
                        ->color(fn($state) => match (true) {
                            $state === null => 'gray',
                            $state > 0      => 'warning',
                            $state < 0      => 'danger',
                            default         => 'success',
                        }),
                    TextEntry::make('closing_notes')
                        ->label('Discrepancy Notes')
                        ->placeholder('No notes')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
