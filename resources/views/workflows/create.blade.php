@extends('layouts.app')

@section('title', 'Create Workflow')

@section('content')
<div class="mb-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('workflows.index') }}" class="text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Workflow</h1>
            <p class="text-gray-600 mt-1">Configure a new synchronization workflow</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <form action="{{ route('workflows.store') }}" method="POST" class="p-6 space-y-6">
        @csrf

        <!-- Workflow Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Workflow Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Product Synchronization">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Description
            </label>
            <textarea name="description" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Describe what this workflow does">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Workflow Type -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Workflow Type <span class="text-red-500">*</span>
            </label>
            <select name="type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select type</option>
                <option value="product_sync" {{ old('type') == 'product_sync' ? 'selected' : '' }}>Product Sync</option>
                <option value="category_sync" {{ old('type') == 'category_sync' ? 'selected' : '' }}>Category Sync</option>
                <option value="inventory_sync" {{ old('type') == 'inventory_sync' ? 'selected' : '' }}>Inventory Sync</option>
                <option value="price_sync" {{ old('type') == 'price_sync' ? 'selected' : '' }}>Price Sync</option>
                <option value="order_sync" {{ old('type') == 'order_sync' ? 'selected' : '' }}>Order Sync</option>
                <option value="customer_sync" {{ old('type') == 'customer_sync' ? 'selected' : '' }}>Customer Sync</option>
            </select>
            @error('type')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Source -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Source <span class="text-red-500">*</span>
            </label>
            <select name="source" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select source</option>
                <option value="ICG" {{ old('source') == 'ICG' ? 'selected' : '' }}>ICG</option>
                <option value="Magento" {{ old('source') == 'Magento' ? 'selected' : '' }}>Magento</option>
                <option value="FTP" {{ old('source') == 'FTP' ? 'selected' : '' }}>FTP</option>
            </select>
            @error('source')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Destination -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Destination <span class="text-red-500">*</span>
            </label>
            <select name="destination" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select destination</option>
                <option value="ICG" {{ old('destination') == 'ICG' ? 'selected' : '' }}>ICG</option>
                <option value="Magento" {{ old('destination') == 'Magento' ? 'selected' : '' }}>Magento</option>
                <option value="FTP" {{ old('destination') == 'FTP' ? 'selected' : '' }}>FTP</option>
            </select>
            @error('destination')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Configuration (JSON) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Configuration (JSON) - Optional
            </label>
            <textarea name="config" rows="6"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                      placeholder='{
  "batch_size": 100,
  "timeout": 300,
  "retry_attempts": 3
}'>{{ old('config') }}</textarea>
            <p class="text-xs text-gray-500 mt-1">Advanced workflow settings in JSON format</p>
            @error('config')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Priority -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Priority
            </label>
            <select name="priority"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
            </select>
            @error('priority')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Active Status -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                <span class="ml-2 text-sm font-medium text-gray-700">Activate workflow immediately</span>
            </label>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-4 pt-4 border-t">
            <a href="{{ route('workflows.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Create Workflow
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Validar JSON en tiempo real
    const configTextarea = document.querySelector('textarea[name="config"]');
    
    configTextarea?.addEventListener('blur', function() {
        if (this.value.trim() === '') return;
        
        try {
            JSON.parse(this.value);
            this.classList.remove('border-red-500');
            this.classList.add('border-green-500');
        } catch (e) {
            this.classList.remove('border-green-500');
            this.classList.add('border-red-500');
            alert('Invalid JSON format: ' + e.message);
        }
    });
</script>
@endpush
@endsection