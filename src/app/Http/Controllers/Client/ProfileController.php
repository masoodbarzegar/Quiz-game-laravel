<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GameSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $gameHistory = GameSession::with('game')
            ->where('client_id', $request->user()->id)
            ->where('status', 'completed')
            ->latest('ended_at')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'score' => $session->score,
                    'correct_answers' => $session->correct_answers,
                    'questions_answered' => $session->questions_answered,
                    'total_time_taken' => $session->total_time_taken,
                    'ended_at' => $session->ended_at,
                    'end_reason' => $session->end_reason,
                    'game' => [
                        'id' => $session->game->id,
                        'name' => $session->game->name,
                        'slug' => $session->game->slug,
                        'difficulty' => $session->game->difficulty,
                    ],
                ];
            });

        return Inertia::render('Client/Profile', [
            'auth' => [
                'user' => $request->user(),
            ],
            'gameHistory' => $gameHistory,
        ]);
    }

    public function update(Request $request)
    {
        $client = auth('client')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('clients')->ignore($client->id),
            ],
        ]);
    
        $client->fill($validated);
    
        if ($client->isDirty('email')) {
            $client->email_verified_at = null;
        }
    
        $saveResult = $client->save();

        // Fetch a fresh instance from DB to double check
        $freshClient = \App\Models\Client::find($client->id);
    
        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
} 