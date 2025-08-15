<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
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
        $query = Document::with(['category', 'uploader']);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('document_category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $documents = $query->latest()->paginate(15);
        $categories = DocumentCategory::active()->ordered()->get();

        return view('documents.index', compact('documents', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $categoryId = $request->get('category');
        $selectedCategory = $categoryId ? DocumentCategory::findOrFail($categoryId) : null;
        $categories = DocumentCategory::active()->ordered()->get();
        
        return view('documents.create', compact('categories', 'selectedCategory'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_category_id' => 'required|exists:document_categories,id',
            'file' => 'required|file|mimes:pdf,doc,docx,txt,jpg,jpeg,png|max:10240', // 10MB max
            'is_printable' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('documents', $fileName, 'public');

        $validated['file_name'] = $file->getClientOriginalName();
        $validated['file_path'] = $filePath;
        $validated['file_type'] = $file->getClientMimeType();
        $validated['file_size'] = $file->getSize();
        $validated['uploaded_by'] = Auth::id();
        $validated['is_printable'] = $request->has('is_printable');
        $validated['is_active'] = $request->has('is_active');

        Document::create($validated);

        return redirect()->route('documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        $document->load(['category', 'uploader']);
        return view('documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        $categories = DocumentCategory::active()->ordered()->get();
        return view('documents.edit', compact('document', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_category_id' => 'required|exists:document_categories,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,jpeg,png|max:10240',
            'is_printable' => 'boolean',
            'is_active' => 'boolean'
        ]);

        // Handle file replacement
        if ($request->hasFile('file')) {
            // Delete old file
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('documents', $fileName, 'public');

            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_path'] = $filePath;
            $validated['file_type'] = $file->getClientMimeType();
            $validated['file_size'] = $file->getSize();
        }

        $validated['is_printable'] = $request->has('is_printable');
        $validated['is_active'] = $request->has('is_active');

        $document->update($validated);

        return redirect()->route('documents.index')
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        // Delete file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * Download document
     */
    public function download(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Print document (increment print count)
     */
    public function print(Document $document)
    {
        if (!$document->canBePrinted()) {
            return redirect()->back()->with('error', 'Document cannot be printed.');
        }

        $document->incrementPrintCount();

        // Return a print view that opens the document in a new window for printing
        return view('documents.print', compact('document'));
    }

    /**
     * Toggle document status
     */
    public function toggleStatus(Document $document)
    {
        $document->update([
            'is_active' => !$document->is_active
        ]);

        $status = $document->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Document {$status} successfully.");
    }
}
