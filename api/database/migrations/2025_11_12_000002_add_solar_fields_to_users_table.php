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
        Schema::table('users', function (Blueprint $table) {
            // Business/Personal info
            $table->string('title_prefix')->nullable()->after('name');
            $table->string('title_suffix')->nullable()->after('title_prefix');
            $table->string('phone_nr')->nullable()->after('email');
            $table->tinyInteger('gender')->nullable()->after('phone_nr'); // 0=other, 1=male, 2=female

            // Customer type
            $table->boolean('is_business')->default(false)->after('gender');
            $table->string('customer_type')->default('investor')->after('is_business'); // investor, plant_owner, both

            // Verification & status
            $table->boolean('user_files_verified')->default(false)->after('email_verified_at');
            $table->timestamp('user_verified_at')->nullable()->after('user_files_verified');

            // Document customization
            $table->text('document_extra_text_block_a')->nullable()->after('user_verified_at');
            $table->text('document_extra_text_block_b')->nullable()->after('document_extra_text_block_a');

            // Auto-generated customer number
            $table->unsignedInteger('customer_no')->unique()->nullable()->after('id');

            // Status tracking (0=active, 99=deleted/soft-deleted)
            $table->integer('status')->default(0)->after('customer_no');

            // Add indexes for common queries
            $table->index('customer_type');
            $table->index(['status', 'created_at']);
            $table->index('user_files_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['customer_type']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['user_files_verified']);

            $table->dropColumn([
                'title_prefix',
                'title_suffix',
                'phone_nr',
                'gender',
                'is_business',
                'customer_type',
                'user_files_verified',
                'user_verified_at',
                'document_extra_text_block_a',
                'document_extra_text_block_b',
                'customer_no',
                'status',
            ]);
        });
    }
};
