<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove customer-specific columns from users table
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'customer_no',
                'customer_type',
                'is_business',
                'title_prefix',
                'title_suffix',
                'phone_nr',
                'gender',
                'user_files_verified',
                'user_verified_at',
                'document_extra_text_block_a',
                'document_extra_text_block_b',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     * Re-add customer-specific columns to users table
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('customer_no')->nullable();
            $table->enum('customer_type', ['investor', 'plant_owner', 'both'])->default('investor');
            $table->boolean('is_business')->default(false);
            $table->string('title_prefix')->nullable();
            $table->string('title_suffix')->nullable();
            $table->string('phone_nr')->nullable();
            $table->string('gender')->nullable();
            $table->boolean('user_files_verified')->default(false);
            $table->timestamp('user_verified_at')->nullable();
            $table->text('document_extra_text_block_a')->nullable();
            $table->text('document_extra_text_block_b')->nullable();
        });
    }
};
