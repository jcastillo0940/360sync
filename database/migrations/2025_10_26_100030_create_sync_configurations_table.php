<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_configurations', function (Blueprint $table) {
            $table->id();
            
            $table->string('key')->unique();
            $table->string('category');
            $table->string('label');
            
            $table->text('value')->nullable();
            $table->text('encrypted_value')->nullable();
            $table->boolean('is_encrypted')->default(false);
            
            $table->enum('type', [
                'string',
                'integer',
                'boolean',
                'json',
                'url',
                'email',
                'password'
            ])->default('string');
            
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->integer('display_order')->default(0);
            
            $table->string('validation_rules')->nullable();
            $table->text('default_value')->nullable();
            
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_tested_at')->nullable();
            $table->boolean('test_passed')->nullable();
            
            $table->timestamps();
            
            $table->index(['category', 'is_visible']);
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_configurations');
    }
};
