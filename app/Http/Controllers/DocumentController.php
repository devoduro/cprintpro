<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\UserDocumentPrint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Only apply admin middleware to certain methods
        $this->middleware('admin')->except(['view', 'download', 'print', 'preview']);
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
     * Show the form for bulk document upload
     */
    public function bulkCreate(Request $request)
    {
        $categoryId = $request->get('category');
        $selectedCategory = $categoryId ? DocumentCategory::findOrFail($categoryId) : null;
        $categories = DocumentCategory::active()->ordered()->get();
        
        return view('documents.bulk-create', compact('categories', 'selectedCategory'));
    }

    /**
     * Store multiple documents in bulk
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'document_category_id' => 'required|exists:document_categories,id',
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:pdf,doc,docx,txt,jpg,jpeg,png|max:10240', // 10MB max per file
            'is_printable' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $uploadedFiles = [];
        $errors = [];
        $successCount = 0;
        $totalFiles = count($request->file('files'));

        foreach ($request->file('files') as $index => $file) {
            try {
                // Generate unique filename
                $fileName = time() . '_' . $index . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('documents', $fileName, 'public');

                // Create document record
                $document = Document::create([
                    'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'description' => 'Bulk uploaded document',
                    'document_category_id' => $validated['document_category_id'],
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                    'is_printable' => $request->has('is_printable'),
                    'is_active' => $request->has('is_active')
                ]);

                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'id' => $document->id,
                    'status' => 'success'
                ];
                $successCount++;

            } catch (\Exception $e) {
                $errors[] = [
                    'name' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }

        // Return JSON response for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $successCount > 0,
                'message' => "Successfully uploaded {$successCount} of {$totalFiles} documents.",
                'uploaded_files' => $uploadedFiles,
                'errors' => $errors,
                'success_count' => $successCount,
                'total_count' => $totalFiles
            ]);
        }

        // Regular form submission
        $message = "Successfully uploaded {$successCount} of {$totalFiles} documents.";
        if (count($errors) > 0) {
            $message .= " " . count($errors) . " files failed to upload.";
        }

        return redirect()->route('documents.index')
            ->with('success', $message)
            ->with('bulk_upload_results', [
                'uploaded_files' => $uploadedFiles,
                'errors' => $errors
            ]);
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
        // Check if document is active
        if (!$document->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found or not available.'
            ], 404);
        }

        if (!$document->canBePrinted()) {
            return response()->json([
                'success' => false,
                'message' => 'Document cannot be printed.'
            ], 403);
        }

        $user = Auth::user();
        
        // Update or create user-specific print record
        $userPrint = UserDocumentPrint::firstOrCreate(
            [
                'user_id' => $user->id,
                'document_id' => $document->id
            ],
            [
                'print_count' => 0,
                'last_printed_at' => null
            ]
        );
        
        $userPrint->incrementPrintCount();
        
        // Also increment the global document print count
        $document->incrementPrintCount();

        // Return JSON response for AJAX requests
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Document sent to printer successfully.',
                'user_print_count' => $userPrint->print_count,
                'total_print_count' => $document->print_count
            ]);
        }

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

    /**
     * View document (for all authenticated users)
     */
    public function view(Document $document)
    {
        // Check if document is active
        if (!$document->is_active) {
            abort(404, 'Document not found or not available.');
        }

        $document->load(['category', 'uploader']);
        return view('documents.show', compact('document'));
    }

    /**
     * Get document preview data for AJAX requests
     */
    public function preview(Document $document)
    {
        // Check if document is active
        if (!$document->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found or not available.'
            ], 404);
        }

        $document->load(['category', 'uploader']);
        
        // Get file extension
        $fileExtension = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
        
        // Determine if preview is available
        $previewableTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'txt', 'md', 'csv'];
        $hasPreview = in_array($fileExtension, $previewableTypes);
        
        return response()->json([
            'success' => true,
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'file_name' => $document->file_name,
                'file_url' => $document->file_url,
                'file_size_formatted' => $document->file_size_formatted,
                'file_type' => $document->file_type,
                'can_be_printed' => $document->canBePrinted(),
                'has_preview' => $hasPreview,
                'preview_url' => $hasPreview ? $document->file_url : null,
                'category' => [
                    'id' => $document->category->id,
                    'name' => $document->category->name,
                    'color' => $document->category->color,
                    'icon' => $document->category->icon
                ],
                'uploader' => [
                    'name' => $document->uploader->name,
                    'email' => $document->uploader->email
                ],
                'created_at' => $document->created_at->toISOString(),
                'print_count' => $document->print_count
            ]
        ]);
    }
}
