<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staff roles for the back office; existing admins become owners. Client accounts link to their Telegram customer.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('owner')->after('email');
            $table->foreignId('telegram_client_id')->nullable()->unique()->after('role')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('telegram_client_id');
            $table->dropColumn('role');
        });
    }
};
