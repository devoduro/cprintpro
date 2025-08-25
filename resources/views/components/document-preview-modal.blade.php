<!-- Modern Document Preview Modal -->
<div id="preview-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-data="{ fullscreen: false }">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-all duration-300" onclick="window.closePreview()"></div>
    
    <!-- Modal Container -->
    <div class="fixed inset-0 flex items-center justify-center p-4" :class="fullscreen ? 'p-2' : 'p-4'">
        <div class="relative w-full max-w-7xl bg-white rounded-2xl shadow-2xl overflow-hidden transition-all duration-300" 
             :class="fullscreen ? 'h-screen max-w-none rounded-none' : 'max-h-[95vh]'">
            
            <!-- Header -->
            <div class="flex items-center justify-between p-6 bg-gradient-to-r from-slate-50 to-blue-50 border-b border-slate-200">
                <!-- Document Info -->
                <div class="flex items-center space-x-4 min-w-0 flex-1">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-file-alt text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-xl font-bold text-slate-900 truncate" id="preview-title">Document Preview</h3>
                        <p class="text-sm text-slate-600 truncate" id="preview-subtitle">Loading document...</p>
                    </div>
                </div>
                
                <!-- Controls -->
                <div class="flex items-center space-x-2 flex-shrink-0">
                    <!-- Zoom Controls -->
                    <div class="hidden lg:flex items-center space-x-1 bg-white rounded-lg p-1 shadow-sm border">
                        <button onclick="window.zoomOut()" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="Zoom Out">
                            <i class="fas fa-search-minus text-sm"></i>
                        </button>
                        <span class="px-2 text-sm text-slate-600 font-medium" id="zoom-level">100%</span>
                        <button onclick="window.zoomIn()" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="Zoom In">
                            <i class="fas fa-search-plus text-sm"></i>
                        </button>
                    </div>
                    
                    <!-- Action Controls -->
                    <div class="flex items-center space-x-1 bg-white rounded-lg p-1 shadow-sm border">
                        <button onclick="window.toggleFullscreen()" class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="Toggle Fullscreen">
                            <i class="fas fa-expand text-sm" id="fullscreen-icon"></i>
                        </button>
                        <button onclick="window.closePreview()" class="p-2 text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Close">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="flex flex-1 overflow-hidden" :class="fullscreen ? 'h-[calc(100vh-140px)]' : 'h-[calc(95vh-140px)]'">
                <!-- Main Preview -->
                <div class="flex-1 flex flex-col bg-slate-50">
                    <div id="preview-content" class="flex-1 overflow-auto bg-white m-4 rounded-xl shadow-inner border">
                        <!-- Loading State -->
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
                    </div>
                </div>
                
                <!-- Sidebar (Hidden on mobile, shown on larger screens) -->
                <div class="hidden lg:block w-80 bg-white border-l border-slate-200 overflow-y-auto">
                    <div class="p-6">
                        <!-- Document Details -->
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wide mb-3">Document Details</h4>
                            <div class="space-y-3" id="document-details">
                                <div class="flex justify-between">
                                    <span class="text-sm text-slate-600">Status</span>
                                    <span class="text-sm font-medium text-green-600">Ready</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wide mb-3">Quick Actions</h4>
                            <div class="space-y-2">
                                <button onclick="window.printFromPreview()" class="w-full flex items-center px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all duration-200 shadow-sm hover:shadow-md" id="preview-print-btn">
                                    <i class="fas fa-print mr-3"></i>
                                    <span class="font-medium">Print Document</span>
                                </button>
                                
                                <button onclick="window.downloadFromPreview()" class="w-full flex items-center px-4 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-lg hover:from-emerald-600 hover:to-green-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                    <i class="fas fa-download mr-3"></i>
                                    <span class="font-medium">Download</span>
                                </button>
                                
                                <button onclick="window.viewOriginal()" class="w-full flex items-center px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                    <i class="fas fa-external-link-alt mr-3"></i>
                                    <span class="font-medium">Open Original</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Keyboard Shortcuts -->
                        <div>
                            <h4 class="text-sm font-semibold text-slate-900 uppercase tracking-wide mb-3">Keyboard Shortcuts</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Close</span>
                                    <kbd class="px-2 py-1 bg-slate-100 rounded text-xs font-mono">ESC</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Print</span>
                                    <kbd class="px-2 py-1 bg-slate-100 rounded text-xs font-mono">Ctrl+P</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Download</span>
                                    <kbd class="px-2 py-1 bg-slate-100 rounded text-xs font-mono">Ctrl+D</kbd>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-600">Fullscreen</span>
                                    <kbd class="px-2 py-1 bg-slate-100 rounded text-xs font-mono">F</kbd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Footer (Shown only on mobile) -->
            <div class="lg:hidden border-t border-slate-200 bg-white p-4">
                <div class="flex space-x-2">
                    <button onclick="window.printFromPreview()" class="flex-1 flex items-center justify-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors" id="mobile-print-btn">
                        <i class="fas fa-print mr-2"></i>
                        Print
                    </button>
                    <button onclick="window.downloadFromPreview()" class="flex-1 flex items-center justify-center px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-download mr-2"></i>
                        Download
                    </button>
                    <button onclick="window.viewOriginal()" class="flex-1 flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        Open
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Simplified modal functionality - main functions are in portal.blade.php

// Remove the duplicate previewDocument function - it's now in portal.blade.php

// Remove duplicate renderPreview function - it's now in portal.blade.php

// All functionality is now handled in portal.blade.php
// This modal component only handles rendering

// Enhanced animation function
function animateContentIn() {
    const content = document.getElementById('preview-content');
    requestAnimationFrame(() => {
        content.style.transition = 'opacity 0.5s ease-in-out';
        content.style.opacity = '1';
    });
}

// Enhanced error display
function showEnhancedError(message) {
    const content = document.getElementById('preview-content');
    content.innerHTML = `
        <div class="flex items-center justify-center h-full">
            <div class="text-center max-w-lg p-8">
                <div class="relative mb-8">
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-red-100 to-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-4xl text-orange-500"></i>
                    </div>
                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-times text-white text-sm"></i>
                    </div>
                </div>
                <h4 class="text-2xl font-bold text-gray-900 mb-3">Preview Unavailable</h4>
                <p class="text-gray-600 mb-8">${message}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button onclick="downloadFromPreview()" class="group inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-2xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-download mr-2 group-hover:animate-bounce"></i>
                        Download File
                    </button>
                    <button onclick="viewOriginal()" class="group inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-external-link-alt mr-2 group-hover:rotate-12 transition-transform duration-300"></i>
                        Open Original
                    </button>
                </div>
            </div>
        </div>
    `;
    animateContentIn();
}

// Enhanced "not available" display
function getEnhancedPreviewNotAvailable(fileType) {
    return `
        <div class="flex items-center justify-center h-full">
            <div class="text-center max-w-lg p-8">
                <div class="relative mb-8">
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-gray-100 to-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-alt text-4xl text-gray-500"></i>
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-xs font-bold">${fileType}</span>
                    </div>
                </div>
                <h4 class="text-2xl font-bold text-gray-900 mb-3">Preview Not Available</h4>
                <p class="text-gray-600 mb-8">Preview is not supported for ${fileType} files, but you can still download or open the original file.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button onclick="downloadFromPreview()" class="group inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-2xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-download mr-2 group-hover:animate-bounce"></i>
                        Download ${fileType}
                    </button>
                    <button onclick="viewOriginal()" class="group inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-external-link-alt mr-2 group-hover:rotate-12 transition-transform duration-300"></i>
                        Open Original
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Enhanced animation function
function animateContentIn() {
    const content = document.getElementById('preview-content');
    requestAnimationFrame(() => {
        content.style.transition = 'opacity 0.5s ease-in-out';
        content.style.opacity = '1';
    });
}

// Enhanced error display
function showEnhancedError(message) {
    const content = document.getElementById('preview-content');
    content.innerHTML = `
        <div class="flex items-center justify-center h-full">
            <div class="text-center max-w-lg p-8">
                <div class="relative mb-8">
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-red-100 to-orange-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-4xl text-orange-500"></i>
                    </div>
                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-times text-white text-sm"></i>
                    </div>
                </div>
                <h4 class="text-2xl font-bold text-gray-900 mb-3">Preview Unavailable</h4>
                <p class="text-gray-600 mb-8">${message}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button onclick="downloadFromPreview()" class="group inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-2xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-download mr-2 group-hover:animate-bounce"></i>
                        Download File
                    </button>
                    <button onclick="viewOriginal()" class="group inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-external-link-alt mr-2 group-hover:rotate-12 transition-transform duration-300"></i>
                        Open Original
                    </button>
                </div>
            </div>
        </div>
    `;
    animateContentIn();
}

// Enhanced "not available" display
function getEnhancedPreviewNotAvailable(fileType) {
    return `
        <div class="flex items-center justify-center h-full">
            <div class="text-center max-w-lg p-8">
                <div class="relative mb-8">
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-gray-100 to-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-alt text-4xl text-gray-500"></i>
                    </div>
                    <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-xs font-bold">${fileType}</span>
                    </div>
                </div>
                <h4 class="text-2xl font-bold text-gray-900 mb-3">Preview Not Available</h4>
                <p class="text-gray-600 mb-8">Preview is not supported for ${fileType} files, but you can still download or open the original file.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button onclick="downloadFromPreview()" class="group inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-2xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-download mr-2 group-hover:animate-bounce"></i>
                        Download ${fileType}
                    </button>
                    <button onclick="viewOriginal()" class="group inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-2xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-external-link-alt mr-2 group-hover:rotate-12 transition-transform duration-300"></i>
                        Open Original
                    </button>
                </div>
            </div>
        </div>
    `;
}
</script>

<style>
/* Enhanced modal animations and styles */
@keyframes shrink {
    from { width: 100%; }
    to { width: 0%; }
}

@keyframes modalEnter {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes modalExit {
    from {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
    to {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
    }
}

@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
    }
    50% {
        box-shadow: 0 0 30px rgba(59, 130, 246, 0.6);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-5px);
    }
}

.fullscreen-modal .sm\:max-w-6xl {
    max-width: 95vw !important;
}

.fullscreen-modal .sm\:my-8 {
    margin-top: 1rem !important;
    margin-bottom: 1rem !important;
}

#preview-content iframe {
    border-radius: 1rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.preview-modal-enter {
    animation: modalEnter 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.preview-modal-exit {
    animation: modalExit 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.floating-element {
    animation: float 3s ease-in-out infinite;
}

.glow-effect {
    animation: pulse-glow 2s ease-in-out infinite;
}

.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
