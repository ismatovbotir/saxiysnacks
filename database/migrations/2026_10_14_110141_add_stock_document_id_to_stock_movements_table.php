<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Movements created by posting a document point back to it.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('stock_document_id')->nullable()->after('order_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_document_id');
        });
    }
};
