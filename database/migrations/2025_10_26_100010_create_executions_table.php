<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('executions', function (Blueprint $table) {
            $table->id();
            $table->string('job_id')->unique();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->enum('action', ['total', 'partial'])->default('total');
            $table->text('skus')->nullable();
            
            $table->string('date_filter')->default('none');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            
            $table->enum('status', [
                'pending',
                'running',
                'completed_success',
                'completed_success_no_ftp',
                'completed_empty',
                'failed'
            ])->default('pending');
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            
            $table->integer('total_items')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('skipped_count')->default(0);
            
            $table->string('csv_filename')->nullable();
            $table->string('csv_path')->nullable();
            $table->boolean('ftp_uploaded')->default(false);
            
            $table->text('result_message')->nullable();
            $table->json('error_details')->nullable();
            $table->json('configuration_snapshot')->nullable();
            
            $table->enum('trigger_type', ['manual', 'scheduled', 'api'])->default('manual');
            $table->foreignId('schedule_rule_id')->nullable()->constrained('schedule_rules')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['workflow_id', 'status']);
            $table->index(['created_at', 'status']);
            $table->index('job_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('executions');
    }
};
