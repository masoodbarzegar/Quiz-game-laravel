<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameSession;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;

class GameReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
 
        $query = GameSession::with('client')->latest();

        if ($request->has('client_id') && $request->client_id != '') {
            $query->where('client_id', $request->client_id);
        }

        $gameSessions = $query->paginate(15)->withQueryString();

        $clients = Client::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/GameReports/Index', [
            'gameSessions' => $gameSessions,
            'clients' => $clients,
            'filters' => $request->only(['client_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Not needed for reports
    }

    /**
     * Display the specified resource.
     */
    public function show(GameSession $gameSession)
    {
        // Could be used to show a detailed single report view if needed
        // return Inertia::render('Admin/GameReports/Show', ['gameSession' => $gameSession->load('client')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GameSession $gameSession)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GameSession $gameSession)
    {
        // Not needed for reports
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GameSession $gameSession)
    {
        // Not needed for reports, unless you want to allow deleting game sessions
    }
}
