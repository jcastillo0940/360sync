<?php

namespace App\Jobs;

use App\Services\API\MagentoApiService;
use App\Models\MagentoCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMagentoCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public function handle()
    {
        try {
            $magentoApi = app(MagentoApiService::class);
            
            Log::info('Starting Magento categories sync...');
            
            $result = $magentoApi->getCategories();
            
            if (!$result['success']) {
                throw new \Exception('Failed to fetch categories: ' . ($result['error'] ?? 'Unknown error'));
            }

            $categories = $result['data'] ?? [];
            
            if (empty($categories)) {
                throw new \Exception('No categories returned from Magento API');
            }

            $synced = 0;
            $created = 0;
            $updated = 0;

            foreach ($categories as $categoryData) {
                // Validar que $categoryData es un array
                if (!is_array($categoryData)) {
                    Log::warning('Invalid category data: ' . json_encode($categoryData));
                    continue;
                }

                // Validar campos requeridos
                if (!isset($categoryData['id']) || !isset($categoryData['name'])) {
                    Log::warning('Missing required fields in category: ' . json_encode($categoryData));
                    continue;
                }

                $category = MagentoCategory::updateOrCreate(
                    ['category_id' => $categoryData['id']],
                    [
                        'name' => $categoryData['name'] ?? 'Unnamed',
                        'parent_id' => $categoryData['parent_id'] ?? null,
                        'level' => $categoryData['level'] ?? 1,
                        'path' => $categoryData['path'] ?? null,
                        'is_active' => $categoryData['is_active'] ?? true,
                        'position' => $categoryData['position'] ?? 0,
                        'product_count' => $categoryData['product_count'] ?? 0,
                    ]
                );

                if ($category->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
                
                $synced++;
            }

            Log::info("Magento categories sync completed: {$synced} total, {$created} created, {$updated} updated");

        } catch (\Exception $e) {
            Log::error('Magento categories sync failed: ' . $e->getMessage());
            throw $e;
        }
    }
}