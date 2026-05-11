<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Basic Info')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('e.g. Cash, QRIS, BCA Debit'),

                    TextInput::make('code')
                        ->label('Code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->alphaDash()
                        ->placeholder('e.g. cash, qris, card_bca')
                        ->helperText('Unique slug — used internally and in reports.'),

                    Select::make('type')
                        ->label('Type')
                        ->required()
                        ->options([
                            'cash'    => 'Cash',
                            'card'    => 'Credit / Debit Card',
                            'qr_code' => 'QR Code',
                        ])
                        ->native(false)
                        ->live()
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Description')
                        ->rows(2)
                        ->columnSpanFull(),

                    FileUpload::make('icon')
                        ->label('Icon / Logo')
                        ->image()
                        ->directory('payment-methods/icons')
                        ->columnSpanFull(),

                    // Static QR image — only for qr_code type when Xendit is disabled
                    FileUpload::make('qr_image')
                        ->label('Static QRIS Image')
                        ->helperText('Upload the static QRIS image provided by your bank/payment provider. Displayed at checkout for customers to scan.')
                        ->image()
                        ->imagePreviewHeight('200')
                        ->directory('payment-methods/qr')
                        ->visible(fn(Get $get) => $get('type') === 'qr_code' && ! Setting::getBool('xendit.enabled'))
                        ->columnSpanFull(),
                ]),

            Section::make('Xendit Integration')
                ->description('Leave blank for offline / manual payment methods (e.g. cash, EDC).')
                ->collapsed()
                ->hidden(fn() => ! Setting::getBool('xendit.enabled'))
                ->columns(2)
                ->schema([
                    Toggle::make('is_online')
                        ->label('Use Xendit API')
                        ->helperText('Enable to process payment via Xendit.')
                        ->live()
                        ->columnSpanFull(),

                    Select::make('xendit_channel_type')
                        ->label('Xendit Channel Type')
                        ->options([
                            'QR_CODE'          => 'QR_CODE',
                            'VIRTUAL_ACCOUNT'  => 'VIRTUAL_ACCOUNT',
                            'EWALLET'          => 'EWALLET',
                            'OVER_THE_COUNTER' => 'OVER_THE_COUNTER',
                            'CREDIT_CARD'      => 'CREDIT_CARD',
                        ])
                        ->native(false)
                        ->live()
                        ->visible(fn(Get $get) => $get('is_online')),

                    Select::make('xendit_channel_code')
                        ->label('Xendit Channel Code')
                        ->options(fn(Get $get) => match ($get('xendit_channel_type')) {
                            'QR_CODE'          => ['ID_QRIS' => 'ID_QRIS (QRIS)'],
                            'VIRTUAL_ACCOUNT'  => [
                                'BCA'                => 'BCA',
                                'BNI'                => 'BNI',
                                'BRI'                => 'BRI',
                                'MANDIRI'            => 'MANDIRI',
                                'PERMATA'            => 'PERMATA',
                                'BSS'                => 'BSS',
                                'BJB'                => 'BJB',
                                'SAHABAT_SAMPOERNA'  => 'SAHABAT_SAMPOERNA',
                            ],
                            'EWALLET' => [
                                'ID_OVO'       => 'OVO',
                                'ID_GOPAY'     => 'GoPay',
                                'ID_SHOPEEPAY' => 'ShopeePay',
                                'ID_DANA'      => 'DANA',
                                'ID_LINKAJA'   => 'LinkAja',
                                'ID_ASTRAPAY'  => 'AstraPay',
                                'ID_JENIUSPAY' => 'Jenius Pay',
                            ],
                            'OVER_THE_COUNTER' => [
                                'ALFAMART'  => 'Alfamart',
                                'INDOMARET' => 'Indomaret',
                            ],
                            default => [],
                        })
                        ->native(false)
                        ->searchable()
                        ->visible(fn(Get $get) => $get('is_online') && filled($get('xendit_channel_type'))),

                    KeyValue::make('xendit_channel_properties')
                        ->label('Channel Properties (JSON)')
                        ->helperText('Optional extra config required by Xendit for specific channels (e.g. mobile_number for e-wallet).')
                        ->visible(fn(Get $get) => $get('is_online'))
                        ->columnSpanFull(),
                ]),

            Section::make('Fee & Status')
                ->columns(3)
                ->schema([
                    Select::make('fee_type')
                        ->label('Fee Type')
                        ->options([
                            'flat'       => 'Flat (IDR)',
                            'percentage' => 'Percentage (%)',
                        ])
                        ->native(false)
                        ->live(),

                    TextInput::make('fee_value')
                        ->label(fn(Get $get) => $get('fee_type') === 'percentage' ? 'Fee (%)' : 'Fee (IDR)')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('0')
                        ->visible(fn(Get $get) => filled($get('fee_type'))),

                    TextInput::make('sort_order')
                        ->label('Sort Order')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
