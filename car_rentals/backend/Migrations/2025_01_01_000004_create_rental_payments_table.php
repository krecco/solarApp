<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('rental_id')->constrained('rentals')->cascadeOnDelete();

            $table->enum('payment_type', ['deposit', 'rental', 'damage', 'refund'])->default('rental');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50);
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('rental_id');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_payments');
    }
};
