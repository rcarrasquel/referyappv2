<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('first_name', 120)->nullable()->after('identity_value');
            $table->string('last_name', 120)->nullable()->after('first_name');
            $table->string('address_1', 190)->nullable()->after('phone');
            $table->string('address_2', 190)->nullable()->after('address_1');
            $table->string('city', 120)->nullable()->after('address_2');
            $table->string('country', 120)->nullable()->after('city');
            $table->string('zip', 40)->nullable()->after('country');
            $table->string('state', 120)->nullable()->after('zip');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn([
                'first_name',
                'last_name',
                'address_1',
                'address_2',
                'city',
                'country',
                'zip',
                'state',
            ]);
        });
    }
};

