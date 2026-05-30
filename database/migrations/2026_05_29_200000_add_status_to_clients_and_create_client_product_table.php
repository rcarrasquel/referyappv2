<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('status', 40)->default('new')->after('state');
            $table->index('status');
        });

        Schema::create('client_product', function (Blueprint $table): void {
            $table->uuid('client_id');
            $table->uuid('product_id');
            $table->timestamps();

            $table->primary(['client_id', 'product_id']);
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_product');

        Schema::table('clients', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};

