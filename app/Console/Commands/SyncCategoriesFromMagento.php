<?php

namespace App\Console\Commands;

use App\Models\CategoryMapping;
use App\Services\API\MagentoApiService;
use Illuminate\Console\Command;

class SyncCategoriesFromMagento extends Command
{
    protected $signature = 'magento:sync-categories 
                            {--force : Force sync even if category already exists}
                            {--with-counts : Sync product counts after syncing categories}';

    protected $description = 'Sync category mappings from Magento store (reads ICG custom attribute)';

    protected $magentoService;

    public function __construct(MagentoApiService $magentoService)
    {
        parent::__construct();
        $this->magentoService = $magentoService;
    }

    public function handle()
    {
        $this->info('?? Starting category synchronization from Magento...');
        $this->newLine();
        
        try {
            // Test de conexión
            $this->info('Testing Magento connection...');
            $connectionTest = $this->magentoService->testConnection();
            
            if (!$connectionTest['success']) {
                $this->error('? Failed to connect to Magento API');
                $this->error($connectionTest['message']);
                return Command::FAILURE;
            }
            
            $this->info("? Connected to Magento ({$connectionTest['stores_count']} stores found)");
            $this->newLine();

            // Obtener todas las categorías
            $this->info('Fetching categories from Magento...');
            $result = $this->magentoService->getAllCategories();
            
            if (!$result['success']) {
                $this->error('? Failed to fetch categories from Magento');
                $this->error($result['error']);
                return Command::FAILURE;
            }

            $categories = $result['data'];
            $totalCategories = count($categories);
            
            $this->info("? Found {$totalCategories} categories in Magento");
            $this->newLine();

            $created = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];

            $progressBar = $this->output->createProgressBar($totalCategories);
            $progressBar->setFormat('verbose');
            $progressBar->start();

            foreach ($categories as $category) {
                try {
                    // Buscar el custom attribute ICG
                    $icgCategoryId = $this->magentoService->getCategoryCustomAttribute(
                        $category, 
                        'icg_category_id'
                    );
                    
                    // Si no tiene ICG ID, saltarlo
                    if (!$icgCategoryId || empty($icgCategoryId)) {
                        $skipped++;
                        $progressBar->advance();
                        continue;
                    }

                    // Parsear el ICG ID para extraer los niveles
                    $levels = $this->parseIcgKey($icgCategoryId);
                    
                    // Buscar si ya existe el mapeo
                    $existing = CategoryMapping::where('icg_key', $icgCategoryId)->first();

                    $data = [
                        'nivel1' => $levels[0] ?? null,
                        'nivel2' => $levels[1] ?? null,
                        'nivel3' => $levels[2] ?? null,
                        'nivel4' => $levels[3] ?? null,
                        'nivel5' => $levels[4] ?? null,
                        'magento_category_id' => (string) $category['id'],
                        'magento_category_name' => $category['name'] ?? null,
                        'magento_category_path' => $category['path'] ?? null,
                        'is_active' => $category['is_active'] ?? true,
                        'last_synced_at' => now(),
                    ];

                    if ($existing) {
                        if ($this->option('force')) {
                            $existing->update($data);
                            $updated++;
                        } else {
                            // Solo actualizar algunos campos sin forzar
                            $existing->update([
                                'magento_category_name' => $data['magento_category_name'],
                                'magento_category_path' => $data['magento_category_path'],
                                'is_active' => $data['is_active'],
                                'last_synced_at' => now(),
                            ]);
                            $updated++;
                        }
                    } else {
                        CategoryMapping::create($data);
                        $created++;
                    }

                } catch (\Exception $e) {
                    $errors[] = [
                        'category_id' => $category['id'] ?? 'unknown',
                        'category_name' => $category['name'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            // Mostrar resultados
            $this->info('? Synchronization completed!');
            $this->newLine();
            
            $this->table(
                ['Action', 'Count'],
                [
                    ['? Created', $created],
                    ['? Updated', $updated],
                    ['? Skipped (No ICG)', $skipped],
                    ['? Errors', count($errors)],
                    ['? Total', $totalCategories],
                ]
            );

            // Mostrar errores si hay
            if (count($errors) > 0) {
                $this->newLine();
                $this->warn('? Errors found:');
                $this->table(
                    ['Category ID', 'Category Name', 'Error'],
                    array_map(function($error) {
                        return [
                            $error['category_id'],
                            $error['category_name'],
                            \Illuminate\Support\Str::limit($error['error'], 50)
                        ];
                    }, $errors)
                );
            }

            // Sincronizar conteos si se solicita
            if ($this->option('with-counts')) {
                $this->newLine();
                $this->info('Syncing product counts...');
                $this->call('magento:sync-product-counts');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('? Critical error syncing categories: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Parsear el ICG key para extraer los niveles
     * Ej: "1004-10-1-2" => ["1004", "10", "1", "2"]
     */
    protected function parseIcgKey(string $icgKey): array
    {
        // Limpiar el string
        $icgKey = trim($icgKey);
        
        // Si es DEFAULT o vacío
        if (empty($icgKey) || strtoupper($icgKey) === 'DEFAULT') {
            return ['DEFAULT'];
        }

        // Dividir por guiones
        return explode('-', $icgKey);
    }
}