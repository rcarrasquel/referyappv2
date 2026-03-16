<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_checkout_session_id', 120)->nullable()->unique();
            $table->string('stripe_invoice_id', 120)->nullable()->unique();
            $table->string('stripe_payment_intent_id', 120)->nullable()->index();
            $table->string('stripe_subscription_id', 120)->nullable()->index();
            $table->unsignedInteger('amount_cents')->default(0);
            $table->string('currency', 10)->default('usd');
            $table->string('status', 50)->default('pending');
            $table->string('description', 255)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_transactions');
    }
};

