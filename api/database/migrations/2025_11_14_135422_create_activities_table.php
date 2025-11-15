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
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable();
            $table->text('content')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->uuid('content_id')->nullable();
            $table->uuid('parent_content_id')->nullable();
            $table->string('content_type')->nullable();
            $table->string('notification_type')->nullable();
            $table->boolean('show_on_user_dashboard')->default(false);
            $table->string('filename')->nullable();

            $table->string('created_by')->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();

            $table->timestamp('t0')->useCurrent();
            $table->integer('rs')->default(0);

            // Indexes
            $table->index('t0');
            $table->index('user_id');
            $table->index('content_type');
            $table->index('show_on_user_dashboard');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
