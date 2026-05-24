<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\PaymentMethod;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Notifications\Notification;
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
                ->label('Proses Pesanan')
                ->outlined()
                ->icon('heroicon-o-arrow-path')
                ->visible(fn() => $this->record->status === 'new')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'processing']);
                    $this->refreshFormData(['status']);
                }),

            Action::make('complete')
                ->label('Tandai Lunas')
                ->outlined()
                ->icon('heroicon-o-check-circle')
                ->visible(fn() => $this->record->status === 'processing')
                ->form(function () {
                    $cashCodes = PaymentMethod::where('type', 'cash')->pluck('code')->toArray();

                    return [
                        Grid::make(4)
                            ->schema([
                                Placeholder::make('total_payment_display')
                                    ->label('Total Pembayaran')
                                    ->content('Rp ' . number_format($this->record->total_payment, 0, ',', '.'))
                                    ->columnSpanFull(),
                                Select::make('payment_method')
                                    ->label('Metode Pembayaran')
                                    ->required()
                                    ->options(fn() => PaymentMethod::where('is_active', true)
                                        ->orderBy('sort_order')
                                        ->pluck('name', 'code'))
                                    ->live()
                                    ->afterStateUpdated(function ($set) {
                                        $set('cash_paid', null);
                                        $set('change_amount_display', null);
                                    })
                                    ->columnSpanFull(),
                                TextInput::make('cash_paid')
                                    ->label('Uang Diterima')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) use ($cashCodes) {
                                        if (in_array($get('payment_method'), $cashCodes)) {
                                            $change = max(0, floatval($state) - floatval($this->record->total_payment));
                                            $set('change_amount_display', number_format($change, 0, ',', '.'));
                                        }
                                    })
                                    ->hidden(fn($get) => !in_array($get('payment_method'), $cashCodes))
                                    ->required(fn($get) => in_array($get('payment_method'), $cashCodes))
                                    ->columnSpan(2),
                                TextInput::make('change_amount_display')
                                    ->label('Kembalian')
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->hidden(fn($get) => !in_array($get('payment_method'), $cashCodes))
                                    ->columnSpan(2),
                            ]),
                    ];
                })
                ->action(function (array $data) {
                    $isCash = PaymentMethod::where('code', $data['payment_method'] ?? null)->value('type') === 'cash';

                    if ($isCash) {
                        $cashPaid = floatval($data['cash_paid'] ?? 0);
                        $totalPayment = floatval($this->record->total_payment);

                        if ($cashPaid < $totalPayment) {
                            Notification::make()
                                ->title('Uang diterima kurang')
                                ->body('Uang diterima (Rp ' . number_format($cashPaid, 0, ',', '.') . ') kurang dari total pembayaran (Rp ' . number_format($totalPayment, 0, ',', '.') . ').')
                                ->danger()
                                ->persistent()
                                ->send();

                            $this->halt();
                            return;
                        }

                        $this->record->update([
                            'status' => 'completed',
                            'payment_status' => 'paid',
                            'payment_method' => $data['payment_method'],
                            'cash_paid' => $cashPaid,
                            'change_amount' => max(0, $cashPaid - $totalPayment),
                        ]);
                    } else {
                        $this->record->update([
                            'status' => 'completed',
                            'payment_status' => 'paid',
                            'payment_method' => $data['payment_method'],
                        ]);
                    }

                    $this->refreshFormData(['status', 'payment_status', 'payment_method', 'cash_paid', 'change_amount']);
                }),

            Action::make('cancel')
                ->label('Batalkan')
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
                ->label('Set ke Draft')
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
                        'cash_paid' => 0,
                        'change_amount' => 0,
                    ]);
                    $this->refreshFormData(['status', 'payment_status']);
                }),
        ];
    }
}
