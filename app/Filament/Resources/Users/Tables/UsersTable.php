<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Alamat Email')
                    ->searchable(),
                // TextColumn::make('email_verified_at')
                //     ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—')
                //     ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
