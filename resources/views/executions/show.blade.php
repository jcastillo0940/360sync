@extends('layouts.app')

@section('title', 'Execution Details')

@section('content')
<div x-data="executionDetail({{ $execution->id }})" x-init="init()">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('executions.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Executions
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Execution Details</h1>
                <p class="text-gray-600 mt-1">Job ID: <span class="font-mono" x-text="execution.job_id">{{ $execution->job_id }}</span></p>
            </div>
            <div class="flex items-center space-x-3">
                @if($execution->csv_filename)
                <a href="{{ route('executions.download-csv', $execution->id) }}" 
                   class="btn btn-white">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                    Download CSV
                </a>
                @endif
                
                @if($execution->hasFailed())
                <form method="POST" action="{{ route('executions.retry', $execution->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Retry Execution
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Workflow Info -->
            <div>
                <p class="text-sm text-gray-600 mb-1">Workflow</p>
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 rounded-lg bg-{{ $execution->workflow->color }}-100 flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-{{ $execution->workflow->color }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900" x-text="execution.workflow_name">{{ $execution->workflow->name }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst($execution->action) }}</p>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div>
                <p class="text-sm text-gray-600 mb-1">Status</p>
                <div x-show="execution.status === 'running'">
                    <span class="badge badge-info inline-flex items-center">
                        <span class="spinner mr-2"></span>
                        <span x-text="execution.status">Running</span>
                    </span>
                </div>
                <div x-show="execution.status === 'pending'">
                    <span class="badge badge-warning">Pending</span>
                </div>
                <div x-show="['completed_success', 'completed_success_no_ftp'].includes(execution.status)">
                    <span class="badge badge-success">Completed</span>
                </div>
                <div x-show="execution.status === 'failed'">
                    <span class="badge badge-error">Failed</span>
                </div>
            </div>

            <!-- Duration -->
            <div>
                <p class="text-sm text-gray-600 mb-1">Duration</p>
                <p class="text-xl font-bold text-gray-900" x-text="execution.duration || '-'">{{ $execution->formatted_duration ?? '-' }}</p>
            </div>

            <!-- Progress -->
            <div>
                <p class="text-sm text-gray-600 mb-1">Progress</p>
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="w-full bg-gray-200 rounded-full h-2 mb-1">
                            <div class="bg-green-600 h-2 rounded-full transition-all duration-500" 
                                 :style="`width: ${execution.progress?.percentage || 0}%`"
                                 style="width: {{ $execution->total_items > 0 ? round(($execution->success_count / $execution->total_items) * 100) : 0 }}%"></div>
                        </div>
                        <p class="text-xs text-gray-600">
                            <span x-text="execution.progress?.success_count || 0">{{ $execution->success_count }}</span> / 
                            <span x-text="execution.progress?.total_items || 0">{{ $execution->total_items }}</span> items
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Result Message -->
        <div x-show="execution.result_message" class="mt-4 p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-700" x-text="execution.result_message">{{ $execution->result_message }}</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-600">Total Items</p>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-900" x-text="(execution.progress?.total_items || 0).toLocaleString()">{{ number_format($execution->total_items) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-600">Success</p>
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-green-600" x-text="(execution.progress?.success_count || 0).toLocaleString()">{{ number_format($execution->success_count) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-600">Failed</p>
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-red-600" x-text="(execution.progress?.failed_count || 0).toLocaleString()">{{ number_format($execution->failed_count) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-600">Skipped</p>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-3xl font-bold text-gray-600" x-text="(execution.progress?.skipped_count || 0).toLocaleString()">{{ number_format($execution->skipped_count) }}</p>
        </div>
    </div>

    <!-- Logs Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Execution Logs</h2>
                <div class="flex items-center space-x-3">
                    <!-- Log Level Filter -->
                    <select x-model="logLevel" @change="filterLogs()" 
                            class="text-sm border-gray-300 rounded-lg focus:ring-[#0244CD] focus:border-[#0244CD]">
                        <option value="">All Levels</option>
                        <option value="ERROR">Errors Only</option>
                        <option value="WARNING">Warnings</option>
                        <option value="SUCCESS">Success</option>
                        <option value="INFO">Info</option>
                    </select>

                    <!-- Auto-scroll Toggle -->
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="autoScroll" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0244CD]"></div>
                        <span class="ml-2 text-sm font-medium text-gray-700">Auto-scroll</span>
                    </label>

                    <!-- Refresh Button -->
                    <button @click="loadStatus()" 
                            class="btn btn-white text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Logs Container -->
        <div class="p-6 bg-gray-900 text-gray-100 font-mono text-sm max-h-[600px] overflow-y-auto scrollbar-thin" 
             id="logsContainer"
             x-ref="logsContainer">
            <template x-for="log in filteredLogs" :key="log.id">
                <div class="mb-2 flex items-start hover:bg-gray-800 px-2 py-1 rounded">
                    <span class="text-gray-500 mr-3" x-text="log.time"></span>
                    <span class="mr-3 font-semibold"
                          :class="{
                              'text-red-400': log.level === 'ERROR' || log.level === 'CRITICAL',
                              'text-yellow-400': log.level === 'WARNING',
                              'text-green-400': log.level === 'SUCCESS',
                              'text-blue-400': log.level === 'INFO',
                              'text-gray-400': log.level === 'DEBUG'
                          }"
                          x-text="log.level"></span>
                    <span class="flex-1 text-gray-300" x-text="log.message"></span>
                </div>
            </template>

            <div x-show="filteredLogs.length === 0" class="text-center py-12 text-gray-500">
                <p>No logs available yet...</p>
                <p class="text-sm mt-2" x-show="execution.status === 'running'">Waiting for execution to start...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const executionData = {!! json_encode([
    'id' => $execution->id,
    'job_id' => $execution->job_id,
    'workflow_name' => $execution->workflow->name,
    'status' => $execution->status,
    'duration' => $execution->formatted_duration,
    'result_message' => $execution->result_message,
    'progress' => [
        'total_items' => $execution->total_items,
        'success_count' => $execution->success_count,
        'failed_count' => $execution->failed_count,
        'skipped_count' => $execution->skipped_count,
        'percentage' => $execution->total_items > 0 ? round(($execution->success_count / $execution->total_items) * 100, 2) : 0
    ]
]) !!};

const logsData = {!! json_encode($execution->logs->map(function($log) {
    return [
        'id' => $log->id,
        'level' => $log->level,
        'message' => $log->formatted_message,
        'time' => $log->formatted_time,
    ];
})->values()) !!};

function executionDetail(executionId) {
    return {
        executionId: executionId,
        execution: executionData,
        logs: logsData,
        filteredLogs: [],
        logLevel: '',
        autoScroll: true,
        pollInterval: null,
        
        init() {
            this.filterLogs();
            
            if (this.execution.status === 'running' || this.execution.status === 'pending') {
                this.startPolling();
            }
        },
        
        startPolling() {
            this.pollInterval = setInterval(() => {
                this.loadStatus();
            }, 2000);
        },
        
        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
                this.pollInterval = null;
            }
        },
        
        async loadStatus() {
            try {
                const response = await fetch(`/executions/${this.executionId}/status`);
                const data = await response.json();
                
                this.execution = data;
                this.logs = data.logs;
                this.filterLogs();
                
                if (!['running', 'pending'].includes(data.status)) {
                    this.stopPolling();
                }
                
                if (this.autoScroll) {
                    this.$nextTick(() => {
                        const container = this.$refs.logsContainer;
                        container.scrollTop = container.scrollHeight;
                    });
                }
            } catch (error) {
                console.error('Error loading status:', error);
            }
        },
        
        filterLogs() {
            if (this.logLevel === '') {
                this.filteredLogs = this.logs;
            } else {
                this.filteredLogs = this.logs.filter(log => log.level === this.logLevel);
            }
        }
    }
}
</script>
@endpush
@endsection