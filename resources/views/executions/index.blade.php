@extends('layouts.app')

@section('title', 'Executions')

@section('content')
<div x-data="{ currentTab: '{{ $tab }}' }">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Executions</h1>
        <p class="text-gray-600 mt-1">Monitor and manage workflow executions</p>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('executions.index', ['tab' => 'in_progress']) }}" 
               class="pb-4 px-1 border-b-2 font-medium text-sm transition {{ $tab === 'in_progress' ? 'border-[#0244CD] text-[#0244CD]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                In progress
            </a>
            <a href="{{ route('executions.index', ['tab' => 'history']) }}" 
               class="pb-4 px-1 border-b-2 font-medium text-sm transition {{ $tab === 'history' ? 'border-[#0244CD] text-[#0244CD]' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Executions history
            </a>
        </nav>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-4">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[300px]">
                    <form method="GET" action="{{ route('executions.index') }}">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <input type="hidden" name="period" value="{{ $period }}">
                        <input type="text" 
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Search by Job ID or Workflow name"
                               class="w-full border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                    </form>
                </div>

                <!-- Period Filter -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Period:</label>
                    <select onchange="window.location.href='{{ route('executions.index') }}?tab={{ $tab }}&period=' + this.value" 
                            class="border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                        <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>

                <!-- Workflow Filter -->
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Workflow:</label>
                    <select class="border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                        <option value="">All Workflows</option>
                        @foreach($workflows as $workflow)
                        <option value="{{ $workflow->id }}">{{ $workflow->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Button -->
                <button class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Executions Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Started</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Success</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failed</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($executions as $execution)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('executions.show', $execution->id) }}" 
                               class="text-sm font-medium text-[#0244CD] hover:text-[#F6D101]">
                                {{ Str::limit($execution->job_id, 15) }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8 rounded-lg bg-{{ $execution->workflow->color }}-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-{{ $execution->workflow->color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $execution->workflow->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge {{ $execution->action === 'total' ? 'badge-info' : 'badge-warning' }}">
                                {{ ucfirst($execution->action) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $execution->started_at ? $execution->started_at->format('M d, H:i') : 'Not started' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $execution->formatted_duration ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($execution->status === 'running')
                                <span class="badge badge-info inline-flex items-center">
                                    <span class="spinner mr-2"></span>
                                    Running
                                </span>
                            @elseif($execution->status === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($execution->isSuccessful())
                                <span class="badge badge-success">Completed</span>
                            @elseif($execution->hasFailed())
                                <span class="badge badge-error">Failed</span>
                            @else
                                <span class="badge badge-gray">{{ ucfirst($execution->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="text-green-600 font-medium">{{ number_format($execution->success_count) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($execution->failed_count > 0)
                                <span class="text-red-600 font-medium">{{ number_format($execution->failed_count) }}</span>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                            <!-- View Details -->
                            <a href="{{ route('executions.show', $execution->id) }}" 
                               title="View Details"
                               class="inline-flex items-center justify-center w-8 h-8 text-[#0244CD] hover:bg-blue-50 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>

                            @if($execution->csv_filename)
                            <!-- Download CSV -->
                            <a href="{{ route('executions.download-csv', $execution->id) }}" 
                               title="Download CSV"
                               class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:bg-green-50 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                                </svg>
                            </a>
                            @endif

                            @if($execution->hasFailed())
                            <!-- Retry -->
                            <form method="POST" action="{{ route('executions.retry', $execution->id) }}" class="inline-block">
                                @csrf
                                <button type="submit" 
                                        title="Retry"
                                        class="inline-flex items-center justify-center w-8 h-8 text-orange-600 hover:bg-orange-50 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </form>
                            @endif

                            @if($execution->isRunning())
                            <!-- Cancel -->
                            <form method="POST" action="{{ route('executions.cancel', $execution->id) }}" class="inline-block">
                                @csrf
                                <button type="submit" 
                                        onclick="return confirm('Are you sure you want to cancel this execution?')"
                                        title="Cancel"
                                        class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                            @endif

                            @if(!$execution->isRunning())
                            <!-- Delete -->
                            <form method="POST" action="{{ route('executions.destroy', $execution->id) }}" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('Are you sure you want to delete this execution?')"
                                        title="Delete"
                                        class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-lg font-medium">No executions found</p>
                            <p class="text-sm text-gray-400 mt-1">Try adjusting your filters or time period</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($executions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-white">
            {{ $executions->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Auto-refresh para ejecuciones en progreso
    @if($executions->contains(fn($e) => $e->isRunning()))
    setTimeout(() => {
        window.location.reload();
    }, 30000); // Recargar cada 30 segundos
    @endif

    // Filtros
    document.getElementById('statusFilter')?.addEventListener('change', function() {
        window.location.href = updateQueryString('status', this.value);
    });

    document.getElementById('workflowFilter')?.addEventListener('change', function() {
        window.location.href = updateQueryString('workflow', this.value);
    });

    function updateQueryString(key, value) {
        const url = new URL(window.location);
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
        return url.toString();
    }
</script>
@endpush
@endsection