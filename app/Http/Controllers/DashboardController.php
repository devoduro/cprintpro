<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\DocumentCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }
    /**
     * Display the dashboard with key metrics and analytics.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get basic user metrics
        $totalUsers = User::count();
        $lastMonthUsers = User::where('created_at', '<', Carbon::now()->subMonth())->count();
        $userGrowth = $lastMonthUsers > 0 ? 
            round((($totalUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 1) : 0;

        // Get document metrics
        $totalDocuments = Document::count();
        $activeDocuments = Document::where('is_active', true)->count();
        $totalPrints = Document::sum('print_count');
        $documentsThisMonth = Document::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        
        // Calculate document growth
        $lastMonthDocuments = Document::where('created_at', '<', Carbon::now()->subMonth())->count();
        $documentGrowth = $lastMonthDocuments > 0 ? 
            round((($totalDocuments - $lastMonthDocuments) / $lastMonthDocuments) * 100, 1) : 0;

        // Get category metrics
        $totalCategories = DocumentCategory::count();
        $activeCategories = DocumentCategory::where('is_active', true)->count();
        $categoriesWithDocuments = DocumentCategory::has('documents')->count();

        // Get storage metrics
        $totalStorageBytes = Document::sum('file_size');
        $averageFileSize = $totalDocuments > 0 ? round($totalStorageBytes / $totalDocuments / 1024 / 1024, 2) : 0;

        // Get recent documents
        $recentDocuments = Document::with(['category', 'uploader'])
            ->latest()
            ->take(5)
            ->get();

        // Get top printed documents
        $topPrintedDocuments = Document::with(['category'])
            ->where('print_count', '>', 0)
            ->orderBy('print_count', 'desc')
            ->take(5)
            ->get();

        // Get recent activities (mix of user registrations and document uploads)
        $recentUserActivities = User::latest()
            ->take(3)
            ->get()
            ->map(function ($user) {
                return [
                    'type' => 'user_registration',
                    'icon' => 'fas fa-user-plus',
                    'color' => 'text-blue-600',
                    'message' => "{$user->name} registered",
                    'time' => $user->created_at->diffForHumans(),
                    'user' => $user->name
                ];
            });

        $recentDocumentActivities = Document::with(['uploader', 'category'])
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($document) {
                return [
                    'type' => 'document_upload',
                    'icon' => 'fas fa-file-upload',
                    'color' => 'text-green-600',
                    'message' => "Document '{$document->title}' uploaded",
                    'time' => $document->created_at->diffForHumans(),
                    'user' => $document->uploader->name,
                    'category' => $document->category->name
                ];
            });

        $recentActivities = $recentUserActivities->merge($recentDocumentActivities)
            ->sortByDesc(function ($activity) {
                return $activity['time'];
            })
            ->take(6)
            ->values();

        return view('dashboard', [
            'totalUsers' => $totalUsers,
            'userGrowth' => $userGrowth,
            'totalDocuments' => $totalDocuments,
            'activeDocuments' => $activeDocuments,
            'totalPrints' => $totalPrints,
            'documentsThisMonth' => $documentsThisMonth,
            'documentGrowth' => $documentGrowth,
            'totalCategories' => $totalCategories,
            'activeCategories' => $activeCategories,
            'categoriesWithDocuments' => $categoriesWithDocuments,
            'totalStorageBytes' => $totalStorageBytes,
            'averageFileSize' => $averageFileSize,
            'recentDocuments' => $recentDocuments,
            'topPrintedDocuments' => $topPrintedDocuments,
            'recentActivities' => $recentActivities
        ]);
    }
}
