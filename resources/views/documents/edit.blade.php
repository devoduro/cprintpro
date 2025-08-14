@extends('components.app-layout')

@section('title', 'Edit Document')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('documents.index') }}" class="hover:text-gray-900">Documents</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="{{ route('documents.show', $document) }}" class="hover:text-gray-900">{{ $document->title }}</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span>Edit</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">
            Edit Document
        </h1>
        <p class="mt-1 text-sm text-gray-600">
            Update document information and file
        </p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('documents.update', $document) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Document Title <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="title" 
                       name="title" 
                       value="{{ old('title', $document->title) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-300 @enderror"
                       placeholder="Enter document title"
                       required>
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea id="description" 
                          name="description" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-300 @enderror"
                          placeholder="Enter document description">{{ old('description', $document->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div>
                <label for="document_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Category <span class="text-red-500">*</span>
                </label>
                <select id="document_category_id" 
                        name="document_category_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('document_category_id') border-red-300 @enderror"
                        required>
                    <option value="">Select a category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('document_category_id', $document->document_category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('document_category_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                @if($categories->isEmpty())
                    <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-yellow-400 mr-2 mt-0.5"></i>
                            <div class="text-sm text-yellow-700">
                                No categories available. 
                                <a href="{{ route('document-categories.create') }}" class="font-medium underline hover:no-underline">
                                    Create a category first
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Current File Info -->
            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Current File</h3>
                <div class="flex items-center">
                    <i class="fas fa-file text-gray-400 mr-3"></i>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">{{ $document->file_name }}</div>
                        <div class="text-xs text-gray-500">
                            {{ number_format($document->file_size / 1024, 2) }} KB • 
                            Uploaded {{ $document->created_at->format('M j, Y') }}
                        </div>
                    </div>
                    <a href="{{ route('documents.download', $document) }}" 
                       class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                        <i class="fas fa-download mr-1"></i>
                        Download
                    </a>
                </div>
            </div>

            <!-- File Upload (Optional) -->
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                    Replace File <span class="text-gray-500">(optional)</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                    <div class="space-y-1 text-center">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-3"></i>
                        <div class="flex text-sm text-gray-600">
                            <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload a new file</span>
                                <input id="file" 
                                       name="file" 
                                       type="file" 
                                       accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
                                       class="sr-only @error('file') border-red-300 @enderror">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">
                            PDF, DOC, DOCX, TXT, JPG, JPEG, PNG up to 10MB
                        </p>
                        <p class="text-xs text-gray-400">
                            Leave empty to keep current file
                        </p>
                    </div>
                </div>
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                <!-- File Preview -->
                <div id="file-preview" class="mt-3 hidden">
                    <div class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-md">
                        <i class="fas fa-file text-gray-400 mr-3"></i>
                        <div class="flex-1">
                            <div id="file-name" class="text-sm font-medium text-gray-900"></div>
                            <div id="file-size" class="text-xs text-gray-500"></div>
                        </div>
                        <button type="button" id="remove-file" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_printable" 
                           name="is_printable" 
                           value="1"
                           {{ old('is_printable', $document->is_printable) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_printable" class="ml-2 block text-sm text-gray-700">
                        Allow printing of this document
                    </label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $document->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                        Active (document will be available for use)
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('documents.show', $document) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-save mr-2"></i>
                    Update Document
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file');
    const filePreview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const removeFileBtn = document.getElementById('remove-file');
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            filePreview.classList.remove('hidden');
        }
    });
    
    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        filePreview.classList.add('hidden');
    });
    
    // Drag and drop functionality
    const dropZone = fileInput.closest('.border-dashed');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight(e) {
        dropZone.classList.add('border-blue-400', 'bg-blue-50');
    }
    
    function unhighlight(e) {
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    }
    
    dropZone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            fileInput.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endpush
@endsection
