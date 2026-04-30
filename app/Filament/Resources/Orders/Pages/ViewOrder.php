<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn() => !in_array($this->record->status, ['completed', 'cancelled'])),

            Action::make('process')
                ->label('Process Order')
                ->outlined()
                ->icon('heroicon-o-arrow-path')
                ->visible(fn() => $this->record->status === 'new')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'processing']);
                    $this->refreshFormData(['status']);
                }),

            Action::make('complete')
                ->label('Set to Paid')
                ->outlined()
                ->icon('heroicon-o-check-circle')
                ->visible(fn() => $this->record->status === 'processing')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'completed',
                        'payment_status' => 'paid',
                    ]);
                    $this->refreshFormData(['status', 'payment_status']);
                }),

            Action::make('cancel')
                ->label('Cancel')
                ->color('danger')
                ->outlined()
                ->icon('heroicon-o-x-circle')
                ->visible(fn() => in_array($this->record->status, ['new', 'processing']))
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);
                    $this->refreshFormData(['status']);
                }),

            Action::make('draft')
                ->label('Set to Draft')
                ->color('gray')
                ->icon('heroicon-o-document')
                ->visible(function () {
                    /** @var \App\Models\User|null $user */
                    $user = Auth::user();
                    return $this->record->status !== 'new' && $user?->can('SetDraftOrder');
                })
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => 'new',
                        'payment_status' => 'unpaid',
                    ]);
                    $this->refreshFormData(['status', 'payment_status']);
                }),
        ];
    }
}
