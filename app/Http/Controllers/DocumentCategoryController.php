<?php

namespace App\Http\Controllers;

use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $parentId = $request->get('parent');
        $parent = $parentId ? DocumentCategory::findOrFail($parentId) : null;
        
        $query = DocumentCategory::withCount('documents')
            ->with(['parent', 'children']);
            
        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->rootCategories();
        }
        
        $categories = $query->ordered()->paginate(15);

        return view('document-categories.index', compact('categories', 'parent'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $parentId = $request->get('parent');
        $parent = $parentId ? DocumentCategory::findOrFail($parentId) : null;
        $type = $request->get('type', 'category'); // category or folder
        
        return view('document-categories.create', compact('parent', 'type'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'required|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'parent_id' => 'nullable|exists:document_categories,id',
            'type' => 'required|in:category,folder'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = DocumentCategory::create($validated);
        
        // Update path after creation
        $category->updatePath();

        $redirectUrl = $validated['parent_id'] 
            ? route('document-categories.index', ['parent' => $validated['parent_id']])
            : route('document-categories.index');

        return redirect($redirectUrl)
            ->with('success', ($validated['type'] === 'folder' ? 'Folder' : 'Category') . ' created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DocumentCategory $documentCategory)
    {
        $documentCategory->load([
            'documents' => function($query) {
                $query->active()->latest();
            },
            'children' => function($query) {
                $query->active()->ordered();
            },
            'parent'
        ]);

        return view('document-categories.show', compact('documentCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentCategory $documentCategory)
    {
        return view('document-categories.edit', compact('documentCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentCategory $documentCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_categories,name,' . $documentCategory->id,
            'description' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'required|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $documentCategory->update($validated);

        return redirect()->route('document-categories.index')
            ->with('success', 'Document category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentCategory $documentCategory)
    {
        // Check if category has documents
        if ($documentCategory->documents()->count() > 0) {
            return redirect()->route('document-categories.index')
                ->with('error', 'Cannot delete category that contains documents. Please move or delete documents first.');
        }

        $documentCategory->delete();

        return redirect()->route('document-categories.index')
            ->with('success', 'Document category deleted successfully.');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(DocumentCategory $documentCategory)
    {
        $documentCategory->update([
            'is_active' => !$documentCategory->is_active
        ]);

        $status = $documentCategory->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Document category {$status} successfully.");
    }
}
