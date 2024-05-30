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
        Schema::create('order_data', function (Blueprint $table) {
            $table->id();
            $table->string('order_id',50);
            $table->string('first_name',100);
            $table->string('last_name',100);
            $table->string('address1',50);
            $table->string('address2',8)->nullable();
            $table->string('phone',50);
            $table->string('city',25);
            $table->string('zip',25);
            $table->string('province',25);
            $table->string('country',10);
            $table->string('name',50);
            $table->string('province_code',125);
            $table->string('note',200);

            $table->timestamps();
        });
    }
  
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_data');
    }
};
