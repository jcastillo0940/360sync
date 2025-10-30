import os
import subprocess
from datetime import datetime

# Configuración
migrations_path = "database/migrations"
base_timestamp = datetime.now().strftime("%Y_%m_%d")

# Definición de migraciones con su contenido
migrations = {
    "create_workflows_table": '''<?php

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
''',
    
    "create_executions_table": '''<?php

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
''',

    "create_execution_logs_table": '''<?php

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
''',

    "create_schedule_rules_table": '''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->enum('action', ['total', 'partial'])->default('total');
            $table->text('skus')->nullable();
            
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->time('execution_time');
            $table->integer('day_of_week')->nullable();
            $table->integer('day_of_month')->nullable();
            
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->integer('run_count')->default(0);
            
            $table->json('configuration')->nullable();
            $table->text('description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['workflow_id', 'is_enabled']);
            $table->index('next_run_at');
            $table->index(['execution_time', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_rules');
    }
};
''',

    "create_category_mappings_table": '''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_mappings', function (Blueprint $table) {
            $table->id();
            
            $table->string('icg_category_key')->unique();
            $table->string('icg_category_type')->default('familia');
            
            $table->string('magento_category_id');
            $table->string('magento_category_name')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->integer('product_count')->default(0);
            $table->timestamp('last_synced_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->json('additional_config')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('icg_category_key');
            $table->index('magento_category_id');
            $table->index(['is_active', 'icg_category_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_mappings');
    }
};
''',

    "create_sync_configurations_table": '''<?php

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
'''
}

def create_migrations():
    print("🚀 Generando migraciones para 360Sync...\n")
    
    # Verificar si existe la carpeta de migraciones
    if not os.path.exists(migrations_path):
        print(f"❌ Error: La carpeta {migrations_path} no existe.")
        return
    
    counter = 100000
    
    for migration_name, content in migrations.items():
        counter += 5
        timestamp = f"{base_timestamp}_{counter:06d}"
        filename = f"{timestamp}_{migration_name}.php"
        filepath = os.path.join(migrations_path, filename)
        
        # Escribir el archivo
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        
        print(f"✅ Creada: {filename}")
    
    print(f"\n🎉 {len(migrations)} migraciones creadas exitosamente!")
    print("\n📌 Siguiente paso: Ejecuta el comando:")
    print("   php artisan migrate\n")

if __name__ == "__main__":
    create_migrations()