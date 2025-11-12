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
        Schema::create('investments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Investor
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Plant relationship
            $table->foreignUuid('solar_plant_id')->constrained()->cascadeOnDelete();

            // Investment details
            $table->decimal('amount', 12, 2);
            $table->integer('duration_months');
            $table->decimal('interest_rate', 5, 2);
            $table->string('repayment_interval')->default('monthly'); // monthly, quarterly, annually

            // Status
            $table->string('status')->default('pending'); // pending, verified, active, completed, cancelled
            $table->string('contract_status')->nullable();

            // Verification
            $table->boolean('verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            // Documents
            $table->foreignUuid('file_container_id')->nullable()->constrained()->nullOnDelete();

            // Dates
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Calculated totals (cached for performance)
            $table->decimal('total_repayment', 12, 2)->nullable(); // Total amount to be repaid
            $table->decimal('total_interest', 12, 2)->nullable(); // Total interest
            $table->decimal('paid_amount', 12, 2)->default(0); // Amount paid so far

            // Notes
            $table->text('notes')->nullable();

            // Tracking
            $table->integer('rs')->default(0)->comment('Record status: 0=active, 99=deleted');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['solar_plant_id', 'status']);
            $table->index(['verified', 'status']);
            $table->index('rs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
