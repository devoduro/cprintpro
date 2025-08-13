@extends('components.app-layout')

@section('title', $documentCategory->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
                <a href="{{ route('document-categories.index') }}" class="hover:text-gray-900">Document Categories</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span>{{ $documentCategory->name }}</span>
            </div>
            <div class="flex items-center">
                <div class="p-3 rounded-lg mr-4" style="background-color: {{ $documentCategory->color }}20;">
                    <i class="{{ $documentCategory->icon }} text-2xl" style="color: {{ $documentCategory->color }};"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $documentCategory->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $documentCategory->documents->count() }} documents in this category</p>
                </div>
            </div>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('document-categories.edit', $documentCategory) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-edit mr-2"></i>
                Edit Category
            </a>
            <a href="{{ route('documents.create') }}?category={{ $documentCategory->id }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-plus mr-2"></i>
                Add Document
            </a>
        </div>
    </div>

    <!-- Category Details -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Description -->
            <div class="md:col-span-2">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Description</h3>
                <p class="text-gray-900">
                    {{ $documentCategory->description ?: 'No description provided.' }}
                </p>
            </div>

            <!-- Status -->
            <div>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Status</h3>
                @if($documentCategory->is_active)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>
                        Active
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <i class="fas fa-pause-circle mr-1"></i>
                        Inactive
                    </span>
                @endif
            </div>

            <!-- Sort Order -->
            <div>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Sort Order</h3>
                <p class="text-gray-900">{{ $documentCategory->sort_order }}</p>
            </div>
        </div>

        <!-- Additional Stats -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $documentCategory->documents->count() }}</div>
                    <div class="text-sm text-gray-500">Total Documents</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $documentCategory->documents->where('is_active', true)->count() }}</div>
                    <div class="text-sm text-gray-500">Active Documents</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $documentCategory->documents->sum('print_count') }}</div>
                    <div class="text-sm text-gray-500">Total Prints</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents in this Category -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">Documents in this Category</h2>
                @if($documentCategory->documents->count() > 0)
                    <a href="{{ route('documents.index') }}?category={{ $documentCategory->id }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        View all in Documents <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                @endif
            </div>
        </div>

        @if($documentCategory->documents->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Document
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Size
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Prints
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Uploaded
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($documentCategory->documents->take(10) as $document)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded flex items-center justify-center" style="background-color: {{ $documentCategory->color }}20;">
                                                <i class="{{ $documentCategory->icon }} text-xs" style="color: {{ $documentCategory->color }};"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $document->title }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $document->file_name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $document->file_size_formatted }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $document->print_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($document->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $document->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs">by {{ $document->uploader->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <!-- View -->
                                    <a href="{{ route('documents.show', $document) }}" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <!-- Download -->
                                    <a href="{{ route('documents.download', $document) }}" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    
                                    <!-- Print -->
                                    @if($document->canBePrinted())
                                        <form action="{{ route('documents.print', $document) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-purple-600 hover:text-purple-900">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <!-- Edit -->
                                    <a href="{{ route('documents.edit', $document) }}" class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($documentCategory->documents->count() > 10)
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 text-center">
                    <a href="{{ route('documents.index') }}?category={{ $documentCategory->id }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        View all {{ $documentCategory->documents->count() }} documents in this category
                    </a>
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <i class="fas fa-file-alt text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No documents yet</h3>
                <p class="text-gray-600 mb-4">This category doesn't have any documents yet.</p>
                <a href="{{ route('documents.create') }}?category={{ $documentCategory->id }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>
                    Add First Document
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
