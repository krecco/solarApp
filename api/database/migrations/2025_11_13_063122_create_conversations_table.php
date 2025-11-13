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
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('subject')->nullable()->comment('Conversation subject/title');
            $table->string('status')->default('active')->comment('active, archived, closed');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable()->comment('Timestamp of last message');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_by_id');
            $table->index('last_message_at');
        });

        // Pivot table for conversation participants (many-to-many)
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable()->comment('Last time user read messages');
            $table->integer('unread_count')->default(0)->comment('Number of unread messages');
            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('conversations');
    }
};
