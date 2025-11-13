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
        Schema::create('customer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Customer identification
            $table->string('customer_no')->nullable();
            $table->enum('customer_type', ['investor', 'plant_owner', 'both'])->default('investor');

            // Personal/Business information
            $table->boolean('is_business')->default(false);
            $table->string('title_prefix')->nullable(); // Dr., Prof., etc.
            $table->string('title_suffix')->nullable(); // MBA, PhD, etc.
            $table->string('phone_nr')->nullable();
            $table->string('gender')->nullable();

            // Document/Verification
            $table->boolean('user_files_verified')->default(false);
            $table->timestamp('user_verified_at')->nullable();
            $table->text('document_extra_text_block_a')->nullable();
            $table->text('document_extra_text_block_b')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('customer_no');
            $table->index('customer_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_profiles');
    }
};
