<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\PosSession;
use Illuminate\Http\Request;
use App\Services\PosSessionService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PosSession\OpenSessionRequest;
use App\Http\Requests\PosSession\CloseSessionRequest;

class PosSessionController extends Controller
{
    protected PosSessionService $posSessionService;

    public function __construct(PosSessionService $posSessionService)
    {
        $this->posSessionService = $posSessionService;
    }

    /**
     * Display a listing of POS sessions.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['per_page', 'search', 'user_id', 'status', 'date_from', 'date_to']);
        $sessions = $this->posSessionService->filterAndPaginate($filters);
        $stats = $this->posSessionService->getGeneralStats($filters);

        return Inertia::render('PosSession/Index', [
            'sessions' => $sessions,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new session.
     */
    public function create()
    {
        // Verificar si ya tiene una sesión activa
        if ($this->posSessionService->hasActiveSession()) {
            return redirect()->route('sessions.current')
                ->with('error', 'Ya tienes una sesión POS activa. Debes cerrarla antes de abrir una nueva.');
        }

        return Inertia::render('PosSession/Create');
    }

    /**
     * Store a newly created session.
     */
    public function store(OpenSessionRequest $request)
    {
        try {
            $session = $this->posSessionService->openSession($request->validated());

            return redirect()->route('sessions.current')
                ->with('success', 'Sesión POS abierta exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified session.
     */
    public function show(PosSession $posSession)
    {
        $summary = $this->posSessionService->getSessionSummary($posSession);

        return Inertia::render('PosSession/Show', [
            'session' => $posSession->load(['user']),
            'summary' => $summary,
        ]);
    }

    /**
     * Show current active session.
     */
    public function current()
    {
        $session = $this->posSessionService->getActiveSession();

        if (!$session) {
            return redirect()->route('sessions.create')
                ->with('info', 'No tienes una sesión POS activa. Abre una nueva sesión para continuar.');
        }

        $summary = $this->posSessionService->getSessionSummary($session);

        return Inertia::render('PosSession/Current', [
            'session' => $session->load(['user']),
            'summary' => $summary,
        ]);
    }

    /**
     * Show the form for closing the session.
     */
    public function edit(PosSession $posSession)
    {
        if ($posSession->isClosed()) {
            return redirect()->route('sessions.show', $posSession)
                ->with('error', 'Esta sesión ya está cerrada.');
        }

        // Solo el usuario propietario o admin puede cerrar
        if ($posSession->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return redirect()->route('sessions.index')
                ->with('error', 'No tienes permisos para cerrar esta sesión.');
        }

        $summary = $this->posSessionService->getSessionSummary($posSession);

        return Inertia::render('PosSession/Close', [
            'session' => $posSession->load(['user']),
            'summary' => $summary,
        ]);
    }

    /**
     * Close the session.
     */
    public function update(CloseSessionRequest $request, PosSession $posSession)
    {
        try {
            // Solo el usuario propietario o admin puede cerrar
            if ($posSession->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return back()->with('error', 'No tienes permisos para cerrar esta sesión.');
            }

            $session = $this->posSessionService->closeSession($posSession, $request->validated());

            return redirect()->route('sessions.index')
                ->with('success', 'Sesión POS cerrada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Force close session (admin only).
     */
    public function forceClose(Request $request, PosSession $posSession)
    {
        try {
            $this->posSessionService->forceCloseSession($posSession, $request->all());

            return redirect()->route('sessions.index')
                ->with('success', 'Sesión cerrada forzadamente.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get active session info for API.
     */
    public function getActiveSession()
    {
        $session = $this->posSessionService->getActiveSession();

        if (!$session) {
            return response()->json([
                'session' => null,
                'message' => 'No hay sesión activa'
            ]);
        }

        $summary = $this->posSessionService->getSessionSummary($session);

        return response()->json([
            'session' => $session,
            'summary' => $summary,
        ]);
    }

    /**
     * Check if user has active session.
     */
    public function checkActiveSession()
    {
        $hasActive = $this->posSessionService->hasActiveSession();
        $session = $hasActive ? $this->posSessionService->getActiveSession() : null;

        return response()->json([
            'has_active_session' => $hasActive,
            'session' => $session,
        ]);
    }

    /**
     * Get session statistics.
     */
    public function getStats(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to']);
        $stats = $this->posSessionService->getGeneralStats($filters);

        return response()->json($stats);
    }
}
