<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Customer;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('order_date')
                    ->default(now())
                    ->disabled()
                    ->hiddenLabel()
                    ->prefix('Date: ')
                    ->columnSpanFull(),
                Section::make()
                    ->description('Customer Information')
                    ->schema([
                        Select::make('customer_id')
                            ->required()
                            ->relationship('customer', 'name')
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, ?string $state) {
                                if ($state) {
                                    $customer = Customer::find($state);
                                    $set('phone', $customer->phone ?? null);
                                    $set('address', $customer->address ?? null);
                                } else {
                                    $set('phone', null);
                                    $set('address', null);
                                }
                            }),
                        TextInput::make('phone')
                            ->disabled()
                            ->hidden(fn (Get $get) => !$get('customer_id'))
                            ->formatStateUsing(fn ($state, Get $get) => $state ?? Customer::find($get('customer_id'))->phone ?? null),
                        TextInput::make('address')
                            ->disabled()
                            ->hidden(fn (Get $get) => !$get('customer_id'))
                            ->formatStateUsing(fn ($state, Get $get) => $state ?? Customer::find($get('customer_id'))->address ?? null)
                            ->columnSpanFull(),
                    ])->columns(2)->columnSpanFull(),
                Section::make()
                    ->description('Order Details')
                    ->schema([
                        Repeater::make('orderDetails')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->required()
                                    ->relationship('product', 'name')
                                    ->reactive()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $product = Product::find($state);
                                        $price = $product->price ?? 0;
                                        $set('price', $price);
                                        $quantity = $get('quantity') ?? 1;
                                        $set('quantity', $quantity);
                                        $set('subtotal', $price * $quantity);

                                        $items = $get('../../orderDetails') ?? [];
                                        $total = collect($items)->sum(function ($item) {
                                            return $item['subtotal'] ?? 0;
                                        });
                                        $set('../../total_price', $total);
                                    }),
                                TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->disabled()
                                    ->formatStateUsing(fn($state, Get $get) => $state ?? Product::find($get('product_id'))->price ?? 0),
                                TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $price = $get('price') ?? 0;
                                        $set('subtotal', $price * $state);

                                        $items = $get('../../orderDetails') ?? [];
                                        $total = collect($items)->sum(function ($item) {
                                            return $item['subtotal'] ?? 0;
                                        });
                                        $set('../../total_price', $total);
                                    }),
                                TextInput::make('subtotal')
                                    ->required()
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('IDR'),
                            ])->columns(4)
                    ])->columnSpanFull(),
                TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->prefix('IDR'),
            ]);
    }
}
