<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stripe_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('publishable_key', 255)->nullable();
            $table->string('secret_key', 255)->nullable();
            $table->string('webhook_secret', 255)->nullable();
            $table->string('currency', 10)->default('usd');
            $table->unsignedInteger('monthly_price_cents')->default(900);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stripe_settings');
    }
};

