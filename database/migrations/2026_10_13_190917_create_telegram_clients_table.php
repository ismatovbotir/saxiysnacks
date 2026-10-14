<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('telegram_clients', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->unsignedBigInteger('telegram_id')->unique();
            $table->string('telegram_username')->nullable();
            $table->string('language_code', 8)->default('uz');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone', 32);
            $table->string('company_name')->nullable();
            $table->string('vat_number', 32)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('address', 500)->nullable();
            $table->timestamp('location_updated_at')->nullable();
            $table->string('type', 16)->default('consumer')->index();
            $table->string('status', 16)->default('active')->index();
            $table->bigInteger('admin_message_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_clients');
    }
};
