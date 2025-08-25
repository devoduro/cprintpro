// User Portal JavaScript Functionality
let currentDocument = null;

// Initialize portal on page load
document.addEventListener('DOMContentLoaded', function() {
    // Restore saved view preference
    const savedView = localStorage.getItem('portal_view') || 'grid';
    switchView(savedView);
    
    // Initialize tooltips and other UI elements
    initializePortal();
});

// View switching functionality
function switchView(view) {
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const gridBtn = document.getElementById('grid-view-btn');
    const listBtn = document.getElementById('list-view-btn');
    
    if (view === 'grid') {
        gridView.style.display = 'grid';
        listView.style.display = 'none';
        gridBtn.classList.add('active', 'bg-white', 'text-gray-900', 'shadow-sm');
        gridBtn.classList.remove('text-gray-600');
        listBtn.classList.remove('active', 'bg-white', 'text-gray-900', 'shadow-sm');
        listBtn.classList.add('text-gray-600');
    } else {
        gridView.style.display = 'none';
        listView.style.display = 'block';
        listBtn.classList.add('active', 'bg-white', 'text-gray-900', 'shadow-sm');
        listBtn.classList.remove('text-gray-600');
        gridBtn.classList.remove('active', 'bg-white', 'text-gray-900', 'shadow-sm');
        gridBtn.classList.add('text-gray-600');
    }
    
    // Store preference
    localStorage.setItem('portal_view', view);
}

// Document preview functionality
function previewDocument(documentId) {
    currentDocument = documentId;
    const modal = document.getElementById('preview-modal');
    const content = document.getElementById('preview-content');
    
    modal.classList.remove('hidden');
    modal.classList.add('preview-modal-enter');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Add keyboard event listener for ESC key
    document.addEventListener('keydown', handleModalKeydown);
    
    // Show loading state
    content.innerHTML = `
        <div class="flex items-center justify-center h-full">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-4"></i>
                <p class="text-gray-600">Loading preview...</p>
            </div>
        </div>
    `;
    
    // Fetch document preview
    fetch(`/api/documents/${documentId}/preview`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Call the renderPreview function from the modal component
            if (typeof renderPreview === 'function') {
                renderPreview(data.document);
            } else {
                // Fallback to basic preview if renderPreview is not available
                document.getElementById('preview-title').textContent = data.document.title;
                
                const fileExtension = data.document.file_name.split('.').pop().toLowerCase();
                
                if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(fileExtension)) {
                    content.innerHTML = `
                        <div class="flex items-center justify-center h-full">
                            <img src="${data.document.file_url}" 
                                 alt="${data.document.title}" 
                                 class="max-w-full max-h-full object-contain rounded-lg shadow-sm">
                        </div>
                    `;
                } else if (fileExtension === 'pdf') {
                    content.innerHTML = `
                        <iframe src="${data.document.file_url}#toolbar=0&navpanes=0&scrollbar=0" 
                                class="w-full h-full border-0 rounded-lg"
                                title="${data.document.title}">
                        </iframe>
                    `;
                } else if (['txt', 'md'].includes(fileExtension)) {
                    // For text files, fetch and display content
                    fetch(data.document.file_url)
                        .then(response => response.text())
                        .then(text => {
                            content.innerHTML = `
                                <div class="h-full overflow-auto p-4 bg-gray-50 rounded-lg">
                                    <pre class="whitespace-pre-wrap text-sm text-gray-800 font-mono">${text}</pre>
                                </div>
                            `;
                        })
                        .catch(() => {
                            content.innerHTML = getPreviewNotAvailable('text');
                        });
                } else {
                    content.innerHTML = getPreviewNotAvailable(fileExtension);
                }
            }
        } else {
            content.innerHTML = getPreviewError();
        }
    })
    .catch(error => {
        console.error('Preview error:', error);
        content.innerHTML = `
            <div class="flex items-center justify-center h-full">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                    <p class="text-red-600 mb-2">Preview Error</p>
                    <p class="text-sm text-gray-500">Error: ${error.message}</p>
                    <p class="text-sm text-gray-500 mt-2">Please check browser console for details</p>
                </div>
            </div>
        `;
    });
}

function getPreviewNotAvailable(fileType) {
    return `
        <div class="flex items-center justify-center h-full">
            <div class="text-center">
                <i class="fas fa-file-alt text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600 mb-2">Preview not available for ${fileType.toUpperCase()} files</p>
                <p class="text-sm text-gray-500">Use download to view the full document</p>
            </div>
        </div>
    `;
}

function getPreviewError() {
    return `
        <div class="flex items-center justify-center h-full">
            <div class="text-center">
                <i class="fas fa-exclamation-circle text-4xl text-red-400 mb-4"></i>
                <p class="text-gray-600">Error loading preview</p>
                <p class="text-sm text-gray-500 mt-2">Please try downloading the document instead</p>
            </div>
        </div>
    `;
}

// Close preview modal
function closePreview() {
    console.log('closePreview called'); // Debug log
    const modal = document.getElementById('preview-modal');
    if (!modal) {
        console.error('Preview modal not found');
        return;
    }
    
    console.log('Modal found, closing...'); // Debug log
    
    // Immediately hide modal without animation for now to test
    modal.classList.add('hidden');
    modal.classList.remove('preview-modal-exit', 'preview-modal-enter');
    document.body.style.overflow = '';
    currentDocument = null;
    
    // Clear preview content
    const content = document.getElementById('preview-content');
    if (content) {
        content.innerHTML = '';
    }
    
    // Reset fullscreen if active
    if (typeof isFullscreen !== 'undefined' && isFullscreen) {
        toggleFullscreen();
    }
    
    // Remove keyboard event listener
    document.removeEventListener('keydown', handleModalKeydown);
    
    console.log('Modal closed successfully'); // Debug log
}

// Handle keyboard events for modal
function handleModalKeydown(event) {
    if (event.key === 'Escape') {
        closePreview();
    } else if (event.key === 'f' || event.key === 'F') {
        if (typeof toggleFullscreen === 'function') {
            toggleFullscreen();
        }
    } else if (event.ctrlKey && event.key === 'p') {
        event.preventDefault();
        printFromPreview();
    } else if (event.ctrlKey && event.key === 'd') {
        event.preventDefault();
        downloadFromPreview();
    }
}

function downloadFromPreview() {
    if (!currentDocument) {
        showNotification('No document selected for download', 'error');
        return;
    }
    
    // Show loading notification
    showNotification('Preparing download...', 'info');
    
    // Use the correct endpoint for download
    window.open(`/documents/${currentDocument}/download`, '_blank');
}

function viewOriginal() {
    if (!currentDocument) {
        showNotification('No document selected', 'error');
        return;
    }
    
    // Show loading notification
    showNotification('Opening document...', 'info');
    
    // Use the correct endpoint for viewing
    window.open(`/documents/${currentDocument}/view`, '_blank');
}

// Simple print functionality
function printFromPreview() {
    if (!currentDocument) {
        showNotification('No document selected for printing', 'error');
        return;
    }
    
    const printBtn = document.getElementById('preview-print-btn');
    if (!printBtn) {
        showNotification('Print button not found', 'error');
        return;
    }
    
    const originalText = printBtn.innerHTML;
    
    // Show loading state
    printBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Printing...';
    printBtn.disabled = true;
    
    fetch(`/documents/${currentDocument}/print`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
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
            showNotification(data.message || 'Document sent to printer successfully!', 'success');
            // Update print count if provided
            if (data.total_print_count) {
                updatePrintCount(currentDocument, data.total_print_count);
            }
        } else {
            showNotification(data.message || 'Failed to print document', 'error');
        }
    })
    .catch(error => {
        console.error('Print error:', error);
        showNotification('An error occurred while printing', 'error');
    })
    .finally(() => {
        // Restore button state
        if (printBtn) {
            printBtn.innerHTML = originalText;
            printBtn.disabled = false;
        }
    });
}

// Print functionality
function printDocument(documentId) {
    if (confirm('Are you sure you want to print this document?')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            showNotification('CSRF token not found. Please refresh the page.', 'error');
            return;
        }

        // Show loading state
        showNotification('Sending document to printer...', 'info');
        
        fetch(`/documents/${documentId}/print`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
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
                // Update print count in UI
                if (data.total_print_count) {
                    updatePrintCount(documentId, data.total_print_count);
                }
            } else {
                showNotification(data.message || 'Failed to print document', 'error');
            }
        })
        .catch(error => {
            console.error('Print error:', error);
            showNotification('Error printing document. Please try again.', 'error');
        });
    }
}

// Update print count in the UI
function updatePrintCount(documentId, newCount) {
    // Update grid view
    const gridCards = document.querySelectorAll('.document-card');
    gridCards.forEach(card => {
        const printBtn = card.querySelector(`button[onclick="printDocument(${documentId})"]`);
        if (printBtn) {
            const printCountSpan = card.querySelector('.text-xs.text-gray-500 span:last-child');
            if (printCountSpan) {
                printCountSpan.textContent = `${newCount} prints`;
            }
        }
    });
    
    // Update list view
    const listRows = document.querySelectorAll('#list-view tbody tr');
    listRows.forEach(row => {
        const printBtn = row.querySelector(`button[onclick="printDocument(${documentId})"]`);
        if (printBtn) {
            const printCountCell = row.children[3];
            if (printCountCell) {
                printCountCell.textContent = newCount;
            }
        }
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
    
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
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Enhanced keyboard shortcuts
function initializeKeyboardShortcuts() {
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
}

// Initialize keyboard shortcuts when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeKeyboardShortcuts);
} else {
    initializeKeyboardShortcuts();
}

// Search functionality
function initializePortal() {
    // Initialize keyboard shortcuts
    initializeKeyboardShortcuts();
    
    // Add search keyboard shortcut
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
    
    // Add loading states to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
                
                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 5000);
            }
        });
    });
    
    // Add hover effects to document cards
    const documentCards = document.querySelectorAll('.document-card');
    documentCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

// Category filter functionality
function filterByCategory(categoryId) {
    const url = new URL(window.location);
    if (categoryId) {
        url.searchParams.set('category', categoryId);
    } else {
        url.searchParams.delete('category');
    }
    window.location.href = url.toString();
}

// File type filter functionality
function filterByFileType(fileType) {
    const url = new URL(window.location);
    if (fileType) {
        url.searchParams.set('file_type', fileType);
    } else {
        url.searchParams.delete('file_type');
    }
    window.location.href = url.toString();
}

// Bulk operations (for future enhancement)
function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.document-checkbox');
    const selectAllCheckbox = document.getElementById('select-all');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateBulkActions();
}

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.document-checkbox:checked');
    const bulkActions = document.getElementById('bulk-actions');
    
    if (bulkActions) {
        if (checkedBoxes.length > 0) {
            bulkActions.classList.remove('hidden');
        } else {
            bulkActions.classList.add('hidden');
        }
    }
}

// Export functionality
function exportDocumentsList() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = `${window.location.pathname}?${params.toString()}`;
}

// Print multiple documents
function printSelected() {
    const checkedBoxes = document.querySelectorAll('.document-checkbox:checked');
    const documentIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (documentIds.length === 0) {
        showNotification('Please select documents to print', 'warning');
        return;
    }
    
    if (confirm(`Are you sure you want to print ${documentIds.length} document(s)?`)) {
        // Implementation for bulk printing
        showNotification('Bulk printing feature coming soon!', 'info');
    }
}
