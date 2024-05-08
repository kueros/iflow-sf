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
        Schema::create('carrier_services', function (Blueprint $table) {
            $table->id();
            $table->string('carrierServiceId');
            $table->string('shopId');
            $table->string('callbackUrl');
            $table->string('nombre');
            $table->string('tipo')->nullable();
            $table->string('state')->default('activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrier_services');
    }
};
