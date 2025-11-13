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
        Schema::create('repayment_reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('repayment_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['upcoming', 'overdue', 'final_notice'])->default('upcoming');
            $table->integer('days_before_due')->nullable();
            $table->integer('days_overdue')->nullable();
            $table->timestamp('sent_at');
            $table->string('sent_via')->default('email');
            $table->string('recipient_email');
            $table->boolean('was_opened')->default(false);
            $table->timestamp('opened_at')->nullable();
            $table->text('message_content')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('rs')->default(0);
            $table->timestamps();

            $table->foreign('repayment_id')->references('id')->on('investment_repayments')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['repayment_id', 'type']);
            $table->index('sent_at');
            $table->index(['user_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repayment_reminders');
    }
};
