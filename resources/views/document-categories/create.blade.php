@extends('components.app-layout')

@section('title', 'Create ' . ($type === 'folder' ? 'Folder' : 'Category'))

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('document-categories.index') }}" class="hover:text-gray-900">Document Categories</a>
            @if($parent)
                @foreach($parent->breadcrumbs as $breadcrumb)
                    <i class="fas fa-chevron-right text-xs"></i>
                    <a href="{{ route('document-categories.index', ['parent' => $breadcrumb->id]) }}" class="hover:text-gray-900">{{ $breadcrumb->name }}</a>
                @endforeach
            @endif
            <i class="fas fa-chevron-right text-xs"></i>
            <span>Create {{ $type === 'folder' ? 'Folder' : 'Category' }}</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">
            Create {{ $type === 'folder' ? 'Folder' : 'Document Category' }}
            @if($parent)
                <span class="text-lg font-normal text-gray-500">in {{ $parent->name }}</span>
            @endif
        </h1>
        <p class="mt-1 text-sm text-gray-600">
            {{ $type === 'folder' ? 'Add a new folder to organize documents and subfolders' : 'Add a new category to organize your documents' }}
        </p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('document-categories.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Hidden Fields -->
            @if($parent)
                <input type="hidden" name="parent_id" value="{{ $parent->id }}">
            @endif
            <input type="hidden" name="type" value="{{ $type }}">

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ $type === 'folder' ? 'Folder' : 'Category' }} Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-300 @enderror"
                       placeholder="Enter category name"
                       required>
                @error('name')
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
                          placeholder="Enter category description">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Color and Icon Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Color -->
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                        Color <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="color" 
                               id="color" 
                               name="color" 
                               value="{{ old('color', '#3B82F6') }}"
                               class="h-10 w-16 border border-gray-300 rounded-md cursor-pointer @error('color') border-red-300 @enderror">
                        <input type="text" 
                               id="color-text" 
                               value="{{ old('color', '#3B82F6') }}"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               readonly>
                    </div>
                    @error('color')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Icon -->
                <div>
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                        Icon <span class="text-red-500">*</span>
                    </label>
                    <select id="icon" 
                            name="icon" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('icon') border-red-300 @enderror">
                        <option value="fas fa-folder" {{ old('icon') == 'fas fa-folder' ? 'selected' : '' }}>📁 Folder</option>
                        <option value="fas fa-file-pdf" {{ old('icon') == 'fas fa-file-pdf' ? 'selected' : '' }}>📄 PDF</option>
                        <option value="fas fa-file-word" {{ old('icon') == 'fas fa-file-word' ? 'selected' : '' }}>📝 Document</option>
                        <option value="fas fa-image" {{ old('icon') == 'fas fa-image' ? 'selected' : '' }}>🖼️ Image</option>
                        <option value="fas fa-print" {{ old('icon') == 'fas fa-print' ? 'selected' : '' }}>🖨️ Print</option>
                        <option value="fas fa-archive" {{ old('icon') == 'fas fa-archive' ? 'selected' : '' }}>📦 Archive</option>
                        <option value="fas fa-clipboard" {{ old('icon') == 'fas fa-clipboard' ? 'selected' : '' }}>📋 Clipboard</option>
                        <option value="fas fa-book" {{ old('icon') == 'fas fa-book' ? 'selected' : '' }}>📚 Book</option>
                    </select>
                    @error('icon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                    Sort Order
                </label>
                <input type="number" 
                       id="sort_order" 
                       name="sort_order" 
                       value="{{ old('sort_order', 0) }}"
                       min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('sort_order') border-red-300 @enderror"
                       placeholder="0">
                <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                @error('sort_order')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status -->
            <div class="flex items-center">
                <input type="checkbox" 
                       id="is_active" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">
                    Active (category will be available for use)
                </label>
            </div>

            <!-- Preview -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Preview</h4>
                <div class="flex items-center">
                    <div id="preview-icon" class="p-2 rounded-lg bg-blue-100">
                        <i class="fas fa-folder text-lg text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <h5 id="preview-name" class="text-sm font-medium text-gray-900">Category Name</h5>
                        <p id="preview-description" class="text-xs text-gray-500">Category description will appear here</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('document-categories.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('color');
    const colorText = document.getElementById('color-text');
    const iconSelect = document.getElementById('icon');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    
    const previewIcon = document.getElementById('preview-icon');
    const previewIconElement = previewIcon.querySelector('i');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');
    
    function updatePreview() {
        const color = colorInput.value;
        const icon = iconSelect.value;
        const name = nameInput.value || 'Category Name';
        const description = descriptionInput.value || 'Category description will appear here';
        
        // Update preview
        previewIcon.style.backgroundColor = color + '20';
        previewIconElement.className = icon + ' text-lg';
        previewIconElement.style.color = color;
        previewName.textContent = name;
        previewDescription.textContent = description;
    }
    
    // Color input sync
    colorInput.addEventListener('input', function() {
        colorText.value = this.value;
        updatePreview();
    });
    
    // Other inputs
    iconSelect.addEventListener('change', updatePreview);
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    
    // Initial preview
    updatePreview();
});
</script>
@endpush
@endsection
