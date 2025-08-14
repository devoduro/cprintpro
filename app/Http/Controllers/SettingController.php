<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // System statistics for settings overview
        $systemStats = [
            'total_users' => User::count(),
            'total_documents' => Document::count(),
            'total_categories' => DocumentCategory::count(),
            'total_storage_mb' => round(Document::sum('file_size') / 1024 / 1024, 2),
            'total_prints' => Document::sum('print_count'),
            'active_documents' => Document::where('is_active', true)->count(),
            'active_categories' => DocumentCategory::where('is_active', true)->count(),
        ];

        // Storage usage by category
        $categoryStorage = DocumentCategory::withSum('documents', 'file_size')
            ->having('documents_sum_file_size', '>', 0)
            ->orderByDesc('documents_sum_file_size')
            ->take(10)
            ->get()
            ->map(function ($category) {
                return [
                    'name' => $category->name,
                    'size_mb' => round(($category->documents_sum_file_size ?? 0) / 1024 / 1024, 2),
                    'color' => $category->color,
                    'icon' => $category->icon
                ];
            });

        // Recent system activities for monitoring
        $recentActivities = collect([
            [
                'type' => 'system_info',
                'message' => 'System cache size: ' . $this->getCacheSize(),
                'time' => now()->subMinutes(5)->diffForHumans(),
                'icon' => 'fas fa-server',
                'color' => 'text-blue-600'
            ],
            [
                'type' => 'storage_info',
                'message' => 'Total storage used: ' . $systemStats['total_storage_mb'] . ' MB',
                'time' => now()->subMinutes(10)->diffForHumans(),
                'icon' => 'fas fa-hdd',
                'color' => 'text-orange-600'
            ]
        ]);
        
        return view('settings.index', compact('systemStats', 'categoryStorage', 'recentActivities'));
    }

    /**
     * Get cache size information
     */
    private function getCacheSize()
    {
        try {
            $cacheSize = Cache::get('system_cache_size', '0 MB');
            return $cacheSize;
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }
    
    /**
     * Display academic years settings.
     */
   
    
    /**
     * Delete an academic year.
     */
    public function destroyAcademicYear(string $id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        
        // Check if there are any results associated with this academic year
        if ($academicYear->results()->count() > 0) {
            return redirect()->route('settings.index')
                ->with('error', 'Cannot delete academic year with associated results.');
        }
        
        $academicYear->delete();
        
        return redirect()->route('settings.index')
            ->with('success', 'Academic year deleted successfully.');
    }
    
    /**
     * Delete a grade scheme.
     */
    public function destroyGradeScheme(string $id)
    {
        $gradeScheme = GradeScheme::findOrFail($id);
        $gradeScheme->delete();
        
        return redirect()->route('settings.index')
            ->with('success', 'Grade scheme deleted successfully.');
    }
    
    /**
     * Delete a classification.
     */
    public function destroyClassification(string $id)
    {
        $classification = Classification::findOrFail($id);
        $classification->delete();
        
        return redirect()->route('settings.index')
            ->with('success', 'Classification deleted successfully.');
    }
    
    /**
     * Backup the database.
     */
    public function backupDatabase(Request $request)
    {
        \Log::info('Backup request details:', [
            'method' => $request->method(),
            'url' => $request->url(),
            'path' => $request->path(),
            'ajax' => $request->ajax(),
            'headers' => $request->headers->all()
        ]);
        try {
            // Generate a timestamp for the backup file
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.sql";
            
            // Get database configuration
            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            
            // Create backup command
            $command = "mysqldump --user={$username} --password={$password} --host={$host} {$database} > storage/app/backups/{$filename}";
            
            // Create backups directory if it doesn't exist
            if (!Storage::exists('backups')) {
                Storage::makeDirectory('backups');
            }
            
            // Execute the command
            exec($command, $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \Exception('Database backup failed.');
            }
            
            return redirect()->route('settings.index')
                ->with('success', 'Database backup created successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('settings.index')
                ->with('error', 'Database backup failed: ' . $e->getMessage());
        }
    }
    
    /**
     * List database backups.
     */
    public function listBackups()
    {
        $backups = Storage::files('backups');
        
        $backupFiles = [];
        foreach ($backups as $backup) {
            $backupFiles[] = [
                'name' => basename($backup),
                'size' => Storage::size($backup),
                'created_at' => Storage::lastModified($backup),
            ];
        }
        
        return view('settings.backups.index', compact('backupFiles'));
    }
    
    /**
     * List database backups.
     */
    public function backup()
    {
        $backupPath = storage_path('app/backups');
        $backups = [];
        
        // Initialize debug information with default values
        $debug = [
            'backup_path' => $backupPath,
            'backup_count' => 0,
            'directory_exists' => false,
            'directory_writable' => false,
            'directory_readable' => false,
            'directory_permissions' => 'N/A',
            'found_files' => '',
            'raw_files' => '',
            'php_user' => get_current_user(),
            'storage_path' => storage_path(),
            'public_path' => public_path()
        ];
        
        try {
            // Update directory status
            $debug['directory_exists'] = is_dir($backupPath);
            if ($debug['directory_exists']) {
                $debug['directory_writable'] = is_writable($backupPath);
                $debug['directory_readable'] = is_readable($backupPath);
                $debug['directory_permissions'] = substr(sprintf('%o', fileperms($backupPath)), -4);
            }
            
            // Ensure backup directory exists
            if (!Storage::exists('backups')) {
                Storage::makeDirectory('backups');
            }

            // Get all backup files
            $files = glob($backupPath . '/*.sql') ?: [];
            $debug['raw_files'] = implode(', ', $files);
            
            foreach ($files as $file) {
                if (is_file($file) && is_readable($file)) {
                    $filename = basename($file);
                    $backups[] = [
                        'filename' => $filename,
                        'size' => filesize($file),
                        'created_at' => filemtime($file),
                    ];
                }
            }
            
            // Sort backups by creation date (newest first)
            if (!empty($backups)) {
                usort($backups, function($a, $b) {
                    return $b['created_at'] - $a['created_at'];
                });
            }
            
            // Update backup-related debug info
            $debug['backup_count'] = count($backups);
            $debug['found_files'] = implode(', ', array_column($backups, 'filename'));
            
            return view('settings.backup', compact('backups', 'debug'));
            
        } catch (\Exception $e) {
            $debug['error'] = $e->getMessage();
            $debug['trace'] = $e->getTraceAsString();
            
            return view('settings.backup', [
                'backups' => [],
                'debug' => $debug
            ])->with('error', 'Error listing backups: ' . $e->getMessage());
        }
    }
    
    /**
     * Display system settings.
     */
    public function system()
    {
        // Get system settings from DB or config
        $settings = DB::table('settings')->where('category', 'system')->get();
        
        return view('settings.system', compact('settings'));
    }
    
    /**
     * Update system settings.
     */
    public function updateSystem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'institution_name' => 'required|string|max:255',
            'institution_code' => 'required|string|max:50',
            'institution_address' => 'nullable|string',
            'institution_contact' => 'nullable|string',
            'transcript_prefix' => 'nullable|string|max:10',
            'transcript_footer' => 'nullable|string',
            'transcript_signature' => 'nullable|string|max:100',
            'transcript_watermark' => 'nullable|string|max:50',
            'email_from_address' => 'nullable|email',
            'email_from_name' => 'nullable|string|max:100',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('settings.system')
                ->withErrors($validator)
                ->withInput();
        }
        
        // Update or create settings in the database
        $fields = [
            'institution_name',
            'institution_code',
            'institution_address',
            'institution_contact',
            'transcript_prefix',
            'transcript_footer',
            'transcript_signature',
            'transcript_watermark',
            'email_from_address',
            'email_from_name',
        ];
        
        foreach ($fields as $field) {
            DB::table('settings')->updateOrInsert(
                ['key' => $field, 'category' => 'system'],
                ['value' => $request->input($field), 'updated_at' => now()]
            );
        }
        
        return redirect()->route('settings.system')->with('success', 'System settings updated successfully.');
    }
    

    

    
/**
 * Display institution profile settings.
 */
public function institution()
{
    // Get institution settings from DB
    $settings = DB::table('settings')->where('category', 'institution')->get();
    
    return view('settings.institution', compact('settings'));
}

/**
 * Update institution profile settings.
 */
public function updateInstitution(Request $request)
{
    try {
        // Validate request
        $validator = Validator::make($request->all(), [
            'institution_name' => 'required|string|max:255',
            'institution_slogan' => 'nullable|string|max:255',
            'institution_address' => 'required|string',
            'institution_phone' => 'required|string|max:20',
            'institution_email' => 'required|email',
            'institution_website' => 'nullable|url',
            'institution_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'academic_affairs_signature' => 'nullable|image|mimes:jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return redirect()->route('settings.institution')
                ->withErrors($validator)
                ->withInput();
        }

        // Update settings in database
        $settings = [
            'institution_name',
            'institution_slogan',
            'institution_address',
            'institution_phone',
            'institution_email',
            'institution_website'
        ];

        foreach ($settings as $key) {
            if ($request->has($key)) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $key, 'category' => 'institution'],
                    [
                        'value' => $request->input($key),
                        'type' => 'text'
                    ]
                );
            }
        }

        // Handle logo upload if present
        if ($request->hasFile('institution_logo')) {
            $logo = $request->file('institution_logo');
            $path = $logo->store('public/logos');
            
            // Update or insert logo path in settings
            DB::table('settings')->updateOrInsert(
                ['key' => 'institution_logo', 'category' => 'institution'],
                [
                    'value' => str_replace('public/', '', $path),
                    'type' => 'image'
                ]
            );
        }

        // Handle academic affairs signature upload if present
        if ($request->hasFile('academic_affairs_signature')) {
            $signature = $request->file('academic_affairs_signature');
            
            // Store in public disk under signatures directory
            $path = $signature->storeAs('signatures', $signature->hashName(), 'public');
            
            // Update or insert signature path in settings
            DB::table('settings')->updateOrInsert(
                ['key' => 'academic_affairs_signature', 'category' => 'institution'],
                [
                    'value' => $path,
                    'type' => 'image'
                ]
            );
        }

        return redirect()->route('settings.institution')
            ->with('success', 'Institution profile updated successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('settings.institution')
                ->with('error', 'Failed to update institution profile: ' . $e->getMessage());
        }
    }
    
    /**
     * Download a database backup.
     */
    public function downloadBackup(string $filename)
    {
        try {
            $backupPath = storage_path('app/backups');
            $filePath = $backupPath . '/' . $filename;
            
            // Security check: ensure the file is within the backups directory
            if (!str_starts_with(realpath($filePath), realpath($backupPath))) {
                return redirect()->route('settings.backup')
                    ->with('error', 'Invalid backup file path.');
            }
            
            // Validate file exists and is readable
            if (!file_exists($filePath) || !is_readable($filePath)) {
                return redirect()->route('settings.backup')
                    ->with('error', 'Backup file not found or not readable.');
            }
            
            // Validate file extension
            if (pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
                return redirect()->route('settings.backup')
                    ->with('error', 'Invalid backup file type.');
            }
            
            // Log debug information
            \Log::debug('Downloading backup', [
                'filename' => $filename,
                'path' => $filePath,
                'exists' => file_exists($filePath),
                'readable' => is_readable($filePath),
                'size' => filesize($filePath)
            ]);
            
            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename=' . $filename,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Backup download failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('settings.backup')
                ->with('error', 'Failed to download backup: ' . $e->getMessage());
        }
    }

    /**
     * Delete a database backup.
     */
    public function destroyBackup(string $filename)
    {
        try {
            $backupPath = storage_path('app/backups');
            $filePath = $backupPath . '/' . $filename;
            
            // Security check: ensure the file is within the backups directory
            if (!str_starts_with(realpath($filePath), realpath($backupPath))) {
                return redirect()->route('settings.backup')
                    ->with('error', 'Invalid backup file path.');
            }
            
            // Validate file exists
            if (!file_exists($filePath)) {
                return redirect()->route('settings.backup')
                    ->with('error', 'Backup file not found.');
            }
            
            // Attempt to delete the file
            if (!unlink($filePath)) {
                throw new \Exception('Failed to delete file');
            }
            
            return redirect()->route('settings.backup')
                ->with('success', 'Backup deleted successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Backup deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('settings.backup')
                ->with('error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }

    /**
     * Set an academic year as current.
     */
    public function setCurrentAcademicYear(AcademicYear $academicYear)
    {
        try {
            DB::beginTransaction();
            
            // Unset all current academic years
            DB::statement('UPDATE academic_years SET is_current = 0');
            
            // Set this one as current
            DB::statement('UPDATE academic_years SET is_current = 1 WHERE id = ?', [$academicYear->id]);
            
            DB::commit();
            
            return redirect()->route('settings.academic-years')
                ->with('success', 'Academic year set as current successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('settings.academic-years')
                ->with('error', 'Failed to set academic year as current: ' . $e->getMessage());
        }
    }
}
