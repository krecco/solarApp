<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Vehicle identification
            $table->string('vin', 17)->unique()->nullable();
            $table->string('make', 50);
            $table->string('model', 50);
            $table->integer('year');
            $table->string('color', 30)->nullable();
            $table->string('license_plate', 20)->unique();

            // Classification
            $table->enum('category', ['economy', 'compact', 'midsize', 'luxury', 'suv', 'van']);
            $table->enum('transmission', ['manual', 'automatic']);
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric', 'hybrid']);

            // Specifications
            $table->integer('seats')->default(5);
            $table->integer('doors')->default(4);
            $table->decimal('mileage', 10, 2)->default(0);
            $table->json('features')->nullable();

            // Pricing
            $table->decimal('daily_rate', 8, 2);
            $table->decimal('weekly_rate', 8, 2)->nullable();
            $table->decimal('monthly_rate', 8, 2)->nullable();
            $table->decimal('security_deposit', 8, 2);

            // Availability
            $table->enum('status', ['available', 'rented', 'maintenance', 'retired'])->default('available');
            $table->string('location', 100);

            // Ownership
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();

            // Documents & images
            $table->uuid('file_container_id')->nullable();

            // Additional info
            $table->text('description')->nullable();
            $table->json('multilang_data')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'category']);
            $table->index(['location', 'status']);
            $table->index(['make', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
