<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim\Claim;
use App\Models\User\User;
use App\Services\LogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
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
     * Display the dashboard with statistics.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Get basic statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_claims' => Claim::count(),
            'pending_claims' => Claim::where('status', 'pending')->count(),
            'in_review_claims' => Claim::where('status', 'in_review')->count(),
            'approved_claims' => Claim::where('status', 'approved')->count(),
            'rejected_claims' => Claim::where('status', 'rejected')->count(),
        ];
        
        // Get claims by month (for the last 6 months)
        $claimsByMonth = $this->getClaimsByMonth(6);
        
        // Get claims by category
        $claimsByCategory = $this->getClaimsByCategory();
        
        // Get average processing time (in days)
        $avgProcessingTime = $this->getAverageProcessingTime();
        
        // Get total claim amounts by status
        $claimAmounts = $this->getClaimAmountsByStatus();
        
        $this->logService->log(
            'admin', 
            'viewed_dashboard', 
            'Admin viewed dashboard and statistics'
        );
        
        return view('pages.admin.reports.dashboard', compact(
            'stats', 
            'claimsByMonth', 
            'claimsByCategory', 
            'avgProcessingTime',
            'claimAmounts'
        ));
    }

    /**
     * Display user activity report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function userActivity(Request $request)
    {
        $query = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select(
                'activity_logs.*', 
                'users.name as user_name', 
                'users.email as user_email'
            );
        
        // Apply filters
        if ($request->has('user_id') && $request->input('user_id')) {
            $query->where('activity_logs.user_id', $request->input('user_id'));
        }
        
        if ($request->has('activity_type') && $request->input('activity_type')) {
            $query->where('activity_logs.activity_type', $request->input('activity_type'));
        }
        
        if ($request->has('date_from')) {
            $query->whereDate('activity_logs.created_at', '>=', $request->input('date_from'));
        }
        
        if ($request->has('date_to')) {
            $query->whereDate('activity_logs.created_at', '<=', $request->input('date_to'));
        }
        
        $activities = $query->orderBy('activity_logs.created_at', 'desc')
            ->paginate(20);
        
        // Get list of users for the filter dropdown
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();
        
        // Get distinct activity types for filter dropdown
        $activityTypes = DB::table('activity_logs')
            ->select('activity_type')
            ->distinct()
            ->pluck('activity_type');
        
        $this->logService->log(
            'admin', 
            'viewed_user_activity_report', 
            'Admin viewed user activity report'
        );
        
        return view('pages.admin.reports.user_activity', compact(
            'activities', 
            'users', 
            'activityTypes'
        ));
    }

    /**
     * Display claim processing report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function claimProcessing(Request $request)
    {
        $period = $request->input('period', 'month');
        $dateRange = $this->getDateRangeFromPeriod($period);
        
        // Get claims processed during period
        $processedClaims = Claim::whereIn('status', ['approved', 'rejected'])
            ->whereBetween('updated_at', [$dateRange['start'], $dateRange['end']])
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Calculate metrics
        $metrics = [
            'total_processed' => $processedClaims->count(),
            'approved' => $processedClaims->where('status', 'approved')->count(),
            'rejected' => $processedClaims->where('status', 'rejected')->count(),
            'approval_rate' => $processedClaims->count() > 0 
                ? round(($processedClaims->where('status', 'approved')->count() / $processedClaims->count()) * 100, 2) 
                : 0,
            'total_amount_approved' => $processedClaims->where('status', 'approved')->sum('amount'),
            'avg_processing_time' => $this->calculateAvgProcessingTime($processedClaims),
        ];
        
        // Get processing time distribution
        $processingTimeDistribution = $this->getProcessingTimeDistribution($processedClaims);
        
        $this->logService->log(
            'admin', 
            'viewed_claim_processing_report', 
            'Admin viewed claim processing report',
            ['period' => $period]
        );
        
        return view('pages.admin.reports.claim_processing', compact(
            'processedClaims', 
            'metrics', 
            'processingTimeDistribution', 
            'period'
        ));
    }

    /**
     * Display financial report.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function financial(Request $request)
    {
        $period = $request->input('period', 'month');
        $dateRange = $this->getDateRangeFromPeriod($period);
        
        // Get approved claims in the period
        $approvedClaims = Claim::where('status', 'approved')
            ->whereBetween('updated_at', [$dateRange['start'], $dateRange['end']])
            ->get();
        
        // Total amount paid out
        $totalPaidOut = $approvedClaims->sum('amount');
        
        // Amount by category
        $amountByCategory = $approvedClaims->groupBy('category')
            ->map(function ($claims) {
                return $claims->sum('amount');
            });
        
        // Monthly payout trend (for the last 12 months)
        $monthlyPayoutTrend = $this->getMonthlyPayoutTrend(12);
        
        // Average claim amount
        $avgClaimAmount = $approvedClaims->count() > 0 
            ? $totalPaidOut / $approvedClaims->count() 
            : 0;
        
        $this->logService->log(
            'admin', 
            'viewed_financial_report', 
            'Admin viewed financial report',
            ['period' => $period]
        );
        
        return view('pages.admin.reports.financial', compact(
            'totalPaidOut', 
            'amountByCategory', 
            'monthlyPayoutTrend', 
            'avgClaimAmount', 
            'period'
        ));
    }

    /**
     * Export a report in the specified format.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:user_activity,claim_processing,financial',
            'format' => 'required|in:csv,excel,pdf',
            'period' => 'nullable|in:day,week,month,quarter,year,custom',
            'date_from' => 'nullable|required_if:period,custom|date',
            'date_to' => 'nullable|required_if:period,custom|date|after_or_equal:date_from',
        ]);
        
        $reportType = $request->input('report_type');
        $format = $request->input('format');
        
        // Determine date range
        $period = $request->input('period', 'month');
        $dateRange = $this->getDateRangeFromPeriod($period);
        
        if ($period === 'custom') {
            $dateRange = [
                'start' => Carbon::parse($request->input('date_from'))->startOfDay(),
                'end' => Carbon::parse($request->input('date_to'))->endOfDay(),
            ];
        }
        
        // Log export attempt
        $this->logService->log(
            'admin', 
            'exported_report', 
            'Admin exported ' . $reportType . ' report',
            [
                'format' => $format,
                'period' => $period,
                'date_range' => [
                    'start' => $dateRange['start']->toDateString(),
                    'end' => $dateRange['end']->toDateString(),
                ],
            ]
        );
        
        // Export implementation would go here - depends on export package
        // For now, we'll just return a placeholder response
        return response()->json([
            'success' => true,
            'message' => 'Export of ' . $reportType . ' report initiated in ' . $format . ' format.'
        ]);
    }

    /**
     * Get claims grouped by month for the specified number of months.
     *
     * @param int $months
     * @return array
     */
    private function getClaimsByMonth($months = 6)
    {
        $result = [];
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();
        
        for ($i = 0; $i < $months; $i++) {
            $currentDate = (clone $startDate)->addMonths($i);
            $monthLabel = $currentDate->format('M Y');
            
            $claims = Claim::whereYear('created_at', $currentDate->year)
                ->whereMonth('created_at', $currentDate->month)
                ->get();
            
            $result[$monthLabel] = [
                'total' => $claims->count(),
                'pending' => $claims->where('status', 'pending')->count(),
                'in_review' => $claims->where('status', 'in_review')->count(),
                'approved' => $claims->where('status', 'approved')->count(),
                'rejected' => $claims->where('status', 'rejected')->count(),
            ];
        }
        
        return $result;
    }

    /**
     * Get claims grouped by category.
     *
     * @return array
     */
    private function getClaimsByCategory()
    {
        return Claim::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->pluck('total', 'category')
            ->toArray();
    }

    /**
     * Get average processing time in days.
     *
     * @return float
     */
    private function getAverageProcessingTime()
    {
        $processedClaims = Claim::whereIn('status', ['approved', 'rejected'])->get();
        
        return $this->calculateAvgProcessingTime($processedClaims);
    }

    /**
     * Calculate average processing time from creation to approval/rejection.
     *
     * @param \Illuminate\Database\Eloquent\Collection $claims
     * @return float
     */
    private function calculateAvgProcessingTime($claims)
    {
        if ($claims->isEmpty()) {
            return 0;
        }
        
        $totalDays = 0;
        
        foreach ($claims as $claim) {
            $createdAt = Carbon::parse($claim->created_at);
            $updatedAt = Carbon::parse($claim->updated_at);
            $totalDays += $createdAt->diffInDays($updatedAt);
        }
        
        return round($totalDays / $claims->count(), 1);
    }

    /**
     * Get processing time distribution.
     *
     * @param \Illuminate\Database\Eloquent\Collection $claims
     * @return array
     */
    private function getProcessingTimeDistribution($claims)
    {
        $distribution = [
            '0-1 days' => 0,
            '2-3 days' => 0,
            '4-7 days' => 0,
            '8-14 days' => 0,
            '15-30 days' => 0,
            '30+ days' => 0,
        ];
        
        foreach ($claims as $claim) {
            $createdAt = Carbon::parse($claim->created_at);
            $updatedAt = Carbon::parse($claim->updated_at);
            $days = $createdAt->diffInDays($updatedAt);
            
            if ($days <= 1) {
                $distribution['0-1 days']++;
            } elseif ($days <= 3) {
                $distribution['2-3 days']++;
            } elseif ($days <= 7) {
                $distribution['4-7 days']++;
            } elseif ($days <= 14) {
                $distribution['8-14 days']++;
            } elseif ($days <= 30) {
                $distribution['15-30 days']++;
            } else {
                $distribution['30+ days']++;
            }
        }
        
        return $distribution;
    }

    /**
     * Get claim amounts by status.
     *
     * @return array
     */
    private function getClaimAmountsByStatus()
    {
        return [
            'pending' => Claim::where('status', 'pending')->sum('amount'),
            'in_review' => Claim::where('status', 'in_review')->sum('amount'),
            'approved' => Claim::where('status', 'approved')->sum('amount'),
            'rejected' => Claim::where('status', 'rejected')->sum('amount'),
        ];
    }

    /**
     * Get monthly payout trend.
     *
     * @param int $months
     * @return array
     */
    private function getMonthlyPayoutTrend($months = 12)
    {
        $result = [];
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();
        
        for ($i = 0; $i < $months; $i++) {
            $currentDate = (clone $startDate)->addMonths($i);
            $monthLabel = $currentDate->format('M Y');
            
            $approvedAmount = Claim::where('status', 'approved')
                ->whereYear('updated_at', $currentDate->year)
                ->whereMonth('updated_at', $currentDate->month)
                ->sum('amount');
            
            $result[$monthLabel] = $approvedAmount;
        }
        
        return $result;
    }

    /**
     * Get date range from period.
     *
     * @param string $period
     * @return array
     */
    private function getDateRangeFromPeriod($period)
    {
        $now = Carbon::now();
        
        switch ($period) {
            case 'day':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
            case 'quarter':
                return [
                    'start' => $now->copy()->startOfQuarter(),
                    'end' => $now->copy()->endOfQuarter(),
                ];
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                ];
            default:
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
        }
    }
} 