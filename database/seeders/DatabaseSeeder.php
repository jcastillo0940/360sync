<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Iniciando seeders...');
        $this->command->newLine();

        $this->call([
            UserSeeder::class,
            ConfigurationSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Seeders completados exitosamente');
    }
}