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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['referral', 'seasonal', 'bonus', 'promotional'])->default('promotional');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('bonus_amount', 10, 2)->default(0);
            $table->decimal('min_investment_amount', 10, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('current_uses')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable();
            $table->text('terms')->nullable();
            $table->string('code')->unique()->nullable();
            $table->integer('rs')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index('start_date');
            $table->index('end_date');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
