<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('stripe_customer_id', 120)->nullable()->after('plan');
            $table->string('stripe_subscription_id', 120)->nullable()->after('stripe_customer_id');
            $table->string('stripe_subscription_status', 60)->nullable()->after('stripe_subscription_id');
            $table->string('stripe_price_id', 120)->nullable()->after('stripe_subscription_status');
            $table->timestamp('stripe_current_period_end')->nullable()->after('stripe_price_id');
        });

        DB::table('users')
            ->whereIn('plan', ['monthly', 'lifetime'])
            ->update(['plan' => 'business']);

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY plan ENUM('free','business') NOT NULL DEFAULT 'free'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY plan ENUM('free','monthly','lifetime') NOT NULL DEFAULT 'free'");
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'stripe_customer_id',
                'stripe_subscription_id',
                'stripe_subscription_status',
                'stripe_price_id',
                'stripe_current_period_end',
            ]);
        });
    }
};

