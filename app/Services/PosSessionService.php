<?php

namespace App\Services;

use App\Models\PosSession;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PosSessionService
{
    /**
     * Obtener la sesión activa del usuario actual
     */
    public function getActiveSession($userId = null): ?PosSession
    {
        $userId = $userId ?? Auth::id();
        return PosSession::getActiveSession($userId);
    }

    /**
     * Verificar si el usuario tiene una sesión activa
     */
    public function hasActiveSession($userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        return PosSession::hasActiveSession($userId);
    }

    /**
     * Abrir una nueva sesión POS
     */
    public function openSession(array $data): PosSession
    {
        $userId = $data['user_id'] ?? Auth::id();

        // Validar que no haya sesión activa
        if ($this->hasActiveSession($userId)) {
            throw new \Exception('Ya existe una sesión POS activa para este usuario.');
        }

        return DB::transaction(function () use ($data, $userId) {
            return PosSession::openSession([
                'user_id' => $userId,
                'initial_cash' => $data['initial_cash'] ?? 0,
                'opening_notes' => $data['opening_notes'] ?? null,
                'cash_breakdown' => $data['cash_breakdown'] ?? null,
            ]);
        });
    }

    /**
     * Cerrar una sesión POS
     */
    public function closeSession(PosSession $session, array $data = []): PosSession
    {
        if ($session->isClosed()) {
            throw new \Exception('La sesión ya está cerrada.');
        }

        return DB::transaction(function () use ($session, $data) {
            // Calcular efectivo esperado basado en ventas
            $expectedCash = $session->initial_cash + $session->cash_sales;

            $finalCash = $data['final_cash'] ?? $expectedCash;
            $cashDifference = $finalCash - $expectedCash;

            return $session->closeSession([
                'final_cash' => $finalCash,
                'expected_cash' => $expectedCash,
                'cash_difference' => $cashDifference,
                'closing_notes' => $data['closing_notes'] ?? null,
                'cash_breakdown' => $data['cash_breakdown'] ?? null,
            ]);
        });
    }

    /**
     * Obtener resumen de la sesión
     */
    public function getSessionSummary(PosSession $session): array
    {
        $invoices = $session->invoices()->where('status', 'paid')->get();

        $summary = [
            'session' => $session,
            'total_sales' => $session->total_sales,
            'sales_count' => $session->sales_count,
            'cash_sales' => $session->cash_sales,
            'card_sales' => $session->card_sales,
            'expected_cash' => $session->initial_cash + $session->cash_sales,
            'duration' => $session->duration,
            'invoices_by_hour' => $this->getInvoicesByHour($invoices),
            'payment_methods_breakdown' => $this->getPaymentMethodsBreakdown($invoices),
        ];

        // Si está cerrada, incluir información de cierre
        if ($session->isClosed()) {
            $summary['cash_difference'] = $session->cash_difference;
            $summary['final_cash'] = $session->final_cash;
        }

        return $summary;
    }

    /**
     * Obtener ventas por hora
     */
    private function getInvoicesByHour($invoices): array
    {
        return $invoices->groupBy(function ($invoice) {
            return Carbon::parse($invoice->date)->format('H:00');
        })->map(function ($hourInvoices) {
            return [
                'count' => $hourInvoices->count(),
                'total' => $hourInvoices->sum('total_amount'),
            ];
        })->toArray();
    }

    /**
     * Obtener desglose por métodos de pago
     */
    private function getPaymentMethodsBreakdown($invoices): array
    {
        return $invoices->groupBy('payment_method')->map(function ($methodInvoices) {
            return [
                'count' => $methodInvoices->count(),
                'total' => $methodInvoices->sum('total_amount'),
            ];
        })->toArray();
    }

    /**
     * Obtener sesiones con filtros y paginación
     */
    public function filterAndPaginate(array $filters = [])
    {
        $query = PosSession::with(['user', 'invoices' => function($query) {
                $query->where('status', 'paid');
            }])
            ->orderBy('opened_at', 'desc');

        // Aplicar filtros
        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('opened_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('opened_at', '<=', $filters['date_to']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = $filters['per_page'] ?? 15;

        $sessions = $query->paginate($perPage);

        // Calcular manualmente los valores para asegurar que aparezcan
        $sessions->getCollection()->transform(function ($session) {
            // Asegurar que tenemos las facturas pagadas
            $paidInvoices = $session->invoices;

            // Calcular totales manualmente
            $totalSales = $paidInvoices->sum('total_amount');
            $salesCount = $paidInvoices->count();
            $cashSales = $paidInvoices->where('payment_method', 'cash')->sum('total_amount');
            $cardSales = $paidInvoices->where('payment_method', 'card')->sum('total_amount');

            // Agregar propiedades calculadas
            $session->total_sales = $totalSales;
            $session->sales_count = $salesCount;
            $session->cash_sales = $cashSales;
            $session->card_sales = $cardSales;
            $session->user_name = $session->user->name ?? 'N/A';

            return $session;
        });

        return $sessions;
    }

    /**
     * Forzar cierre de sesión (solo para admin)
     */
    public function forceCloseSession(PosSession $session, array $data = []): PosSession
    {
        if ($session->isClosed()) {
            throw new \Exception('La sesión ya está cerrada.');
        }

        return DB::transaction(function () use ($session, $data) {
            return $session->closeSession([
                'final_cash' => $data['final_cash'] ?? $session->initial_cash,
                'expected_cash' => $session->initial_cash + $session->cash_sales,
                'cash_difference' => ($data['final_cash'] ?? $session->initial_cash) - ($session->initial_cash + $session->cash_sales),
                'closing_notes' => $data['closing_notes'] ?? 'Sesión cerrada forzadamente por administrador',
                'cash_breakdown' => $data['cash_breakdown'] ?? null,
            ]);
        });
    }

    /**
     * Obtener estadísticas generales de sesiones
     */
    public function getGeneralStats(array $filters = []): array
    {
        $query = PosSession::with(['invoices' => function($query) {
                $query->where('status', 'paid');
            }]);

        // Aplicar filtros de fecha
        if (isset($filters['date_from'])) {
            $query->whereDate('opened_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('opened_at', '<=', $filters['date_to']);
        }

        $sessions = $query->get();

        // Calcular estadísticas manualmente
        $totalSales = 0;
        $openSessions = 0;
        $closedSessions = 0;
        $totalCashDifference = 0;

        foreach ($sessions as $session) {
            if ($session->status === 'open') {
                $openSessions++;
            } else {
                $closedSessions++;
                $totalCashDifference += $session->cash_difference ?? 0;
            }

            // Sumar ventas de todas las facturas pagadas
            $totalSales += $session->invoices->sum('total_amount');
        }

        return [
            'total_sessions' => $sessions->count(),
            'open_sessions' => $openSessions,
            'closed_sessions' => $closedSessions,
            'total_sales' => $totalSales,
            'average_session_duration' => $this->getAverageSessionDuration($sessions->where('status', 'closed')),
            'total_cash_difference' => $totalCashDifference,
        ];
    }

    /**
     * Calcular duración promedio de sesiones cerradas
     */
    private function getAverageSessionDuration($closedSessions): ?string
    {
        if ($closedSessions->isEmpty()) {
            return null;
        }

        $totalMinutes = $closedSessions->sum(function ($session) {
            return Carbon::parse($session->opened_at)->diffInMinutes($session->closed_at);
        });

        $averageMinutes = $totalMinutes / $closedSessions->count();
        $hours = floor($averageMinutes / 60);
        $minutes = $averageMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
