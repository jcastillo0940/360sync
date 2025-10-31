<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
