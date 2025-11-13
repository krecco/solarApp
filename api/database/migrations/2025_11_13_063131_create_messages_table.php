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
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->comment('Message content');
            $table->string('type')->default('text')->comment('text, system, file_attachment');
            $table->json('attachments')->nullable()->comment('File attachments metadata');
            $table->boolean('is_read')->default(false)->comment('Has message been read by all participants');
            $table->timestamp('read_at')->nullable()->comment('When message was read');
            $table->timestamps();
            $table->softDeletes();

            $table->index('conversation_id');
            $table->index('sender_id');
            $table->index('is_read');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
