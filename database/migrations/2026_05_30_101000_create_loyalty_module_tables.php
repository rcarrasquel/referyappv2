<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_programs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('business_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('stamps_required');
            $table->string('reward', 255);
            $table->date('start_date')->nullable();
            $table->date('expires_at')->nullable();
            $table->enum('status', ['draft', 'active', 'inactive', 'expired'])->default('draft');
            $table->timestamps();

            $table->index(['business_user_id', 'status']);
        });

        Schema::create('loyalty_cards', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->foreignId('business_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('stamps_current')->default(0);
            $table->unsignedInteger('stamps_required');
            $table->enum('status', ['active', 'completed', 'redeemed', 'inactive'])->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('loyalty_programs')->cascadeOnDelete();
            $table->unique(['program_id', 'customer_user_id'], 'loyalty_cards_unique_program_customer');
            $table->index(['business_user_id', 'status']);
        });

        Schema::create('loyalty_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('card_id');
            $table->uuid('program_id');
            $table->foreignId('business_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['card_created', 'stamp_added', 'stamp_removed', 'card_completed', 'reward_redeemed']);
            $table->integer('stamp_delta')->default(0);
            $table->unsignedInteger('stamps_before')->default(0);
            $table->unsignedInteger('stamps_after')->default(0);
            $table->string('meta', 255)->nullable();
            $table->timestamps();

            $table->foreign('card_id')->references('id')->on('loyalty_cards')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('loyalty_programs')->cascadeOnDelete();
            $table->index(['program_id', 'created_at']);
        });

        Schema::create('loyalty_qr_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('program_id');
            $table->foreignId('customer_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 120)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('invalidated_at')->nullable();
            $table->string('issued_device', 120)->nullable();
            $table->timestamps();

            $table->foreign('program_id')->references('id')->on('loyalty_programs')->cascadeOnDelete();
            $table->index(['customer_user_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_qr_tokens');
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_cards');
        Schema::dropIfExists('loyalty_programs');
    }
};

