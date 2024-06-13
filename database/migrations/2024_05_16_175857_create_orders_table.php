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
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('user_address_id')->nullable();
            $table->unsignedBigInteger('shipping_method_id')->nullable();

            $table->unsignedBigInteger('total_price')->nullable();
            $table->enum('status', ['checkout', 'pending_payment', 'expired', 'user_canceled', 'payment_success', 'payment_failed', 'review', 'processing', 'completed', 'cancelled']);

            $table->string('short_address')->nullable();
            $table->string('short_shipping_data')->nullable();
            $table->string('payment_gateway', 20)->nullable();
            $table->boolean('use_wallet')->nullable();
            $table->unsignedBigInteger('wallet_price_used')->default(0);

            $table->timestamps();
            $table->index('updated_at');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('user_address_id')->references('id')->on('user_addresses');
            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods');
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
