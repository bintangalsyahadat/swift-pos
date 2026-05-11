<?php

namespace App\Filament\Resources\Cashiers\Tables;

use App\Filament\Pages\PosTerminal;
use App\Models\PosSession;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CashiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                TextColumn::make('session_status')
                    ->label('Status')
                    ->getStateUsing(fn($record) => PosSession::openSessionForTerminal($record->id) ? 'In Use' : 'Available')
                    ->badge()
                    ->color(fn($state) => $state === 'In Use' ? 'warning' : 'success'),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Total Orders')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('open_pos')
                    ->label('Open POS')
                    ->icon('heroicon-o-computer-desktop')
                    ->color('success')
                    ->url(fn($record) => PosTerminal::getUrl(['cashier_id' => $record->id])),
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
