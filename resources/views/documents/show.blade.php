@extends('components.app-layout')

@section('title', $document->title)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
                <a href="{{ route('documents.index') }}" class="hover:text-gray-900">Documents</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span>{{ $document->title }}</span>
            </div>
            <div class="flex items-center">
                <div class="p-3 rounded-lg mr-4" style="background-color: {{ $document->category->color }}20;">
                    <i class="{{ $document->category->icon }} text-2xl" style="color: {{ $document->category->color }};"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $document->title }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $document->file_name }}</p>
                </div>
            </div>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('documents.download', $document) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-download mr-2"></i>
                Download
            </a>
            @if($document->canBePrinted())
                <form action="{{ route('documents.print', $document) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <i class="fas fa-print mr-2"></i>
                        Print
                    </button>
                </form>
            @endif
            <a href="{{ route('documents.edit', $document) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-edit mr-2"></i>
                Edit
            </a>
        </div>
    </div>

    <!-- Document Details -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Description -->
            <div class="md:col-span-2 lg:col-span-1">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Description</h3>
                <p class="text-gray-900">
                    {{ $document->description ?: 'No description provided.' }}
                </p>
            </div>

            <!-- Category -->
            <div>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Category</h3>
                <a href="{{ route('document-categories.show', $document->category) }}" class="inline-flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium hover:opacity-80 transition-opacity" 
                          style="background-color: {{ $document->category->color }}20; color: {{ $document->category->color }};">
                        <i class="{{ $document->category->icon }} mr-1"></i>
                        {{ $document->category->name }}
                    </span>
                </a>
            </div>

            <!-- Status -->
            <div>
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Status</h3>
                <div class="space-y-1">
                    @if($document->is_active)
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
                    
                    @if($document->is_printable)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <i class="fas fa-print mr-1"></i>
                            Printable
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            <i class="fas fa-ban mr-1"></i>
                            Not Printable
                        </span>
                    @endif
                </div>
            </div>
        </div>
 
 

    <!-- Document Preview Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Document Preview</h3>
            <p class="text-sm text-gray-600">Preview and interact with your document</p>
        </div>
        
        <div class="p-6">
            <!-- Preview Container -->
            <div class="mb-6">
                <div id="document-preview" class="w-full bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 relative overflow-hidden" style="height: 400px;">
                    @if(in_array(strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <img src="{{ $document->file_url }}" alt="{{ $document->title }}" class="w-full h-full object-contain">
                    @elseif(strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION)) === 'pdf')
                        <iframe src="{{ $document->file_url }}#toolbar=0&navpanes=0&scrollbar=1" class="w-full h-full border-0" title="{{ $document->title }}"></iframe>
                    @else
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center">
                                <div class="p-4 rounded-full mb-4" style="background-color: {{ $document->category->color }}20;">
                                    <i class="{{ $document->category->icon }} text-4xl" style="color: {{ $document->category->color }};"></i>
                                </div>
                                <h4 class="text-lg font-medium text-gray-900 mb-2">{{ $document->title }}</h4>
                                <p class="text-sm text-gray-600 mb-4">Preview not available for this file type</p>
                                <div class="space-y-2">
                                    <a href="{{ $document->file_url }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        <i class="fas fa-external-link-alt mr-2"></i>
                                        Open File
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Preview Overlay Actions -->
                    <div class="absolute top-4 right-4 flex space-x-2">
                        <button onclick="expandPreview()" class="p-2 bg-white bg-opacity-90 rounded-full shadow-sm hover:bg-opacity-100 transition-all" title="Expand Preview">
                            <i class="fas fa-expand text-gray-700"></i>
                        </button>
                        <a href="{{ $document->file_url }}" target="_blank" class="p-2 bg-white bg-opacity-90 rounded-full shadow-sm hover:bg-opacity-100 transition-all" title="Open in New Tab">
                            <i class="fas fa-external-link-alt text-gray-700"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Preview Button -->
                <button onclick="previewDocument({{ $document->id }})" class="flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-eye mr-2"></i>
                    Full Preview
                </button>

                <!-- Download -->
                <a href="{{ route('documents.download', $document) }}" class="flex items-center justify-center px-4 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    <i class="fas fa-download mr-2"></i>
                    Download
                </a>

                <!-- Print -->
                @if($document->canBePrinted())
                    <button onclick="printDocument({{ $document->id }})" class="flex items-center justify-center px-4 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                        <i class="fas fa-print mr-2"></i>
                        Print Now
                    </button>
                @else
                    <div class="flex items-center justify-center px-4 py-3 border border-gray-200 rounded-md text-sm font-medium text-gray-400 bg-gray-50 cursor-not-allowed">
                        <i class="fas fa-ban mr-2"></i>
                        Cannot Print
                    </div>
                @endif

                <!-- Edit -->
                <a href="{{ route('documents.edit', $document) }}" class="flex items-center justify-center px-4 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
        <h3 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h3>
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium text-red-900">Delete Document</h4>
                <p class="text-sm text-red-700">This action cannot be undone. The file will be permanently deleted.</p>
            </div>
            <form action="{{ route('documents.destroy', $document) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this document? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fas fa-trash mr-2"></i>
                    Delete Document
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@include('components.document-preview-modal')

@push('scripts')
<script src="{{ asset('js/portal.js') }}"></script>
<script>
function expandPreview() {
    previewDocument({{ $document->id }});
}
</script>
@endpush
