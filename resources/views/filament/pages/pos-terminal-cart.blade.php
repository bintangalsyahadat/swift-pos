{{-- Cart header (only shown in desktop sidebar; mobile drawer has its own header) --}}
@unless($hideMobileHeader ?? false)
<div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800 shrink-0">
    <div class="flex items-center gap-2">
        <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm">Keranjang</span>
        @if(!empty($cart))
        <span class="bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-bold px-1.5 py-0.5 rounded-full">{{ collect($cart)->sum('qty') }}</span>
        @endif
    </div>
    @if(!empty($cart))
    <button wire:click="clearCart" class="text-xs text-red-500 hover:text-red-700">Hapus semua</button>
    @endif
</div>
@endunless

{{-- If the mobile drawer header is shown, still show the Clear all button separately --}}
@if($hideMobileHeader ?? false)
@if(!empty($cart))
<div class="flex justify-end px-4 py-2 border-b border-gray-100 dark:border-gray-800 shrink-0">
    <button wire:click="clearCart" class="text-xs text-red-500 hover:text-red-700">Hapus semua</button>
</div>
@endif
@endif

{{-- Cart items --}}
<div class="flex-1 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-800 min-h-0">
    @forelse ($cart as $productId => $item)
    <div class="px-4 py-3">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 dark:text-white truncate">{{ $item['name'] }}</p>
                <p class="text-xs text-gray-400">{{ number_format($item['price'], 0, ',', '.') }} / item</p>
            </div>
            <button wire:click="removeFromCart({{ $productId }})" class="text-gray-300 hover:text-red-500 text-lg leading-none shrink-0">&times;</button>
        </div>
        <div class="flex items-center justify-between mt-2">
            <div class="flex items-center gap-1">
                <button wire:click="decrementQty({{ $productId }})" class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-800 font-bold text-sm hover:bg-primary-100 transition">-</button>
                <span class="w-7 text-center text-sm font-semibold">{{ $item['qty'] }}</span>
                <button wire:click="incrementQty({{ $productId }})" class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-800 font-bold text-sm hover:bg-primary-100 transition">+</button>
            </div>
            <span class="text-sm font-semibold text-primary-600">{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
        </div>
    </div>
    @empty
    <div class="flex flex-col items-center justify-center py-16 text-gray-300 dark:text-gray-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <p class="text-sm font-medium">Keranjang kosong</p>
        <p class="text-xs">Ketuk produk untuk menambahkan</p>
    </div>
    @endforelse
</div>

{{-- Cart footer: totals + checkout --}}
<div class="border-t border-gray-100 dark:border-gray-800 p-4 space-y-3 shrink-0">
    {{-- Customer picker --}}
    <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
            Pelanggan <span class="text-gray-400 font-normal">(opsional)</span>
        </label>
        @if($this->selectedCustomer)
        {{-- Customer terpilih --}}
        <div class="flex items-center gap-2 rounded-lg border border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20 px-3 py-2">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-primary-800 dark:text-primary-200 truncate">{{ $this->selectedCustomer->name }}</p>
                @if($this->selectedCustomer->phone)
                <p class="text-xs text-primary-600 dark:text-primary-400 truncate">{{ $this->selectedCustomer->phone }}</p>
                @endif
            </div>
            <button wire:click="clearCustomer" class="shrink-0 text-primary-400 hover:text-red-500 transition p-0.5" title="Hapus">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <button wire:click="openCustomerModal" class="shrink-0 text-primary-400 hover:text-primary-600 transition p-0.5" title="Ganti customer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828A2 2 0 0110 16.414H8v-2a2 2 0 01.586-1.414z" />
                </svg>
            </button>
        </div>
        @else
        {{-- Belum dipilih --}}
        <button wire:click="openCustomerModal"
            class="w-full flex items-center gap-2 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-sm text-gray-500 dark:text-gray-400 px-3 py-2 hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400 transition text-left">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            <span>Pilih customer…</span>
        </button>
        @endif
    </div>
    {{-- Discount --}}
    <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Diskon (%)</label>
        <input type="number" wire:model.live="discount" min="0" max="100"
            class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500" />
    </div>
    {{-- Totals --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-3 space-y-1.5">
        <div class="flex justify-between text-xs text-gray-500">
            <span>Subtotal</span>
            <span>IDR {{ number_format($this->totalPrice, 0, ',', '.') }}</span>
        </div>
        @if($discount > 0)
        <div class="flex justify-between text-xs text-gray-500">
            <span>Diskon ({{ $discount }}%)</span>
            <span class="text-red-500">- IDR {{ number_format($this->discountAmount, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="flex justify-between text-base font-bold text-gray-900 dark:text-white pt-1.5 border-t border-gray-200 dark:border-gray-700">
            <span>Total</span>
            <span class="text-primary-600">IDR {{ number_format($this->totalPayment, 0, ',', '.') }}</span>
        </div>
    </div>
    {{-- Checkout button --}}
    <button wire:click="openCheckoutModal" wire:loading.attr="disabled"
        @disabled(empty($cart))
        class="w-full py-3 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition
            {{ !empty($cart) ? 'bg-primary-600 hover:bg-primary-700 text-white shadow-lg' : 'bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" wire:loading.remove wire:target="openCheckoutModal">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span wire:loading.remove wire:target="openCheckoutModal">Bayar — IDR {{ number_format($this->totalPayment, 0, ',', '.') }}</span>
        <span wire:loading wire:target="openCheckoutModal">Memuat…</span>
    </button>
</div>