<?php

namespace App\Filament\Resources\Orders\Schemas;

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
                        TextEntry::make('order_number')
                            ->label('Order No.')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->copyable()->columnSpan(1),
                        Group::make()
                            ->schema([
                                TextEntry::make('order_date')
                                    ->dateTime()->columnSpan(1),
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
                            ])->columns(2)
                    ])->columnSpanFull(),
                Group::make()
                    ->schema([
                        Section::make()
                            ->label('Customer Details')
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
                            ->label('Subtotal')
                            ->schema([
                                TextEntry::make('total_price')
                                    ->label('Total Price')
                                    ->money('IDR'),
                                Group::make()
                                    ->schema([
                                        TextEntry::make('discount')
                                            ->suffix('%'),
                                        TextEntry::make('discount_amount')
                                            ->money('IDR'),
                                    ])->columns(2),
                            ]),
                    ]),
                Group::make()
                    ->schema([
                        Section::make()
                            ->label('Payment Information')
                            ->schema([
                                TextEntry::make('total_payment')
                                    ->label('Total Payment')
                                    ->money('IDR'),
                                Group::make()
                                    ->schema([
                                        TextEntry::make('payment_method')
                                            ->formatStateUsing(fn(string $state): string => ucfirst($state))
                                            ->label('Payment Method'),
                                        TextEntry::make('payment_status')
                                            ->formatStateUsing(fn(string $state): string => ucfirst($state))
                                            ->badge()
                                            ->color(fn($state) => match ($state) {
                                                'unpaid' => 'info',
                                                'paid' => 'success',
                                                'failed' => 'danger',
                                                default => 'secondary',
                                            })
                                            ->label('Payment Status'),
                                    ])->columns(2)
                            ])
                    ])->columnSpanFull(),
            ])->columns(3);
    }
}
