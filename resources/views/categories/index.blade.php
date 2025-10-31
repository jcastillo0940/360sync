@extends('layouts.app')

@section('title', 'Category Mappings')

@section('content')
<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Category Mappings</h1>
                <p class="text-gray-600 mt-1">Map ICG categories to Magento categories</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('categories.export') }}" 
                   class="btn btn-white">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export
                </a>
                <button onclick="document.getElementById('syncForm').classList.toggle('hidden')" 
                        class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Sync from Magento
                </button>
            </div>
        </div>
    </div>

    <div id="syncForm" class="hidden mb-6">
        <form method="POST" action="{{ route('categories.sync') }}" 
              class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            @csrf
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sync Categories from Magento</h3>
            <p class="text-sm text-gray-600 mb-4">
                This will fetch all categories from Magento that have the ICG Category ID custom attribute set.
            </p>
            <div class="flex items-center space-x-4">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Start Sync
                </button>
                <button type="button" 
                        onclick="document.getElementById('syncForm').classList.add('hidden')"
                        class="btn btn-white">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-4">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[300px]">
                    <form method="GET" action="{{ route('categories.index') }}">
                        <input type="hidden" name="level" value="{{ request('level') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="text" 
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by ICG key or Magento category"
                               class="w-full border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                    </form>
                </div>

                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Level:</label>
                    <select onchange="window.location.href='{{ route('categories.index') }}?level=' + this.value + '&status={{ request('status') }}&search={{ request('search') }}'" 
                            class="border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                        <option value="">All Levels</option>
                        <option value="1" {{ request('level') == '1' ? 'selected' : '' }}>Level 1</option>
                        <option value="2" {{ request('level') == '2' ? 'selected' : '' }}>Level 2</option>
                        <option value="3" {{ request('level') == '3' ? 'selected' : '' }}>Level 3</option>
                        <option value="4" {{ request('level') == '4' ? 'selected' : '' }}>Level 4</option>
                        <option value="5" {{ request('level') == '5' ? 'selected' : '' }}>Level 5</option>
                    </select>
                </div>

                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Status:</label>
                    <select onchange="window.location.href='{{ route('categories.index') }}?status=' + this.value + '&level={{ request('level') }}&search={{ request('search') }}'" 
                            class="border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <form method="POST" action="{{ route('categories.sync-counts') }}">
                    @csrf
                    <button type="submit" class="btn btn-white text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Sync Counts
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ICG Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Magento Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Sync</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($mappings as $mapping)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $mapping->icg_key }}</div>
                            @if($mapping->notes)
                            <div class="text-xs text-gray-500">{{ Str::limit($mapping->notes, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge badge-info">
                                Level {{ $mapping->category_level }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $mapping->magento_category_name ?: 'ID: ' . $mapping->magento_category_id }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $mapping->magento_category_id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ number_format($mapping->product_count) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $mapping->last_synced_at ? $mapping->last_synced_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($mapping->is_active)
                                @if($mapping->product_count > 0)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-warning">No Products</span>
                                @endif
                            @else
                                <span class="badge badge-gray">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                            <form method="POST" action="{{ route('categories.toggle', $mapping->id) }}" class="inline-block">
                                @csrf
                                <button type="submit" 
                                        title="{{ $mapping->is_active ? 'Deactivate' : 'Activate' }}"
                                        class="inline-flex items-center justify-center w-8 h-8 {{ $mapping->is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50' }} rounded-lg transition">
                                    @if($mapping->is_active)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <p class="text-lg font-medium">No category mappings found</p>
                            <p class="text-sm text-gray-400 mt-1">Sync categories from Magento to get started</p>
                            <button onclick="document.getElementById('syncForm').classList.remove('hidden')" class="btn btn-primary mt-4 inline-flex">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Sync from Magento
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mappings->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $mappings->links() }}
        </div>
        @endif
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-600">Total Mappings</p>
            <p class="text-2xl font-bold text-gray-900">{{ $mappings->total() }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-600">Active</p>
            <p class="text-2xl font-bold text-green-600">{{ \App\Models\CategoryMapping::active()->count() }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-600">Level 1</p>
            <p class="text-2xl font-bold text-blue-600">{{ \App\Models\CategoryMapping::byLevel(1)->count() }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-600">Level 2+</p>
            <p class="text-2xl font-bold text-purple-600">{{ \App\Models\CategoryMapping::where('category_level', '>', 1)->count() }}</p>
        </div>
    </div>
</div>
@endsection