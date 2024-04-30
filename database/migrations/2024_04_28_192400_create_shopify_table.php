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
        Schema::create('shopify', function (Blueprint $table) {
            $table->id();
            $table->string('hmac')->nullable();
            $table->string('host')->nullable();
            $table->string('shop');
            $table->string('state')->nullable();
            $table->string('fapiusr');
            $table->string('fapiclave');
            $table->string('code')->nullable();
            $table->string('access_token')->nullable();
            $table->json('token')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopify');
    }
};
