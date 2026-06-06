<div
    x-data="{ cartOpen: false }"
    class="h-screen bg-gray-100 dark:bg-gray-950 flex flex-col overflow-hidden">

    {{-- ══════════════════════════════════════════════════════════════ NAVBAR --}}
    <nav class="shrink-0 flex items-center justify-between gap-2 px-4 py-2.5 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        {{-- Left --}}
        <div class="flex items-center gap-2 min-w-0">
            <span class="font-extrabold text-base text-primary-600 dark:text-primary-400 tracking-tight shrink-0">{{ \App\Models\Setting::get('general.store_name', 'SwiftPOS') }}</span>
            <span class="hidden sm:block text-gray-300 dark:text-gray-600">|</span>
            <span class="hidden sm:block font-semibold text-gray-700 dark:text-gray-200 text-sm truncate">{{ $cashier->name }}</span>
            <span class="hidden sm:block text-xs text-gray-400 shrink-0">({{ $cashier->code }})</span>
            @if($phase === 'operational' && $this->posSession)
            <span class="flex items-center gap-1.5 text-xs bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-1 rounded-full border border-green-200 dark:border-green-700 shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>
                <span class="hidden sm:inline">Shift:</span> {{ $this->posSession->opened_at->format('H:i') }}
            </span>
            @endif
        </div>
        {{-- Right --}}
        <div class="flex items-center gap-2 shrink-0">
            <div class="hidden md:block text-right text-xs text-gray-500 dark:text-gray-400">
                <p class="font-medium text-gray-700 dark:text-gray-200">{{ auth()->user()->name }}</p>
                <p>{{ now()->format('D, d M Y') }}</p>
            </div>
            @if($phase === 'operational')
            <button wire:click="showCloseSession"
                class="text-xs font-medium text-red-600 border border-red-200 rounded-lg px-3 py-1.5 hover:bg-red-50 transition">
                Tutup Shift
            </button>
            @else
            <a href="{{ \App\Filament\Resources\Cashiers\CashierResource::getUrl('index') }}"
                class="text-xs font-medium text-gray-600 border border-gray-200 rounded-lg px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                &larr; Kembali
            </a>
            @endif
        </div>
    </nav>

    {{-- ══════════════════════════════════════════════════ PHASE: no_session --}}
    @if($phase === 'no_session')
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-primary-600 px-6 py-5 text-white">
                <h2 class="text-lg font-bold">Buka Shift</h2>
                <p class="text-primary-200 text-sm">Terminal: {{ $cashier->name }}</p>
            </div>
            <div class="p-6 space-y-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Masukkan saldo kas awal di laci sebelum memulai shift.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Saldo Awal (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-sm text-gray-500">Rp</span>
                        <input type="number" wire:model="openingBalance" min="0" step="1000" placeholder="0"
                            class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    @error('openingBalance') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <button wire:click="openSession" wire:loading.attr="disabled"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2.5 rounded-lg flex items-center justify-center gap-2 transition">
                    <span wire:loading.remove wire:target="openSession">Mulai Shift</span>
                    <span wire:loading wire:target="openSession">Membuka…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════ PHASE: operational --}}
    @if($phase === 'operational')
    {{-- Marker: session sedang open (dipakai oleh beforeunload guard) --}}
    <span data-session-open class="hidden"></span>
    @if(!empty($cart))
    {{-- Marker: cart ada isi --}}
    <span data-cart-has-items class="hidden"></span>
    @endif
    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden relative">

        {{-- ── Products panel ───────────────────────────────────────────────── --}}
        <div class="flex-1 flex flex-col p-3 sm:p-4 gap-3 overflow-hidden min-w-0">

            {{-- Search + mobile cart toggle --}}
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    {{-- Icon scan --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8H3a2 2 0 00-2 2v10a2 2 0 002 2h4a2 2 0 002-2V10a2 2 0 00-2-2zm12 0h2a2 2 0 012 2v10a2 2 0 01-2 2h-4a2 2 0 01-2-2V10a2 2 0 012-2zM9 16h6" />
                    </svg>
                    <input
                        id="pos-search-input"
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Scan barcode atau ketik nama / SKU…"
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>

                {{-- Cart toggle (mobile only) --}}
                <button
                    @click="cartOpen = !cartOpen"
                    class="lg:hidden relative flex items-center justify-center w-11 h-11 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    @if(!empty($cart))
                    <span class="absolute -top-1.5 -right-1.5 bg-primary-600 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">
                        {{ collect($cart)->sum('qty') }}
                    </span>
                    @endif
                </button>
            </div>

            {{-- Cashier info on small screens --}}
            <div class="flex sm:hidden items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $cashier->name }}</span>
                <span>({{ $cashier->code }})</span>
            </div>

            {{-- Product grid --}}
            <div class="flex-1 overflow-y-auto pb-2">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-2 sm:gap-3">
                    @forelse ($this->products as $product)
                    @php $stock = $product->currentStock(); @endphp
                    <button wire:click="addToCart({{ $product->id }})" @disabled($stock <=0)
                        class="relative rounded-xl border p-2.5 sm:p-3 text-left transition focus:outline-none
                            {{ $stock > 0
                                ? 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-primary-400 hover:shadow-md'
                                : 'opacity-50 cursor-not-allowed bg-gray-100 dark:bg-gray-900 border-gray-200' }}">
                        @if($product->image)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image) }}"
                            class="w-full h-16 sm:h-20 object-cover rounded-lg mb-2" alt="">
                        @else
                        <div class="w-full h-16 sm:h-20 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2"></div>
                        @endif
                        <p class="text-xs font-semibold text-gray-800 dark:text-white leading-tight line-clamp-2">{{ $product->name }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5 truncate">{{ $product->sku }}</p>
                        <p class="mt-1.5 text-sm font-bold text-primary-600 dark:text-primary-400">
                            {{ number_format($product->price, 0, ',', '.') }}
                        </p>
                        <span class="absolute top-2 right-2 text-[10px] font-bold px-1.5 py-0.5 rounded-full
                            {{ $stock > 10 ? 'bg-green-100 text-green-700' : ($stock > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ $stock }}
                        </span>
                    </button>
                    @empty
                    <div class="col-span-full text-center text-gray-400 py-16">Produk tidak ditemukan</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── Cart: Desktop sidebar ─────────────────────────────────────────── --}}
        <div class="hidden lg:flex w-[340px] xl:w-[380px] shrink-0 flex-col border-l border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 min-h-0">
            @include('filament.pages.pos-terminal-cart')
        </div>

        {{-- ── Cart: Mobile slide-over ───────────────────────────────────────── --}}
        <div
            x-show="cartOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="cartOpen = false"
            class="lg:hidden fixed inset-0 z-30 bg-black/40"
            style="display:none"></div>
        <div
            x-show="cartOpen"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="lg:hidden fixed top-0 right-0 bottom-0 z-40 w-full max-w-sm flex flex-col bg-white dark:bg-gray-900 shadow-2xl"
            style="display:none">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm">Keranjang</span>
                    @if(!empty($cart))
                    <span class="bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-bold px-1.5 py-0.5 rounded-full">{{ collect($cart)->sum('qty') }}</span>
                    @endif
                </div>
                <button @click="cartOpen = false" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 p-1 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto flex flex-col min-h-0">
                @include('filament.pages.pos-terminal-cart', ['hideMobileHeader' => true])
            </div>
        </div>

    </div>

    {{-- ── Mobile sticky checkout bar ───────────────────────────────────────────── --}}
    @if(!empty($cart))
    <div class="lg:hidden shrink-0 border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-3 flex items-center gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-xs text-gray-500">{{ collect($cart)->sum('qty') }} item</p>
            <p class="font-bold text-primary-600 text-base leading-tight">{{ $this->currencySymbol }} {{ number_format($this->totalPayment, 0, ',', '.') }}</p>
        </div>
        <button wire:click="openCheckoutModal" wire:loading.attr="disabled"
            class="shrink-0 bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition flex items-center gap-2">
            <span wire:loading.remove wire:target="openCheckoutModal">Bayar</span>
            <span wire:loading wire:target="openCheckoutModal">Memuat…</span>
        </button>
    </div>
    @endif
    @endif

    {{-- ══════════════════════════════════════════════ PHASE: close_session --}}
    @if($phase === 'close_session')
    @php
    $expectedBal = $this->posSession ? $this->posSession->computeExpectedBalance() : 0;
    $cashSalesAmt = $this->posSession ? $this->posSession->cashSales() : 0;
    @endphp
    <div class="flex-1 flex items-start sm:items-center justify-center p-4 overflow-y-auto">
        <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden my-4">
            <div class="bg-red-600 px-6 py-5 text-white">
                <h2 class="text-lg font-bold">Tutup Shift</h2>
                <p class="text-red-200 text-sm">{{ $cashier->name }} — {{ $this->posSession?->opened_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-gray-500">Shift Dimulai</span>
                        <span class="font-medium">{{ $this->posSession?->opened_at->format('H:i') }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-gray-500">Saldo Awal</span>
                        <span class="font-medium">{{ $this->currencySymbol }} {{ number_format($this->posSession?->opening_balance ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-gray-500">Penjualan Tunai</span>
                        <span class="font-medium text-green-600">+ {{ $this->currencySymbol }} {{ number_format($cashSalesAmt, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2.5 bg-amber-50 dark:bg-amber-900/20">
                        <span class="font-semibold text-amber-700 dark:text-amber-300">Perkiraan Saldo</span>
                        <span class="font-bold text-amber-700 dark:text-amber-300">{{ $this->currencySymbol }} {{ number_format($expectedBal, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Saldo Aktual (Uang di Laci) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-sm text-gray-500">Rp</span>
                        <input type="number" wire:model.lazy="actualBalance" min="0" step="1000" placeholder="0"
                            class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    @error('actualBalance') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    @if($actualBalance > 0)
                    @php $diff = (int)$actualBalance - $expectedBal; @endphp
                    <div class="mt-2 flex items-center gap-2 text-xs font-medium px-3 py-2 rounded-lg
                        {{ $diff === 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                        @if($diff === 0) ✓ Seimbang — Tidak ada selisih
                        @elseif($diff > 0) ▲ Surplus: {{ $this->currencySymbol }} {{ number_format($diff, 0, ',', '.') }} — catatan diperlukan
                        @else ▼ Kekurangan: {{ $this->currencySymbol }} {{ number_format(abs($diff), 0, ',', '.') }} — catatan diperlukan
                        @endif
                    </div>
                    @endif
                </div>
                @if($notesRequired)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Catatan Selisih <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="closingNotes" rows="3"
                        placeholder="Jelaskan alasan selisih…"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                    @error('closingNotes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
                <div class="flex flex-col sm:flex-row gap-3 pt-1">
                    <button wire:click="cancelCloseSession"
                        class="flex-1 py-2.5 rounded-xl font-semibold text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        Batal
                    </button>
                    <button wire:click="closeSession" wire:loading.attr="disabled"
                        class="flex-1 py-2.5 rounded-xl font-bold text-sm bg-red-600 hover:bg-red-700 text-white flex items-center justify-center gap-2 transition">
                        <span wire:loading.remove wire:target="closeSession">Tutup Shift</span>
                        <span wire:loading wire:target="closeSession">Menutup…</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════ CUSTOMER PICKER MODAL --}}
    @if($showCustomerModal)
    <div class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4" wire:click.self="closeCustomerModal">
        <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-2xl flex flex-col max-h-[85vh]">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
                <h2 class="font-bold text-gray-900 dark:text-white text-base">Pilih Pelanggan</h2>
                <button wire:click="closeCustomerModal" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 p-1 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Search --}}
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800 shrink-0">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                    </svg>
                    <input type="text"
                        wire:model.live.debounce.250ms="customerSearch"
                        placeholder="Cari nama, nomor HP, atau email…"
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                        autofocus />
                </div>
            </div>

            {{-- Customer list --}}
            <div class="flex-1 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-800 min-h-0">
                @forelse($this->filteredCustomers as $customer)
                <button wire:click="selectCustomer({{ $customer->id }})"
                    class="w-full flex items-start gap-3 px-5 py-3.5 hover:bg-primary-50 dark:hover:bg-primary-900/20 text-left transition
                        {{ $customerId == $customer->id ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}">
                    {{-- Avatar initial --}}
                    <div class="w-9 h-9 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 font-bold text-sm flex items-center justify-center shrink-0 mt-0.5">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $customer->name }}</p>
                            @if($customerId == $customer->id)
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-primary-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-x-3 gap-y-0.5 mt-0.5">
                            @if($customer->phone)
                            <span class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                {{ $customer->phone }}
                            </span>
                            @endif
                            @if($customer->email)
                            <span class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                {{ $customer->email }}
                            </span>
                            @endif
                            @if($customer->address)
                            <span class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400 truncate max-w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="truncate">{{ $customer->address }}</span>
                            </span>
                            @endif
                        </div>
                    </div>
                </button>
                @empty
                <div class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <p class="text-sm font-medium">Tidak ada customer ditemukan</p>
                    @if($customerSearch)
                    <p class="text-xs mt-1">Coba kata kunci lain</p>
                    @endif
                </div>
                @endforelse
            </div>

            {{-- Footer --}}
            <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800 shrink-0">
                <button wire:click="closeCustomerModal" class="w-full py-2.5 rounded-xl font-medium text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Tutup
                </button>
            </div>

        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════ CHECKOUT MODAL --}}
    @if($showCheckoutModal)
    {{-- Backdrop --}}
    <div class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4" wire:click.self="closeCheckoutModal">
        <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
                <div>
                    <h2 class="font-bold text-gray-900 dark:text-white text-base">Pembayaran</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $cashier->name }} &middot; {{ now()->format('d M Y, H:i') }}</p>
                </div>
                <button wire:click="closeCheckoutModal" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 p-1 rounded-lg"></button>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="overflow-y-auto flex-1 p-5 space-y-4">

                {{-- Order items --}}
                <div class="space-y-1.5">
                    @foreach($cart as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-700 dark:text-gray-300">
                            {{ $item['name'] }}
                            <span class="text-gray-400 text-xs ml-1">×{{ $item['qty'] }}</span>
                        </span>
                        <span class="font-medium text-gray-800 dark:text-white shrink-0 ml-3">{{ $this->currencySymbol }} {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Totals --}}
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 space-y-1.5 border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Subtotal</span>
                        <span>{{ $this->currencySymbol }} {{ number_format($this->totalPrice, 0, ',', '.') }}</span>
                    </div>
                    @if($discount > 0)
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Diskon ({{ $discount }}%)</span>
                        <span class="text-red-500">- {{ $this->currencySymbol }} {{ number_format($this->discountAmount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-lg font-extrabold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                        <span>Total</span>
                        <span class="text-primary-600">{{ $this->currencySymbol }} {{ number_format($this->totalPayment, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Warning: customer belum ada & default belum diset --}}
                @if(! $this->hasCustomer)
                <div class="flex items-start gap-3 rounded-xl border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-4 py-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Pelanggan belum dipilih</p>
                        <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                            Kembali dan pilih pelanggan, atau atur
                            <a href="{{ \App\Filament\Pages\SettingsPage::getUrl() }}" target="_blank"
                                class="underline font-medium hover:text-amber-900">Pelanggan Default di Pengaturan</a>
                            agar transaksi bisa diproses.
                        </p>
                    </div>
                </div>
                @endif

                {{-- Payment method --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($this->paymentMethods as $pm)
                        <button type="button"
                            wire:click="$set('paymentMethodId', {{ $pm->id }})"
                            class="flex items-center gap-2 px-3 py-2.5 rounded-xl border-2 text-sm font-medium transition text-left
                                {{ $paymentMethodId == $pm->id
                                    ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300'
                                    : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-primary-300' }}">
                            @if($pm->icon)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($pm->icon) }}" class="w-6 h-6 object-contain rounded shrink-0" alt="">
                            @endif
                            <span class="truncate">{{ $pm->name }}</span>
                            @if($paymentMethodId == $pm->id)
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-auto shrink-0 text-primary-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- QR Image — tampil jika metode yang dipilih adalah qr_code + punya qr_image --}}
                @php $selectedPm = $this->paymentMethods->firstWhere('id', $paymentMethodId); @endphp
                @if($selectedPm && $selectedPm->type === 'qr_code' && $selectedPm->qr_image)
                <div class="flex flex-col items-center gap-2 py-2">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Scan QRIS berikut untuk membayar</p>
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($selectedPm->qr_image) }}"
                        class="w-48 h-48 object-contain rounded-xl border border-gray-200 dark:border-gray-700 bg-white p-2"
                        alt="QRIS {{ $selectedPm->name }}">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $selectedPm->name }}</p>
                </div>
                @endif

                {{-- Cash paid input — hanya tampil jika payment method = cash --}}
                @if($this->isCashPayment)
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-3 space-y-2">
                    <label class="block text-sm font-semibold text-amber-800 dark:text-amber-300">
                        Nominal Dibayar (Cash)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-sm text-gray-500">Rp</span>
                        <input type="number"
                            wire:model.lazy="cashPaid"
                            min="0"
                            step="1000"
                            placeholder="{{ number_format((int) ceil($this->totalPayment), 0, ',', '.') }}"
                            class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-amber-300 dark:border-amber-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                    </div>
                    {{-- Quick amount buttons --}}
                    <div class="flex flex-wrap gap-1.5">
                        @php
                        $total = (int) ceil($this->totalPayment);
                        $suggestions = [];
                        foreach ([1000,2000,5000,10000,20000,50000,100000] as $denom) {
                        $rounded = (int)(ceil($total / $denom) * $denom);
                        if (!in_array($rounded, $suggestions)) $suggestions[] = $rounded;
                        if (count($suggestions) >= 4) break;
                        }
                        @endphp
                        @foreach($suggestions as $sug)
                        <button type="button" wire:click="$set('cashPaid', {{ $sug }})"
                            class="text-xs px-2.5 py-1 rounded-lg border border-amber-300 dark:border-amber-600 bg-white dark:bg-gray-800 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-800/30 transition font-medium">
                            {{ number_format($sug, 0, ',', '.') }}
                        </button>
                        @endforeach
                        <button type="button" wire:click="$set('cashPaid', {{ $total }})"
                            class="text-xs px-2.5 py-1 rounded-lg bg-amber-500 text-white hover:bg-amber-600 transition font-medium">
                            Pas
                        </button>
                    </div>
                    {{-- Kembalian --}}
                    @if($cashPaid > 0)
                    @php $change = $cashPaid - (int) ceil($this->totalPayment); @endphp
                    <div class="flex justify-between items-center pt-1 border-t border-amber-200 dark:border-amber-700">
                        <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">Kembalian</span>
                        <span class="text-lg font-extrabold {{ $change >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $this->currencySymbol }} {{ number_format(max(0, $change), 0, ',', '.') }}
                            @if($change < 0)
                                <span class="text-xs font-normal">(kurang {{ number_format(abs($change), 0, ',', '.') }})</span>
                        @endif
                        </span>
                    </div>
                    @endif
                </div>
                @endif

            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800 shrink-0 space-y-2">
                <button wire:click="pay" wire:loading.attr="disabled"
                    @disabled(!$paymentMethodId || !$this->hasCustomer || ($this->isCashPayment && $cashPaid < (int) ceil($this->totalPayment)))
                        class="w-full py-3.5 rounded-xl font-bold text-base flex items-center justify-center gap-2 transition
                        {{ ($paymentMethodId && $this->hasCustomer && (!$this->isCashPayment || $cashPaid >= (int) ceil($this->totalPayment)))
                            ? 'bg-primary-600 hover:bg-primary-700 text-white shadow-lg'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed' }}">
                        {{-- Normal state --}}
                        <svg wire:loading.remove wire:target="pay" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span wire:loading.remove wire:target="pay">Konfirmasi Pembayaran — {{ $this->currencySymbol }} {{ number_format($this->totalPayment, 0, ',', '.') }}</span>
                        {{-- Loading state --}}
                        <svg wire:loading wire:target="pay" class="animate-spin w-5 h-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span wire:loading wire:target="pay">Memproses…</span>
                </button>
                <button wire:click="closeCheckoutModal" class="w-full py-2.5 rounded-xl font-medium text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Batal
                </button>
            </div>

        </div>
    </div>
    @endif

    {{-- ── Receipt Modal ──────────────────────────────────────────────── --}}
    @if($showReceiptModal && $this->receiptOrder)
    @php
    $order = $this->receiptOrder;
    $storeName = \App\Models\Setting::get('general.store_name', 'SwiftPOS');
    $storeAddr = \App\Models\Setting::get('general.store_address', '');
    $storePhone = \App\Models\Setting::get('general.store_phone', '');
    $footerMsg = \App\Models\Setting::get('general.receipt_footer','Terima kasih atas pembelian Anda!');
    $currency = $this->currencySymbol;
    $fmt = fn($n) => number_format($n, 0, ',', '.');
    @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 print:hidden" id="receipt-modal-overlay">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden flex flex-col max-h-[90vh]">
            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-white/10">
                <h3 class="font-semibold text-gray-800 dark:text-white text-sm">Struk</h3>
                <div class="flex items-center gap-2">
                    <button onclick="posReceiptPrint()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                        </svg>
                        Cetak
                    </button>
                    <button wire:click="closeReceiptModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            {{-- Receipt content (scrollable) --}}
            <div class="overflow-y-auto flex-1 p-4">
                <style>
                    #receipt-content .border-dashed {
                        border-top: 1px dashed #ccc;
                        margin: 6px 0;
                    }
                </style>
                <div id="receipt-content">
                    {{-- Store header --}}
                    <div style="text-align:center;margin-bottom:8px;">
                        <p style="font-weight:bold;font-size:14px;">{{ $storeName }}</p>
                        @if($storeAddr)
                        <p style="font-size:11px;color:#555;margin-top:2px;">{{ $storeAddr }}</p>
                        @endif
                        @if($storePhone)
                        <p style="font-size:11px;color:#555;">{{ $storePhone }}</p>
                        @endif
                    </div>

                    <div class="border-dashed"></div>

                    {{-- Order meta --}}
                    <div style="font-size:11px;margin-bottom:6px;">
                        <div style="display:flex;justify-content:space-between;margin:2px 0;">
                            <span style="color:#888;">No. Pesanan</span>
                            <span style="font-weight:600;">{{ $order->order_number }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin:2px 0;">
                            <span style="color:#888;">Tanggal</span>
                            <span>{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, H:i') }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin:2px 0;">
                            <span style="color:#888;">Kasir</span>
                            <span>{{ $cashier->name ?? '-' }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin:2px 0;">
                            <span style="color:#888;">Pelanggan</span>
                            <span>{{ $order->customer?->name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="border-dashed"></div>

                    {{-- Items --}}
                    <table style="width:100%;font-size:11px;border-collapse:collapse;margin-bottom:4px;">
                        <thead>
                            <tr style="color:#888;border-bottom:1px solid #ddd;">
                                <th style="text-align:left;padding-bottom:3px;font-weight:normal;">Item</th>
                                <th style="text-align:center;padding-bottom:3px;font-weight:normal;width:30px;">Jml</th>
                                <th style="text-align:right;padding-bottom:3px;font-weight:normal;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderDetails as $detail)
                            <tr style="border-bottom:1px solid #f0f0f0;">
                                <td style="padding:3px 4px 3px 0;line-height:1.4;">
                                    {{ $detail->product->name ?? '—' }}<br>
                                    <span style="color:#888;">{{ $currency }} {{ $fmt($detail->subtotal / max($detail->quantity, 1)) }}</span>
                                </td>
                                <td style="padding:3px 2px;text-align:center;">{{ $detail->quantity }}</td>
                                <td style="padding:3px 0;text-align:right;white-space:nowrap;">{{ $currency }} {{ $fmt($detail->subtotal) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="border-dashed"></div>

                    {{-- Totals --}}
                    <div style="font-size:11px;">
                        <div style="display:flex;justify-content:space-between;margin:2px 0;">
                            <span>Subtotal</span>
                            <span>{{ $currency }} {{ $fmt($order->total_price) }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                        <div style="display:flex;justify-content:space-between;margin:2px 0;color:#c00;">
                            <span>Diskon ({{ $order->discount }}%)</span>
                            <span>− {{ $currency }} {{ $fmt($order->discount_amount) }}</span>
                        </div>
                        @endif
                        <div style="display:flex;justify-content:space-between;margin:4px 0 2px;font-weight:bold;font-size:13px;border-top:1px solid #ddd;padding-top:4px;">
                            <span>Total</span>
                            <span>{{ $currency }} {{ $fmt($order->total_payment) }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin:2px 0;color:#666;">
                            <span>Pembayaran</span>
                            <span>{{ $order->paymentMethod?->name ?? ucfirst($order->payment_method) }}</span>
                        </div>
                        @if($order->cash_paid)
                        <div style="display:flex;justify-content:space-between;margin:2px 0;color:#666;">
                            <span>Tunai</span>
                            <span>{{ $currency }} {{ $fmt($order->cash_paid) }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin:2px 0;font-weight:bold;color:#000;">
                            <span>Kembalian</span>
                            <span>{{ $currency }} {{ $fmt($order->change_amount ?? 0) }}</span>
                        </div>
                        @endif
                    </div>

                    @if($footerMsg)
                    <div class="border-dashed"></div>
                    <p style="text-align:center;font-size:11px;color:#888;font-style:italic;">{{ $footerMsg }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════ XENDIT PAYMENT MODAL --}}
    @if($showXenditModal)
    <div class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4">

        @if($xenditPaymentFailed)
        {{-- ── Failure state — tidak ada wire:poll ────────────────────────────── --}}
        <div class="w-full max-w-sm bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h2 class="font-bold text-gray-900 dark:text-white text-base">Pembayaran Gagal</h2>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300 px-2.5 py-1 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                    Gagal
                </span>
            </div>
            <div class="p-5 flex flex-col items-center gap-4">
                {{-- Icon --}}
                <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                {{-- Failure note --}}
                <div class="w-full bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50 rounded-xl px-4 py-3">
                    <p class="text-xs font-semibold text-red-600 dark:text-red-400 mb-1 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                        Keterangan
                    </p>
                    <p class="text-sm text-red-700 dark:text-red-300">{{ $xenditFailureNote }}</p>
                </div>
                {{-- Total (dicoret) --}}
                <div class="w-full bg-gray-50 dark:bg-gray-800 rounded-xl px-4 py-3 text-center">
                    <p class="text-xs text-gray-400 mb-0.5">Total Tagihan (Dibatalkan)</p>
                    <p class="text-xl font-bold text-gray-400 line-through">
                        {{ $this->currencySymbol }} {{ number_format($this->xenditOrder?->total_payment ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <button wire:click="closeXenditModal"
                    class="w-full py-2.5 rounded-xl font-semibold text-sm bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition">
                    Tutup
                </button>
            </div>
        </div>

        @else
        {{-- ── Waiting state — dengan wire:poll ────────────────────────────────── --}}
        <div class="w-full max-w-sm bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden"
            wire:poll.5000ms="checkXenditStatus">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h2 class="font-bold text-gray-900 dark:text-white text-base">Menunggu Pembayaran</h2>
                    @if($xenditExpiresAt)
                    <p class="text-xs text-gray-400 mt-0.5">
                        Kedaluwarsa: {{ \Carbon\Carbon::parse($xenditExpiresAt)->locale('id')->translatedFormat('d M Y, H:i') }}
                    </p>
                    @endif
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300 px-2.5 py-1 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse"></span>
                    Menunggu
                </span>
            </div>

            <div class="p-5 flex flex-col items-center gap-4">

                {{-- QR Code --}}
                @if($xenditType === 'qr_code' && $xenditQrString)
                <div class="flex flex-col items-center gap-2">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Scan QRIS untuk membayar</p>
                    <div class="p-3 bg-white rounded-xl border border-gray-200 shadow-sm">
                        <img
                            src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($xenditQrString) }}"
                            alt="QRIS Code"
                            class="w-[200px] h-[200px]" />
                    </div>
                    <p class="text-xs text-gray-400 text-center max-w-[220px] break-all font-mono">{{ Str::limit($xenditQrString, 40) }}</p>
                </div>
                @endif

                {{-- Virtual Account --}}
                @if($xenditType === 'virtual_account' && $xenditVaNumber)
                <div class="w-full flex flex-col gap-2">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300 text-center">Transfer ke Virtual Account</p>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-400 mb-1 uppercase tracking-wide">{{ $xenditVaBank }}</p>
                        <p class="text-2xl font-bold tracking-widest text-gray-900 dark:text-white">{{ $xenditVaNumber }}</p>
                    </div>
                    <p class="text-xs text-gray-400 text-center">Salin nomor VA di atas, transfer tepat sesuai nominal tagihan.</p>
                </div>
                @endif

                {{-- E-Wallet / Checkout URL --}}
                @if($xenditType === 'ewallet' && ($xenditCheckoutUrl || $xenditPaymentCode))
                <div class="w-full flex flex-col gap-3">
                    @if($xenditCheckoutUrl)
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300 text-center">Link Pembayaran</p>
                    <a href="{{ $xenditCheckoutUrl }}" target="_blank"
                        class="w-full text-center bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                        Buka Halaman Pembayaran ↗
                    </a>
                    @endif
                    @if($xenditPaymentCode)
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4 text-center">
                        <p class="text-xs text-gray-400 mb-1 uppercase tracking-wide">Kode Pembayaran</p>
                        <p class="text-2xl font-bold tracking-widest text-gray-900 dark:text-white">{{ $xenditPaymentCode }}</p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Total --}}
                <div class="w-full bg-primary-50 dark:bg-primary-900/20 rounded-xl px-4 py-3 text-center">
                    <p class="text-xs text-primary-500 dark:text-primary-300 mb-0.5">Total Tagihan</p>
                    <p class="text-xl font-bold text-primary-700 dark:text-primary-300">
                        {{ $this->currencySymbol }} {{ number_format($this->xenditOrder?->total_payment ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                {{-- Dev Mode: Simulate Payment button (sandbox, non-ewallet) --}}
                @if(app()->environment('local') && $xenditType !== 'ewallet')
                @php
                $devChannelCode = $this->xenditOrder?->paymentMethod?->xendit_channel_code;
                $canSimulate = $xenditType !== 'qr_code' || $devChannelCode === 'ID_DANA';
                @endphp
                <div class="w-full bg-yellow-50 dark:bg-yellow-900/20 border border-dashed border-yellow-300 dark:border-yellow-600 rounded-xl px-4 py-3">
                    <p class="text-xs font-semibold text-yellow-700 dark:text-yellow-400 mb-2 flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        Dev Mode — Xendit Sandbox
                        @if($devChannelCode)
                        <span class="ml-auto font-mono text-yellow-500">{{ $devChannelCode }}</span>
                        @endif
                    </p>

                    @if($canSimulate)
                    <button wire:click="simulateXenditPayment"
                        wire:loading.attr="disabled"
                        wire:target="simulateXenditPayment"
                        class="w-full py-2 rounded-lg text-xs font-semibold bg-yellow-400 hover:bg-yellow-500 dark:bg-yellow-600 dark:hover:bg-yellow-500 text-yellow-900 dark:text-white transition flex items-center justify-center gap-1.5 disabled:opacity-60">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            wire:loading.class="animate-spin" wire:target="simulateXenditPayment">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span wire:loading.remove wire:target="simulateXenditPayment">Simulate Payment</span>
                        <span wire:loading wire:target="simulateXenditPayment">Mengirim…</span>
                    </button>
                    @else
                    {{-- QR Code channel bukan ID_DANA: simulate tidak didukung --}}
                    <div class="w-full rounded-lg bg-orange-100 dark:bg-orange-900/30 border border-orange-200 dark:border-orange-700 px-3 py-2">
                        <p class="text-xs text-orange-700 dark:text-orange-300 font-semibold mb-0.5">⚠ Simulate tidak didukung untuk {{ $devChannelCode }}</p>
                        <p class="text-xs text-orange-600 dark:text-orange-400">Xendit Sandbox hanya mendukung simulate QR Code untuk channel <span class="font-mono font-bold">ID_DANA</span>. Ubah channel code payment method ini ke ID_DANA untuk testing.</p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Actions --}}
                <div class="w-full flex gap-2">
                    <button wire:click="checkXenditStatus"
                        wire:loading.attr="disabled"
                        class="flex-1 py-2.5 rounded-xl font-semibold text-sm bg-green-600 hover:bg-green-700 text-white transition flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" wire:loading.class="animate-spin" wire:target="checkXenditStatus">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span wire:loading.remove wire:target="checkXenditStatus">Cek Status</span>
                        <span wire:loading wire:target="checkXenditStatus">Memeriksa…</span>
                    </button>
                    <button wire:click="closeXenditModal"
                        class="px-4 py-2.5 rounded-xl font-medium text-sm text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        Tutup
                    </button>
                </div>

                <p class="text-xs text-gray-400 text-center -mt-1">
                    Status diperbarui otomatis setiap 5 detik
                </p>
            </div>
        </div>
        @endif

    </div>
    @endif

</div>

@script
<script>
    // ── Focus search input ────────────────────────────────────────────────────
    function posSearchFocus() {
        var el = document.getElementById('pos-search-input');
        if (el) el.focus();
    }

    // Auto-focus saat load
    setTimeout(posSearchFocus, 100);

    // Re-focus setelah scan/cart update
    $wire.on('cart-updated', function() {
        setTimeout(posSearchFocus, 50);
    });

    // ── Enter di search input = scan barcode ──────────────────────────────────
    document.addEventListener('keydown', function(e) {
        var searchInput = document.getElementById('pos-search-input');
        if (!searchInput) return;

        // Jika focus di input lain, jangan intercept
        var active = document.activeElement;
        var tag = active ? active.tagName.toLowerCase() : '';
        var isOtherInput = (tag === 'input' || tag === 'textarea' || tag === 'select') &&
            active !== searchInput;
        if (isOtherInput) return;

        // Ctrl/Alt/Meta shortcuts — jangan intercept
        if (e.ctrlKey || e.altKey || e.metaKey) return;

        // Redirect focus ke search input agar scan langsung masuk
        if (active !== searchInput && e.key.length === 1) {
            searchInput.focus();
            // Biarkan karakter masuk secara natural
            return;
        }

        // Enter di search input = proses sebagai barcode
        if (e.key === 'Enter' && active === searchInput) {
            e.preventDefault();
            var val = searchInput.value.trim();
            if (val.length >= 2) {
                $wire.scanBarcode(val);
                searchInput.value = '';
                $wire.set('search', '');
            }
        }
    });

    // ── Before unload guard ───────────────────────────────────────────────────
    window.addEventListener('beforeunload', function(e) {
        var hasCart = document.querySelector('[data-cart-has-items]') !== null;
        var hasSession = document.querySelector('[data-session-open]') !== null;
        if (hasCart || hasSession) {
            e.preventDefault();
            e.returnValue = 'Shift masih aktif atau cart belum dikosongkan. Yakin ingin keluar?';
            return e.returnValue;
        }
    });

    // ── Print receipt ─────────────────────────────────────────────────────────
    function posReceiptPrint() {
        var content = document.getElementById('receipt-content');
        if (!content) return;

        var win = window.open('', '_blank', 'width=400,height=600');
        win.document.write(
            '<!DOCTYPE html><html><head>' +
            '<meta charset="utf-8">' +
            '<title>Struk</title>' +
            '<style>' +
            '  * { box-sizing: border-box; margin: 0; padding: 0; }' +
            '  body { font-family: "Courier New", Courier, monospace; font-size: 12px; color: #000; background: #fff; width: 80mm; padding: 4mm; }' +
            '  .text-center { text-align: center; }' +
            '  .text-right { text-align: right; }' +
            '  .font-bold { font-weight: bold; }' +
            '  .text-base { font-size: 14px; }' +
            '  .text-sm { font-size: 12px; }' +
            '  .text-xs { font-size: 11px; }' +
            '  .border-dashed { border-top: 1px dashed #666; margin: 6px 0; }' +
            '  table { width: 100%; border-collapse: collapse; }' +
            '  td, th { vertical-align: top; padding: 2px 2px; }' +
            '  .muted { color: #555; }' +
            '  .row { display: flex; justify-content: space-between; margin: 2px 0; }' +
            '  @media print { @page { size: 80mm auto; margin: 0; } body { width: 80mm; } }' +
            '</style>' +
            '</head><body>' +
            content.innerHTML +
            '<script>window.onload=function(){window.print();window.onafterprint=function(){window.close();};}<\/script>' +
            '</body></html>'
        );
        win.document.close();
    }

    $wire.on('open-receipt', function() {
        setTimeout(function() {
            posReceiptPrint();
        }, 350);
    });
</script>
@endscript