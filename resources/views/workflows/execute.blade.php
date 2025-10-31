@extends('layouts.app')

@section('title', 'Execute Workflow')

@section('content')
<div x-data="executeWorkflow()" class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('workflows.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Workflows
        </a>
        <h1 class="text-3xl font-bold text-gray-900">Execute {{ $workflow->name }}</h1>
        <p class="text-gray-600 mt-1">{{ $workflow->description }}</p>
    </div>

    <form method="POST" action="{{ route('workflows.process-execution', $workflow->id) }}" class="space-y-6">
        @csrf

        <!-- Step 1: Select Scope -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Select the scope for execution</h2>
            <p class="text-sm text-gray-600 mb-6">
                Manual Executions Enable On-Demand Workflow Runs in Addition to Scheduled Recurring Executions.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Partial -->
                <label class="relative cursor-pointer">
                    <input type="radio" 
                           name="action" 
                           value="partial" 
                           x-model="action"
                           class="peer sr-only">
                    <div class="p-6 border-2 rounded-lg transition peer-checked:border-[#0244CD] peer-checked:bg-blue-50">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-gray-400 peer-checked:text-[#0244CD] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Partial</h3>
                                <p class="text-sm text-gray-600">
                                    Executes only the unit data indicated. You will need either the data identifiers number or a file that specifies this information.
                                </p>
                            </div>
                        </div>
                    </div>
                </label>

                <!-- Total -->
                <label class="relative cursor-pointer">
                    <input type="radio" 
                           name="action" 
                           value="total" 
                           x-model="action"
                           class="peer sr-only"
                           checked>
                    <div class="p-6 border-2 rounded-lg transition peer-checked:border-[#0244CD] peer-checked:bg-blue-50">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-gray-400 peer-checked:text-[#0244CD] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Total</h3>
                                <p class="text-sm text-gray-600">
                                    This option executes the workflow considering all unit data to be updated between systems.
                                </p>
                            </div>
                        </div>
                    </div>
                </label>
            </div>

            <!-- Advanced Configuration -->
            @if($workflow->supports_date_filter)
            <div x-data="{ open: false }" class="mt-6">
                <button type="button" 
                        @click="open = !open"
                        class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900">
                    <svg class="w-4 h-4 mr-2 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                    Advanced execution configuration
                </button>

                <div x-show="open" x-collapse class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last modification date</label>
                    <select name="date_filter" 
                            x-model="dateFilter"
                            class="w-full border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                        <option value="none">None</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last_week">Last week</option>
                        <option value="custom">Custom</option>
                    </select>

                    <!-- Custom Date Range -->
                    <div x-show="dateFilter === 'custom'" x-collapse class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="date" 
                                   name="start_date"
                                   class="w-full border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input type="date" 
                                   name="end_date"
                                   class="w-full border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Step 2: Choose Method (Only for Partial) -->
        <div x-show="action === 'partial'" x-collapse class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Choose method</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <label class="relative cursor-pointer">
                    <input type="radio" 
                           name="method" 
                           value="paste" 
                           x-model="method"
                           class="peer sr-only"
                           checked>
                    <div class="p-6 border-2 rounded-lg transition peer-checked:border-[#0244CD] peer-checked:bg-blue-50">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-gray-400 peer-checked:text-[#0244CD] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Copy and paste</h3>
                                <p class="text-sm text-gray-600">
                                    Directly paste the data identifiers numbers from a spreadsheet or similar list.
                                </p>
                            </div>
                        </div>
                    </div>
                </label>

                <label class="relative cursor-pointer">
                    <input type="radio" 
                           name="method" 
                           value="upload" 
                           x-model="method"
                           class="peer sr-only">
                    <div class="p-6 border-2 rounded-lg transition peer-checked:border-[#0244CD] peer-checked:bg-blue-50">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-gray-400 peer-checked:text-[#0244CD] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Upload a file</h3>
                                <p class="text-sm text-gray-600">
                                    Import contacts from a CSV or a tab-delimited TXT file.
                                </p>
                            </div>
                        </div>
                    </div>
                </label>
            </div>

            <!-- SKUs Input (Copy/Paste) -->
            <div x-show="method === 'paste'" x-collapse>
                <label class="block text-sm font-medium text-gray-700 mb-2">Identifiers</label>
                <textarea name="skus" 
                          rows="5" 
                          placeholder="Enter SKUs separated by comma, space or new line&#10;Example: 000518, 000519, 000520"
                          class="w-full border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD] font-mono text-sm"></textarea>
                <p class="text-xs text-gray-500 mt-2">
                    Complete the field with the individual identifiers of each data (for example, sku, or order number). 
                    You may enter one or more identification id's separating them with commas ",".
                </p>
            </div>

            <!-- File Upload -->
            <div x-show="method === 'upload'" x-collapse>
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload File</label>
                <input type="file" 
                       accept=".csv,.txt"
                       class="w-full border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                <p class="text-xs text-gray-500 mt-2">
                    Accepted formats: CSV, TXT (tab-delimited)
                </p>
            </div>
        </div>

        <!-- Step 3: Timing -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Select the scope for execution</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Execute Now -->
                <label class="relative cursor-pointer">
                    <input type="radio" 
                           name="execution_type" 
                           value="now" 
                           x-model="executionType"
                           class="peer sr-only"
                           checked>
                    <div class="p-6 border-2 rounded-lg transition peer-checked:border-[#0244CD] peer-checked:bg-blue-50">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-gray-400 peer-checked:text-[#0244CD] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Execute now</h3>
                                <p class="text-sm text-gray-600">
                                    The process will start running right after you continue to the next step.
                                </p>
                            </div>
                        </div>
                    </div>
                </label>

                <!-- Program Execution -->
                <label class="relative cursor-pointer">
                    <input type="radio" 
                           name="execution_type" 
                           value="scheduled" 
                           x-model="executionType"
                           class="peer sr-only">
                    <div class="p-6 border-2 rounded-lg transition peer-checked:border-[#0244CD] peer-checked:bg-blue-50">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-gray-400 peer-checked:text-[#0244CD] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <h3 class="font-semibold text-gray-900 mb-1">Program execution</h3>
                                <p class="text-sm text-gray-600">
                                    Select the date and time of scheduling for the process to start.
                                </p>
                            </div>
                        </div>
                    </div>
                </label>
            </div>

            <!-- Scheduled Date/Time -->
            <div x-show="executionType === 'scheduled'" x-collapse class="mt-6 grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start date</label>
                    <input type="date" 
                           name="scheduled_date"
                           class="w-full border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                    <input type="time" 
                           name="scheduled_time"
                           class="w-full border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('workflows.index') }}" 
               class="btn btn-white">
                Cancel
            </a>
            <button type="submit" 
                    class="btn btn-primary">
                <span x-show="executionType === 'now'">Continue with <span x-text="action === 'partial' ? 'Partial' : 'Total'"></span> →</span>
                <span x-show="executionType === 'scheduled'">Schedule Execution →</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function executeWorkflow() {
    return {
        action: 'total',
        dateFilter: 'none',
        method: 'paste',
        executionType: 'now'
    }
}
</script>
@endpush
@endsection
