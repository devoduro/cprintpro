@extends('components.app-layout')

@section('title', $parent ? $parent->name : 'Document Categories')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb Navigation -->
    @if($parent)
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('document-categories.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home mr-2"></i>
                        Categories
                    </a>
                </li>
                @foreach($parent->breadcrumbs->slice(0, -1) as $breadcrumb)
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <a href="{{ route('document-categories.index', ['parent' => $breadcrumb->id]) }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">
                                {{ $breadcrumb->name }}
                            </a>
                        </div>
                    </li>
                @endforeach
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">{{ $parent->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    @endif

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                @if($parent)
                    <div class="flex items-center">
                        <div class="p-2 rounded-lg mr-3" style="background-color: {{ $parent->color }}20;">
                            <i class="{{ $parent->icon }} text-xl" style="color: {{ $parent->color }};"></i>
                        </div>
                        {{ $parent->name }}
                        <span class="ml-2 text-sm font-normal text-gray-500">({{ $parent->isFolder() ? 'Folder' : 'Category' }})</span>
                    </div>
                @else
                    Document Categories
                @endif
            </h1>
            <p class="mt-1 text-sm text-gray-600">
                @if($parent)
                    {{ $parent->description ?: 'Manage folders and documents in this ' . ($parent->isFolder() ? 'folder' : 'category') }}
                @else
                    Organize your documents into categories for better management
                @endif
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-2">
            @if($parent)
                <a href="{{ route('document-categories.create', ['parent' => $parent->id, 'type' => 'folder']) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-folder-plus mr-2"></i>
                    New Folder
                </a>
                <a href="{{ route('documents.create', ['category' => $parent->id]) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-file-plus mr-2"></i>
                    Add Document
                </a>
            @else
                <a href="{{ route('document-categories.create', ['type' => 'category']) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-plus mr-2"></i>
                    Create Category
                </a>
            @endif
        </div>
    </div>

    <!-- Categories and Folders Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow cursor-pointer" onclick="window.location='{{ $category->isFolder() ? route('document-categories.index', ['parent' => $category->id]) : route('document-categories.show', $category) }}'">
                <!-- Category/Folder Header -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="p-2 rounded-lg" style="background-color: {{ $category->color }}20;">
                            <i class="{{ $category->icon }} text-lg" style="color: {{ $category->color }};"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900">{{ $category->name }}</h3>
                            <div class="flex items-center space-x-2 text-sm text-gray-500">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $category->isFolder() ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    <i class="fas {{ $category->isFolder() ? 'fa-folder' : 'fa-layer-group' }} mr-1"></i>
                                    {{ $category->isFolder() ? 'Folder' : 'Category' }}
                                </span>
                                <span>{{ $category->documents_count }} documents</span>
                                @if($category->children->count() > 0)
                                    <span>{{ $category->children->count() }} {{ $category->isFolder() ? 'items' : 'folders' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Badge -->
                    @if($category->is_active)
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

                <!-- Description -->
                @if($category->description)
                    <p class="text-sm text-gray-600 mb-4">{{ Str::limit($category->description, 100) }}</p>
                @endif

                <!-- Path Information -->
                @if($category->path && $category->depth > 0)
                    <div class="mb-4 p-2 bg-gray-50 rounded text-xs text-gray-500">
                        <i class="fas fa-route mr-1"></i>
                        Path: {{ $category->path }}
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-100" onclick="event.stopPropagation()">
                    <div class="flex space-x-2">
                        @if($category->isFolder())
                            <a href="{{ route('document-categories.index', ['parent' => $category->id]) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                <i class="fas fa-folder-open mr-1"></i>
                                Open
                            </a>
                        @else
                            <a href="{{ route('document-categories.show', $category) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i>
                                View
                            </a>
                        @endif
                        <a href="{{ route('document-categories.edit', $category) }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                            <i class="fas fa-edit mr-1"></i>
                            Edit
                        </a>
                    </div>
                    
                    <div class="flex space-x-2">
                        <!-- Toggle Status -->
                        <form action="{{ route('document-categories.toggle-status', $category) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-sm font-medium {{ $category->is_active ? 'text-orange-600 hover:text-orange-700' : 'text-green-600 hover:text-green-700' }}">
                                {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                        
                        <!-- Delete -->
                        @if($category->documents_count == 0)
                            <form action="{{ route('document-categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-12">
                    <i class="fas fa-folder-open text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No categories yet</h3>
                    <p class="text-gray-600 mb-4">Get started by creating your first document category.</p>
                    <a href="{{ route('document-categories.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>
                        Create Category
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($categories->hasPages())
        <div class="mt-6">
            {{ $categories->links() }}
        </div>
    @endif
</div>
@endsection
