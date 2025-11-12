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
        // User addresses
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('type')->default('billing'); // billing, shipping, property
            $table->string('street');
            $table->string('street_number')->nullable();
            $table->string('city');
            $table->string('postal_code');
            $table->string('country')->default('DE');

            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'type']);
        });

        // SEPA permissions/mandates
        Schema::create('user_sepa_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('iban');
            $table->string('bic')->nullable();
            $table->string('account_holder');
            $table->string('mandate_reference')->unique();
            $table->date('mandate_date');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        // Solar plant property owners (when different from investor)
        Schema::create('solar_plant_property_owners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('solar_plant_id')->constrained()->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('solar_plant_id');
        });

        // Extras/Add-ons catalog
        Schema::create('extras', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('default_price', 10, 2);
            $table->string('unit')->default('piece'); // piece, hour, service
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('is_active');
        });

        // Solar plant extras (many-to-many with pricing)
        Schema::create('solar_plant_extras', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('solar_plant_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('extra_id')->constrained('extras')->cascadeOnDelete();

            $table->decimal('price', 10, 2)->nullable();
            $table->integer('quantity')->default(1);

            $table->timestamps();

            $table->index('solar_plant_id');
            $table->index('extra_id');
        });

        // Campaigns
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft'); // draft, active, completed

            $table->timestamps();

            $table->index('status');
        });

        // Settings (key-value store)
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();

            $table->timestamps();
        });

        // Activity logs (audit trail)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // created, updated, deleted, verified, etc.
            $table->string('subject_type'); // SolarPlant, Investment, User, etc.
            $table->string('subject_id')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('action');
        });

        // Web info/pages
        Schema::create('web_info', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('slug')->unique();
            $table->string('title');
            $table->longText('content');
            $table->boolean('is_published')->default(false);

            $table->timestamps();

            $table->index(['is_published', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_info');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('solar_plant_extras');
        Schema::dropIfExists('extras');
        Schema::dropIfExists('solar_plant_property_owners');
        Schema::dropIfExists('user_sepa_permissions');
        Schema::dropIfExists('user_addresses');
    }
};
