<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('card_id');
            $table->uuid('product_id')->nullable();
            $table->string('full_name', 160);
            $table->string('phone', 40)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('interest', 255)->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 20)->default('scheduled');
            $table->text('notes')->nullable();
            $table->string('source', 30)->default('public');
            $table->timestamps();

            $table->foreign('card_id')->references('id')->on('cards')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->index(['user_id', 'starts_at']);
            $table->index(['card_id', 'starts_at']);
            $table->index(['status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
