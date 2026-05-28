<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('cal_api_key')->nullable()->after('stripe_current_period_end');
            $table->string('cal_username')->nullable()->after('cal_api_key');
            $table->string('cal_event_type_slug')->nullable()->after('cal_username');
            $table->timestamp('cal_connected_at')->nullable()->after('cal_event_type_slug');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['cal_api_key', 'cal_username', 'cal_event_type_slug', 'cal_connected_at']);
        });
    }
};
