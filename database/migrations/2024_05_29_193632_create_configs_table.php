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
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->string('urlroot');
            $table->string('cli_id');
            $table->string('cli_pass');
            $table->string('re_dir_url');
            $table->string('fi_logs');
            $table->string('scope');
            $table->string('callback_url_carrier');
            $table->string('webhook_address_orders_create');
            $table->string('webhook_address_orders_paid');
            $table->string('webhook_address_orders_cancelled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configs');
    }
};
