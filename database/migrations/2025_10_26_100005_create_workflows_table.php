<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('class_name');
            $table->text('description')->nullable();
            $table->string('icon')->default('refresh');
            $table->string('color')->default('blue');
            $table->boolean('is_active')->default(true);
            $table->boolean('supports_partial')->default(true);
            $table->boolean('supports_date_filter')->default(true);
            $table->json('configuration')->nullable();
            $table->integer('execution_count')->default(0);
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
