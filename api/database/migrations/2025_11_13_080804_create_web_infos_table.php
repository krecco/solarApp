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
        Schema::create('web_infos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->text('content');
            $table->enum('type', ['news', 'info', 'page', 'announcement'])->default('news');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('author_id');
            $table->string('featured_image')->nullable();
            $table->json('meta')->nullable();
            $table->json('tags')->nullable();
            $table->string('category')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('rs')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['type', 'is_published']);
            $table->index('published_at');
            $table->index('is_featured');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_infos');
    }
};
