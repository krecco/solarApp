<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();

            $table->enum('maintenance_type', ['routine', 'repair', 'inspection', 'cleaning']);
            $table->text('description');
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('performed_by', 100)->nullable();
            $table->dateTime('performed_at');
            $table->date('next_maintenance_date')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['vehicle_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance');
    }
};
