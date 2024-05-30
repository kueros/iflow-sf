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
        Schema::create('order_prod', function (Blueprint $table) {
            $table->id();
            $table->string('order_id',100);
            $table->string('line_items_id',100)->nullable();
            $table->string('line_items_quantity',4)->nullable();
            $table->string('line_items_price',50)->nullable();
            $table->integer('line_items_sku')->nullable();
            $table->integer('depth')->nullable();
            $table->integer('height')->nullable();
            $table->integer('width')->nullable();
        });
    }
  
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_prod');
    }
};
