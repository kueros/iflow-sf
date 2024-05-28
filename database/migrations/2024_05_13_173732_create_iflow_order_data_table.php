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
        Schema::create('iflow_order_data', function (Blueprint $table) {
            $table->id();
            $table->string('cli_id')->nullable();
            $table->string('order_id', 50);
            $table->string('shopId');
            $table->string('tracking_id', 50);
            $table->string('shipment_id', 50);
            $table->string('print_url', 200);
            $table->string('code', 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iflow_order_data');
    }
};
