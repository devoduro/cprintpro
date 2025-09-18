@extends('components.app-layout')

@push('styles')
<style>
.view-toggle {
    color: #6b7280;
}
.view-toggle.active {
    background-color: #3b82f6;
    color: white;
}
.view-toggle:hover:not(.active) {
    background-color: #f3f4f6;
    color: #374151;
}
</style>
@endpush

@push('scripts')
<script>
console.log('Portal JavaScript loading...');

// Global variables
let currentDocument = null;
let isFullscreen = false;

// Notification system
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification-toast');
    existingNotifications.forEach(notification => notification.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification-toast fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
    
    // Set colors based on type
    switch (type) {
        case 'success':
            notification.className += ' bg-green-500 text-white';
            break;
        case 'error':
            notification.className += ' bg-red-500 text-white';
            break;
        case 'warning':
            notification.className += ' bg-yellow-500 text-white';
            break;
        default:
            notification.className += ' bg-blue-500 text-white';
    }
    
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Close preview modal function
function closePreview() {
    console.log('Closing preview modal...');
    const modal = document.getElementById('preview-modal');
    if (!modal) {
        console.error('Preview modal not found');
        return;
    }
    
    // Hide modal and restore body scroll
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    currentDocument = null;
    
    // Reset fullscreen state
    if (isFullscreen) {
        isFullscreen = false;
        const icon = document.getElementById('fullscreen-icon');
        if (icon) {
            icon.className = 'fas fa-expand';
        }
    }
    
    // Clear content
    const content = document.getElementById('preview-content');
    if (content) {
        content.innerHTML = '';
    }
    
    console.log('Preview modal closed successfully');
}

// Print from preview function
function printFromPreview() {
    console.log('Print from preview called, currentDocument:', currentDocument);
    
    if (!currentDocument) {
        console.error('No currentDocument set');
        showNotification('No document selected for printing', 'error');
        return;
    }
    
    // Try to find print button with multiple selectors
    let printBtn = document.getElementById('preview-print-btn');
    if (!printBtn) {
        printBtn = document.getElementById('mobile-print-btn');
    }
    
    let originalText = 'Print Document';
    if (printBtn) {
        originalText = printBtn.innerHTML;
        // Show loading state
        printBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Printing...';
        printBtn.disabled = true;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        showNotification('CSRF token not found. Please refresh the page.', 'error');
        if (printBtn) {
            printBtn.innerHTML = originalText;
            printBtn.disabled = false;
        }
        return;
    }
    
    console.log('Sending print request for document:', currentDocument);
    showNotification('Sending document to printer...', 'info');
    
    fetch(`/documents/${currentDocument}/print`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('Print response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Print response data:', data);
        if (data.success) {
            showNotification(data.message || 'Document sent to printer successfully!', 'success');
        } else {
            showNotification(data.message || 'Failed to print document', 'error');
        }
    })
    .catch(error => {
        console.error('Print error:', error);
        showNotification('An error occurred while printing: ' + error.message, 'error');
    })
    .finally(() => {
        // Restore button state
        if (printBtn) {
            printBtn.innerHTML = originalText;
            printBtn.disabled = false;
        }
    });
}

// Print document function (for grid/list view)
function printDocument(documentId) {
    if (confirm('Are you sure you want to print this document?')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            showNotification('CSRF token not found. Please refresh the page.', 'error');
            return;
        }

        showNotification('Sending document to printer...', 'info');
        
        fetch(`/documents/${documentId}/print`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('Document sent to printer successfully!', 'success');
            } else {
                showNotification(data.message || 'Failed to print document', 'error');
            }
        })
        .catch(error => {
            console.error('Print error:', error);
            showNotification('Error printing document: ' + error.message, 'error');
        });
    }
}

// Download from preview function
function downloadFromPreview() {
    if (!currentDocument) {
        showNotification('No document selected for download', 'error');
        return;
    }
    
    showNotification('Preparing download...', 'info');
    window.open(`/documents/${currentDocument}/download`, '_blank');
}

// View original function
function viewOriginal() {
    if (!currentDocument) {
        showNotification('No document selected', 'error');
        return;
    }
    
    showNotification('Opening document...', 'info');
    window.open(`/documents/${currentDocument}/view`, '_blank');
}

// Toggle fullscreen function
function toggleFullscreen() {
    const modal = document.getElementById('preview-modal');
    const icon = document.getElementById('fullscreen-icon');
    
    if (!modal) {
        console.error('Preview modal not found');
        return;
    }
    
    // Use Alpine.js data if available, otherwise fallback to class manipulation
    const alpineComponent = modal.querySelector('[x-data]');
    if (alpineComponent && alpineComponent.__x) {
        const currentFullscreen = alpineComponent.__x.$data.fullscreen;
        alpineComponent.__x.$data.fullscreen = !currentFullscreen;
        isFullscreen = !currentFullscreen;
        
        if (icon) {
            icon.className = isFullscreen ? 'fas fa-compress text-sm' : 'fas fa-expand text-sm';
        }
    } else {
        // Fallback for non-Alpine implementation
        if (!isFullscreen) {
            modal.classList.add('fullscreen-modal');
            if (icon) icon.className = 'fas fa-compress text-sm';
            isFullscreen = true;
        } else {
            modal.classList.remove('fullscreen-modal');
            if (icon) icon.className = 'fas fa-expand text-sm';
            isFullscreen = false;
        }
    }
}

// Keyboard event handler
document.addEventListener('keydown', function(event) {
    const modal = document.getElementById('preview-modal');
    const isModalOpen = modal && !modal.classList.contains('hidden');
    
    if (isModalOpen) {
        switch(event.key) {
            case 'Escape':
                event.preventDefault();
                closePreview();
                break;
            case 'f':
            case 'F':
                if (!event.ctrlKey && !event.altKey && !event.metaKey) {
                    event.preventDefault();
                    toggleFullscreen();
                }
                break;
            case 'p':
            case 'P':
                if (event.ctrlKey) {
                    event.preventDefault();
                    printFromPreview();
                }
                break;
            case 'd':
            case 'D':
                if (event.ctrlKey) {
                    event.preventDefault();
                    downloadFromPreview();
                }
                break;
        }
    }
});

// View switching function
function switchView(viewType) {
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const gridBtn = document.getElementById('grid-view-btn');
    const listBtn = document.getElementById('list-view-btn');
    
    if (viewType === 'grid') {
        gridView.style.display = 'grid';
        listView.style.display = 'none';
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
    } else {
        gridView.style.display = 'none';
        listView.style.display = 'block';
        listBtn.classList.add('active');
        gridBtn.classList.remove('active');
    }
}

// Make functions available globally - CRITICAL for button functionality
console.log('Assigning functions to window object...');
window.closePreview = closePreview;
window.printFromPreview = printFromPreview;
window.downloadFromPreview = downloadFromPreview;
window.viewOriginal = viewOriginal;
window.toggleFullscreen = toggleFullscreen;
window.showNotification = showNotification;
window.printDocument = printDocument;
window.zoomIn = zoomIn;
window.zoomOut = zoomOut;
window.updateZoom = updateZoom;
window.toggleImageZoom = toggleImageZoom;
window.previewDocument = previewDocument;
window.renderPreview = renderPreview;
window.switchView = switchView;

// Make variables globally accessible
Object.defineProperty(window, 'currentDocument', {
    get: function() { return currentDocument; },
    set: function(value) { currentDocument = value; }
});

console.log('All functions assigned to window. Testing access...');
console.log('window.previewDocument:', typeof window.previewDocument);
console.log('window.printFromPreview:', typeof window.printFromPreview);
console.log('window.downloadFromPreview:', typeof window.downloadFromPreview);

// Preview document function
function previewDocument(documentId, documentData = null) {
    console.log('Opening preview for document:', documentId);
    
    // Set current document globally
    currentDocument = documentId;
    window.currentDocument = documentId;
    
    const modal = document.getElementById('preview-modal');
    const content = document.getElementById('preview-content');
    const title = document.getElementById('preview-title');
    const subtitle = document.getElementById('preview-subtitle');
    
    if (!modal || !content) {
        console.error('Preview modal elements not found');
        return;
    }
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Show loading state
    content.innerHTML = `
        <div class="flex items-center justify-center h-full">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full animate-spin opacity-20"></div>
                    <div class="absolute inset-2 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-alt text-white animate-pulse"></i>
                    </div>
                </div>
                <h4 class="text-lg font-semibold text-slate-800 mb-2">Loading Preview</h4>
                <p class="text-slate-600">Please wait while we prepare your document...</p>
            </div>
        </div>
    `;
    
    // Fetch document preview
    fetch(`/api/documents/${documentId}/preview`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            renderPreview(data.document);
        } else {
            throw new Error(data.message || 'Failed to load preview');
        }
    })
    .catch(error => {
        console.error('Preview error:', error);
        content.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-center max-w-md">
                    <i class="fas fa-exclamation-triangle text-5xl text-yellow-500 mb-4"></i>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Preview Unavailable</h4>
                    <p class="text-gray-600 mb-4">${error.message}</p>
                </div>
            </div>
        `;
    });
}

// Enhanced zoom functionality
let currentZoom = 100;
function zoomIn() {
    if (currentZoom < 200) {
        currentZoom += 25;
        updateZoom();
    }
}

function zoomOut() {
    if (currentZoom > 50) {
        currentZoom -= 25;
        updateZoom();
    }
}

function updateZoom() {
    const zoomLevel = document.getElementById('zoom-level');
    const content = document.getElementById('preview-content');
    
    if (zoomLevel) zoomLevel.textContent = currentZoom + '%';
    if (content) {
        const iframe = content.querySelector('iframe');
        const img = content.querySelector('img');
        
        if (iframe) {
            iframe.style.transform = `scale(${currentZoom / 100})`;
            iframe.style.transformOrigin = 'top left';
        }
        if (img) {
            img.style.transform = `scale(${currentZoom / 100})`;
            img.style.transformOrigin = 'center';
        }
    }
}

// Image zoom toggle functionality
function toggleImageZoom(img) {
    if (img.classList.contains('scale-150')) {
        img.classList.remove('scale-150', 'cursor-zoom-out');
        img.classList.add('cursor-zoom-in');
    } else {
        img.classList.add('scale-150', 'cursor-zoom-out');
        img.classList.remove('cursor-zoom-in');
    }
}

// Render preview function
function renderPreview(documentData) {
    const content = document.getElementById('preview-content');
    const titleElement = document.getElementById('preview-title');
    const subtitleElement = document.getElementById('preview-subtitle');
    const detailsElement = document.getElementById('document-details');
    
    if (!content) {
        console.error('Preview content element not found');
        return;
    }
    
    const fileExtension = documentData.file_name.split('.').pop().toLowerCase();
    
    // Update modal info
    if (titleElement) titleElement.textContent = documentData.title;
    if (subtitleElement) {
        subtitleElement.innerHTML = `
            <i class="fas fa-file mr-2 text-blue-500"></i>
            ${documentData.file_name} • ${documentData.file_size_formatted}
        `;
    }
    
    // Update document details sidebar
    if (detailsElement) {
        detailsElement.innerHTML = `
            <div class="flex justify-between">
                <span class="text-sm text-slate-600">Status</span>
                <span class="text-sm font-medium text-green-600">Ready</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-slate-600">File Type</span>
                <span class="text-sm font-medium text-slate-900">${fileExtension.toUpperCase()}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm text-slate-600">Size</span>
                <span class="text-sm font-medium text-slate-900">${documentData.file_size_formatted}</span>
            </div>
            ${documentData.category ? `
            <div class="flex justify-between">
                <span class="text-sm text-slate-600">Category</span>
                <span class="text-sm font-medium" style="color: ${documentData.category.color}">${documentData.category.name}</span>
            </div>
            ` : ''}
            <div class="flex justify-between">
                <span class="text-sm text-slate-600">Created</span>
                <span class="text-sm font-medium text-slate-900">${new Date(documentData.created_at).toLocaleDateString()}</span>
            </div>
        `;
    }
    
    // Enhanced preview rendering based on file type
    switch (fileExtension) {
        case 'pdf':
            content.innerHTML = `
                <div class="h-full relative group">
                    <div class="absolute top-4 right-4 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="bg-black/70 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm flex items-center">
                            <i class="fas fa-file-pdf mr-2 text-red-400"></i>
                            PDF Document
                        </div>
                    </div>
                    <iframe src="${documentData.file_url}#toolbar=0&navpanes=0&scrollbar=1&zoom=page-fit" 
                            class="w-full h-full border-0"
                            title="${documentData.title}"
                            loading="lazy">
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center p-8">
                                <i class="fas fa-file-pdf text-6xl text-red-500 mb-4"></i>
                                <p class="text-slate-600 mb-4">Your browser doesn't support PDF viewing.</p>
                                <button onclick="window.downloadFromPreview()" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    <i class="fas fa-download mr-2"></i>
                                    Download PDF
                                </button>
                            </div>
                        </div>
                    </iframe>
                </div>
            `;
            break;
            
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
        case 'webp':
        case 'svg':
            content.innerHTML = `
                <div class="flex items-center justify-center h-full p-6 relative group">
                    <div class="absolute top-4 right-4 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <div class="bg-black/70 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm flex items-center">
                            <i class="fas fa-image mr-2 text-blue-400"></i>
                            ${fileExtension.toUpperCase()} Image
                        </div>
                    </div>
                    <img src="${documentData.file_url}" 
                         alt="${documentData.title}" 
                         class="max-w-full max-h-full object-contain rounded-lg shadow-lg transition-transform duration-300 hover:scale-105 cursor-zoom-in"
                         loading="lazy"
                         onclick="window.toggleImageZoom(this)"
                         onerror="this.parentElement.innerHTML='<div class=\'text-center p-8\'><i class=\'fas fa-image text-6xl text-slate-400 mb-4\'></i><h4 class=\'text-xl font-semibold text-slate-700 mb-2\'>Failed to Load Image</h4><p class=\'text-slate-600\'>The image could not be displayed</p></div>'">
                </div>
            `;
            break;
            
        default:
            content.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="text-center p-8 max-w-md">
                        <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-slate-500 to-slate-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-file text-white text-2xl"></i>
                        </div>
                        <h4 class="text-xl font-semibold text-slate-800 mb-3">${fileExtension.toUpperCase()} File</h4>
                        <p class="text-slate-600 mb-6">Preview not available for this file type. Use the actions below to download or view the file.</p>
                        <div class="space-y-3">
                            <button onclick="window.downloadFromPreview()" class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-lg hover:from-emerald-600 hover:to-green-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-download mr-2"></i>
                                Download File
                            </button>
                            <button onclick="window.viewOriginal()" class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                Open in New Tab
                            </button>
                        </div>
                    </div>
                </div>
            `;
    }
}

// Make functions available globally - CRITICAL for button functionality
console.log('Assigning functions to window object...');
window.closePreview = closePreview;
window.printFromPreview = printFromPreview;
window.downloadFromPreview = downloadFromPreview;
window.viewOriginal = viewOriginal;
window.toggleFullscreen = toggleFullscreen;
window.showNotification = showNotification;
window.printDocument = printDocument;
window.zoomIn = zoomIn;
window.zoomOut = zoomOut;
window.updateZoom = updateZoom;
window.toggleImageZoom = toggleImageZoom;
window.previewDocument = previewDocument;
window.renderPreview = renderPreview;

// Make variables globally accessible
Object.defineProperty(window, 'currentDocument', {
    get: function() { return currentDocument; },
    set: function(value) { currentDocument = value; }
});

console.log('All functions assigned to window. Testing access...');
console.log('window.previewDocument:', typeof window.previewDocument);
console.log('window.printFromPreview:', typeof window.printFromPreview);
console.log('window.downloadFromPreview:', typeof window.downloadFromPreview);

</script>
@endpush

@section('title', 'Document Portal')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
   
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Enhanced Search and Filter Bar -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8 mb-8 hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Find Your Documents</h2>
                    <p class="text-gray-600">Search, filter, and discover documents quickly</p>
                </div>
                <div class="hidden lg:block">
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <i class="fas fa-lightbulb text-yellow-500"></i>
                        <span>Pro tip: Use keywords or file types to narrow results</span>
                    </div>
                </div>
            </div>
            
            <form method="GET" action="{{ route('users.portal') }}" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    <!-- Enhanced Search Input -->
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Search documents by title, filename, or description..."
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="w-full lg:w-64">
                        <select name="category" class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- File Type Filter -->
                    <div class="w-full lg:w-48">
                        <select name="file_type" class="block w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Types</option>
                            <option value="pdf" {{ request('file_type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                            <option value="doc" {{ request('file_type') == 'doc' ? 'selected' : '' }}>Word</option>
                            <option value="txt" {{ request('file_type') == 'txt' ? 'selected' : '' }}>Text</option>
                            <option value="image" {{ request('file_type') == 'image' ? 'selected' : '' }}>Images</option>
                        </select>
                    </div>
                    
                    <!-- Filter Button -->
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-colors">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                </div>
                
                <!-- Quick Filter Tags -->
                @if(request()->hasAny(['search', 'category', 'file_type']))
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Active filters:</span>
                        @if(request('search'))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Search: "{{ request('search') }}"
                                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="ml-2 text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        @endif
                        @if(request('category'))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Category: {{ $categories->find(request('category'))->name ?? 'Unknown' }}
                                <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="ml-2 text-green-600 hover:text-green-800">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        @endif
                        <a href="{{ route('users.portal') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear all</a>
                    </div>
                @endif
            </form>
        </div>

        <!-- View Toggle -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium text-gray-700">View:</span>
                <div class="flex bg-gray-100 rounded-lg p-1">
                    <button onclick="switchView('grid')" id="grid-view-btn" class="px-3 py-1 rounded-md text-sm font-medium transition-colors view-toggle">
                        <i class="fas fa-th-large mr-1"></i>
                        Grid
                    </button>
                    <button onclick="switchView('list')" id="list-view-btn" class="px-3 py-1 rounded-md text-sm font-medium transition-colors view-toggle active">
                        <i class="fas fa-list mr-1"></i>
                        List
                    </button>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    Showing {{ $documents->count() }} of {{ $documents->total() }} documents
                    <span class="text-gray-400 ml-2">(Read-only access)</span>
                </div>
                 
            </div>
        </div>

        <!-- Documents Grid/List View -->
        <div id="documents-container">
            @if($documents->count() > 0)
                <!-- Grid View -->
                <div id="grid-view" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" style="display: none;">
                    @foreach($documents as $document)
                        <div class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-100 hover:border-gray-200 transform hover:-translate-y-2 document-card overflow-hidden">
                            <!-- Enhanced Document Thumbnail/Icon -->
                            <div class="relative h-52 bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden">
                                @if(in_array(strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    <img src="{{ $document->file_url }}" alt="{{ $document->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                @else
                                    <div class="flex items-center justify-center h-full relative">
                                        <div class="text-center z-10">
                                            @php
                                                $extension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                                                $iconClass = match($extension) {
                                                    'pdf' => 'fas fa-file-pdf text-red-500',
                                                    'doc', 'docx' => 'fas fa-file-word text-blue-600',
                                                    'xls', 'xlsx' => 'fas fa-file-excel text-green-600',
                                                    'ppt', 'pptx' => 'fas fa-file-powerpoint text-orange-600',
                                                    'txt' => 'fas fa-file-alt text-gray-600',
                                                    'zip', 'rar' => 'fas fa-file-archive text-purple-600',
                                                    default => 'fas fa-file text-gray-500'
                                                };
                                            @endphp
                                            <i class="{{ $iconClass }} text-6xl mb-3 group-hover:scale-110 transition-transform duration-300"></i>
                                            <div class="bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full">
                                                <p class="text-sm font-bold text-gray-700 uppercase tracking-wider">{{ strtoupper($extension) }}</p>
                                            </div>
                                        </div>
                                        <!-- Background Pattern -->
                                        <div class="absolute inset-0 opacity-10">
                                            <div class="w-full h-full" style="background-image: radial-gradient(circle at 25% 25%, #f3f4f6 2px, transparent 2px), radial-gradient(circle at 75% 75%, #e5e7eb 2px, transparent 2px); background-size: 20px 20px;"></div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Enhanced Quick Actions Overlay -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-end justify-center pb-4">
                                    <div class="flex space-x-3 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                        @if($document->canBePrinted())
                                            <form action="{{ route('documents.print', $document) }}" method="POST" target="_blank" class="inline">
                                                @csrf
                                                <button type="submit" class="p-3 bg-white/90 backdrop-blur-sm rounded-full text-gray-700 hover:text-purple-600 hover:bg-purple-50 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-110" title="Print Document">
                                                    <i class="fas fa-print text-lg"></i>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('documents.view', $document) }}" target="_blank" class="p-3 bg-white/90 backdrop-blur-sm rounded-full text-gray-700 hover:text-blue-600 hover:bg-blue-50 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-110" title="View Document">
                                                <i class="fas fa-eye text-lg"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('documents.download', $document) }}" class="p-3 bg-white/90 backdrop-blur-sm rounded-full text-gray-700 hover:text-green-600 hover:bg-green-50 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-110" title="Download">
                                            <i class="fas fa-download text-lg"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Status Badge -->
                                @if($document->is_active)
                                    <div class="absolute top-3 right-3">
                                        <div class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold flex items-center shadow-lg">
                                            <div class="w-2 h-2 bg-white rounded-full mr-1 animate-pulse"></div>
                                            Active
                                        </div>
                                    </div>
                                @endif
                                
                            </div>
                            
                            <!-- Document Information -->
                            <div class="p-4">
                                <!-- Category Badge -->
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" style="background-color: {{ $document->category->color }}20; color: {{ $document->category->color }};">
                                        <i class="{{ $document->category->icon }} mr-1"></i>
                                        {{ $document->category->name }}
                                    </span>
                                </div>
                                
                             
                                
                           
                                
                                <!-- Action Buttons -->
                                <div class="mt-4 flex space-x-2">
                                    @if($document->canBePrinted())
                                        <form action="{{ route('documents.print', $document) }}" method="POST" target="_blank" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full px-3 py-2 bg-purple-600 text-white text-xs rounded-md hover:bg-purple-700 transition-colors">
                                                <i class="fas fa-print mr-1"></i>
                                                Print
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('documents.view', $document) }}" target="_blank" class="flex-1 px-3 py-2 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 transition-colors text-center inline-block">
                                            <i class="fas fa-eye mr-1"></i>
                                            View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- List View -->
                <div id="list-view" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($documents as $document)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-lg flex items-center justify-center" style="background-color: {{ $document->category->color }}20;">
                                                        <i class="{{ $document->category->icon }} text-sm" style="color: {{ $document->category->color }};"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-base font-bold text-gray-900 mb-1">{{ $document->title }}</div>
                                                    <div class="text-sm text-gray-600 flex items-center">
                                                        <i class="fas fa-file mr-1 text-gray-400"></i>
                                                        {{ $document->file_name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                                  style="background-color: {{ $document->category->color }}20; color: {{ $document->category->color }};">
                                                {{ $document->category->name }}
                                            </span>
                                        </td>
                                         
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                @if($document->canBePrinted())
                                                    <form action="{{ route('documents.print', $document) }}" method="POST" target="_blank" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-purple-600 hover:text-purple-900" title="Print Document">
                                                            <i class="fas fa-print"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('documents.view', $document) }}" target="_blank" class="text-blue-600 hover:text-blue-900" title="View Document">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('documents.download', $document) }}" class="text-green-600 hover:text-green-900" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
                        <i class="fas fa-folder-open text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No documents found</h3>
                    <p class="text-gray-600 mb-6">
                        @if(request()->hasAny(['search', 'category', 'file_type']))
                            Try adjusting your search criteria or filters.
                        @else
                            No documents are available in your portal yet.
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'category', 'file_type']))
                        <a href="{{ route('users.portal') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-refresh mr-2"></i>
                            Clear Filters
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Pagination -->
        @if($documents->hasPages())
            <div class="mt-8">
                {{ $documents->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

<!-- Include Document Preview Modal Component -->
<x-document-preview-modal />
