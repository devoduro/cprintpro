<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function profile()
    {
        $user = Auth::user();
        return view('users.profile', compact('user'));
    }

    /**
     * Show the form for editing the authenticated user's profile.
     */
    public function editProfile()
    {
        return view('users.profile-edit');
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'profile_photo' => 'nullable|image|max:2048',
        ];

        // Handle password change
        if ($request->filled('password')) {
            $rules['current_password'] = 'required|string';
            $rules['password'] = 'required|string|min:8|confirmed';
            
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->route('profile.edit')
                    ->withErrors(['current_password' => 'The current password is incorrect.'])
                    ->withInput();
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('profile.edit')
                ->withErrors($validator)
                ->withInput();
        }

        $userData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->input('password'));
        }

        if ($request->hasFile('profile_photo')) {
            // Delete old profile photo if exists
            if ($user->profile_photo) {
                \Storage::disk('public')->delete($user->profile_photo);
            }
            
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $userData['profile_photo'] = $path;
            
            // Debug logging
            \Log::info('Profile photo uploaded', [
                'user_id' => $user->id,
                'file_path' => $path,
                'full_path' => storage_path('app/public/' . $path)
            ]);
        }

        $user->update($userData);

        $message = $request->filled('password') ? 
            'Profile and password updated successfully.' : 
            'Profile updated successfully.';

        return redirect()->route('profile.edit')
            ->with('success', $message);
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(User $user)
    {
        // Check if the current user is an admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('users.index')
                ->with('error', 'You are not authorized to reset passwords.');
        }
        
        return view('users.reset-password', compact('user'));
    }
    
    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request, User $user)
    {
        // Check if the current user is an admin
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('users.index')
                ->with('error', 'You are not authorized to reset passwords.');
        }
        
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user->update([
            'password' => Hash::make($request->password)
        ]);
        
        return redirect()->route('users.show', $user->id)
            ->with('success', 'Password has been reset successfully.');
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query()->orderBy('name');
        
        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Apply role filter
        if ($request->has('role') && !empty($request->role)) {
            $query->where('role', $request->role);
        }
        
        $users = $query->paginate(20)->withQueryString();
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,staff',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->route('users.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        $userData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
        ];

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $userData['profile_photo'] = $path;
        }

        $user = User::create($userData);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'profile_photo' => 'nullable|image|max:2048',
        ];

        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        // Only admin can change roles
        if (Auth::user()->role === 'admin') {
            $rules['role'] = 'required|in:admin,staff,student';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('users.edit', $user->id)
                ->withErrors($validator)
                ->withInput();
        }

        $userData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->input('password'));
        }

        if (Auth::user()->role === 'admin' && $request->has('role')) {
            $userData['role'] = $request->input('role');
        }

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $userData['profile_photo'] = $path;
        }

        $user->update($userData);

        return redirect()->route('users.show', $user->id)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Display the user portal with documents.
     */
    public function portal(Request $request)
    {
        $user = Auth::user();
        
        // Get documents with filters
        $query = \App\Models\Document::with(['category', 'uploader'])
            ->where('is_active', true);
            
        // Apply search filter
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply category filter
        if (request('category')) {
            $query->where('document_category_id', request('category'));
        }
        
        // Apply file type filter
        if (request('file_type')) {
            $query->where('file_type', request('file_type'));
        }
        
        // Get documents with pagination - sorted alphabetically by title
        $documents = $query->orderBy('title', 'asc')->paginate(12);
        
        // Get categories for filter dropdown
        $categories = \App\Models\DocumentCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        // Get file types for filter
        $fileTypes = \App\Models\Document::select('file_type')
            ->distinct()
            ->whereNotNull('file_type')
            ->orderBy('file_type')
            ->pluck('file_type');
            
        // Get user-specific statistics
        $userStats = [
            'total_prints' => $user->total_prints,
            'unique_documents_printed' => $user->unique_printed_documents,
            'last_print' => $user->documentPrints()->latest('last_printed_at')->first()?->last_printed_at
        ];
            
        // Get general statistics
        $stats = [
            'total_documents' => \App\Models\Document::where('is_active', true)->count(),
            'active_documents' => \App\Models\Document::where('is_active', true)->count(),
            'total_prints' => \App\Models\Document::sum('print_count'),
            'categories_count' => \App\Models\DocumentCategory::where('is_active', true)->count()
        ];
        
        return view('users.portal', compact('documents', 'categories', 'fileTypes', 'stats', 'userStats'));
    }

    /**
     * Get documents for portal (AJAX endpoint).
     */
    public function portalDocuments(Request $request)
    {
        $query = \App\Models\Document::with(['category', 'uploader'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc');
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('category')) {
            $query->where('document_category_id', $request->category);
        }
        
        if ($request->filled('file_type')) {
            $fileType = $request->file_type;
            if ($fileType === 'image') {
                $query->where(function($q) {
                    $q->where('file_name', 'like', '%.jpg')
                      ->orWhere('file_name', 'like', '%.jpeg')
                      ->orWhere('file_name', 'like', '%.png')
                      ->orWhere('file_name', 'like', '%.gif')
                      ->orWhere('file_name', 'like', '%.webp');
                });
            } else {
                $query->where('file_name', 'like', "%.{$fileType}");
            }
        }
        
        $documents = $query->paginate(12)->withQueryString();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'documents' => $documents->items(),
                'pagination' => [
                    'current_page' => $documents->currentPage(),
                    'last_page' => $documents->lastPage(),
                    'per_page' => $documents->perPage(),
                    'total' => $documents->total()
                ]
            ]);
        }
        
        return redirect()->route('users.portal');
    }
}
