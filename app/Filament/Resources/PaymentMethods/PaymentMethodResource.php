<?php

namespace App\Filament\Resources\PaymentMethods;

use App\Filament\Resources\PaymentMethods\Pages\CreatePaymentMethod;
use App\Filament\Resources\PaymentMethods\Pages\EditPaymentMethod;
use App\Filament\Resources\PaymentMethods\Pages\ListPaymentMethods;
use App\Filament\Resources\PaymentMethods\Schemas\PaymentMethodForm;
use App\Filament\Resources\PaymentMethods\Tables\PaymentMethodsTable;
use App\Models\PaymentMethod;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static string|BackedEnum|null $navigationIcon       = Heroicon::OutlinedCreditCard;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::CreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Point of Sale';

    protected static ?string $navigationLabel = 'Metode Pembayaran';
    protected static ?string $slug            = 'payment-methods';
    protected static ?int    $navigationSort  = 5;

    public static function form(Schema $schema): Schema
    {
        return PaymentMethodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentMethodsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPaymentMethods::route('/'),
            'create' => CreatePaymentMethod::route('/create'),
            'edit'   => EditPaymentMethod::route('/{record}/edit'),
        ];
    }

    public static function typeOptions(): array
    {
        return [
            'cash'            => 'Tunai',
            'card'            => 'Kartu Kredit / Debit',
            'qr_code'         => 'QR Code',
            'virtual_account' => 'Virtual Account',
        ];
    }
}
