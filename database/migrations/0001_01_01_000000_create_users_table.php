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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('mobile')->unique();

            $table->bigInteger('wallet_balance')->default(0);

            $table->string('email')->unique()->nullable();
            $table->string('national_code')->unique()->nullable();
            $table->tinyInteger('dob')->nullable();
            $table->tinyInteger('mob')->nullable();
            $table->tinyInteger('yob')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
