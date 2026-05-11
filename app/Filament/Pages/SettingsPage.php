<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class SettingsPage extends Page
{
    protected string $view = 'filament.pages.settings';

    protected static string|\BackedEnum|null $navigationIcon       = Heroicon::OutlinedCog6Tooth;
    protected static string|\BackedEnum|null $activeNavigationIcon = Heroicon::Cog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

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
            $rules['xendit_secret_key']  = 'required|string';
            $rules['xendit_public_key']  = 'required|string';
            $rules['xendit_webhook_token'] = 'nullable|string';
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
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'Settings';
    }

    public function getCustomersProperty()
    {
        return Customer::orderBy('name')->get();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }
}
