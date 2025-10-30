@extends('layouts.app')

@section('title', 'Create Schedule')

@section('content')
<div class="mb-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('schedule.index') }}" class="text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Schedule</h1>
            <p class="text-gray-600 mt-1">Set up automated workflow execution</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <form action="{{ route('schedule.store') }}" method="POST" class="p-6 space-y-6">
        @csrf

        <!-- Workflow Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Workflow <span class="text-red-500">*</span>
            </label>
            <select name="workflow_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select a workflow</option>
                @foreach($workflows as $workflow)
                    <option value="{{ $workflow->id }}" {{ old('workflow_id') == $workflow->id ? 'selected' : '' }}>
                        {{ $workflow->name }}
                    </option>
                @endforeach
            </select>
            @error('workflow_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Schedule Name -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Schedule Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Daily Product Sync">
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
                      placeholder="Optional description">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Action Type -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Action Type <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-4">
                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                       :class="{'border-blue-500 bg-blue-50': action === 'total', 'border-gray-300': action !== 'total'}"
                       x-data="{ action: '{{ old('action', 'total') }}' }">
                    <input type="radio" name="action" value="total" x-model="action" required class="sr-only">
                    <div>
                        <div class="font-semibold text-gray-900">Total Sync</div>
                        <div class="text-sm text-gray-500">Synchronize all data</div>
                    </div>
                </label>

                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition"
                       :class="{'border-blue-500 bg-blue-50': action === 'partial', 'border-gray-300': action !== 'partial'}"
                       x-data="{ action: '{{ old('action', 'total') }}' }">
                    <input type="radio" name="action" value="partial" x-model="action" class="sr-only">
                    <div>
                        <div class="font-semibold text-gray-900">Partial Sync</div>
                        <div class="text-sm text-gray-500">Synchronize changes only</div>
                    </div>
                </label>
            </div>
            @error('action')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Frequency -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Frequency <span class="text-red-500">*</span>
            </label>
            <select name="frequency" id="frequency" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="once" {{ old('frequency') == 'once' ? 'selected' : '' }}>Once</option>
                <option value="daily" {{ old('frequency') == 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly" {{ old('frequency') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
            @error('frequency')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Execution Time -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Execution Time <span class="text-red-500">*</span>
            </label>
            <input type="time" name="execution_time" value="{{ old('execution_time', '00:00') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('execution_time')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Days of Week (for weekly frequency) -->
        <div id="daysOfWeek" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Days of Week
            </label>
            <div class="grid grid-cols-7 gap-2">
                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                    <label class="flex items-center justify-center p-2 border rounded cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="days_of_week[]" value="{{ $day }}" class="mr-1">
                        <span class="text-sm">{{ substr($day, 0, 3) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Day of Month (for monthly frequency) -->
        <div id="dayOfMonth" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Day of Month
            </label>
            <input type="number" name="day_of_month" value="{{ old('day_of_month', 1) }}" min="1" max="31"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Start Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Start Date
            </label>
            <input type="date" name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('start_date')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- End Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                End Date (Optional)
            </label>
            <input type="date" name="end_date" value="{{ old('end_date') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('end_date')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Enable Schedule -->
        <div>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', true) ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                <span class="ml-2 text-sm font-medium text-gray-700">Enable schedule immediately</span>
            </label>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-4 pt-4 border-t">
            <a href="{{ route('schedule.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Create Schedule
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Show/hide fields based on frequency
    document.getElementById('frequency').addEventListener('change', function() {
        const daysOfWeek = document.getElementById('daysOfWeek');
        const dayOfMonth = document.getElementById('dayOfMonth');
        
        daysOfWeek.style.display = this.value === 'weekly' ? 'block' : 'none';
        dayOfMonth.style.display = this.value === 'monthly' ? 'block' : 'none';
    });

    // Trigger on page load
    document.getElementById('frequency').dispatchEvent(new Event('change'));
</script>
@endpush
@endsection