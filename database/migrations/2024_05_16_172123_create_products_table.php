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
            $table->unsignedBigInteger('tag_id')->nullable();
            $table->string('title');
            $table->integer('code')->unique()->nullable();

            $table->unsignedInteger('price');
            $table->unsignedInteger('special_offer_price')->nullable();
            $table->unsignedSmallInteger('inventory')->default(1);
            $table->tinyInteger('discount_percent')->default(0);

            $table->json('discount_rules')->nullable();

            $table->string('sheet_file')->nullable();
            $table->longText('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->longText('Technical_description')->nullable();
            $table->longText('faq')->nullable();

            $table->unsignedInteger('item_sold')->default(0)->index();

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
