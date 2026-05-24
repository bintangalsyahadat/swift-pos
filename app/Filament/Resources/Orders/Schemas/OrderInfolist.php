<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\PaymentMethod;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->label('Informasi Pesanan')
                            ->schema([
                                TextEntry::make('order_number')
                                    ->label('No. Pesanan')
                                    ->weight(FontWeight::Bold)
                                    ->copyable(),
                                TextEntry::make('cashier.name')
                                    ->label('Kasir')
                                    ->placeholder('—'),
                                TextEntry::make('order_date')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->locale('id')->translatedFormat('d F Y, H:i') : '—')->columnSpan(1),
                                TextEntry::make('status')
                                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'new' => 'info',
                                        'processing' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary',
                                    }),
                            ])->columns(2),
                        Section::make()
                            ->label('Detail Pelanggan')
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->numeric(),
                                TextEntry::make('customer.phone'),
                                TextEntry::make('customer.address')
                                    ->columnSpanFull(),
                            ])->columns(2)->columnSpanFull(),
                    ])->columnSpan(2),
                Group::make()
                    ->schema([
                        Section::make()
                            ->label('Informasi Pembayaran')
                            ->schema([
                                TextEntry::make('total_price')
                                    ->label('Total Harga')
                                    ->formatStateUsing(fn($state) => $state !== null ? 'Rp ' . number_format($state, 0, ',', '.') : '—')
                                    ->columnSpanFull(),
                                TextEntry::make('discount')
                                    ->suffix('%')
                                    ->columnSpan(2),
                                TextEntry::make('discount_amount')
                                    ->formatStateUsing(fn($state) => $state !== null ? 'Rp ' . number_format($state, 0, ',', '.') : '—')
                                    ->columnSpan(2),
                                TextEntry::make('total_payment')
                                    ->label('Total Pembayaran')
                                    ->formatStateUsing(fn($state) => $state !== null ? 'Rp ' . number_format($state, 0, ',', '.') : '—')
                                    ->columnSpan(2),
                                TextEntry::make('payment_method')
                                    ->formatStateUsing(fn(string $state): string => PaymentMethod::where('code', $state)->value('name') ?? ucfirst($state))
                                    ->label('Metode Pembayaran')
                                    ->columnSpan(2),
                                TextEntry::make('cash_paid')
                                    ->label('Uang Diterima')
                                    ->formatStateUsing(fn($state) => $state !== null ? 'Rp ' . number_format($state, 0, ',', '.') : '—')
                                    ->columnSpan(2)
                                    ->visible(fn($record) => PaymentMethod::where('code', $record->payment_method)->value('type') === 'cash' && $record->payment_status === 'paid'),
                                TextEntry::make('change_amount')
                                    ->label('Kembalian')
                                    ->formatStateUsing(fn($state) => $state !== null ? 'Rp ' . number_format($state, 0, ',', '.') : '—')
                                    ->columnSpan(2)
                                    ->visible(fn($record) => PaymentMethod::where('code', $record->payment_method)->value('type') === 'cash' && $record->payment_status === 'paid'),
                                TextEntry::make('payment_status')
                                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'unpaid' => 'info',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        default => 'secondary',
                                    })
                                    ->label('Status Pembayaran')
                                    ->columnSpanFull(),
                            ])->columnSpan(1)->columns(4),
                    ]),
            ])->columns(3);
    }
}
