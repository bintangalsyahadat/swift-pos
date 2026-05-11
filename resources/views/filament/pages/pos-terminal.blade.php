<div
    x-data="{ cartOpen: false }"
    class="min-h-screen bg-gray-100 dark:bg-gray-950 flex flex-col">

    {{-- ══════════════════════════════════════════════════════════════ NAVBAR --}}
    <nav class="shrink-0 flex items-center justify-between gap-2 px-4 py-2.5 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        {{-- Left --}}
        <div class="flex items-center gap-2 min-w-0">
            <span class="font-extrabold text-base text-primary-600 dark:text-primary-400 tracking-tight shrink-0">{{ \App\Models\Setting::get('general.store_name', 'SwiftPOS') }}</span>
            <span class="hidden sm:block text-gray-300 dark:text-gray-600">|</span>
            <span class="hidden sm:block font-semibold text-gray-700 dark:text-gray-200 text-sm truncate">{{ $cashier->name }}</span>
            <span class="hidden sm:block text-xs text-gray-400 shrink-0">({{ $cashier->code }})</span>
            @if($phase === 'operational' && $session)
            <span class="flex items-center gap-1.5 text-xs bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-1 rounded-full border border-green-200 dark:border-green-700 shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>
                <span class="hidden sm:inline">Shift:</span> {{ $session->opened_at->format('H:i') }}
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
    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden relative">

        {{-- ── Products panel ───────────────────────────────────────────────── --}}
        <div class="flex-1 flex flex-col p-3 sm:p-4 gap-3 overflow-hidden min-w-0">

            {{-- Search + mobile cart toggle --}}
            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Search by name, SKU or barcode…"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" />

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
        <div class="hidden lg:flex w-[340px] xl:w-[380px] shrink-0 flex-col border-l border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
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
        <button wire:click="checkout" wire:loading.attr="disabled"
            class="shrink-0 bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition flex items-center gap-2">
            <span wire:loading.remove wire:target="checkout">Checkout</span>
            <span wire:loading wire:target="checkout">Processing…</span>
        </button>
    </div>
    @endif
    @endif

    {{-- ══════════════════════════════════════════════ PHASE: close_session --}}
    @if($phase === 'close_session')
    @php
    $expectedBal = $session ? $session->computeExpectedBalance() : 0;
    $cashSalesAmt = $session ? $session->cashSales() : 0;
    @endphp
    <div class="flex-1 flex items-start sm:items-center justify-center p-4 overflow-y-auto">
        <div class="w-full max-w-lg bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden my-4">
            <div class="bg-red-600 px-6 py-5 text-white">
                <h2 class="text-lg font-bold">Close Shift</h2>
                <p class="text-red-200 text-sm">{{ $cashier->name }} — {{ $session?->opened_at->format('d M Y, H:i') }}</p>
            </div>
            <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-gray-500">Shift Started</span>
                        <span class="font-medium">{{ $session?->opened_at->format('H:i') }}</span>
                    </div>
                    <div class="flex justify-between px-4 py-2.5">
                        <span class="text-gray-500">Opening Balance</span>
                        <span class="font-medium">IDR {{ number_format($session?->opening_balance ?? 0, 0, ',', '.') }}</span>
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

</div>