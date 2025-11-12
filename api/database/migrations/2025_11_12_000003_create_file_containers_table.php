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
        Schema::create('file_containers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // contracts, verification_docs, reports, plant_docs, investment_docs
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });

        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('file_container_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type');
            $table->bigInteger('size'); // bytes
            $table->string('extension');

            // Metadata
            $table->morphs('uploaded_by'); // User, Admin, etc.
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['file_container_id', 'created_at']);
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
        Schema::dropIfExists('file_containers');
    }
};
