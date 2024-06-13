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
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['express', 'express_option', 'post', 'in_person']);
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('price_caption')->nullable();
            $table->unsignedInteger('price')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('capacity')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('shipping_methods')->onDelete('cascade');
            $table->index(['starts_at', 'ends_at']);

            $table->unique(['type', 'parent_id', 'starts_at', 'ends_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
