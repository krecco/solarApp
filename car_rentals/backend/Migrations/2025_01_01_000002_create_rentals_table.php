<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('rental_number', 20)->unique();

            // Renter
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Vehicle
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();

            // Dates & duration
            $table->dateTime('pickup_date');
            $table->dateTime('return_date');
            $table->dateTime('actual_pickup_date')->nullable();
            $table->dateTime('actual_return_date')->nullable();

            // Pricing
            $table->decimal('daily_rate', 8, 2);
            $table->integer('total_days');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('insurance_fee', 10, 2)->default(0);
            $table->decimal('extras_total', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('security_deposit', 8, 2);

            // Payment
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'failed'])->default('pending');
            $table->string('payment_method', 50)->nullable();
            $table->dateTime('payment_date')->nullable();

            // Status
            $table->enum('status', ['draft', 'pending', 'verified', 'confirmed', 'active', 'completed', 'overdue', 'cancelled', 'rejected'])->default('draft');
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            // Mileage tracking
            $table->decimal('pickup_mileage', 10, 2)->nullable();
            $table->decimal('return_mileage', 10, 2)->nullable();
            $table->decimal('mileage_limit', 10, 2)->nullable();
            $table->decimal('excess_mileage', 10, 2)->nullable();

            // Condition
            $table->text('pickup_condition')->nullable();
            $table->text('return_condition')->nullable();
            $table->text('damage_report')->nullable();
            $table->decimal('damage_cost', 10, 2)->nullable();

            // Documents
            $table->uuid('file_container_id')->nullable();
            $table->string('document_language', 5)->default('en');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['vehicle_id', 'pickup_date', 'return_date']);
            $table->index(['status', 'pickup_date']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
