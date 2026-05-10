<?php

namespace App\Filament\Resources\InventoryAdjustments\Pages;

use App\Filament\Resources\InventoryAdjustments\InventoryAdjustmentResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInventoryAdjustment extends ViewRecord
{
    protected static string $resource = InventoryAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn() => $this->record->status === 'draft'),

            Action::make('confirm')
                ->label('Konfirmasi (Done)')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Inventory Adjustment')
                ->modalDescription('Setelah dikonfirmasi, stock move akan dibuat dan adjustment tidak bisa diubah lagi.')
                ->action(function () {
                    $this->record->update(['status' => 'done']);
                    $this->refreshFormData(['status']);

                    \Filament\Notifications\Notification::make()
                        ->title('Adjustment dikonfirmasi')
                        ->body('Stock move telah dibuat untuk semua produk.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
