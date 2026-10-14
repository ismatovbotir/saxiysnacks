<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Warehouse documents: receipts (incoming invoices), corrections and sales (outgoing invoices).
     * A document changes stock only once it is posted; cancelling a posted document removes its movements.
     */
    public function up(): void
    {
        Schema::create('stock_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16);
            $table->string('number', 20)->unique();
            $table->date('document_date')->index();
            $table->string('status', 16)->default('draft')->index();
            $table->string('counterparty')->nullable();
            $table->foreignId('order_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('total')->default(0);
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_documents');
    }
};
