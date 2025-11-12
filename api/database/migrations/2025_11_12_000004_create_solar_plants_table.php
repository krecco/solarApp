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
        Schema::create('solar_plants', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Basic info
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('DE');

            // Technical specifications
            $table->decimal('nominal_power', 10, 2); // kWp
            $table->decimal('annual_production', 12, 2)->nullable(); // kWh
            $table->decimal('consumption', 10, 2)->nullable(); // kWh
            $table->decimal('peak_power', 10, 2)->nullable(); // kWp

            // Financial
            $table->decimal('total_cost', 12, 2);
            $table->decimal('investment_needed', 12, 2)->nullable();
            $table->decimal('kwh_price', 8, 4)->nullable(); // Price per kWh
            $table->integer('contract_duration_years')->default(20);
            $table->decimal('interest_rate', 5, 2)->nullable();

            // Forecast & calculations
            $table->json('monthly_forecast')->nullable(); // Monthly production forecast
            $table->json('repayment_calculation')->nullable(); // Stored calculation data

            // Status & lifecycle
            $table->string('status')->default('draft'); // draft, active, funded, operational, completed, cancelled
            $table->date('start_date')->nullable();
            $table->date('operational_date')->nullable();
            $table->date('end_date')->nullable();

            // Ownership
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Plant owner/customer
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete(); // Assigned manager

            // Documents & contracts
            $table->foreignUuid('file_container_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('contracts_signed')->default(false);
            $table->timestamp('contract_signed_at')->nullable();

            // Tracking
            $table->integer('rs')->default(0)->comment('Record status: 0=active, 99=deleted');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['manager_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('rs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solar_plants');
    }
};
