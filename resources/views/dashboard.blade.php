@extends('components.app-layout')

@section('title', 'Dashboard')
@section('subtitle', 'Overview of your user management system')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Welcome Banner -->
    <div class="col-span-1 md:col-span-3">
        <div class="gradient-bg rounded-lg shadow-lg p-6 text-white">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Welcome to Printer Management System</h2>
                    <p class="opacity-90">Manage users and system administration efficiently.</p>
                </div>
            </div>
        </div>
    </div>

  
    <div class="bg-gradient-to-br from-orange-300 to-orange-400 rounded-lg shadow-lg p-6 transform transition-all duration-200 hover:scale-105">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-white bg-opacity-20">
                <i class="fas fa-book text-2xl text-white"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-white text-opacity-90">Total Courses</h3>
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-white">{{ $totalCourses ?? 0 }}</span>
                    @if(isset($courseGrowth))
                        <span class="ml-2 text-sm font-medium text-white bg-white bg-opacity-20 px-2 py-0.5 rounded-full">
                            <i class="fas fa-arrow-{{ $courseGrowth >= 0 ? 'up' : 'down' }}"></i> {{ abs($courseGrowth) }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>

    </div>

   

     

 
    <!-- Recent Activities -->
    <div class="col-span-1 md:col-span-2 bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-800">Recent Activities</h3>
            <div class="flex space-x-2">
                <button class="px-3 py-1 text-xs font-medium bg-primary-50 text-primary-600 rounded-full">All</button>
                <button class="px-3 py-1 text-xs font-medium text-gray-500 hover:bg-gray-100 rounded-full">Results</button>
                <button class="px-3 py-1 text-xs font-medium text-gray-500 hover:bg-gray-100 rounded-full">Transcripts</button>
            </div>
        </div>
        <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
            @if(!empty($recentActivities))
                @foreach($recentActivities as $activity)
                    <div class="flex items-start">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-800">{{ $activity['message'] }}</p>
                            <div class="flex items-center mt-1">
                                <span class="text-xs text-gray-500">{{ $activity['user'] }}</span>
                                <span class="mx-1 text-gray-300">•</span>
                                <span class="text-xs text-gray-500">{{ $activity['time'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-4">
                    <p class="text-gray-500">No recent activities found.</p>
                </div>
            @endif
        </div>
    </div>

  
</div>
@endsection



 