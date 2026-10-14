<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each order unit has its own tasnif.soliq.uz package code: package_code is for a single piece,
     * box_package_code for a whole package of box_quantity pieces.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('box_package_code', 20)->nullable()->after('package_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('box_package_code');
        });
    }
};
