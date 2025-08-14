@extends('components.app-layout')

@section('title', 'Dashboard')
@section('subtitle', 'Overview of your printer management system')

@section('content')
<div class="space-y-8">
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="relative px-8 py-12">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
                <div class="text-white">
                    <h1 class="text-4xl font-bold mb-3">Welcome to Printer Management</h1>
                    <p class="text-xl opacity-90 mb-6">Streamline your document workflow and printing operations</p>
                    <div class="flex flex-wrap gap-4">
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <div class="text-2xl font-bold">{{ $totalDocuments }}</div>
                            <div class="text-sm opacity-90">Total Documents</div>
                        </div>
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <div class="text-2xl font-bold">{{ number_format($totalPrints) }}</div>
                            <div class="text-sm opacity-90">Total Prints</div>
                        </div>
                        <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <div class="text-2xl font-bold">{{ $totalCategories }}</div>
                            <div class="text-sm opacity-90">Categories</div>
                        </div>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <div class="w-32 h-32 bg-white bg-opacity-10 rounded-full flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-print text-6xl text-white opacity-80"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-5 rounded-full"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-32 h-32 bg-white opacity-5 rounded-full"></div>
    </div>

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Users Metric -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Users</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalUsers) }}</p>
                    @if($userGrowth != 0)
                        <div class="flex items-center mt-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $userGrowth >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas fa-arrow-{{ $userGrowth >= 0 ? 'up' : 'down' }} mr-1"></i>
                                {{ abs($userGrowth) }}%
                            </span>
                            <span class="text-xs text-gray-500 ml-2">vs last month</span>
                        </div>
                    @endif
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-users text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Documents Metric -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Documents</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalDocuments) }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-xs text-green-600 font-medium">{{ $activeDocuments }} active</span>
                        @if($documentGrowth != 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $documentGrowth >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} ml-2">
                                <i class="fas fa-arrow-{{ $documentGrowth >= 0 ? 'up' : 'down' }} mr-1"></i>
                                {{ abs($documentGrowth) }}%
                            </span>
                        @endif
                    </div>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-file-alt text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Prints Metric -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Prints</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalPrints) }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-xs text-purple-600 font-medium">All time</span>
                    </div>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-print text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <!-- Storage Metric -->
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Storage Used</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalStorageBytes / 1024 / 1024, 1) }}</p>
                    <div class="flex items-center mt-2">
                        <span class="text-xs text-orange-600 font-medium">MB total</span>
                        <span class="text-xs text-gray-500 ml-2">Avg: {{ $averageFileSize }}MB</span>
                    </div>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <i class="fas fa-hdd text-2xl text-orange-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Activities -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
                    <span class="text-sm text-gray-500">Last 24 hours</span>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4 max-h-80 overflow-y-auto">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-start space-x-4 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                    <i class="{{ $activity['icon'] }} {{ $activity['color'] }}"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $activity['message'] }}</p>
                                <div class="flex items-center mt-1 space-x-2">
                                    <span class="text-xs text-gray-500">by {{ $activity['user'] }}</span>
                                    @if(isset($activity['category']))
                                        <span class="text-xs text-gray-400">•</span>
                                        <span class="text-xs text-gray-500">in {{ $activity['category'] }}</span>
                                    @endif
                                    <span class="text-xs text-gray-400">•</span>
                                    <span class="text-xs text-gray-500">{{ $activity['time'] }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <i class="fas fa-history text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">No recent activities found.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Stats & Recent Documents -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('documents.create') }}" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                        <div class="p-2 bg-blue-100 rounded-lg group-hover:bg-blue-200 transition-colors">
                            <i class="fas fa-file-upload text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Upload Document</p>
                            <p class="text-xs text-gray-500">Add new files to print</p>
                        </div>
                    </a>
                    <a href="{{ route('document-categories.create', ['type' => 'category']) }}" class="flex items-center p-3 rounded-lg hover:bg-purple-50 transition-colors group">
                        <div class="p-2 bg-purple-100 rounded-lg group-hover:bg-purple-200 transition-colors">
                            <i class="fas fa-layer-group text-purple-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Create Category</p>
                            <p class="text-xs text-gray-500">Organize your documents</p>
                        </div>
                    </a>
                    <a href="{{ route('users.create') }}" class="flex items-center p-3 rounded-lg hover:bg-green-50 transition-colors group">
                        <div class="p-2 bg-green-100 rounded-lg group-hover:bg-green-200 transition-colors">
                            <i class="fas fa-user-plus text-green-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Add User</p>
                            <p class="text-xs text-gray-500">Manage system users</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Documents -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Documents</h3>
                        <a href="{{ route('documents.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View all</a>
                    </div>
                </div>
                <div class="p-6">
                    @forelse($recentDocuments as $document)
                        <div class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: {{ $document->category->color }}20;">
                                    <i class="{{ $document->category->icon }} text-sm" style="color: {{ $document->category->color }};"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $document->title }}</p>
                                <p class="text-xs text-gray-500">{{ $document->category->name }} • {{ $document->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex-shrink-0">
                                @if($document->print_count > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $document->print_count }} prints
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        New
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <i class="fas fa-file text-3xl text-gray-300 mb-2"></i>
                            <p class="text-gray-500 text-sm">No documents uploaded yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Top Printed Documents -->
    @if($topPrintedDocuments->count() > 0)
        <div class="bg-white rounded-xl shadow-lg border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Most Printed Documents</h3>
                    <span class="text-sm text-gray-500">All time</span>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($topPrintedDocuments as $document)
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: {{ $document->category->color }}20;">
                                        <i class="{{ $document->category->icon }}" style="color: {{ $document->category->color }};"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ Str::limit($document->title, 20) }}</h4>
                                        <p class="text-xs text-gray-600">{{ $document->category->name }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-purple-600">{{ $document->print_count }}</div>
                                    <div class="text-xs text-purple-500">prints</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection



 