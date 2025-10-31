<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $workflow->name }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('workflows.execute', $workflow->id) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Execute Workflow
                </a>
                <a href="{{ route('workflows.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Workflow Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Workflow Details</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Name</p>
                            <p class="font-semibold">{{ $workflow->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Type</p>
                            <p class="font-semibold">{{ ucfirst(str_replace('_', ' ', $workflow->type)) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="px-2 py-1 text-xs rounded {{ $workflow->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $workflow->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Executions</p>
                            <p class="font-semibold">{{ $stats['total_executions'] }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Success Rate</p>
                            <p class="font-semibold">{{ number_format($workflow->success_rate, 2) }}%</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Avg Duration</p>
                            <p class="font-semibold">{{ $stats['avg_duration'] ? round($stats['avg_duration']) . 's' : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-600">Successful</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['successful'] }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-600">Failed</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-600">In Progress</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['in_progress'] }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-600">Success Rate</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['success_rate'], 1) }}%</p>
                </div>
            </div>

            <!-- Recent Executions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Recent Executions</h3>
                    
                    @if($workflow->executions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Started</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($workflow->executions as $execution)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">
                                                {{ $execution->job_id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs rounded {{ $execution->status_badge_color }}">
                                                    {{ $execution->status_label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ $execution->success_count }}/{{ $execution->total_items }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ $execution->formatted_duration }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ $execution->created_at->format('Y-m-d H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="{{ route('executions.show', $execution->id) }}" 
                                                   class="text-blue-600 hover:text-blue-900">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No executions yet</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>