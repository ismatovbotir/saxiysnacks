<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Business expenses by category, for the finance report.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category', 32)->index();
            $table->unsignedBigInteger('amount');
            $table->date('spent_on')->index();
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
        Schema::dropIfExists('expenses');
    }
};
