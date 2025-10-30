<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magento_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id')->unique();
            $table->string('name');
            $table->integer('parent_id')->nullable();
            $table->integer('level')->default(1);
            $table->string('path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);
            $table->integer('product_count')->default(0);
            $table->timestamps();
            
            $table->index('category_id');
            $table->index('parent_id');
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magento_categories');
    }
};