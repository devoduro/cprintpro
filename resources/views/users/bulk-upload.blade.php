@extends('components.app-layout')

@push('styles')
<style>
.drag-drop-zone {
    border: 2px dashed #cbd5e1;
    transition: all 0.3s ease;
}
.drag-drop-zone.dragover {
    border-color: #3b82f6;
    background-color: #eff6ff;
}
.file-preview {
    max-height: 300px;
    overflow-y: auto;
}
.file-item {
    transition: all 0.2s ease;
}
.file-item:hover {
    background-color: #f8fafc;
}
.progress-bar {
    transition: width 0.3s ease;
}
.upload-success {
    animation: slideInRight 0.3s ease;
}
.upload-error {
    animation: shake 0.5s ease;
}
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
</style>
@endpush

@section('title', 'Upload Documents to Category')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Upload Documents</h1>
                    <p class="text-gray-600">Upload multiple documents to any category (requires admin approval)</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('users.portal') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Portal
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                <div>
                    <h4 class="text-blue-800 font-medium mb-1">Document Upload Policy</h4>
                    <p class="text-blue-700 text-sm">
                        Documents uploaded by users require admin approval before becoming available for viewing and printing. 
                        You'll be notified once your documents are approved.
                    </p>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Form -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <form id="bulk-upload-form" action="{{ route('users.portal.bulk-upload.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Category Selection -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Target Category</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="document_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-folder mr-2 text-blue-500"></i>
                                Document Category *
                            </label>
                            <select name="document_category_id" id="document_category_id" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    required>
                                <option value="">Choose a category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                            {{ $selectedCategory && $selectedCategory->id == $category->id ? 'selected' : '' }}
                                            data-color="{{ $category->color }}"
                                            data-icon="{{ $category->icon }}">
                                        {{ $category->name }}
                                        @if($category->parent)
                                            ({{ $category->parent->name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('document_category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-info-circle mr-2 text-gray-500"></i>
                                Upload Information
                            </label>
                            <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <i class="fas fa-check text-green-500 mr-2"></i>
                                        <span>Documents will be printable by default</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-yellow-500 mr-2"></i>
                                        <span>Requires admin approval to become active</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-user text-blue-500 mr-2"></i>
                                        <span>Uploaded by: {{ auth()->user()->name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Upload Zone -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Files</h3>
                    
                    <!-- Drag & Drop Zone -->
                    <div id="drop-zone" class="drag-drop-zone rounded-lg p-8 text-center mb-6">
                        <div class="mb-4">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <h4 class="text-lg font-medium text-gray-700 mb-2">Drag & Drop Files Here</h4>
                            <p class="text-gray-500 mb-4">or click to browse and select multiple files</p>
                            <button type="button" id="browse-btn" 
                                    class="inline-flex items-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                <i class="fas fa-folder-open mr-2"></i>
                                Browse Files
                            </button>
                        </div>
                        <input type="file" id="file-input" name="files[]" multiple 
                               accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png" 
                               class="hidden">
                        <div class="text-xs text-gray-500 mt-4">
                            Supported formats: PDF, DOC, DOCX, TXT, JPG, JPEG, PNG (Max: 10MB per file)
                        </div>
                    </div>

                    <!-- File Preview -->
                    <div id="file-preview" class="file-preview hidden">
                        <h4 class="text-md font-medium text-gray-700 mb-3">
                            <i class="fas fa-list mr-2"></i>
                            Selected Files (<span id="file-count">0</span>)
                        </h4>
                        <div id="file-list" class="space-y-2 border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <!-- Files will be listed here -->
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <button type="button" id="clear-files" 
                                    class="text-red-600 hover:text-red-700 text-sm font-medium">
                                <i class="fas fa-trash mr-1"></i>
                                Clear All Files
                            </button>
                            <div class="text-sm text-gray-600">
                                Total Size: <span id="total-size">0 MB</span>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Progress -->
                    <div id="upload-progress" class="hidden mt-6">
                        <div class="bg-gray-200 rounded-full h-3 mb-2">
                            <div id="progress-bar" class="progress-bar bg-blue-500 h-3 rounded-full" style="width: 0%"></div>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span id="progress-text">Uploading...</span>
                            <span id="progress-percent">0%</span>
                        </div>
                    </div>

                    <!-- Upload Results -->
                    <div id="upload-results" class="hidden mt-6">
                        <h4 class="text-md font-medium text-gray-700 mb-3">Upload Results</h4>
                        <div id="results-list" class="space-y-2">
                            <!-- Results will be shown here -->
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Documents will be pending admin approval after upload
                        </div>
                        <button type="submit" id="upload-btn" 
                                class="inline-flex items-center px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" 
                                disabled>
                            <i class="fas fa-upload mr-2"></i>
                            Upload Documents
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');
    const browseBtn = document.getElementById('browse-btn');
    const filePreview = document.getElementById('file-preview');
    const fileList = document.getElementById('file-list');
    const fileCount = document.getElementById('file-count');
    const totalSize = document.getElementById('total-size');
    const clearBtn = document.getElementById('clear-files');
    const uploadBtn = document.getElementById('upload-btn');
    const uploadProgress = document.getElementById('upload-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressPercent = document.getElementById('progress-percent');
    const uploadResults = document.getElementById('upload-results');
    const resultsList = document.getElementById('results-list');
    const form = document.getElementById('bulk-upload-form');
    const categorySelect = document.getElementById('document_category_id');

    let selectedFiles = [];

    // Drag and drop handlers
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });

    // Browse button click
    browseBtn.addEventListener('click', function() {
        fileInput.click();
    });

    // File input change
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    // Clear files
    clearBtn.addEventListener('click', function() {
        selectedFiles = [];
        fileInput.value = '';
        updateFilePreview();
        updateUploadButton();
    });

    // Category change
    categorySelect.addEventListener('change', function() {
        updateUploadButton();
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (selectedFiles.length === 0) {
            alert('Please select files to upload');
            return;
        }
        if (!categorySelect.value) {
            alert('Please select a category');
            return;
        }
        uploadFiles();
    });

    function handleFiles(files) {
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain', 'image/jpeg', 'image/jpg', 'image/png'];
        const maxSize = 10 * 1024 * 1024; // 10MB

        Array.from(files).forEach(file => {
            if (!allowedTypes.includes(file.type)) {
                alert(`File "${file.name}" is not a supported format`);
                return;
            }
            if (file.size > maxSize) {
                alert(`File "${file.name}" is too large (max 10MB)`);
                return;
            }
            
            // Check if file already exists
            const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (!exists) {
                selectedFiles.push(file);
            }
        });

        updateFilePreview();
        updateUploadButton();
    }

    function updateFilePreview() {
        if (selectedFiles.length === 0) {
            filePreview.classList.add('hidden');
            return;
        }

        filePreview.classList.remove('hidden');
        fileCount.textContent = selectedFiles.length;

        let totalSizeBytes = 0;
        fileList.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            totalSizeBytes += file.size;
            
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item flex items-center justify-between p-3 bg-white rounded border';
            fileItem.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-file text-gray-400 mr-3"></i>
                    <div>
                        <div class="font-medium text-gray-900">${file.name}</div>
                        <div class="text-sm text-gray-500">${formatFileSize(file.size)}</div>
                    </div>
                </div>
                <button type="button" class="text-red-500 hover:text-red-700" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            fileList.appendChild(fileItem);
        });

        totalSize.textContent = formatFileSize(totalSizeBytes);
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        updateFilePreview();
        updateUploadButton();
    }

    function updateUploadButton() {
        const hasFiles = selectedFiles.length > 0;
        const hasCategory = categorySelect.value !== '';
        uploadBtn.disabled = !hasFiles || !hasCategory;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    async function uploadFiles() {
        uploadProgress.classList.remove('hidden');
        uploadBtn.disabled = true;
        uploadResults.classList.add('hidden');

        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('document_category_id', categorySelect.value);

        selectedFiles.forEach((file, index) => {
            formData.append(`files[${index}]`, file);
        });

        try {
            const response = await fetch('{{ route("users.portal.bulk-upload.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();
            
            progressBar.style.width = '100%';
            progressPercent.textContent = '100%';
            progressText.textContent = 'Upload completed!';

            setTimeout(() => {
                uploadProgress.classList.add('hidden');
                showResults(result);
            }, 1000);

        } catch (error) {
            progressText.textContent = 'Upload failed!';
            console.error('Upload error:', error);
            alert('Upload failed. Please try again.');
        }
    }

    function showResults(result) {
        uploadResults.classList.remove('hidden');
        resultsList.innerHTML = '';

        // Success message
        const successDiv = document.createElement('div');
        successDiv.className = `p-4 rounded-lg mb-4 ${result.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}`;
        successDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${result.success ? 'check-circle text-green-500' : 'exclamation-circle text-red-500'} mr-2"></i>
                <span class="font-medium">${result.message}</span>
            </div>
        `;
        resultsList.appendChild(successDiv);

        // Uploaded files
        if (result.uploaded_files && result.uploaded_files.length > 0) {
            const successSection = document.createElement('div');
            successSection.innerHTML = `
                <h5 class="font-medium text-green-700 mb-2">Successfully Uploaded (${result.uploaded_files.length})</h5>
                <div class="space-y-1">
                    ${result.uploaded_files.map(file => `
                        <div class="flex items-center text-sm text-green-600">
                            <i class="fas fa-check mr-2"></i>
                            ${file.name} (Pending approval)
                        </div>
                    `).join('')}
                </div>
            `;
            resultsList.appendChild(successSection);
        }

        // Errors
        if (result.errors && result.errors.length > 0) {
            const errorSection = document.createElement('div');
            errorSection.className = 'mt-4';
            errorSection.innerHTML = `
                <h5 class="font-medium text-red-700 mb-2">Failed Uploads (${result.errors.length})</h5>
                <div class="space-y-1">
                    ${result.errors.map(error => `
                        <div class="text-sm text-red-600">
                            <i class="fas fa-times mr-2"></i>
                            ${error.name}: ${error.error}
                        </div>
                    `).join('')}
                </div>
            `;
            resultsList.appendChild(errorSection);
        }

        // Reset form if all successful
        if (result.success && result.errors.length === 0) {
            setTimeout(() => {
                window.location.href = '{{ route("users.portal") }}';
            }, 3000);
        } else {
            uploadBtn.disabled = false;
        }
    }

    // Make removeFile function global
    window.removeFile = removeFile;
});
</script>
@endpush
@endsection
