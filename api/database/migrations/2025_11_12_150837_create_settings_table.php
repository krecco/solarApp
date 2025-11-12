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
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('group', 100)->index(); // general, email, investment, payment, notification, security
            $table->string('key', 255)->index(); // setting key
            $table->text('value')->nullable(); // setting value
            $table->string('type', 50)->default('string'); // string, integer, boolean, json, decimal
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Whether setting is visible to non-admin users
            $table->integer('rs')->default(0); // Soft delete flag
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint on group + key
            $table->unique(['group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
