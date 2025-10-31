@extends('layouts.app')

@section('title', 'API Configuration')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">API Configuration</h1>
    <p class="text-gray-600 mt-1">Manage your API connections and system settings</p>
</div>

@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('configuration.update') }}" method="POST" class="space-y-6">
    @csrf

    <!-- ICG API Configuration -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">ICG API Configuration</h3>
                <p class="text-sm text-gray-600">Configure your ICG system connection</p>
            </div>
            <button type="button" onclick="testConnection('icg')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                ? Test Connection
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">API URL <span class="text-red-500">*</span></label>
                <input type="url" name="config[icg_api_url]" value="{{ \App\Models\SyncConfiguration::get('icg_api_url') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="http://190.14.213.6:5004/icg/api/articulo" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                <input type="text" name="config[icg_api_user]" value="{{ \App\Models\SyncConfiguration::get('icg_api_user') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="supercarnes" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-red-500">*</span></label>
                <input type="password" name="config[icg_api_password]" value="{{ \App\Models\SyncConfiguration::get('icg_api_password') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="••••••••" required>
            </div>
        </div>
    </div>

    <!-- Magento API Configuration -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Magento API Configuration</h3>
                <p class="text-sm text-gray-600">Configure your Magento store connection</p>
            </div>
            <button type="button" onclick="testConnection('magento')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                ? Test Connection
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Magento Base URL <span class="text-red-500">*</span></label>
                <input type="url" name="config[magento_base_url]" value="{{ \App\Models\SyncConfiguration::get('magento_base_url') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="https://magento.example.com" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">API Token <span class="text-red-500">*</span></label>
                <input type="text" name="config[magento_api_token]" value="{{ \App\Models\SyncConfiguration::get('magento_api_token') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="Bearer token" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Store ID</label>
                <input type="number" name="config[magento_store_id]" value="{{ \App\Models\SyncConfiguration::get('magento_store_id', 1) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="1">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Batch Size</label>
                <input type="number" name="config[magento_batch_size]" value="{{ \App\Models\SyncConfiguration::get('magento_batch_size', 50) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       min="10" max="100" placeholder="50">
            </div>
        </div>
    </div>

    <!-- FTP Configuration -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">FTP Configuration</h3>
                <p class="text-sm text-gray-600">Configure FTP file transfer settings</p>
            </div>
            <button type="button" onclick="testConnection('ftp')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                ? Test Connection
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="config[ftp_enabled]" value="true" 
                           {{ \App\Models\SyncConfiguration::get('ftp_enabled') === 'true' ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                    <span class="ml-2 text-sm font-medium text-gray-700">Enable FTP</span>
                </label>
            </div>

            <div></div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">FTP Server</label>
                <input type="text" name="config[ftp_server]" value="{{ \App\Models\SyncConfiguration::get('ftp_server') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="ftp.example.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">FTP Port</label>
                <input type="number" name="config[ftp_port]" value="{{ \App\Models\SyncConfiguration::get('ftp_port', 21) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="21">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">FTP Username</label>
                <input type="text" name="config[ftp_username]" value="{{ \App\Models\SyncConfiguration::get('ftp_username') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="FTP Username">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">FTP Password</label>
                <input type="password" name="config[ftp_password]" value="{{ \App\Models\SyncConfiguration::get('ftp_password') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="••••••••">
            </div>
        </div>
    </div>

    <!-- Process Configuration -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Process Configuration</h3>
            <p class="text-sm text-gray-600">Configure execution and processing settings</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Concurrent Pages</label>
                <input type="number" name="config[concurrent_pages]" value="{{ \App\Models\SyncConfiguration::get('concurrent_pages', 97) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       min="1" max="200">
                <p class="text-xs text-gray-500 mt-1">Number of API pages to process concurrently</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Max Pages</label>
                <input type="number" name="config[max_pages]" value="{{ \App\Models\SyncConfiguration::get('max_pages', 2000) }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                       min="1" max="10000">
            </div>

            <div class="flex items-center">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="config[filter_by_webvisb]" value="true" 
                           {{ \App\Models\SyncConfiguration::get('filter_by_webvisb') === 'true' ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                    <span class="ml-2 text-sm font-medium text-gray-700">Filter by WEBVISB = T</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-end gap-4 mt-6">
        <a href="{{ route('dashboard') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
            Cancel
        </a>
        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
            ? Save Configuration
        </button>
    </div>
</form>

@push('scripts')
<script>
    async function testConnection(type) {
        const button = event.target;
        const originalText = button.textContent;
        button.disabled = true;
        button.textContent = '? Testing...';

        let url;
        if (type === 'icg') {
            url = '{{ route("configuration.test-icg") }}';
        } else if (type === 'magento') {
            url = '{{ route("configuration.test-magento") }}';
        } else if (type === 'ftp') {
            url = '{{ route("configuration.test-ftp") }}';
        }

        console.log('?? Testing connection to:', url);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                throw new Error('CSRF token not found. Add <meta name="csrf-token"> to your layout.');
            }

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            console.log('?? Response status:', response.status);
            console.log('?? Content-Type:', response.headers.get('content-type'));

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const htmlContent = await response.text();
                console.error('? Server returned HTML instead of JSON:', htmlContent.substring(0, 500));
                throw new Error('Server error: Expected JSON but received HTML. Check server logs.');
            }

            const data = await response.json();
            console.log('?? Response data:', data);

            if (data.success) {
                let message = '? Connection successful!\n\n' + data.message;
                
                if (type === 'magento' && data.stores_count) {
                    message += '\n\n?? Stores found: ' + data.stores_count;
                }
                if (type === 'ftp' && data.current_directory) {
                    message += '\n\n?? Current directory: ' + data.current_directory;
                }
                
                alert(message);
            } else {
                let errorMessage = '? Connection failed!\n\n' + data.message;
                
                if (data.tips && Array.isArray(data.tips)) {
                    const validTips = data.tips.filter(tip => tip !== null);
                    if (validTips.length > 0) {
                        errorMessage += '\n\n?? Tips:\n' + validTips.join('\n');
                    }
                }
                
                alert(errorMessage);
            }
        } catch (error) {
            console.error('? Error details:', error);
            
            let errorMsg = '? Error testing connection:\n\n' + error.message;
            
            if (error.message.includes('CSRF')) {
                errorMsg += '\n\n?? Solution: Add CSRF meta tag to your layout header.';
            } else if (error.message.includes('HTML')) {
                errorMsg += '\n\n?? Check the browser console (F12) for more details.';
                errorMsg += '\n?? Also check storage/logs/laravel.log for server errors.';
            }
            
            alert(errorMsg);
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    }
</script>
@endpush
@endsection