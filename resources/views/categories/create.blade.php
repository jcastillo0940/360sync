@extends('layouts.app')

@section('title', 'Create Category Mapping')

@section('content')
<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create Category Mapping</h1>
                <p class="text-gray-600 mt-1">Map a new ICG category to a Magento category</p>
            </div>
            <a href="{{ route('categories.index') }}" class="btn btn-white">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            
            <div class="p-6 space-y-6">
                <!-- ICG Category Key -->
                <div>
                    <label for="icg_category_key" class="block text-sm font-medium text-gray-700 mb-2">
                        ICG Category Key <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="icg_category_key" 
                        id="icg_category_key" 
                        value="{{ old('icg_category_key') }}"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0244CD] focus:ring-[#0244CD] @error('icg_category_key') border-red-300 @enderror"
                        placeholder="e.g., ALIMENTOS"
                        required
                    >
                    @error('icg_category_key')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Unique identifier for the ICG category
                    </p>
                </div>

                <!-- ICG Category Type -->
                <div>
                    <label for="icg_category_type" class="block text-sm font-medium text-gray-700 mb-2">
                        ICG Category Type <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="icg_category_type" 
                        id="icg_category_type"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0244CD] focus:ring-[#0244CD] @error('icg_category_type') border-red-300 @enderror"
                        required
                    >
                        <option value="">Select a type...</option>
                        <option value="familia" {{ old('icg_category_type') == 'familia' ? 'selected' : '' }}>
                            Familia
                        </option>
                        <option value="subfamilia" {{ old('icg_category_type') == 'subfamilia' ? 'selected' : '' }}>
                            Subfamilia
                        </option>
                    </select>
                    @error('icg_category_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Whether this is a parent category (familia) or child category (subfamilia)
                    </p>
                </div>

                <!-- Magento Category ID -->
                <div>
                    <label for="magento_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Magento Category ID <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="magento_category_id" 
                        id="magento_category_id" 
                        value="{{ old('magento_category_id') }}"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0244CD] focus:ring-[#0244CD] @error('magento_category_id') border-red-300 @enderror"
                        placeholder="e.g., 123"
                        required
                    >
                    @error('magento_category_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        The corresponding category ID in Magento
                    </p>
                </div>

                <!-- Magento Category Name (Optional) -->
                <div>
                    <label for="magento_category_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Magento Category Name
                    </label>
                    <input 
                        type="text" 
                        name="magento_category_name" 
                        id="magento_category_name" 
                        value="{{ old('magento_category_name') }}"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0244CD] focus:ring-[#0244CD] @error('magento_category_name') border-red-300 @enderror"
                        placeholder="e.g., Food & Beverages"
                    >
                    @error('magento_category_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Optional: Descriptive name for the Magento category
                    </p>
                </div>

                <!-- Notes (Optional) -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes
                    </label>
                    <textarea 
                        name="notes" 
                        id="notes" 
                        rows="3"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#0244CD] focus:ring-[#0244CD] @error('notes') border-red-300 @enderror"
                        placeholder="Optional notes about this mapping..."
                    >{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        id="is_active" 
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        class="h-4 w-4 text-[#0244CD] focus:ring-[#0244CD] border-gray-300 rounded"
                    >
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                        Active (Enable this mapping immediately)
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                <a href="{{ route('categories.index') }}" class="btn btn-white">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create Mapping
                </button>
            </div>
        </form>
    </div>

    <!-- Help Card -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Tips for creating mappings</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>The <strong>ICG Category Key</strong> must be unique across all mappings</li>
                        <li>Use <strong>familia</strong> for parent categories and <strong>subfamilia</strong> for child categories</li>
                        <li>Make sure the <strong>Magento Category ID</strong> exists in your Magento store</li>
                        <li>You can add optional notes to document the purpose of this mapping</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection