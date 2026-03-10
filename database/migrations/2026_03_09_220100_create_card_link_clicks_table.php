<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('card_link_clicks', function (Blueprint $table) {
            $table->id();
            $table->uuid('card_id');
            $table->unsignedSmallInteger('link_index')->nullable();
            $table->string('link_title', 120)->nullable();
            $table->text('link_url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('browser', 120)->nullable();
            $table->string('os', 120)->nullable();
            $table->string('device_type', 40)->nullable();
            $table->string('session_id', 120)->nullable();
            $table->string('fingerprint', 64)->nullable();
            $table->string('accept_language', 255)->nullable();
            $table->string('referer', 2048)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('clicked_at')->useCurrent();
            $table->timestamps();

            $table->foreign('card_id')->references('id')->on('cards')->cascadeOnDelete();
            $table->index(['card_id', 'clicked_at']);
            $table->index(['card_id', 'link_index']);
            $table->index('clicked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_link_clicks');
    }
};
