<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('cal_sync_enabled')->default(false)->after('cal_connected_at');
        });

        Schema::table('appointments', function (Blueprint $table): void {
            $table->string('cal_booking_id', 120)->nullable()->after('source');
            $table->index('cal_booking_id');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex(['cal_booking_id']);
            $table->dropColumn('cal_booking_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('cal_sync_enabled');
        });
    }
};
