<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignUuid('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->integer('rating');
            $table->string('title', 100)->nullable();
            $table->text('comment')->nullable();
            $table->text('pros')->nullable();
            $table->text('cons')->nullable();

            $table->boolean('is_verified')->default(false);
            $table->boolean('is_published')->default(true);

            $table->timestamps();

            $table->index(['vehicle_id', 'rating']);
            $table->unique(['rental_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_reviews');
    }
};
