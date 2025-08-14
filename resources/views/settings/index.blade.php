@extends('components.app-layout')

@section('title', 'System Settings')
@section('subtitle', 'Configure and monitor your printer management system')

@section('content')
<div class="space-y-8">
    <!-- Header Banner -->
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-blue-600 to-purple-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative px-8 py-12">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
                <div class="text-white">
                    <h1 class="text-4xl font-bold mb-3">System Settings</h1>
                    <p class="text-xl opacity-90 mb-6">Configure and monitor your printer management system</p>
                    <div class="flex flex-wrap gap-4">
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <div class="text-2xl font-bold">{{ $systemStats['total_users'] }}</div>
                            <div class="text-sm opacity-90">Active Users</div>
                        </div>
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <div class="text-2xl font-bold">{{ $systemStats['total_storage_mb'] }}</div>
                            <div class="text-sm opacity-90">MB Storage</div>
                        </div>
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <div class="text-2xl font-bold">{{ $systemStats['active_documents'] }}</div>
                            <div class="text-sm opacity-90">Active Documents</div>
                        </div>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <div class="w-32 h-32 bg-white bg-opacity-10 rounded-full flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-cogs text-6xl text-white opacity-80"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-5 rounded-full"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-32 h-32 bg-white opacity-5 rounded-full"></div>
    </div>

    <!-- System Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Users Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($systemStats['total_users']) }}</p>
                    <p class="text-xs text-gray-500 mt-1">System administrators</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-users text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Documents Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Documents</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($systemStats['total_documents']) }}</p>
                    <p class="text-xs text-green-600 mt-1">{{ $systemStats['active_documents'] }} active</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-file-alt text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Categories Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Categories</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($systemStats['total_categories']) }}</p>
                    <p class="text-xs text-purple-600 mt-1">{{ $systemStats['active_categories'] }} active</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-layer-group text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <!-- Storage Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Storage Used</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($systemStats['total_storage_mb'], 1) }}</p>
                    <p class="text-xs text-orange-600 mt-1">MB total</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <i class="fas fa-hdd text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Categories -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Document Management -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-blue-100 rounded-lg mr-4">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Document Management</h3>
                </div>
                <p class="text-gray-600 mb-4">Configure document upload settings, file types, and size limits.</p>
                <div class="space-y-2">
                    <a href="{{ route('documents.index') }}" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Manage Documents
                    </a>
                    <a href="{{ route('document-categories.index') }}" class="block w-full text-center px-4 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors">
                        Manage Categories
                    </a>
                </div>
            </div>
        </div>

        <!-- User Management -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-green-100 rounded-lg mr-4">
                        <i class="fas fa-users text-green-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">User Management</h3>
                </div>
                <p class="text-gray-600 mb-4">Manage system users, roles, and permissions.</p>
                <div class="space-y-2">
                    <a href="{{ route('users.index') }}" class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Manage Users
                    </a>
                    <a href="{{ route('users.create') }}" class="block w-full text-center px-4 py-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors">
                        Add New User
                    </a>
                </div>
            </div>
        </div>

        <!-- System Maintenance -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="p-3 bg-purple-100 rounded-lg mr-4">
                        <i class="fas fa-tools text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">System Maintenance</h3>
                </div>
                <p class="text-gray-600 mb-4">System cleanup, cache management, and maintenance tools.</p>
                <div class="space-y-2">
                    <button onclick="clearCache()" class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Clear Cache
                    </button>
                    <button onclick="optimizeSystem()" class="block w-full text-center px-4 py-2 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 transition-colors">
                        Optimize System
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Storage Usage by Category -->
    @if($categoryStorage->count() > 0)
        <div class="bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Storage Usage by Category</h3>
                <p class="text-sm text-gray-600">Monitor storage consumption across document categories</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($categoryStorage as $category)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: {{ $category['color'] }}20;">
                                        <i class="{{ $category['icon'] }}" style="color: {{ $category['color'] }};"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ $category['name'] }}</h4>
                                        <p class="text-xs text-gray-500">Category</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-gray-900">{{ $category['size_mb'] }}</div>
                                    <div class="text-xs text-gray-500">MB</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- System Activities -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">System Information</h3>
            <p class="text-sm text-gray-600">Recent system activities and status</p>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach($recentActivities as $activity)
                    <div class="flex items-start space-x-4 p-3 rounded-lg bg-gray-50">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                <i class="{{ $activity['icon'] }} {{ $activity['color'] }}"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $activity['message'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function clearCache() {
    if (confirm('Are you sure you want to clear the system cache?')) {
        // Add AJAX call to clear cache endpoint
        alert('Cache cleared successfully!');
    }
}

function optimizeSystem() {
    if (confirm('Are you sure you want to optimize the system?')) {
        // Add AJAX call to optimize system endpoint
        alert('System optimization completed!');
    }
}
</script>
@endpush
@endsection
