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
        Schema::create('stock_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('type', ['in', 'out']);
            $table->integer('quantity');
            $table->foreignId('order_detail_id')->nullable()->constrained('order_details')->nullOnDelete();
            $table->string('reference')->nullable();
            $table->enum('state', ['draft', 'done', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_moves');
    }
};
