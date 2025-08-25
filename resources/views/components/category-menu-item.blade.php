@php
    $hasChildren = $category->children->where('is_active', true)->count() > 0;
    $isFolder = $category->isFolder() || $hasChildren;
    $totalDocs = $category->activeDocuments->count();
    
    // Calculate total documents including children
    if ($hasChildren) {
        foreach ($category->children->where('is_active', true) as $child) {
            $totalDocs += $child->activeDocuments->count();
            foreach ($child->children->where('is_active', true) as $grandchild) {
                $totalDocs += $grandchild->activeDocuments->count();
            }
        }
    }
@endphp

<div class="category-item">
    @if($hasChildren)
        <!-- Folder with children (expandable) -->
        <div class="flex items-center" style="padding-left: {{ $level * 20 }}px;">
            <button 
                @click="openFolders['{{ $category->id }}'] = !openFolders['{{ $category->id }}']"
                class="flex items-center gap-3 px-3 py-2 text-gray-700 transition-all duration-200 hover:bg-gray-50 rounded-md w-full text-left group"
            >
                <!-- Expand/Collapse Icon -->
                <i class="fas fa-chevron-right text-xs text-gray-400 transition-transform duration-200 w-3" 
                   :class="openFolders['{{ $category->id }}'] ? 'rotate-90' : ''"></i>
                
                <!-- Folder Icon -->
                <i class="fas fa-folder text-amber-500 text-sm w-4"></i>
                
                <!-- Category Name -->
                <span class="flex-1 text-sm font-medium">{{ $category->name }}</span>
                
                <!-- Document Count -->
                @if($totalDocs > 0)
                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                    {{ $totalDocs }}
                </span>
                @endif
            </button>
        </div>

        <!-- Children (collapsible) -->
        <div x-show="openFolders['{{ $category->id }}']" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 max-h-0"
             x-transition:enter-end="opacity-100 max-h-screen"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 max-h-screen"
             x-transition:leave-end="opacity-0 max-h-0"
             class="overflow-hidden">
            @foreach($category->children->where('is_active', true)->sortBy('sort_order') as $child)
                @include('components.category-menu-item', ['category' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    @else
        <!-- Category without children (clickable link) -->
        <div class="flex items-center" style="padding-left: {{ ($level * 20) + 16 }}px;">
            <a href="{{ route('users.portal', ['category' => $category->id]) }}" 
               class="flex items-center gap-3 px-3 py-2 text-gray-700 transition-all duration-200 hover:bg-gray-50 rounded-md w-full group {{ request()->get('category') == $category->id ? 'bg-blue-50 text-blue-700' : '' }}">
                
                <!-- File Icon -->
                <i class="fas fa-file-alt text-gray-400 text-sm w-4"></i>
                
                <!-- Category Name -->
                <span class="flex-1 text-sm">{{ $category->name }}</span>
                
                <!-- Document Count -->
                @if($category->active_documents_count > 0)
                <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                    {{ $category->active_documents_count }}
                </span>
                @endif
            </a>
        </div>
    @endif
</div>
