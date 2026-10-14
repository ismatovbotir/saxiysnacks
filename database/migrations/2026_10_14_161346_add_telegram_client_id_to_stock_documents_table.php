<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A sales invoice can be linked to a customer picked from the client list (the counterparty name stays as text).
     */
    public function up(): void
    {
        Schema::table('stock_documents', function (Blueprint $table) {
            $table->foreignId('telegram_client_id')->nullable()->after('counterparty')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('telegram_client_id');
        });
    }
};
