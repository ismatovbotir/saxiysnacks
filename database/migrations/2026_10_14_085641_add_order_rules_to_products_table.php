<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ordering rules per customer type, in boxes: minimum order and the step after it (5, 10, 15… for min 5 / step 5).
     * Reseller values may stay empty to use the consumer values.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedSmallInteger('consumer_min_quantity')->default(1)->after('consumer_price');
            $table->unsignedSmallInteger('consumer_quantity_step')->default(1)->after('consumer_min_quantity');
            $table->unsignedSmallInteger('reseller_min_quantity')->nullable()->after('reseller_price');
            $table->unsignedSmallInteger('reseller_quantity_step')->nullable()->after('reseller_min_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['consumer_min_quantity', 'consumer_quantity_step', 'reseller_min_quantity', 'reseller_quantity_step']);
        });
    }
};
