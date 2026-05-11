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
                Close Shift
            </button>
            @else
            <a href="{{ \App\Filament\Resources\Cashiers\CashierResource::getUrl('index') }}"
                class="text-xs font-medium text-gray-600 border border-gray-200 rounded-lg px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                &larr; Back
            </a>
            @endif
        </div>
    </nav>

    {{-- ══════════════════════════════════════════════════ PHASE: no_session --}}
    @if($phase === 'no_session')
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-primary-600 px-6 py-5 text-white">
                <h2 class="text-lg font-bold">Open Shift</h2>
                <p class="text-primary-200 text-sm">Terminal: {{ $cashier->name }}</p>
            </div>
            <div class="p-6 space-y-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Enter the opening cash balance in the drawer before starting the shift.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Opening Balance (IDR) <span class="text-red-500">*</span>
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
                    <span wire:loading.remove wire:target="openSession">Start Shift</span>
                    <span wire:loading wire:target="openSession">Opening…</span>
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
                    <div class="col-span-full text-center text-gray-400 py-16">No products found</div>
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
                    <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm">Cart</span>
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
            <p class="text-xs text-gray-500">{{ collect($cart)->sum('qty') }} item(s)</p>
            <p class="font-bold text-primary-600 text-base leading-tight">IDR {{ number_format($this->totalPayment, 0, ',', '.') }}</p>
        </div>
        <button wire:click="openCheckoutModal" wire:loading.attr="disabled"
            class="shrink-0 bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition flex items-center gap-2">
            <span wire:loading.remove wire:target="openCheckoutModal">Checkout</span>
            <span wire:loading wire:target="openCheckoutModal">Loading…</span>
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
                <h2 class="text-lg font-bold">Close Shift</h2>
                <p class="text-red-200 text-sm">{{ $cashier->name }} — {{ $this->posSession?->opened_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-gray-500">Shift Started</span>
                        <span class="font-medium">{{ $this->posSession?->opened_at->format('H:i') }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-gray-500">Opening Balance</span>
                        <span class="font-medium">IDR {{ number_format($this->posSession?->opening_balance ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-gray-500">Cash Sales</span>
                        <span class="font-medium text-green-600">+ IDR {{ number_format($cashSalesAmt, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2.5 bg-amber-50 dark:bg-amber-900/20">
                        <span class="font-semibold text-amber-700 dark:text-amber-300">Expected Balance</span>
                        <span class="font-bold text-amber-700 dark:text-amber-300">IDR {{ number_format($expectedBal, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Actual Balance (Cash in Drawer) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-sm text-gray-500">Rp</span>
                        <input type="number" wire:model.live="actualBalance" min="0" step="1000" placeholder="0"
                            class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    @error('actualBalance') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    @if($actualBalance > 0)
                    @php $diff = (int)$actualBalance - $expectedBal; @endphp
                    <div class="mt-2 flex items-center gap-2 text-xs font-medium px-3 py-2 rounded-lg
                        {{ $diff === 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                        @if($diff === 0) ✓ Balanced — No discrepancy
                        @elseif($diff > 0) ▲ Surplus: IDR {{ number_format($diff, 0, ',', '.') }} — notes required
                        @else ▼ Shortage: IDR {{ number_format(abs($diff), 0, ',', '.') }} — notes required
                        @endif
                    </div>
                    @endif
                </div>
                @if($notesRequired)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                        Discrepancy Notes <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="closingNotes" rows="3"
                        placeholder="Explain the reason for the discrepancy…"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                    @error('closingNotes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
                <div class="flex flex-col sm:flex-row gap-3 pt-1">
                    <button wire:click="cancelCloseSession"
                        class="flex-1 py-2.5 rounded-xl font-semibold text-sm text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        Cancel
                    </button>
                    <button wire:click="closeSession" wire:loading.attr="disabled"
                        class="flex-1 py-2.5 rounded-xl font-bold text-sm bg-red-600 hover:bg-red-700 text-white flex items-center justify-center gap-2 transition">
                        <span wire:loading.remove wire:target="closeSession">Close Shift</span>
                        <span wire:loading wire:target="closeSession">Closing…</span>
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
                <h2 class="font-bold text-gray-900 dark:text-white text-base">Pilih Customer</h2>
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
                    <h2 class="font-bold text-gray-900 dark:text-white text-base">Payment</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $cashier->name }} &middot; {{ now()->format('d M Y, H:i') }}</p>
                </div>
                <button wire:click="closeCheckoutModal" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 p-1 rounded-lg">
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
                        <span class="font-medium text-gray-800 dark:text-white shrink-0 ml-3">IDR {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Totals --}}
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 space-y-1.5 border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Subtotal</span>
                        <span>IDR {{ number_format($this->totalPrice, 0, ',', '.') }}</span>
                    </div>
                    @if($discount > 0)
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Discount ({{ $discount }}%)</span>
                        <span class="text-red-500">- IDR {{ number_format($this->discountAmount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-lg font-extrabold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                        <span>Total</span>
                        <span class="text-primary-600">IDR {{ number_format($this->totalPayment, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Warning: customer belum ada & default belum diset --}}
                @if(! $this->hasCustomer)
                <div class="flex items-start gap-3 rounded-xl border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-4 py-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Customer belum dipilih</p>
                        <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                            Kembali dan pilih customer, atau atur
                            <a href="{{ \App\Filament\Pages\SettingsPage::getUrl() }}" target="_blank"
                                class="underline font-medium hover:text-amber-900">Default Customer di Settings</a>
                            agar transaksi bisa diproses.
                        </p>
                    </div>
                </div>
                @endif

                {{-- Payment method --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
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
                    <p class="text-xs text-gray-500 dark:text-gray-400">Scan QRIS below to pay</p>
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($selectedPm->qr_image) }}"
                        class="w-48 h-48 object-contain rounded-xl border border-gray-200 dark:border-gray-700 bg-white p-2"
                        alt="QRIS {{ $selectedPm->name }}">
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-400">{{ $selectedPm->name }}</p>
                </div>
                @endif

            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800 shrink-0 space-y-2">
                <button wire:click="pay" wire:loading.attr="disabled"
                    @disabled(!$paymentMethodId || !$this->hasCustomer)
                    class="w-full py-3.5 rounded-xl font-bold text-base flex items-center justify-center gap-2 transition
                    {{ $paymentMethodId && $this->hasCustomer
                            ? 'bg-primary-600 hover:bg-primary-700 text-white shadow-lg'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed' }}">
                    {{-- Normal state --}}
                    <svg wire:loading.remove wire:target="pay" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span wire:loading.remove wire:target="pay">Confirm Payment — IDR {{ number_format($this->totalPayment, 0, ',', '.') }}</span>
                    {{-- Loading state --}}
                    <svg wire:loading wire:target="pay" class="animate-spin w-5 h-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span wire:loading wire:target="pay">Processing…</span>
                </button>
                <button wire:click="closeCheckoutModal" class="w-full py-2.5 rounded-xl font-medium text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancel
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
    $footerMsg = \App\Models\Setting::get('general.receipt_footer','Thank you for your purchase!');
    $currency = \App\Models\Setting::get('general.currency', 'IDR');
    $fmt = fn($n) => number_format($n, 0, ',', '.');
    @endphp
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 print:hidden" id="receipt-modal-overlay">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden flex flex-col max-h-[90vh]">
            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-white/10">
                <h3 class="font-semibold text-gray-800 dark:text-white text-sm">Receipt</h3>
                <div class="flex items-center gap-2">
                    <button onclick="posReceiptPrint()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                        </svg>
                        Print
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
                            <span style="color:#888;">Order</span>
                            <span style="font-weight:600;">{{ $order->order_number }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin:2px 0;">
                            <span style="color:#888;">Date</span>
                            <span>{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y, H:i') }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin:2px 0;">
                            <span style="color:#888;">Cashier</span>
                            <span>{{ $cashier->name ?? '-' }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin:2px 0;">
                            <span style="color:#888;">Customer</span>
                            <span>{{ $order->customer?->name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="border-dashed"></div>

                    {{-- Items --}}
                    <table style="width:100%;font-size:11px;border-collapse:collapse;margin-bottom:4px;">
                        <thead>
                            <tr style="color:#888;border-bottom:1px solid #ddd;">
                                <th style="text-align:left;padding-bottom:3px;font-weight:normal;">Item</th>
                                <th style="text-align:center;padding-bottom:3px;font-weight:normal;width:30px;">Qty</th>
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
                            <span>Discount ({{ $order->discount }}%)</span>
                            <span>− {{ $currency }} {{ $fmt($order->discount_amount) }}</span>
                        </div>
                        @endif
                        <div style="display:flex;justify-content:space-between;margin:4px 0 2px;font-weight:bold;font-size:13px;border-top:1px solid #ddd;padding-top:4px;">
                            <span>Total</span>
                            <span>{{ $currency }} {{ $fmt($order->total_payment) }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin:2px 0;color:#666;">
                            <span>Payment</span>
                            <span>{{ $order->paymentMethod?->name ?? ucfirst($order->payment_method) }}</span>
                        </div>
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
            '<title>Receipt</title>' +
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