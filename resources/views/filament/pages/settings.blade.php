<x-filament-panels::page>

    {{-- ═══════════════════════════════════════════════════════════════════════════
    GENERAL SETTINGS
═══════════════════════════════════════════════════════════════════════════ --}}
    <div class="space-y-6">

        {{-- General --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center gap-x-3 overflow-hidden px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <div class="grid flex-1 gap-y-1">
                    <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">Umum</h3>
                    <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">Konfigurasi dasar toko.</p>
                </div>
            </div>
            <div class="fi-section-content p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    <div class="sm:col-span-2">
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Nama Toko <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="general_store_name"
                            class="fi-input block w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-gray-950 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        @error('general_store_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Alamat Toko
                        </label>
                        <input type="text" wire:model="general_store_address"
                            placeholder="e.g. Jl. Sudirman No. 1, Jakarta"
                            class="fi-input block w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-gray-950 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        @error('general_store_address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Telepon Toko
                        </label>
                        <input type="text" wire:model="general_store_phone"
                            placeholder="e.g. +62 812 3456 7890"
                            class="fi-input block w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-gray-950 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                        @error('general_store_phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Mata Uang <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="general_currency"
                            class="fi-select-input block w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-gray-950 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="IDR">IDR — Indonesian Rupiah</option>
                            <option value="USD">USD — US Dollar</option>
                            <option value="SGD">SGD — Singapore Dollar</option>
                            <option value="MYR">MYR — Malaysian Ringgit</option>
                        </select>
                        @error('general_currency') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Zona Waktu <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="general_timezone"
                            class="fi-select-input block w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-gray-950 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="Asia/Jakarta">Asia/Jakarta (WIB, UTC+7)</option>
                            <option value="Asia/Makassar">Asia/Makassar (WITA, UTC+8)</option>
                            <option value="Asia/Jayapura">Asia/Jayapura (WIT, UTC+9)</option>
                            <option value="Asia/Singapore">Asia/Singapore (SGT, UTC+8)</option>
                            <option value="UTC">UTC</option>
                        </select>
                        @error('general_timezone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Pesan Footer Struk
                        </label>
                        <textarea wire:model="general_receipt_footer" rows="2"
                            placeholder="cth. Terima kasih telah berbelanja bersama kami!"
                            class="fi-input block w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-gray-950 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                        @error('general_receipt_footer') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Pelanggan Default
                            <span class="text-xs font-normal text-gray-500 ml-1">(untuk transaksi tanpa customer spesifik / customer umum)</span>
                        </label>
                        <select wire:model="general_default_customer_id"
                            class="fi-select-input block w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-gray-950 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">— Tidak ada default —</option>
                            @foreach($this->customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Jika kasir tidak memilih customer di POS, order akan otomatis menggunakan customer ini.</p>
                        @error('general_default_customer_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════
        XENDIT SETTINGS
    ═══════════════════════════════════════════════════════════════════════ --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center justify-between gap-x-3 overflow-hidden px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <div class="grid flex-1 gap-y-1">
                    <div class="flex items-center gap-2">
                        <h3 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
                            Xendit Payment Gateway
                        </h3>
                        @if($xendit_enabled)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aktif
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-semibold bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Nonaktif
                        </span>
                        @endif
                    </div>
                    <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                        Aktifkan untuk menerima pembayaran online via QRIS, Virtual Account, dan e-wallet melalui Xendit.
                    </p>
                </div>

                {{-- Enable / Disable Toggle --}}
                <button type="button" wire:click="$toggle('xendit_enabled')"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                    {{ $xendit_enabled ? 'bg-primary-600' : 'bg-gray-200 dark:bg-white/20' }}"
                    role="switch" aria-checked="{{ $xendit_enabled ? 'true' : 'false' }}">
                    <span class="pointer-events-none inline-block h-5 w-5 translate-x-0 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                    {{ $xendit_enabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>

            @if($xendit_enabled)
            <div class="fi-section-content p-6">
                <div class="mb-4 flex items-center gap-3 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-4 py-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    <p class="text-xs text-amber-700 dark:text-amber-300">
                        <strong>Jaga keamanan API key Anda.</strong> Jangan pernah membagikan Secret Key. Gunakan kredensial Sandbox terlebih dahulu.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    <div class="sm:col-span-2">
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Lingkungan <span class="text-red-500">*</span>
                        </label>
                        <div class="flex rounded-lg border border-gray-300 dark:border-white/10 overflow-hidden">
                            <button type="button" wire:click="$set('xendit_environment', 'sandbox')"
                                class="flex-1 px-4 py-2 text-sm font-medium transition
                                {{ $xendit_environment === 'sandbox'
                                    ? 'bg-primary-600 text-white'
                                    : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/10' }}">
                                Sandbox (Pengujian)
                            </button>
                            <button type="button" wire:click="$set('xendit_environment', 'production')"
                                class="flex-1 px-4 py-2 text-sm font-medium border-l border-gray-300 dark:border-white/10 transition
                                {{ $xendit_environment === 'production'
                                    ? 'bg-primary-600 text-white'
                                    : 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/10' }}">
                                🚀 Produksi (Live)
                            </button>
                        </div>
                        @if($xendit_environment === 'production')
                        <p class="mt-1 text-xs text-red-500 font-medium">⚠ Mode Produksi — transaksi uang nyata akan terjadi.</p>
                        @else
                        <p class="mt-1 text-xs text-gray-500">Mode Sandbox — gunakan kredensial uji Xendit, tidak ada tagihan nyata.</p>
                        @endif
                    </div>

                    <div>
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Secret Key <span class="text-red-500">*</span>
                        </label>
                        <input type="password" wire:model="xendit_secret_key"
                            placeholder="{{ $xendit_environment === 'sandbox' ? 'xnd_development_...' : 'xnd_production_...' }}"
                            autocomplete="new-password"
                            class="fi-input block w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-gray-950 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 font-mono" />
                        <p class="mt-1 text-xs text-gray-500">Digunakan untuk panggilan API sisi server. Jaga kerahasiaannya.</p>
                        @error('xendit_secret_key') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Public Key <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="xendit_public_key"
                            placeholder="{{ $xendit_environment === 'sandbox' ? 'xnd_public_development_...' : 'xnd_public_production_...' }}"
                            class="fi-input block w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-gray-950 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 font-mono" />
                        <p class="mt-1 text-xs text-gray-500">Digunakan untuk tokenisasi sisi klien.</p>
                        @error('xendit_public_key') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Webhook Token
                            <span class="text-xs font-normal text-gray-500 ml-1">(opsional)</span>
                        </label>
                        <input type="text" wire:model="xendit_webhook_token"
                            placeholder="Set in Xendit Dashboard → Webhooks → Verification Token"
                            class="fi-input block w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-1.5 text-sm text-gray-950 dark:text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 font-mono" />
                        <p class="mt-1 text-xs text-gray-500">Digunakan untuk memverifikasi permintaan webhook yang masuk dari Xendit.</p>
                        @error('xendit_webhook_token') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Webhook URL --}}
                    <div class="sm:col-span-2">
                        <label class="fi-fo-field-wrp-label block text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">
                            Webhook URL
                            <span class="text-xs font-normal text-gray-500 ml-1">(daftarkan ini di Xendit Dashboard)</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <code class="flex-1 block rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 font-mono">
                                {{ url('/api/webhooks/xendit') }}
                            </code>
                            <button type="button"
                                onclick="navigator.clipboard.writeText('{{ url('/api/webhooks/xendit') }}')"
                                class="shrink-0 px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/10 transition">
                                Copy
                            </button>
                        </div>
                    </div>

                </div>
            </div>
            @else
            <div class="px-6 py-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <p class="text-sm font-medium text-gray-400 dark:text-gray-500">Integrasi Xendit dinonaktifkan.</p>
                <p class="text-xs text-gray-400 dark:text-gray-600 mt-1">Aktifkan toggle di atas untuk mengonfigurasi pemrosesan pembayaran online.</p>
            </div>
            @endif
        </div>

        {{-- Save Button --}}
        <div class="flex justify-end gap-3">
            <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="fi-btn fi-btn-size-md inline-grid grid-flow-col items-center justify-center gap-1.5 font-semibold rounded-lg px-4 py-2 text-sm bg-primary-600 text-white shadow-sm hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
                <span wire:loading wire:target="save">Menyimpan…</span>
            </button>
        </div>

    </div>

</x-filament-panels::page>