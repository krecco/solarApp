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
        Schema::create('investment_repayments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('investment_id')->constrained()->cascadeOnDelete();

            // Repayment details
            $table->decimal('amount', 12, 2);
            $table->decimal('principal', 12, 2);
            $table->decimal('interest', 12, 2);

            // Dates
            $table->date('due_date');
            $table->date('paid_date')->nullable();

            // Status
            $table->string('status')->default('pending'); // pending, paid, overdue, cancelled

            // Payment details
            $table->string('payment_method')->nullable(); // bank_transfer, sepa, cash
            $table->string('reference_number')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['investment_id', 'due_date']);
            $table->index(['status', 'due_date']);
            $table->index('paid_date');
        });

        Schema::create('solar_plant_repayment_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('solar_plant_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->string('repayment_type'); // principal, interest, combined
            $table->string('status')->default('pending'); // pending, paid, overdue, cancelled

            $table->timestamps();

            $table->index(['solar_plant_id', 'due_date']);
            $table->index(['status', 'due_date']);
        });

        Schema::create('solar_plant_repayment_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('solar_plant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('solar_plant_repayment_data_id')->nullable()->constrained('solar_plant_repayment_data')->nullOnDelete();

            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('payment_method')->nullable(); // bank_transfer, sepa, etc.
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['solar_plant_id', 'payment_date']);
        });

        Schema::create('solar_plant_repayment_log_reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('solar_plant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('solar_plant_repayment_data_id')->nullable()->constrained('solar_plant_repayment_data')->nullOnDelete();

            $table->date('reminder_date');
            $table->boolean('sent')->default(false);
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['sent', 'reminder_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solar_plant_repayment_log_reminders');
        Schema::dropIfExists('solar_plant_repayment_logs');
        Schema::dropIfExists('solar_plant_repayment_data');
        Schema::dropIfExists('investment_repayments');
    }
};
