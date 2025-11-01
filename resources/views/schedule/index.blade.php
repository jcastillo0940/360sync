@extends('layouts.app')

@section('title', 'Schedule & Planning')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Schedule & Planning</h1>
            <p class="text-gray-600 mt-1">Manage automated workflow schedules</p>
        </div>
        <a href="{{ route('schedules.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Schedule
        </a>
    </div>
</div>

<!-- Date Navigation -->
<div class="bg-white rounded-lg shadow mb-6 p-4">
    <div class="flex items-center justify-between">
        <a href="{{ route('schedules.index', ['date' => \Carbon\Carbon::parse($selectedDate)->subDay()->format('Y-m-d')]) }}" 
           class="btn btn-secondary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>

        <div class="text-center">
            <h2 class="text-xl font-semibold text-gray-900">
                {{ \Carbon\Carbon::parse($selectedDate)->format('l, F d, Y') }}
            </h2>
            <p class="text-sm text-gray-500">
               {{ count($scheduledExecutions) }} scheduled executions
            </p>
        </div>

        <a href="{{ route('schedules.index', ['date' => \Carbon\Carbon::parse($selectedDate)->addDay()->format('Y-m-d')]) }}" 
           class="btn btn-secondary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('schedules.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
            Go to Today
        </a>
    </div>
</div>

<!-- Timeline View -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <h3 class="text-lg font-semibold mb-4">Scheduled Executions Timeline</h3>
        
        @if(empty($scheduledExecutions))
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-500">No schedules for this date</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($scheduledExecutions as $time => $executions)
                    <div class="flex">
                        <div class="w-20 text-sm font-medium text-gray-500 pt-1">
                            {{ $time }}
                        </div>
                        <div class="flex-1 space-y-2">
                            @foreach($executions as $execution)
                                <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-blue-500">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">
                                                {{ $execution['workflow']->name }}
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                {{ $execution['schedule']->description ?? 'Scheduled execution' }}
                                            </p>
                                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                                <span>
                                                    Frequency: {{ ucfirst($execution['schedule']->frequency) }}
                                                </span>
                                                @if($execution['schedule']->is_enabled)
                                                    <span class="text-green-600">● Active</span>
                                                @else
                                                    <span class="text-gray-400">● Inactive</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('workflows.show', $execution['workflow']->id) }}" 
                                               class="btn btn-sm btn-secondary">
                                                View Workflow
                                            </a>
                                            <a href="{{ route('schedules.edit', $execution['schedule']->id) }}" 
                                               class="btn btn-sm btn-secondary">
                                                Edit Schedule
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Active Schedule Rules -->
<div class="bg-white rounded-lg shadow overflow-hidden mt-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold">All Active Schedules</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Workflow
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Frequency
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Execution Time
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Next Run
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($scheduleRules as $rule)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $rule->workflow->name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="badge badge-info">
                                {{ ucfirst($rule->frequency) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $rule->execution_time }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $rule->next_run_at ? $rule->next_run_at->format('M d, Y H:i') : 'Not scheduled' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($rule->is_enabled)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-gray">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                            <a href="{{ route('schedules.edit', $rule->id) }}" 
                               class="text-blue-600 hover:text-blue-800">
                                Edit
                            </a>
                            
                            <form method="POST" action="{{ route('schedules.toggle', $rule->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-orange-600 hover:text-orange-800">
                                    {{ $rule->is_enabled ? 'Disable' : 'Enable' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('schedules.destroy', $rule->id) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('Are you sure?')"
                                        class="text-red-600 hover:text-red-800">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No schedule rules found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
