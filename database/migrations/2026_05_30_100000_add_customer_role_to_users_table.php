<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('business','admin','customer') NOT NULL DEFAULT 'business'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('business','admin') NOT NULL DEFAULT 'business'");
    }
};

