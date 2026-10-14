<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Money received from customers, usually against an order (resellers may also pay their balance in general).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('telegram_client_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('method', 16);
            $table->date('paid_on')->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
