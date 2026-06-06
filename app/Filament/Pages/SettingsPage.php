<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Setting;
use App\Services\XenditService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class SettingsPage extends Page
{
    protected string $view = 'filament.pages.settings';

    protected static string|\BackedEnum|null $navigationIcon       = Heroicon::OutlinedCog6Tooth;
    protected static string|\BackedEnum|null $activeNavigationIcon = Heroicon::Cog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 99;

    protected static ?string $slug = 'settings';

    // ── Livewire properties ───────────────────────────────────────────────────

    // General
    public string $general_store_name        = 'SwiftPOS';
    public string $general_store_address     = '';
    public string $general_store_phone       = '';
    public string $general_currency          = 'IDR';
    public string $general_timezone          = 'Asia/Jakarta';
    public string $general_receipt_footer    = '';
    public ?int   $general_default_customer_id = null;

    // Xendit
    public bool   $xendit_enabled         = false;
    public string $xendit_environment     = 'sandbox';
    public string $xendit_secret_key      = '';
    public string $xendit_public_key      = '';
    public string $xendit_webhook_token   = '';

    // Xendit test result
    public ?bool   $xendit_test_success = null;  // null = belum ditest
    public string  $xendit_test_message = '';
    public bool    $xendit_testing      = false;

    // Xendit webhook test result
    public ?bool   $xendit_webhook_test_success = null;
    public string  $xendit_webhook_test_message = '';

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->general_store_name          = Setting::get('general.store_name',     'SwiftPOS');
        $this->general_store_address       = Setting::get('general.store_address',  '');
        $this->general_store_phone         = Setting::get('general.store_phone',    '');
        $this->general_currency            = Setting::get('general.currency',        'IDR');
        $this->general_timezone            = Setting::get('general.timezone',        'Asia/Jakarta');
        $this->general_receipt_footer      = Setting::get('general.receipt_footer',  '');
        $this->general_default_customer_id = (int) Setting::get('general.default_customer_id') ?: null;

        $this->xendit_enabled         = Setting::getBool('xendit.enabled');
        $this->xendit_environment     = Setting::get('xendit.environment',     'sandbox');
        $this->xendit_secret_key      = Setting::get('xendit.secret_key',      '');
        $this->xendit_public_key      = Setting::get('xendit.public_key',      '');
        $this->xendit_webhook_token   = Setting::get('xendit.webhook_token',   '');
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        $rules = [
            'general_store_name'          => 'required|string|max:100',
            'general_store_address'       => 'nullable|string|max:255',
            'general_store_phone'         => 'nullable|string|max:30',
            'general_currency'            => 'required|string|max:10',
            'general_timezone'            => 'required|string',
            'general_receipt_footer'      => 'nullable|string|max:500',
            'general_default_customer_id' => 'nullable|integer|exists:customers,id',
            'xendit_enabled'              => 'boolean',
            'xendit_environment'          => 'required_if:xendit_enabled,true|in:sandbox,production',
        ];

        if ($this->xendit_enabled) {
            $rules['xendit_secret_key']    = 'required|string';
            $rules['xendit_public_key']    = 'required|string';
            $rules['xendit_webhook_token'] = 'required|string|min:8';
        }

        return $rules;
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate();

        Setting::setMany([
            'general.store_name'          => $this->general_store_name,
            'general.store_address'       => $this->general_store_address,
            'general.store_phone'         => $this->general_store_phone,
            'general.currency'            => $this->general_currency,
            'general.timezone'            => $this->general_timezone,
            'general.receipt_footer'      => $this->general_receipt_footer,
            'general.default_customer_id' => $this->general_default_customer_id,
        ], 'general');

        Setting::setMany([
            'xendit.enabled'       => $this->xendit_enabled,
            'xendit.environment'   => $this->xendit_environment,
            'xendit.secret_key'    => $this->xendit_secret_key,
            'xendit.public_key'    => $this->xendit_public_key,
            'xendit.webhook_token' => $this->xendit_webhook_token,
        ], 'xendit');

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'Pengaturan';
    }

    public function getCustomersProperty()
    {
        return Customer::orderBy('name')->get();
    }

    // ── Xendit Connection Test ────────────────────────────────────────────────

    public function testXenditConnection(): void
    {
        if (! filled($this->xendit_secret_key)) {
            $this->xendit_test_success = false;
            $this->xendit_test_message = 'Secret Key belum diisi.';
            return;
        }

        $this->xendit_testing = true;
        $result = app(XenditService::class)->testWithKey($this->xendit_secret_key);
        $this->xendit_testing = false;

        $this->xendit_test_success = $result['success'];
        $this->xendit_test_message = $result['message'];

        if ($result['success']) {
            Notification::make()
                ->title('Koneksi Xendit berhasil')
                ->body($result['message'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Koneksi Xendit gagal')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }

    // Reset hasil test jika secret key diubah
    public function updatedXenditSecretKey(): void
    {
        $this->xendit_test_success = null;
        $this->xendit_test_message = '';
    }

    // Reset hasil webhook test jika token diubah
    public function updatedXenditWebhookToken(): void
    {
        $this->xendit_webhook_test_success = null;
        $this->xendit_webhook_test_message = '';
    }

    // ── Webhook Endpoint Test ─────────────────────────────────────────────────

    public function testWebhookEndpoint(): void
    {
        $formToken = trim($this->xendit_webhook_token);

        // ── Token kosong: wajib diisi ─────────────────────────────────────────
        if (! filled($formToken)) {
            $this->xendit_webhook_test_success = false;
            $this->xendit_webhook_test_message = 'Webhook Token wajib diisi agar status pembayaran dapat diperbarui otomatis.';

            Notification::make()
                ->title('Webhook Token wajib diisi')
                ->body('Isi Webhook Token terlebih dahulu, lalu simpan pengaturan.')
                ->danger()
                ->send();

            return;
        }

        // ── Bandingkan form vs nilai yang tersimpan di DB ─────────────────────
        $savedToken = trim(Setting::get('xendit.webhook_token', ''));

        if (! filled($savedToken)) {
            $this->xendit_webhook_test_success = false;
            $this->xendit_webhook_test_message = 'Token belum tersimpan di database. Klik "Simpan Pengaturan" terlebih dahulu, lalu test ulang.';

            Notification::make()
                ->title('Token belum disimpan')
                ->body('Simpan pengaturan terlebih dahulu sebelum melakukan test.')
                ->warning()
                ->send();

            return;
        }

        if (! hash_equals($savedToken, $formToken)) {
            $this->xendit_webhook_test_success = false;
            $this->xendit_webhook_test_message = 'Token di form berbeda dengan yang tersimpan. Klik "Simpan Pengaturan" terlebih dahulu, lalu test ulang.';

            Notification::make()
                ->title('Token belum disimpan')
                ->body('Ada perubahan token yang belum disimpan.')
                ->warning()
                ->send();

            return;
        }

        // ── Token cocok: konfigurasi webhook benar ────────────────────────────
        $webhookUrl = url('/api/webhooks/xendit');

        $this->xendit_webhook_test_success = true;
        $this->xendit_webhook_test_message = "Token webhook valid ✓ — Daftarkan URL berikut di Xendit Dashboard: {$webhookUrl}";

        Notification::make()
            ->title('Konfigurasi webhook valid ✓')
            ->body('Token tersimpan dengan benar. Pastikan URL webhook sudah didaftarkan di Xendit Dashboard.')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }
}
