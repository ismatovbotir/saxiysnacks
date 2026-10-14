<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Product identification codes: GS1 barcode (GTIN) and the Uzbek tax classifier codes
     * (MXIK / ИКПУ class code and package code from tasnif.soliq.uz) used on e-invoices.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('gtin', 14)->nullable()->unique()->after('sku');
            $table->string('class_code', 17)->nullable()->after('gtin');
            $table->string('package_code', 20)->nullable()->after('class_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['gtin']);
            $table->dropColumn(['gtin', 'class_code', 'package_code']);
        });
    }
};
