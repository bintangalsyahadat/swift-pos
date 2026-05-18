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

            Section::make('Info Dasar')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('cth. Tunai, QRIS, Debit BCA'),

                    TextInput::make('code')
                        ->label('Kode')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->alphaDash()
                        ->placeholder('cth. cash, qris, card_bca')
                        ->helperText('Slug unik — digunakan secara internal dan dalam laporan.'),

                    Select::make('type')
                        ->label('Tipe')
                        ->required()
                        ->options([
                            'cash'    => 'Tunai',
                            'card'    => 'Kartu Kredit / Debit',
                            'qr_code' => 'QR Code',
                        ])
                        ->native(false)
                        ->live()
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(2)
                        ->columnSpanFull(),

                    FileUpload::make('icon')
                        ->label('Ikon / Logo')
                        ->image()
                        ->directory('payment-methods/icons')
                        ->columnSpanFull(),

                    // Static QR image — only for qr_code type when Xendit is disabled
                    FileUpload::make('qr_image')
                        ->label('Gambar QRIS Statis')
                        ->helperText('Unggah gambar QRIS statis dari bank/penyedia pembayaran Anda. Ditampilkan saat checkout agar pelanggan dapat memindai.')
                        ->image()
                        ->imagePreviewHeight('200')
                        ->directory('payment-methods/qr')
                        ->visible(fn(Get $get) => $get('type') === 'qr_code' && ! Setting::getBool('xendit.enabled'))
                        ->columnSpanFull(),
                ]),

            Section::make('Integrasi Xendit')
                ->description('Biarkan kosong untuk metode pembayaran offline/manual (mis. tunai, EDC).')
                ->collapsed()
                ->hidden(fn() => ! Setting::getBool('xendit.enabled'))
                ->columns(2)
                ->schema([
                    Toggle::make('is_online')
                        ->label('Gunakan API Xendit')
                        ->helperText('Aktifkan untuk memproses pembayaran melalui Xendit.')
                        ->live()
                        ->columnSpanFull(),

                    Select::make('xendit_channel_type')
                        ->label('Tipe Channel Xendit')
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
                        ->helperText('Konfigurasi tambahan opsional yang diperlukan Xendit untuk channel tertentu (mis. mobile_number untuk e-wallet).')
                        ->visible(fn(Get $get) => $get('is_online'))
                        ->columnSpanFull(),
                ]),

            Section::make('Biaya & Status')
                ->columns(3)
                ->schema([
                    Select::make('fee_type')
                        ->label('Tipe Biaya')
                        ->options([
                            'flat'       => 'Tetap (IDR)',
                            'percentage' => 'Persentase (%)',
                        ])
                        ->native(false)
                        ->live(),

                    TextInput::make('fee_value')
                        ->label(fn(Get $get) => $get('fee_type') === 'percentage' ? 'Biaya (%)' : 'Biaya (IDR)')
                        ->numeric()
                        ->minValue(0)
                        ->placeholder('0')
                        ->visible(fn(Get $get) => filled($get('fee_type'))),

                    TextInput::make('sort_order')
                        ->label('Urutan')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
