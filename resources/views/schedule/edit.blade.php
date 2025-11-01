@extends('layouts.app')

@section('title', 'Edit Schedule')

@section('content')
<div class="mb-6">
    <div class="flex items-center space-x-4">
        <a href="{{ route('schedules.index') }}" class="text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Schedule</h1>
            <p class="text-gray-600 mt-1">Update automated workflow execution</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <form action="{{ route('schedules.update', $rule->id) }}" method="POST" class="p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Workflow <span class="text-red-500">*</span>
            </label>
            <select name="workflow_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select a workflow</option>
                @foreach($workflows as $workflow)
                    <option value="{{ $workflow->id }}" {{ old('workflow_id', $rule->workflow_id) == $workflow->id ? 'selected' : '' }}>
                        {{ $workflow->name }}
                    </option>
                @endforeach
            </select>
            @error('workflow_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Schedule Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $rule->name) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea name="description" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $rule->description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Action Type <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-4">
                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ old('action', $rule->action) === 'total' ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                    <input type="radio" name="action" value="total" {{ old('action', $rule->action) === 'total' ? 'checked' : '' }} required class="sr-only">
                    <div>
                        <div class="font-semibold text-gray-900">Total Sync</div>
                        <div class="text-sm text-gray-500">Synchronize all data</div>
                    </div>
                </label>

                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 transition {{ old('action', $rule->action) === 'partial' ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}">
                    <input type="radio" name="action" value="partial" {{ old('action', $rule->action) === 'partial' ? 'checked' : '' }} class="sr-only">
                    <div>
                        <div class="font-semibold text-gray-900">Partial Sync</div>
                        <div class="text-sm text-gray-500">Synchronize changes only</div>
                    </div>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Frequency <span class="text-red-500">*</span>
            </label>
            <select name="frequency" id="frequency" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="once" {{ old('frequency', $rule->frequency) == 'once' ? 'selected' : '' }}>Once</option>
                <option value="daily" {{ old('frequency', $rule->frequency) == 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly" {{ old('frequency', $rule->frequency) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ old('frequency', $rule->frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Execution Time <span class="text-red-500">*</span>
            </label>
            <input type="time" name="execution_time" value="{{ old('execution_time', $rule->execution_time) }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div id="daysOfWeek" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-2">Days of Week</label>
            <div class="grid grid-cols-7 gap-2">
                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                    <label class="flex items-center justify-center p-2 border rounded cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="days_of_week[]" value="{{ $day }}" class="mr-1">
                        <span class="text-sm">{{ substr($day, 0, 3) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div id="dayOfMonth" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-2">Day of Month</label>
            <input type="number" name="day_of_month" value="{{ old('day_of_month', $rule->day_of_month) }}" min="1" max="31"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $rule->is_enabled) ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                <span class="ml-2 text-sm font-medium text-gray-700">Enable schedule</span>
            </label>
        </div>

        <div class="flex justify-end space-x-4 pt-4 border-t">
            <a href="{{ route('schedules.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Update Schedule
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.getElementById('frequency').addEventListener('change', function() {
        const daysOfWeek = document.getElementById('daysOfWeek');
        const dayOfMonth = document.getElementById('dayOfMonth');
        
        daysOfWeek.style.display = this.value === 'weekly' ? 'block' : 'none';
        dayOfMonth.style.display = this.value === 'monthly' ? 'block' : 'none';
    });

    document.getElementById('frequency').dispatchEvent(new Event('change'));
</script>
@endpush
@endsection
