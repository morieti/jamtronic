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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('title');
            $table->integer('code')->unique();

            $table->unsignedInteger('price');
            $table->unsignedSmallInteger('inventory')->default(1);
            $table->tinyInteger('discount_percent')->default(0);
            $table->boolean('special_offer')->default(false);

            $table->json('discount_rules')->nullable();

            $table->longText('description')->nullable();
            $table->longText('Technical_description')->nullable();
            $table->longText('faq')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->foreign('brand_id')->references('id')->on('brands')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
