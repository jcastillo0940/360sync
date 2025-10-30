<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\SyncConfiguration;

$config = SyncConfiguration::getMagentoApiConfig();

echo "Testing Magento products...\n\n";

// Sin filtros
$response1 = Http::withHeaders([
    'Authorization' => 'Bearer ' . $config['api_token'],
])
->get($config['base_url'] . '/rest/V1/products', [
    'searchCriteria[pageSize]' => 1
]);
echo "Total products (no filters): " . ($response1->json()['total_count'] ?? 'N/A') . "\n";

// Solo activos
$response2 = Http::withHeaders([
    'Authorization' => 'Bearer ' . $config['api_token'],
])
->get($config['base_url'] . '/rest/V1/products', [
    'searchCriteria[pageSize]' => 1,
    'searchCriteria[filterGroups][0][filters][0][field]' => 'status',
    'searchCriteria[filterGroups][0][filters][0][value]' => 1,
    'searchCriteria[filterGroups][0][filters][0][condition_type]' => 'eq',
]);
echo "Total active products: " . ($response2->json()['total_count'] ?? 'N/A') . "\n";

// Activos + visibility 4
$response3 = Http::withHeaders([
    'Authorization' => 'Bearer ' . $config['api_token'],
])
->get($config['base_url'] . '/rest/V1/products', [
    'searchCriteria[pageSize]' => 1,
    'searchCriteria[filterGroups][0][filters][0][field]' => 'status',
    'searchCriteria[filterGroups][0][filters][0][value]' => 1,
    'searchCriteria[filterGroups][0][filters][0][condition_type]' => 'eq',
    'searchCriteria[filterGroups][1][filters][0][field]' => 'visibility',
    'searchCriteria[filterGroups][1][filters][0][value]' => 4,
    'searchCriteria[filterGroups][1][filters][0][condition_type]' => 'eq',
]);
echo "Total active + visibility=4: " . ($response3->json()['total_count'] ?? 'N/A') . "\n";

// Tipos de productos
$response4 = Http::withHeaders([
    'Authorization' => 'Bearer ' . $config['api_token'],
])
->get($config['base_url'] . '/rest/V1/products', [
    'searchCriteria[pageSize]' => 100,
    'searchCriteria[filterGroups][0][filters][0][field]' => 'status',
    'searchCriteria[filterGroups][0][filters][0][value]' => 1,
    'searchCriteria[filterGroups][0][filters][0][condition_type]' => 'eq',
]);

$types = [];
foreach ($response4->json()['items'] ?? [] as $item) {
    $types[$item['type_id']] = ($types[$item['type_id']] ?? 0) + 1;
}

echo "\nProduct types breakdown:\n";
foreach ($types as $type => $count) {
    echo "  {$type}: {$count}\n";
}