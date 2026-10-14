<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Document lines: quantity in the chosen unit (pieces or packages) and the same amount in pieces, which is what
     * moves in stock. Corrections may have negative quantities (shortage / write-off).
     */
    public function up(): void
    {
        Schema::create('stock_document_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('unit', 16);
            $table->integer('quantity');
            $table->integer('pieces');
            $table->unsignedBigInteger('price')->nullable();
            $table->bigInteger('amount')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_document_lines');
    }
};
