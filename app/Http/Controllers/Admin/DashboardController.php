<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim\Claim;
use App\Models\User\User;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * @var LogService
     */
    protected $logService;

    /**
     * Create a new controller instance.
     *
     * @param LogService $logService
     * @return void
     */
    public function __construct(LogService $logService)
    {
        $this->middleware(['auth', 'activity', 'admin']);
        $this->logService = $logService;
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get key metrics for the dashboard
        $metrics = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_claims' => Claim::count(),
            'pending_claims' => Claim::where('status', 'pending')->count(),
            'approved_claims' => Claim::where('status', 'approved')->count(),
            'rejected_claims' => Claim::where('status', 'rejected')->count(),
        ];
        
        // Get latest claims
        $latestClaims = Claim::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get latest users
        $latestUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get claims by status for chart
        $claimsByStatus = Claim::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
        
        // Get claims by month for trend chart
        $claimsByMonth = Claim::select(
                DB::raw('MONTH(created_at) as month'), 
                DB::raw('YEAR(created_at) as year'),
                DB::raw('count(*) as count')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        return view('pages.admin.dashboard', compact(
            'metrics', 
            'latestClaims', 
            'latestUsers', 
            'claimsByStatus', 
            'claimsByMonth'
        ));
    }
} 