@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div x-data="dashboard()" x-init="init()">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Welcome Back, {{ auth()->user()->name }}!</h1>
        <p class="text-gray-600 mt-1">Let's see your current sync work today</p>
    </div>

    <!-- Filters -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
            <label class="text-sm font-medium text-gray-700">Time period:</label>
            <select x-model="period" @change="loadStats()" 
                    class="border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
        </div>

        <button @click="loadStats()" 
                class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Update
        </button>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Successful -->
        <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="font-semibold text-green-900">Successful</h3>
                </div>
                <a href="{{ route('executions.index') }}?status=success" class="text-sm text-green-700 hover:text-green-900">
                    View detail →
                </a>
            </div>
            <div class="space-y-2">
                <p class="text-4xl font-bold text-green-900" x-text="stats.successful.executions">{{ $stats['successful']['executions'] }}</p>
                <p class="text-sm text-green-700">Executions</p>
                <p class="text-2xl font-semibold text-green-800" x-text="stats.successful.entities.toLocaleString()">{{ number_format($stats['successful']['entities']) }}</p>
                <p class="text-sm text-green-700">Entities</p>
            </div>
        </div>

        <!-- With Errors -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="font-semibold text-orange-900">With Errors</h3>
                </div>
                <a href="{{ route('executions.index') }}?status=errors" class="text-sm text-orange-700 hover:text-orange-900">
                    View detail →
                </a>
            </div>
            <div class="space-y-2">
                <p class="text-4xl font-bold text-orange-900" x-text="stats.with_errors.executions">{{ $stats['with_errors']['executions'] }}</p>
                <p class="text-sm text-orange-700">Executions</p>
                <p class="text-2xl font-semibold text-orange-800" x-text="stats.with_errors.entities.toLocaleString()">{{ $stats['with_errors']['entities'] }}</p>
                <p class="text-sm text-orange-700">Entities</p>
            </div>
        </div>

        <!-- Failed -->
        <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="font-semibold text-red-900">Failed</h3>
                </div>
                <a href="{{ route('executions.index') }}?status=failed" class="text-sm text-red-700 hover:text-red-900">
                    View detail →
                </a>
            </div>
            <div class="space-y-2">
                <p class="text-4xl font-bold text-red-900" x-text="stats.failed.executions">{{ $stats['failed']['executions'] }}</p>
                <p class="text-sm text-red-700">Executions</p>
                <p class="text-2xl font-semibold text-red-800" x-text="stats.failed.entities.toLocaleString()">{{ $stats['failed']['entities'] }}</p>
                <p class="text-sm text-red-700">Entities</p>
            </div>
        </div>
    </div>

    <!-- Executions Overview -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Executions overview</h2>
            <p class="text-sm text-gray-600 mt-1">Detailed record of all executions carried out within your specified timeframe</p>
        </div>

        <!-- Search and Filter -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex-1 max-w-md">
                    <input type="text" 
                           placeholder="Search ID or Workflow name"
                           class="w-full border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                </div>
                <button class="ml-4 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Workflow</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">State</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entities</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentExecutions as $execution)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ Str::limit($execution->job_id, 10) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            default
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $execution->workflow->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $execution->started_at?->format('D, M d Y H:i') ?? 'Pending' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ ucfirst($execution->status) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($execution->isSuccessful())
                                <span class="px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Successful</span>
                            @elseif($execution->hasFailed())
                                <span class="px-3 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Failed</span>
                            @elseif($execution->isRunning())
                                <span class="px-3 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Running</span>
                            @else
                                <span class="px-3 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ number_format($execution->success_count) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('executions.show', $execution->id) }}" class="text-[#0244CD] hover:text-[#F6D101]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            No executions found for this period
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function dashboard() {
    return {
        period: '{{ $period }}',
        stats: {
            successful: { executions: {{ $stats['successful']['executions'] }}, entities: {{ $stats['successful']['entities'] }} },
            with_errors: { executions: {{ $stats['with_errors']['executions'] }}, entities: {{ $stats['with_errors']['entities'] }} },
            failed: { executions: {{ $stats['failed']['executions'] }}, entities: {{ $stats['failed']['entities'] }} }
        },
        
        init() {
            // Auto-refresh cada 30 segundos
            setInterval(() => this.loadStats(), 30000);
        },
        
        async loadStats() {
            try {
                const response = await fetch(`{{ route('dashboard.stats') }}?period=${this.period}`);
                const data = await response.json();
                this.stats = data;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
    }
}
</script>
@endpush
@endsection