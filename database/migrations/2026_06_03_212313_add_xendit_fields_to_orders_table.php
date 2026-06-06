<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // ID invoice / charge dari Xendit (digunakan untuk polling status)
            $table->string('xendit_invoice_id')->nullable()->after('payment_status');

            // External ID yang dikirim ke Xendit (digunakan untuk mencocokkan webhook)
            $table->string('xendit_external_id')->nullable()->after('xendit_invoice_id');

            // Data spesifik per channel
            $table->text('xendit_qr_string')->nullable()->after('xendit_external_id');    // QRIS string
            $table->string('xendit_va_number')->nullable()->after('xendit_qr_string');    // nomor VA
            $table->string('xendit_va_bank')->nullable()->after('xendit_va_number');      // kode bank VA
            $table->string('xendit_checkout_url')->nullable()->after('xendit_va_bank');   // link e-wallet/OTC
            $table->string('xendit_payment_code')->nullable()->after('xendit_checkout_url'); // kode OTC
            $table->timestamp('xendit_expires_at')->nullable()->after('xendit_payment_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'xendit_invoice_id',
                'xendit_external_id',
                'xendit_qr_string',
                'xendit_va_number',
                'xendit_va_bank',
                'xendit_checkout_url',
                'xendit_payment_code',
                'xendit_expires_at',
            ]);
        });
    }
};
