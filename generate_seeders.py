import os

seeders_path = "database/seeders"

# WorkflowSeeder
workflow_seeder = '''<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Carbon\\Carbon;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $workflows = [
            [
                'name' => 'Price Update',
                'slug' => 'price_update',
                'class_name' => 'App\\\\Services\\\\Workflows\\\\PriceUpdateWorkflow',
                'description' => 'Synchronizes product prices from ICG to Magento (Tarifa 12)',
                'icon' => 'currency-dollar',
                'color' => 'green',
                'is_active' => true,
                'supports_partial' => true,
                'supports_date_filter' => true,
                'configuration' => json_encode([
                    'tariff_id' => 12,
                    'csv_filename' => 'update_productos.csv'
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Offer Update',
                'slug' => 'offer_update',
                'class_name' => 'App\\\\Services\\\\Workflows\\\\OfferUpdateWorkflow',
                'description' => 'Synchronizes special prices and promotional offers',
                'icon' => 'tag',
                'color' => 'orange',
                'is_active' => true,
                'supports_partial' => true,
                'supports_date_filter' => true,
                'configuration' => json_encode([
                    'tariff_id' => 12,
                    'csv_filename' => 'update_ofertas.csv'
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Stock Synchronization',
                'slug' => 'stock_update',
                'class_name' => 'App\\\\Services\\\\Workflows\\\\StockUpdateWorkflow',
                'description' => 'Synchronizes inventory levels across multiple warehouses',
                'icon' => 'cube',
                'color' => 'blue',
                'is_active' => true,
                'supports_partial' => true,
                'supports_date_filter' => true,
                'configuration' => json_encode([
                    'csv_filename' => 'update_stock.csv',
                    'warehouse_mapping' => [
                        'B01' => 1, 'B02' => 2, 'B03' => 3, 'B04' => 4,
                        'B05' => 5, 'B06' => 6, 'B07' => 7, 'B08' => 8,
                        'B09' => 9, 'B10' => 10, 'B11' => 11, 'B12' => 12, 'B13' => 13
                    ]
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Product Creation',
                'slug' => 'product_creation',
                'class_name' => 'App\\\\Services\\\\Workflows\\\\ProductCreationWorkflow',
                'description' => 'Creates new products in Magento from ICG data',
                'icon' => 'plus-circle',
                'color' => 'purple',
                'is_active' => true,
                'supports_partial' => true,
                'supports_date_filter' => true,
                'configuration' => json_encode([
                    'csv_filename' => 'create_productos.csv',
                    'default_attribute_set_id' => 4,
                    'default_tax_class_id' => 2
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Category Synchronization',
                'slug' => 'category_sync',
                'class_name' => 'App\\\\Services\\\\Workflows\\\\CategorySyncWorkflow',
                'description' => 'Synchronizes product categories between systems',
                'icon' => 'folder',
                'color' => 'indigo',
                'is_active' => true,
                'supports_partial' => false,
                'supports_date_filter' => false,
                'configuration' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Image Synchronization',
                'slug' => 'image_sync',
                'class_name' => 'App\\\\Services\\\\Workflows\\\\ImageSyncWorkflow',
                'description' => 'Synchronizes product images from Google Drive to Magento',
                'icon' => 'photograph',
                'color' => 'pink',
                'is_active' => true,
                'supports_partial' => true,
                'supports_date_filter' => false,
                'configuration' => json_encode([
                    'google_drive_folder_id' => '',
                    'image_naming' => 'sku'
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('workflows')->insert($workflows);

        $this->command->info('✅ Workflows seeded successfully!');
    }
}
'''

# CategoryMappingSeeder
category_mapping_seeder = '''<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Carbon\\Carbon;

class CategoryMappingSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $mappings = [
            ['icg_category_key' => 'CARNES ROJAS', 'magento_category_id' => '12', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'CARNES BLANCAS', 'magento_category_id' => '13', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'POLLO', 'magento_category_id' => '13', 'icg_category_type' => 'subfamilia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'PESCADO', 'magento_category_id' => '14', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'MARISCOS', 'magento_category_id' => '14', 'icg_category_type' => 'subfamilia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'LACTEOS', 'magento_category_id' => '15', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'QUESOS', 'magento_category_id' => '15', 'icg_category_type' => 'subfamilia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'YOGURT', 'magento_category_id' => '15', 'icg_category_type' => 'subfamilia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'PANADERIA', 'magento_category_id' => '16', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'PAN', 'magento_category_id' => '16', 'icg_category_type' => 'subfamilia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'FRUTAS', 'magento_category_id' => '17', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'VERDURAS', 'magento_category_id' => '18', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'VEGETALES', 'magento_category_id' => '18', 'icg_category_type' => 'subfamilia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'EMBUTIDOS', 'magento_category_id' => '19', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'BEBIDAS', 'magento_category_id' => '20', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'SNACKS', 'magento_category_id' => '21', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'DULCES', 'magento_category_id' => '22', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'LIMPIEZA', 'magento_category_id' => '23', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['icg_category_key' => 'CUIDADO PERSONAL', 'magento_category_id' => '24', 'icg_category_type' => 'familia', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('category_mappings')->insert($mappings);

        $this->command->info('✅ Category mappings seeded successfully!');
    }
}
'''

# SyncConfigurationSeeder
sync_config_seeder = '''<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Crypt;
use Carbon\\Carbon;

class SyncConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $configs = [
            // ICG API
            ['key' => 'icg_api_url', 'category' => 'api', 'label' => 'ICG API URL', 'value' => env('ICG_API_URL', ''), 'type' => 'url', 'is_required' => true, 'is_visible' => true, 'display_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'icg_api_user', 'category' => 'api', 'label' => 'ICG API User', 'value' => env('ICG_API_USER', ''), 'type' => 'string', 'is_required' => true, 'is_visible' => true, 'display_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'icg_api_password', 'category' => 'api', 'label' => 'ICG API Password', 'encrypted_value' => env('ICG_API_PASSWORD') ? Crypt::encryptString(env('ICG_API_PASSWORD')) : null, 'is_encrypted' => true, 'type' => 'password', 'is_required' => true, 'is_visible' => true, 'display_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            
            // Magento API
            ['key' => 'magento_base_url', 'category' => 'api', 'label' => 'Magento Base URL', 'value' => env('MAGENTO_BASE_URL', ''), 'type' => 'url', 'is_required' => true, 'is_visible' => true, 'display_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'magento_api_token', 'category' => 'api', 'label' => 'Magento API Token', 'encrypted_value' => env('MAGENTO_API_TOKEN') ? Crypt::encryptString(env('MAGENTO_API_TOKEN')) : null, 'is_encrypted' => true, 'type' => 'password', 'is_required' => true, 'is_visible' => true, 'display_order' => 11, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'magento_store_id', 'category' => 'api', 'label' => 'Magento Store ID', 'value' => env('MAGENTO_STORE_ID', '1'), 'type' => 'integer', 'is_required' => true, 'is_visible' => true, 'display_order' => 12, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'magento_batch_size', 'category' => 'api', 'label' => 'Magento Batch Size', 'value' => env('MAGENTO_BATCH_SIZE', '50'), 'type' => 'integer', 'is_required' => false, 'is_visible' => true, 'display_order' => 13, 'created_at' => $now, 'updated_at' => $now],
            
            // FTP
            ['key' => 'ftp_enabled', 'category' => 'ftp', 'label' => 'FTP Enabled', 'value' => env('FTP_ENABLED', 'true'), 'type' => 'boolean', 'is_required' => false, 'is_visible' => true, 'display_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ftp_server', 'category' => 'ftp', 'label' => 'FTP Server', 'value' => env('FTP_SERVER', ''), 'type' => 'string', 'is_required' => false, 'is_visible' => true, 'display_order' => 21, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ftp_port', 'category' => 'ftp', 'label' => 'FTP Port', 'value' => env('FTP_PORT', '21'), 'type' => 'integer', 'is_required' => false, 'is_visible' => true, 'display_order' => 22, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ftp_username', 'category' => 'ftp', 'label' => 'FTP Username', 'value' => env('FTP_USERNAME', ''), 'type' => 'string', 'is_required' => false, 'is_visible' => true, 'display_order' => 23, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'ftp_password', 'category' => 'ftp', 'label' => 'FTP Password', 'encrypted_value' => env('FTP_PASSWORD') ? Crypt::encryptString(env('FTP_PASSWORD')) : null, 'is_encrypted' => true, 'type' => 'password', 'is_required' => false, 'is_visible' => true, 'display_order' => 24, 'created_at' => $now, 'updated_at' => $now],
            
            // Process
            ['key' => 'concurrent_pages', 'category' => 'process', 'label' => 'Concurrent Pages', 'value' => '97', 'type' => 'integer', 'is_required' => false, 'is_visible' => true, 'display_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'max_pages', 'category' => 'process', 'label' => 'Max Pages', 'value' => '2000', 'type' => 'integer', 'is_required' => false, 'is_visible' => true, 'display_order' => 31, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'filter_by_webvisb', 'category' => 'process', 'label' => 'Filter by WEBVISB', 'value' => 'true', 'type' => 'boolean', 'is_required' => false, 'is_visible' => true, 'display_order' => 32, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('sync_configurations')->insert($configs);

        $this->command->info('✅ Sync configurations seeded successfully!');
    }
}
'''

# DatabaseSeeder
database_seeder = '''<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WorkflowSeeder::class,
            CategoryMappingSeeder::class,
            SyncConfigurationSeeder::class,
        ]);
    }
}
'''

def create_seeders():
    print("🌱 Generando seeders para 360Sync...\\n")
    
    seeders = {
        'WorkflowSeeder.php': workflow_seeder,
        'CategoryMappingSeeder.php': category_mapping_seeder,
        'SyncConfigurationSeeder.php': sync_config_seeder,
        'DatabaseSeeder.php': database_seeder,
    }
    
    for filename, content in seeders.items():
        filepath = os.path.join(seeders_path, filename)
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"✅ Creado: {filename}")
    
    print(f"\\n🎉 {len(seeders)} seeders creados exitosamente!")
    print("\\n📌 Siguiente paso: Ejecuta el comando:")
    print("   php artisan db:seed\\n")

if __name__ == "__main__":
    create_seeders()