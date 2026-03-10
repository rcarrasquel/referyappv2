<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('card_id');
            $table->uuid('product_id')->nullable();
            $table->string('full_name', 160);
            $table->string('phone', 40)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('interest', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('new');
            $table->string('source', 30)->default('public');
            $table->timestamps();

            $table->foreign('card_id')->references('id')->on('cards')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->index(['user_id', 'created_at']);
            $table->index(['card_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
