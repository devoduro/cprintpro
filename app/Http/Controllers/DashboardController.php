<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        
        // Get total number of users
        $totalUsers = User::count();
        
        // Calculate user growth percentage
        $lastMonthUsers = User::where('created_at', '<', Carbon::now()->subMonth())->count();
        $userGrowth = $lastMonthUsers > 0 ? 
            round((($totalUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 1) : 0;


        

        

        
        // Get recent users
        $recentUsers = User::latest()
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->diffForHumans()
                ];
            });
        
        // Get recent user activities (latest 5 user registrations)
        $recentActivities = User::latest()
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'user_name' => $user->name,
                    'created_at' => $user->created_at->diffForHumans(),
                    'type' => 'registration',
                    'message' => "{$user->name} registered",
                    'user' => $user->name,
                    'time' => $user->created_at->diffForHumans()
                ];
            });
        
        return view('dashboard', [
            'totalUsers' => $totalUsers,
            'userGrowth' => $userGrowth,
            'recentActivities' => $recentActivities ?? [],
            'recentUsers' => $recentUsers ?? []
        ]);
    }
}
