<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();

            // Display info
            $table->string('name');                        // "QRIS", "Virtual Account BCA"
            $table->string('code')->unique();              // slug: "qris", "va_bca", "cash"
            $table->text('description')->nullable();
            $table->string('icon')->nullable();            // path to icon image

            // Classification
            $table->enum('type', [
                'cash',
                'card',
                'qr_code',
                'virtual_account',
                'ewallet',
                'over_the_counter',
            ])->default('cash');

            // Xendit integration (nullable = offline / manual)
            $table->boolean('is_online')->default(false);  // true = uses Xendit API
            $table->string('xendit_channel_type')->nullable();   // QR_CODE, VIRTUAL_ACCOUNT, EWALLET
            $table->string('xendit_channel_code')->nullable();   // ID_QRIS, BCA, ID_OVO, etc.
            $table->json('xendit_channel_properties')->nullable(); // extra config per channel

            // Fees
            $table->enum('fee_type', ['flat', 'percentage'])->nullable();
            $table->decimal('fee_value', 15, 2)->nullable(); // flat in IDR or % e.g. 0.70

            // Status & ordering
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
