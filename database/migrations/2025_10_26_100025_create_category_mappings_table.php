<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_mappings', function (Blueprint $table) {
            $table->id();
            
            // Niveles ICG (pueden ser de 1 a 5 niveles)
            $table->string('nivel1')->nullable(); // Ej: 1004
            $table->string('nivel2')->nullable(); // Ej: 10
            $table->string('nivel3')->nullable(); // Ej: 1
            $table->string('nivel4')->nullable(); // Ej: 2
            $table->string('nivel5')->nullable(); // Ej: null (raramente usado)
            
            // Clave ICG compuesta (se genera automáticamente)
            $table->string('icg_key')->unique(); // Ej: 1004-10-1-2
            
            // Información de Magento
            $table->string('magento_category_id');
            $table->string('magento_category_name')->nullable();
            $table->string('magento_category_path')->nullable(); // Ej: 1/2/3272/3281/3407/4835/4295
            
            // Metadata
            $table->integer('category_level')->default(1); // Nivel de profundidad (1-5)
            $table->integer('product_count')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('icg_key');
            $table->index('magento_category_id');
            $table->index(['nivel1', 'nivel2', 'nivel3', 'nivel4', 'nivel5']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_mappings');
    }
};