<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table): void {
            $table->string('phone', 60)->nullable()->after('description');
            $table->string('email', 190)->nullable()->after('phone');
            $table->string('address', 255)->nullable()->after('email');
            $table->string('google_maps_url', 2048)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'email', 'address', 'google_maps_url']);
        });
    }
};
