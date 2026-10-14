<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Website orders: guests leave a name and phone (a manager calls back), so they have no Telegram customer
     * and no map location.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source', 16)->default('telegram')->after('uuid')->index();
            $table->foreignId('telegram_client_id')->nullable()->change();
            $table->string('customer_name')->nullable()->after('telegram_client_id');
            $table->string('customer_phone', 32)->nullable()->after('customer_name');
            $table->decimal('delivery_latitude', 10, 7)->nullable()->change();
            $table->decimal('delivery_longitude', 10, 7)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropColumn(['source', 'customer_name', 'customer_phone']);
        });
    }
};
