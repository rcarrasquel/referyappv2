<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->string('cal_sync_status', 20)->nullable()->after('cal_booking_id');
            $table->text('cal_sync_error')->nullable()->after('cal_sync_status');
            $table->timestamp('cal_synced_at')->nullable()->after('cal_sync_error');
            $table->index('cal_sync_status');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex(['cal_sync_status']);
            $table->dropColumn(['cal_sync_status', 'cal_sync_error', 'cal_synced_at']);
        });
    }
};

