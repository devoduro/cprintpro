@extends('components.app-layout')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Enhanced Header Section -->
    <div class="relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 opacity-90"></div>
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-12">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-6">
                        <!-- Enhanced User Avatar -->
                        <div class="relative">
                            @if(auth()->user()->avatar)
                                <img class="h-16 w-16 rounded-full ring-4 ring-white shadow-lg" src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
                            @else
                                <div class="h-16 w-16 rounded-full bg-white bg-opacity-20 backdrop-blur-sm ring-4 ring-white shadow-lg flex items-center justify-center">
                                    <span class="text-white font-bold text-xl">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <div class="absolute -bottom-1 -right-1 h-6 w-6 bg-green-400 rounded-full ring-2 ring-white flex items-center justify-center">
                                <i class="fas fa-check text-white text-xs"></i>
                            </div>
                        </div>
                        
                        <div>
                            <h1 class="text-4xl font-bold text-white mb-2">Welcome back, {{ auth()->user()->name }}!</h1>
                            <p class="text-blue-100 text-lg">Your printing activity and document statistics</p>
                            <div class="flex items-center mt-3 space-x-4 text-blue-100">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt mr-2"></i>
                                    <span class="text-sm">{{ now()->format('l, F j, Y') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-clock mr-2"></i>
                                    <span class="text-sm">{{ now()->format('g:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="hidden lg:flex items-center space-x-3">
                        <a href="{{ route('users.portal') }}" class="bg-white bg-opacity-20 backdrop-blur-sm text-white px-4 py-2 rounded-lg hover:bg-opacity-30 transition-all duration-200 flex items-center">
                            <i class="fas fa-folder-open mr-2"></i>
                            Browse Documents
                        </a>
                         
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- My Total Prints -->
            <div class="group bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-blue-200 hover:border-blue-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg group-hover:shadow-xl transition-all duration-300">
                            <i class="fas fa-print text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-blue-700 uppercase tracking-wide">My Total Prints</p>
                            <p class="text-3xl font-bold text-blue-900 mt-1">{{ number_format($userStats['total_prints']) }}</p>
                            <p class="text-xs text-blue-600 mt-1">Documents printed by you</p>
                        </div>
                    </div>
                    <div class="text-blue-400 opacity-20 group-hover:opacity-40 transition-opacity duration-300">
                        <i class="fas fa-chart-line text-3xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- My Unique Documents -->
            <div class="group bg-gradient-to-br from-emerald-50 to-green-100 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-emerald-200 hover:border-emerald-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg group-hover:shadow-xl transition-all duration-300">
                            <i class="fas fa-file-check text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-emerald-700 uppercase tracking-wide">Documents Printed</p>
                            <p class="text-3xl font-bold text-emerald-900 mt-1">{{ number_format($userStats['unique_documents_printed']) }}</p>
                            <p class="text-xs text-emerald-600 mt-1">Unique documents</p>
                        </div>
                    </div>
                    <div class="text-emerald-400 opacity-20 group-hover:opacity-40 transition-opacity duration-300">
                        <i class="fas fa-layer-group text-3xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Available Documents -->
            <div class="group bg-gradient-to-br from-purple-50 to-violet-100 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-purple-200 hover:border-purple-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 text-white shadow-lg group-hover:shadow-xl transition-all duration-300">
                            <i class="fas fa-folder-open text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-purple-700 uppercase tracking-wide">Available Documents</p>
                            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $stats['total_documents'] }}</p>
                            <p class="text-xs text-purple-600 mt-1">Ready to view/print</p>
                        </div>
                    </div>
                    <div class="text-purple-400 opacity-20 group-hover:opacity-40 transition-opacity duration-300">
                        <i class="fas fa-database text-3xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Last Print Activity -->
            <div class="group bg-gradient-to-br from-amber-50 to-orange-100 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-amber-200 hover:border-amber-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg group-hover:shadow-xl transition-all duration-300">
                            <i class="fas fa-history text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-semibold text-amber-700 uppercase tracking-wide">Last Print</p>
                            <p class="text-2xl font-bold text-amber-900 mt-1">
                                @if($userStats['last_print'])
                                    {{ $userStats['last_print']->diffForHumans() }}
                                @else
                                    <span class="text-lg">Never</span>
                                @endif
                            </p>
                            <p class="text-xs text-amber-600 mt-1">Your last activity</p>
                        </div>
                    </div>
                    <div class="text-amber-400 opacity-20 group-hover:opacity-40 transition-opacity duration-300">
                        <i class="fas fa-clock text-3xl"></i>
                    </div>
                </div>
            </div>
        </div>

  
        <!-- Recent Documents Section -->
        @if($recentDocuments && $recentDocuments->count() > 0)
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Recently Printed Documents</h2>
                <a href="{{ route('users.portal') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    View All Documents <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($recentDocuments->take(6) as $document)
                <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center" style="background-color: {{ $document->category->color }}20;">
                        <i class="{{ $document->category->icon }} text-sm" style="color: {{ $document->category->color }};"></i>
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $document->title }}</p>
                        <p class="text-xs text-gray-500">{{ $document->category->name }}</p>
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $document->updated_at->diffForHumans() }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
