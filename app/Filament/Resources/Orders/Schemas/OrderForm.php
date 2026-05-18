<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Cashier;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
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
                    ->prefix('Tanggal: ')
                    ->columnSpanFull(),
                Group::make()
                    ->schema([
                        Section::make()
                            ->description('Informasi Pelanggan')
                            ->schema([
                                Select::make('customer_id')
                                    ->label('Pelanggan')
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
                                    })
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nama')
                                            ->required(),
                                        TextInput::make('phone')
                                            ->label('Telepon'),
                                        TextInput::make('email')
                                            ->label('Alamat Email')
                                            ->email(),
                                        TextInput::make('address')
                                            ->label('Alamat'),
                                    ])
                                    ->disabled(fn(Get $get) => in_array($get('status'), ['completed', 'cancelled'])),
                                TextInput::make('phone')
                                    ->label('Telepon')
                                    ->disabled()
                                    ->hidden(fn(Get $get) => !$get('customer_id'))
                                    ->formatStateUsing(fn($state, Get $get) => $state ?? Customer::find($get('customer_id'))->phone ?? null),
                                TextInput::make('address')
                                    ->label('Alamat')
                                    ->disabled()
                                    ->hidden(fn(Get $get) => !$get('customer_id'))
                                    ->formatStateUsing(fn($state, Get $get) => $state ?? Customer::find($get('customer_id'))->address ?? null)
                                    ->columnSpanFull(),
                            ])->columns(2)->columnSpanFull(),
                        Section::make()
                            ->description('Detail Pesanan')
                            ->schema([
                                Repeater::make('orderDetails')
                                    ->relationship()
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Produk')
                                            ->required()
                                            ->relationship('product', 'name', modifyQueryUsing: fn($query) => $query->where('is_active', true))
                                            ->getSearchResultsUsing(fn(string $search) => Product::where('is_active', true)
                                                ->where(
                                                    fn($query) => $query
                                                        ->where('name', 'like', "%{$search}%")
                                                        ->orWhere('sku', 'like', "%{$search}%")
                                                        ->orWhere('barcode', 'like', "%{$search}%")
                                                )
                                                ->limit(20)
                                                ->pluck('name', 'id'))
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

                                                $discount = $get('../../discount') ?? 0;
                                                $discount_amount = $total * ($discount / 100);
                                                $set('../../discount_amount', $discount_amount);
                                                $set('../../total_payment', $total - $discount_amount);
                                            })
                                            ->columnSpanFull()
                                            ->searchable(),
                                        TextInput::make('price')
                                            ->label('Harga')
                                            ->required()
                                            ->numeric()
                                            ->prefix(\App\Models\Setting::currencySymbol())
                                            ->readOnly()
                                            ->formatStateUsing(fn($state, Get $get) => $state ?? Product::find($get('product_id'))->price ?? 0),
                                        TextInput::make('quantity')
                                            ->label('Jumlah')
                                            ->required()
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->hint(function (Get $get) {
                                                $product = Product::find($get('product_id'));
                                                if (! $product) return null;
                                                $stock = $product->currentStock();
                                                return 'Stok tersedia: ' . $stock . ($stock <= 0 ? ' (Habis)' : '');
                                            })
                                            ->hintColor(function (Get $get) {
                                                $product = Product::find($get('product_id'));
                                                if (! $product) return null;
                                                return $product->currentStock() > 0 ? 'success' : 'danger';
                                            })
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $price = $get('price') ?? 0;
                                                $set('subtotal', $price * $state);

                                                $items = $get('../../orderDetails') ?? [];
                                                $total = collect($items)->sum(function ($item) {
                                                    return $item['subtotal'] ?? 0;
                                                });
                                                $set('../../total_price', $total);

                                                $discount = $get('../../discount') ?? 0;
                                                $discount_amount = $total * ($discount / 100);
                                                $set('../../discount_amount', $discount_amount);
                                                $set('../../total_payment', $total - $discount_amount);
                                            }),
                                        TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->required()
                                            ->numeric()
                                            ->readOnly()
                                            ->default(0)
                                            ->prefix(\App\Models\Setting::currencySymbol()),
                                    ])->columns(3)
                                    ->hiddenLabel()
                                    ->addAction(
                                        fn(Action $action) => $action
                                            ->label('Tambah Produk')
                                            ->icon('heroicon-o-plus')
                                    )
                            ])->columnSpanFull()->hidden(fn(Get $get) => in_array($get('status'), ['completed', 'cancelled'])),

                    ])->columnSpan(2),

                Section::make()
                    ->description('Informasi Pembayaran')
                    ->schema([
                        Select::make('cashier_id')
                            ->label('Kasir')
                            ->relationship('cashier', 'name', modifyQueryUsing: fn($query) => $query->where('is_active', true))
                            ->searchable()
                            ->nullable()
                            ->columnSpanFull()
                            ->disabled(fn(Get $get) => in_array($get('status'), ['completed', 'cancelled'])),
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
                        TextInput::make('total_price')
                            ->label('Total Harga')
                            ->required()
                            ->numeric()
                            ->readOnly()
                            ->prefix(\App\Models\Setting::currencySymbol())
                            ->default(0)
                            ->columnSpanFull()
                            ->disabled(fn(Get $get) => in_array($get('status'), ['completed', 'cancelled'])),
                        TextInput::make('discount')
                            ->label('Diskon')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $discount = floatval($state) ?? 0;
                                $total_price = $get('total_price') ?? 0;
                                $discount_amount = $total_price * ($discount / 100);
                                $set('discount_amount', $discount_amount);
                                $set('total_payment', $total_price - ($state ?? 0));
                            })
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->columnSpan(2)
                            ->disabled(fn(Get $get) => in_array($get('status'), ['completed', 'cancelled'])),
                        TextInput::make('discount_amount')
                            ->label('Jumlah Diskon')
                            ->required()
                            ->numeric()
                            ->readOnly()
                            ->prefix(\App\Models\Setting::currencySymbol())
                            ->columnSpan(2)
                            ->disabled(fn(Get $get) => in_array($get('status'), ['completed', 'cancelled'])),
                        TextInput::make('total_payment')
                            ->label('Total Pembayaran')
                            ->required()
                            ->numeric()
                            ->readOnly()
                            ->default(0)
                            ->prefix(\App\Models\Setting::currencySymbol())
                            ->columnSpanFull()
                            ->disabled(fn(Get $get) => in_array($get('status'), ['completed', 'cancelled'])),
                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->required()
                            ->options([
                                'cash' => 'Tunai',
                                'credit' => 'Kartu Kredit',
                                'debit' => 'Kartu Debit',
                                'qris' => 'QRIS',
                            ])
                            ->default('cash')
                            ->columnSpan(2)
                            ->disabled(fn(Get $get) => in_array($get('status'), ['completed', 'cancelled'])),
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
                            ->columnSpan(2),
                    ])->columnSpan(1)->columns(4),
            ])->columns(3);
    }
}
