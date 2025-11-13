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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique()->comment('ISO 639-1 language code (e.g., en, de, es, fr, si)');
            $table->string('name', 100)->comment('English name of the language');
            $table->string('native_name', 100)->comment('Native name of the language');
            $table->string('flag_emoji', 10)->nullable()->comment('Flag emoji representation');
            $table->boolean('is_active')->default(true)->comment('Whether the language is currently available');
            $table->boolean('is_default')->default(false)->comment('Whether this is the default system language');
            $table->integer('sort_order')->default(0)->comment('Display order in language selection');
            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index('is_default');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
