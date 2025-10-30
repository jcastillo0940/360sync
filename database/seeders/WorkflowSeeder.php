<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $workflows = [
            [
                'name' => 'Price Update',
                'slug' => 'price_update',
                'class_name' => 'App\\Services\\Workflows\\PriceUpdateWorkflow',
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
                'class_name' => 'App\\Services\\Workflows\\OfferUpdateWorkflow',
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
                'class_name' => 'App\\Services\\Workflows\\StockUpdateWorkflow',
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
                'class_name' => 'App\\Services\\Workflows\\ProductCreationWorkflow',
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
                'class_name' => 'App\\Services\\Workflows\\CategorySyncWorkflow',
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
                'class_name' => 'App\\Services\\Workflows\\ImageSyncWorkflow',
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
