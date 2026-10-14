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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('telegram_client_id')->constrained()->restrictOnDelete();
            $table->string('status', 16)->default('new')->index();
            $table->string('price_type', 16);
            $table->unsignedBigInteger('total');
            $table->decimal('delivery_latitude', 10, 7);
            $table->decimal('delivery_longitude', 10, 7);
            $table->string('delivery_address', 500)->nullable();
            $table->text('comment')->nullable();
            $table->bigInteger('admin_message_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
