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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->timestamp('order_date');
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['new', 'processing', 'completed', 'cancelled'])->default('new');
            $table->integer('discount')->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_payment', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
