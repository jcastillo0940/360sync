<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('magento_skus', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 191)->unique(); // ← Cambio: 191 en lugar de 255
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->index('sku'); // ← Este índice ahora funcionará
            $table->index('synced_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('magento_skus');
    }
};