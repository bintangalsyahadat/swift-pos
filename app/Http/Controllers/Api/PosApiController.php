<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CloseSessionRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\OpenSessionRequest;
use App\Http\Requests\Api\SyncOrdersRequest;
use App\Models\Cashier;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentMethod;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\StockMove;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosApiController extends Controller
{
    // =========================================================================
    // 1. LOGIN
    // =========================================================================

    /**
     * POST /api/login
     * Otentikasi kasir dan kembalikan Sanctum token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $token = $user->createToken('pos-terminal')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                ],
            ],
        ]);
    }

    // =========================================================================
    // 2. INITIAL SYNC — Data master saat kasir menekan "Mulai Shift"
    // =========================================================================

    /**
     * GET /api/init-data
     * Kembalikan semua data master yang dibutuhkan kasir untuk operasional offline.
     */
    public function initData(): JsonResponse
    {
        // ── Products (aktif, stok real-time) ──────────────────────────────────
        $products = Product::where('is_active', true)->get()
            ->map(fn(Product $p) => [
                'id'              => $p->api_id,
                'name'            => $p->name,
                'sku'             => $p->sku,
                'barcode'         => $p->barcode,
                'price'           => (float) $p->price,
                'base_price'      => (float) $p->base_price,
                'image'           => $p->image,
                'current_stock'   => $p->currentStock(),
                'category_id'     => optional($p->category)->api_id,
                'sub_category_id' => optional($p->subCategory)->api_id,
                'brand_id'        => optional($p->brand)->api_id,
            ]);

        // ── Brands ────────────────────────────────────────────────────────────
        $brands = \App\Models\Brand::where('is_active', true)->get()
            ->map(fn($b) => [
                'id'    => $b->api_id,
                'name'  => $b->name,
                'image' => $b->image,
            ]);

        // ── Categories ────────────────────────────────────────────────────────
        $categories = Category::where('is_active', true)->get()
            ->map(fn($c) => [
                'id'    => $c->api_id,
                'name'  => $c->name,
                'image' => $c->image,
            ]);

        // ── Sub-Categories ────────────────────────────────────────────────────
        $subCategories = \App\Models\SubCategory::where('is_active', true)->get()
            ->map(fn($sc) => [
                'id'          => $sc->api_id,
                'name'        => $sc->name,
                'category_id' => optional($sc->category)->api_id,
            ]);

        // ── Payment Methods (aktif) ───────────────────────────────────────────
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($pm) => [
                'id'        => $pm->api_id,
                'name'      => $pm->name,
                'code'      => $pm->code,
                'type'      => $pm->type,
                'is_online' => $pm->is_online,
                'icon'      => $pm->icon,
                'qr_image'  => $pm->qr_image,
                'fee_type'  => $pm->fee_type,
                'fee_value' => (float) $pm->fee_value,
            ]);

        // ── Terminals (Cashier) — hanya yang aktif ────────────────────────────
        $terminals = Cashier::where('is_active', true)->get()
            ->map(fn($t) => [
                'id'   => $t->api_id,
                'name' => $t->name,
                'code' => $t->code,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dimuat.',
            'data'    => [
                'products'        => $products,
                'brands'          => $brands,
                'categories'      => $categories,
                'sub_categories'  => $subCategories,
                'payment_methods' => $paymentMethods,
                'terminals'       => $terminals,
                'synced_at'       => now()->toIso8601String(),
            ],
        ]);
    }

    // =========================================================================
    // 3A. BUKA SESI (SHIFT)
    // =========================================================================

    /**
     * POST /api/session/open
     * Membuat sesi kasir baru dengan status 'open'.
     */
    public function openSession(OpenSessionRequest $request): JsonResponse
    {
        $user = $request->user();

        // Pastikan user tidak memiliki sesi yang masih terbuka
        $existingSession = PosSession::where('user_id', $user->id)
            ->where('state', 'open')
            ->first();

        if ($existingSession) {
            return response()->json([
                'success' => false,
                'message' => 'Anda masih memiliki sesi yang sedang berjalan. Tutup sesi tersebut terlebih dahulu.',
                'data'    => ['session_id' => $existingSession->id],
            ], 409);
        }

        // Resolve api_id → internal id
        $terminal = Cashier::where('api_id', $request->terminal_id)->firstOrFail();

        $session = PosSession::create([
            'terminal_id'     => $terminal->id,
            'user_id'         => $user->id,
            'opening_balance' => $request->opening_balance,
            'opened_at'       => now(),
            'state'           => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi berhasil dibuka.',
            'data'    => [
                'session_id'      => $session->id,
                'terminal_id'     => $terminal->api_id,
                'terminal_name'   => $terminal->name,
                'opened_at'       => $session->opened_at->toIso8601String(),
                'opening_balance' => $session->opening_balance,
            ],
        ], 201);
    }

    // =========================================================================
    // 3B. TUTUP SESI (SHIFT)
    // =========================================================================

    /**
     * POST /api/session/close
     * Menghitung selisih saldo dan menutup sesi kasir aktif.
     */
    public function closeSession(CloseSessionRequest $request): JsonResponse
    {
        $user = $request->user();

        $session = PosSession::where('user_id', $user->id)
            ->where('state', 'open')
            ->first();

        if (! $session) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada sesi yang sedang berjalan.',
            ], 404);
        }

        $actualBalance   = $request->actual_balance;
        $expectedBalance = $session->opening_balance + $session->cashSales();
        $difference      = $actualBalance - $expectedBalance;

        // closing_notes wajib jika ada selisih
        if ($difference !== 0 && empty($request->closing_notes)) {
            throw ValidationException::withMessages([
                'closing_notes' => ['Catatan penutupan wajib diisi jika terdapat selisih saldo.'],
            ]);
        }

        $session->update([
            'actual_balance'   => $actualBalance,
            'expected_balance' => $expectedBalance,
            'difference_amount' => $difference,
            'closing_notes'    => $request->closing_notes,
            'closed_at'        => now(),
            'state'            => 'closed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi berhasil ditutup.',
            'data'    => [
                'session_id'       => $session->id,
                'opening_balance'  => $session->opening_balance,
                'expected_balance' => $expectedBalance,
                'actual_balance'   => $actualBalance,
                'difference_amount' => $difference,
                'closed_at'        => $session->closed_at->toIso8601String(),
            ],
        ]);
    }

    // =========================================================================
    // 4. SINKRONISASI TRANSAKSI OFFLINE (BULK INSERT)
    // =========================================================================

    /**
     * POST /api/orders/sync
     * Menerima array transaksi dari React PWA yang terjadi saat offline,
     * lalu memprosesnya secara atomik dalam satu DB transaction.
     */
    public function syncOrders(SyncOrdersRequest $request): JsonResponse
    {
        $user    = $request->user();
        $session = PosSession::where('user_id', $user->id)
            ->where('state', 'open')
            ->first();

        if (! $session) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada sesi aktif. Buka sesi terlebih dahulu sebelum sinkronisasi.',
            ], 422);
        }

        $synced  = [];
        $skipped = [];
        $failed  = [];

        // ── Pre-load lookup maps (hindari N+1 query di dalam loop) ────────────
        $paymentMethodApiIds = collect($request->orders)
            ->pluck('payment_method_id')
            ->unique()->filter()->values();

        $productApiIds = collect($request->orders)
            ->flatMap(fn($o) => collect($o['items'])->pluck('product_id'))
            ->unique()->filter()->values();

        $paymentMethodMap = PaymentMethod::whereIn('api_id', $paymentMethodApiIds)
            ->pluck('id', 'api_id'); // ['uuid' => internal_id]

        $productMap = Product::whereIn('api_id', $productApiIds)
            ->pluck('id', 'api_id'); // ['uuid' => internal_id]

        // ── Tambahkan juga kolom `payment_method` (string enum) dari PaymentMethod ──
        $paymentMethodTypeMap = PaymentMethod::whereIn('api_id', $paymentMethodApiIds)
            ->pluck('code', 'api_id'); // ['uuid' => 'cash'/'qris'/...]

        DB::transaction(function () use (
            $request,
            $session,
            $user,
            $paymentMethodMap,
            $paymentMethodTypeMap,
            $productMap,
            &$synced,
            &$skipped,
            &$failed
        ) {
            foreach ($request->orders as $orderData) {
                $posReference = $orderData['pos_reference'];

                // ── Cek Duplikasi ─────────────────────────────────────────────
                if (Order::where('pos_reference', $posReference)->exists()) {
                    $skipped[] = $posReference;
                    continue;
                }

                try {
                    // ── Resolve payment method ────────────────────────────────
                    $pmApiId          = $orderData['payment_method_id'];
                    $paymentMethodId  = $paymentMethodMap[$pmApiId] ?? null;
                    $paymentMethodStr = $paymentMethodTypeMap[$pmApiId] ?? 'cash';

                    // ── Simpan Order Header ───────────────────────────────────
                    $order = Order::create([
                        'pos_reference'     => $posReference,
                        'pos_session_id'    => $session->id,
                        'cashier_id'        => null,
                        'customer_id'       => $orderData['customer_id'] ?? null,
                        'payment_method_id' => $paymentMethodId,
                        'payment_method'    => $paymentMethodStr,
                        'order_number'      => $orderData['order_number'] ?? null,
                        'order_date'        => $orderData['created_at'],
                        'total_price'       => $orderData['total_price'],
                        'discount'          => $orderData['discount'] ?? 0,
                        'discount_amount'   => $orderData['discount_amount'] ?? 0,
                        'total_payment'     => $orderData['total_payment'],
                        'cash_paid'         => $orderData['cash_paid'] ?? null,
                        'change_amount'     => $orderData['change_amount'] ?? 0,
                        'payment_status'    => $orderData['payment_status'],
                        'status'            => 'completed',
                        'created_at'        => $orderData['created_at'],
                        'updated_at'        => now(),
                    ]);

                    // ── Simpan Line Items & Kurangi Stok ─────────────────────
                    foreach ($orderData['items'] as $item) {
                        // Resolve product_id (UUID) → internal product_id
                        $productId = $productMap[$item['product_id']] ?? null;

                        if (! $productId) {
                            throw new \RuntimeException(
                                "Produk dengan id [{$item['product_id']}] tidak ditemukan."
                            );
                        }

                        $detail = OrderDetail::create([
                            'order_id'   => $order->id,
                            'product_id' => $productId,
                            'quantity'   => $item['quantity'],
                            'subtotal'   => $item['subtotal'],
                        ]);

                        StockMove::create([
                            'product_id'      => $productId,
                            'user_id'         => $user->id,
                            'order_detail_id' => $detail->id,
                            'quantity'        => $item['quantity'],
                            'type'            => 'out',
                            'reference'       => $order->order_number ?? $posReference,
                            'state'           => 'done',
                            'notes'           => 'Penjualan POS (sync offline)',
                        ]);
                    }

                    $synced[] = $posReference;
                } catch (\Throwable $e) {
                    $failed[] = [
                        'pos_reference' => $posReference,
                        'reason'        => $e->getMessage(),
                    ];
                }
            }
        });

        $hasFailures = count($failed) > 0;

        return response()->json([
            'success' => ! $hasFailures,
            'message' => $hasFailures
                ? 'Sinkronisasi selesai dengan beberapa kegagalan.'
                : 'Sinkronisasi berhasil.',
            'data'    => [
                'synced'  => $synced,
                'skipped' => $skipped,
                'failed'  => $failed,
            ],
        ], $hasFailures ? 207 : 200);
    }
}
