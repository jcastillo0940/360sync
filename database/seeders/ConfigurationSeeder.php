<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Configuration;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configurations = [
            // ICG API Configuration
            ['key' => 'icg_api_url', 'value' => 'https://api.icg.com', 'type' => 'string', 'description' => 'ICG API Base URL'],
            ['key' => 'icg_api_username', 'value' => '', 'type' => 'string', 'description' => 'ICG API Username'],
            ['key' => 'icg_api_password', 'value' => '', 'type' => 'password', 'description' => 'ICG API Password'],
            ['key' => 'icg_api_token', 'value' => '', 'type' => 'string', 'description' => 'ICG API Token (optional)'],
            ['key' => 'icg_timeout', 'value' => '30', 'type' => 'number', 'description' => 'ICG API Timeout in seconds'],
            ['key' => 'icg_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable ICG API'],

            // Magento API Configuration
            ['key' => 'magento_api_url', 'value' => '', 'type' => 'string', 'description' => 'Magento API Base URL'],
            ['key' => 'magento_api_token', 'value' => '', 'type' => 'string', 'description' => 'Magento API Bearer Token'],
            ['key' => 'magento_store_code', 'value' => 'default', 'type' => 'string', 'description' => 'Magento Store Code'],
            ['key' => 'magento_timeout', 'value' => '30', 'type' => 'number', 'description' => 'Magento API Timeout in seconds'],
            ['key' => 'magento_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable Magento API'],

            // FTP Configuration
            ['key' => 'ftp_host', 'value' => '', 'type' => 'string', 'description' => 'FTP Server Host'],
            ['key' => 'ftp_port', 'value' => '21', 'type' => 'number', 'description' => 'FTP Server Port'],
            ['key' => 'ftp_username', 'value' => '', 'type' => 'string', 'description' => 'FTP Username'],
            ['key' => 'ftp_password', 'value' => '', 'type' => 'password', 'description' => 'FTP Password'],
            ['key' => 'ftp_root', 'value' => '/', 'type' => 'string', 'description' => 'FTP Root Directory'],
            ['key' => 'ftp_enabled', 'value' => 'false', 'type' => 'boolean', 'description' => 'Enable FTP'],

            // Sync Process Configuration
            ['key' => 'sync_batch_size', 'value' => '100', 'type' => 'number', 'description' => 'Batch size for sync operations'],
            ['key' => 'sync_timeout', 'value' => '300', 'type' => 'number', 'description' => 'Sync timeout in seconds'],
            ['key' => 'sync_retry_attempts', 'value' => '3', 'type' => 'number', 'description' => 'Number of retry attempts'],
            ['key' => 'sync_retry_delay', 'value' => '60', 'type' => 'number', 'description' => 'Delay between retries in seconds'],
            ['key' => 'sync_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Enable sync process'],

            // Logging Configuration
            ['key' => 'logs_retention_days', 'value' => '30', 'type' => 'number', 'description' => 'Log retention period in days'],

            // Notification Configuration
            ['key' => 'notifications_email', 'value' => '', 'type' => 'string', 'description' => 'Email for notifications'],
            ['key' => 'notifications_enabled', 'value' => 'false', 'type' => 'boolean', 'description' => 'Enable notifications'],
        ];

        foreach ($configurations as $config) {
            Configuration::updateOrCreate(
                ['key' => $config['key']],
                [
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'description' => $config['description'] ?? null,
                ]
            );
        }

        $this->command->info('✅ Configuration seeded successfully with ' . count($configurations) . ' items');
    }
}