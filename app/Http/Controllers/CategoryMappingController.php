<?php

namespace App\Http\Controllers;

use App\Models\CategoryMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CategoryMappingController extends Controller
{
    public function index(Request $request)
    {
        $query = CategoryMapping::query();

        if ($request->filled('search')) {
            $query->searchByIcg($request->search);
        }

        if ($request->filled('level')) {
            $query->byLevel($request->level);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $mappings = $query->orderBy('category_level')
            ->orderBy('icg_key')
            ->paginate(20);

        return view('categories.index', compact('mappings'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nivel1' => 'required|string',
            'nivel2' => 'nullable|string',
            'nivel3' => 'nullable|string',
            'nivel4' => 'nullable|string',
            'nivel5' => 'nullable|string',
            'magento_category_id' => 'required|string',
            'magento_category_name' => 'nullable|string',
            'magento_category_path' => 'nullable|string',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $mapping = CategoryMapping::create($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', "Category mapping {$mapping->icg_key} created successfully!");
    }

    public function edit(CategoryMapping $category)
    {
        return view('categories.edit', ['mapping' => $category]);
    }

    public function update(Request $request, CategoryMapping $category)
    {
        $validated = $request->validate([
            'nivel1' => 'required|string',
            'nivel2' => 'nullable|string',
            'nivel3' => 'nullable|string',
            'nivel4' => 'nullable|string',
            'nivel5' => 'nullable|string',
            'magento_category_id' => 'required|string',
            'magento_category_name' => 'nullable|string',
            'magento_category_path' => 'nullable|string',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', "Category mapping {$category->icg_key} updated successfully!");
    }

    public function destroy(CategoryMapping $category)
    {
        $icgKey = $category->icg_key;
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', "Category mapping {$icgKey} deleted successfully!");
    }

    public function toggle(CategoryMapping $category)
    {
        $category->is_active = !$category->is_active;
        $category->save();

        $status = $category->is_active ? 'activated' : 'deactivated';
        
        return redirect()
            ->route('categories.index')
            ->with('success', "Category mapping {$category->icg_key} has been {$status}!");
    }
public function sync()
{
    try {
        // Despachar el job
        \App\Jobs\SyncMagentoCategoriesJob::dispatch();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category sync started in background. This may take a few minutes.');

    } catch (\Exception $e) {
        return redirect()
            ->route('categories.index')
            ->with('error', 'Sync failed: ' . $e->getMessage());
    }
}
    public function syncCounts()
    {
        try {
            Artisan::call('magento:sync-product-counts');
            
            return redirect()
                ->route('categories.index')
                ->with('success', 'Product counts synchronized successfully!');
                
        } catch (\Exception $e) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Error syncing counts: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        return redirect()
            ->route('categories.index')
            ->with('info', 'Import functionality not implemented yet.');
    }

    public function export()
    {
        return redirect()
            ->route('categories.index')
            ->with('info', 'Export functionality not implemented yet.');
    }

    public function getIcgCategories()
    {
        return response()->json([]);
    }

    public function getMagentoCategories()
    {
        return response()->json([]);
    }
}