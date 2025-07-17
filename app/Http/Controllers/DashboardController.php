<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\PosSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $userId = Auth::id();
        $dashboardData = $this->dashboardService->getDashboardData();

        // Sesión POS activa
        $activeSession = PosSession::getActiveSession($userId);

        return Inertia::render('Dashboard', [
            'stats' => $dashboardData['stats'],
            'todaySales' => $dashboardData['todaySales'],
            'monthlySales' => $dashboardData['monthlySales'],
            'lowStockProducts' => $dashboardData['lowStockProducts'],
            'recentSales' => $dashboardData['recentSales'],
            'salesChart' => $dashboardData['salesChart'],
            'topProducts' => $dashboardData['topProducts'],
            'activeSession' => $activeSession,
            'hasDynamicDashboard' => true, // Indicar que hay dashboard dinámico disponible
        ]);
    }
}
