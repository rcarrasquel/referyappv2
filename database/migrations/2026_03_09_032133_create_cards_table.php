<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            // UUID como primary key
            $table->uuid('id')->primary();
            $table->timestamps();
            
            // Relación con usuario
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Información básica
            $table->string('name');
            $table->string('username')->unique();
            $table->text('description')->nullable();

            // Imágenes
            $table->string('profile_image')->nullable();
            $table->string('header_image')->nullable();

            // Header
           $table->string('header_color')->default('#6DBE45');

            // Fondo
            $table->enum('background_type', ['color', 'gradient', 'image'])->default('color');

            $table->string('background_color')->default('#F5F5F5');
            $table->string('background_gradient')->nullable();
            $table->string('background_image')->nullable();

            // Estilo de botones
            $table->enum('button_style', ['rounded', 'normal', 'square'])->default('rounded');

            // Colores personalizados
            $table->string('text_color')->default('#111111');
            $table->string('button_background_color')->default('#6DBE45');
            $table->string('button_text_color')->default('#FFFFFF');
                        // Estado
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
