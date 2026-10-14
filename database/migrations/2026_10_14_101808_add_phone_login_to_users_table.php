<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phone sign-in: users get a phone (digits only, e.g. 998901234567) and the Telegram chat that verified it,
     * where one-time codes are sent. Email becomes optional; only users with an email may open the back office.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->bigInteger('telegram_chat_id')->nullable()->unique()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropUnique(['telegram_chat_id']);
            $table->dropColumn(['phone', 'telegram_chat_id']);
        });
    }
};
