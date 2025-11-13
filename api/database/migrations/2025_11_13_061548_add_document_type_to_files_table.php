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
        Schema::table('files', function (Blueprint $table) {
            $table->string('document_type', 50)->nullable()->after('file_type')
                ->comment('Specific document type for customer verification (identity_card, passport, etc.)');
            $table->boolean('is_required')->default(false)->after('document_type')
                ->comment('Whether this document type is required for the customer');
            $table->text('rejection_reason')->nullable()->after('verified_by_id')
                ->comment('Reason for document rejection if verification failed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'is_required', 'rejection_reason']);
        });
    }
};
