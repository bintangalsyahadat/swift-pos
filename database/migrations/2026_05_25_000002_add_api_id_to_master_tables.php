<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Tabel master yang membutuhkan kolom api_id.
     * 'table' => nama tabel, 'nullable' => apakah kolom boleh null sementara (untuk data lama)
     */
    private array $tables = [
        'products',
        'brands',
        'categories',
        'sub_categories',
        'payment_methods',
        'cashiers', // model Terminal di kode = Cashier
    ];

    public function up(): void
    {
        // ── Langkah 1: Tambahkan kolom api_id sebagai NULLABLE dulu ──────────
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('api_id', 36)->nullable()->unique()->after('id');
            });
        }

        // ── Langkah 2: Isi api_id untuk semua baris lama yang sudah ada ──────
        foreach ($this->tables as $table) {
            $rows = DB::table($table)->whereNull('api_id')->pluck('id');

            foreach ($rows as $id) {
                DB::table($table)
                    ->where('id', $id)
                    ->update(['api_id' => (string) Str::uuid()]);
            }
        }

        // ── Langkah 3: Ubah kolom menjadi NOT NULL setelah semua terisi ──────
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('api_id', 36)->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('api_id');
            });
        }
    }
};
