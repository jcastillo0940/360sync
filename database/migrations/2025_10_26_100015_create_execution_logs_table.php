<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('execution_id')->constrained('executions')->onDelete('cascade');
            
            $table->enum('level', ['DEBUG', 'INFO', 'SUCCESS', 'WARNING', 'ERROR', 'CRITICAL'])->default('INFO');
            $table->text('message');
            $table->json('context')->nullable();
            
            $table->string('sku')->nullable();
            $table->integer('current_page')->nullable();
            $table->integer('total_pages')->nullable();
            $table->integer('progress_percentage')->nullable();
            
            $table->timestamp('logged_at')->useCurrent();
            
            $table->index(['execution_id', 'level']);
            $table->index('logged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('execution_logs');
    }
};
